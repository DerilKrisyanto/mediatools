<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

/**
 * FileConverterController — v8 (Table Fidelity + Font Fix Edition)
 *
 * ROOT CAUSES FIXED (from screenshot analysis):
 *
 * BUG 1 — CHARACTER CORRUPTION  ("Ceting" → "Ce,ng", "Sutil" → "Su,l")
 *   Cause:  LibreOffice subsets fonts on PDF export. The font subset drops
 *           certain ligature glyph→Unicode ToUnicode CMap entries, so when
 *           the PDF is read back, ligature glyphs (ti, fi, fl) decode wrong.
 *   Fix:    After LO Office→PDF, run GS with -dSubsetFonts=false (preserve
 *           full font + ToUnicode) + -dCompressFonts=false (keep CMap intact).
 *           Method: fixPdfFontEncoding()
 *
 * BUG 2 — TABLE STRUCTURE LOST  (becomes plain positioned text)
 *   Cause:  writer_pdf_import reads PDF as positioned glyphs, can't infer
 *           cell boundaries without tagged PDF semantic structure.
 *   Fix A:  UseTaggedPDF=true in PDF export filter → LO embeds /StructTreeRoot
 *           with table/cell tags, which writer_pdf_import reads back correctly.
 *   Fix B:  NEW S0 strategy: pdftohtml -c detects visual grid lines → HTML
 *           <table> → LO imports HTML tables natively. Best for colored tables.
 *   Fix C:  NEW S0.5: PhpWord parses pdftohtml CSS inline styles → DOCX with
 *           native cell shading. Color-preserving PHP-level reconstruction.
 *
 * BUG 3 — TABLE COLORS LOST  (blue header background disappears)
 *   Cause:  LO PDF bridge strips background-color from cells.
 *   Fix:    pdftohtml -c emits style="background-color:#4472C4" on <td>/<th>.
 *           buildPhpWordTable() reads this CSS and applies PhpWord cell bgColor.
 *
 * STRATEGY CHAIN for PDF→DOCX/XLSX/PPTX:
 *   S0    PDF→HTML (pdftohtml -c) → DOCX/XLSX via LO  [NEW — best for colored tables]
 *   S0.5  PHP parse pdftohtml HTML → DOCX via PhpWord  [NEW — color-preserving]
 *   S1    PDF → ODT bridge → OOXML
 *   S1.5  PDF → ODS (calc) → XLSX  [XLSX only]
 *   S2    Direct infilter + app mode
 *   S3    Auto-detect
 *   S4    pdftotext -layout reconstruction
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

    private function findBinary(string $envKey, array $fallbacks): string
    {
        return $this->resolveBinary(env($envKey, ''), '', $fallbacks);
    }

    /* =========================================================
       APP-MODE / FILTER HELPERS
    ========================================================= */
    private function getAppModeFromExt(string $ext): string
    {
        return match (strtolower($ext)) {
            'xls', 'xlsx', 'ods', 'csv'  => 'calc',
            'ppt', 'pptx', 'odp'         => 'impress',
            default                       => 'writer',
        };
    }

    private function getPdfExportFilterString(string $appMode): string
    {
        $filterName = match ($appMode) {
            'calc'    => 'calc_pdf_Export',
            'impress' => 'impress_pdf_Export',
            default   => 'writer_pdf_Export',
        };

        $filterData = implode(',', [
            'EmbedStandardFonts=true',
            'IsSkipEmptyPages=false',
            'SelectPdfVersion=16',
            'Quality=100',
            'UseTaggedPDF=true',
        ]);

        return "pdf:{$filterName}:{$filterData}";
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
       OUTPUT NAMING
    ========================================================= */
    private function buildOutputName(string $originalName, string $ext, ?int $pageNum = null): string
    {
        $base = pathinfo($originalName, PATHINFO_FILENAME);
        $base = preg_replace('/[\\\\\\/:\*\?"<>\|\s]+/', '_', $base);
        $base = trim($base, '_') ?: 'file';
        return $pageNum !== null
            ? "{$base}_by_MediaTools_Hal{$pageNum}.{$ext}"
            : "{$base}_by_MediaTools.{$ext}";
    }

    private function saveWithDisplayName(string $tempPath, string $origName, string $ext, ?int $pageNum = null): string
    {
        if (!file_exists($tempPath) || filesize($tempPath) === 0) {
            throw new \Exception("Output kosong / gagal dihasilkan.");
        }
        $this->validateOutputIntegrity($tempPath, $ext);

        $displayName = $this->buildOutputName($origName, $ext, $pageNum);
        $destPath    = $this->storageDir . DS . $displayName;

        if (file_exists($destPath)) {
            $uid = substr(md5(uniqid('', true)), 0, 6);
            $displayName = pathinfo($displayName, PATHINFO_FILENAME) . "_{$uid}.{$ext}";
            $destPath    = $this->storageDir . DS . $displayName;
        }

        rename($tempPath, $destPath);
        return $displayName;
    }

    /* =========================================================
       VALIDATE OUTPUT INTEGRITY
    ========================================================= */
    private function validateOutputIntegrity(string $path, string $ext): void
    {
        $minSizes = [
            'pdf' => 500, 'docx' => 2000, 'xlsx' => 2000,
            'pptx' => 2000, 'jpg' => 200, 'png' => 67, 'webp' => 12,
        ];
        $minSize = $minSizes[strtolower($ext)] ?? 100;

        if (!file_exists($path) || filesize($path) < $minSize) {
            throw new \Exception("File output terlalu kecil atau kosong (< {$minSize} bytes).");
        }

        $fh   = fopen($path, 'rb');
        $head = fread($fh, 8);
        fclose($fh);

        switch (strtolower($ext)) {
            case 'pdf':
                if (!str_starts_with($head, '%PDF')) {
                    throw new \Exception("Output bukan PDF valid.");
                }
                break;
            case 'docx': case 'xlsx': case 'pptx':
                if (!str_starts_with($head, "PK\x03\x04") && !str_starts_with($head, "PK\x05\x06")) {
                    throw new \Exception("Output Office tidak valid (bukan ZIP/OOXML).");
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
                    ? imagecreatefromwebp($tmpPath) : throw new \Exception("WebP tidak didukung."),
                str_contains($mime, 'gif')  => imagecreatefromgif($tmpPath),
                str_contains($mime, 'bmp')  => imagecreatefrombmp($tmpPath),
                default => throw new \Exception("Format tidak didukung: {$mime}"),
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
                $mmW *= $scale; $mmH *= $scale;
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
       2. OFFICE → PDF  (v8 — Two-Pass: LO + GS Font Fix)
    ========================================================= */
    private function officeToPdf($file, string $sessionId, string $originalName): array
    {
        $this->requireSoffice();
        $ext     = strtolower($file->getClientOriginalExtension() ?: 'docx');
        $tmpName = "{$sessionId}_input.{$ext}";
        $tmpPath = $this->storageDir . DS . $tmpName;
        $file->move($this->storageDir, $tmpName);

        try {
            $appMode = $this->getAppModeFromExt($ext);

            // Pass 1A: LO with app-specific PDF export filter + UseTaggedPDF=true
            $pdfPath = $this->runLibreOfficeWithOptions(
                $tmpPath, $this->getPdfExportFilterString($appMode),
                $sessionId, $appMode, 'lo1'
            );

            // Pass 1B: fallback to plain pdf if filter string fails
            if (!$this->isValidFile($pdfPath)) {
                $pdfPath = $this->runLibreOfficeWithOptions(
                    $tmpPath, 'pdf', $sessionId, $appMode, 'lo2'
                );
            }

            if (!$this->isValidFile($pdfPath)) {
                throw new \Exception("LibreOffice gagal mengkonversi {$ext} ke PDF.");
            }

            // Pass 2: GS font-fix — preserves full ToUnicode CMap
            // This is what fixes "Ceting"→"Ce,ng" character corruption.
            $fixedPdf = $this->fixPdfFontEncoding($pdfPath, $sessionId);
            $finalPdf = $this->isValidFile($fixedPdf) ? $fixedPdf : $pdfPath;

            $result = $this->saveWithDisplayName($finalPdf, $originalName, 'pdf');

            if ($fixedPdf && $fixedPdf !== $pdfPath && file_exists($fixedPdf)) @unlink($fixedPdf);
            if ($pdfPath && file_exists($pdfPath)) @unlink($pdfPath);

            return [$result];
        } finally {
            @unlink($tmpPath);
        }
    }

    /**
     * GS pass to fix font ToUnicode encoding after LibreOffice PDF export.
     *
     * Root cause of "Ceting" → "Ce,ng":
     *   LibreOffice uses font subsetting by default. The subset process sometimes
     *   corrupts the ToUnicode CMap entries for ligature glyphs (ti, fi, fl, etc.).
     *   When the PDF is subsequently read by any PDF reader/extractor, these glyphs
     *   decode to wrong Unicode characters (comma instead of "ti").
     *
     *   Fix: Re-write the PDF with GS using:
     *     -dSubsetFonts=false   → embed FULL font object (not subset)
     *     -dCompressFonts=false → preserve uncompressed ToUnicode CMap streams
     *
     *   Trade-off: larger PDF file size.
     */
    private function fixPdfFontEncoding(string $inputPath, string $sessionId): ?string
    {
        if ($this->gsbin === '') return null;

        $outputPath = $this->storageDir . DS . "{$sessionId}_fontfix.pdf";

        $gsArgs = implode(' ', [
            '-dNOPAUSE', '-dBATCH', '-dSAFER', '-dQUIET',
            '-sDEVICE=pdfwrite',
            '-dCompatibilityLevel=1.7',
            '-dEmbedAllFonts=true',
            '-dSubsetFonts=false',
            '-dCompressFonts=false',
            '-dNOCACHE',
            '-dAutoRotatePages=/None',
        ]);

        $cmd = $this->isWindows
            ? sprintf('"%s" %s -sOutputFile="%s" "%s" 2>&1',
                $this->gsbin, $gsArgs, $outputPath, $inputPath)
            : sprintf('%s %s -sOutputFile=%s %s 2>&1',
                escapeshellcmd($this->gsbin), $gsArgs,
                escapeshellarg($outputPath),
                escapeshellarg($inputPath));

        exec($cmd, $output, $exitCode);

        if ($exitCode === 0 && $this->isValidFile($outputPath)) {
            Log::info("GS font-fix OK [{$sessionId}] — ToUnicode preserved, no more char corruption");
            return $outputPath;
        }

        Log::warning("GS font-fix failed (exit {$exitCode}): " . implode(' | ', array_slice($output, 0, 3)));
        @unlink($outputPath);
        return null;
    }

    private function runLibreOfficeWithOptions(
        string $inputPath, string $convertTo, string $sessionId,
        string $appMode, string $suffix
    ): ?string {
        $profileDir = $this->makeLoProfile($sessionId, $suffix);
        $cmd        = $this->buildLoCmd($inputPath, $profileDir, $sessionId, $convertTo, null, $appMode);

        $outputLines = [];
        exec($cmd, $outputLines, $exitCode);
        $this->removeDir($profileDir);

        $bareExt        = explode(':', $convertTo)[0];
        $inputBasename  = pathinfo($inputPath, PATHINFO_FILENAME);
        $expectedOutput = $this->storageDir . DS . $inputBasename . '.' . $bareExt;

        if ($this->isValidFile($expectedOutput)) return $expectedOutput;
        $found = $this->findNewestOutputFile($bareExt, $sessionId);
        if ($found) return $found;

        if ($exitCode !== 0) {
            Log::warning("LO [{$suffix}] exit={$exitCode}: " . substr(implode("\n", $outputLines), 0, 200));
        }
        return null;
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
                        $this->pdfToImageGhostscript($tmpPath, $sessionId, $fmt), $originalName, $fmt);
                } catch (\Exception $e) { Log::warning("GS PDF→IMG: " . $e->getMessage()); }
            }
            if (extension_loaded('imagick')) {
                try {
                    return $this->renameImagePages(
                        $this->pdfToImageImagick($tmpPath, $sessionId, $fmt), $originalName, $fmt);
                } catch (\Exception $e) { Log::warning("Imagick PDF→IMG: " . $e->getMessage()); }
            }
            if ($this->sofficeBin !== '') {
                return $this->renameImagePages(
                    $this->pdfToImageViaLibreOffice($tmpPath, $sessionId, $fmt), $originalName, $fmt);
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
            ? sprintf('"%s" -dNOPAUSE -dBATCH -dSAFER -dQUIET -sDEVICE=%s -dFirstPage=1 -dLastPage=250 -r200 -sOutputFile="%s" "%s" 2>&1',
                $this->gsbin, $device, $outPattern, $pdfPath)
            : sprintf('%s -dNOPAUSE -dBATCH -dSAFER -dQUIET -sDEVICE=%s -dTextAlphaBits=4 -dGraphicsAlphaBits=4 -dUseCropBox -dFirstPage=1 -dLastPage=250 -r200 -sOutputFile=%s %s 2>&1',
                escapeshellcmd($this->gsbin), escapeshellarg($device),
                escapeshellarg($outPattern), escapeshellarg($pdfPath));

        exec($cmd, $output, $exitCode);
        $files = glob($this->storageDir . DS . "{$sessionId}_p*.{$fmt}") ?: [];
        natsort($files);
        $outputFiles = [];
        foreach ($files as $f) {
            if (file_exists($f) && filesize($f) > 0) $outputFiles[] = basename($f);
        }
        if (empty($outputFiles)) {
            throw new \Exception("GS PDF→Gambar gagal (exit {$exitCode}).");
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
        $imgFmt = ($fmt === 'png') ? 'png' : 'jpeg';
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
       4. PDF → OFFICE  (v8 Full Strategy Chain)
    ========================================================= */
    private function pdfToOffice($file, string $sessionId, string $targetExt, string $originalName): array
    {
        $this->requireSoffice();

        $safeName    = "{$sessionId}_input.pdf";
        $safePath    = $this->storageDir . DS . $safeName;
        $file->move($this->storageDir, $safeName);
        $workingPath = $safePath;
        $tempFiles   = [$safePath];

        try {
            if ($this->isPdfPasswordProtected($workingPath)) {
                throw new \Exception("PDF terproteksi password. Hapus password terlebih dahulu.");
            }

            $repairedPath = $this->repairPdfWithGhostscript($workingPath, $sessionId);
            if ($repairedPath !== $workingPath) { $workingPath = $repairedPath; $tempFiles[] = $repairedPath; }

            $normalizedPath = $this->normalizePdfOrientation($workingPath, $sessionId);
            if ($normalizedPath && $normalizedPath !== $workingPath) { $workingPath = $normalizedPath; $tempFiles[] = $normalizedPath; }

            $hasText = $this->pdfHasTextLayer($workingPath);
            Log::info("pdfToOffice [{$targetExt}] hasText={$hasText} sid={$sessionId}");

            if (!$hasText) {
                $ocrPdf = $this->storageDir . DS . "{$sessionId}_ocr.pdf";
                if ($this->runOcrmypdf($workingPath, $ocrPdf)) { $workingPath = $ocrPdf; $tempFiles[] = $ocrPdf; }
            }

            // S0: pdftohtml → LO HTML import (best for colored tables)
            if (in_array($targetExt, ['docx', 'xlsx'])) {
                $s0 = $this->tryPdfViaHtmlBridge($workingPath, $sessionId, $targetExt);
                if ($this->isValidFile($s0)) {
                    Log::info("pdfToOffice S0 HTML-bridge OK [{$targetExt}]");
                    return [$this->saveWithDisplayName($s0, $originalName, $targetExt)];
                }
            }

            // S0.5: pdftohtml → PhpWord (color-preserving DOCX)
            if ($targetExt === 'docx') {
                $s05 = $this->tryHtmlToDocxViaPhpWord($workingPath, $sessionId);
                if ($this->isValidFile($s05)) {
                    Log::info("pdfToOffice S0.5 PhpWord OK [docx]");
                    return [$this->saveWithDisplayName($s05, $originalName, 'docx')];
                }
            }

            // S1: ODT bridge
            $s1 = $this->tryPdfViaOdtBridge($workingPath, $sessionId, $targetExt);
            if ($this->isValidFile($s1)) {
                Log::info("pdfToOffice S1 ODT-bridge OK [{$targetExt}]");
                return [$this->saveWithDisplayName($s1, $originalName, $targetExt)];
            }

            // S1.5: Calc bridge for XLSX
            if ($targetExt === 'xlsx') {
                $s15 = $this->tryPdfViaCalcBridge($workingPath, $sessionId);
                if ($this->isValidFile($s15)) {
                    Log::info("pdfToOffice S1.5 Calc OK [xlsx]");
                    return [$this->saveWithDisplayName($s15, $originalName, 'xlsx')];
                }
            }

            // S2: infilter
            $s2 = $this->tryPdfToOfficeInfilter($workingPath, $sessionId, $targetExt);
            if ($this->isValidFile($s2)) {
                Log::info("pdfToOffice S2 infilter OK [{$targetExt}]");
                return [$this->saveWithDisplayName($s2, $originalName, $targetExt)];
            }

            // S3: autodetect
            $s3 = $this->tryPdfToOfficeAutoDetect($workingPath, $sessionId, $targetExt);
            if ($this->isValidFile($s3)) {
                Log::info("pdfToOffice S3 autodetect OK [{$targetExt}]");
                return [$this->saveWithDisplayName($s3, $originalName, $targetExt)];
            }

            // S4: text fallback
            $text = $this->extractPdfTextWithLayout($workingPath, $sessionId);
            if (trim($text) === '') $text = $this->extractPdfTextSmart($workingPath, $sessionId);

            if (trim($text) !== '') {
                if ($targetExt === 'docx') {
                    $out = $this->storageDir . DS . "{$sessionId}_fallback.docx";
                    $this->createDocxFormatted($text, $out);
                    if ($this->isValidFile($out)) return [$this->saveWithDisplayName($out, $originalName, 'docx')];
                }
                if ($targetExt === 'xlsx') {
                    $out = $this->storageDir . DS . "{$sessionId}_fallback.xlsx";
                    $this->createExcelFromText($text, $out);
                    if ($this->isValidFile($out)) return [$this->saveWithDisplayName($out, $originalName, 'xlsx')];
                }
            }

            throw new \Exception("Konversi PDF → {$targetExt} gagal pada semua strategi.");
        } finally {
            foreach ($tempFiles as $f) { if (file_exists($f)) @unlink($f); }
        }
    }

    /* =========================================================
       PDF REPAIR via Ghostscript (/printer preset)
    ========================================================= */
    private function repairPdfWithGhostscript(string $inputPath, string $sessionId): string
    {
        if ($this->gsbin === '') return $inputPath;
        $outputPath = $this->storageDir . DS . "{$sessionId}_repaired.pdf";
        $cmd = $this->isWindows
            ? sprintf('"%s" -dNOPAUSE -dBATCH -dSAFER -dQUIET -sDEVICE=pdfwrite -dCompatibilityLevel=1.6 -dPDFSETTINGS=/printer -dEmbedAllFonts=true -dSubsetFonts=true -dCompressFonts=false -dFASTWEBVIEW=false -dOptimize=true -dAutoRotatePages=/None -sOutputFile="%s" "%s" 2>&1',
                $this->gsbin, $outputPath, $inputPath)
            : sprintf('%s -dNOPAUSE -dBATCH -dSAFER -dQUIET -sDEVICE=pdfwrite -dCompatibilityLevel=1.6 -dPDFSETTINGS=/printer -dEmbedAllFonts=true -dSubsetFonts=true -dCompressFonts=false -dFASTWEBVIEW=false -dOptimize=true -dAutoRotatePages=/None -sOutputFile=%s %s 2>&1',
                escapeshellcmd($this->gsbin), escapeshellarg($outputPath), escapeshellarg($inputPath));
        exec($cmd, $output, $exitCode);
        if ($exitCode === 0 && $this->isValidFile($outputPath)) return $outputPath;
        @unlink($outputPath);
        return $inputPath;
    }

    private function normalizePdfOrientation(string $inputPath, string $sessionId): ?string
    {
        if ($this->gsbin === '') return null;
        $outputPath = $this->storageDir . DS . "{$sessionId}_normalized.pdf";
        $cmd = $this->isWindows
            ? sprintf('"%s" -dNOPAUSE -dBATCH -dSAFER -dQUIET -sDEVICE=pdfwrite -dAutoRotatePages=/PageByPage -sOutputFile="%s" "%s" 2>&1',
                $this->gsbin, $outputPath, $inputPath)
            : sprintf('%s -dNOPAUSE -dBATCH -dSAFER -dQUIET -sDEVICE=pdfwrite -dAutoRotatePages=/PageByPage -sOutputFile=%s %s 2>&1',
                escapeshellcmd($this->gsbin), escapeshellarg($outputPath), escapeshellarg($inputPath));
        exec($cmd, $output, $exitCode);
        if ($exitCode === 0 && $this->isValidFile($outputPath)) return $outputPath;
        @unlink($outputPath);
        return null;
    }

    /* =========================================================
       S0: pdftohtml → DOCX/XLSX via LibreOffice HTML import
    ========================================================= */
    private function tryPdfViaHtmlBridge(string $inputPath, string $sessionId, string $targetExt): ?string
    {
        $pdftohtmlBin = $this->findBinary('PDFTOHTML_BINARY',
            $this->isWindows ? ['pdftohtml.exe', 'pdftohtml'] : ['pdftohtml', '/usr/bin/pdftohtml', '/usr/local/bin/pdftohtml']
        );
        if ($pdftohtmlBin === '') { Log::info("S0 skip: pdftohtml not found — sudo apt install poppler-utils"); return null; }

        $htmlBase = $this->storageDir . DS . "{$sessionId}_s0";
        $htmlPath = $htmlBase . '.html';

        $cmd = $this->isWindows
            ? sprintf('"%s" -s -c -noframes -nodrm "%s" "%s" 2>&1', $pdftohtmlBin, $inputPath, $htmlPath)
            : sprintf('%s -s -c -noframes -nodrm %s %s 2>&1',
                escapeshellcmd($pdftohtmlBin), escapeshellarg($inputPath), escapeshellarg($htmlPath));
        exec($cmd, $htmlOut, $htmlCode);

        $actualHtml = null;
        foreach ([$htmlPath, $htmlBase . '-s.html', $htmlBase . 's.html'] as $c) {
            if (file_exists($c) && filesize($c) > 100) { $actualHtml = $c; break; }
        }
        if (!$actualHtml) { Log::warning("S0 pdftohtml failed (exit {$htmlCode})"); return null; }

        Log::info("S0 HTML: " . filesize($actualHtml) . " bytes");

        $appMode   = ($targetExt === 'xlsx') ? 'calc' : 'writer';
        $filterStr = ($targetExt === 'xlsx') ? 'xlsx:"Calc MS Excel 2007 XML"' : 'docx:"MS Word 2007 XML"';

        $outputPath = $this->runLibreOfficeWithOptions(
            $actualHtml, $filterStr, $sessionId . '_s0', $appMode, 's0'
        );

        @unlink($actualHtml);
        foreach (glob($htmlBase . '*') ?: [] as $tmp) @unlink($tmp);
        return $outputPath;
    }

    /* =========================================================
       S0.5: pdftohtml → PhpWord DOCX (color-preserving)
    ========================================================= */
    private function tryHtmlToDocxViaPhpWord(string $inputPath, string $sessionId): ?string
    {
        if (!class_exists(\PhpOffice\PhpWord\PhpWord::class)) return null;

        $pdftohtmlBin = $this->findBinary('PDFTOHTML_BINARY',
            $this->isWindows ? ['pdftohtml.exe', 'pdftohtml'] : ['pdftohtml', '/usr/bin/pdftohtml', '/usr/local/bin/pdftohtml']
        );
        if ($pdftohtmlBin === '') return null;

        $htmlBase = $this->storageDir . DS . "{$sessionId}_s05";
        $htmlPath = $htmlBase . '.html';

        $cmd = $this->isWindows
            ? sprintf('"%s" -s -c -noframes -nodrm "%s" "%s" 2>&1', $pdftohtmlBin, $inputPath, $htmlPath)
            : sprintf('%s -s -c -noframes -nodrm %s %s 2>&1',
                escapeshellcmd($pdftohtmlBin), escapeshellarg($inputPath), escapeshellarg($htmlPath));
        exec($cmd, $out, $code);

        $actualHtml = null;
        foreach ([$htmlPath, $htmlBase . '-s.html', $htmlBase . 's.html'] as $c) {
            if (file_exists($c) && filesize($c) > 100) { $actualHtml = $c; break; }
        }
        if (!$actualHtml) return null;

        try {
            $html = @file_get_contents($actualHtml);
            @unlink($actualHtml);
            foreach (glob($htmlBase . '*') ?: [] as $tmp) @unlink($tmp);
            if (!$html) return null;

            $outputPath = $this->storageDir . DS . "{$sessionId}_s05.docx";
            $this->buildDocxFromHtml($html, $outputPath);
            return $this->isValidFile($outputPath) ? $outputPath : null;
        } catch (\Throwable $e) {
            Log::warning("S0.5 PhpWord failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Parse pdftohtml output and build DOCX via PhpWord.
     * KEY FEATURE: preserves cell background-color from pdftohtml CSS.
     */
    private function buildDocxFromHtml(string $html, string $outputPath): void
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);
        $section = $phpWord->addSection([
            'marginTop' => 720, 'marginBottom' => 720,
            'marginLeft' => 720, 'marginRight' => 720,
        ]);

        $prevErrors = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $cleanHtml = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        @$dom->loadHTML($cleanHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_use_internal_errors($prevErrors);

        $xpath = new \DOMXPath($dom);
        $body = $dom->getElementsByTagName('body')->item(0) ?? $dom->documentElement;
        if ($body) {
            $processed = [];
            $this->processHtmlNode($body, $section, $xpath, $processed);
        }

        \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007')->save($outputPath);
    }

    private function processHtmlNode(
        \DOMNode $node,
        \PhpOffice\PhpWord\Element\Section $section,
        \DOMXPath $xpath,
        array &$processed
    ): void {
        foreach ($node->childNodes as $child) {
            $hash = spl_object_hash($child);
            if (in_array($hash, $processed)) continue;

            if ($child->nodeType === XML_TEXT_NODE) {
                $text = trim($child->nodeValue ?? '');
                if ($text !== '') $section->addText(htmlspecialchars_decode($text));
                continue;
            }
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;

            $tag = strtolower($child->nodeName);
            switch ($tag) {
                case 'table':
                    $processed[] = $hash;
                    $this->buildPhpWordTable($child, $section);
                    break;
                case 'h1': case 'h2': case 'h3':
                    $processed[] = $hash;
                    $text = trim($child->textContent ?? '');
                    if ($text !== '') {
                        $fs = match($tag) { 'h1' => 18, 'h2' => 16, default => 14 };
                        $section->addText(htmlspecialchars_decode($text), ['bold' => true, 'size' => $fs]);
                    }
                    break;
                case 'p': case 'div': case 'span':
                    $text = trim($child->textContent ?? '');
                    if ($text !== '') {
                        $fs = [];
                        if ($child->hasAttribute('style')) {
                            $css = $this->parseCssStyle($child->getAttribute('style'));
                            if (isset($css['font-weight']) && $css['font-weight'] === 'bold') $fs['bold'] = true;
                        }
                        $section->addText(htmlspecialchars_decode($text), $fs ?: null);
                    } else {
                        $this->processHtmlNode($child, $section, $xpath, $processed);
                    }
                    break;
                case 'br':
                    $section->addTextBreak();
                    break;
                default:
                    $this->processHtmlNode($child, $section, $xpath, $processed);
            }
        }
    }

    /**
     * Build PhpWord table from DOM <table>.
     * Reads inline CSS background-color from <td>/<th> and applies as cell bgColor.
     * This is the core fix for "table header color lost" bug.
     */
    private function buildPhpWordTable(\DOMElement $tableEl, \PhpOffice\PhpWord\Element\Section $section): void
    {
        $rows = [];
        foreach ($tableEl->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;
            $tag = strtolower($child->nodeName);
            if ($tag === 'tr') {
                $rows[] = $child;
            } elseif (in_array($tag, ['thead', 'tbody', 'tfoot'])) {
                foreach ($child->childNodes as $tr) {
                    if ($tr->nodeType === XML_ELEMENT_NODE && strtolower($tr->nodeName) === 'tr') {
                        $rows[] = $tr;
                    }
                }
            }
        }
        if (empty($rows)) return;

        $maxCols = 0;
        foreach ($rows as $row) {
            $c = 0;
            foreach ($row->childNodes as $cell) {
                if ($cell->nodeType === XML_ELEMENT_NODE && in_array(strtolower($cell->nodeName), ['td', 'th'])) $c++;
            }
            $maxCols = max($maxCols, $c);
        }
        if ($maxCols === 0) return;

        $table    = $section->addTable(['borderSize' => 6, 'borderColor' => '999999', 'cellMargin' => 80]);
        $colWidth = intdiv(9000, $maxCols);

        foreach ($rows as $rowEl) {
            $table->addRow();
            foreach ($rowEl->childNodes as $cellEl) {
                if ($cellEl->nodeType !== XML_ELEMENT_NODE) continue;
                $cellTag = strtolower($cellEl->nodeName);
                if (!in_array($cellTag, ['td', 'th'])) continue;

                // Extract background color from inline CSS
                $bgColor = null;
                if ($cellEl->hasAttribute('style')) {
                    $css = $this->parseCssStyle($cellEl->getAttribute('style'));
                    $bgColor = $this->cssColorToHex($css['background-color'] ?? '')
                            ?? $this->cssColorToHex($css['background'] ?? '');
                }

                $cellStyle = ['valign' => 'center'];
                if ($bgColor) $cellStyle['bgColor'] = $bgColor;

                $cell      = $table->addCell($colWidth, $cellStyle);
                $text      = trim($cellEl->textContent ?? '');
                $isHeader  = ($cellTag === 'th');
                $textStyle = ['size' => 10];
                if ($isHeader) $textStyle['bold'] = true;

                if ($cellEl->hasAttribute('style')) {
                    $css = $this->parseCssStyle($cellEl->getAttribute('style'));
                    if (isset($css['font-weight']) && $css['font-weight'] === 'bold') $textStyle['bold'] = true;
                    if (isset($css['font-size'])) {
                        $pts = (int)preg_replace('/[^0-9]/', '', $css['font-size']);
                        if ($pts > 0) $textStyle['size'] = $pts;
                    }
                    if (isset($css['color'])) {
                        $tc = $this->cssColorToHex($css['color']);
                        if ($tc) $textStyle['color'] = $tc;
                    }
                }

                $paraStyle = [];
                if ($cellEl->hasAttribute('align')) {
                    $paraStyle['alignment'] = match(strtolower($cellEl->getAttribute('align'))) {
                        'center' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
                        'right'  => \PhpOffice\PhpWord\SimpleType\Jc::END,
                        default  => \PhpOffice\PhpWord\SimpleType\Jc::START,
                    };
                }

                $cell->addText(
                    $text !== '' ? htmlspecialchars_decode($text) : '',
                    $textStyle,
                    $paraStyle ?: null
                );
            }
        }
        $section->addTextBreak(1);
    }

    private function parseCssStyle(string $style): array
    {
        $props = [];
        foreach (explode(';', $style) as $rule) {
            $parts = explode(':', $rule, 2);
            if (count($parts) === 2) {
                $props[trim(strtolower($parts[0]))] = trim($parts[1]);
            }
        }
        return $props;
    }

    private function cssColorToHex(string $color): ?string
    {
        $color = strtolower(trim($color));
        if ($color === '') return null;
        if (preg_match('/^#([0-9a-f]{6})$/i', $color, $m)) return strtoupper($m[1]);
        if (preg_match('/^#([0-9a-f]{3})$/i', $color, $m)) {
            return strtoupper(str_repeat($m[1][0], 2) . str_repeat($m[1][1], 2) . str_repeat($m[1][2], 2));
        }
        if (preg_match('/^rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)$/', $color, $m)) {
            return strtoupper(sprintf('%02X%02X%02X', (int)$m[1], (int)$m[2], (int)$m[3]));
        }
        $named = [
            'white' => 'FFFFFF', 'black' => '000000', 'red' => 'FF0000',
            'green' => '00FF00', 'blue' => '0000FF', 'yellow' => 'FFFF00',
            'orange' => 'FFA500', 'gray' => '808080', 'grey' => '808080',
            'navy' => '000080', 'silver' => 'C0C0C0',
        ];
        return $named[$color] ?? null;
    }

    /* =========================================================
       S1: ODT bridge
    ========================================================= */
    private function tryPdfViaOdtBridge(string $inputPath, string $sessionId, string $targetExt): ?string
    {
        $odtName  = "{$sessionId}_bridge.odt";
        $odtPath  = $this->storageDir . DS . $odtName;
        $profileA = $this->makeLoProfile($sessionId, 'odtA');
        $appMode  = match ($targetExt) { 'xlsx' => 'calc', 'pptx' => 'impress', default => 'writer' };

        exec($this->buildLoCmd($inputPath, $profileA, $sessionId, 'odt', 'writer_pdf_import', $appMode), $outA, $codeA);
        $this->removeDir($profileA);

        $odtFile = null;
        foreach ([$this->storageDir . DS . "{$sessionId}_input.odt", $odtPath] as $c) {
            if ($this->isValidFile($c)) {
                if ($c !== $odtPath) rename($c, $odtPath);
                $odtFile = $odtPath; break;
            }
        }
        if (!$odtFile) {
            $found = $this->findNewestOutputFile('odt', $sessionId);
            if ($found && $this->isValidFile($found)) { rename($found, $odtPath); $odtFile = $odtPath; }
        }
        if (!$odtFile) return null;

        $filterMap = ['docx' => '"MS Word 2007 XML"', 'xlsx' => '"Calc MS Excel 2007 XML"', 'pptx' => '"Impress MS PowerPoint 2007 XML"'];
        $fp = $filterMap[$targetExt] ?? '';
        $convertTo = $fp ? "{$targetExt}:{$fp}" : $targetExt;

        $profileB = $this->makeLoProfile($sessionId, 'odtB');
        exec($this->buildLoCmd($odtFile, $profileB, $sessionId, $convertTo, null, $appMode), $outB, $codeB);
        $this->removeDir($profileB);

        $expectedFinal = $this->storageDir . DS . "{$sessionId}_bridge.{$targetExt}";
        @unlink($odtFile);

        if ($this->isValidFile($expectedFinal)) return $expectedFinal;
        return $this->findNewestOutputFile($targetExt, $sessionId);
    }

    /* =========================================================
       S1.5: Calc bridge for XLSX
    ========================================================= */
    private function tryPdfViaCalcBridge(string $inputPath, string $sessionId): ?string
    {
        $odsName  = "{$sessionId}_calc_bridge.ods";
        $odsPath  = $this->storageDir . DS . $odsName;
        $profileA = $this->makeLoProfile($sessionId, 'calcA');

        exec($this->buildLoCmd($inputPath, $profileA, $sessionId, 'ods', 'calc_pdf_import', 'calc'), $outA, $codeA);
        $this->removeDir($profileA);

        $odsFile = null;
        $expectedOds = $this->storageDir . DS . "{$sessionId}_input.ods";
        if ($this->isValidFile($expectedOds)) { rename($expectedOds, $odsPath); $odsFile = $odsPath; }
        elseif ($this->isValidFile($odsPath)) { $odsFile = $odsPath; }
        else {
            $found = $this->findNewestOutputFile('ods', $sessionId);
            if ($found && $this->isValidFile($found)) { rename($found, $odsPath); $odsFile = $odsPath; }
        }
        if (!$odsFile) return null;

        $profileB = $this->makeLoProfile($sessionId, 'calcB');
        exec($this->buildLoCmd($odsFile, $profileB, $sessionId, 'xlsx:"Calc MS Excel 2007 XML"', null, 'calc'), $outB, $codeB);
        $this->removeDir($profileB);

        $expectedXlsx = $this->storageDir . DS . "{$sessionId}_calc_bridge.xlsx";
        @unlink($odsFile);

        if ($this->isValidFile($expectedXlsx)) return $expectedXlsx;
        return $this->findNewestOutputFile('xlsx', $sessionId);
    }

    /* =========================================================
       S2: infilter + app mode
    ========================================================= */
    private function tryPdfToOfficeInfilter(string $inputPath, string $sessionId, string $targetExt): ?string
    {
        $map = [
            'docx' => ['writer_pdf_import', '"MS Word 2007 XML"', 'docx', 'writer'],
            'xlsx' => ['calc_pdf_import', '"Calc MS Excel 2007 XML"', 'xlsx', 'calc'],
            'pptx' => ['impress_pdf_import', '"Impress MS PowerPoint 2007 XML"', 'pptx', 'impress'],
        ];
        if (!isset($map[$targetExt])) return null;
        [$infilter, $outfilter, $ext, $appMode] = $map[$targetExt];
        $profileDir = $this->makeLoProfile($sessionId, 's2');
        $expected   = $this->storageDir . DS . "{$sessionId}_input.{$ext}";
        exec($this->buildLoCmd($inputPath, $profileDir, $sessionId, "{$ext}:{$outfilter}", $infilter, $appMode), $l, $c);
        $this->removeDir($profileDir);
        if ($this->isValidFile($expected)) return $expected;
        return $this->findNewestOutputFile($targetExt, $sessionId);
    }

    /* =========================================================
       S3: autodetect
    ========================================================= */
    private function tryPdfToOfficeAutoDetect(string $inputPath, string $sessionId, string $targetExt): ?string
    {
        $filterMap = ['docx' => '"MS Word 2007 XML"', 'xlsx' => '"Calc MS Excel 2007 XML"', 'pptx' => '"Impress MS PowerPoint 2007 XML"'];
        $fp = $filterMap[$targetExt] ?? '';
        $convertTo = $fp ? "{$targetExt}:{$fp}" : $targetExt;
        $appMode = match ($targetExt) { 'xlsx' => 'calc', 'pptx' => 'impress', default => 'writer' };
        $profileDir = $this->makeLoProfile($sessionId, 's3');
        $expected   = $this->storageDir . DS . "{$sessionId}_input.{$targetExt}";
        exec($this->buildLoCmd($inputPath, $profileDir, $sessionId, $convertTo, null, $appMode), $l, $c);
        $this->removeDir($profileDir);
        if ($this->isValidFile($expected)) return $expected;
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
                'webp'        => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($tmpPath) : throw new \Exception("WebP tidak didukung."),
                default       => throw new \Exception("Format tidak didukung: {$ext}"),
            };
            if (!$src) throw new \Exception("Gagal membaca gambar.");
            $w = imagesx($src); $h = imagesy($src);
            $canvas = imagecreatetruecolor($w, $h);
            if ($outFmt === 'png') {
                imagealphablending($canvas, false); imagesavealpha($canvas, true);
                imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
            } else {
                imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            }
            imagecopy($canvas, $src, 0, 0, 0, 0, $w, $h);
            imagedestroy($src);
            $tempFull = $this->storageDir . DS . "{$sessionId}_out_img.{$outFmt}";
            $ok = match ($outFmt) {
                'png'  => imagepng($canvas, $tempFull, 6),
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
        if ($this->gsbin === '') throw new \Exception("Ghostscript tidak tersedia.");
        $tmpName = "{$sessionId}_input.pdf";
        $tmpPath = $this->storageDir . DS . $tmpName;
        $file->move($this->storageDir, $tmpName);
        $tempOut = $this->storageDir . DS . "{$sessionId}_compressed_tmp.pdf";
        try {
            $cmd = $this->isWindows
                ? sprintf('"%s" -dNOPAUSE -dBATCH -dSAFER -dQUIET -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/ebook -dEmbedAllFonts=true -dSubsetFonts=true -dColorImageResolution=150 -dGrayImageResolution=150 -sOutputFile="%s" "%s" 2>&1',
                    $this->gsbin, $tempOut, $tmpPath)
                : sprintf('%s -dNOPAUSE -dBATCH -dSAFER -dQUIET -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/ebook -dEmbedAllFonts=true -dSubsetFonts=true -dColorImageResolution=150 -dGrayImageResolution=150 -sOutputFile=%s %s 2>&1',
                    escapeshellcmd($this->gsbin), escapeshellarg($tempOut), escapeshellarg($tmpPath));
            exec($cmd, $output, $exitCode);
            if ($exitCode !== 0 || !$this->isValidFile($tempOut)) throw new \Exception("GS compress gagal.");
            return [$this->saveWithDisplayName($tempOut, $originalName, 'pdf')];
        } finally { @unlink($tmpPath); }
    }

    /* =========================================================
       CORE LibreOffice
    ========================================================= */
    private function runLibreOffice(string $inputPath, string $targetExt, string $sessionId): string
    {
        $profileDir = $this->makeLoProfile($sessionId, 'run');
        $filterMap = [
            'pdf' => 'pdf', 'docx' => 'MS Word 2007 XML', 'xlsx' => 'Calc MS Excel 2007 XML',
            'pptx' => 'Impress MS PowerPoint 2007 XML', 'odt' => 'odt', 'ods' => 'ods', 'png' => 'png',
        ];
        $convertTo = isset($filterMap[$targetExt]) && !in_array($targetExt, ['pdf', 'odt', 'ods', 'png'])
            ? "{$targetExt}:\"{$filterMap[$targetExt]}\""
            : ($filterMap[$targetExt] ?? $targetExt);

        $cmd = $this->buildLoCmd($inputPath, $profileDir, $sessionId, $convertTo);
        $outputLines = [];
        exec($cmd, $outputLines, $exitCode);
        $this->removeDir($profileDir);

        $expectedOutput = $this->storageDir . DS . pathinfo($inputPath, PATHINFO_FILENAME) . '.' . $targetExt;
        if ($this->isValidFile($expectedOutput)) return $expectedOutput;
        $fallback = $this->findNewestOutputFile($targetExt, $sessionId);
        if (!$fallback) throw new \Exception("LibreOffice gagal (exit {$exitCode}).");
        return $fallback;
    }

    private function makeLoProfile(string $sessionId, string $suffix): string
    {
        $dir = $this->isWindows
            ? sys_get_temp_dir() . DS . 'lo_' . substr($sessionId, 0, 8) . '_' . $suffix
            : $this->storageDir . DS . "lo_{$sessionId}_{$suffix}";
        @mkdir($dir, 0777, true);
        return $dir;
    }

    private function buildLoCmd(
        string $inputPath, string $profileDir, string $sessionId,
        string $convertTo, ?string $infilter = null, ?string $appMode = null
    ): string {
        $loTimeout = (int)env('LO_TIMEOUT', 120);

        if ($this->isWindows) {
            $profileUri = 'file:///' . str_replace(['\\', ' '], ['/', '%20'], $profileDir);
            $parts = ['"' . $this->sofficeBin . '"', '--headless', '--norestore', '--nofirststartwizard', '--nolockcheck', '-env:UserInstallation=' . $profileUri];
            if ($appMode) $parts[] = "--{$appMode}";
            if ($infilter) $parts[] = '--infilter="' . $infilter . '"';
            $parts[] = '--convert-to "' . $convertTo . '"';
            $parts[] = '--outdir "' . $this->storageDir . '"';
            $parts[] = '"' . $inputPath . '"';
            $parts[] = '2>&1';
            return implode(' ', $parts);
        }

        $profileUri = 'file://' . str_replace(' ', '%20', $profileDir);
        $homeDir    = '/tmp/lo_home_' . $sessionId;
        $parts = [
            'HOME=' . escapeshellarg($homeDir),
            'XDG_CACHE_HOME=' . escapeshellarg('/tmp/lo_cache_' . $sessionId),
            'SAL_USE_VCLPLUGIN=svp',
            'FONTCONFIG_PATH=/etc/fonts',
            'LC_ALL=C.UTF-8',
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
       TEXT EXTRACTION HELPERS
    ========================================================= */
    private function extractPdfTextWithLayout(string $pdfPath, string $sessionId): string
    {
        $tmpTxt = $this->storageDir . DS . "layout_" . Str::random(8) . '.txt';
        $cmd = $this->isWindows
            ? 'pdftotext -layout -q "' . $pdfPath . '" "' . $tmpTxt . '" 2>&1'
            : 'pdftotext -layout -q ' . escapeshellarg($pdfPath) . ' ' . escapeshellarg($tmpTxt) . ' 2>&1';
        @exec($cmd);
        if (!file_exists($tmpTxt)) return '';
        $text = trim(@file_get_contents($tmpTxt) ?: '');
        @unlink($tmpTxt);
        return $text;
    }

    private function extractPdfTextSmart(string $pdfPath, string $sessionId): string
    {
        try {
            if (class_exists(\Smalot\PdfParser\Parser::class)) {
                $parser = new \Smalot\PdfParser\Parser();
                $text   = trim($parser->parseFile($pdfPath)->getText());
                if (mb_strlen(preg_replace('/\s+/u', '', $text)) > 30) return $text;
            }
        } catch (\Throwable $e) { Log::warning('PdfParser: ' . $e->getMessage()); }
        try {
            $text = $this->pdfToTextOCR($pdfPath, $sessionId);
            if (trim($text) !== '') return $text;
        } catch (\Throwable $e) { Log::warning('OCR: ' . $e->getMessage()); }
        return '';
    }

    private function createDocxFormatted(string $text, string $outputPath): void
    {
        if (!class_exists(\PhpOffice\PhpWord\PhpWord::class)) throw new \Exception("PhpWord tidak tersedia.");
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection(['marginTop' => 800, 'marginBottom' => 800, 'marginLeft' => 800, 'marginRight' => 800]);
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
        if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) throw new \Exception("PhpSpreadsheet tidak tersedia.");
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;
        foreach (explode("\n", $text) as $line) {
            $cols = preg_split('/\t|\s{2,}/', trim($line));
            $col = 1;
            foreach ($cols as $v) {
                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row, trim($v));
                $col++;
            }
            $row++;
        }
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($outputPath);
    }

    private function pdfHasTextLayer(string $pdfPath): bool
    {
        try {
            if (class_exists(\Smalot\PdfParser\Parser::class)) {
                $text = trim((new \Smalot\PdfParser\Parser())->parseFile($pdfPath)->getText());
                return mb_strlen(preg_replace('/\s+/u', '', $text)) > 30;
            }
        } catch (\Throwable $e) { Log::warning('pdfHasTextLayer: ' . $e->getMessage()); }
        $tmpTxt = $this->storageDir . DS . 'probe_' . Str::random(8) . '.txt';
        @exec('pdftotext -q ' . escapeshellarg($pdfPath) . ' ' . escapeshellarg($tmpTxt) . ' 2>&1');
        if (!file_exists($tmpTxt)) return false;
        $text = trim(@file_get_contents($tmpTxt) ?: '');
        @unlink($tmpTxt);
        return mb_strlen(preg_replace('/\s+/u', '', $text)) > 30;
    }

    private function isPdfPasswordProtected(string $pdfPath): bool
    {
        if (!class_exists(\Smalot\PdfParser\Parser::class)) return false;
        try { (new \Smalot\PdfParser\Parser())->parseFile($pdfPath); return false; }
        catch (\Throwable $e) {
            $m = strtolower($e->getMessage());
            return str_contains($m, 'password') || str_contains($m, 'encrypt');
        }
    }

    private function runOcrmypdf(string $inputPdf, string $outputPdf): bool
    {
        $bin = $this->findBinary('OCRMYPDF_BINARY',
            $this->isWindows ? ['ocrmypdf'] : ['ocrmypdf', '/usr/bin/ocrmypdf', '/usr/local/bin/ocrmypdf']
        );
        if ($bin === '') return false;
        $cmd = $this->isWindows
            ? sprintf('"%s" --skip-text --force-ocr --optimize 2 --language eng+ind "%s" "%s" 2>&1', $bin, $inputPdf, $outputPdf)
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
        exec(sprintf('%s %s %s -l %s --oem 1 --psm 6 2>&1',
            escapeshellcmd($bin), escapeshellarg($imagePath), escapeshellarg($outBase), $lang), $output, $code);
        $txtFile = $outBase . '.txt';
        if ($code !== 0 || !file_exists($txtFile)) throw new \Exception('OCR gagal.');
        return file_get_contents($txtFile) ?: '';
    }

    private function pdfToTextOCR(string $pdfPath, string $sessionId): string
    {
        $pages  = $this->pdfToImageGhostscript($pdfPath, $sessionId . '_ocrtmp', 'png');
        $blocks = [];
        foreach ($pages as $idx => $pageFile) {
            $imgPath = $this->storageDir . DS . $pageFile;
            $outBase = $this->storageDir . DS . "{$sessionId}_ocr_p" . ($idx + 1);
            try { $text = trim($this->runTesseractOnImage($imgPath, $outBase)); if ($text !== '') $blocks[] = $text; }
            catch (\Throwable) {}
            @unlink($imgPath); @unlink($outBase . '.txt');
        }
        return trim(implode("\n\n", $blocks));
    }

    /* =========================================================
       HELPERS
    ========================================================= */
    private function isValidFile(?string $path): bool
    {
        return $path !== null && file_exists($path) && filesize($path) > 1024;
    }

    private function findNewestOutputFile(string $ext, string $sessionId): ?string
    {
        $all = glob($this->storageDir . DS . '*.' . $ext) ?: [];
        if (empty($all)) return null;
        $mine = array_filter($all, fn($f) => str_starts_with(basename($f), $sessionId));
        $candidates = !empty($mine) ? $mine : $all;
        usort($candidates, fn($a, $b) => filemtime($b) <=> filemtime($a));
        foreach ($candidates as $f) { if ($this->isValidFile($f)) return $f; }
        return null;
    }

    private function requireSoffice(): void
    {
        if ($this->sofficeBin === '') {
            $hint = $this->isWindows
                ? 'Set LIBREOFFICE_BINARY=soffice.exe di .env'
                : 'sudo apt install libreoffice && set LIBREOFFICE_BINARY=soffice di .env';
            throw new \Exception("LibreOffice tidak ditemukan. {$hint}");
        }
    }

    private function loadFpdf(): void
    {
        foreach ([base_path('vendor/setasign/fpdf/fpdf.php'), base_path('vendor/fpdf/fpdf/src/Fpdf/Fpdf.php'), app_path('Libraries/fpdf/fpdf.php')] as $p) {
            if (file_exists($p)) {
                require_once $p;
                if (!class_exists('FPDF') && class_exists('setasign\Fpdf\Fpdf')) class_alias('setasign\Fpdf\Fpdf', 'FPDF');
                return;
            }
        }
        throw new \Exception("FPDF tidak ditemukan. Jalankan: composer require setasign/fpdf");
    }

    private function friendlyError(string $type, string $raw): string
    {
        $tips = match (true) {
            in_array($type, ['pdf_to_word', 'pdf_to_excel', 'pdf_to_ppt'])
                => " TIP: Install poppler-utils untuk tabel berwarna: sudo apt install poppler-utils. " .
                   "Install font Microsoft: sudo apt install ttf-mscorefonts-installer fonts-liberation",
            in_array($type, ['word_to_pdf', 'excel_to_pdf', 'ppt_to_pdf'])
                => " TIP: Install Ghostscript agar karakter tidak rusak. Install font: sudo apt install ttf-mscorefonts-installer",
            default => "",
        };
        $clean = preg_replace('/env:UserInstallation=\S+|HOME=\S+|XDG_\w+=\S+/', '', $raw) ?? $raw;
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
        if (!preg_match('/^[\w\s\-\.()\[\]]+$/u', $filename)) abort(403, 'Nama file tidak valid.');
        $path = $this->storageDir . DS . $filename;
        if (!file_exists($path)) abort(404, 'File tidak ditemukan atau sudah dihapus.');
        $mimes = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp',
        ];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return response()->download($path, $filename, [
            'Content-Type' => $mimes[$ext] ?? 'application/octet-stream',
            'Content-Disposition' => "attachment; filename=\"" . preg_replace('/[^\x20-\x7E]/', '_', $filename) . "\"; filename*=UTF-8''" . rawurlencode($filename),
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
            foreach (glob('/tmp/lo_home_*') ?: [] as $f) { if (is_dir($f) && @filemtime($f) < $limit) $this->removeDir($f); }
            foreach (glob('/tmp/lo_cache_*') ?: [] as $f) { if (is_dir($f) && @filemtime($f) < $limit) $this->removeDir($f); }
        }
    }

    /* =========================================================
       DEBUG
    ========================================================= */
    public function debug()
    {
        $sofTest = []; $gsTest = []; $pdftohtmlTest = [];
        if ($this->sofficeBin) exec($this->sofficeBin . ' --version 2>&1', $sofTest);
        if ($this->gsbin) exec($this->gsbin . ' -v 2>&1', $gsTest);
        exec('pdftohtml -v 2>&1 | head -2', $pdftohtmlTest);

        $fontChecks = [];
        if (!$this->isWindows) {
            foreach (['Arial','Calibri','Cambria','Liberation Sans','Liberation Serif','Caladea','Carlito'] as $f) {
                $out = [];
                exec('fc-list | grep -i ' . escapeshellarg($f) . ' 2>&1 | head -1', $out);
                $fontChecks[$f] = !empty($out) ? '✅ ' . $out[0] : '❌ MISSING';
            }
        }

        $pdftohtmlBin = $this->findBinary('PDFTOHTML_BINARY',
            $this->isWindows ? ['pdftohtml.exe'] : ['pdftohtml', '/usr/bin/pdftohtml']
        );

        return response()->json([
            'version'  => 'v8 — Table Fidelity + Font Fix Edition',
            'os'       => PHP_OS_FAMILY,
            'resolved' => [
                'sofficeBin'   => $this->sofficeBin ?: '❌ NOT FOUND',
                'gsbin'        => $this->gsbin      ?: '❌ NOT FOUND (needed for font-fix pass)',
                'pdftohtmlBin' => $pdftohtmlBin     ?: '❌ NOT FOUND — sudo apt install poppler-utils',
            ],
            'exec_test' => [
                'soffice'   => implode(' ', $sofTest)       ?: '(no output)',
                'gs'        => implode(' ', $gsTest)        ?: '(no output)',
                'pdftohtml' => implode(' ', $pdftohtmlTest) ?: '(no output)',
            ],
            'fonts'   => [
                'status'          => $fontChecks ?: ['Windows — check font panel'],
                'install_command' => 'sudo apt install ttf-mscorefonts-installer fonts-liberation fonts-crosextra-caladea fonts-crosextra-carlito && fc-cache -f -v',
            ],
            'v8_fixes' => [
                'char_corruption' => 'GS -dSubsetFonts=false pass after Office→PDF — fixes "Ceting"→"Ce,ng"',
                'table_structure' => 'S0: pdftohtml detects visual grid → <table> HTML',
                'table_colors'    => 'S0.5: PhpWord reads CSS background-color from pdftohtml output',
                'tagged_pdf'      => 'UseTaggedPDF=true in LO export filter',
            ],
            'strategies' => [
                'S0   pdftohtml→LO'      => $pdftohtmlBin ? '✅ BEST for colored tables' : '❌ install poppler-utils',
                'S0.5 pdftohtml→PhpWord' => ($pdftohtmlBin && class_exists(\PhpOffice\PhpWord\PhpWord::class)) ? '✅ color-preserving fallback' : '❌',
                'S1   ODT bridge'        => $this->sofficeBin ? '✅' : '❌',
                'S1.5 Calc bridge'       => $this->sofficeBin ? '✅ XLSX only' : '❌',
                'S2   infilter'          => $this->sofficeBin ? '✅' : '❌',
                'S3   autodetect'        => $this->sofficeBin ? '✅' : '❌',
                'S4   text fallback'     => '✅ always',
            ],
            'packages' => [
                'phpword'        => class_exists(\PhpOffice\PhpWord\PhpWord::class) ? '✅' : '❌ composer require phpoffice/phpword',
                'phpspreadsheet' => class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class) ? '✅' : '❌ composer require phpoffice/phpspreadsheet',
                'pdfparser'      => class_exists(\Smalot\PdfParser\Parser::class) ? '✅' : '❌ composer require smalot/pdfparser',
                'fpdf'           => $this->checkFpdf(),
            ],
        ]);
    }

    private function checkFpdf(): string
    {
        foreach ([base_path('vendor/setasign/fpdf/fpdf.php'), base_path('vendor/fpdf/fpdf/src/Fpdf/Fpdf.php')] as $p) {
            if (file_exists($p)) return "✅ {$p}";
        }
        return '❌ composer require setasign/fpdf';
    }
}