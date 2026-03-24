<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BgRemoverController extends Controller
{
    /** Supported quality → Python model mapping */
    private const MODEL_MAP = [
        'fast'     => ['model' => 'isnet-general-use', 'matting' => 'false'],
        'medium'   => ['model' => 'isnet-general-use', 'matting' => 'true'],
        'high'     => ['model' => 'birefnet-general',  'matting' => 'true'],
        'portrait' => ['model' => 'birefnet-portrait', 'matting' => 'true'],
    ];

    public function index()
    {
        return view('tools.bgremover.index');
    }

    public function process(Request $request)
    {
        /* ── Validate ──────────────────────────────────────── */
        $request->validate([
            'image'   => 'required|image|mimes:jpg,jpeg,png,webp|max:20480',
            'quality' => 'nullable|in:fast,medium,high,portrait',
        ]);

        $file    = $request->file('image');
        $quality = $request->input('quality', 'high');
        $cfg     = self::MODEL_MAP[$quality] ?? self::MODEL_MAP['high'];

        /* ── Temp workspace ────────────────────────────────── */
        $session   = Str::uuid()->toString();
        $dir       = storage_path("app/temp/{$session}");
        $inputPath = "{$dir}/input.png";
        $outPath   = "{$dir}/output.png";

        File::makeDirectory($dir, 0755, true, true);

        try {
            $file->move($dir, 'input.png');

            /* ── Run Python ─────────────────────────────────── */
            $python = config('app.python_path', 'python3');
            $script = base_path('scripts/remove_bg.py');

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

            /* ── Check result ───────────────────────────────── */
            if ($exitCode !== 0 || ! file_exists($outPath)) {
                $detail = implode(' ', array_slice($lines, -3));
                File::deleteDirectory($dir);
                return response()->json([
                    'error' => 'Pemrosesan gagal. ' . $this->friendlyError($detail),
                ], 500);
            }

            /* ── Stream PNG back to browser ─────────────────── */
            $content = file_get_contents($outPath);
            File::deleteDirectory($dir);

            return response($content, 200, [
                'Content-Type'        => 'image/png',
                'Content-Length'      => strlen($content),
                'Cache-Control'       => 'no-store, no-cache',
                'X-Bgr-Model'         => $cfg['model'],
            ]);

        } catch (\Throwable $e) {
            File::deleteDirectory($dir);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /** Turn raw Python stderr into a user-friendly message */
    private function friendlyError(string $raw): string
    {
        if (str_contains($raw, 'Missing dependency') || str_contains($raw, 'ModuleNotFound')) {
            return 'Dependensi Python belum terinstal. Jalankan: pip install rembg[gpu] Pillow';
        }
        if (str_contains($raw, 'python')) {
            return 'Python tidak ditemukan. Pastikan python3 terinstal dan dapat diakses.';
        }
        return 'Hubungi administrator jika masalah berlanjut.';
    }
}