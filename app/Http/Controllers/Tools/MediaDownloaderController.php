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
    // ─── Cobalt API (non-YouTube) ───────────────────────────────
    private string $cobaltUrl = 'https://co.wuk.sh';

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

    // ─── Cookie file (optional, improves YouTube success rate) ──
    private function cookieFile(): ?string
    {
        $f = env('YTDLP_COOKIES', storage_path('app/yt_cookies.txt'));
        return file_exists($f) ? $f : null;
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
    //  YOUTUBE — GET AVAILABLE FORMATS  (real formats from yt-dlp)
    // ──────────────────────────────────────────────────────────────

    private function youtubeGetFormats(string $url): JsonResponse
    {
        $ytdlp = $this->ytdlpBin();

        $cmd = $this->buildCmd([$ytdlp, '--dump-json', '--no-playlist', $url]);
        $output = []; $exit = 0;
        exec($cmd . ' 2>&1', $output, $exit);

        $json = '';
        foreach (array_reverse($output) as $line) {
            $line = trim($line);
            if ($line && $line[0] === '{') { $json = $line; break; }
        }

        if (!$json) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tidak bisa mengambil info video. Cek URL atau pastikan yt-dlp terinstall.',
                'debug'   => implode("\n", array_slice($output, -5)),
            ]);
        }

        $info     = json_decode($json, true);
        $formats  = $info['formats'] ?? [];
        $title    = $info['title'] ?? 'Video';
        $thumb    = $info['thumbnail'] ?? null;
        $duration = $info['duration'] ?? 0;

        // Build quality list (video formats only, with combined audio)
        $qualities = [];
        $seen      = [];

        foreach ($formats as $fmt) {
            $height = $fmt['height'] ?? 0;
            $vcodec = $fmt['vcodec'] ?? 'none';
            $acodec = $fmt['acodec'] ?? 'none';

            if (!$height || $vcodec === 'none') continue;
            if (isset($seen[$height])) continue;

            // Only include common resolutions
            if (!in_array($height, [144, 240, 360, 480, 720, 1080, 1440, 2160])) continue;

            $seen[$height] = true;

            // Estimate filesize (may be null for some formats)
            $filesize = $fmt['filesize'] ?? $fmt['filesize_approx'] ?? null;

            // For formats without audio, we need to add audio stream (~128k)
            $isVideoOnly = ($acodec === 'none');
            if ($isVideoOnly && $filesize) {
                // Add ~1MB per minute for audio
                $filesize += ($duration / 60) * 1_000_000;
            }

            $qualities[] = [
                'height'    => $height,
                'label'     => $this->heightLabel($height),
                'format_id' => $fmt['format_id'],
                'ext'       => $fmt['ext'] ?? 'mp4',
                'filesize'  => $filesize,
                'filesize_fmt' => $filesize ? $this->formatBytes($filesize) : null,
                'fps'       => $fmt['fps'] ?? null,
            ];
        }

        // Sort highest first
        usort($qualities, fn($a, $b) => $b['height'] - $a['height']);

        // Deduplicate by height (keep one entry per resolution)
        $unique = [];
        $uSeen  = [];
        foreach ($qualities as $q) {
            if (!isset($uSeen[$q['height']])) {
                $unique[]            = $q;
                $uSeen[$q['height']] = true;
            }
        }

        if (empty($unique)) {
            // Fallback: provide standard quality options without filesizes
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
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  YOUTUBE — DOWNLOAD
    // ──────────────────────────────────────────────────────────────

    private function youtubeDownload(string $url, Request $request): JsonResponse
    {
        $mode    = $request->input('downloadMode', 'video'); // video | audio
        $height  = (int) $request->input('quality', 720);

        $ytdlp  = $this->ytdlpBin();
        $outDir = storage_path('app/md_temp');
        if (!is_dir($outDir)) mkdir($outDir, 0775, true);

        $token   = Str::random(32);
        $ext     = $mode === 'audio' ? 'mp3' : 'mp4';
        $outFile = "{$outDir}/{$token}.{$ext}";

        if ($mode === 'audio') {
            // ── MP3 Audio ──────────────────────────────────────
            $args = [
                $ytdlp,
                '--no-playlist',
                '-x', '--audio-format', 'mp3',
                '--audio-quality', '0',
                '-o', $outFile,
            ];
        } else {
            // ── MP4 Video (merge video + audio) ───────────────
            $formatStr = "bestvideo[height<={$height}][ext=mp4]+bestaudio[ext=m4a]/bestvideo[height<={$height}]+bestaudio/best[height<={$height}]";
            $args = [
                $ytdlp,
                '--no-playlist',
                '-f', $formatStr,
                '--merge-output-format', 'mp4',
                '-o', $outFile,
            ];
        }

        // Add cookies if available
        $cookieFile = $this->cookieFile();
        if ($cookieFile) {
            array_push($args, '--cookies', $cookieFile);
        }

        $args[] = $url;

        $cmd    = $this->buildCmd($args);
        $output = []; $exit = 0;

        // Increase PHP time limit
        set_time_limit(300);
        exec($cmd . ' 2>&1', $output, $exit);

        if ($exit !== 0 || !file_exists($outFile) || filesize($outFile) === 0) {
            $stderr = implode("\n", array_slice($output, -10));
            Log::error("yt-dlp failed", ['exit' => $exit, 'stderr' => $stderr]);

            return response()->json([
                'status'  => 'error',
                'message' => $this->mapYtdlpError($stderr),
            ]);
        }

        $filesize = filesize($outFile);

        // Store in cache for download endpoint
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
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  NON-YOUTUBE — Cobalt API
    // ──────────────────────────────────────────────────────────────

    private function cobaltDownload(string $url, Request $request): JsonResponse
    {
        $payload = ['url' => $url];

        if ($request->input('downloadMode') === 'audio') {
            $payload['isAudioOnly'] = true;
        }
        if ($request->has('tiktokFullAudio')) {
            $payload['tiktokFullAudio'] = (bool) $request->input('tiktokFullAudio');
        }
        if ($request->has('removeTikTokWatermark')) {
            $payload['removeTikTokWatermark'] = (bool) $request->input('removeTikTokWatermark');
        }

        // Try multiple Cobalt instances
        $instances = [
            $this->cobaltUrl,
            'https://cobalt.tools',
            'https://api.cobalt.tools',
        ];

        foreach ($instances as $base) {
            try {
                $response = Http::timeout(20)
                    ->withHeaders([
                        'Accept'       => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post("{$base}/api/json", $payload);

                if (!$response->successful()) continue;

                $data   = $response->json();
                $status = $data['status'] ?? 'error';

                if (in_array($status, ['stream', 'redirect'])) {
                    // Store URL as a temp proxy token so we can stream it server-side
                    $token = Str::random(32);
                    Cache::put("md_proxy_{$token}", [
                        'url'     => $data['url'],
                        'created' => time(),
                    ], now()->addMinutes(15));

                    return response()->json([
                        'status' => 'ready',
                        'token'  => $token,
                        'mode'   => 'proxy',
                        'type'   => 'video',
                    ]);
                }

                if ($status === 'picker') {
                    return response()->json([
                        'status'  => 'picker',
                        'picker'  => $data['picker'] ?? [],
                    ]);
                }

                if ($status === 'error') {
                    return response()->json([
                        'status'  => 'error',
                        'message' => $this->mapCobaltError($data['text'] ?? ''),
                    ]);
                }

            } catch (\Exception $e) {
                Log::warning("Cobalt instance {$base} failed: " . $e->getMessage());
            }
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Semua server tidak merespons. Coba lagi dalam beberapa menit.',
        ], 502);
    }

    // ──────────────────────────────────────────────────────────────
    //  DOWNLOAD ENDPOINT  (stream file to user)
    // ──────────────────────────────────────────────────────────────

    public function download(Request $request, string $token)
    {
        // Check yt-dlp downloaded file
        $ytData = Cache::get("md_dl_{$token}");
        if ($ytData && file_exists($ytData['path'])) {
            $path = $ytData['path'];
            $mime = $ytData['mime'];
            $ext  = $ytData['ext'];

            Cache::forget("md_dl_{$token}");

            return response()->download($path, "mediatools_video.{$ext}", [
                'Content-Type'    => $mime,
                'Cache-Control'   => 'no-store',
            ])->deleteFileAfterSend(true);
        }

        // Check Cobalt proxy URL
        $proxyData = Cache::get("md_proxy_{$token}");
        if ($proxyData && isset($proxyData['url'])) {
            Cache::forget("md_proxy_{$token}");
            return redirect($proxyData['url']);
        }

        abort(404, 'Link sudah expired. Silakan proses ulang.');
    }

    // ──────────────────────────────────────────────────────────────
    //  CLEANUP (called via scheduled command or manually)
    // ──────────────────────────────────────────────────────────────

    public function cleanup(): JsonResponse
    {
        $dir     = storage_path('app/md_temp');
        $cleaned = 0;
        $maxAge  = 1800; // 30 minutes

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
        if (PHP_OS_FAMILY === 'Windows') {
            return implode(' ', array_map('escapeshellarg', $args));
        }
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
        if (str_contains($r, 'sign in') || str_contains($r, 'login'))
            return 'Video ini memerlukan login YouTube. Tidak bisa didownload secara publik.';
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

    private function mapCobaltError(string $text): string
    {
        $map = [
            'private'     => 'Konten ini bersifat privat.',
            'age'         => 'Konten ini dibatasi usia.',
            'deleted'     => 'Konten tidak tersedia atau telah dihapus.',
            'login'       => 'Konten ini memerlukan login.',
            'unavailable' => 'Konten tidak tersedia.',
            'rate'        => 'Server kelebihan beban. Coba lagi sebentar.',
        ];
        foreach ($map as $k => $v) {
            if (str_contains(strtolower($text), $k)) return $v;
        }
        return $text ?: 'Gagal memproses. Pastikan URL benar dan konten bersifat publik.';
    }
}
