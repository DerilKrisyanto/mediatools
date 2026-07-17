<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MediaDownloaderController extends Controller
{
    // ─── yt-dlp binary ─────────────────────────────────────────
    private function ytdlpBin(): string
    {
        $env = env('YTDLP_BINARY', '');
        if ($env && (file_exists($env) || $this->binExists($env))) return $env;

        if (PHP_OS_FAMILY === 'Windows') {
            $paths = [
                'C:\\tools\\yt-dlp.exe',
                'C:\\yt-dlp\\yt-dlp.exe',
                base_path('yt-dlp.exe'),
            ];
            foreach ($paths as $p) if (file_exists($p)) return $p;
            return 'yt-dlp';
        }

        foreach (['/usr/local/bin/yt-dlp', '/usr/bin/yt-dlp', 'yt-dlp'] as $b) {
            if ($this->binExists($b)) return $b;
        }
        return 'yt-dlp';
    }

    private function binExists(string $bin): bool
    {
        if (file_exists($bin)) return true;
        $check = PHP_OS_FAMILY === 'Windows' ? "where \"{$bin}\"" : "which {$bin}";
        exec($check . ' 2>&1', $out, $code);
        return $code === 0;
    }

    // ─── Cookie file (sangat menentukan keberhasilan di VPS) ────
    private function cookieFile(): ?string
    {
        $f = env('YTDLP_COOKIES', storage_path('app/yt_cookies.txt'));
        return file_exists($f) ? $f : null;
    }

    // ─── Cobalt instance (self-hosted, internal only) ───────────
    private function cobaltUrl(): string
    {
        return rtrim(config('services.cobalt.url', env('COBALT_API_URL', 'http://127.0.0.1:9000')), '/');
    }

    // ──────────────────────────────────────────────────────────────
    //  INDEX
    // ──────────────────────────────────────────────────────────────

    public function index()
    {
        return view('tools.mediadownloader.index');
    }

    // ──────────────────────────────────────────────────────────────
    //  PROCESS  (main entry)
    // ──────────────────────────────────────────────────────────────

    public function process(Request $request): JsonResponse
    {
        $request->validate(['url' => 'required|string|min:10|max:2048']);

        $url    = $this->normalizeUrl(trim($request->input('url')));
        $action = $request->input('action', 'process'); // process | get_formats

        if ($this->isYouTubeUrl($url)) {
            if ($action === 'get_formats') {
                return $this->youtubeGetFormats($url);
            }
            return $this->youtubeDownload($url, $request);
        }

        return $this->cobaltDownload($url, $request);
    }

    // ──────────────────────────────────────────────────────────────
    //  YOUTUBE — MULTI-CLIENT ATTEMPT HELPER
    //  Coba cookies dulu (kualitas penuh), lalu fallback ke client
    //  yang biasanya lolos bot-check tanpa login (kualitas terbatas).
    // ──────────────────────────────────────────────────────────────

    private function ytdlpAttemptChain(): array
    {
        $chain = [];

        if ($this->cookieFile()) {
            $chain[] = ['label' => 'cookies', 'args' => ['--cookies', $this->cookieFile()]];
        }

        // Urutan ini yang paling sering berhasil tanpa login per laporan komunitas 2026
        $chain[] = ['label' => 'tv',      'args' => ['--extractor-args', 'youtube:player_client=tv']];
        $chain[] = ['label' => 'android', 'args' => ['--extractor-args', 'youtube:player_client=android']];
        $chain[] = ['label' => 'ios',     'args' => ['--extractor-args', 'youtube:player_client=ios']];

        return $chain;
    }

    private function ytdlpAttempt(array $extraArgs, string $url): array
    {
        $ytdlp = $this->ytdlpBin();
        $chain = $this->ytdlpAttemptChain();

        $lastOutput = [];
        $lastExit   = 1;
        $lastLabel  = 'none';

        foreach ($chain as $attempt) {
            $args = array_merge([$ytdlp], $extraArgs, $attempt['args'], ['-4', $url]);
            $cmd  = $this->buildCmd($args);

            $output = []; $exit = 0;
            set_time_limit(300);
            exec($cmd . ' 2>&1', $output, $exit);

            $lastOutput = $output;
            $lastExit   = $exit;
            $lastLabel  = $attempt['label'];

            if ($exit === 0) {
                return ['success' => true, 'output' => $output, 'exit' => 0, 'client' => $attempt['label']];
            }

            $stderr     = strtolower(implode("\n", $output));
            $isAuthWall = str_contains($stderr, 'sign in') || str_contains($stderr, 'confirm you') || str_contains($stderr, 'login_required');

            // Kalau kegagalannya BUKAN soal bot-wall (mis. video privat/dihapus), tidak perlu coba client lain.
            if (!$isAuthWall) {
                return ['success' => false, 'output' => $output, 'exit' => $exit, 'client' => $attempt['label']];
            }
            // Kalau auth-wall, lanjut coba client berikutnya di chain.
        }

        return ['success' => false, 'output' => $lastOutput, 'exit' => $lastExit, 'client' => $lastLabel];
    }

    // ──────────────────────────────────────────────────────────────
    //  YOUTUBE — GET AVAILABLE FORMATS
    // ──────────────────────────────────────────────────────────────

    private function youtubeGetFormats(string $url): JsonResponse
    {
        $result = $this->ytdlpAttempt(['--dump-json', '--no-playlist'], $url);

        if (!$result['success']) {
            $stderr = implode("\n", array_slice($result['output'], -8));
            Log::warning('yt-dlp get_formats failed', ['client' => $result['client'], 'stderr' => $stderr]);
            return response()->json([
                'status'  => 'error',
                'message' => $this->mapYtdlpError($stderr),
            ]);
        }

        $json = '';
        foreach (array_reverse($result['output']) as $line) {
            $line = trim($line);
            if ($line && $line[0] === '{') { $json = $line; break; }
        }

        if (!$json) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tidak bisa mengambil info video. Cek URL atau pastikan yt-dlp terinstall.',
            ]);
        }

        $info     = json_decode($json, true);
        $formats  = $info['formats'] ?? [];
        $title    = $info['title'] ?? 'Video';
        $thumb    = $info['thumbnail'] ?? null;
        $duration = $info['duration'] ?? 0;

        $qualities = [];
        $seen      = [];

        foreach ($formats as $fmt) {
            $height = $fmt['height'] ?? 0;
            $vcodec = $fmt['vcodec'] ?? 'none';
            $acodec = $fmt['acodec'] ?? 'none';

            if (!$height || $vcodec === 'none') continue;
            if (isset($seen[$height])) continue;
            if (!in_array($height, [144, 240, 360, 480, 720, 1080, 1440, 2160])) continue;

            $seen[$height] = true;

            $filesize = $fmt['filesize'] ?? $fmt['filesize_approx'] ?? null;
            $isVideoOnly = ($acodec === 'none');
            if ($isVideoOnly && $filesize) {
                $filesize += ($duration / 60) * 1_000_000;
            }

            $qualities[] = [
                'height'       => $height,
                'label'        => $this->heightLabel($height),
                'format_id'    => $fmt['format_id'],
                'ext'          => $fmt['ext'] ?? 'mp4',
                'filesize'     => $filesize,
                'filesize_fmt' => $filesize ? $this->formatBytes($filesize) : null,
                'fps'          => $fmt['fps'] ?? null,
            ];
        }

        usort($qualities, fn($a, $b) => $b['height'] - $a['height']);

        $unique = [];
        $uSeen  = [];
        foreach ($qualities as $q) {
            if (!isset($uSeen[$q['height']])) {
                $unique[]            = $q;
                $uSeen[$q['height']] = true;
            }
        }

        if (empty($unique)) {
            $unique = [
                ['height' => 1080, 'label' => '1080p FHD', 'filesize_fmt' => null],
                ['height' => 720,  'label' => '720p HD',   'filesize_fmt' => null],
                ['height' => 480,  'label' => '480p',      'filesize_fmt' => null],
                ['height' => 360,  'label' => '360p',      'filesize_fmt' => null],
            ];
        }

        return response()->json([
            'status'    => 'formats',
            'title'     => $title,
            'thumb'     => $thumb,
            'duration'  => $duration,
            'qualities' => $unique,
            'client'    => $result['client'],
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  YOUTUBE — DOWNLOAD
    // ──────────────────────────────────────────────────────────────

    private function youtubeDownload(string $url, Request $request): JsonResponse
    {
        $mode   = $request->input('downloadMode', 'video'); // video | audio
        $height = (int) $request->input('quality', 720);

        $outDir = storage_path('app/md_temp');
        if (!is_dir($outDir)) mkdir($outDir, 0775, true);

        $token   = Str::random(32);
        $ext     = $mode === 'audio' ? 'mp3' : 'mp4';
        $outFile = "{$outDir}/{$token}.{$ext}";

        if ($mode === 'audio') {
            $extraArgs = [
                '--no-playlist',
                '-x', '--audio-format', 'mp3',
                '--audio-quality', '0',
                '-o', $outFile,
            ];
        } else {
            $formatStr = "bestvideo[height<={$height}][ext=mp4]+bestaudio[ext=m4a]/bestvideo[height<={$height}]+bestaudio/best[height<={$height}]";
            $extraArgs = [
                '--no-playlist',
                '-f', $formatStr,
                '--merge-output-format', 'mp4',
                '-o', $outFile,
            ];
        }

        $result = $this->ytdlpAttempt($extraArgs, $url);

        if (!$result['success'] || !file_exists($outFile) || filesize($outFile) === 0) {
            $stderr = implode("\n", array_slice($result['output'], -10));
            Log::error('yt-dlp download failed', ['client' => $result['client'], 'stderr' => $stderr]);

            return response()->json([
                'status'  => 'error',
                'message' => $this->mapYtdlpError($stderr),
            ]);
        }

        $filesize = filesize($outFile);

        Cache::put("md_dl_{$token}", [
            'path'    => $outFile,
            'mime'    => $mode === 'audio' ? 'audio/mpeg' : 'video/mp4',
            'ext'     => $ext,
            'created' => time(),
        ], now()->addMinutes(30));

        return response()->json([
            'status'   => 'ready',
            'token'    => $token,
            'ext'      => $ext,
            'filesize' => $this->formatBytes($filesize),
            'type'     => $mode,
            'client'   => $result['client'],
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  NON-YOUTUBE — Cobalt API (self-hosted, skema v10+)
    // ──────────────────────────────────────────────────────────────

    private function cobaltDownload(string $url, Request $request): JsonResponse
    {
        $payload = [
            'url'           => $url,
            'downloadMode'  => 'auto',
            'filenameStyle' => 'basic',
        ];

        if ($request->input('downloadMode') === 'audio') {
            $payload['downloadMode'] = 'audio';
        }
        if ($request->boolean('tiktokFullAudio')) {
            $payload['tiktokFullAudio'] = true;
        }

        $base   = $this->cobaltUrl();
        $type   = $payload['downloadMode'] === 'audio' ? 'audio' : 'video';
        $data   = $this->cobaltRequest($base, $payload);
        $status = $data['status'] ?? 'error';

        if (in_array($status, ['tunnel', 'redirect'], true)) {
            $token = Str::random(32);
            Cache::put("md_proxy_{$token}", [
                'url'      => $data['url'],
                'filename' => $data['filename'] ?? null,
                'type'     => $type,
                'created'  => time(),
            ], now()->addMinutes(15));

            return response()->json([
                'status' => 'ready',
                'token'  => $token,
                'mode'   => 'proxy',
                'type'   => $type,
            ]);
        }

        if ($status === 'local-processing') {
            return $this->handleLocalProcessing($data, $type);
        }

        if ($status === 'picker') {
            return response()->json([
                'status' => 'picker',
                'picker' => $data['picker'] ?? [],
            ]);
        }

        if ($status === 'error') {
            $code = $data['error']['code'] ?? 'unknown';
            Log::warning('Cobalt error (final, setelah retry)', ['code' => $code, 'url' => $url]);
            return response()->json([
                'status'  => 'error',
                'message' => $this->mapCobaltError($code),
            ]);
        }

        Log::warning('Cobalt: status tidak dikenali', ['raw' => $data]);
        return response()->json([
            'status'  => 'error',
            'message' => 'Respons tidak dikenali dari server pemroses. Coba lagi dalam beberapa menit.',
        ], 502);
    }

    // Retry otomatis (2x) — TikTok sering gagal sekali di percobaan pertama
    private function cobaltRequest(string $base, array $payload, int $attempts = 2): array
    {
        $last = ['status' => 'error', 'error' => ['code' => 'connection_failed']];

        for ($i = 0; $i < $attempts; $i++) {
            try {
                $response = Http::timeout(30)
                    ->withHeaders(['Accept' => 'application/json', 'Content-Type' => 'application/json'])
                    ->post("{$base}/", $payload);
                $data = $response->json() ?? [];
                $last = $data;
                if (($data['status'] ?? null) !== 'error') {
                    return $data;
                }
                Log::info("Cobalt error di percobaan " . ($i + 1) . ", mencoba lagi...", ['code' => $data['error']['code'] ?? null]);
            } catch (\Exception $e) {
                Log::warning("Cobalt tidak terhubung (percobaan " . ($i + 1) . "): " . $e->getMessage());
                $last = ['status' => 'error', 'error' => ['code' => 'connection_failed']];
            }
            if ($i < $attempts - 1) usleep(700_000); // jeda 0.7 detik sebelum retry
        }

        return $last;
    }

    // ──────────────────────────────────────────────────────────────
    //  COBALT "local-processing" — gabung video+audio terpisah
    //  dengan ffmpeg (dipakai TikTok saat video & audio dipisah)
    // ──────────────────────────────────────────────────────────────

    private function handleLocalProcessing(array $data, string $type): JsonResponse
    {
        $tunnels = $data['tunnel'] ?? [];
        if (empty($tunnels)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Server pemroses tidak mengembalikan file yang valid. Coba lagi.',
            ]);
        }

        $outDir = storage_path('app/md_temp');
        if (!is_dir($outDir)) mkdir($outDir, 0775, true);

        set_time_limit(300);
        $token     = Str::random(32);
        $tempFiles = [];

        try {
            // Ambil semua stream (video-only + audio-only, atau cukup 1 stream)
            foreach ($tunnels as $i => $tunnelUrl) {
                $tmpPath = "{$outDir}/{$token}_part{$i}.tmp";
                $resp    = Http::timeout(120)->withOptions(['sink' => $tmpPath])->get($tunnelUrl);

                if (!$resp->successful() || !file_exists($tmpPath) || filesize($tmpPath) === 0) {
                    throw new \RuntimeException('Gagal mengambil bagian file dari server pemroses.');
                }
                $tempFiles[] = $tmpPath;
            }

            $ext     = $type === 'audio' ? 'mp3' : 'mp4';
            $outFile = "{$outDir}/{$token}.{$ext}";
            $ffmpeg  = env('FFMPEG_BINARY', 'ffmpeg');

            if (count($tempFiles) >= 2) {
                // Video (index 0) + audio (index 1) terpisah — mux tanpa re-encode
                $cmd = $this->buildCmd([
                    $ffmpeg, '-y',
                    '-i', $tempFiles[0],
                    '-i', $tempFiles[1],
                    '-map', '0:v:0', '-map', '1:a:0',
                    '-c', 'copy',
                    $outFile,
                ]);
                $out = []; $exit = 0;
                exec($cmd . ' 2>&1', $out, $exit);

                if ($exit !== 0 || !file_exists($outFile) || filesize($outFile) === 0) {
                    // Fallback: codec audio tidak kompatibel utk copy langsung → re-encode ke AAC
                    $cmd2 = $this->buildCmd([
                        $ffmpeg, '-y',
                        '-i', $tempFiles[0],
                        '-i', $tempFiles[1],
                        '-map', '0:v:0', '-map', '1:a:0',
                        '-c:v', 'copy', '-c:a', 'aac',
                        $outFile,
                    ]);
                    $out2 = []; $exit2 = 0;
                    exec($cmd2 . ' 2>&1', $out2, $exit2);

                    if ($exit2 !== 0 || !file_exists($outFile) || filesize($outFile) === 0) {
                        Log::error('ffmpeg merge failed', ['stderr' => implode("\n", array_slice($out2, -10))]);
                        throw new \RuntimeException('Gagal menggabungkan video & audio.');
                    }
                }
            } else {
                // Hanya 1 stream (mute/audio/remux/gif) — langsung pakai
                rename($tempFiles[0], $outFile);
            }

            foreach ($tempFiles as $f) { if (file_exists($f)) @unlink($f); }

            Cache::put("md_dl_{$token}", [
                'path'    => $outFile,
                'mime'    => $type === 'audio' ? 'audio/mpeg' : 'video/mp4',
                'ext'     => $ext,
                'created' => time(),
            ], now()->addMinutes(30));

            return response()->json([
                'status' => 'ready',
                'token'  => $token,
                'type'   => $type,
            ]);

        } catch (\Throwable $e) {
            foreach ($tempFiles as $f) { if (file_exists($f)) @unlink($f); }
            Log::error('Local-processing merge failed: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memproses file TikTok/Instagram. Coba lagi dalam beberapa saat.',
            ]);
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  DOWNLOAD ENDPOINT  (stream file ke user)
    // ──────────────────────────────────────────────────────────────

    public function download(Request $request, string $token)
    {
        // 1) File hasil yt-dlp / ffmpeg merge (lokal di server)
        $ytData = Cache::get("md_dl_{$token}");
        if ($ytData && file_exists($ytData['path'])) {
            $path = $ytData['path'];
            $mime = $ytData['mime'];
            $ext  = $ytData['ext'];

            Cache::forget("md_dl_{$token}");

            return response()->download($path, "mediatools_video.{$ext}", [
                'Content-Type'  => $mime,
                'Cache-Control' => 'no-store',
            ])->deleteFileAfterSend(true);
        }

        // 2) Tunnel dari Cobalt — validasi dulu (peek), baru stream penuh
        $proxyData = Cache::get("md_proxy_{$token}");
        if ($proxyData && isset($proxyData['url'])) {
            Cache::forget("md_proxy_{$token}");

            $type = $proxyData['type'] ?? 'video';

            // Cek kadaluwarsa tunnel dari parameter "exp" di URL-nya sendiri
            $query = [];
            parse_str((string) parse_url($proxyData['url'], PHP_URL_QUERY), $query);
            if (!empty($query['exp']) && (int) $query['exp'] < (int) (microtime(true) * 1000)) {
                abort(410, 'Link download sudah kedaluwarsa (lebih dari beberapa menit). Silakan klik Download Sekarang lagi.');
            }

            return $this->streamRemoteFile($proxyData['url'], $proxyData['filename'] ?? null, $type);
        }

        abort(404, 'Link sudah expired. Silakan proses ulang.');
    }

    // Cek cepat 2KB pertama sebelum commit ke browser — mencegah "video" palsu
    // (misalnya halaman error HTML) ikut ter-download sebagai .mp4
    // Cek + stream sekaligus dalam SATU request (tunnel Cobalt kemungkinan
    // sekali-pakai/berumur pendek — jangan pernah fetch tunnel yang sama 2x)
    private function streamRemoteFile(string $url, ?string $filename, string $type = 'video')
    {
        $ext      = $type === 'audio' ? 'mp3' : 'mp4';
        $mime     = $type === 'audio' ? 'audio/mpeg' : 'video/mp4';
        $filename = $filename ?: ('mediatools_' . time() . '.' . $ext);

        return response()->stream(function () use ($url) {
            @set_time_limit(0);

            $valid       = null;  // null = belum tahu, diputuskan begitu header final diterima
            $sentInvalid = false;

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_MAXREDIRS       => 5,
                CURLOPT_CONNECTTIMEOUT  => 20,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_LOW_SPEED_LIMIT => 500,
                CURLOPT_LOW_SPEED_TIME  => 30,
                CURLOPT_HTTPHEADER      => ['Accept: */*'],
                CURLOPT_HEADERFUNCTION  => function ($curl, $header) use (&$valid) {
                    $len     = strlen($header);
                    $trimmed = trim($header);

                    // Setiap kali ada status line baru (termasuk saat redirect),
                    // reset validasi — yang dipakai cuma response TERAKHIR.
                    if (stripos($trimmed, 'HTTP/') === 0) {
                        $valid = null;
                    }
                    if (stripos($trimmed, 'content-type:') === 0) {
                        $ct    = trim(substr($trimmed, strlen('content-type:')));
                        $valid = (bool) preg_match('#^(video|audio|application/octet-stream)#i', $ct);
                    }
                    return $len;
                },
                CURLOPT_WRITEFUNCTION => function ($curl, $chunk) use (&$valid, &$sentInvalid) {
                    if ($valid === false) {
                        $sentInvalid = true;
                        return -1; // hentikan transfer segera
                    }
                    echo $chunk;
                    if (ob_get_level() > 0) { @ob_flush(); }
                    @flush();
                    return strlen($chunk);
                },
            ]);

            $ok       = curl_exec($ch);
            $err      = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (!$ok || $sentInvalid || $valid === false) {
                Log::error('Tunnel stream gagal atau bukan file media', [
                    'curl_error' => $err,
                    'http_code'  => $httpCode,
                    'valid'      => $valid,
                ]);
            }
        }, 200, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'attachment; filename="' . addslashes($filename) . '"',
            'Cache-Control'       => 'no-store',
            'X-Accel-Buffering'   => 'no',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  CLEANUP
    // ──────────────────────────────────────────────────────────────

    public function cleanup(): JsonResponse
    {
        $dir     = storage_path('app/md_temp');
        $cleaned = 0;
        $maxAge  = 1800;

        if (!is_dir($dir)) return response()->json(['cleaned' => 0]);

        foreach (glob("{$dir}/*") ?: [] as $file) {
            if (is_file($file) && (time() - filemtime($file)) > $maxAge) {
                @unlink($file);
                $cleaned++;
            }
        }

        return response()->json(['success' => true, 'cleaned' => $cleaned]);
    }

    // ──────────────────────────────────────────────────────────────
    //  HELPERS
    // ──────────────────────────────────────────────────────────────

    private function buildCmd(array $args): string
    {
        return implode(' ', array_map('escapeshellarg', $args));
    }

    private function isYouTubeUrl(string $url): bool
    {
        return (bool) preg_match('/youtube\.com|youtu\.be/i', $url);
    }

    private function normalizeUrl(string $url): string
    {
        // Instagram: ambil hanya /reel|p|tv/{kode}/, buang semua query string (?utm_source=...)
        if (preg_match('#instagram\.com/(reel|p|tv)/([A-Za-z0-9_-]+)#i', $url, $m)) {
            return "https://www.instagram.com/{$m[1]}/{$m[2]}/";
        }

        // TikTok (link video biasa): ambil hanya sampai /video/{id}, buang semua setelah "?"
        if (preg_match('#tiktok\.com/@([\w.\-]+)/video/(\d+)#i', $url, $m)) {
            return "https://www.tiktok.com/@{$m[1]}/video/{$m[2]}";
        }

        // TikTok short link (vm.tiktok.com/xxxx atau vt.tiktok.com/xxxx) — buang query string saja
        if (preg_match('#^(https?://(?:vm|vt)\.tiktok\.com/[A-Za-z0-9]+/?)#i', $url, $m)) {
            return $m[1];
        }

        return $url;
    }

    private function heightLabel(int $h): string
    {
        return match(true) {
            $h >= 2160 => '4K (2160p)',
            $h >= 1440 => '2K (1440p)',
            $h >= 1080 => '1080p Full HD',
            $h >= 720  => '720p HD',
            $h >= 480  => '480p',
            $h >= 360  => '360p',
            $h >= 240  => '240p',
            default    => "{$h}p",
        };
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) return '—';
        $u = ['B','KB','MB','GB'];
        $i = (int) floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 1) . ' ' . $u[$i];
    }

    private function mapYtdlpError(string $raw): string
    {
        $r = strtolower($raw);
        if (str_contains($r, 'private video') || str_contains($r, 'private'))
            return 'Video ini bersifat privat dan tidak bisa didownload.';
        if (str_contains($r, 'age') && str_contains($r, 'restrict'))
            return 'Video ini memiliki batasan usia. Pemilik membatasi aksesnya.';
        if (str_contains($r, 'unavailable') || str_contains($r, 'removed'))
            return 'Video tidak tersedia atau sudah dihapus.';
        if (str_contains($r, 'sign in') || str_contains($r, 'confirm you') || str_contains($r, 'login'))
            return 'YouTube sedang membatasi akses dari server kami. Tim kami sudah diberi tahu — coba lagi dalam beberapa menit.';
        if (str_contains($r, 'copyright') || str_contains($r, 'blocked'))
            return 'Video diblokir karena masalah hak cipta di wilayah ini.';
        if (str_contains($r, 'not found') || str_contains($r, 'no such'))
            return 'yt-dlp tidak ditemukan. Jalankan install script terlebih dahulu.';
        if (str_contains($r, 'network'))
            return 'Masalah jaringan. Coba lagi dalam beberapa saat.';
        if (str_contains($r, 'ffmpeg'))
            return 'FFmpeg tidak ditemukan. Install FFmpeg untuk download video berkualitas tinggi.';
        return 'Gagal mendownload. Pastikan URL benar dan video bersifat publik.';
    }

    private function mapCobaltError(string $code): string
    {
        $c = strtolower($code);
        $map = [
            'private'     => 'Konten ini bersifat privat.',
            'age'         => 'Konten ini dibatasi usia.',
            'unavailable' => 'Konten tidak tersedia atau sudah dihapus.',
            'not_found'   => 'Konten tidak ditemukan.',
            'rate'        => 'Server sumber sedang membatasi permintaan. Coba lagi sebentar.',
            'unsupported' => 'URL tidak didukung. Pastikan link dari platform yang didukung.',
            'invalid'     => 'URL tidak valid. Periksa kembali link yang Anda masukkan.',
            'geo'         => 'Konten tidak tersedia di wilayah server kami.',
        ];
        foreach ($map as $k => $v) {
            if (str_contains($c, $k)) return $v;
        }
        return 'Gagal memproses. Pastikan URL benar dan konten bersifat publik.';
    }
}