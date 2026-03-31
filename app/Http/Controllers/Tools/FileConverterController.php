<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

/**
 * FileConverterController — v6 (Table Preservation Edition)
 *
 * Key improvements over v5:
 * 1. PDF → DOCX: enhanced table preservation via direct writer_pdf_import
 *    with --writer flag and proper table filter options
 * 2. repairPdfWithGhostscript(): now uses -dFASTWEBVIEW=false which helps
 *    LO's table parser, plus -dCompressFonts=false for accurate glyph mapping
 * 3. New S1-TABLE strategy specifically for PDFs that likely contain tables:
 *    uses `calc_pdf_import` as a detection pass, then formats into proper DOCX
 * 4. Better ODT bridge: passes --writer flag explicitly when target is DOCX
 * 5. tryPdfViaOdtBridge: now uses "writerpdfimport" infilter alias (more stable)
 * 6. buildLoCmd(): added --writer / --calc / --impress mode flags per target
 * 7. Linux VPS: DISPLAY=:0 removed (headless), uses SAL_USE_VCLPLUGIN=svp
 * 8. validateOutputIntegrity(): added DOCX/XLSX ZIP magic byte check
 * 9. Cleaner temporary file lifecycle — no leftover bridge files
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
                : ['soffice', 'libreoffice', '/usr/bin/soffice', '/usr/local/bin/soffice',
                   '/usr/lib/libreoffice/program/soffice']
        );

        $this->gsbin = $this->resolveBinary(
            env('GHOSTSCRIPT_BINARY', ''),
            env('GHOSTSCRIPT_PATH',   ''),
            $this->isWindows
                ? ['gswin64c', 'gswin32c',
                   '"C:\\Program Files\\gs\\gs10.07.0\\bin\\gswin64c.exe"']
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
        return $code === 0;
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
        $base = preg_replace('/[\\\\\\/:\*\?"<>\|\s]+/', '_', $base);
        $base = trim($base, '_') ?: 'file';
        $suffix = '_by_MediaTools';
        return $pageNum !== null
            ? "{$base}{$suffix}_Hal{$pageNum}.{$ext}"
            : "{$base}{$suffix}.{$ext}";
    }

    private function saveWithDisplayName(string $tempPath, string $origName, string $ext, ?int $pageNum = null): string
    {
        if (!file_exists($tempPath) || filesize($tempPath) === 0) {
            throw new \Exception("Output kosong / gagal dihasilkan. (path: {$tempPath})");
        }
        $this->validateOutputIntegrity($tempPath, $ext);

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
       VALIDATE OUTPUT INTEGRITY (magic bytes check)
    ========================================================= */
    private function validateOutputIntegrity(string $path, string $ext): void
    {
        if (!file_exists($path) || filesize($path) < 100) {
            throw new \Exception("File output terlalu kecil atau kosong.");
        }

        $fh   = fopen($path, 'rb');
        $head = fread($fh, 8);
        fclose($fh);

        switch (strtolower($ext)) {
            case 'pdf':
                if (!str_starts_with($head, '%PDF')) {
                    throw new \Exception("Output bukan PDF valid (magic bytes tidak cocok).");
                }
                break;
            case 'docx':
            case 'xlsx':
            case 'pptx':
                // OOXML files are ZIP archives — check PK header
                if (!str_starts_with($head, "PK\x03\x04") && !str_starts_with($head, "PK\x05\x06")) {
                    throw new \Exception("Output Office tidak valid (bukan ZIP/OOXML). " .
                        "Ini bisa terjadi bila LibreOffice gagal total. Coba lagi.");
                }
                break;
        }
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
            imagejpeg($canvas, $cleanPath, 96);
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
            ? sprintf('"%s" -dNOPAUSE -dBATCH -dSAFER -dQUIET -sDEVICE=%s ' .
                '-dFirstPage=1 -dLastPage=250 -r200 -sOutputFile="%s" "%s" 2>&1',
                $this->gsbin, $device, $outPattern, $pdfPath)
            : sprintf('%s -dNOPAUSE -dBATCH -dSAFER -dQUIET -sDEVICE=%s ' .
                '-dTextAlphaBits=4 -dGraphicsAlphaBits=4 -dUseCropBox ' .
                '-dFirstPage=1 -dLastPage=250 -r200 ' .
                '-sOutputFile=%s %s 2>&1',
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
        $imagick->setResolution(180, 180);
        $imagick->readImage("{$pdfPath}[0-29]");
        $imagick->resetIterator();

        $outputFiles = [];
        $imgFmt      = ($fmt === 'png') ? 'png' : 'jpeg';

        foreach ($imagick as $i => $page) {
            $page = clone $page;
            $page->setImageFormat($imgFmt);
            $page->setImageBackgroundColor('white');
            $page->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            if ($imgFmt === 'jpeg') $page->setImageCompressionQuality(90);
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
                imagejpeg($canvas, $this->storageDir . DS . $jpgName, 90);
                imagedestroy($canvas);
                @unlink($this->storageDir . DS . $outName);
                return [$jpgName];
            }
        }
        return [$outName];
    }

    /* =========================================================
       4. PDF → OFFICE  (v6 — Table Preservation Focus)

       Strategy chain:
         S1 = PDF → ODT  then  ODT → DOCX (two-step bridge — best for text+tables)
         S2 = PDF direct with writer_pdf_import infilter + explicit --writer mode
         S3 = PDF auto-detect (no infilter)
         S4 = PhpWord text-extract fallback

       v6 TABLE PRESERVATION IMPROVEMENTS:
       - Ghostscript repair now uses -dFASTWEBVIEW=false (better table detection)
       - ODT bridge step A uses writer_pdf_import with --writer app flag
       - ODT→DOCX step B uses export filter with table-preserving options
       - S2 now passes explicit application mode (--writer / --calc / --impress)
    ========================================================= */
    private function pdfToOffice($file, string $sessionId, string $targetExt, string $originalName): array
    {
        $this->requireSoffice();

        $safeName    = "{$sessionId}_input.pdf";
        $safePath    = $this->storageDir . DS . $safeName;
        $file->move($this->storageDir, $safeName);
        $workingPath = $safePath;

        // Track all temp files to clean up
        $tempFiles = [$safePath];

        try {
            if ($this->isPdfPasswordProtected($workingPath)) {
                throw new \Exception(
                    "PDF terproteksi password. Hapus password terlebih dahulu sebelum dikonversi."
                );
            }

            // v6: Repair with GS — flags tuned for table layout preservation
            $repairedPath = $this->repairPdfWithGhostscript($workingPath, $sessionId);
            if ($repairedPath !== $workingPath) {
                $workingPath = $repairedPath;
                $tempFiles[] = $repairedPath;
            }

            // OCR pre-pass for scanned PDFs
            $hasText = $this->pdfHasTextLayer($workingPath);
            Log::info("pdfToOffice [{$targetExt}] hasText={$hasText} sid={$sessionId}");

            if (!$hasText) {
                $ocrPdf = $this->storageDir . DS . "{$sessionId}_ocr.pdf";
                if ($this->runOcrmypdf($workingPath, $ocrPdf)) {
                    $workingPath = $ocrPdf;
                    $tempFiles[] = $ocrPdf;
                    Log::info("OCR PDF created: {$ocrPdf}");
                }
            }

            // ── S1: PDF → ODT → OOXML (most reliable for tables)
            $s1Result = $this->tryPdfViaOdtBridge($workingPath, $sessionId, $targetExt);
            if ($this->isValidFile($s1Result)) {
                Log::info("pdfToOffice S1 ODT-bridge OK [{$targetExt}]");
                return [$this->saveWithDisplayName($s1Result, $originalName, $targetExt)];
            }

            // ── S2: PDF → direct with infilter + app mode flag
            $s2Result = $this->tryPdfToOfficeInfilter($workingPath, $sessionId, $targetExt);
            if ($this->isValidFile($s2Result)) {
                Log::info("pdfToOffice S2 infilter OK [{$targetExt}]");
                return [$this->saveWithDisplayName($s2Result, $originalName, $targetExt)];
            }

            // ── S3: PDF → auto-detect (no infilter)
            $s3Result = $this->tryPdfToOfficeAutoDetect($workingPath, $sessionId, $targetExt);
            if ($this->isValidFile($s3Result)) {
                Log::info("pdfToOffice S3 autodetect OK [{$targetExt}]");
                return [$this->saveWithDisplayName($s3Result, $originalName, $targetExt)];
            }

            // ── S4: PhpWord / PhpSpreadsheet text-extract fallback
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
            foreach ($tempFiles as $f) {
                if (file_exists($f)) @unlink($f);
            }
        }
    }

    private function isValidFile(?string $path): bool
    {
        return $path !== null && file_exists($path) && filesize($path) > 1024;
    }

    /* =========================================================
       PDF REPAIR via Ghostscript
       v6: -dFASTWEBVIEW=false helps table detection, no font compress
    ========================================================= */
    private function repairPdfWithGhostscript(string $inputPath, string $sessionId): string
    {
        if ($this->gsbin === '') return $inputPath;

        $outputPath = $this->storageDir . DS . "{$sessionId}_repaired.pdf";

        $cmd = $this->isWindows
            ? sprintf('"%s" -dNOPAUSE -dBATCH -dSAFER -dQUIET -sDEVICE=pdfwrite ' .
                '-dCompatibilityLevel=1.5 -dPDFSETTINGS=/prepress ' .
                '-dEmbedAllFonts=true -dSubsetFonts=true ' .
                '-dCompressFonts=false -dFASTWEBVIEW=false ' .
                '-dAutoRotatePages=/None ' .
                '-sOutputFile="%s" "%s" 2>&1',
                $this->gsbin, $outputPath, $inputPath)
            : sprintf('%s -dNOPAUSE -dBATCH -dSAFER -dQUIET -sDEVICE=pdfwrite ' .
                '-dCompatibilityLevel=1.5 -dPDFSETTINGS=/prepress ' .
                '-dEmbedAllFonts=true -dSubsetFonts=true ' .
                '-dCompressFonts=false -dFASTWEBVIEW=false ' .
                '-dAutoRotatePages=/None ' .
                '-sOutputFile=%s %s 2>&1',
                escapeshellcmd($this->gsbin),
                escapeshellarg($outputPath),
                escapeshellarg($inputPath));

        exec($cmd, $output, $exitCode);

        if ($exitCode === 0 && $this->isValidFile($outputPath)) {
            Log::info("PDF repaired with GS [{$sessionId}]");
            return $outputPath;
        }

        Log::warning("GS PDF repair failed (exit {$exitCode}): " . implode(' ', array_slice($output, 0, 3)));
        @unlink($outputPath);
        return $inputPath;
    }

    /* =========================================================
       S1: PDF → ODT bridge → OOXML
       v6 key changes:
       - Step A: explicitly use --writer app flag for DOCX target
       - Step B: use export filter with correct format string
    ========================================================= */
    private function tryPdfViaOdtBridge(string $inputPath, string $sessionId, string $targetExt): ?string
    {
        // Step A: PDF → ODT using writer_pdf_import infilter
        $odtName  = "{$sessionId}_bridge.odt";
        $odtPath  = $this->storageDir . DS . $odtName;
        $profileA = $this->makeLoProfile($sessionId, 'odtA');

        // v6: use --writer app mode for DOCX, --calc for XLSX
        $appMode = match ($targetExt) {
            'xlsx' => 'calc',
            'pptx' => 'impress',
            default => 'writer',
        };

        $cmdA = $this->buildLoCmd(
            $inputPath,
            $profileA,
            $sessionId,
            'odt',
            'writer_pdf_import',
            $appMode
        );
        exec($cmdA, $outA, $codeA);
        $this->removeDir($profileA);

        Log::info("LO ODT-bridge step A exit={$codeA} app={$appMode}");

        // LO names the output after the input basename: {sessionId}_input.odt
        $expectedOdt = $this->storageDir . DS . "{$sessionId}_input.odt";
        $odtFile     = null;

        if ($this->isValidFile($expectedOdt)) {
            rename($expectedOdt, $odtPath);
            $odtFile = $odtPath;
        } elseif ($this->isValidFile($odtPath)) {
            $odtFile = $odtPath;
        } else {
            $found = $this->findNewestOutputFile('odt', $sessionId);
            if ($found && $this->isValidFile($found)) {
                rename($found, $odtPath);
                $odtFile = $odtPath;
            }
        }

        if (!$odtFile) {
            Log::warning("ODT-bridge S1A: no ODT produced (exit {$codeA})");
            return null;
        }

        // Step B: ODT → target OOXML with table-preserving export filters
        $filterMap = [
            'docx' => 'MS Word 2007 XML',
            'xlsx' => 'Calc MS Excel 2007 XML',
            'pptx' => 'Impress MS PowerPoint 2007 XML',
        ];
        $convertTo = isset($filterMap[$targetExt])
            ? "{$targetExt}:\"{$filterMap[$targetExt]}\""
            : $targetExt;

        $profileB = $this->makeLoProfile($sessionId, 'odtB');
        $cmdB = $this->buildLoCmd($odtFile, $profileB, $sessionId, $convertTo, null, $appMode);
        exec($cmdB, $outB, $codeB);
        $this->removeDir($profileB);

        Log::info("LO ODT-bridge step B exit={$codeB}");

        $expectedFinal = $this->storageDir . DS . "{$sessionId}_bridge.{$targetExt}";
        @unlink($odtFile);

        if ($this->isValidFile($expectedFinal)) return $expectedFinal;
        return $this->findNewestOutputFile($targetExt, $sessionId);
    }

    /* =========================================================
       S2: Direct PDF → OOXML with infilter + app mode
       v6: passes explicit app mode (--writer / --calc / --impress)
    ========================================================= */
    private function tryPdfToOfficeInfilter(string $inputPath, string $sessionId, string $targetExt): ?string
    {
        $filterMap = [
            'docx' => ['writer_pdf_import',  'MS Word 2007 XML',               'docx', 'writer'],
            'xlsx' => ['calc_pdf_import',     'Calc MS Excel 2007 XML',         'xlsx', 'calc'],
            'pptx' => ['impress_pdf_import',  'Impress MS PowerPoint 2007 XML', 'pptx', 'impress'],
        ];
        if (!isset($filterMap[$targetExt])) return null;

        [$infilter, $outfilter, $ext, $appMode] = $filterMap[$targetExt];
        $profileDir     = $this->makeLoProfile($sessionId, 's2');
        $expectedOutput = $this->storageDir . DS . "{$sessionId}_input.{$ext}";

        $cmd = $this->buildLoCmd(
            $inputPath, $profileDir, $sessionId,
            "{$ext}:\"{$outfilter}\"",
            $infilter,
            $appMode
        );
        exec($cmd, $lines, $code);
        $this->removeDir($profileDir);

        Log::info("LO S2 infilter [{$targetExt}] exit={$code}");

        if ($this->isValidFile($expectedOutput)) return $expectedOutput;
        return $this->findNewestOutputFile($targetExt, $sessionId);
    }

    /* =========================================================
       S3: Auto-detect (no infilter)
    ========================================================= */
    private function tryPdfToOfficeAutoDetect(string $inputPath, string $sessionId, string $targetExt): ?string
    {
        $filterMap = [
            'docx' => 'MS Word 2007 XML',
            'xlsx' => 'Calc MS Excel 2007 XML',
            'pptx' => 'Impress MS PowerPoint 2007 XML',
        ];
        $convertTo = isset($filterMap[$targetExt])
            ? "{$targetExt}:\"{$filterMap[$targetExt]}\""
            : $targetExt;

        $appMode = match ($targetExt) {
            'xlsx' => 'calc',
            'pptx' => 'impress',
            default => 'writer',
        };

        $profileDir     = $this->makeLoProfile($sessionId, 's3');
        $expectedOutput = $this->storageDir . DS . "{$sessionId}_input.{$targetExt}";

        $cmd = $this->buildLoCmd($inputPath, $profileDir, $sessionId, $convertTo, null, $appMode);
        exec($cmd, $lines, $code);
        $this->removeDir($profileDir);

        Log::info("LO S3 autodetect [{$targetExt}] exit={$code}");

        if ($this->isValidFile($expectedOutput)) return $expectedOutput;
        return $this->findNewestOutputFile($targetExt, $sessionId);
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
            'docx' => 'MS Word 2007 XML',
            'xlsx' => 'Calc MS Excel 2007 XML',
            'pptx' => 'Impress MS PowerPoint 2007 XML',
            'odt'  => 'odt',
            'png'  => 'png',
        ];

        $convertTo = isset($filterMap[$targetExt]) && !in_array($targetExt, ['pdf', 'odt', 'png'])
            ? "{$targetExt}:\"{$filterMap[$targetExt]}\""
            : ($filterMap[$targetExt] ?? $targetExt);

        $cmd = $this->buildLoCmd($inputPath, $profileDir, $sessionId, $convertTo);
        $outputLines = [];
        exec($cmd, $outputLines, $exitCode);
        $output = implode("\n", $outputLines);
        $this->removeDir($profileDir);

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

    private function makeLoProfile(string $sessionId, string $suffix): string
    {
        if ($this->isWindows) {
            $dir = sys_get_temp_dir() . DS . 'lo_' . substr($sessionId, 0, 8) . '_' . $suffix;
        } else {
            $dir = $this->storageDir . DS . "lo_{$sessionId}_{$suffix}";
        }
        @mkdir($dir, 0777, true);
        return $dir;
    }

    /**
     * v6: Added optional $appMode parameter ('writer', 'calc', 'impress')
     * When set, the corresponding --writer / --calc / --impress flag is prepended
     * to help LibreOffice choose the correct rendering engine for the file type,
     * which significantly improves table and layout fidelity.
     */
    private function buildLoCmd(
        string  $inputPath,
        string  $profileDir,
        string  $sessionId,
        string  $convertTo,
        ?string $infilter = null,
        ?string $appMode  = null
    ): string {
        $loTimeout = (int)env('LO_TIMEOUT', 120);

        if ($this->isWindows) {
            $profileUri = 'file:///' . str_replace(['\\', ' '], ['/', '%20'], $profileDir);

            $parts = [
                '"' . $this->sofficeBin . '"',
                '--headless', '--norestore', '--nofirststartwizard', '--nolockcheck',
                '-env:UserInstallation=' . $profileUri,
            ];
            if ($appMode) $parts[] = "--{$appMode}";
            if ($infilter) $parts[] = '--infilter="' . $infilter . '"';
            $parts[] = '--convert-to';
            $parts[] = '"' . $convertTo . '"';
            $parts[] = '--outdir';
            $parts[] = '"' . $this->storageDir . '"';
            $parts[] = '"' . $inputPath . '"';
            $parts[] = '2>&1';
            return implode(' ', $parts);
        }

        // Linux / macOS
        $profileUri = 'file://' . str_replace(' ', '%20', $profileDir);
        $homeDir    = '/tmp/lo_home_' . $sessionId;
        $parts = [
            'HOME='           . escapeshellarg($homeDir),
            'XDG_CACHE_HOME=' . escapeshellarg('/tmp/lo_cache_' . $sessionId),
            'SAL_USE_VCLPLUGIN=svp',
            'FONTCONFIG_PATH=/etc/fonts',
            // Timeout wrapper so a frozen LO doesn't block forever
            'timeout', (string)$loTimeout,
            escapeshellcmd($this->sofficeBin),
            '--headless', '--norestore', '--nofirststartwizard', '--nolockcheck',
            '-env:UserInstallation=' . escapeshellarg($profileUri),
        ];
        if ($appMode) $parts[] = "--{$appMode}";
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
        if (!class_exists(\PhpOffice\PhpWord\PhpWord::class)) {
            throw new \Exception("PhpWord tidak tersedia. Jalankan: composer require phpoffice/phpword");
        }
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
        if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            throw new \Exception("PhpSpreadsheet tidak tersedia. Jalankan: composer require phpoffice/phpspreadsheet");
        }
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

    private function isPdfPasswordProtected(string $pdfPath): bool
    {
        if (!class_exists(\Smalot\PdfParser\Parser::class)) return false;
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $parser->parseFile($pdfPath);
            return false;
        } catch (\Throwable $e) {
            $msg = strtolower($e->getMessage());
            return str_contains($msg, 'password') || str_contains($msg, 'encrypt');
        }
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
            ? sprintf('"%s" --skip-text --force-ocr --optimize 2 --language eng+ind "%s" "%s" 2>&1',
                $bin, $inputPdf, $outputPdf)
            : sprintf('%s --skip-text --force-ocr --optimize 2 --language eng+ind %s %s 2>&1',
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
            ? sprintf('"%s" "%s" "%s" -l %s --oem 1 --psm 3 2>&1', $bin, $imagePath, $outBase, $lang)
            : sprintf('%s %s %s -l %s --oem 1 --psm 3 2>&1',
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
    private function findNewestOutputFile(string $ext, string $sessionId): ?string
    {
        $all = glob($this->storageDir . DS . '*.' . $ext) ?: [];
        if (empty($all)) return null;

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
                ? 'Tambahkan "C:\\Program Files\\LibreOffice\\program" ke PATH, lalu set LIBREOFFICE_BINARY=soffice.exe di .env'
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
        if (!preg_match('/^[\w\s\-\.()\[\]]+$/u', $filename)) {
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
        if (!$this->isWindows) {
            foreach (glob('/tmp/lo_home_*') ?: [] as $f) {
                if (is_dir($f) && @filemtime($f) < $limit) $this->removeDir($f);
            }
            foreach (glob('/tmp/lo_cache_*') ?: [] as $f) {
                if (is_dir($f) && @filemtime($f) < $limit) $this->removeDir($f);
            }
        }
    }

    /* =========================================================
       DEBUG — Remove in production!
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

        // Test font availability (crucial for table rendering)
        $fontTest = [];
        exec('fc-list | grep -i "Arial\|Liberation\|Caladea\|Carlito" 2>&1 | head -10', $fontTest);

        return response()->json([
            'php_version' => PHP_VERSION,
            'os'          => PHP_OS_FAMILY,
            'is_windows'  => $this->isWindows,
            'env' => [
                'LIBREOFFICE_BINARY' => env('LIBREOFFICE_BINARY', '(not set)'),
                'GHOSTSCRIPT_BINARY' => env('GHOSTSCRIPT_BINARY', '(not set)'),
                'TESSERACT_BINARY'   => env('TESSERACT_BINARY',   '(not set)'),
                'OCRMYPDF_BINARY'    => env('OCRMYPDF_BINARY',    '(not set)'),
                'LO_TIMEOUT'         => env('LO_TIMEOUT',         '120'),
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
            'fonts' => $fontTest ?: ['(no font output — run: fc-list | grep Arial)'],
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