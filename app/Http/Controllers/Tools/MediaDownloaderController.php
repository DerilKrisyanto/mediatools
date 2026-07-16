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

        $url    = trim($request->input('url'));
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
            'downloadMode'  => 'auto', // auto | audio | mute
            'filenameStyle' => 'basic',
        ];

        if ($request->input('downloadMode') === 'audio') {
            $payload['downloadMode'] = 'audio';
        }
        if ($request->boolean('tiktokFullAudio')) {
            $payload['tiktokFullAudio'] = true;
        }

        $base = $this->cobaltUrl();

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post("{$base}/", $payload);
        } catch (\Exception $e) {
            Log::error('Cobalt instance unreachable: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Server pemroses TikTok/Instagram sedang tidak aktif. Coba lagi sebentar.',
            ], 502);
        }

        $data   = $response->json();
        $status = $data['status'] ?? 'error';
        $type   = $payload['downloadMode'] === 'audio' ? 'audio' : 'video';

        if (in_array($status, ['tunnel', 'redirect'], true)) {
            $token = Str::random(32);
            Cache::put("md_proxy_{$token}", [
                'url'      => $data['url'],
                'filename' => $data['filename'] ?? null,
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
            $tunnels = $data['tunnel'] ?? [];
            if (!empty($tunnels)) {
                $token = Str::random(32);
                Cache::put("md_proxy_{$token}", [
                    'url'      => $tunnels[0],
                    'filename' => $data['output']['filename'] ?? null,
                    'created'  => time(),
                ], now()->addMinutes(15));

                return response()->json([
                    'status' => 'ready',
                    'token'  => $token,
                    'mode'   => 'proxy',
                    'type'   => $type,
                ]);
            }
        }

        if ($status === 'picker') {
            return response()->json([
                'status' => 'picker',
                'picker' => $data['picker'] ?? [],
            ]);
        }

        if ($status === 'error') {
            $code = $data['error']['code'] ?? 'unknown';
            Log::warning('Cobalt error', ['code' => $code, 'url' => $url]);
            return response()->json([
                'status'  => 'error',
                'message' => $this->mapCobaltError($code),
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Respons tidak dikenali dari server pemroses. Coba lagi dalam beberapa menit.',
        ], 502);
    }

    // ──────────────────────────────────────────────────────────────
    //  DOWNLOAD ENDPOINT  (stream file ke user)
    // ──────────────────────────────────────────────────────────────

    public function download(Request $request, string $token)
    {
        // 1) File hasil yt-dlp (lokal di server)
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

        // 2) Tunnel dari Cobalt — di-stream lewat server kita sendiri
        //    (Cobalt hanya bisa diakses dari 127.0.0.1, jadi TIDAK boleh redirect() langsung)
        $proxyData = Cache::get("md_proxy_{$token}");
        if ($proxyData && isset($proxyData['url'])) {
            Cache::forget("md_proxy_{$token}");

            set_time_limit(300);

            try {
                $upstream = Http::timeout(120)->withOptions(['stream' => true])->get($proxyData['url']);
            } catch (\Exception $e) {
                Log::error('Gagal fetch tunnel Cobalt: ' . $e->getMessage());
                abort(502, 'Gagal mengambil file dari server pemroses. Silakan proses ulang.');
            }

            if (!$upstream->successful()) {
                abort(502, 'File sudah tidak tersedia di server pemroses. Silakan proses ulang.');
            }

            $filename    = $proxyData['filename'] ?? ('mediatools_' . time() . '.mp4');
            $contentType = $upstream->header('Content-Type') ?: 'application/octet-stream';

            return response()->stream(function () use ($upstream) {
                $body = $upstream->toPsrResponse()->getBody();
                while (!$body->eof()) {
                    echo $body->read(1024 * 512);
                    if (ob_get_level() > 0) { @ob_flush(); }
                    flush();
                }
            }, 200, [
                'Content-Type'        => $contentType,
                'Content-Disposition' => 'attachment; filename="' . addslashes($filename) . '"',
                'Cache-Control'       => 'no-store',
            ]);
        }

        abort(404, 'Link sudah expired. Silakan proses ulang.');
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