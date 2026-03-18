<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MediaDownloaderController extends Controller
{
    private string $cobaltV7 = 'https://downloadapi.stuff.solutions';

    public function index()
    {
        return view('tools.mediadownloader.index');
    }

    public function process(Request $request)
    {
        $request->validate([
            'url' => 'required|string|min:10',
        ]);

        $url = trim($request->input('url'));

        if ($this->isYouTubeUrl($url)) {
            return $this->handleYouTube($url, $request);
        }

        return $this->processCobaltV7($url, $request);
    }


    /**
     * YouTube: kembalikan info + link ke layanan eksternal
     * Cobalt v7 masih bisa handle YouTube sebagian — coba dulu
     */
    private function handleYouTube(string $url, Request $request)
    {
        $downloadMode = $request->input('downloadMode', 'auto');
        $quality      = $request->input('videoQuality', '720');

        $payload = ['url' => $url];

        if ($downloadMode === 'audio') {
            $payload['isAudioOnly'] = true;
        }

        try {
            Log::info("YouTube via Cobalt v7", ['url' => $url]);

            $response = Http::timeout(20)
                ->withHeaders([
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($this->cobaltV7 . '/api/json', $payload);

            if ($response->successful()) {
                $data     = $response->json();
                $v7Status = $data['status'] ?? 'error';

                // Jika berhasil
                if (in_array($v7Status, ['stream', 'redirect'])) {
                    return response()->json([
                        'status' => 'redirect',
                        'url'    => $data['url'],
                        'type'   => $downloadMode === 'audio' ? 'audio' : 'video',
                    ]);
                }

                // Jika error karena perlu login / tidak support
                $errText = strtolower($data['text'] ?? '');
                if (str_contains($errText, 'account') || str_contains($errText, 'login')) {
                    return $this->youtubeExternalFallback($url, $downloadMode);
                }
            }

            // Gagal → fallback ke external
            return $this->youtubeExternalFallback($url, $downloadMode);

        } catch (\Exception $e) {
            Log::warning("YouTube Cobalt v7 failed: " . $e->getMessage());
            return $this->youtubeExternalFallback($url, $downloadMode);
        }
    }

    /**
     * Fallback: beri user pilihan layanan eksternal untuk YouTube
     * Ini transparan dan honest — tidak pura-pura bisa
     */
    private function youtubeExternalFallback(string $url, string $mode)
    {
        $encodedUrl = urlencode($url);

        $services = $mode === 'audio'
            ? [
                [
                    'name' => 'Y2Mate MP3',
                    'url'  => "https://www.y2mate.com/youtube-mp3/{$encodedUrl}",
                    'desc' => 'Konversi YouTube ke MP3',
                ],
                [
                    'name' => 'SSYouTube Audio',
                    'url'  => "https://ssyoutube.com/en/youtube-to-mp3?url={$encodedUrl}",
                    'desc' => 'Download audio YouTube',
                ],
            ]
            : [
                [
                    'name' => 'SSYouTube',
                    'url'  => str_replace(
                        ['https://www.youtube.com', 'https://youtube.com', 'https://youtu.be'],
                        ['https://www.ssyoutube.com', 'https://ssyoutube.com', 'https://ssyoutu.be'],
                        $url
                    ),
                    'desc' => 'Download video YouTube (ganti www → ss)',
                ],
                [
                    'name' => 'SaveFrom',
                    'url'  => "https://en.savefrom.net/#url={$encodedUrl}",
                    'desc' => 'Download video YouTube kualitas pilihan',
                ],
            ];

        return response()->json([
            'status'   => 'youtube_external',
            'message'  => 'YouTube memerlukan layanan khusus. Gunakan salah satu pilihan di bawah.',
            'services' => $services,
            'original_url' => $url,
        ]);
    }

    private function processCobaltV7(string $url, Request $request)
    {
        $payload = ['url' => $url];

        $downloadMode = $request->input('downloadMode');
        if ($downloadMode === 'audio') {
            $payload['isAudioOnly'] = true;
        }

        if ($request->has('tiktokFullAudio')) {
            $payload['tiktokFullAudio'] = (bool) $request->input('tiktokFullAudio');
        }

        try {
            Log::info("Cobalt v7 request", ['url' => $url]);

            $response = Http::timeout(25)
                ->withHeaders([
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($this->cobaltV7 . '/api/json', $payload);

            $statusCode = $response->status();

            Log::info("Cobalt v7 response HTTP {$statusCode}", [
                'body' => substr($response->body(), 0, 400),
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Server error HTTP {$statusCode}. Coba lagi.",
                ], 502);
            }

            $data     = $response->json();
            $v7Status = $data['status'] ?? 'error';

            if ($v7Status === 'error' || $v7Status === 'rate-limit') {
                return response()->json([
                    'status'  => 'error',
                    'message' => $this->mapV7Error($data['text'] ?? ''),
                    'error'   => ['code' => $data['text'] ?? 'error.unknown'],
                ]);
            }

            if ($v7Status === 'stream' || $v7Status === 'redirect') {
                return response()->json([
                    'status' => 'redirect',
                    'url'    => $data['url'],
                    'type'   => isset($data['isAudio']) && $data['isAudio'] ? 'audio' : 'video',
                ]);
            }

            if ($v7Status === 'picker') {
                $pickerItems = array_map(fn($item) => [
                    'url'   => $item['url'],
                    'thumb' => $item['thumb'] ?? null,
                    'type'  => $item['type'] ?? 'video',
                ], $data['picker'] ?? []);

                return response()->json([
                    'status'  => 'picker',
                    'picker'  => $pickerItems,
                ]);
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'Respons tidak dikenali dari server.',
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tidak bisa terhubung ke server. Coba lagi.',
            ], 502);
        } catch (\Exception $e) {
            Log::error("Cobalt v7 Exception: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan internal.',
            ], 500);
        }
    }

    private function mapV7Error(string $text): string
    {
        $map = [
            'private'     => 'Konten ini bersifat privat.',
            'age'         => 'Konten ini dibatasi usia.',
            'deleted'     => 'Konten tidak tersedia atau telah dihapus.',
            'login'       => 'Konten ini memerlukan login.',
            'unavailable' => 'Konten tidak tersedia.',
            'account'     => 'Konten ini memerlukan akun YouTube.',
        ];
        foreach ($map as $key => $msg) {
            if (str_contains(strtolower($text), $key)) return $msg;
        }
        return $text ?: 'Gagal memproses URL. Pastikan link benar dan konten bersifat publik.';
    }

    private function isYouTubeUrl(string $url): bool
    {
        return (bool) preg_match('/youtube\.com|youtu\.be/i', $url);
    }
}