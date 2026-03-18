<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class FileConverterController extends Controller
{
    private string $storageDir;
    private string $sofficeBin  = '';
    private string $gsbin       = '/usr/bin/gs';
    private string $convertBin  = '/usr/bin/convert'; // ImageMagick
    private string $ffmpegBin   = '/usr/bin/ffmpeg';

    public function __construct()
    {
        $this->storageDir = storage_path('app/file_converter');

        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0777, true);
        }

        // Auto-detect LibreOffice path
        $loPaths = [
            '/usr/bin/soffice',
            '/usr/lib/libreoffice/program/soffice',
            '/opt/libreoffice/program/soffice',
            '/snap/bin/libreoffice',
        ];
        foreach ($loPaths as $p) {
            if (file_exists($p)) {
                $this->sofficeBin = $p;
                break;
            }
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
            'conversion_type' => 'required|string|in:' .
                'jpg_to_pdf,png_to_pdf,webp_to_pdf,' .
                'word_to_pdf,excel_to_pdf,ppt_to_pdf,' .
                'pdf_to_jpg,pdf_to_png,' .
                'pdf_to_word,pdf_to_excel,pdf_to_ppt,' .
                'jpg_to_png,png_to_jpg,jpg_to_webp,' .
                'png_to_webp,webp_to_jpg,webp_to_png,' .
                'pdf_compress,pdf_merge',
        ]);

        $type      = $request->input('conversion_type');
        $file      = $request->file('file');
        $sessionId = Str::uuid()->toString();

        $this->lazyCleanup();

        try {
            $outputFiles = match (true) {
                // Image → PDF
                in_array($type, ['jpg_to_pdf','png_to_pdf','webp_to_pdf'])
                    => $this->imageToPdf($file, $sessionId),

                // Office → PDF via LibreOffice
                in_array($type, ['word_to_pdf','excel_to_pdf','ppt_to_pdf'])
                    => $this->officeToPdf($file, $sessionId),

                // PDF → Image via ImageMagick/Imagick
                in_array($type, ['pdf_to_jpg','pdf_to_png'])
                    => $this->pdfToImage($file, $sessionId,
                        $type === 'pdf_to_jpg' ? 'jpg' : 'png'),

                // PDF → Office via LibreOffice
                in_array($type, ['pdf_to_word','pdf_to_excel','pdf_to_ppt'])
                    => $this->pdfToOffice($file, $sessionId, match($type) {
                        'pdf_to_word'  => 'docx',
                        'pdf_to_excel' => 'xlsx',
                        'pdf_to_ppt'   => 'pptx',
                    }),

                // Image → Image via GD
                in_array($type, [
                    'jpg_to_png','png_to_jpg','jpg_to_webp',
                    'png_to_webp','webp_to_jpg','webp_to_png',
                ]) => $this->convertImage($file, $sessionId, $type),

                // PDF Compress via Ghostscript
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
                'trace' => substr($e->getTraceAsString(), 0, 500),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
       1. IMAGE → PDF
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

            $gd = match (true) {
                str_contains($mime, 'jpeg') => imagecreatefromjpeg($tmpPath),
                str_contains($mime, 'png')  => imagecreatefrompng($tmpPath),
                str_contains($mime, 'webp') => imagecreatefromwebp($tmpPath),
                str_contains($mime, 'gif')  => imagecreatefromgif($tmpPath),
                str_contains($mime, 'bmp')  => imagecreatefrombmp($tmpPath),
                default => throw new \Exception("Format tidak didukung: {$mime}"),
            };

            if (!$gd) throw new \Exception("Gagal membaca gambar.");

            // Canvas putih
            $canvas = imagecreatetruecolor($imgW, $imgH);
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            imagecopy($canvas, $gd, 0, 0, 0, 0, $imgW, $imgH);
            imagedestroy($gd);

            $cleanPath = $this->storageDir . "/{$sessionId}_clean.jpg";
            imagejpeg($canvas, $cleanPath, 95);
            imagedestroy($canvas);

            // Fit ke A4
            $mmW  = $imgW * 25.4 / 96;
            $mmH  = $imgH * 25.4 / 96;
            $maxW = 190; $maxH = 277;

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
            $pdf->Output('F', $this->storageDir . "/{$outName}");

            @unlink($cleanPath);
            return [$outName];

        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       2. OFFICE → PDF via LibreOffice
          Word, Excel, PPT → PDF dengan kualitas sempurna
    ========================================================= */
    private function officeToPdf($file, string $sessionId): array
    {
        $this->requireLibreOffice();

        $ext     = strtolower($file->getClientOriginalExtension());
        $tmpName = "{$sessionId}_input.{$ext}";
        $tmpPath = $this->storageDir . "/{$tmpName}";
        $file->move($this->storageDir, $tmpName);

        try {
            $outputPath = $this->runLibreOffice($tmpPath, 'pdf');
            $outName    = "{$sessionId}.pdf";
            rename($outputPath, $this->storageDir . "/{$outName}");
            return [$outName];
        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       3. PDF → IMAGE
          Gunakan Imagick jika tersedia, fallback ke Ghostscript
    ========================================================= */
    private function pdfToImage($file, string $sessionId, string $fmt = 'jpg'): array
    {
        $tmpName = "{$sessionId}_input.pdf";
        $tmpPath = $this->storageDir . "/{$tmpName}";
        $file->move($this->storageDir, $tmpName);

        try {
            // Prioritas 1: Imagick
            if (extension_loaded('imagick')) {
                return $this->pdfToImageImagick($tmpPath, $sessionId, $fmt);
            }

            // Prioritas 2: Ghostscript
            if (file_exists($this->gsbin)) {
                return $this->pdfToImageGhostscript($tmpPath, $sessionId, $fmt);
            }

            throw new \Exception(
                "Butuh Imagick atau Ghostscript untuk konversi PDF → Gambar. " .
                "Jalankan: apt install php8.4-imagick ghostscript"
            );

        } finally {
            @unlink($tmpPath);
        }
    }

    private function pdfToImageImagick(
        string $pdfPath,
        string $sessionId,
        string $fmt
    ): array {
        $imagick = new \Imagick();
        $imagick->setResolution(200, 200);
        $imagick->readImage($pdfPath . '[0-29]');
        $imagick->resetIterator();

        $outputFiles = [];
        $imgFmt      = $fmt === 'png' ? 'png' : 'jpeg';

        foreach ($imagick as $i => $page) {
            $page->setImageFormat($imgFmt);
            $page->setImageBackgroundColor('white');
            $page->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            if ($imgFmt === 'jpeg') {
                $page->setImageCompressionQuality(92);
            }
            $fname = "{$sessionId}_p" . ($i + 1) . ".{$fmt}";
            $page->writeImage($this->storageDir . "/{$fname}");
            $outputFiles[] = $fname;
        }

        $imagick->clear();
        $imagick->destroy();

        if (empty($outputFiles)) {
            throw new \Exception("Tidak ada halaman yang berhasil dikonversi.");
        }

        return $outputFiles;
    }

    private function pdfToImageGhostscript(
        string $pdfPath,
        string $sessionId,
        string $fmt
    ): array {
        $device  = $fmt === 'png' ? 'png16m' : 'jpeg';
        $outPattern = $this->storageDir . "/{$sessionId}_p%d.{$fmt}";

        $cmd = sprintf(
            '%s -dNOPAUSE -dBATCH -dSAFER ' .
            '-sDEVICE=%s -r200 ' .
            '-dFirstPage=1 -dLastPage=30 ' .
            '-sOutputFile=%s %s 2>&1',
            escapeshellcmd($this->gsbin),
            escapeshellarg($device),
            escapeshellarg($outPattern),
            escapeshellarg($pdfPath)
        );

        exec($cmd, $output, $exitCode);

        $outputFiles = [];
        $i           = 1;
        while (file_exists(sprintf(
            $this->storageDir . "/{$sessionId}_p%d.{$fmt}", $i
        ))) {
            $outputFiles[] = "{$sessionId}_p{$i}.{$fmt}";
            $i++;
        }

        if (empty($outputFiles)) {
            throw new \Exception(
                "Ghostscript gagal konversi PDF. Output: " .
                implode("\n", array_slice($output, 0, 5))
            );
        }

        return $outputFiles;
    }

    /* =========================================================
       4. PDF → OFFICE via LibreOffice
          PDF → Word, Excel, PPT
    ========================================================= */
    private function pdfToOffice(
        $file,
        string $sessionId,
        string $targetExt
    ): array {
        $this->requireLibreOffice();

        $tmpName = "{$sessionId}_input.pdf";
        $tmpPath = $this->storageDir . "/{$tmpName}";
        $file->move($this->storageDir, $tmpName);

        try {
            $outputPath = $this->runLibreOffice($tmpPath, $targetExt);
            $outName    = "{$sessionId}.{$targetExt}";
            rename($outputPath, $this->storageDir . "/{$outName}");
            return [$outName];
        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       5. IMAGE → IMAGE via GD
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
                default => throw new \Exception("Format tidak didukung: {$ext}"),
            };

            if (!$src) throw new \Exception("Gagal membaca gambar.");

            $w = imagesx($src); $h = imagesy($src);
            $canvas = imagecreatetruecolor($w, $h);

            if ($outFmt === 'png') {
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
                imagefill($canvas, 0, 0,
                    imagecolorallocatealpha($canvas, 0, 0, 0, 127));
            } else {
                imagefill($canvas, 0, 0,
                    imagecolorallocate($canvas, 255, 255, 255));
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

            if (!$ok) throw new \Exception("Gagal menyimpan gambar.");
            return [$outName];

        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       6. PDF COMPRESS via Ghostscript
    ========================================================= */
    private function compressPdf($file, string $sessionId): array
    {
        if (!file_exists($this->gsbin)) {
            throw new \Exception(
                "Ghostscript tidak tersedia. " .
                "Install: apt install ghostscript"
            );
        }

        $tmpName = "{$sessionId}_input.pdf";
        $tmpPath = $this->storageDir . "/{$tmpName}";
        $file->move($this->storageDir, $tmpName);

        try {
            $outName = "{$sessionId}_compressed.pdf";
            $outPath = $this->storageDir . "/{$outName}";

            $cmd = sprintf(
                '%s -dNOPAUSE -dBATCH -dSAFER ' .
                '-sDEVICE=pdfwrite ' .
                '-dCompatibilityLevel=1.4 ' .
                '-dPDFSETTINGS=/ebook ' .
                '-dEmbedAllFonts=true ' .
                '-dSubsetFonts=true ' .
                '-dColorImageDownsampleType=/Bicubic ' .
                '-dColorImageResolution=150 ' .
                '-dGrayImageDownsampleType=/Bicubic ' .
                '-dGrayImageResolution=150 ' .
                '-sOutputFile=%s %s 2>&1',
                escapeshellcmd($this->gsbin),
                escapeshellarg($outPath),
                escapeshellarg($tmpPath)
            );

            exec($cmd, $output, $exitCode);

            if ($exitCode !== 0 || !file_exists($outPath)) {
                throw new \Exception(
                    "Kompresi PDF gagal. " . implode(' ', array_slice($output, 0, 3))
                );
            }

            return [$outName];

        } finally {
            @unlink($tmpPath);
        }
    }

    /* =========================================================
       CORE: LibreOffice Runner
       Dipakai untuk semua konversi dokumen
    ========================================================= */
    private function runLibreOffice(
        string $inputPath,
        string $targetExt
    ): string {
        // Profile unik per request — hindari race condition
        $profileDir = $this->storageDir . "/lo_{$this->sid($inputPath)}";
        @mkdir($profileDir, 0777, true);
        @chown($profileDir, 'www-data');

        // Filter map LibreOffice
        $filterMap = [
            'pdf'  => 'pdf',
            'docx' => 'docx:"MS Word 2007 XML"',
            'xlsx' => 'xlsx:"Calc MS Excel 2007 XML"',
            'pptx' => 'pptx:"Impress MS PowerPoint 2007 XML"',
            'odt'  => 'odt',
            'ods'  => 'ods',
            'odp'  => 'odp',
        ];

        $filter = $filterMap[$targetExt] ?? $targetExt;

        // Set HOME agar LibreOffice tidak error saat jalan sebagai www-data
        $env = 'HOME=/var/www';

        $cmd = sprintf(
            '%s %s --headless --norestore --nofirststartwizard --nolockcheck ' .
            '-env:UserInstallation=file://%s ' .
            '--convert-to %s --outdir %s %s 2>&1',
            $env,
            escapeshellcmd($this->sofficeBin),
            escapeshellarg($profileDir),
            escapeshellarg($filter),
            escapeshellarg($this->storageDir),
            escapeshellarg($inputPath)
        );

        $outputLines = [];
        $exitCode    = 0;
        exec($cmd, $outputLines, $exitCode);
        $output = implode("\n", $outputLines);

        // Cleanup profile
        $this->removeDir($profileDir);

        // Cari output file
        $baseName   = pathinfo($inputPath, PATHINFO_FILENAME);
        $outputFile = $this->storageDir . "/{$baseName}.{$targetExt}";

        // LibreOffice kadang output nama berbeda — scan folder
        if (!file_exists($outputFile) || filesize($outputFile) === 0) {
            $found = glob(
                $this->storageDir . "/{$baseName}*.{$targetExt}"
            );
            if (!empty($found) && filesize($found[0]) > 0) {
                $outputFile = $found[0];
            } else {
                Log::error("LibreOffice failed", [
                    'cmd'      => $cmd,
                    'output'   => $output,
                    'exitCode' => $exitCode,
                    'expected' => $outputFile,
                ]);
                throw new \Exception(
                    "Konversi gagal. " .
                    "Pastikan file tidak password-protected dan format valid. " .
                    "Error: " . substr($output, 0, 200)
                );
            }
        }

        return $outputFile;
    }

    /* =========================================================
       HELPERS
    ========================================================= */
    private function requireLibreOffice(): void
    {
        if (empty($this->sofficeBin) || !file_exists($this->sofficeBin)) {
            throw new \Exception(
                "LibreOffice tidak ditemukan di server. " .
                "Install: sudo apt install libreoffice"
            );
        }
    }

    private function loadFpdf(): void
    {
        $paths = [
            base_path('vendor/setasign/fpdf/fpdf.php'),
            app_path('Libraries/fpdf/fpdf.php'),
        ];
        foreach ($paths as $p) {
            if (file_exists($p)) { require_once $p; return; }
        }
        throw new \Exception("FPDF tidak ditemukan. Jalankan: composer require setasign/fpdf");
    }

    private function sid(string $path): string
    {
        $name = pathinfo($path, PATHINFO_FILENAME);
        $parts = explode('_', $name);
        return $parts[0] ?? Str::random(8);
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $p = "{$dir}/{$item}";
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

        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
            abort(403, 'Nama file tidak valid.');
        }

        $path = $this->storageDir . "/{$filename}";

        if (!file_exists($path)) {
            abort(404, 'File tidak ditemukan atau sudah dihapus (> 30 menit).');
        }

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

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return response()->download($path, $filename, [
            'Content-Type'        => $mimeMap[$ext] ?? 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /* =========================================================
       CLEANUP
    ========================================================= */
    public function cleanup(Request $request)
    {
        $sessionId = $request->input('session_id');

        if ($sessionId && preg_match('/^[a-zA-Z0-9\-]+$/', $sessionId)) {
            foreach (glob($this->storageDir . "/{$sessionId}*") ?: [] as $f) {
                is_dir($f) ? $this->removeDir($f) : @unlink($f);
            }
        }

        return response()->json(['success' => true]);
    }

    private function lazyCleanup(): void
    {
        $limit = time() - (30 * 60);

        foreach (glob($this->storageDir . '/*') ?: [] as $item) {
            if (filemtime($item) >= $limit) continue;

            is_dir($item)
                ? $this->removeDir($item)
                : @unlink($item);
        }
    }
}