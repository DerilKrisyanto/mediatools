<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class MetadataSanitizerController extends Controller
{
    private const MAX_FILES   = 10;
    private const MAX_SIZE_MB = 20;
    private const TOKEN_TTL   = 600;  // seconds to keep download token alive
    private const SESSION_TTL = 3600; // seconds to keep scan session alive
    private const ALLOWED_EXT = 'jpg,jpeg,png,webp,pdf';

    public function index()
    {
        return view('tools.sanitizer.index');
    }

    // ── PHASE 1: Scan ──────────────────────────────────────────────────────────

    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'files'   => ['required', 'array', 'min:1', 'max:' . self::MAX_FILES],
            'files.*' => ['required', 'file', 'mimes:' . self::ALLOWED_EXT, 'max:' . (self::MAX_SIZE_MB * 1024)],
        ], [
            'files.required' => 'Pilih minimal satu file untuk diproses.',
            'files.max'      => 'Maksimal ' . self::MAX_FILES . ' file sekaligus.',
            'files.*.mimes'  => 'Format tidak didukung. Gunakan JPG, PNG, WebP, atau PDF.',
            'files.*.max'    => 'Ukuran file tidak boleh melebihi ' . self::MAX_SIZE_MB . ' MB.',
        ]);

        $sessionKey = Str::uuid()->toString();
        $tempDir    = "temp/sanitizer/{$sessionKey}";
        Storage::makeDirectory($tempDir);

        $fileResults = [];
        $safeCount   = 0;
        $threatCount = 0;

        foreach ($request->file('files') as $idx => $file) {
            $baseName   = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $ext        = strtolower($file->getClientOriginalExtension());
            $safeSlug   = Str::slug($baseName) ?: 'file';
            $storedName = "{$idx}_{$safeSlug}.{$ext}";

            $file->storeAs($tempDir, $storedName);
            $fullPath = Storage::path("{$tempDir}/{$storedName}");

            $threats = $this->scanFileForThreats($fullPath, $ext);

            empty($threats) ? $safeCount++ : $threatCount++;

            $fileResults[] = [
                'id'           => $idx,
                'name'         => $file->getClientOriginalName(),
                'stored_name'  => $storedName,
                'ext'          => $ext,
                'size'         => $file->getSize(),
                'size_fmt'     => $this->fmtBytes($file->getSize()),
                'safe'         => empty($threats),
                'threat_count' => count($threats),
                'severity'     => $this->maxSeverity($threats),
                'threats'      => $threats,
            ];
        }

        cache()->put("san_session:{$sessionKey}", [
            'dir'   => $tempDir,
            'files' => $fileResults,
        ], self::SESSION_TTL);

        return response()->json([
            'success'     => true,
            'session_key' => $sessionKey,
            'files'       => $fileResults,
            'summary'     => [
                'total'   => count($fileResults),
                'safe'    => $safeCount,
                'threats' => $threatCount,
            ],
        ]);
    }

    // ── PHASE 2: Process ───────────────────────────────────────────────────────

    public function process(Request $request): JsonResponse
    {
        $request->validate([
            'session_key' => ['required', 'string', 'regex:/^[a-f0-9\-]{36}$/'],
            'file_ids'    => ['required', 'array', 'min:1'],
            'file_ids.*'  => ['integer', 'min:0'],
        ]);

        $sessionKey  = $request->input('session_key');
        $selectedIds = array_map('intval', $request->input('file_ids'));

        $session = cache()->get("san_session:{$sessionKey}");

        if (! $session) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak ditemukan atau sudah kedaluwarsa. Silakan upload ulang.',
            ], 422);
        }

        $tempDir   = $session['dir'];
        $allFiles  = $session['files'];
        $outputDir = "{$tempDir}/output";
        Storage::makeDirectory($outputDir);

        // BUG FIX #5 — Use config() so the path stays in one place (config/mediatools.php).
        // OLD: $scriptPath = storage_path('app/py_scripts/sanitize_metadata.py');
        //      → hardcoded path that diverged from config('mediatools.python_scripts_path')
        // FIX: Read from config; keep the script at storage/app/py_scripts/sanitize_metadata.py
        $pythonBin  = config('mediatools.python_bin', 'python3');
        $scriptPath = rtrim(config('mediatools.python_scripts_path'), '/') . '/sanitize_metadata.py';

        $processedPaths = [];
        $errors         = [];

        foreach ($allFiles as $fileData) {
            if (! in_array($fileData['id'], $selectedIds, true)) {
                continue;
            }

            $inputPath  = Storage::path("{$tempDir}/{$fileData['stored_name']}");
            $outputName = pathinfo($fileData['stored_name'], PATHINFO_FILENAME) . '_clean.' . $fileData['ext'];
            $outputPath = Storage::path("{$outputDir}/{$outputName}");

            if (! file_exists($inputPath)) {
                $errors[] = "{$fileData['name']}: file sementara tidak ditemukan.";
                continue;
            }

            $cmd = sprintf(
                '%s %s %s %s 2>&1',
                escapeshellcmd($pythonBin),
                escapeshellarg($scriptPath),
                escapeshellarg($inputPath),
                escapeshellarg($outputPath)
            );

            // BUG FIX #6 — Clean variable initialisation.
            // OLD: $output = $exitCode = [];  then  $exitCode = 0;
            //      ($exitCode was needlessly double-assigned)
            $output   = [];
            $exitCode = 0;
            exec($cmd, $output, $exitCode);

            if ($exitCode !== 0) {
                $errors[] = "{$fileData['name']}: " . implode(' ', $output);
                continue;
            }

            if (file_exists($outputPath)) {
                $dlName                  = pathinfo($fileData['name'], PATHINFO_FILENAME) . '_clean.' . $fileData['ext'];
                $processedPaths[$dlName] = $outputPath;
            }
        }

        if (empty($processedPaths)) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses file. ' . implode('; ', $errors),
            ], 422);
        }

        cache()->forget("san_session:{$sessionKey}");

        [$dlFile, $dlName] = $this->buildDownload($processedPaths, Storage::path($tempDir));

        $token = Str::random(48);
        cache()->put("san_dl:{$token}", [
            'file' => $dlFile,
            'name' => $dlName,
            'dir'  => Storage::path($tempDir),
        ], self::TOKEN_TTL);

        return response()->json([
            'success'  => true,
            'token'    => $token,
            'filename' => $dlName,
            'count'    => count($processedPaths),
            'errors'   => $errors,
        ]);
    }

    // ── Download ───────────────────────────────────────────────────────────────

    public function download(string $token)
    {
        if (! preg_match('/^[a-zA-Z0-9]{48}$/', $token)) {
            abort(404);
        }

        $data = cache()->pull("san_dl:{$token}");

        if (! $data || ! file_exists($data['file'])) {
            abort(404, 'File tidak ditemukan atau link sudah kedaluwarsa.');
        }

        $dir = $data['dir'];
        register_shutdown_function(static function () use ($dir) {
            if (! is_dir($dir)) return;
            $iter = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iter as $entry) {
                $entry->isDir() ? @rmdir($entry->getPathname()) : @unlink($entry->getPathname());
            }
            @rmdir($dir);
        });

        return response()->download($data['file'], $data['name'], [
            'Cache-Control' => 'no-cache, must-revalidate',
            'Pragma'        => 'no-cache',
        ]);
    }

    // ── Threat Scanner ─────────────────────────────────────────────────────────

    private function scanFileForThreats(string $path, string $ext): array
    {
        $threats = [];
        $raw     = file_get_contents($path, false, null, 0, 3 * 1024 * 1024);
        if ($raw === false) return [];

        // JPEG polyglot — appended data after EOI marker
        if (in_array($ext, ['jpg', 'jpeg'], true)) {
            $eoi = strrpos($raw, "\xFF\xD9");
            if ($eoi !== false && ($eoi + 2) < strlen($raw)) {
                $tail = substr($raw, $eoi + 2);
                if (strlen(trim($tail)) > 4) {
                    $threats[] = $this->t('Polyglot File (Appended Data)', 'high',
                        'Data tersembunyi ditemukan setelah JPEG EOI marker — script mungkin disisipkan di akhir file.',
                        'fa-triangle-exclamation');
                    $raw = $tail;
                }
            }
        }

        // PHP backdoor patterns
        $phpRules = [
            ['/<\?php/i',                   'PHP Code Injection',     'critical', 'Tag PHP (<?) ditemukan dalam file media.'],
            ['/eval\s*\(/i',               'PHP eval() Exploit',     'critical', 'eval() — eksekusi kode arbitrary.'],
            ['/base64_decode\s*\(/i',      'Encoded Payload',        'high',     'base64_decode() — payload terenkripsi tersembunyi.'],
            ['/shell_exec\s*\(/i',         'Shell Command Exec',     'critical', 'shell_exec() — menjalankan perintah shell.'],
            ['/system\s*\(/i',             'OS System Command',      'critical', 'system() — eksekusi perintah OS.'],
            ['/passthru\s*\(/i',           'Shell Passthru',         'critical', 'passthru() — output shell ke browser.'],
            ['/proc_open\s*\(/i',          'Process Spawn',          'critical', 'proc_open() — spawn proses baru.'],
            ['/popen\s*\(/i',              'Pipe Open',              'critical', 'popen() — pipe ke proses.'],
            ['/\$_GET\s*\[/i',             '$_GET Access',           'high',     'Akses $_GET parameter URL.'],
            ['/\$_POST\s*\[/i',            '$_POST Access',          'high',     'Akses $_POST parameter form.'],
            ['/\$_REQUEST\s*\[/i',         '$_REQUEST Access',       'high',     'Akses $_REQUEST superglobal.'],
            ['/file_put_contents\s*\(/i',  'File Write',             'high',     'file_put_contents() — penulisan ke disk.'],
            ['/move_uploaded_file\s*\(/i', 'File Upload Handler',    'high',     'move_uploaded_file() — pemindahan file upload.'],
            ['/assert\s*\(/i',             'Assert Code Exec',       'critical', 'assert() — eksekusi kode via assertion.'],
            ['/preg_replace\s*\(.*\/e/i',  'Regex /e Eval',          'critical', 'preg_replace /e — evaluasi kode PHP.'],
            ['/create_function\s*\(/i',    'Dynamic Function',       'high',     'create_function() — fungsi kode dinamis.'],
            ['/gzinflate\s*\(/i',          'Compressed Payload',     'medium',   'gzinflate() — payload terkompresi.'],
            ['/str_rot13\s*\(/i',          'Obfuscated Code',        'medium',   'str_rot13() — teknik obfuskasi.'],
            ['/@eval/i',                   'Silent Eval',            'critical', '@eval — eksekusi tersembunyi (error suppressed).'],
            ['/b374k|phpspy|c99shell|r57shell/i', 'Known Webshell', 'critical', 'Signature webshell terkenal ditemukan.'],
        ];

        foreach ($phpRules as [$pat, $type, $sev, $detail]) {
            if (! $this->seen($threats, $type) && preg_match($pat, $raw)) {
                $threats[] = $this->t($type, $sev, $detail, 'fa-bug');
            }
        }

        // Python malicious patterns
        $pyRules = [
            ['/import\s+subprocess\b/i',             'Python Subprocess',       'critical', 'import subprocess — eksekusi perintah sistem.'],
            ['/import\s+os\b/i',                     'Python OS Module',        'high',     'import os — akses file system & perintah.'],
            ['/import\s+socket\b/i',                 'Python Network Socket',   'high',     'import socket — koneksi jaringan tersembunyi.'],
            ['/os\.system\s*\(/i',                   'Python System Exec',      'critical', 'os.system() — eksekusi perintah OS.'],
            ['/subprocess\.(Popen|call|run)\s*\(/i', 'Python Process',          'critical', 'subprocess execution — menjalankan proses.'],
            ['/base64\.b64decode\s*\(/i',            'Python Encoded Payload',  'high',     'base64.b64decode() — payload tersembunyi.'],
            ['/marshal\.loads\s*\(/i',               'Python Deserialization',  'critical', 'marshal.loads() — deserialisasi berbahaya.'],
            ['/exec\s*\([^)]+\)/i',                  'Python Code Exec',        'critical', 'exec() — eksekusi kode Python arbitrary.'],
        ];

        foreach ($pyRules as [$pat, $type, $sev, $detail]) {
            if (! $this->seen($threats, $type) && preg_match($pat, $raw)) {
                $threats[] = $this->t($type, $sev, $detail, 'fa-code');
            }
        }

        // PDF-specific
        if ($ext === 'pdf') {
            $pdfRules = [
                ['/\/JavaScript\b|\/JS\s+/i',  'PDF JavaScript',         'high',     'JavaScript tersembunyi — dieksekusi saat PDF dibuka.'],
                ['/\/Launch\b/i',               'PDF Launch Action',      'critical', 'Launch action — menjalankan program eksternal.'],
                ['/\/OpenAction\b/i',           'PDF Auto-Execute Action','high',     'OpenAction — aksi berjalan otomatis saat dibuka.'],
                ['/\/EmbeddedFile\b/i',         'PDF Embedded File',      'medium',   'File tersembunyi tertanam dalam PDF.'],
                ['/\/URI\s*\(/i',               'PDF URI Action',         'low',      'URI action ditemukan — link ke URL eksternal.'],
            ];
            foreach ($pdfRules as [$pat, $type, $sev, $detail]) {
                if (! $this->seen($threats, $type) && preg_match($pat, $raw)) {
                    $threats[] = $this->t($type, $sev, $detail, 'fa-file-code');
                }
            }
        }

        return $threats;
    }

    private function t(string $type, string $severity, string $detail, string $icon): array
    {
        return compact('type', 'severity', 'detail', 'icon');
    }

    private function seen(array $threats, string $type): bool
    {
        foreach ($threats as $t) {
            if ($t['type'] === $type) return true;
        }
        return false;
    }

    private function maxSeverity(array $threats): string
    {
        $order = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1, 'safe' => 0];
        $max   = 'safe';
        foreach ($threats as $t) {
            if (($order[$t['severity']] ?? 0) > ($order[$max] ?? 0)) $max = $t['severity'];
        }
        return $max;
    }

    private function fmtBytes(int $bytes): string
    {
        return $bytes < 1048576
            ? round($bytes / 1024, 1) . ' KB'
            : round($bytes / 1048576, 2) . ' MB';
    }

    private function buildDownload(array $files, string $tempDirAbs): array
    {
        if (count($files) === 1) {
            return [reset($files), array_key_first($files)];
        }
        $zipName = 'mediatools_clean_' . date('Ymd_His') . '.zip';
        $zipPath = "{$tempDirAbs}/{$zipName}";
        $zip     = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat arsip ZIP.');
        }
        foreach ($files as $name => $path) {
            $zip->addFile($path, $name);
        }
        $zip->close();
        return [$zipPath, $zipName];
    }
}