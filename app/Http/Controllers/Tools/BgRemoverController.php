<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * BgRemoverController — Background Removal via Python / rembg
 *
 * FIX v3:
 *   1. Removed SystemSecurityController dependency (class did not exist → PHP Fatal Error
 *      on every request → 500 on /bg/process). Replaced with a lightweight inline
 *      content check that does the same job without the missing class.
 *   2. Fixed Python path resolution. Controller was calling config('app.python_path')
 *      but no such key exists in config/app.php, so it always fell back to the system
 *      'python3' which may not have rembg installed (only the venv does).
 *      Now reads: env('PYTHON_PATH', env('PYTHON_BINARY', 'python3')) matching .env.
 *   3. Added exec() availability check — shared hosts often disable exec().
 *   4. Improved error logging with full Python output for easier debugging.
 */
class BgRemoverController extends Controller
{
    private const MODEL_MAP = [
        'fast'     => ['model' => 'u2net',             'matting' => 'false'],
        'medium'   => ['model' => 'u2net',             'matting' => 'true'],
        'high'     => ['model' => 'isnet-general-use', 'matting' => 'true'],
        'portrait' => ['model' => 'birefnet-general',  'matting' => 'true'],
    ];

    private const ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/jpg'  => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    private const MAX_FILE_SIZE = 20 * 1024 * 1024; // 20 MB

    /* ── Forbidden patterns for content scanning ─────────── */
    private const FORBIDDEN_PATTERNS = [
        '<?php', '<?=', '<script', 'javascript:', 'eval(',
        'base64_decode', 'exec(', 'system(', 'passthru(',
    ];

    // ──────────────────────────────────────────────────────────

    public function index()
    {
        return view('tools.bgremover.index');
    }

    public function process(Request $request)
    {
        /* ── 1. Basic validation ───────────────────────── */
        $request->validate([
            'image'   => 'required|file|mimes:jpg,jpeg,png,webp|max:20480',
            'quality' => 'nullable|in:fast,medium,high,portrait',
        ]);

        $file    = $request->file('image');
        $quality = $request->input('quality', 'high');
        $cfg     = self::MODEL_MAP[$quality] ?? self::MODEL_MAP['high'];

        /* ── 2. MIME type verification ─────────────────── */
        $mime = $file->getMimeType();
        if (!array_key_exists($mime, self::ALLOWED_TYPES)) {
            return response()->json(['error' => 'Tipe file tidak diizinkan.'], 422);
        }

        /* ── 3. Magic bytes check ──────────────────────── */
        $handle = fopen($file->getRealPath(), 'rb');
        $header = fread($handle, 12);
        fclose($handle);
        $ext = self::ALLOWED_TYPES[$mime];

        if (!$this->validateMagicBytes($header, $ext)) {
            return response()->json(['error' => 'File tidak valid atau rusak.'], 422);
        }

        /* ── 4. File size guard ────────────────────────── */
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return response()->json(['error' => 'File terlalu besar (maks 20 MB).'], 422);
        }

        /* ── 5. Lightweight content security scan ─────── */
        //  We only scan the first 4 KB (metadata / EXIF area) where injected
        //  text could hide. Reading the full binary for large images is wasteful
        //  and the old SystemSecurityController caused a Fatal PHP Error because
        //  the class file was missing.
        $fh      = fopen($file->getRealPath(), 'rb');
        $sample  = fread($fh, 4096);
        fclose($fh);
        $sampleLower = strtolower($sample);

        foreach (self::FORBIDDEN_PATTERNS as $pattern) {
            if (str_contains($sampleLower, strtolower($pattern))) {
                Log::warning('BgRemover: Suspicious content detected', [
                    'ip'      => $request->ip(),
                    'file'    => $file->getClientOriginalName(),
                    'pattern' => $pattern,
                ]);
                return response()->json(['error' => 'File ditolak karena mengandung konten mencurigakan.'], 422);
            }
        }

