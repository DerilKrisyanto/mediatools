<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

/**
 * FileConverterController — Final
 *
 * Mengikuti pola yang sama dengan PDFUtilitiesController:
 * gunakan nama binary (via PATH) bukan full path di .env.
 *
 * .env yang diperlukan:
 * ──────────────────────────────────────────
 * # Windows (Laragon/lokal)
 * LIBREOFFICE_BINARY=soffice.exe
 * GHOSTSCRIPT_BINARY=gswin64c.exe
 *
 * # Ubuntu / VPS
 * LIBREOFFICE_BINARY=soffice
 * GHOSTSCRIPT_BINARY=gs
 * ──────────────────────────────────────────
 *
 * CATATAN WINDOWS: Pastikan LibreOffice ada di PATH:
 *   System → Environment Variables → Path → Tambah:
 *   C:\Program Files\LibreOffice\program
 *
 * Ghostscript di Windows (Laragon) biasanya sudah otomatis di PATH.
 */

// Alias agar tidak perlu tulis DIRECTORY_SEPARATOR terus-menerus
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

        // ── LibreOffice ──────────────────────────────────────────────
        // Ikuti pola PDFUtilitiesController: pakai nama binary via PATH
        $this->sofficeBin = $this->resolveBinary(
            env('LIBREOFFICE_BINARY', ''),
            env('LIBREOFFICE_PATH',   ''),
            $this->isWindows
                ? [
                    'soffice.exe',
                    'soffice',
                    '"C:\\Program Files\\LibreOffice\\program\\soffice.exe"',
                ]
                : ['soffice', 'libreoffice', '/usr/bin/soffice', '/usr/local/bin/soffice']

        );

        // ── Ghostscript ──────────────────────────────────────────────
        $this->gsbin = $this->resolveBinary(
            env('GHOSTSCRIPT_BINARY', ''),
            env('GHOSTSCRIPT_PATH',   ''),
            $this->isWindows
                ? [
                    'gswin64c',
                    'gswin32c',
                    '"C:\\Program Files\\gs\\gs10.07.0\\bin\\gswin64c.exe"',
                    '"C:\\Program Files\\gs\\gs10.05.0\\bin\\gswin64c.exe"',
                    '"C:\\Program Files\\gs\\gs10.04.0\\bin\\gswin64c.exe"',
                    '"C:\\Program Files\\gs\\gs10.03.1\\bin\\gswin64c.exe"',
                ]
                : ['gs', '/usr/bin/gs', '/usr/local/bin/gs']
        );
    }

    /**
     * Normalize nilai binary/path dari .env atau fallback.
     */
    private function normalizeBinaryValue(string $value): string
    {
        $value = trim($value);
        $value = trim($value, "\"'");

        if ($value === '') {
            return '';
        }

        if ($this->isWindows) {
            $value = str_replace('/', '\\', $value);
        }

        return $value;
    }

    /**
     * Resolve binary — prioritas:
     * 1. BINARY env  → nama binary, cek via exec
     * 2. PATH env    → full path, cek file_exists
     * 3. Fallback list → nama binary / path kandidat
     */
    private function resolveBinary(string $binaryEnv, string $pathEnv, array $fallbacks): string
    {
        // 1. BINARY dari .env (nama binary saja, contoh: soffice.exe)
        if ($binaryEnv !== '') {
            $candidate = $this->normalizeBinaryValue($binaryEnv);
            if ($candidate !== '' && $this->binaryExists($candidate)) {
                return $candidate;
            }
        }

        // 2. PATH dari .env (full path)
        if ($pathEnv !== '') {
            $cleaned = $this->normalizeBinaryValue($pathEnv);
            if ($cleaned !== '' && file_exists($cleaned)) {
                return $cleaned;
            }
        }

        // 3. Fallback: coba nama binary / path kandidat
        foreach ($fallbacks as $candidate) {
            $candidate = $this->normalizeBinaryValue((string) $candidate);
            if ($candidate !== '' && $this->binaryExists($candidate)) {
                return $candidate;
            }
        }

        return '';
    }

    /**
     * Cek apakah binary bisa dijalankan.
     */
    private function binaryExists(string $binary): bool
    {
        $binary = $this->normalizeBinaryValue($binary);

        if ($binary === '') {
            return false;
        }

        if ($this->looksLikePath($binary)) {
            return is_file($binary) && is_readable($binary);
        }

        $probeCmd = $this->isWindows
            ? 'where ' . $binary . ' >NUL 2>NUL'
            : 'command -v ' . escapeshellarg($binary) . ' >/dev/null 2>&1';

        exec($probeCmd, $out, $code);
        if ($code === 0) {
            return true;
        }

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
       PROCESS — entry point
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

        $type      = $request->input('conversion_type');
        $file      = $request->file('file');
        $sessionId = Str::uuid()->toString();

        $this->lazyCleanup();

        try {
            $outputFiles = match (true) {
                in_array($type, ['jpg_to_pdf', 'png_to_pdf', 'webp_to_pdf'])
                    => $this->imageToPdf($file, $sessionId),

                in_array($type, ['word_to_pdf', 'excel_to_pdf', 'ppt_to_pdf'])
                    => $this->officeToPdf($file, $sessionId),

                in_array($type, ['pdf_to_jpg', 'pdf_to_png'])
                    => $this->pdfToImage($file, $sessionId, $type === 'pdf_to_jpg' ? 'jpg' : 'png'),

                in_array($type, ['pdf_to_word', 'pdf_to_excel', 'pdf_to_ppt'])
                    => $this->pdfToOffice($file, $sessionId, match ($type) {
                        'pdf_to_word'  => 'docx',
                        'pdf_to_excel' => 'xlsx',
                        'pdf_to_ppt'   => 'pptx',
                    }),

                in_array($type, [
                    'jpg_to_png', 'png_to_jpg', 'jpg_to_webp',
                    'png_to_webp', 'webp_to_jpg', 'webp_to_png',
                ]) => $this->convertImage($file, $sessionId, $type),

                $type === 'pdf_compress'
                    => $this->compressPdf($file, $sessionId),

                default => throw new \Exception("Tipe konversi tidak didukung: {$type}"),
            };

            return response()->json([
                'success' => true,
                'files'   => $outputFiles,
                'session' => $sessionId,
            ]);

        } catch (\Exception $e) {
            Log::error("FileConverter [{$type}]: " . $e->getMessage(), [
                'file'  => $file->getClientOriginalName(),
                'trace' => substr($e->getTraceAsString(), 0, 800),
            ]);

            return response()->json([
                'success' => false,
                'message' => $this->friendlyError($type, $e->getMessage()),
            ], 422);
        }
    }

    /* =========================================================
       1. IMAGE → PDF  (PHP GD + FPDF)
    ========================================================= */
    private function imageToPdf($file, string $sessionId): array
    {
        $this->loadFpdf();

        $ext     = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $tmpName = "{$sessionId}_input.{$ext}";
        $tmpPath = $this->storageDir . DS . $tmpName;
        $file->move($this->storageDir, $tmpName);

        try {
            $imgInfo = @getimagesize($tmpPath);
            if (!$imgInfo || !$imgInfo[0]) {
                throw new \Exception("File gambar tidak valid atau rusak.");
            }

            [$imgW, $imgH] = $imgInfo;
            $mime = $imgInfo['mime'];

            $gd = match (true) {
                str_contains($mime, 'jpeg') => imagecreatefromjpeg($tmpPath),
                str_contains($mime, 'png')  => imagecreatefrompng($tmpPath),
                str_contains($mime, 'webp') => function_exists('imagecreatefromwebp')
                    ? imagecreatefromwebp($tmpPath)
                    : throw new \Exception("WebP tidak didukung di PHP ini."),
                str_contains($mime, 'gif')  => imagecreatefromgif($tmpPath),
                str_contains($mime, 'bmp')  => imagecreatefrombmp($tmpPath),
                default => throw new \Exception("Format gambar tidak didukung: {$mime}"),
            };

            if (!$gd) throw new \Exception("Gagal membaca file gambar.");

            // Flatten ke canvas putih (PNG transparan → putih)
            $canvas = imagecreatetruecolor($imgW, $imgH);
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            imagecopy($canvas, $gd, 0, 0, 0, 0, $imgW, $imgH);
            imagedestroy($gd);

            $cleanPath = $this->storageDir . DS . "{$sessionId}_clean.jpg";
            imagejpeg($canvas, $cleanPath, 92);
            imagedestroy($canvas);

            // Skala ke A4 (margin 10mm, area 190×277 mm)
            $maxW = 190; $maxH = 277;
            $mmW  = $imgW * 25.4 / 96;
            $mmH  = $imgH * 25.4 / 96;
            if ($mmW > $maxW || $mmH > $maxH) {
                $scale = min($maxW / $mmW, $maxH / $mmH);
                $mmW  *= $scale;
                $mmH  *= $scale;
            }

            $pdf = new \FPDF('P', 'mm', 'A4');
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(false);
            $pdf->AddPage();
            $pdf->Image($cleanPath, 10, 10, $mmW, $mmH, 'JPEG');

            $outName = "{$sessionId}.pdf";
            $pdf->Output('F', $this->storageDir . DS . $outName);
            @unlink($cleanPath);

            return [$outName];

        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       2. OFFICE → PDF  (LibreOffice)
    ========================================================= */
    private function officeToPdf($file, string $sessionId): array
    {
        $this->requireSoffice();

        $ext     = strtolower($file->getClientOriginalExtension() ?: 'docx');
        $tmpName = "{$sessionId}_input.{$ext}";
        $tmpPath = $this->storageDir . DS . $tmpName;
        $file->move($this->storageDir, $tmpName);

        try {
            $outputPath = $this->runLibreOffice($tmpPath, 'pdf', $sessionId);
            $outName    = "{$sessionId}.pdf";
            @rename($outputPath, $this->storageDir . DS . $outName);
            return [$outName];
        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       3. PDF → IMAGE  (GS → Imagick → LO)
    ========================================================= */
    private function pdfToImage($file, string $sessionId, string $fmt): array
    {
        $tmpName = "{$sessionId}_input.pdf";
        $tmpPath = $this->storageDir . DS . $tmpName;
        $file->move($this->storageDir, $tmpName);

        try {
            // Strategi 1: Ghostscript (paling cepat & reliable)
            if ($this->gsbin !== '') {
                try {
                    return $this->pdfToImageGhostscript($tmpPath, $sessionId, $fmt);
                } catch (\Exception $e) {
                    Log::warning("GS PDF→IMG gagal, coba Imagick: " . $e->getMessage());
                }
            }

            // Strategi 2: PHP Imagick extension
            if (extension_loaded('imagick')) {
                try {
                    return $this->pdfToImageImagick($tmpPath, $sessionId, $fmt);
                } catch (\Exception $e) {
                    Log::warning("Imagick PDF→IMG gagal: " . $e->getMessage());
                }
            }

            // Strategi 3: LibreOffice (halaman pertama saja)
            if ($this->sofficeBin !== '') {
                return $this->pdfToImageViaLibreOffice($tmpPath, $sessionId, $fmt);
            }

            throw new \Exception(
                "Tidak ada tool tersedia untuk PDF → Gambar. " .
                "Install Ghostscript: sudo apt install ghostscript " .
                "atau set GHOSTSCRIPT_BINARY=gswin64c.exe di .env (Windows)."
            );
        } finally {
            @unlink($tmpPath);
        }
    }

    private function pdfToImageGhostscript(string $pdfPath, string $sessionId, string $fmt): array
    {
        $device     = ($fmt === 'png') ? 'png16m' : 'jpeg';
        $outPattern = $this->storageDir . DS . "{$sessionId}_p%d.{$fmt}";

        // Pola command sama persis dengan PDFUtilitiesController
        if ($this->isWindows) {
            $cmd = sprintf(
                '"%s" -dNOPAUSE -dBATCH -dSAFER -dQUIET -sDEVICE=%s -r200 ' .
                '-dFirstPage=1 -dLastPage=250 -sOutputFile="%s" "%s" 2>&1',
                $this->gsbin,
                $device,
                $outPattern,
                $pdfPath
            );
        } else {
            $cmd = sprintf(
                '%s -dNOPAUSE -dBATCH -dSAFER -dQUIET -sDEVICE=%s -r200 ' .
                '-dFirstPage=1 -dLastPage=250 -sOutputFile=%s %s 2>&1',
                escapeshellcmd($this->gsbin),
                escapeshellarg($device),
                escapeshellarg($outPattern),
                escapeshellarg($pdfPath)
            );
        }

        exec($cmd, $output, $exitCode);

        $outputFiles = [];
        $files = glob($this->storageDir . DS . "{$sessionId}_p*.{$fmt}") ?: [];
        natsort($files);

        foreach ($files as $f) {
            if (file_exists($f) && filesize($f) > 0) {
                $outputFiles[] = basename($f);
            }
        }


        if (empty($outputFiles)) {
            throw new \Exception(
                "Ghostscript PDF→Gambar gagal (exit {$exitCode}). " .
                implode(' | ', array_slice($output, 0, 3))
            );
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

        $imagick->clear();
        $imagick->destroy();

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
       4. PDF → OFFICE  (LibreOffice multi-strategi)
    ========================================================= */
    private function extractPdfTextSmart(string $pdfPath, string $sessionId): string
    {
        // A. Coba parser PHP dulu
        try {
            if (class_exists(\Smalot\PdfParser\Parser::class)) {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf    = $parser->parseFile($pdfPath);
                $text   = trim($pdf->getText());

                if (mb_strlen(preg_replace('/\s+/u', '', $text)) > 30) {
                    return $text;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('extractPdfTextSmart parser gagal: ' . $e->getMessage());
        }

        // B. OCR fallback kalau PDF memang scan
        try {
            $text = $this->pdfToTextOCR($pdfPath, $sessionId);
            if (trim($text) !== '') {
                return $text;
            }
        } catch (\Throwable $e) {
            Log::warning('extractPdfTextSmart OCR gagal: ' . $e->getMessage());
        }

        return '';
    }

    private function createDocxFormatted(string $text, string $outputPath)
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        $section = $phpWord->addSection([
            'marginTop' => 800,
            'marginBottom' => 800,
            'marginLeft' => 800,
            'marginRight' => 800,
        ]);

        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                $section->addTextBreak();
                continue;
            }

            // detect heading
            if (strlen($line) < 50 && strtoupper($line) === $line) {
                $section->addText($line, ['bold' => true, 'size' => 14]);
            } else {
                $section->addText($line, ['size' => 11]);
            }
        }

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($outputPath);
    }

    private function createExcelFromText(string $text, string $outputPath)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $rows = explode("\n", $text);
        $rowIndex = 1;

        foreach ($rows as $row) {
            $cols = preg_split('/\s+/', trim($row));

            $colIndex = 1;
            foreach ($cols as $col) {

                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;

                $sheet->setCellValue($cell, $col);
                $colIndex++;
            }

            $rowIndex++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($outputPath);
    }

    /* =========================================================
       4. PDF TO OFFICE
    ========================================================= */
    private function pdfToOffice($file, string $sessionId, string $targetExt): array
    {
        $this->requireSoffice();

        $tmpName = "{$sessionId}_input.pdf";
        $tmpPath = $this->storageDir . DS . $tmpName;
        $file->move($this->storageDir, $tmpName);

        try {
            // ===============================
            // 1. DETECT PDF TYPE
            // ===============================
            $hasText = $this->pdfHasTextLayer($tmpPath);

            // ===============================
            // 2. JIKA SCAN → OCR DULU
            // ===============================
            if (!$hasText) {
                Log::info("PDF scan detected → OCR...");

                $ocrPdf = $this->storageDir . DS . "{$sessionId}_ocr.pdf";

                if ($this->runOcrmypdf($tmpPath, $ocrPdf)) {
                    $tmpPath = $ocrPdf;
                }
            }

            // ===============================
            // 3. STRATEGI 1 (BEST)
            // ===============================
            $result = $this->tryPdfToOfficeInfilter($tmpPath, $sessionId, $targetExt);
            if ($result) {
                return [$this->finalizeOutput($result, $file, $targetExt)];
            }

            // ===============================
            // 4. STRATEGI 2 (AUTO)
            // ===============================
            $result = $this->tryPdfToOfficeAutoDetect($tmpPath, $sessionId, $targetExt);
            if ($result) {
                return [$this->finalizeOutput($result, $file, $targetExt)];
            }

            // ===============================
            // 5. STRATEGI 3 (ODT BRIDGE)
            // ===============================
            if ($targetExt === 'docx') {
                $result = $this->tryPdfViaOdt($tmpPath, $sessionId);
                if ($result) {
                    return [$this->finalizeOutput($result, $file, 'docx')];
                }
            }

            // ===============================
            // 6. STRATEGI 4 (TEXT PARSER)
            // ===============================
            $text = $this->extractPdfTextSmart($tmpPath, $sessionId);

            if (trim($text) !== '') {

                if ($targetExt === 'docx') {
                    $out = $this->storageDir . DS . "{$sessionId}.docx";
                    $this->createDocxFormatted($text, $out);
                    return [$this->finalizeOutput($out, $file, 'docx')];
                }

                if ($targetExt === 'xlsx') {
                    $out = $this->storageDir . DS . "{$sessionId}.xlsx";
                    $this->createExcelFromText($text, $out);
                    return [$this->finalizeOutput($out, $file, 'xlsx')];
                }
            }

            throw new \Exception("PDF → {$targetExt} gagal total.");

        } finally {
            @unlink($tmpPath);
        }
    }


    private function tryPdfToOfficeInfilter(string $inputPath, string $sessionId, string $targetExt): ?string
    {
        $filterMap = [
            'docx' => ['writer_pdf_import',  'MS Word 2007 XML',               'docx'],
            'xlsx' => ['calc_pdf_import',     'Calc MS Excel 2007 XML',         'xlsx'],
            'pptx' => ['impress_pdf_import',  'Impress MS PowerPoint 2007 XML', 'pptx'],
        ];

        if (!isset($filterMap[$targetExt])) return null;

        [$infilter, $outfilter, $ext] = $filterMap[$targetExt];
        $profileDir = $this->storageDir . DS . "lo_{$sessionId}_s1";
        @mkdir($profileDir, 0777, true);

        $cmd = $this->buildLoCmd(
            $inputPath, $profileDir, $sessionId,
            "{$ext}:\"{$outfilter}\"",
            $infilter
        );

        exec($cmd, $lines, $code);
        $this->removeDir($profileDir);
        Log::info("PDF→Office S1 [{$targetExt}] exit={$code} output=" . implode('|', array_slice($lines, 0, 3)));

        return $this->findOutputFile($inputPath, $targetExt);
    }

    private function tryPdfToOfficeAutoDetect(string $inputPath, string $sessionId, string $targetExt): ?string
    {
        $filterMap = [
            'docx' => 'docx:"MS Word 2007 XML"',
            'xlsx' => 'xlsx:"Calc MS Excel 2007 XML"',
            'pptx' => 'pptx:"Impress MS PowerPoint 2007 XML"',
        ];

        $profileDir = $this->storageDir . DS . "lo_{$sessionId}_s2";
        @mkdir($profileDir, 0777, true);

        $cmd = $this->buildLoCmd(
            $inputPath, $profileDir, $sessionId,
            $filterMap[$targetExt] ?? $targetExt
        );

        exec($cmd, $lines, $code);
        $this->removeDir($profileDir);
        Log::info("PDF→Office S2 [{$targetExt}] exit={$code}");

        return $this->findOutputFile($inputPath, $targetExt);
    }

    private function tryPdfViaOdt(string $inputPath, string $sessionId): ?string
    {
        // Langkah A: PDF → ODT
        $profileA = $this->storageDir . DS . "lo_{$sessionId}_s3a";
        @mkdir($profileA, 0777, true);
        $cmdA = $this->buildLoCmd($inputPath, $profileA, $sessionId, 'odt', 'writer_pdf_import');
        exec($cmdA, $outA, $codeA);
        $this->removeDir($profileA);

        $odtFile = $this->findOutputFile($inputPath, 'odt');
        if (!$odtFile) { Log::warning("PDF→ODT gagal (S3A)"); return null; }

        // Langkah B: ODT → DOCX
        $profileB = $this->storageDir . DS . "lo_{$sessionId}_s3b";
        @mkdir($profileB, 0777, true);
        $cmdB = $this->buildLoCmd($odtFile, $profileB, $sessionId, 'docx:"MS Word 2007 XML"');
        exec($cmdB, $outB, $codeB);
        $this->removeDir($profileB);
        @unlink($odtFile);

        $result = $this->storageDir . DS . pathinfo($odtFile, PATHINFO_FILENAME) . '.docx';
        Log::info("PDF→Office S3 result={$result} exists=" . (int) file_exists($result));

        return (file_exists($result) && filesize($result) > 0) ? $result : null;
    }

    /* =========================================================
       5. IMAGE → IMAGE  (PHP GD)
    ========================================================= */
    private function convertImage($file, string $sessionId, string $type): array
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
                    : throw new \Exception("WebP tidak didukung GD di PHP ini."),
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

            $outName = "{$sessionId}.{$outFmt}";
            $ok = match ($outFmt) {
                'png'  => imagepng($canvas,  $this->storageDir . DS . $outName, 6),
                'webp' => imagewebp($canvas, $this->storageDir . DS . $outName, 90),
                default=> imagejpeg($canvas, $this->storageDir . DS . $outName, 95),
            };
            imagedestroy($canvas);

            if (!$ok) throw new \Exception("Gagal menyimpan gambar output.");
            return [$outName];

        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       6. PDF COMPRESS  (Ghostscript — sama dengan PDFUtilities)
    ========================================================= */
    private function compressPdf($file, string $sessionId): array
    {
        if ($this->gsbin === '') {
            throw new \Exception(
                "Ghostscript tidak tersedia. " .
                "Set GHOSTSCRIPT_BINARY=gswin64c.exe (Windows) atau GHOSTSCRIPT_BINARY=gs (Linux) di .env"
            );
        }

        $tmpName = "{$sessionId}_input.pdf";
        $tmpPath = $this->storageDir . DS . $tmpName;
        $file->move($this->storageDir, $tmpName);

        try {
            $outName = "{$sessionId}_compressed.pdf";
            $outPath = $this->storageDir . DS . $outName;

            if ($this->isWindows) {
                $cmd = sprintf(
                    '"%s" -dNOPAUSE -dBATCH -dSAFER -dQUIET -sDEVICE=pdfwrite ' .
                    '-dCompatibilityLevel=1.4 -dPDFSETTINGS=/ebook ' .
                    '-dEmbedAllFonts=true -dSubsetFonts=true ' .
                    '-dColorImageResolution=150 -dGrayImageResolution=150 ' .
                    '-sOutputFile="%s" "%s" 2>&1',
                    $this->gsbin, $outPath, $tmpPath
                );
            } else {
                $cmd = sprintf(
                    '%s -dNOPAUSE -dBATCH -dSAFER -dQUIET -sDEVICE=pdfwrite ' .
                    '-dCompatibilityLevel=1.4 -dPDFSETTINGS=/ebook ' .
                    '-dEmbedAllFonts=true -dSubsetFonts=true ' .
                    '-dColorImageResolution=150 -dGrayImageResolution=150 ' .
                    '-sOutputFile=%s %s 2>&1',
                    escapeshellcmd($this->gsbin),
                    escapeshellarg($outPath),
                    escapeshellarg($tmpPath)
                );
            }

            exec($cmd, $output, $exitCode);

            if ($exitCode !== 0 || !file_exists($outPath) || filesize($outPath) === 0) {
                throw new \Exception("GS compress gagal (exit {$exitCode}): " . implode(' | ', array_slice($output, 0, 3)));
            }

            return [$outName];
        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       CORE: Jalankan LibreOffice
    ========================================================= */
    private function runLibreOffice(string $inputPath, string $targetExt, string $sessionId): string
    {
        $profileDir = $this->storageDir . DS . "lo_{$sessionId}_run";
        @mkdir($profileDir, 0777, true);

        $filterMap = [
            'pdf'  => 'pdf',
            'docx' => 'docx:"MS Word 2007 XML"',
            'xlsx' => 'xlsx:"Calc MS Excel 2007 XML"',
            'pptx' => 'pptx:"Impress MS PowerPoint 2007 XML"',
            'odt'  => 'odt',
            'png'  => 'png',
        ];

        $filter = $filterMap[$targetExt] ?? $targetExt;
        $cmd    = $this->buildLoCmd($inputPath, $profileDir, $sessionId, $filter);

        $outputLines = [];
        exec($cmd, $outputLines, $exitCode);
        $output = implode("\n", $outputLines);
        $this->removeDir($profileDir);

        $outputFile = $this->findOutputFile($inputPath, $targetExt);

        if (!$outputFile) {
            Log::error("LibreOffice gagal", [
                'cmd'       => $cmd,
                'exit'      => $exitCode,
                'output'    => substr($output, 0, 400),
                'soffice'   => $this->sofficeBin,
                'inputPath' => $inputPath,
            ]);
            throw new \Exception(
                "Konversi LibreOffice gagal (exit {$exitCode}). " .
                "Pastikan file tidak terproteksi. Detail: " . substr($output, 0, 150)
            );
        }

        return $outputFile;
    }

    /**
     * Build command LibreOffice
     *
     * WINDOWS — tidak pakai env var HOME= (tidak dikenal CMD.exe)
     *   - Bungkus semua path dengan double-quote
     *   - UserInstallation: file:///C:/path/to/dir
     *
     * LINUX — harus set HOME= & XDG_CACHE_HOME= agar LO punya dir writable
     *   - SAL_USE_VCLPLUGIN=svp untuk headless
     */
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
                '--headless',
                '--norestore',
                '--nofirststartwizard',
                '--nolockcheck',
                '-env:UserInstallation=' . $profileUri,
            ];

            if ($infilter) {
                $parts[] = '--infilter=' . $infilter;
            }

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
            'HOME=' . escapeshellarg($homeDir),
            'XDG_CACHE_HOME=' . escapeshellarg('/tmp/lo_cache_' . $sessionId),
            'SAL_USE_VCLPLUGIN=svp',
            escapeshellcmd($this->sofficeBin),
            '--headless',
            '--norestore',
            '--nofirststartwizard',
            '--nolockcheck',
            '-env:UserInstallation=' . escapeshellarg($profileUri),
        ];

        if ($infilter) {
            $parts[] = '--infilter=' . escapeshellarg($infilter);
        }

        $parts[] = '--convert-to';
        $parts[] = escapeshellarg($convertTo);
        $parts[] = '--outdir';
        $parts[] = escapeshellarg($this->storageDir);
        $parts[] = escapeshellarg($inputPath);
        $parts[] = '2>&1';

        return implode(' ', $parts);
    }

    /* =========================================================
       HELPERS
    ========================================================= */

    /**
     * Cari file output LibreOffice.
     *
     * LO menulis output berdasarkan nama file INPUT:
     * {uuid}_input.docx → {uuid}_input.pdf
     * Bukan berdasarkan sessionId.
     */
    private function convertPdfWithLibreOffice(string $inputPath, string $sessionId, string $targetExt): ?string
    {
        $profileDir = $this->storageDir . DS . "lo_{$sessionId}_run";
        @mkdir($profileDir, 0777, true);

        $convertMap = [
            'docx' => 'docx:"MS Word 2007 XML"',
            'xlsx' => 'xlsx:"Calc MS Excel 2007 XML"',
            'pptx' => 'pptx:"Impress MS PowerPoint 2007 XML"',
        ];

        if (!isset($convertMap[$targetExt])) {
            $this->removeDir($profileDir);
            return null;
        }

        $cmd = $this->buildLoCmd($inputPath, $profileDir, $sessionId, $convertMap[$targetExt]);

        $outputLines = [];
        $exitCode = 0;
        exec($cmd, $outputLines, $exitCode);

        $this->removeDir($profileDir);

        Log::info("PDF→Office [{$targetExt}] exit={$exitCode} output=" . implode(' | ', array_slice($outputLines, 0, 3)));

        // tunggu sebentar kalau file baru saja ditulis
        usleep(300000);

        $found = $this->findNewestFileByExt($targetExt);
        return $found ?: null;
    }

    private function findNewestFileByExt(string $ext): ?string
    {
        $files = glob($this->storageDir . DS . '*.' . $ext) ?: [];

        if (empty($files)) {
            return null;
        }

        usort($files, function ($a, $b) {
            return filemtime($b) <=> filemtime($a);
        });

        return (file_exists($files[0]) && filesize($files[0]) > 0) ? $files[0] : null;
    }

    private function findOutputFile(string $inputPath, string $targetExt): ?string
    {
        $files = glob($this->storageDir . DS . '*.' . $targetExt) ?: [];

        if (empty($files)) return null;

        usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a));

        foreach ($files as $f) {
            if (filesize($f) > 0) return $f;
        }

        return null;
    }

    private function finalizeOutput(string $path, $file, string $ext): string
    {
        if (!file_exists($path) || filesize($path) === 0) {
            throw new \Exception("Output kosong / gagal.");
        }

        $finalName = $this->generateOutputName(
            $file->getClientOriginalName(),
            $ext
        );

        rename($path, $this->storageDir . DS . $finalName);

        return $finalName;
    }

    private function requireSoffice(): void
    {
        if ($this->sofficeBin === '') {
            $hint = $this->isWindows
                ? 'Tambahkan "C:\Program Files\LibreOffice\program" ke PATH Windows, ' .
                  'lalu set LIBREOFFICE_BINARY=soffice.exe di .env'
                : 'sudo apt install libreoffice, lalu set LIBREOFFICE_BINARY=soffice di .env';
            throw new \Exception("LibreOffice tidak ditemukan. {$hint}");
        }
    }

    private function loadFpdf(): void
    {
        $candidates = [
            base_path('vendor/setasign/fpdf/fpdf.php'),
            base_path('vendor/fpdf/fpdf/src/Fpdf/Fpdf.php'),
            app_path('Libraries/fpdf/fpdf.php'),
        ];
        foreach ($candidates as $p) {
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
                => " Tip: Pastikan PDF tidak terproteksi & memiliki teks yang bisa dipilih (bukan scan).",
            in_array($type, ['word_to_pdf', 'excel_to_pdf', 'ppt_to_pdf'])
                => " Tip: Pastikan file Office tidak terproteksi dan formatnya valid.",
            default => "",
        };

        $clean = preg_replace('/env:UserInstallation=\S+/', '', $raw) ?? $raw;
        $clean = preg_replace('/HOME=\S+/', '', $clean) ?? $clean;
        $clean = preg_replace('/XDG_\w+=\S+/', '', $clean) ?? $clean;
        $clean = substr(trim($clean), 0, 250);

        return $clean . $tips;
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

    private function runTesseractOnImage(string $imagePath, string $outBase, string $lang = 'eng+ind'): string
    {
        $bin = env('TESSERACT_BINARY', 'tesseract');
        $bin = $this->normalizeBinaryValue($bin);

        if ($bin === '') {
            throw new \Exception('Tesseract tidak ditemukan.');
        }

        if ($this->isWindows) {
            $cmd = sprintf(
                '"%s" "%s" "%s" -l %s 2>&1',
                $bin,
                $imagePath,
                $outBase,
                $lang
            );
        } else {
            $cmd = sprintf(
                '%s %s %s -l %s 2>&1',
                escapeshellcmd($bin),
                escapeshellarg($imagePath),
                escapeshellarg($outBase),
                $lang
            );
        }

        $output = [];
        $code   = 0;
        exec($cmd, $output, $code);

        $txtFile = $outBase . '.txt';
        if ($code !== 0 || !file_exists($txtFile)) {
            throw new \Exception('OCR gambar gagal: ' . implode(' | ', array_slice($output, 0, 3)));
        }

        return file_get_contents($txtFile) ?: '';
    }


    private function pdfToTextOCR(string $pdfPath, string $sessionId): string
    {
        // Render PDF ke PNG dulu, lalu OCR per halaman
        $pages = $this->pdfToImageGhostscript($pdfPath, $sessionId, 'png');

        $blocks = [];
        foreach ($pages as $idx => $pageFile) {
            $imgPath = $this->storageDir . DS . $pageFile;
            $outBase = $this->storageDir . DS . "{$sessionId}_ocr_p" . ($idx + 1);

            $text = $this->runTesseractOnImage($imgPath, $outBase);
            $text = trim($text);

            if ($text !== '') {
                $blocks[] = $text;
            }

            @unlink($imgPath);
            @unlink($outBase . '.txt');
        }

        return trim(implode("\n\n", $blocks));
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
        if ($bin === '') {
            return false;
        }

        if ($this->isWindows) {
            $cmd = sprintf(
                '"%s" --skip-text --force-ocr --language eng+ind "%s" "%s" 2>&1',
                $bin,
                $inputPdf,
                $outputPdf
            );
        } else {
            $cmd = sprintf(
                '%s --skip-text --force-ocr --language eng+ind %s %s 2>&1',
                escapeshellcmd($bin),
                escapeshellarg($inputPdf),
                escapeshellarg($outputPdf)
            );
        }

        $out = [];
        $code = 0;
        exec($cmd, $out, $code);

        return $code === 0 && file_exists($outputPdf) && filesize($outputPdf) > 0;
    }



    private function createDocxFromText(string $text, string $outputPath)
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();

        foreach (explode("\n", $text) as $line) {
            $section->addText(trim($line));
        }

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($outputPath);
    }

    private function pdfHasTextLayer(string $pdfPath): bool
    {
        // Prioritas: parser PHP (lebih aman di Windows)
        try {
            if (class_exists(\Smalot\PdfParser\Parser::class)) {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf    = $parser->parseFile($pdfPath);
                $text   = trim($pdf->getText());

                return mb_strlen(preg_replace('/\s+/u', '', $text)) > 30;
            }
        } catch (\Throwable $e) {
            Log::warning('PdfParser gagal: ' . $e->getMessage());
        }

        // Fallback: pdftotext kalau ada
        $tmpTxt = $this->storageDir . DS . 'probe_' . Str::random(8) . '.txt';

        if ($this->isWindows) {
            $cmd = 'pdftotext -q "' . $pdfPath . '" "' . $tmpTxt . '" 2>&1';
        } else {
            $cmd = 'pdftotext -q ' . escapeshellarg($pdfPath) . ' ' . escapeshellarg($tmpTxt) . ' 2>&1';
        }

        @exec($cmd, $out, $code);

        if (!file_exists($tmpTxt)) {
            return false;
        }

        $text = trim(@file_get_contents($tmpTxt) ?: '');
        @unlink($tmpTxt);

        return mb_strlen(preg_replace('/\s+/u', '', $text)) > 30;
    }

    /* =========================================================
       DOWNLOAD
    ========================================================= */
    private function generateOutputName($originalName, $ext)
    {
        $base = pathinfo($originalName, PATHINFO_FILENAME);
        $base = Str::slug($base, '_'); // aman

        return "{$base}_by_mediatools.{$ext}";
    }

    public function download(string $filename)
    {
        $filename = basename($filename);

        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
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
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'webp' => 'image/webp',
        ];

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return response()->download($path, $filename, [
            'Content-Type'        => $mimes[$ext] ?? 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
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
    }

    /* =========================================================
       DEBUG — tambah route sementara untuk troubleshoot:
       Route::get('/file-converter/debug', [FileConverterController::class, 'debug']);
       Akses: http://localhost/file-converter/debug
       Hapus route ini setelah selesai!
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
                'LIBREOFFICE_PATH'   => env('LIBREOFFICE_PATH',   '(not set)'),
                'GHOSTSCRIPT_BINARY' => env('GHOSTSCRIPT_BINARY', '(not set)'),
                'GHOSTSCRIPT_PATH'   => env('GHOSTSCRIPT_PATH',   '(not set)'),
            ],
            'resolved' => [
                'sofficeBin' => $this->sofficeBin ?: '❌ NOT FOUND',
                'gsbin'      => $this->gsbin      ?: '❌ NOT FOUND',
            ],
            'exec_test' => [
                'soffice' => implode(' ', $sofTest) ?: '(no output / not found)',
                'gs'      => implode(' ', $gsTest)  ?: '(no output / not found)',
            ],
            'php_ext' => [
                'gd'      => extension_loaded('gd'),
                'imagick' => extension_loaded('imagick'),
            ],
            'storage' => [
                'path'     => $this->storageDir,
                'writable' => is_writable($this->storageDir),
            ],
            'fpdf' => $this->checkFpdf(),
        ]);
    }

    private function checkFpdf(): string
    {
        foreach ([
            base_path('vendor/setasign/fpdf/fpdf.php'),
            base_path('vendor/fpdf/fpdf/src/Fpdf/Fpdf.php'),
        ] as $p) {
            if (file_exists($p)) return "✅ Found: {$p}";
        }
        return '❌ NOT FOUND — run: composer require setasign/fpdf';
    }
}