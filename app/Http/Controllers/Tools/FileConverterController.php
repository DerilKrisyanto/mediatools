<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;

class FileConverterController extends Controller
{
    /**
     * conv_type => [ accepted_extensions[], output_extension, output_mime ]
     */
    private const CONV_MAP = [
        // → PDF
        'jpg_to_pdf'   => [['jpg','jpeg'],      'pdf',  'application/pdf'],
        'png_to_pdf'   => [['png'],              'pdf',  'application/pdf'],
        'word_to_pdf'  => [['doc','docx'],       'pdf',  'application/pdf'],
        'excel_to_pdf' => [['xls','xlsx'],       'pdf',  'application/pdf'],
        'ppt_to_pdf'   => [['ppt','pptx'],       'pdf',  'application/pdf'],
        // PDF →
        'pdf_to_word'  => [['pdf'],  'docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'pdf_to_excel' => [['pdf'],  'xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'pdf_to_ppt'   => [['pdf'],  'pptx', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        'pdf_to_jpg'   => [['pdf'],  'zip',  'application/zip'],
        'pdf_to_png'   => [['pdf'],  'zip',  'application/zip'],
        // Image ↔ Image
        'jpg_to_png'   => [['jpg','jpeg'], 'png',  'image/png'],
        'png_to_jpg'   => [['png'],        'jpg',  'image/jpeg'],
        'jpg_to_webp'  => [['jpg','jpeg'], 'webp', 'image/webp'],
        'png_to_webp'  => [['png'],        'webp', 'image/webp'],
        'webp_to_jpg'  => [['webp'],       'jpg',  'image/jpeg'],
        'webp_to_png'  => [['webp'],       'png',  'image/png'],
    ];

    // ──────────────────────────────────────────────────────────────
    //  INDEX
    // ──────────────────────────────────────────────────────────────

    public function index()
    {
        return view('tools.fileconverter.index');
    }

    // ──────────────────────────────────────────────────────────────
    //  PROCESS  (main entry point)
    // ──────────────────────────────────────────────────────────────

    public function process(Request $request): JsonResponse
    {
        $validTypes = implode(',', array_keys(self::CONV_MAP));

        $request->validate([
            'files'     => 'required|array|min:1|max:5',
            'files.*'   => 'required|file|max:51200',   // 50 MB
            'conv_type' => "required|string|in:{$validTypes}",
        ]);

        $convType                          = $request->input('conv_type');
        [$allowedExts, $outputExt, $outputMime] = self::CONV_MAP[$convType];

        // --- Work directory ---
        $sessionId = Str::uuid()->toString();
        $workDir   = storage_path("app/file_converter/{$sessionId}");
        $inputDir  = "{$workDir}/input";
        $outputDir = "{$workDir}/output";

        foreach ([$workDir, $inputDir, $outputDir] as $d) {
            if (!mkdir($d, 0775, true) && !is_dir($d)) {
                return response()->json(['success' => false, 'message' => "Tidak dapat membuat direktori kerja."], 500);
            }
        }

        $results = [];
        $errors  = [];

        /** @var \Illuminate\Http\UploadedFile $file */
        foreach ($request->file('files') as $file) {
            $origName = $file->getClientOriginalName();
            $ext      = strtolower($file->getClientOriginalExtension());

            if (!in_array($ext, $allowedExts, true)) {
                $errors[] = ['file' => $origName, 'error' => "Format .{$ext} tidak didukung untuk konversi ini."];
                continue;
            }

            // Safe filename
            $safeStem  = Str::slug(pathinfo($origName, PATHINFO_FILENAME)) ?: ('file_' . Str::random(6));
            $inputPath = "{$inputDir}/{$safeStem}.{$ext}";
            $file->move($inputDir, "{$safeStem}.{$ext}");

            $isMultiPage = in_array($convType, ['pdf_to_jpg', 'pdf_to_png']);

            if ($isMultiPage) {
                $outputPath = "{$outputDir}/{$safeStem}_pages";
                @mkdir($outputPath, 0775, true);
            } else {
                $outputPath = "{$outputDir}/{$safeStem}_converted.{$outputExt}";
            }

            // --- Run conversion ---
            $res = $this->runConversion($convType, $inputPath, $outputPath, $outputDir);

            if ($res['success']) {
                if ($isMultiPage) {
                    $zipPath   = "{$outputDir}/{$safeStem}_pages.zip";
                    $this->zipDirectory($outputPath, $zipPath);
                    $finalPath = $zipPath;
                    $dlMime    = 'application/zip';
                    $dlExt     = 'zip';
                } else {
                    // LibreOffice may rename the output — find it
                    $finalPath = $res['output'] ?? $outputPath;
                    if (!file_exists($finalPath)) {
                        // Search outputDir for a matching file
                        $found = $this->findConvertedFile($outputDir, $outputExt, $safeStem);
                        $finalPath = $found ?: $outputPath;
                    }
                    $dlMime = $outputMime;
                    $dlExt  = $outputExt;
                }

                if (!file_exists($finalPath) || filesize($finalPath) === 0) {
                    $errors[] = ['file' => $origName, 'error' => 'File output tidak ditemukan atau kosong setelah konversi.'];
                    Log::warning("FC: output missing after conversion", [
                        'conv_type' => $convType,
                        'input'     => $inputPath,
                        'expected'  => $finalPath,
                        'res'       => $res,
                    ]);
                    continue;
                }

                $token  = $this->storeToken($finalPath, $sessionId, "{$safeStem}_converted.{$dlExt}");
                $results[] = [
                    'original' => $origName,
                    'token'    => $token,
                    'filename' => "{$safeStem}_converted.{$dlExt}",
                    'mime'     => $dlMime,
                    'size'     => filesize($finalPath),
                    'engine'   => $res['engine'] ?? 'unknown',
                ];
            } else {
                Log::error("FC: conversion failed", [
                    'conv_type' => $convType,
                    'file'      => $origName,
                    'error'     => $res['error'] ?? '',
                    'stdout'    => $res['stdout'] ?? '',
                ]);
                $errors[] = [
                    'file'  => $origName,
                    'error' => $this->humanError($res['error'] ?? $res['stdout'] ?? 'Konversi gagal.'),
                ];
            }
        }

        // Record session meta for cleanup
        @file_put_contents("{$workDir}/meta.json", json_encode([
            'created_at' => time(),
            'session_id' => $sessionId,
        ]));

        if (empty($results) && !empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => $errors[0]['error'] ?? 'Semua konversi gagal.',
                'errors'  => $errors,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'files'   => $results,
            'errors'  => $errors,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  CONVERSION DISPATCH
    // ──────────────────────────────────────────────────────────────

    private function runConversion(
        string $convType,
        string $inputPath,
        string $outputPath,
        string $outputDir
    ): array {
        // Office → PDF: use LibreOffice directly (most reliable)
        if (in_array($convType, ['word_to_pdf', 'excel_to_pdf', 'ppt_to_pdf'])) {
            return $this->runLibreOffice($inputPath, $outputDir);
        }

        // Everything else: Python script
        return $this->runPython($convType, $inputPath, $outputPath);
    }

    // ──────────────────────────────────────────────────────────────
    //  PYTHON ENGINE
    // ──────────────────────────────────────────────────────────────

    private function runPython(string $convType, string $inputPath, string $outputPath): array
    {
        $python = $this->pythonBin();
        $script = storage_path('app/py_scripts/converter.py');
        $lo     = $this->loBin();

        if (!file_exists($script)) {
            return [
                'success' => false,
                'error'   => "Python converter script tidak ditemukan di: {$script}. Jalankan install script terlebih dahulu.",
            ];
        }

        if (PHP_OS_FAMILY === 'Windows') {
            // Windows: use cmd /c to allow proper quoting
            $cmd = 'cmd /c "'
                . escapeshellarg($python) . ' '
                . escapeshellarg($script) . ' '
                . '--type '      . escapeshellarg($convType) . ' '
                . '--input '     . escapeshellarg($inputPath) . ' '
                . '--output '    . escapeshellarg($outputPath) . ' '
                . '--lo-binary ' . escapeshellarg($lo)
                . '"';
        } else {
            $parts = [
                $python, $script,
                '--type',      $convType,
                '--input',     $inputPath,
                '--output',    $outputPath,
                '--lo-binary', $lo,
            ];
            $cmd = implode(' ', array_map('escapeshellarg', $parts));
        }

        return $this->execCommand($cmd, (int) env('LO_TIMEOUT', 180));
    }

    // ──────────────────────────────────────────────────────────────
    //  LIBREOFFICE ENGINE (Office → PDF)
    // ──────────────────────────────────────────────────────────────

    private function runLibreOffice(string $inputPath, string $outputDir): array
    {
        $lo  = $this->loBin();
        $tmp = sys_get_temp_dir() . '/lo_home_' . Str::random(8);
        @mkdir($tmp, 0775, true);

        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = escapeshellarg($lo)
                . ' --headless --norestore --nofirststartwizard'
                . ' --convert-to pdf'
                . ' --outdir ' . escapeshellarg($outputDir)
                . ' ' . escapeshellarg($inputPath);
        } else {
            $envPrefix = "HOME={$tmp} ";
            $cmd = $envPrefix
                . escapeshellarg($lo)
                . ' --headless --norestore --nofirststartwizard'
                . ' --convert-to pdf'
                . ' --outdir ' . escapeshellarg($outputDir)
                . ' ' . escapeshellarg($inputPath);
        }

        $result = $this->execCommand($cmd, (int) env('LO_TIMEOUT', 120), false);

        // LO outputs the PDF with same stem as input
        $stem   = pathinfo($inputPath, PATHINFO_FILENAME);
        $outPdf = "{$outputDir}/{$stem}.pdf";

        if (file_exists($outPdf) && filesize($outPdf) > 0) {
            @array_map('unlink', glob("{$tmp}/*"));
            @rmdir($tmp);
            return ['success' => true, 'output' => $outPdf, 'engine' => 'libreoffice'];
        }

        @array_map('unlink', glob("{$tmp}/*"));
        @rmdir($tmp);

        $errMsg = $result['stdout'] ?? 'LibreOffice: output PDF tidak ditemukan setelah konversi.';
        return ['success' => false, 'error' => $errMsg];
    }

    // ──────────────────────────────────────────────────────────────
    //  EXECUTE COMMAND
    // ──────────────────────────────────────────────────────────────

    private function execCommand(string $cmd, int $timeout = 120, bool $parseJson = true): array
    {
        $old = (int) ini_get('max_execution_time');
        set_time_limit(max($old ?: 0, $timeout + 30));

        $output = [];
        $exit   = 0;

        // Redirect stderr to stdout for capture
        exec($cmd . ' 2>&1', $output, $exit);

        set_time_limit($old ?: 0);

        $stdout = implode("\n", $output);

        if ($parseJson) {
            // Find last JSON line from output
            foreach (array_reverse($output) as $line) {
                $line = trim($line);
                if ($line && $line[0] === '{') {
                    $decoded = json_decode($line, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $decoded;
                    }
                }
            }
        }

        return [
            'success'   => ($exit === 0),
            'exit_code' => $exit,
            'stdout'    => $stdout,
            'error'     => $exit !== 0 ? $stdout : '',
        ];
    }

    // ──────────────────────────────────────────────────────────────
    //  DOWNLOAD
    // ──────────────────────────────────────────────────────────────

    public function download(Request $request, string $token)
    {
        $data = Cache::get("fc_dl_{$token}");

        if (!$data) {
            abort(404, 'Link download sudah expired atau tidak valid. Silakan konversi ulang.');
        }

        $path = $data['path'] ?? null;

        if (!$path || !file_exists($path)) {
            Cache::forget("fc_dl_{$token}");
            abort(404, 'File tidak ditemukan. Silakan konversi ulang.');
        }

        $filename = $data['filename'] ?? basename($path);
        $mime     = $data['mime'] ?? (mime_content_type($path) ?: 'application/octet-stream');

        return response()->download($path, $filename, [
            'Content-Type'           => $mime,
            'Content-Disposition'    => 'attachment; filename="' . $filename . '"',
            'Cache-Control'          => 'no-cache, no-store, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  CLEANUP
    // ──────────────────────────────────────────────────────────────

    public function cleanup(Request $request): JsonResponse
    {
        $base   = storage_path('app/file_converter');
        $maxAge = 3600;
        $cleaned = 0;

        if (!is_dir($base)) {
            return response()->json(['cleaned' => 0]);
        }

        foreach (glob("{$base}/*/meta.json") ?: [] as $metaFile) {
            $meta = json_decode(@file_get_contents($metaFile), true) ?? [];
            if (isset($meta['created_at']) && (time() - $meta['created_at']) > $maxAge) {
                $this->deleteDir(dirname($metaFile));
                $cleaned++;
            }
        }

        return response()->json(['success' => true, 'cleaned' => $cleaned]);
    }

    // ──────────────────────────────────────────────────────────────
    //  HELPERS
    // ──────────────────────────────────────────────────────────────

    /** Store download token in cache (2 hours TTL) */
    private function storeToken(string $filePath, string $sessionId, string $filename): string
    {
        $token = Str::random(48);
        Cache::put("fc_dl_{$token}", [
            'path'       => $filePath,
            'session_id' => $sessionId,
            'filename'   => $filename,
            'mime'       => mime_content_type($filePath) ?: 'application/octet-stream',
        ], now()->addHours(2));

        return $token;
    }

    /** Find converted file in outputDir by extension (handles LO renaming) */
    private function findConvertedFile(string $outputDir, string $ext, string $stem): ?string
    {
        // Exact name first
        $exact = "{$outputDir}/{$stem}.{$ext}";
        if (file_exists($exact)) return $exact;

        // Glob search
        $matches = glob("{$outputDir}/*.{$ext}");
        if ($matches) {
            // Return most recent
            usort($matches, fn($a, $b) => filemtime($b) - filemtime($a));
            return $matches[0];
        }

        return null;
    }

    private function pythonBin(): string
    {
        $env = env('PYTHON_BINARY', '');
        if ($env && $this->binExists($env)) return $env;

        if (PHP_OS_FAMILY === 'Windows') {
            foreach (['python', 'python3', 'py'] as $b) {
                if ($this->binExists($b)) return $b;
            }
            return 'python';
        }

        foreach (['/usr/bin/python3', '/usr/local/bin/python3', 'python3', 'python'] as $b) {
            if ($this->binExists($b)) return $b;
        }
        return 'python3';
    }

    private function loBin(): string
    {
        $env = env('LIBREOFFICE_BINARY', '');
        if ($env && (file_exists($env) || $this->binExists($env))) return $env;

        if (PHP_OS_FAMILY === 'Windows') {
            foreach ([
                'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
                'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
            ] as $p) {
                if (file_exists($p)) return $p;
            }
            return 'soffice';
        }

        foreach (['/usr/bin/soffice', '/usr/local/bin/soffice', 'soffice'] as $b) {
            if ($this->binExists($b)) return $b;
        }
        return 'soffice';
    }

    private function binExists(string $bin): bool
    {
        if (file_exists($bin)) return true;
        $check = PHP_OS_FAMILY === 'Windows' ? "where \"{$bin}\"" : "which {$bin}";
        exec($check . ' 2>&1', $out, $code);
        return $code === 0;
    }

    private function zipDirectory(string $sourceDir, string $zipPath): bool
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) return false;

        foreach (glob("{$sourceDir}/*") ?: [] as $file) {
            if (is_file($file)) {
                $zip->addFile($file, basename($file));
            }
        }
        $zip->close();
        return file_exists($zipPath);
    }

    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        foreach (array_diff(scandir($dir), ['.', '..']) as $item) {
            $p = "{$dir}/{$item}";
            is_dir($p) ? $this->deleteDir($p) : @unlink($p);
        }
        @rmdir($dir);
    }

    private function humanError(string $raw): string
    {
        if (str_contains($raw, 'No module named') || str_contains($raw, 'ModuleNotFoundError')) {
            return 'Dependensi Python belum terinstall. Jalankan install script terlebih dahulu.';
        }
        if (str_contains($raw, 'not found') && str_contains($raw, 'script')) {
            return 'Script Python tidak ditemukan. Salin converter.py ke storage/app/py_scripts/.';
        }
        if (str_contains($raw, 'LibreOffice') || str_contains($raw, 'soffice')) {
            return 'LibreOffice tidak ditemukan. Install LibreOffice dan pastikan path di .env sudah benar.';
        }
        if (str_contains($raw, 'timed out') || str_contains($raw, 'timeout')) {
            return 'Konversi timeout — file terlalu besar atau LibreOffice berjalan terlalu lambat.';
        }
        if (str_contains($raw, 'Permission denied')) {
            return 'Izin akses ditolak. Periksa permission folder storage/app/file_converter.';
        }
        if (str_contains($raw, 'password') || str_contains($raw, 'encrypted')) {
            return 'File PDF terproteksi/terenkripsi. Hapus proteksi dahulu sebelum dikonversi.';
        }
        // Return first line, max 220 chars
        return mb_substr(trim(explode("\n", $raw)[0]), 0, 220) ?: 'Konversi gagal. Periksa file dan coba lagi.';
    }
}