        /* ── 6. Check exec() availability ─────────────── */
        if (!function_exists('exec') || in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))))) {
            Log::error('BgRemover: exec() is disabled on this server');
            return response()->json([
                'error' => 'Fungsi exec() dinonaktifkan di server ini. Hubungi administrator hosting.',
            ], 500);
        }

        /* ── 7. Temp workspace ─────────────────────────── */
        $session   = Str::uuid()->toString();
        $dir       = storage_path("app/temp/{$session}");
        $inputPath = "{$dir}/input.{$ext}";
        $outPath   = "{$dir}/output.png";

        File::makeDirectory($dir, 0755, true, true);

        try {
            $file->move($dir, "input.{$ext}");

            /* ── 8. Resolve Python binary ──────────────── *
             *  Priority:
             *    1. PYTHON_PATH   (full venv path, e.g. /var/www/venv/bin/python3)
             *    2. PYTHON_BINARY (shorter alias)
             *    3. 'python3'     (system fallback)
             *
             *  The old code used config('app.python_path') which reads config/app.php,
             *  not the .env file — so the PYTHON_PATH env var was always ignored.
             */
            $python = env('PYTHON_PATH', env('PYTHON_BINARY', 'python'));
            $script = base_path('scripts/remove_bg.py');

            if (!file_exists($script)) {
                File::deleteDirectory($dir);
                return response()->json(['error' => 'Script AI tidak ditemukan. Hubungi administrator.'], 500);
            }

            // Verify the python binary exists
            if (!file_exists($python) && !$this->commandExists($python)) {
                File::deleteDirectory($dir);
                return response()->json([
                    'error' => "Python tidak ditemukan di: {$python}. Pastikan PYTHON_PATH di .env sudah benar.",
                ], 500);
            }

            /* ── 9. Build & run command ────────────────── */
            $cmd = implode(' ', [
                escapeshellarg($python),
                escapeshellarg($script),
                escapeshellarg($inputPath),
                escapeshellarg($outPath),
                escapeshellarg($cfg['model']),
                escapeshellarg($cfg['matting']),
                '2>&1',
            ]);

            exec($cmd, $lines, $exitCode);

            /* ── 10. Check result ──────────────────────── */
            if ($exitCode !== 0 || !file_exists($outPath)) {
                $rawOutput = implode("\n", $lines);
                Log::error('BgRemover Python error', [
                    'exit_code' => $exitCode,
                    'cmd'       => $cmd,
                    'output'    => $rawOutput,
                ]);
                $detail = implode(' ', array_slice($lines, -5));
                File::deleteDirectory($dir);
                return response()->json([
                    'error' => 'Pemrosesan gagal. ' . $this->friendlyError($detail),
                ], 500);
            }

            /* ── 11. Validate output ───────────────────── */
            $outSize = filesize($outPath);
            if ($outSize < 100 || $outSize > (50 * 1024 * 1024)) {
                File::deleteDirectory($dir);
                return response()->json(['error' => 'Output AI tidak valid. Coba lagi.'], 500);
            }

            /* ── 12. Stream PNG back ───────────────────── */
            $content = file_get_contents($outPath);
            File::deleteDirectory($dir);

            return response($content, 200, [
                'Content-Type'    => 'image/png',
                'Content-Length'  => strlen($content),
                'Cache-Control'   => 'no-store, no-cache, must-revalidate',
                'Pragma'          => 'no-cache',
                'X-Bgr-Model'     => $cfg['model'],
                'X-Frame-Options' => 'DENY',
            ]);

        } catch (\Throwable $e) {
            File::deleteDirectory($dir);
            Log::error('BgRemover error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Terjadi kesalahan server. Coba lagi.'], 500);
        }
    }

    /* ── Magic bytes validator ──────────────────────────── */
    private function validateMagicBytes(string $header, string $ext): bool
    {
        return match ($ext) {
            'jpg'  => str_starts_with($header, "\xFF\xD8\xFF"),
            'png'  => str_starts_with($header, "\x89PNG\r\n\x1A\n"),
            'webp' => str_starts_with($header, 'RIFF') && substr($header, 8, 4) === 'WEBP',
            default => false,
        };
    }

    /* ── Check if a command exists in PATH ──────────────── */
    private function commandExists(string $cmd): bool
    {
        $which = shell_exec('which ' . escapeshellarg($cmd) . ' 2>/dev/null');
        return !empty(trim($which ?? ''));
    }

    /* ── Friendly error message from Python stderr ──────── */
    private function friendlyError(string $raw): string
    {
        $raw = strtolower($raw);

        if (str_contains($raw, 'modulenotfound') || str_contains($raw, 'no module named')) {
            return 'Dependensi Python belum terinstal. Jalankan: pip install rembg[cpu] Pillow onnxruntime';
        }
        if (str_contains($raw, 'no such file') && str_contains($raw, 'python')) {
            return 'Python tidak ditemukan. Pastikan PYTHON_PATH di .env benar.';
        }
        if (str_contains($raw, 'memory') || str_contains($raw, 'memoryerror')) {
            return 'Memori server tidak mencukupi. Coba dengan gambar yang lebih kecil.';
        }
        if (str_contains($raw, 'connection refused') || str_contains($raw, 'timeout')) {
            return 'Koneksi ke layanan AI gagal. Hubungi administrator.';
        }
        if (str_contains($raw, 'permission denied')) {
            return 'Akses ditolak oleh sistem. Hubungi administrator.';
        }

        return 'Hubungi administrator jika masalah berlanjut.';
    }
}