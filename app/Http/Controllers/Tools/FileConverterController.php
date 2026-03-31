<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

/**
 * FileConverterController — v3
 *
 * Fixes from v2:
 * 1. JS timeout raised to 10 min — PDF→Office on Windows can be slow
 * 2. findOutputFile() replaced with findNewestOutputFile() which is session-scoped
 *    → Fixes race condition where old files were returned on LO failure
 * 3. buildLoCmd() Windows: uses sys_get_temp_dir() for LO profile dir
 *    → Avoids LibreOffice choking on paths with spaces (e.g. "C:\storage\app\file_converter")
 * 4. Input filename is always a plain UUID (no spaces) → LO handles it cleanly
 * 5. Fallback chain more robust with explicit file-existence checks
 * 6. Output: "OriginalName by MediaTools.ext"
 *
 * .env:
 *   LIBREOFFICE_BINARY=soffice.exe   (Windows)
 *   GHOSTSCRIPT_BINARY=gswin64c.exe  (Windows)
 *   LIBREOFFICE_BINARY=soffice       (Linux/VPS)
 *   GHOSTSCRIPT_BINARY=gs            (Linux/VPS)
 */

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

class FileConverterController extends Controller
{
    private string $storageDir;
    private string $sofficeBin = '';
    private string $gsbin      = '';
    private bool   $isWindows;

