<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PDFUtilitiesController extends Controller
{
    public function index()
    {
        return view('tools.pdfutilities.index');
    }

    /**
     * Compress PDF using Ghostscript.
     * File is processed entirely in system temp — NEVER stored permanently.
     * Returns binary PDF directly as response.
     */
    public function compress(Request $request)
    {
        $request->validate([
            'pdf'  => 'required|file|mimes:pdf|max:102400', // 100MB max
            'mode' => 'sometimes|in:low,medium,high',
        ]);

        $file = $request->file('pdf');
        $mode = $request->input('mode', 'medium');

        /*
         * Ghostscript profiles — tuned to exceed iLovePDF results.
         * Strategy: force re-encode all images (PassThroughJPEGImages=false),
         * aggressive downsampling, font subsetting, strip metadata/annotations.
         */
        $profiles = [
            'low' => [
                'color_dpi' => 200,
                'gray_dpi'  => 200,
                'mono_dpi'  => 400,
                'jpeg_q'    => 82,
                'label'     => 'Ringan',
            ],
            'medium' => [
                'color_dpi' => 120,
                'gray_dpi'  => 120,
                'mono_dpi'  => 200,
                'jpeg_q'    => 60,
                'label'     => 'Sedang',
            ],
            'high' => [
                'color_dpi' => 72,
                'gray_dpi'  => 72,
                'mono_dpi'  => 150,
                'jpeg_q'    => 30,
                'label'     => 'Tinggi',
            ],
        ];

        $conf = $profiles[$mode] ?? $profiles['medium'];

        $gs = $this->findGhostscript();
        if (!$gs) {
            return response()->json([
                'error' => 'Ghostscript tidak tersedia di server ini. Hubungi administrator.'
            ], 503);
        }

        // Temp output — no permanent storage
        $outputFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pdf_compress_' . uniqid() . '.pdf';
        $originalSize = $file->getSize();

        try {
            $inputPath  = escapeshellarg($file->getRealPath());
            $outputPath = escapeshellarg($outputFile);

            $cmd = "$gs"
                . " -sDEVICE=pdfwrite"
                . " -dCompatibilityLevel=1.4"
                . " -dNOPAUSE -dQUIET -dBATCH"

                // Duplicate & font optimisation
                . " -dDetectDuplicateImages=true"
                . " -dCompressFonts=true"
                . " -dSubsetFonts=true"
                . " -dEmbedAllFonts=false"

                // Strip non-essential data
                . " -dDiscardMetadata=true"
                . " -dPreserveAnnots=false"
                . " -dPreserveOPIComments=false"
                . " -dPreserveOverprintSettings=false"

                // Image downsampling — Bicubic for best quality-to-size
                . " -dDownsampleColorImages=true"
                . " -dDownsampleGrayImages=true"
                . " -dDownsampleMonoImages=true"
                . " -dColorImageDownsampleType=/Bicubic"
                . " -dGrayImageDownsampleType=/Bicubic"
                . " -dMonoImageDownsampleType=/Subsample"
                . " -dColorImageDownsampleThreshold=1.0"
                . " -dGrayImageDownsampleThreshold=1.0"
                . " -dMonoImageDownsampleThreshold=1.0"
                . " -dColorImageResolution={$conf['color_dpi']}"
                . " -dGrayImageResolution={$conf['gray_dpi']}"
                . " -dMonoImageResolution={$conf['mono_dpi']}"

                // Force re-encode ALL images (key for maximum compression)
                . " -dAutoFilterColorImages=false"
                . " -dAutoFilterGrayImages=false"
                . " -dColorImageFilter=/DCTEncode"
                . " -dGrayImageFilter=/DCTEncode"
                . " -dJPEGQ={$conf['jpeg_q']}"
                . " -dPassThroughJPEGImages=false"
                . " -dPassThroughJPXImages=false"

                // Linearise for fast web view
                . " -dFastWebView=true"

                . " -sOutputFile=$outputPath $inputPath";

            exec($cmd . ' 2>&1', $execOutput, $exitCode);

            if ($exitCode !== 0 || !file_exists($outputFile)) {
                throw new \RuntimeException(
                    'Ghostscript gagal (exit ' . $exitCode . '): ' . implode(' | ', array_slice($execOutput, 0, 3))
                );
            }

            $compressedSize = filesize($outputFile);
            $bytes          = file_get_contents($outputFile);

        } catch (\Throwable $e) {
            // Clean up any partial output
            if (file_exists($outputFile)) {
                @unlink($outputFile);
            }
            return response()->json(['error' => $e->getMessage()], 500);

        } finally {
            // Always delete temp output — zero permanent storage
            if (isset($outputFile) && file_exists($outputFile)) {
                @unlink($outputFile);
            }
        }

        // Return binary PDF directly; expose size headers for JS
        return response($bytes, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="compressed_mediatools.pdf"',
            'Content-Length'      => strlen($bytes),
            'X-Original-Size'     => $originalSize,
            'X-Compressed-Size'   => $compressedSize,
            // Allow JS to read custom headers cross-origin if ever needed
            'Access-Control-Expose-Headers' => 'X-Original-Size, X-Compressed-Size',
        ]);
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    private function findGhostscript(): ?string
    {
        $isWin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        $candidates = $isWin
            ? [
                'gswin64c',
                'gswin32c',
                '"C:\\Program Files\\gs\\gs10.07.0\\bin\\gswin64c.exe"',
                '"C:\\Program Files\\gs\\gs10.05.0\\bin\\gswin64c.exe"',
                '"C:\\Program Files\\gs\\gs10.04.0\\bin\\gswin64c.exe"',
                '"C:\\Program Files\\gs\\gs10.03.1\\bin\\gswin64c.exe"',
            ]
            : ['gs', '/usr/bin/gs', '/usr/local/bin/gs'];

        foreach ($candidates as $candidate) {
            exec($candidate . ' -v 2>&1', $out, $code);
            if ($code === 0) {
                return $candidate;
            }
            $out = [];
        }

        return null;
    }
}