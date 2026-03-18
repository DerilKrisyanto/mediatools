<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class FileConverterController extends Controller
{
    private string $storageDir;
    private string $sofficeBin = '/usr/bin/soffice';

    public function __construct()
    {
        $this->storageDir = storage_path('app/file_converter');
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    /* =========================================================
       INDEX
    ========================================================= */
    public function index()
    {
        return view('tools.fileconverter.index');
    }

    /* =========================================================
       PROCESS
    ========================================================= */
    public function process(Request $request)
    {
        $request->validate([
            'file'            => 'required|file|max:51200',
            'conversion_type' => 'required|string|in:jpg_to_pdf,png_to_pdf,word_to_pdf,excel_to_pdf,ppt_to_pdf,pdf_to_jpg,pdf_to_png,pdf_to_word,pdf_to_excel,pdf_to_ppt,jpg_to_png,png_to_jpg,jpg_to_webp,png_to_webp,webp_to_jpg,webp_to_png',
        ]);

        $type      = $request->input('conversion_type');
        $file      = $request->file('file');
        $sessionId = Str::uuid()->toString();

        // Lazy cleanup — hapus file > 30 menit
        $this->lazyCleanup();

        try {
            $outputFiles = match ($type) {
                'jpg_to_pdf', 'png_to_pdf' => $this->imageToPdf($file, $sessionId),
                'word_to_pdf'              => $this->officeToPdf($file, $sessionId),
                'excel_to_pdf'             => $this->officeToPdf($file, $sessionId),
                'ppt_to_pdf'               => $this->officeToPdf($file, $sessionId),
                'pdf_to_jpg'               => $this->pdfToImage($file, $sessionId, 'jpg'),
                'pdf_to_png'               => $this->pdfToImage($file, $sessionId, 'png'),
                'pdf_to_word'              => $this->pdfToOffice($file, $sessionId, 'docx'),
                'pdf_to_excel'             => $this->pdfToOffice($file, $sessionId, 'xlsx'),
                'pdf_to_ppt'               => $this->pdfToOffice($file, $sessionId, 'pptx'),
                'jpg_to_png', 'png_to_jpg',
                'jpg_to_webp', 'png_to_webp',
                'webp_to_jpg', 'webp_to_png' => $this->convertImage($file, $sessionId, $type),
                default => throw new \Exception("Tipe konversi tidak didukung."),
            };

            return response()->json([
                'success' => true,
                'files'   => $outputFiles,
                'session' => $sessionId,
            ]);

        } catch (\Exception $e) {
            Log::error("FileConverter [{$type}]: " . $e->getMessage(), [
                'file'  => $file->getClientOriginalName(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
       1. IMAGE → PDF (GD + FPDF)
         Tidak butuh LibreOffice — langsung via PHP
    ========================================================= */
    private function imageToPdf($file, string $sessionId): array
    {
        $this->loadFpdf();

        $ext     = strtolower($file->getClientOriginalExtension());
        $tmpName = "{$sessionId}_input.{$ext}";
        $tmpPath = $this->storageDir . "/{$tmpName}";
        $file->move($this->storageDir, $tmpName);

        try {
            $imgInfo = @getimagesize($tmpPath);
            if (!$imgInfo || !$imgInfo[0]) {
                throw new \Exception("File gambar tidak valid atau rusak.");
            }

            [$imgW, $imgH] = $imgInfo;
            $mime = $imgInfo['mime'];

            // Load via GD untuk normalisasi
            $gd = match (true) {
                str_contains($mime, 'jpeg') => imagecreatefromjpeg($tmpPath),
                str_contains($mime, 'png')  => imagecreatefrompng($tmpPath),
                str_contains($mime, 'webp') => imagecreatefromwebp($tmpPath),
                str_contains($mime, 'gif')  => imagecreatefromgif($tmpPath),
                str_contains($mime, 'bmp')  => imagecreatefrombmp($tmpPath),
                default => throw new \Exception("Format gambar tidak didukung: {$mime}"),
            };

            if (!$gd) throw new \Exception("Gagal membaca file gambar.");

            // Canvas putih untuk transparansi PNG
            $canvas = imagecreatetruecolor($imgW, $imgH);
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            imagecopy($canvas, $gd, 0, 0, 0, 0, $imgW, $imgH);
            imagedestroy($gd);

            $cleanPath = $this->storageDir . "/{$sessionId}_clean.jpg";
            imagejpeg($canvas, $cleanPath, 95);
            imagedestroy($canvas);

            // Hitung ukuran mm (96 DPI) fit ke A4
            $mmW   = $imgW * 25.4 / 96;
            $mmH   = $imgH * 25.4 / 96;
            $maxW  = 190;
            $maxH  = 277;

            if ($mmW > $maxW || $mmH > $maxH) {
                $scale = min($maxW / $mmW, $maxH / $mmH);
                $mmW  *= $scale;
                $mmH  *= $scale;
            }

            $pdf = new \FPDF('P', 'mm', 'A4');
            $pdf->SetMargins(10, 10, 10);
            $pdf->AddPage();
            $pdf->Image($cleanPath, 10, 10, $mmW, $mmH, 'JPEG');

            $outName = "{$sessionId}.pdf";
            $pdf->Output('F', $this->storageDir . "/{$outName}");

            @unlink($cleanPath);
            return [$outName];

        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       2. OFFICE → PDF (Word/Excel/PPT → PDF via LibreOffice)
          Kualitas sempurna — layout, font, warna semua preserved
    ========================================================= */
    private function officeToPdf($file, string $sessionId): array
    {
        $ext     = strtolower($file->getClientOriginalExtension());
        $tmpName = "{$sessionId}_input.{$ext}";
        $tmpPath = $this->storageDir . "/{$tmpName}";
        $file->move($this->storageDir, $tmpName);

        try {
            $outputPath = $this->runLibreOffice($tmpPath, 'pdf');

            $outName   = "{$sessionId}.pdf";
            $finalPath = $this->storageDir . "/{$outName}";
            rename($outputPath, $finalPath);

            return [$outName];

        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       3. PDF → IMAGE (Imagick — kualitas tinggi)
    ========================================================= */
    private function pdfToImage($file, string $sessionId, string $format = 'jpg'): array
    {
        if (!extension_loaded('imagick')) {
            throw new \Exception(
                "Ekstensi Imagick tidak aktif. Jalankan: apt install php8.4-imagick"
            );
        }

        $tmpName = "{$sessionId}_input.pdf";
        $tmpPath = $this->storageDir . "/{$tmpName}";
        $file->move($this->storageDir, $tmpName);

        try {
            $imagick = new \Imagick();
            $imagick->setResolution(200, 200); // Resolusi tinggi untuk kualitas bagus
            $imagick->readImage($tmpPath . '[0-29]'); // Maks 30 halaman
            $imagick->resetIterator();

            $imgFormat   = $format === 'png' ? 'png' : 'jpeg';
            $outputFiles = [];

            foreach ($imagick as $i => $page) {
                $page->setImageFormat($imgFormat);
                $page->setImageBackgroundColor('white');
                $page->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);

                if ($imgFormat === 'jpeg') {
                    $page->setImageCompressionQuality(92);
                }

                $fname = "{$sessionId}_p" . ($i + 1) . ".{$format}";
                $page->writeImage($this->storageDir . "/{$fname}");
                $outputFiles[] = $fname;
            }

            $imagick->clear();
            $imagick->destroy();

            if (empty($outputFiles)) {
                throw new \Exception("Tidak ada halaman yang berhasil dikonversi.");
            }

            return $outputFiles;

        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       4. PDF → OFFICE (PDF → Word/Excel/PPT via LibreOffice)
          LibreOffice mampu mengekstrak layout, tabel, teks
    ========================================================= */
    private function pdfToOffice($file, string $sessionId, string $targetExt): array
    {
        $tmpName = "{$sessionId}_input.pdf";
        $tmpPath = $this->storageDir . "/{$tmpName}";
        $file->move($this->storageDir, $tmpName);

        try {
            $outputPath = $this->runLibreOffice($tmpPath, $targetExt);

            $outName   = "{$sessionId}.{$targetExt}";
            $finalPath = $this->storageDir . "/{$outName}";
            rename($outputPath, $finalPath);

            return [$outName];

        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       5. IMAGE → IMAGE (GD — format conversion)
    ========================================================= */
    private function convertImage($file, string $sessionId, string $type): array
    {
        $ext     = strtolower($file->getClientOriginalExtension());
        $tmpName = "{$sessionId}_input.{$ext}";
        $tmpPath = $this->storageDir . "/{$tmpName}";
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
                'webp'        => imagecreatefromwebp($tmpPath),
                default       => throw new \Exception("Format sumber tidak didukung: {$ext}"),
            };

            if (!$src) throw new \Exception("Gagal membaca gambar.");

            $w      = imagesx($src);
            $h      = imagesy($src);
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
            $outPath = $this->storageDir . "/{$outName}";

            $ok = match ($outFmt) {
                'png'  => imagepng($canvas, $outPath, 6),
                'webp' => imagewebp($canvas, $outPath, 90),
                default=> imagejpeg($canvas, $outPath, 95),
            };

            imagedestroy($canvas);

            if (!$ok) throw new \Exception("Gagal menyimpan hasil konversi.");

            return [$outName];

        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       CORE: Jalankan LibreOffice Headless
       Ini jantung dari semua konversi dokumen berkualitas tinggi
    ========================================================= */
    private function runLibreOffice(string $inputPath, string $targetExt): string
    {
        // Validasi LibreOffice tersedia
        if (!file_exists($this->sofficeBin)) {
            // Coba path alternatif
            $altPaths = [
                '/usr/lib/libreoffice/program/soffice',
                '/opt/libreoffice/program/soffice',
            ];
            foreach ($altPaths as $path) {
                if (file_exists($path)) {
                    $this->sofficeBin = $path;
                    break;
                }
            }
            if (!file_exists($this->sofficeBin)) {
                throw new \Exception(
                    "LibreOffice tidak ditemukan. Install dengan: sudo apt install libreoffice"
                );
            }
        }

        // Profile unik per request — hindari konflik concurrent users
        $profileDir = $this->storageDir . "/lo_profile_{$this->getSessionFromPath($inputPath)}";
        @mkdir($profileDir, 0755, true);

        // Tentukan filter LibreOffice berdasarkan target format
        $filterMap = [
            'pdf'  => 'pdf',
            'docx' => 'docx:"MS Word 2007 XML"',
            'xlsx' => 'xlsx:"Calc MS Excel 2007 XML"',
            'pptx' => 'pptx:"Impress MS PowerPoint 2007 XML"',
        ];

        $filter = $filterMap[$targetExt] ?? $targetExt;

        $cmd = sprintf(
            '%s --headless --norestore --nofirststartwizard --nolockcheck ' .
            '-env:UserInstallation=file://%s ' .
            '--convert-to %s --outdir %s %s 2>&1',
            escapeshellcmd($this->sofficeBin),
            escapeshellarg($profileDir),
            escapeshellarg($filter),
            escapeshellarg($this->storageDir),
            escapeshellarg($inputPath)
        );

        $output   = '';
        $exitCode = 0;
        exec($cmd, $outputLines, $exitCode);
        $output = implode("\n", $outputLines);

        // Cleanup profile
        $this->removeDirectory($profileDir);

        // Cari file output yang dihasilkan LibreOffice
        $baseName   = pathinfo($inputPath, PATHINFO_FILENAME);
        $outputFile = $this->storageDir . "/{$baseName}.{$targetExt}";

        if (!file_exists($outputFile) || filesize($outputFile) === 0) {
            Log::error("LibreOffice conversion failed", [
                'cmd'      => $cmd,
                'output'   => $output,
                'exitCode' => $exitCode,
                'expected' => $outputFile,
            ]);
            throw new \Exception(
                "Konversi dokumen gagal. " .
                "Pastikan file tidak rusak dan format didukung. " .
                "Detail: " . substr($output, 0, 150)
            );
        }

        return $outputFile;
    }

    /* =========================================================
       HELPER: Load FPDF
    ========================================================= */
    private function loadFpdf(): void
    {
        $paths = [
            base_path('vendor/setasign/fpdf/fpdf.php'),
            app_path('Libraries/fpdf/fpdf.php'),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }

        throw new \Exception(
            "FPDF tidak ditemukan. Jalankan: composer require setasign/fpdf"
        );
    }

    /* =========================================================
       HELPER: Ambil session ID dari path file
    ========================================================= */
    private function getSessionFromPath(string $path): string
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);
        // Ambil UUID dari nama file (format: uuid_input)
        return explode('_', $filename)[0] ?? Str::random(8);
    }

    /* =========================================================
       HELPER: Hapus direktori rekursif
    ========================================================= */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "{$dir}/{$file}";
            is_dir($path) ? $this->removeDirectory($path) : @unlink($path);
        }
        @rmdir($dir);
    }

    /* =========================================================
       DOWNLOAD
    ========================================================= */
    public function download(string $filename)
    {
        $filename = basename($filename);

        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
            abort(403, 'Nama file tidak valid.');
        }

        $path = $this->storageDir . "/{$filename}";

        if (!file_exists($path)) {
            abort(404, 'File tidak ditemukan atau sudah dihapus (> 30 menit).');
        }

        $ext     = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimeMap = [
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'webp' => 'image/webp',
        ];

        return response()->download($path, $filename, [
            'Content-Type'        => $mimeMap[$ext] ?? 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /* =========================================================
       CLEANUP — Auto hapus file lama
    ========================================================= */
    public function cleanup(Request $request)
    {
        $sessionId = $request->input('session_id');

        if ($sessionId && preg_match('/^[a-zA-Z0-9\-]+$/', $sessionId)) {
            foreach (glob($this->storageDir . "/{$sessionId}*") ?: [] as $f) {
                @unlink($f);
            }
            // Hapus juga profile LibreOffice jika ada
            $profileDir = $this->storageDir . "/lo_profile_{$sessionId}";
            $this->removeDirectory($profileDir);
        }

        return response()->json(['success' => true]);
    }

    /* =========================================================
       LAZY CLEANUP — Hapus file > 30 menit otomatis
       Dipanggil setiap ada request process baru
    ========================================================= */
    private function lazyCleanup(): void
    {
        $limit = time() - (30 * 60); // 30 menit

        foreach (glob($this->storageDir . '/*') ?: [] as $item) {
            // Hapus file lama
            if (is_file($item) && filemtime($item) < $limit) {
                @unlink($item);
                continue;
            }
            // Hapus folder lo_profile lama
            if (is_dir($item) && str_contains($item, 'lo_profile') && filemtime($item) < $limit) {
                $this->removeDirectory($item);
            }
        }
    }
}