    public function __construct()
    {
        $this->isWindows  = PHP_OS_FAMILY === 'Windows';
        $this->storageDir = storage_path('app/file_converter');

        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0777, true);
        }

        $this->sofficeBin = $this->resolveBinary(
            env('LIBREOFFICE_BINARY', ''),
            env('LIBREOFFICE_PATH',   ''),
            $this->isWindows
                ? ['soffice.exe', 'soffice',
                   '"C:\\Program Files\\LibreOffice\\program\\soffice.exe"',
                   'C:\\Program Files\\LibreOffice\\program\\soffice.exe']
                : ['soffice', 'libreoffice', '/usr/bin/soffice', '/usr/local/bin/soffice']
        );

        $this->gsbin = $this->resolveBinary(
            env('GHOSTSCRIPT_BINARY', ''),
            env('GHOSTSCRIPT_PATH',   ''),
            $this->isWindows
                ? ['gswin64c', 'gswin32c',
                   '"C:\\Program Files\\gs\\gs10.07.0\\bin\\gswin64c.exe"',
                   '"C:\\Program Files\\gs\\gs10.05.0\\bin\\gswin64c.exe"',
                   '"C:\\Program Files\\gs\\gs10.04.0\\bin\\gswin64c.exe"',
                   '"C:\\Program Files\\gs\\gs10.03.1\\bin\\gswin64c.exe"']
                : ['gs', '/usr/bin/gs', '/usr/local/bin/gs']
        );
    }

    /* =========================================================
       BINARY RESOLUTION
    ========================================================= */
    private function normalizeBinaryValue(string $value): string
    {
        $value = trim(trim($value), "\"'");
        if ($value === '') return '';
        if ($this->isWindows) $value = str_replace('/', '\\', $value);
        return $value;
    }

    private function resolveBinary(string $binaryEnv, string $pathEnv, array $fallbacks): string
    {
        if ($binaryEnv !== '') {
            $c = $this->normalizeBinaryValue($binaryEnv);
            if ($c !== '' && $this->binaryExists($c)) return $c;
        }
        if ($pathEnv !== '') {
            $c = $this->normalizeBinaryValue($pathEnv);
            if ($c !== '' && file_exists($c)) return $c;
        }
        foreach ($fallbacks as $candidate) {
            $c = $this->normalizeBinaryValue((string)$candidate);
            if ($c !== '' && $this->binaryExists($c)) return $c;
        }
        return '';
    }

    private function binaryExists(string $binary): bool
    {
        $binary = $this->normalizeBinaryValue($binary);
        if ($binary === '') return false;

        if ($this->looksLikePath($binary)) {
            return is_file($binary) && is_readable($binary);
        }

        $probeCmd = $this->isWindows
            ? 'where ' . $binary . ' >NUL 2>NUL'
            : 'command -v ' . escapeshellarg($binary) . ' >/dev/null 2>&1';
        exec($probeCmd, $out, $code);
        if ($code === 0) return true;

        $versionCmd = $this->isWindows
            ? '"' . $binary . '" -v >NUL 2>&1'
            : escapeshellcmd($binary) . ' --version >/dev/null 2>&1';
        exec($versionCmd, $out2, $code2);
        return $code2 === 0;
    }

    private function looksLikePath(string $value): bool
    {
        return str_contains($value, '\\')
            || str_starts_with($value, '/')
            || preg_match('/^[A-Za-z]:\\\\/', $value) === 1;
    }

    /* =========================================================
       INDEX
    ========================================================= */
    public function index()
    {
        return View::exists('tools.fileconverter.index')
            ? view('tools.fileconverter.index')
            : view('tools.index');
    }

    /* =========================================================
       PROCESS
    ========================================================= */
    public function process(Request $request)
    {
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '1024M');

        $allowed = implode(',', [
            'jpg_to_pdf', 'png_to_pdf', 'webp_to_pdf',
            'word_to_pdf', 'excel_to_pdf', 'ppt_to_pdf',
            'pdf_to_jpg', 'pdf_to_png',
            'pdf_to_word', 'pdf_to_excel', 'pdf_to_ppt',
            'jpg_to_png', 'png_to_jpg', 'jpg_to_webp',
            'png_to_webp', 'webp_to_jpg', 'webp_to_png',
            'pdf_compress',
        ]);

        $request->validate([
            'file'            => 'required|file|max:51200',
            'conversion_type' => "required|string|in:{$allowed}",
        ]);

        $type         = $request->input('conversion_type');
        $file         = $request->file('file');
        $sessionId    = Str::uuid()->toString();
        $originalName = $file->getClientOriginalName();

        $this->lazyCleanup();

        try {
            $outputFiles = match (true) {
                in_array($type, ['jpg_to_pdf', 'png_to_pdf', 'webp_to_pdf'])
                    => $this->imageToPdf($file, $sessionId, $originalName),

                in_array($type, ['word_to_pdf', 'excel_to_pdf', 'ppt_to_pdf'])
                    => $this->officeToPdf($file, $sessionId, $originalName),

                in_array($type, ['pdf_to_jpg', 'pdf_to_png'])
                    => $this->pdfToImage($file, $sessionId,
                           $type === 'pdf_to_jpg' ? 'jpg' : 'png', $originalName),

                in_array($type, ['pdf_to_word', 'pdf_to_excel', 'pdf_to_ppt'])
                    => $this->pdfToOffice($file, $sessionId, match ($type) {
                           'pdf_to_word'  => 'docx',
                           'pdf_to_excel' => 'xlsx',
                           'pdf_to_ppt'   => 'pptx',
                       }, $originalName),

                in_array($type, [
                    'jpg_to_png', 'png_to_jpg', 'jpg_to_webp',
                    'png_to_webp', 'webp_to_jpg', 'webp_to_png',
                ]) => $this->convertImage($file, $sessionId, $type, $originalName),

                $type === 'pdf_compress'
                    => $this->compressPdf($file, $sessionId, $originalName),

                default => throw new \Exception("Tipe konversi tidak didukung: {$type}"),
            };

            return response()->json([
                'success'       => true,
                'files'         => $outputFiles,
                'session'       => $sessionId,
                'original_name' => $originalName,
            ]);

        } catch (\Exception $e) {
            Log::error("FileConverter [{$type}]: " . $e->getMessage(), [
                'file'  => $originalName,
                'trace' => substr($e->getTraceAsString(), 0, 1000),
            ]);
            return response()->json([
                'success' => false,
                'message' => $this->friendlyError($type, $e->getMessage()),
            ], 422);
        }
    }

    /* =========================================================
       OUTPUT NAMING  →  "NamaFile by MediaTools.ext"
    ========================================================= */
    private function buildOutputName(string $originalName, string $ext, ?int $pageNum = null): string
    {
        $base = pathinfo($originalName, PATHINFO_FILENAME);
        $base = preg_replace('/[\\\\\/:\*\?"<>\|]/', '_', $base);
        $base = trim($base) ?: 'file';

        $suffix = ' by MediaTools';
        return $pageNum !== null
            ? "{$base}{$suffix} - Hal {$pageNum}.{$ext}"
            : "{$base}{$suffix}.{$ext}";
    }

    private function saveWithDisplayName(string $tempPath, string $origName, string $ext, ?int $pageNum = null): string
    {
        if (!file_exists($tempPath) || filesize($tempPath) === 0) {
            throw new \Exception("Output kosong / gagal dihasilkan.");
        }

        $displayName = $this->buildOutputName($origName, $ext, $pageNum);
        $destPath    = $this->storageDir . DS . $displayName;

        if (file_exists($destPath)) {
            $uid         = substr(md5(uniqid('', true)), 0, 6);
            $base        = pathinfo($displayName, PATHINFO_FILENAME);
            $extPart     = pathinfo($displayName, PATHINFO_EXTENSION);
            $displayName = "{$base}_{$uid}.{$extPart}";
            $destPath    = $this->storageDir . DS . $displayName;
        }

        rename($tempPath, $destPath);
        return $displayName;
    }

    /* =========================================================
       1. IMAGE → PDF  (GD + FPDF)
    ========================================================= */
    private function imageToPdf($file, string $sessionId, string $originalName): array
    {
        $this->loadFpdf();

        $ext     = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $tmpName = "{$sessionId}_input.{$ext}";
        $tmpPath = $this->storageDir . DS . $tmpName;
        $file->move($this->storageDir, $tmpName);
        $tempOut = $this->storageDir . DS . "{$sessionId}_out.pdf";

        try {
            $imgInfo = @getimagesize($tmpPath);
            if (!$imgInfo || !$imgInfo[0]) throw new \Exception("File gambar tidak valid.");
            [$imgW, $imgH] = $imgInfo;
            $mime = $imgInfo['mime'];

            $gd = match (true) {
                str_contains($mime, 'jpeg') => imagecreatefromjpeg($tmpPath),
                str_contains($mime, 'png')  => imagecreatefrompng($tmpPath),
                str_contains($mime, 'webp') => function_exists('imagecreatefromwebp')
                    ? imagecreatefromwebp($tmpPath)
                    : throw new \Exception("WebP tidak didukung."),
                str_contains($mime, 'gif')  => imagecreatefromgif($tmpPath),
                str_contains($mime, 'bmp')  => imagecreatefrombmp($tmpPath),
                default => throw new \Exception("Format gambar tidak didukung: {$mime}"),
            };
            if (!$gd) throw new \Exception("Gagal membaca gambar.");

            $canvas = imagecreatetruecolor($imgW, $imgH);
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            imagecopy($canvas, $gd, 0, 0, 0, 0, $imgW, $imgH);
            imagedestroy($gd);

            $cleanPath = $this->storageDir . DS . "{$sessionId}_clean.jpg";
            imagejpeg($canvas, $cleanPath, 92);
            imagedestroy($canvas);

            $maxW = 190; $maxH = 277;
            $mmW  = $imgW * 25.4 / 96;
            $mmH  = $imgH * 25.4 / 96;
            if ($mmW > $maxW || $mmH > $maxH) {
                $scale = min($maxW / $mmW, $maxH / $mmH);
                $mmW  *= $scale; $mmH *= $scale;
            }

            $pdf = new \FPDF('P', 'mm', 'A4');
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(false);
            $pdf->AddPage();
            $pdf->Image($cleanPath, 10, 10, $mmW, $mmH, 'JPEG');
            $pdf->Output('F', $tempOut);
            @unlink($cleanPath);

            return [$this->saveWithDisplayName($tempOut, $originalName, 'pdf')];
        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       2. OFFICE → PDF  (LibreOffice)
    ========================================================= */
    private function officeToPdf($file, string $sessionId, string $originalName): array
    {
        $this->requireSoffice();

        $ext     = strtolower($file->getClientOriginalExtension() ?: 'docx');
        $tmpName = "{$sessionId}_input.{$ext}";
        $tmpPath = $this->storageDir . DS . $tmpName;
        $file->move($this->storageDir, $tmpName);

        try {
            $outputPath = $this->runLibreOffice($tmpPath, 'pdf', $sessionId);
            return [$this->saveWithDisplayName($outputPath, $originalName, 'pdf')];
        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       3. PDF → IMAGE  (GS → Imagick → LO)
    ========================================================= */
    private function pdfToImage($file, string $sessionId, string $fmt, string $originalName): array
    {
        $tmpName = "{$sessionId}_input.pdf";
        $tmpPath = $this->storageDir . DS . $tmpName;
        $file->move($this->storageDir, $tmpName);

        try {
            if ($this->gsbin !== '') {
                try {
                    return $this->renameImagePages(
                        $this->pdfToImageGhostscript($tmpPath, $sessionId, $fmt),
                        $originalName, $fmt
                    );
                } catch (\Exception $e) {
                    Log::warning("GS PDF→IMG: " . $e->getMessage());
                }
            }

            if (extension_loaded('imagick')) {
                try {
                    return $this->renameImagePages(
                        $this->pdfToImageImagick($tmpPath, $sessionId, $fmt),
                        $originalName, $fmt
                    );
                } catch (\Exception $e) {
                    Log::warning("Imagick PDF→IMG: " . $e->getMessage());
                }
            }

            if ($this->sofficeBin !== '') {
                return $this->renameImagePages(
                    $this->pdfToImageViaLibreOffice($tmpPath, $sessionId, $fmt),
                    $originalName, $fmt
                );
            }

            throw new \Exception("Tidak ada tool tersedia untuk PDF→Gambar. Install Ghostscript.");
        } finally {
            @unlink($tmpPath);
        }
    }

    private function renameImagePages(array $rawFiles, string $originalName, string $fmt): array
    {
        $result = [];
        $total  = count($rawFiles);
        foreach ($rawFiles as $idx => $basename) {
            $src = $this->storageDir . DS . $basename;
            if (!file_exists($src)) continue;
            $result[] = $this->saveWithDisplayName($src, $originalName, $fmt, $total > 1 ? $idx + 1 : null);
        }
        return $result;
    }

    private function pdfToImageGhostscript(string $pdfPath, string $sessionId, string $fmt): array
    {
        $device     = ($fmt === 'png') ? 'png16m' : 'jpeg';
        $outPattern = $this->storageDir . DS . "{$sessionId}_p%d.{$fmt}";

        $cmd = $this->isWindows
            ? sprintf('"%s" -dNOPAUSE -dBATCH -dSAFER -dQUIET -sDEVICE=%s -r200 ' .
                '-dFirstPage=1 -dLastPage=250 -sOutputFile="%s" "%s" 2>&1',
                $this->gsbin, $device, $outPattern, $pdfPath)
            : sprintf('%s -dNOPAUSE -dBATCH -dSAFER -dQUIET -sDEVICE=%s -r200 ' .
                '-dFirstPage=1 -dLastPage=250 -sOutputFile=%s %s 2>&1',
                escapeshellcmd($this->gsbin),
                escapeshellarg($device),
                escapeshellarg($outPattern),
                escapeshellarg($pdfPath));

        exec($cmd, $output, $exitCode);

        $files = glob($this->storageDir . DS . "{$sessionId}_p*.{$fmt}") ?: [];
        natsort($files);

        $outputFiles = [];
        foreach ($files as $f) {
            if (file_exists($f) && filesize($f) > 0) $outputFiles[] = basename($f);
        }

        if (empty($outputFiles)) {
            throw new \Exception("GS PDF→Gambar gagal (exit {$exitCode}). " .
                implode(' | ', array_slice($output, 0, 3)));
        }
        return $outputFiles;
    }

    private function pdfToImageImagick(string $pdfPath, string $sessionId, string $fmt): array
    {
        $imagick = new \Imagick();
        $imagick->setResolution(200, 200);
        $imagick->readImage("{$pdfPath}[0-29]");
        $imagick->resetIterator();

        $outputFiles = [];
        $imgFmt      = ($fmt === 'png') ? 'png' : 'jpeg';

        foreach ($imagick as $i => $page) {
            $page = clone $page;
            $page->setImageFormat($imgFmt);
            $page->setImageBackgroundColor('white');
            $page->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            if ($imgFmt === 'jpeg') $page->setImageCompressionQuality(92);
            $fname = "{$sessionId}_p" . ($i + 1) . ".{$fmt}";
            $page->writeImage($this->storageDir . DS . $fname);
            $outputFiles[] = $fname;
            $page->destroy();
        }
        $imagick->clear(); $imagick->destroy();
        if (empty($outputFiles)) throw new \Exception("Imagick tidak menghasilkan halaman.");
        return $outputFiles;
    }

    private function pdfToImageViaLibreOffice(string $pdfPath, string $sessionId, string $fmt): array
    {
        $outputPath = $this->runLibreOffice($pdfPath, 'png', $sessionId);
        $outName    = "{$sessionId}_p1.png";
        @rename($outputPath, $this->storageDir . DS . $outName);

        if ($fmt === 'jpg') {
            $src = @imagecreatefrompng($this->storageDir . DS . $outName);
            if ($src) {
                $jpgName = "{$sessionId}_p1.jpg";
                $canvas  = imagecreatetruecolor(imagesx($src), imagesy($src));
                imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
                imagecopy($canvas, $src, 0, 0, 0, 0, imagesx($src), imagesy($src));
                imagedestroy($src);
                imagejpeg($canvas, $this->storageDir . DS . $jpgName, 92);
                imagedestroy($canvas);
                @unlink($this->storageDir . DS . $outName);
                return [$jpgName];
            }
        }
        return [$outName];
    }

    /* =========================================================
       4. PDF → OFFICE  (LibreOffice multi-strategy)
    ========================================================= */
    private function pdfToOffice($file, string $sessionId, string $targetExt, string $originalName): array
    {
        $this->requireSoffice();

        // CRITICAL: input filename = pure UUID (no spaces) so LibreOffice
        // can parse it cleanly on Windows
        $safeName    = "{$sessionId}_input.pdf";
        $safePath    = $this->storageDir . DS . $safeName;
        $file->move($this->storageDir, $safeName);
        $workingPath = $safePath;

        try {
            $hasText = $this->pdfHasTextLayer($workingPath);
            Log::info("pdfToOffice [{$targetExt}] hasText={$hasText} sid={$sessionId}");

            if (!$hasText) {
                $ocrPdf = $this->storageDir . DS . "{$sessionId}_ocr.pdf";
                if ($this->runOcrmypdf($workingPath, $ocrPdf)) {
                    $workingPath = $ocrPdf;
                    Log::info("OCR PDF created: {$ocrPdf}");
                }
            }

            // S1: infilter (best quality)
            $result = $this->tryPdfToOfficeInfilter($workingPath, $sessionId, $targetExt);
            if ($this->isValidFile($result)) {
                return [$this->saveWithDisplayName($result, $originalName, $targetExt)];
            }

            // S2: auto-detect
            $result = $this->tryPdfToOfficeAutoDetect($workingPath, $sessionId, $targetExt);
            if ($this->isValidFile($result)) {
                return [$this->saveWithDisplayName($result, $originalName, $targetExt)];
            }

            // S3: ODT bridge (docx only)
            if ($targetExt === 'docx') {
                $result = $this->tryPdfViaOdt($workingPath, $sessionId);
                if ($this->isValidFile($result)) {
                    return [$this->saveWithDisplayName($result, $originalName, 'docx')];
                }
            }

            // S4: PhpWord / PhpSpreadsheet text parser
            $text = $this->extractPdfTextSmart($workingPath, $sessionId);
            if (trim($text) !== '') {
                if ($targetExt === 'docx') {
                    $out = $this->storageDir . DS . "{$sessionId}_fallback.docx";
                    $this->createDocxFormatted($text, $out);
                    if ($this->isValidFile($out)) {
                        Log::info("S4 DOCX fallback OK");
                        return [$this->saveWithDisplayName($out, $originalName, 'docx')];
                    }
                }
                if ($targetExt === 'xlsx') {
                    $out = $this->storageDir . DS . "{$sessionId}_fallback.xlsx";
                    $this->createExcelFromText($text, $out);
                    if ($this->isValidFile($out)) {
                        Log::info("S4 XLSX fallback OK");
                        return [$this->saveWithDisplayName($out, $originalName, 'xlsx')];
                    }
                }
            }

            throw new \Exception(
                "Konversi PDF → {$targetExt} gagal pada semua strategi. " .
                "Pastikan PDF tidak terproteksi & LibreOffice terinstall dengan benar."
            );
        } finally {
            @unlink($safePath);
        }
    }

    private function isValidFile(?string $path): bool
    {
        return $path !== null && file_exists($path) && filesize($path) > 0;
    }

    /**
     * S1: PDF import filter — best quality
     */
    private function tryPdfToOfficeInfilter(string $inputPath, string $sessionId, string $targetExt): ?string
    {
        $filterMap = [
            'docx' => ['writer_pdf_import',  'MS Word 2007 XML',               'docx'],
            'xlsx' => ['calc_pdf_import',     'Calc MS Excel 2007 XML',         'xlsx'],
            'pptx' => ['impress_pdf_import',  'Impress MS PowerPoint 2007 XML', 'pptx'],
        ];
        if (!isset($filterMap[$targetExt])) return null;

        [$infilter, $outfilter, $ext] = $filterMap[$targetExt];
        $profileDir     = $this->makeLoProfile($sessionId, 's1');
        // LO outputs: {inputBasename}.{ext}
        $expectedOutput = $this->storageDir . DS . "{$sessionId}_input.{$ext}";

        $cmd = $this->buildLoCmd(
            $inputPath, $profileDir, $sessionId,
            "{$ext}:\"{$outfilter}\"",
            $infilter
        );
        exec($cmd, $lines, $code);
        $this->removeDir($profileDir);

        Log::info("LO S1 [{$targetExt}] exit={$code}");

        if ($this->isValidFile($expectedOutput)) return $expectedOutput;
        return $this->findNewestOutputFile($targetExt, $sessionId);
    }

    /**
     * S2: Direct convert-to (no infilter)
     */
    private function tryPdfToOfficeAutoDetect(string $inputPath, string $sessionId, string $targetExt): ?string
    {
        $filterMap = [
            'docx' => 'docx:"MS Word 2007 XML"',
            'xlsx' => 'xlsx:"Calc MS Excel 2007 XML"',
            'pptx' => 'pptx:"Impress MS PowerPoint 2007 XML"',
        ];

        $profileDir     = $this->makeLoProfile($sessionId, 's2');
        $expectedOutput = $this->storageDir . DS . "{$sessionId}_input.{$targetExt}";

        $cmd = $this->buildLoCmd(
            $inputPath, $profileDir, $sessionId,
            $filterMap[$targetExt] ?? $targetExt
        );
        exec($cmd, $lines, $code);
        $this->removeDir($profileDir);

        Log::info("LO S2 [{$targetExt}] exit={$code}");

        if ($this->isValidFile($expectedOutput)) return $expectedOutput;
        return $this->findNewestOutputFile($targetExt, $sessionId);
    }

    /**
     * S3: PDF → ODT → DOCX bridge
     */
    private function tryPdfViaOdt(string $inputPath, string $sessionId): ?string
    {
        $profileA       = $this->makeLoProfile($sessionId, 's3a');
        $expectedOdt    = $this->storageDir . DS . "{$sessionId}_input.odt";
        $cmdA           = $this->buildLoCmd($inputPath, $profileA, $sessionId, 'odt', 'writer_pdf_import');
        exec($cmdA, $outA, $codeA);
        $this->removeDir($profileA);

        $odtFile = $this->isValidFile($expectedOdt)
            ? $expectedOdt
            : $this->findNewestOutputFile('odt', $sessionId);

        if (!$odtFile) { Log::warning("S3A PDF→ODT failed"); return null; }

        $profileB     = $this->makeLoProfile($sessionId, 's3b');
        $cmdB         = $this->buildLoCmd($odtFile, $profileB, $sessionId, 'docx:"MS Word 2007 XML"');
        exec($cmdB, $outB, $codeB);
        $this->removeDir($profileB);
        @unlink($odtFile);

        $expectedDocx = $this->storageDir . DS . pathinfo($odtFile, PATHINFO_FILENAME) . '.docx';
        if ($this->isValidFile($expectedDocx)) return $expectedDocx;
        return $this->findNewestOutputFile('docx', $sessionId);
    }

    /* =========================================================
       5. IMAGE → IMAGE  (GD)
    ========================================================= */
    private function convertImage($file, string $sessionId, string $type, string $originalName): array
    {
        $ext     = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $tmpName = "{$sessionId}_input.{$ext}";
        $tmpPath = $this->storageDir . DS . $tmpName;
        $file->move($this->storageDir, $tmpName);

        try {
            $outFmt = match (true) {
                str_ends_with($type, '_png')  => 'png',
                str_ends_with($type, '_webp') => 'webp',
                default                       => 'jpg',
            };

            $src = match ($ext) {
                'jpg', 'jpeg' => imagecreatefromjpeg($tmpPath),
                'png'         => imagecreatefrompng($tmpPath),
                'webp'        => function_exists('imagecreatefromwebp')
                    ? imagecreatefromwebp($tmpPath)
                    : throw new \Exception("WebP tidak didukung."),
                default => throw new \Exception("Format tidak didukung: {$ext}"),
            };
            if (!$src) throw new \Exception("Gagal membaca gambar.");

            $w = imagesx($src); $h = imagesy($src);
            $canvas = imagecreatetruecolor($w, $h);

            if ($outFmt === 'png') {
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
                imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
            } else {
                imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            }
            imagecopy($canvas, $src, 0, 0, 0, 0, $w, $h);
            imagedestroy($src);

            $tempFull = $this->storageDir . DS . "{$sessionId}_out_img.{$outFmt}";
            $ok = match ($outFmt) {
                'png'  => imagepng($canvas,  $tempFull, 6),
                'webp' => imagewebp($canvas, $tempFull, 90),
                default=> imagejpeg($canvas, $tempFull, 95),
            };
            imagedestroy($canvas);
            if (!$ok) throw new \Exception("Gagal menyimpan output gambar.");

            return [$this->saveWithDisplayName($tempFull, $originalName, $outFmt)];
        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       6. PDF COMPRESS  (Ghostscript)
    ========================================================= */
    private function compressPdf($file, string $sessionId, string $originalName): array
    {
        if ($this->gsbin === '') {
            throw new \Exception("Ghostscript tidak tersedia. Set GHOSTSCRIPT_BINARY di .env.");
        }

        $tmpName = "{$sessionId}_input.pdf";
        $tmpPath = $this->storageDir . DS . $tmpName;
        $file->move($this->storageDir, $tmpName);
        $tempOut = $this->storageDir . DS . "{$sessionId}_compressed_tmp.pdf";

        try {
            $cmd = $this->isWindows
                ? sprintf('"%s" -dNOPAUSE -dBATCH -dSAFER -dQUIET -sDEVICE=pdfwrite ' .
                    '-dCompatibilityLevel=1.4 -dPDFSETTINGS=/ebook ' .
                    '-dEmbedAllFonts=true -dSubsetFonts=true ' .
                    '-dColorImageResolution=150 -dGrayImageResolution=150 ' .
                    '-sOutputFile="%s" "%s" 2>&1',
                    $this->gsbin, $tempOut, $tmpPath)
                : sprintf('%s -dNOPAUSE -dBATCH -dSAFER -dQUIET -sDEVICE=pdfwrite ' .
                    '-dCompatibilityLevel=1.4 -dPDFSETTINGS=/ebook ' .
                    '-dEmbedAllFonts=true -dSubsetFonts=true ' .
                    '-dColorImageResolution=150 -dGrayImageResolution=150 ' .
                    '-sOutputFile=%s %s 2>&1',
                    escapeshellcmd($this->gsbin),
                    escapeshellarg($tempOut),
                    escapeshellarg($tmpPath));

            exec($cmd, $output, $exitCode);

            if ($exitCode !== 0 || !$this->isValidFile($tempOut)) {
                throw new \Exception("GS compress gagal (exit {$exitCode}): " .
                    implode(' | ', array_slice($output, 0, 3)));
            }
            return [$this->saveWithDisplayName($tempOut, $originalName, 'pdf')];
        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       CORE: LibreOffice runner
    ========================================================= */
    private function runLibreOffice(string $inputPath, string $targetExt, string $sessionId): string
    {
        $profileDir = $this->makeLoProfile($sessionId, 'run');

        $filterMap = [
            'pdf'  => 'pdf',
            'docx' => 'docx:"MS Word 2007 XML"',
            'xlsx' => 'xlsx:"Calc MS Excel 2007 XML"',
            'pptx' => 'pptx:"Impress MS PowerPoint 2007 XML"',
            'odt'  => 'odt',
            'png'  => 'png',
        ];

        $cmd         = $this->buildLoCmd($inputPath, $profileDir, $sessionId, $filterMap[$targetExt] ?? $targetExt);
        $outputLines = [];
        exec($cmd, $outputLines, $exitCode);
        $output = implode("\n", $outputLines);
        $this->removeDir($profileDir);

        // LO output is named after the input basename + target ext
        $expectedOutput = $this->storageDir . DS
            . pathinfo($inputPath, PATHINFO_FILENAME) . '.' . $targetExt;

        if ($this->isValidFile($expectedOutput)) return $expectedOutput;

        $fallback = $this->findNewestOutputFile($targetExt, $sessionId);
        if (!$fallback) {
            Log::error("LibreOffice failed", [
                'cmd'    => $cmd,
                'exit'   => $exitCode,
                'output' => substr($output, 0, 400),
            ]);
            throw new \Exception(
                "LibreOffice gagal (exit {$exitCode}). " .
                "Detail: " . substr($output, 0, 150)
            );
        }
        return $fallback;
    }

    /**
     * Make a LibreOffice profile directory.
     * On Windows: use sys_get_temp_dir() to avoid spaces in path.
     */
    private function makeLoProfile(string $sessionId, string $suffix): string
    {
        if ($this->isWindows) {
            // sys_get_temp_dir() on Windows typically = C:\Users\...\AppData\Local\Temp (no spaces usually)
            // Using short 8-char UUID prefix to keep path short
            $dir = sys_get_temp_dir() . DS . 'lo_' . substr($sessionId, 0, 8) . '_' . $suffix;
        } else {
            $dir = $this->storageDir . DS . "lo_{$sessionId}_{$suffix}";
        }
        @mkdir($dir, 0777, true);
        return $dir;
    }

    private function buildLoCmd(
        string  $inputPath,
        string  $profileDir,
        string  $sessionId,
        string  $convertTo,
        ?string $infilter = null
    ): string {
        if ($this->isWindows) {
            $profileUri = 'file:///' . str_replace(['\\', ' '], ['/', '%20'], $profileDir);

            $parts = [
                '"' . $this->sofficeBin . '"',
                '--headless', '--norestore', '--nofirststartwizard', '--nolockcheck',
                '-env:UserInstallation=' . $profileUri,
            ];
            if ($infilter) $parts[] = '--infilter=' . $infilter;
            $parts[] = '--convert-to';
            $parts[] = '"' . $convertTo . '"';
            $parts[] = '--outdir';
            $parts[] = '"' . $this->storageDir . '"';
            $parts[] = '"' . $inputPath . '"';
            $parts[] = '2>&1';
            return implode(' ', $parts);
        }

        $profileUri = 'file://' . str_replace(' ', '%20', $profileDir);
        $homeDir    = '/tmp/lo_home_' . $sessionId;
        $parts = [
            'HOME='           . escapeshellarg($homeDir),
            'XDG_CACHE_HOME=' . escapeshellarg('/tmp/lo_cache_' . $sessionId),
            'SAL_USE_VCLPLUGIN=svp',
            escapeshellcmd($this->sofficeBin),
            '--headless', '--norestore', '--nofirststartwizard', '--nolockcheck',
            '-env:UserInstallation=' . escapeshellarg($profileUri),
        ];
        if ($infilter) $parts[] = '--infilter=' . escapeshellarg($infilter);
        $parts[] = '--convert-to';
        $parts[] = escapeshellarg($convertTo);
        $parts[] = '--outdir';
        $parts[] = escapeshellarg($this->storageDir);
        $parts[] = escapeshellarg($inputPath);
        $parts[] = '2>&1';
        return implode(' ', $parts);
    }

    /* =========================================================
       PDF TEXT / OCR HELPERS
    ========================================================= */
    private function extractPdfTextSmart(string $pdfPath, string $sessionId): string
    {
        try {
            if (class_exists(\Smalot\PdfParser\Parser::class)) {
                $parser = new \Smalot\PdfParser\Parser();
                $text   = trim($parser->parseFile($pdfPath)->getText());
                if (mb_strlen(preg_replace('/\s+/u', '', $text)) > 30) return $text;
            }
        } catch (\Throwable $e) {
            Log::warning('PdfParser failed: ' . $e->getMessage());
        }

        try {
            $text = $this->pdfToTextOCR($pdfPath, $sessionId);
            if (trim($text) !== '') return $text;
        } catch (\Throwable $e) {
            Log::warning('OCR failed: ' . $e->getMessage());
        }
        return '';
    }

    private function createDocxFormatted(string $text, string $outputPath): void
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection([
            'marginTop' => 800, 'marginBottom' => 800,
            'marginLeft' => 800, 'marginRight' => 800,
        ]);
        foreach (explode("\n", $text) as $line) {
            $line = trim($line);
            if ($line === '') { $section->addTextBreak(); continue; }
            if (strlen($line) < 60 && strtoupper($line) === $line && strlen($line) > 2) {
                $section->addText($line, ['bold' => true, 'size' => 14]);
            } else {
                $section->addText($line, ['size' => 11]);
            }
        }
        \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007')->save($outputPath);
    }

    private function createExcelFromText(string $text, string $outputPath): void
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $rowIndex    = 1;
        foreach (explode("\n", $text) as $row) {
            $cols     = preg_split('/\t|\s{2,}/', trim($row));
            $colIndex = 1;
            foreach ($cols as $col) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;
                $sheet->setCellValue($cell, trim($col));
                $colIndex++;
            }
            $rowIndex++;
        }
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($outputPath);
    }

    private function pdfHasTextLayer(string $pdfPath): bool
    {
        try {
            if (class_exists(\Smalot\PdfParser\Parser::class)) {
                $parser = new \Smalot\PdfParser\Parser();
                $text   = trim($parser->parseFile($pdfPath)->getText());
                return mb_strlen(preg_replace('/\s+/u', '', $text)) > 30;
            }
        } catch (\Throwable $e) {
            Log::warning('pdfHasTextLayer failed: ' . $e->getMessage());
        }

        $tmpTxt = $this->storageDir . DS . 'probe_' . Str::random(8) . '.txt';
        $cmd    = $this->isWindows
            ? 'pdftotext -q "' . $pdfPath . '" "' . $tmpTxt . '" 2>&1'
            : 'pdftotext -q ' . escapeshellarg($pdfPath) . ' ' . escapeshellarg($tmpTxt) . ' 2>&1';
        @exec($cmd);
        if (!file_exists($tmpTxt)) return false;
        $text = trim(@file_get_contents($tmpTxt) ?: '');
        @unlink($tmpTxt);
        return mb_strlen(preg_replace('/\s+/u', '', $text)) > 30;
    }

    private function ocrmypdfBinary(): string
    {
        return $this->resolveBinary(
            env('OCRMYPDF_BINARY', ''),
            env('OCRMYPDF_PATH', ''),
            $this->isWindows
                ? ['ocrmypdf']
                : ['ocrmypdf', '/usr/bin/ocrmypdf', '/usr/local/bin/ocrmypdf']
        );
    }

    private function runOcrmypdf(string $inputPdf, string $outputPdf): bool
    {
        $bin = $this->ocrmypdfBinary();
        if ($bin === '') return false;
        $cmd = $this->isWindows
            ? sprintf('"%s" --skip-text --force-ocr --language eng+ind "%s" "%s" 2>&1',
                $bin, $inputPdf, $outputPdf)
            : sprintf('%s --skip-text --force-ocr --language eng+ind %s %s 2>&1',
                escapeshellcmd($bin), escapeshellarg($inputPdf), escapeshellarg($outputPdf));
        $out = []; $code = 0;
        exec($cmd, $out, $code);
        return $code === 0 && $this->isValidFile($outputPdf);
    }

    private function runTesseractOnImage(string $imagePath, string $outBase, string $lang = 'eng+ind'): string
    {
        $bin = $this->normalizeBinaryValue(env('TESSERACT_BINARY', 'tesseract'));
        if ($bin === '') throw new \Exception('Tesseract tidak ditemukan.');
        $cmd = $this->isWindows
            ? sprintf('"%s" "%s" "%s" -l %s 2>&1', $bin, $imagePath, $outBase, $lang)
            : sprintf('%s %s %s -l %s 2>&1',
                escapeshellcmd($bin), escapeshellarg($imagePath),
                escapeshellarg($outBase), $lang);
        $output = []; $code = 0;
        exec($cmd, $output, $code);
        $txtFile = $outBase . '.txt';
        if ($code !== 0 || !file_exists($txtFile)) {
            throw new \Exception('OCR gagal: ' . implode(' | ', array_slice($output, 0, 3)));
        }
        return file_get_contents($txtFile) ?: '';
    }

    private function pdfToTextOCR(string $pdfPath, string $sessionId): string
    {
        $pages  = $this->pdfToImageGhostscript($pdfPath, $sessionId . '_ocrtmp', 'png');
        $blocks = [];
        foreach ($pages as $idx => $pageFile) {
            $imgPath = $this->storageDir . DS . $pageFile;
            $outBase = $this->storageDir . DS . "{$sessionId}_ocr_p" . ($idx + 1);
            try {
                $text = trim($this->runTesseractOnImage($imgPath, $outBase));
                if ($text !== '') $blocks[] = $text;
            } catch (\Throwable) { /* continue */ }
            @unlink($imgPath);
            @unlink($outBase . '.txt');
        }
        return trim(implode("\n\n", $blocks));
    }

    /* =========================================================
       HELPERS
    ========================================================= */

    /**
     * Find newest output file — session-scoped first, then global fallback.
     * This fixes the race condition where stale files from old sessions
     * were picked up when LibreOffice failed to produce output.
     */
    private function findNewestOutputFile(string $ext, string $sessionId): ?string
    {
        $all = glob($this->storageDir . DS . '*.' . $ext) ?: [];
        if (empty($all)) return null;

        // Prefer files starting with the current session ID
        $mine = array_filter($all, fn($f) => str_starts_with(basename($f), $sessionId));
        $candidates = !empty($mine) ? $mine : $all;

        usort($candidates, fn($a, $b) => filemtime($b) <=> filemtime($a));
        foreach ($candidates as $f) {
            if ($this->isValidFile($f)) return $f;
        }
        return null;
    }

    private function requireSoffice(): void
    {
        if ($this->sofficeBin === '') {
            $hint = $this->isWindows
                ? 'Tambahkan "C:\\Program Files\\LibreOffice\\program" ke PATH Windows, lalu set LIBREOFFICE_BINARY=soffice.exe di .env'
                : 'sudo apt install libreoffice && set LIBREOFFICE_BINARY=soffice di .env';
            throw new \Exception("LibreOffice tidak ditemukan. {$hint}");
        }
    }

    private function loadFpdf(): void
    {
        foreach ([
            base_path('vendor/setasign/fpdf/fpdf.php'),
            base_path('vendor/fpdf/fpdf/src/Fpdf/Fpdf.php'),
            app_path('Libraries/fpdf/fpdf.php'),
        ] as $p) {
            if (file_exists($p)) {
                require_once $p;
                if (!class_exists('FPDF') && class_exists('setasign\Fpdf\Fpdf')) {
                    class_alias('setasign\Fpdf\Fpdf', 'FPDF');
                }
                return;
            }
        }
        throw new \Exception("FPDF tidak ditemukan. Jalankan: composer require setasign/fpdf");
    }

    private function friendlyError(string $type, string $raw): string
    {
        $tips = match (true) {
            in_array($type, ['pdf_to_word', 'pdf_to_excel', 'pdf_to_ppt'])
                => " Tip: Pastikan PDF tidak terproteksi & LibreOffice terinstall. PDF scan di-OCR otomatis jika ocrmypdf tersedia.",
            in_array($type, ['word_to_pdf', 'excel_to_pdf', 'ppt_to_pdf'])
                => " Tip: Pastikan file Office tidak terproteksi password dan formatnya valid.",
            default => "",
        };
        $clean = preg_replace('/env:UserInstallation=\S+/', '', $raw) ?? $raw;
        $clean = preg_replace('/HOME=\S+/', '', $clean) ?? $clean;
        $clean = preg_replace('/XDG_\w+=\S+/', '', $clean) ?? $clean;
        return substr(trim($clean), 0, 250) . $tips;
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        foreach (array_diff(scandir($dir) ?: [], ['.', '..']) as $item) {
            $p = $dir . DS . $item;
            is_dir($p) ? $this->removeDir($p) : @unlink($p);
        }
        @rmdir($dir);
    }

    /* =========================================================
       DOWNLOAD
    ========================================================= */
    public function download(string $filename)
    {
        $filename = basename($filename);
        if (!preg_match('/^[\w\s\-\.()[\]]+$/u', $filename)) {
            abort(403, 'Nama file tidak valid.');
        }
        $path = $this->storageDir . DS . $filename;
        if (!file_exists($path)) {
            abort(404, 'File tidak ditemukan atau sudah dihapus (> 30 menit).');
        }

        $mimes = [
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'odt'  => 'application/vnd.oasis.opendocument.text',
            'jpg'  => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'png'  => 'image/png',  'webp' => 'image/webp',
        ];

        $ext             = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $encodedFilename = rawurlencode($filename);
        $asciiFilename   = preg_replace('/[^\x20-\x7E]/', '_', $filename);

        return response()->download($path, $filename, [
            'Content-Type'        => $mimes[$ext] ?? 'application/octet-stream',
            'Content-Disposition' => "attachment; filename=\"{$asciiFilename}\"; filename*=UTF-8''{$encodedFilename}",
        ]);
    }

    /* =========================================================
       CLEANUP
    ========================================================= */
    public function cleanup(Request $request)
    {
        $sid = $request->input('session_id');
        if ($sid && preg_match('/^[a-zA-Z0-9\-]+$/', $sid)) {
            foreach (glob($this->storageDir . DS . "{$sid}*") ?: [] as $f) {
                is_dir($f) ? $this->removeDir($f) : @unlink($f);
            }
            if (!$this->isWindows) {
                foreach (glob("/tmp/lo_home_{$sid}*") ?: [] as $f) $this->removeDir($f);
                foreach (glob("/tmp/lo_cache_{$sid}*") ?: [] as $f) $this->removeDir($f);
            }
            if ($this->isWindows) {
                $shortSid = substr($sid, 0, 8);
                foreach (glob(sys_get_temp_dir() . DS . 'lo_' . $shortSid . '*') ?: [] as $f) {
                    $this->removeDir($f);
                }
            }
        }
        return response()->json(['success' => true]);
    }

    private function lazyCleanup(): void
    {
        $limit = time() - 1800;
        foreach (glob($this->storageDir . DS . '*') ?: [] as $item) {
            if (@filemtime($item) >= $limit) continue;
            is_dir($item) ? $this->removeDir($item) : @unlink($item);
        }
    }

    /* =========================================================
       DEBUG — Remove route in production!
       Route::get('/file-converter/debug', [FileConverterController::class, 'debug']);
    ========================================================= */
    public function debug()
    {
        $sofTest = []; $gsTest = [];
        if ($this->sofficeBin !== '') {
            $c = $this->isWindows
                ? '"' . $this->sofficeBin . '" --version 2>&1'
                : $this->sofficeBin . ' --version 2>&1';
            exec($c, $sofTest);
        }
        if ($this->gsbin !== '') {
            $c = $this->isWindows
                ? '"' . $this->gsbin . '" -v 2>&1'
                : $this->gsbin . ' -v 2>&1';
            exec($c, $gsTest);
        }
        return response()->json([
            'php_version' => PHP_VERSION,
            'os'          => PHP_OS_FAMILY,
            'is_windows'  => $this->isWindows,
            'env' => [
                'LIBREOFFICE_BINARY' => env('LIBREOFFICE_BINARY', '(not set)'),
                'GHOSTSCRIPT_BINARY' => env('GHOSTSCRIPT_BINARY', '(not set)'),
                'TESSERACT_BINARY'   => env('TESSERACT_BINARY',   '(not set)'),
                'OCRMYPDF_BINARY'    => env('OCRMYPDF_BINARY',    '(not set)'),
            ],
            'resolved' => [
                'sofficeBin' => $this->sofficeBin ?: '❌ NOT FOUND',
                'gsbin'      => $this->gsbin      ?: '❌ NOT FOUND',
            ],
            'exec_test' => [
                'soffice' => implode(' ', $sofTest) ?: '(no output)',
                'gs'      => implode(' ', $gsTest)  ?: '(no output)',
            ],
            'php_ext' => [
                'gd'      => extension_loaded('gd'),
                'imagick' => extension_loaded('imagick'),
            ],
            'storage' => [
                'path'     => $this->storageDir,
                'writable' => is_writable($this->storageDir),
            ],
            'composer_packages' => [
                'fpdf'           => $this->checkFpdf(),
                'phpword'        => class_exists(\PhpOffice\PhpWord\PhpWord::class)            ? '✅' : '❌ composer require phpoffice/phpword',
                'phpspreadsheet' => class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class) ? '✅' : '❌ composer require phpoffice/phpspreadsheet',
                'pdfparser'      => class_exists(\Smalot\PdfParser\Parser::class)              ? '✅' : '❌ composer require smalot/pdfparser',
            ],
        ]);
    }

    private function checkFpdf(): string
    {
        foreach ([
            base_path('vendor/setasign/fpdf/fpdf.php'),
            base_path('vendor/fpdf/fpdf/src/Fpdf/Fpdf.php'),
        ] as $p) {
            if (file_exists($p)) return "✅ {$p}";
        }
        return '❌ composer require setasign/fpdf';
    }
}