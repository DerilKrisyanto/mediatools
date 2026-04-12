<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProposalBuilderController extends Controller
{
    /* ═══════════════════════════════════════════════════
       PAGE
    ═══════════════════════════════════════════════════ */

    public function index()
    {
        return view('tools.proposal.index');
    }

    /* ═══════════════════════════════════════════════════
       GENERATE — returns HTML preview only
    ═══════════════════════════════════════════════════ */

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'template'      => 'required|in:mahasiswa,freelancer,bisnis,event',
            'template_name' => 'required|string|max:200',
            'form_data'     => 'required|array',
            'logo'          => 'nullable|string',
        ]);

        $template     = $request->input('template');
        $templateName = $request->input('template_name');
        $formData     = $request->input('form_data', []);
        $logoBase64   = $request->input('logo');

        try {
            $bodyContent = $this->buildContent($template, $formData);
            $html        = $this->wrapInDocument($bodyContent, $templateName, $formData, $logoBase64, $template);

            $cacheKey = 'proposal_html_' . Str::random(32);
            Cache::put($cacheKey, [
                'html'     => $html,
                'template' => $template,
                'data'     => $formData,
                'title'    => $this->getDocTitle($formData, $templateName),
            ], now()->addHours(2));

            return response()->json([
                'success'   => true,
                'html'      => $html,
                'cache_key' => $cacheKey,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /* ═══════════════════════════════════════════════════
       DOWNLOAD — convert HTML → DOCX or PDF via LibreOffice
    ═══════════════════════════════════════════════════ */

    public function download(Request $request): JsonResponse
    {
        $request->validate([
            'cache_key' => 'required|string|max:64',
            'format'    => 'required|in:docx,pdf',
            'html'      => 'nullable|string',
        ]);

        $cacheKey = $request->input('cache_key');
        $format   = $request->input('format');

        $cached = Cache::get($cacheKey);
        if ($cached) {
            $html  = $cached['html'];
            $title = $cached['title'] ?? 'Proposal';
        } elseif ($request->filled('html')) {
            $html  = $request->input('html');
            $title = 'Proposal';
        } else {
            return response()->json(['error' => 'Sesi habis. Silakan generate ulang.'], 404);
        }

        $safeTitle = Str::slug(Str::limit($title, 60), '_') ?: 'proposal';
        $filename  = $safeTitle . '.' . $format;

        $workDir = storage_path('app/proposal_builder/' . Str::random(16));
        @mkdir($workDir, 0775, true);

        $htmlPath = $workDir . '/proposal.html';
        file_put_contents($htmlPath, $html);

        try {
            if ($format === 'pdf') {
                $result = $this->convertWithLibreOffice($htmlPath, $workDir, 'pdf');
            } else {
                $result = $this->convertWithLibreOffice($htmlPath, $workDir, 'docx');
            }

            if (!$result['success']) {
                @array_map('unlink', glob($workDir . '/*'));
                @rmdir($workDir);
                return response()->json(['error' => $result['error'] ?? 'Konversi gagal.'], 500);
            }

            $convertedPath = $result['output'];

            if (!file_exists($convertedPath) || filesize($convertedPath) === 0) {
                @array_map('unlink', glob($workDir . '/*'));
                @rmdir($workDir);
                return response()->json(['error' => 'File hasil konversi tidak ditemukan.'], 500);
            }

            $token = $this->storeToken($convertedPath, $workDir, $filename);

            return response()->json([
                'success'  => true,
                'token'    => $token,
                'filename' => $filename,
                'format'   => $format,
            ]);

        } catch (\Exception $e) {
            @array_map('unlink', glob($workDir . '/*'));
            @rmdir($workDir);
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /* ═══════════════════════════════════════════════════
       SERVE DOWNLOAD
    ═══════════════════════════════════════════════════ */

    public function serveDownload(string $token)
    {
        $data = Cache::get("pb_dl_{$token}");

        if (!$data || !file_exists($data['path'])) {
            abort(404, 'Link download sudah expired atau tidak valid.');
        }

        $path     = $data['path'];
        $filename = $data['filename'];
        $workDir  = $data['workDir'];

        $ext  = pathinfo($filename, PATHINFO_EXTENSION);
        $mime = match ($ext) {
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            default => 'application/octet-stream',
        };

        app()->terminating(function () use ($workDir, $token) {
            Cache::forget("pb_dl_{$token}");
            @array_map('unlink', glob($workDir . '/*'));
            @rmdir($workDir);
        });

        return response()->download($path, $filename, ['Content-Type' => $mime]);
    }

    /* ═══════════════════════════════════════════════════
       CLEANUP
    ═══════════════════════════════════════════════════ */

    public function cleanup(Request $request): JsonResponse
    {
        $base    = storage_path('app/proposal_builder');
        $maxAge  = 3600;
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

    /* ═══════════════════════════════════════════════════
       LIBREOFFICE CONVERSION ENGINE
    ═══════════════════════════════════════════════════ */

    private function convertWithLibreOffice(string $inputPath, string $outputDir, string $targetFmt): array
    {
        $lo  = $this->loBin();
        $tmp = sys_get_temp_dir() . '/lo_pb_' . Str::random(8);
        @mkdir($tmp, 0775, true);

        // LibreOffice format strings
        $loFmt = match ($targetFmt) {
            'pdf'  => 'pdf',
            'docx' => 'docx:"MS Word 2007 XML"',
            default => $targetFmt,
        };

        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = escapeshellarg($lo)
                . ' --headless --norestore --nofirststartwizard'
                . ' --convert-to ' . $loFmt
                . ' --outdir ' . escapeshellarg($outputDir)
                . ' ' . escapeshellarg($inputPath);
        } else {
            $envPrefix = "HOME={$tmp} ";
            $cmd = $envPrefix
                . escapeshellarg($lo)
                . ' --headless --norestore --nofirststartwizard'
                . ' --convert-to ' . $loFmt
                . ' --outdir ' . escapeshellarg($outputDir)
                . ' ' . escapeshellarg($inputPath);
        }

        $old = (int) ini_get('max_execution_time');
        set_time_limit(max($old ?: 0, 180));

        exec($cmd . ' 2>&1', $output, $exitCode);

        @array_map('unlink', glob($tmp . '/*'));
        @rmdir($tmp);

        // LibreOffice names output as: {stem}.{ext}
        $stem = pathinfo($inputPath, PATHINFO_FILENAME);
        $ext  = ($targetFmt === 'docx') ? 'docx' : 'pdf';
        $outPath = $outputDir . '/' . $stem . '.' . $ext;

        if (file_exists($outPath) && filesize($outPath) > 0) {
            return ['success' => true, 'output' => $outPath, 'engine' => 'libreoffice'];
        }

        $errorMsg = implode("\n", $output) ?: 'LibreOffice tidak menghasilkan output.';
        return ['success' => false, 'error' => $this->friendlyError($errorMsg)];
    }

    private function loBin(): string
    {
        if ($env = env('LO_BINARY')) return $env;
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
            if (@shell_exec("which {$b} 2>/dev/null")) return $b;
        }
        return 'soffice';
    }

    private function friendlyError(string $raw): string
    {
        if (str_contains($raw, 'LibreOffice') || str_contains($raw, 'soffice'))
            return 'LibreOffice tidak ditemukan di server. Hubungi administrator.';
        if (str_contains($raw, 'timeout') || str_contains($raw, 'Timeout'))
            return 'Konversi timeout — coba dengan file yang lebih kecil.';
        if (str_contains($raw, 'Permission') || str_contains($raw, 'denied'))
            return 'Permission error pada folder temporary server.';
        return 'Konversi gagal: ' . Str::limit($raw, 200);
    }

    private function storeToken(string $path, string $workDir, string $filename): string
    {
        $token = Str::random(48);
        Cache::put("pb_dl_{$token}", [
            'path'     => $path,
            'workDir'  => $workDir,
            'filename' => $filename,
        ], now()->addHours(2));
        return $token;
    }

    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        array_map('unlink', glob($dir . '/*') ?: []);
        @rmdir($dir);
    }

    /* ═══════════════════════════════════════════════════
       HTML DOCUMENT WRAPPER — Professional Indonesian Standard
       A4 format, Times New Roman, standard university margins
    ═══════════════════════════════════════════════════ */

    private function wrapInDocument(
        string $bodyContent,
        string $templateName,
        array  $data,
        ?string $logoBase64,
        string $template
    ): string {
        $title     = $this->getDocTitle($data, $templateName);
        $coverHtml = $this->buildCover($template, $data, $logoBase64, $templateName);

        /* ── Inline CSS for LibreOffice-compatible print styling ── */
        $styles = '
            /* ── Page Setup: Indonesian standard A4 ── */
            @page {
                size: A4;
                margin: 3cm 2.5cm 3cm 4cm;
            }
            @page cover-page {
                margin: 3cm 2.5cm 2cm 3cm;
            }

            /* ── Base Typography ── */
            * { box-sizing: border-box; }
            body {
                font-family: "Times New Roman", Times, serif;
                font-size: 12pt;
                line-height: 2;
                color: #000000;
                margin: 0;
                padding: 0;
                background: #ffffff;
            }

            /* ── Headings ── */
            h1 {
                font-family: "Times New Roman", Times, serif;
                font-size: 14pt;
                font-weight: bold;
                text-align: center;
                text-transform: uppercase;
                letter-spacing: 1pt;
                margin: 0 0 18pt 0;
                line-height: 1.5;
                page-break-after: avoid;
                break-after: avoid;
            }
            h2 {
                font-family: "Times New Roman", Times, serif;
                font-size: 12pt;
                font-weight: bold;
                text-align: left;
                margin: 18pt 0 10pt 0;
                page-break-after: avoid;
                break-after: avoid;
            }
            h3 {
                font-family: "Times New Roman", Times, serif;
                font-size: 12pt;
                font-weight: bold;
                margin: 14pt 0 8pt 0;
                page-break-after: avoid;
                break-after: avoid;
            }

            /* ── Paragraphs ── */
            p {
                margin: 0 0 12pt 0;
                text-align: justify;
                text-indent: 1.25cm;
                orphans: 3;
                widows: 3;
            }
            p.no-indent {
                text-indent: 0;
            }
            p.center {
                text-align: center;
                text-indent: 0;
            }

            /* ── Lists ── */
            ul, ol {
                margin: 6pt 0 12pt 0;
                padding-left: 2cm;
            }
            li {
                margin: 5pt 0;
                text-align: justify;
                line-height: 1.8;
            }

            /* ── Tables ── */
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 12pt 0 18pt 0;
                font-size: 11pt;
                page-break-inside: auto;
            }
            th {
                padding: 7pt 10pt;
                text-align: center;
                font-weight: bold;
                border: 1pt solid #333333;
                background-color: #2e5496;
                color: #ffffff;
                font-family: "Times New Roman", Times, serif;
                font-size: 11pt;
            }
            th.left { text-align: left; }
            td {
                padding: 6pt 10pt;
                border: 1pt solid #999999;
                vertical-align: top;
                font-family: "Times New Roman", Times, serif;
                font-size: 11pt;
                line-height: 1.5;
            }
            tr:nth-child(even) td { background-color: #f0f4fb; }
            .tbl-header-alt { background-color: #1e3a6e; }
            .tbl-total td {
                background-color: #dce6f1;
                font-weight: bold;
                border-top: 2pt solid #2e5496;
            }

            /* ── Misc ── */
            strong { font-weight: bold; }
            em { font-style: italic; }
            mark { background: transparent; font-weight: bold; }

            /* ── Page Breaks ── */
            .page-break {
                page-break-before: always;
                break-before: page;
                margin: 0;
                padding: 0;
                height: 0;
                display: block;
            }

            /* ═══════════════════════════════════
               COVER PAGE
            ═══════════════════════════════════ */
            .cover-page {
                page: cover-page;
                page-break-after: always;
                break-after: page;
                text-align: center;
                min-height: 260mm;
                padding: 0;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            /* Logo: centered, fixed size so it fits the page */
            .cover-logo-wrap {
                margin: 0 auto 16pt;
                text-align: center;
            }
            .cover-logo {
                display: block;
                margin: 0 auto;
                width: 100px;
                height: 100px;
                object-fit: contain;
                object-position: center;
            }

            /* Cover titles */
            .cover-label {
                font-size: 13pt;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 2pt;
                margin: 8pt 0;
            }
            .cover-title {
                font-size: 14pt;
                font-weight: bold;
                text-transform: uppercase;
                line-height: 1.5;
                margin: 12pt 0 8pt;
                max-width: 80%;
                margin-left: auto;
                margin-right: auto;
            }
            .cover-subtitle {
                font-size: 12pt;
                font-weight: normal;
                margin: 6pt 0;
                font-style: italic;
                max-width: 85%;
                margin-left: auto;
                margin-right: auto;
            }
            .cover-purpose {
                font-size: 12pt;
                margin: 8pt auto;
                max-width: 80%;
                text-align: center;
                text-indent: 0;
                line-height: 1.6;
            }
            .cover-divider {
                display: block;
                width: 70%;
                height: 2pt;
                background: #000000;
                margin: 14pt auto;
                border: none;
            }
            .cover-divider-thin {
                display: block;
                width: 70%;
                height: 1pt;
                background: #000000;
                margin: 10pt auto;
                border: none;
            }
            .cover-identity {
                font-size: 12pt;
                margin: 4pt 0;
                text-align: center;
                text-indent: 0;
            }
            .cover-institution {
                font-size: 13pt;
                font-weight: bold;
                text-transform: uppercase;
                margin: 4pt 0;
                text-align: center;
                letter-spacing: 0.5pt;
            }
            .cover-year {
                font-size: 12pt;
                margin: 6pt 0;
                text-align: center;
            }
            .cover-spacer {
                flex: 1;
                min-height: 10pt;
            }

            /* ── Tanda tangan / signature area ── */
            .sign-right {
                text-align: right;
                text-indent: 0;
            }
            .sign-block {
                display: inline-block;
                text-align: center;
                min-width: 140pt;
            }
            .sign-space {
                height: 48pt;
                display: block;
            }
        ';

        return '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . htmlspecialchars($title) . '</title>
<style>' . $styles . '</style>
</head>
<body>
' . $coverHtml . '
' . $bodyContent . '
</body>
</html>';
    }

    /* ═══════════════════════════════════════════════════
       COVER PAGE BUILDERS — Indonesian Professional Standard
    ═══════════════════════════════════════════════════ */

    private function buildCover(string $template, array $data, ?string $logo, string $templateName): string
    {
        /* ── Logo HTML: always resize to 100×100px to fit page ── */
        if ($logo) {
            // Detect image type from base64 data URI or assume PNG
            $type = 'png';
            if (preg_match('/^data:image\/(\w+);base64,/', $logo, $m)) {
                $type = $m[1];
            }
            $logoHtml = '<div class="cover-logo-wrap">'
                . '<img src="' . $logo . '" class="cover-logo" '
                . 'width="100" height="100" '
                . 'alt="Logo Institusi">'
                . '</div>';
        } else {
            $logoHtml = '';
        }

        return match ($template) {
            'mahasiswa'  => $this->coverMahasiswa($data, $logoHtml),
            'freelancer' => $this->coverFreelancer($data, $logoHtml),
            'bisnis'     => $this->coverBisnis($data, $logoHtml),
            default      => $this->coverEvent($data, $logoHtml),
        };
    }

    /* ── Mahasiswa / Tugas Akhir Cover ── */
    private function coverMahasiswa(array $d, string $logo): string
    {
        $jenjang   = strtoupper($this->e($this->v($d, 'jenjang',          'S1')));
        $judul     = $this->e($this->v($d, 'judul_proposal', 'Judul Proposal Penelitian'));
        $prodi     = $this->e($this->v($d, 'program_studi',  '—'));
        $nama      = $this->e($this->v($d, 'nama_mahasiswa', '—'));
        $nim       = $this->e($this->v($d, 'nim',            '—'));
        $dosen     = $this->e($this->v($d, 'nama_dosen',     '—'));
        $nipDosen  = $this->e($this->v($d, 'nip_dosen',      ''));
        $fakultas  = $this->e($this->v($d, 'fakultas',       ''));
        $kampus    = $this->e($this->v($d, 'nama_kampus',    '—'));
        $kota      = $this->e($this->v($d, 'kota',           '—'));
        $tahun     = $this->e($this->v($d, 'tahun',          date('Y')));

        $nipLine   = $nipDosen ? '<p class="cover-identity">NIP/NIDN: ' . $nipDosen . '</p>' : '';
        $fakultasLine = $fakultas ? '<p class="cover-institution">' . $fakultas . '</p>' : '';

        return '<div class="cover-page">'
            . $logo
            . '<div>'
            . '<p class="cover-label">PROPOSAL ' . $jenjang . '</p>'
            . '</div>'
            . '<hr class="cover-divider">'
            . '<p class="cover-title">' . $judul . '</p>'
            . '<hr class="cover-divider">'
            . '<p class="cover-purpose">Diajukan untuk Memenuhi Salah Satu Syarat Menyelesaikan Studi<br>'
            . 'pada Program Studi ' . $prodi . '</p>'
            . '<div class="cover-spacer"></div>'
            . '<div>'
            . '<p class="cover-identity">Disusun oleh:</p>'
            . '<p class="cover-identity"><strong>' . $nama . '</strong></p>'
            . '<p class="cover-identity">NIM: ' . $nim . '</p>'
            . '<br>'
            . '<p class="cover-identity">Dosen Pembimbing:</p>'
            . '<p class="cover-identity"><strong>' . $dosen . '</strong></p>'
            . $nipLine
            . '</div>'
            . '<hr class="cover-divider">'
            . '<div>'
            . $fakultasLine
            . '<p class="cover-institution">' . $kampus . '</p>'
            . '<p class="cover-year">' . $kota . '</p>'
            . '<p class="cover-year">' . $tahun . '</p>'
            . '</div>'
            . '</div>';
    }

    /* ── Freelancer Cover ── */
    private function coverFreelancer(array $d, string $logo): string
    {
        $judul   = $this->e($this->v($d, 'judul_proyek',       'Nama Proyek'));
        $nama    = $this->e($this->v($d, 'nama_freelancer',    '—'));
        $klien   = $this->e($this->v($d, 'nama_klien',         '—'));
        $tgl     = $this->e($this->v($d, 'tanggal_proposal',   date('d/m/Y')));
        $durasi  = $this->e($this->v($d, 'durasi_proyek',      '—'));
        $budget  = $this->e($this->v($d, 'total_anggaran',     '—'));
        $kontak  = $this->e($this->v($d, 'kontak_freelancer',  '—'));

        return '<div class="cover-page">'
            . $logo
            . '<div>'
            . '<p class="cover-label">PROPOSAL PENAWARAN JASA PROFESIONAL</p>'
            . '</div>'
            . '<hr class="cover-divider">'
            . '<p class="cover-title">' . $judul . '</p>'
            . '<hr class="cover-divider">'
            . '<div class="cover-spacer"></div>'
            . '<div>'
            . '<p class="cover-identity">Dipersiapkan oleh:</p>'
            . '<p class="cover-identity" style="font-size:14pt;"><strong>' . $nama . '</strong></p>'
            . '<br>'
            . '<p class="cover-identity">Dipersiapkan untuk:</p>'
            . '<p class="cover-identity" style="font-size:14pt;"><strong>' . $klien . '</strong></p>'
            . '</div>'
            . '<hr class="cover-divider-thin">'
            . '<div>'
            . '<p class="cover-identity">Tanggal Proposal: ' . $tgl . '</p>'
            . '<p class="cover-identity">Estimasi Durasi: ' . $durasi . '</p>'
            . '<p class="cover-identity">Total Anggaran: ' . $budget . '</p>'
            . '<br>'
            . '<p class="cover-identity" style="font-size:10pt;color:#555;">Kontak: ' . $kontak . '</p>'
            . '</div>'
            . '</div>';
    }

    /* ── Bisnis Cover ── */
    private function coverBisnis(array $d, string $logo): string
    {
        $nama    = $this->e($this->v($d, 'nama_bisnis',   'Nama Bisnis'));
        $bidang  = $this->e($this->v($d, 'bidang_usaha',  '—'));
        $visi    = $this->e($this->v($d, 'visi',          ''));
        $lokasi  = $this->e($this->v($d, 'lokasi_bisnis', '—'));
        $tahun   = $this->e($this->v($d, 'tahun_berdiri', date('Y')));
        $invest  = $this->e($this->v($d, 'total_investasi','—'));

        $visiLine = $visi
            ? '<p class="cover-subtitle">"' . $visi . '"</p>'
            : '';

        return '<div class="cover-page">'
            . $logo
            . '<div>'
            . '<p class="cover-label">PROPOSAL BISNIS</p>'
            . '</div>'
            . '<hr class="cover-divider">'
            . '<p class="cover-title">' . $nama . '</p>'
            . '<p class="cover-identity" style="font-size:13pt;font-weight:bold;">' . $bidang . '</p>'
            . $visiLine
            . '<hr class="cover-divider">'
            . '<div class="cover-spacer"></div>'
            . '<div>'
            . '<p class="cover-identity">Disusun oleh Tim Pendiri</p>'
            . '<p class="cover-identity">Berdiri sejak: ' . $tahun . '</p>'
            . '<p class="cover-identity">Domisili: ' . $lokasi . '</p>'
            . '<p class="cover-identity">Total Investasi Dibutuhkan: <strong>' . $invest . '</strong></p>'
            . '</div>'
            . '</div>';
    }

    /* ── Event Cover ── */
    private function coverEvent(array $d, string $logo): string
    {
        $namaAcara    = $this->e($this->v($d, 'nama_acara',        'Nama Acara'));
        $tema         = $this->e($this->v($d, 'tema_acara',        '—'));
        $penyelengg   = $this->e($this->v($d, 'penyelenggara',     '—'));
        $tgl          = $this->e($this->v($d, 'tanggal_acara',     '—'));
        $lokasi       = $this->e($this->v($d, 'lokasi_acara',      '—'));
        $peserta      = $this->e($this->v($d, 'target_peserta',    '—'));
        $narahubung   = $this->e($this->v($d, 'narahubung',        '—'));

        return '<div class="cover-page">'
            . $logo
            . '<div>'
            . '<p class="cover-label">PROPOSAL PENYELENGGARAAN KEGIATAN</p>'
            . '</div>'
            . '<hr class="cover-divider">'
            . '<p class="cover-title">' . $namaAcara . '</p>'
            . '<p class="cover-subtitle">"' . $tema . '"</p>'
            . '<hr class="cover-divider">'
            . '<div class="cover-spacer"></div>'
            . '<div>'
            . '<p class="cover-identity" style="font-size:13pt;font-weight:bold;">' . $penyelengg . '</p>'
            . '<br>'
            . '<p class="cover-identity">Tanggal Pelaksanaan: ' . $tgl . '</p>'
            . '<p class="cover-identity">Lokasi: ' . $lokasi . '</p>'
            . '<p class="cover-identity">Target Peserta: ' . $peserta . ' orang</p>'
            . '<br>'
            . '<p class="cover-identity" style="font-size:10.5pt;">Narahubung: ' . $narahubung . '</p>'
            . '</div>'
            . '</div>';
    }

    /* ═══════════════════════════════════════════════════
       CONTENT BUILDERS
    ═══════════════════════════════════════════════════ */

    private function buildContent(string $template, array $d): string
    {
        return match ($template) {
            'mahasiswa'  => $this->buildMahasiswaContent($d),
            'freelancer' => $this->buildFreelancerContent($d),
            'bisnis'     => $this->buildBisnisContent($d),
            'event'      => $this->buildEventContent($d),
            default      => '<p>Template tidak dikenal.</p>',
        };
    }

    /* ─────────────────────────────────────
       HELPERS — Table header / divider
    ───────────────────────────────────── */

    /** Standard blue table header row */
    private function thRow(array $cols, bool $center = false): string
    {
        $align = $center ? ' style="text-align:center;"' : '';
        $cells = '';
        foreach ($cols as $col) {
            if (is_array($col)) {
                $width = isset($col[1]) ? ' style="width:' . $col[1] . ';text-align:' . ($center ? 'center' : 'left') . ';"' : $align;
                $cells .= '<th' . $width . '>' . $col[0] . '</th>';
            } else {
                $cells .= '<th' . $align . '>' . $col . '</th>';
            }
        }
        return '<thead><tr>' . $cells . '</tr></thead>';
    }

    /** Table wrapper */
    private function tableWrap(string $thead, string $tbody): string
    {
        return '<table style="width:100%;border-collapse:collapse;margin:10pt 0 18pt;">'
            . $thead
            . '<tbody>' . $tbody . '</tbody>'
            . '</table>';
    }

    /** Standard data row */
    private function tdRow(array $cells, bool $isTotal = false): string
    {
        $cls = $isTotal ? ' class="tbl-total"' : '';
        $out = '<tr' . $cls . '>';
        foreach ($cells as $cell) {
            if (is_array($cell)) {
                $style = isset($cell[1]) ? ' style="' . $cell[1] . '"' : '';
                $colspan = isset($cell[2]) ? ' colspan="' . $cell[2] . '"' : '';
                $out .= '<td' . $style . $colspan . '>' . $cell[0] . '</td>';
            } else {
                $out .= '<td>' . $cell . '</td>';
            }
        }
        $out .= '</tr>';
        return $out;
    }

    /* ─────────────────────────────────────
       MAHASISWA / TUGAS AKHIR
    ───────────────────────────────────── */

    private function buildMahasiswaContent(array $d): string
    {
        $judul   = $this->e($this->v($d, 'judul_proposal',   'Judul Penelitian'));
        $nama    = $this->e($this->v($d, 'nama_mahasiswa',   'Nama Mahasiswa'));
        $nim     = $this->e($this->v($d, 'nim',              '—'));
        $prodi   = $this->e($this->v($d, 'program_studi',    '—'));
        $kampus  = $this->e($this->v($d, 'nama_kampus',      '—'));
        $kota    = $this->e($this->v($d, 'kota',             '—'));
        $tahun   = $this->e($this->v($d, 'tahun',            date('Y')));
        $dosen   = $this->e($this->v($d, 'nama_dosen',       '—'));

        $latarBelakang   = $this->v($d, 'latar_belakang',           '');
        $identifikasi    = $this->v($d, 'identifikasi_masalah',     '');
        $batasan         = $this->v($d, 'batasan_masalah',          '');
        $rumusan         = $this->v($d, 'rumusan_masalah',          '');
        $tujuan          = $this->v($d, 'tujuan_penelitian',        '');
        $manfaatTeoritis = $this->v($d, 'manfaat_teoritis',         '');
        $manfaatPraktis  = $this->v($d, 'manfaat_praktis',          '');
        $jenisPenelitian = $this->e($this->v($d, 'jenis_penelitian', 'Kuantitatif'));
        $metodePengump   = $this->v($d, 'metode_pengumpulan_data',  '');
        $teknikAnalisis  = $this->v($d, 'teknik_analisis',          '');
        $jadwal          = $this->v($d, 'jadwal_penelitian',        '');
        $pustaka         = $this->v($d, 'daftar_pustaka',           '');

        $bab1 = (bool)($d['bab1'] ?? true);
        $bab2 = (bool)($d['bab2'] ?? true);
        $bab3 = (bool)($d['bab3'] ?? true);
        $bab4 = (bool)($d['bab4'] ?? false);
        $bab5 = (bool)($d['bab5'] ?? true);

        /* ── Daftar Isi ── */
        $tocItems = ['Kata Pengantar' => 'i', 'Daftar Isi' => 'ii'];
        if ($bab1) {
            $tocItems['BAB I  – Pendahuluan']                      = '1';
            $tocItems['&nbsp;&nbsp;&nbsp;A. Latar Belakang']       = '1';
            $tocItems['&nbsp;&nbsp;&nbsp;B. Identifikasi Masalah'] = '2';
            $tocItems['&nbsp;&nbsp;&nbsp;C. Batasan Masalah']      = '2';
            $tocItems['&nbsp;&nbsp;&nbsp;D. Rumusan Masalah']      = '3';
            $tocItems['&nbsp;&nbsp;&nbsp;E. Tujuan Penelitian']    = '3';
            $tocItems['&nbsp;&nbsp;&nbsp;F. Manfaat Penelitian']   = '4';
        }
        if ($bab2) {
            $tocItems['BAB II – Landasan Teori']                   = '5';
            $tocItems['&nbsp;&nbsp;&nbsp;A. Kajian Teori']         = '5';
            $tocItems['&nbsp;&nbsp;&nbsp;B. Penelitian Relevan']   = '7';
            $tocItems['&nbsp;&nbsp;&nbsp;C. Kerangka Berpikir']    = '8';
        }
        if ($bab3) {
            $tocItems['BAB III – Metodologi Penelitian']                   = '9';
            $tocItems['&nbsp;&nbsp;&nbsp;A. Jenis Penelitian']             = '9';
            $tocItems['&nbsp;&nbsp;&nbsp;B. Subjek dan Objek Penelitian']  = '9';
            $tocItems['&nbsp;&nbsp;&nbsp;C. Teknik Pengumpulan Data']      = '10';
            $tocItems['&nbsp;&nbsp;&nbsp;D. Instrumen Penelitian']         = '10';
            $tocItems['&nbsp;&nbsp;&nbsp;E. Teknik Analisis Data']         = '11';
            $tocItems['&nbsp;&nbsp;&nbsp;F. Jadwal Penelitian']            = '11';
        }
        if ($bab4) {
            $tocItems['BAB IV – Hasil dan Pembahasan']         = '12';
            $tocItems['&nbsp;&nbsp;&nbsp;A. Hasil Penelitian'] = '12';
            $tocItems['&nbsp;&nbsp;&nbsp;B. Pembahasan']       = '13';
        }
        if ($bab5) {
            $tocItems['BAB V  – Penutup']               = '15';
            $tocItems['&nbsp;&nbsp;&nbsp;A. Kesimpulan'] = '15';
            $tocItems['&nbsp;&nbsp;&nbsp;B. Saran']      = '15';
        }
        $tocItems['Daftar Pustaka'] = '16';

        $tocTbody = '';
        foreach ($tocItems as $label => $page) {
            $tocTbody .= '<tr>'
                . '<td style="border:1pt solid #cccccc;padding:4pt 10pt;">' . $label . '</td>'
                . '<td style="border:1pt solid #cccccc;padding:4pt 10pt;text-align:right;width:50pt;">' . $page . '</td>'
                . '</tr>';
        }

        $out = '';

        /* ── KATA PENGANTAR ── */
        $out .= '<h1>KATA PENGANTAR</h1>';
        $out .= '<p>Segala puji dan syukur penulis panjatkan ke hadirat Tuhan Yang Maha Esa atas segala rahmat dan karunia-Nya sehingga penulis dapat menyelesaikan penyusunan proposal penelitian ini dengan baik dan tepat waktu.</p>';
        $out .= '<p>Proposal penelitian yang berjudul <strong>"' . $judul . '"</strong> ini disusun sebagai salah satu syarat untuk menyelesaikan studi pada Program Studi ' . $prodi . ', ' . $kampus . '.</p>';
        $out .= '<p>Dalam proses penyusunan proposal ini, penulis mendapatkan banyak bantuan, bimbingan, dan dukungan dari berbagai pihak. Oleh karena itu, penulis menyampaikan ucapan terima kasih yang sebesar-besarnya kepada:</p>';
        $out .= '<ol>'
            . '<li>Bapak/Ibu <strong>' . $dosen . '</strong> selaku Dosen Pembimbing yang telah memberikan arahan, bimbingan, serta motivasi kepada penulis.</li>'
            . '<li>Seluruh dosen dan staf pengajar Program Studi ' . $prodi . ' yang telah memberikan ilmu dan pengetahuan selama masa perkuliahan.</li>'
            . '<li>Keluarga tercinta yang senantiasa memberikan doa, dukungan, dan semangat yang tak ternilai kepada penulis.</li>'
            . '<li>Rekan-rekan mahasiswa yang telah memberikan bantuan, masukan, dan motivasi selama proses penyusunan proposal ini.</li>'
            . '</ol>';
        $out .= '<p>Penulis menyadari bahwa proposal penelitian ini masih jauh dari sempurna. Oleh karena itu, penulis sangat mengharapkan kritik dan saran yang membangun dari semua pihak demi penyempurnaan proposal ini.</p>';
        $out .= '<p class="sign-right">' . $kota . ', ' . $tahun . '</p>';
        $out .= '<p class="sign-right">Penulis,</p>';
        $out .= '<p class="sign-right"><span class="sign-space">&nbsp;</span></p>';
        $out .= '<p class="sign-right"><strong>' . $nama . '</strong><br>NIM: ' . $nim . '</p>';

        /* ── DAFTAR ISI ── */
        $out .= '<div class="page-break"></div>';
        $out .= '<h1>DAFTAR ISI</h1>';
        $out .= '<table style="width:100%;border-collapse:collapse;margin:10pt 0;">'
            . '<tbody>' . $tocTbody . '</tbody>'
            . '</table>';

        /* ── BAB I ── */
        if ($bab1) {
            $out .= '<div class="page-break"></div>';
            $out .= '<h1>BAB I<br>PENDAHULUAN</h1>';

            $out .= '<h2>A. Latar Belakang</h2>';
            $out .= $latarBelakang ? $this->paragraphs($latarBelakang)
                : '<p>Perkembangan ilmu pengetahuan dan teknologi yang semakin pesat memberikan dampak yang signifikan terhadap berbagai aspek kehidupan manusia. Hal ini mendorong para peneliti untuk terus berinovasi dan menghasilkan temuan-temuan baru yang dapat memberikan kontribusi nyata bagi masyarakat.</p>'
                . '<p>Penelitian mengenai <strong>"' . $judul . '"</strong> ini dilatarbelakangi oleh adanya permasalahan yang memerlukan kajian mendalam dan solusi yang komprehensif. Berdasarkan observasi awal dan studi literatur yang telah dilakukan, ditemukan berbagai fenomena yang menarik untuk dikaji lebih lanjut.</p>'
                . '<p>Oleh karena itu, peneliti merasa perlu untuk melakukan penelitian ini sebagai upaya memahami, menganalisis, dan menemukan solusi atas permasalahan yang ada, serta memberikan kontribusi terhadap pengembangan ilmu pengetahuan di bidang terkait.</p>';

            $out .= '<h2>B. Identifikasi Masalah</h2>';
            $out .= '<p>Berdasarkan latar belakang yang telah diuraikan di atas, maka dapat diidentifikasikan beberapa permasalahan sebagai berikut:</p>';
            $out .= $identifikasi ? $this->bulletList($identifikasi)
                : '<ol><li>Terdapat kesenjangan antara kondisi ideal dan kondisi nyata di lapangan.</li><li>Belum tersedianya kajian yang komprehensif mengenai topik yang diteliti.</li><li>Diperlukan pendekatan baru untuk mengatasi permasalahan yang ada.</li></ol>';

            $out .= '<h2>C. Batasan Masalah</h2>';
            $out .= '<p>Mengingat luasnya permasalahan yang ada serta keterbatasan waktu, tenaga, dan sumber daya, maka penelitian ini dibatasi pada hal-hal sebagai berikut:</p>';
            $out .= $batasan ? $this->bulletList($batasan)
                : '<ol><li>Penelitian ini difokuskan pada aspek-aspek yang berkaitan langsung dengan judul penelitian.</li><li>Subjek penelitian dibatasi sesuai dengan ruang lingkup yang telah ditentukan.</li><li>Periode penelitian dibatasi sesuai dengan kebutuhan dan kemampuan peneliti.</li></ol>';

            $out .= '<h2>D. Rumusan Masalah</h2>';
            $out .= '<p>Berdasarkan batasan masalah yang telah ditetapkan, maka rumusan masalah dalam penelitian ini adalah:</p>';
            $out .= $rumusan ? $this->bulletList($rumusan)
                : '<ol><li>Bagaimana gambaran umum dari ' . $judul . '?</li><li>Faktor-faktor apa saja yang mempengaruhi ' . $judul . '?</li><li>Bagaimana solusi yang dapat ditawarkan untuk mengatasi permasalahan tersebut?</li></ol>';

            $out .= '<h2>E. Tujuan Penelitian</h2>';
            $out .= '<p>Adapun tujuan yang ingin dicapai dalam penelitian ini adalah:</p>';
            $out .= $tujuan ? $this->bulletList($tujuan)
                : '<ol><li>Untuk mengetahui dan mendeskripsikan gambaran umum dari ' . $judul . '.</li><li>Untuk mengidentifikasi faktor-faktor yang mempengaruhi ' . $judul . '.</li><li>Untuk menemukan solusi yang tepat dan efektif terhadap permasalahan yang diteliti.</li></ol>';

            $out .= '<h2>F. Manfaat Penelitian</h2>';
            $out .= '<p><strong>1. Manfaat Teoritis</strong></p>';
            $out .= $manfaatTeoritis ? $this->paragraphs($manfaatTeoritis)
                : '<p>Secara teoritis, penelitian ini diharapkan dapat memberikan sumbangan pemikiran dan memperkaya khasanah ilmu pengetahuan, khususnya yang berkaitan dengan topik penelitian ini. Hasil penelitian ini juga diharapkan dapat menjadi referensi bagi penelitian-penelitian selanjutnya.</p>';
            $out .= '<p><strong>2. Manfaat Praktis</strong></p>';
            $out .= $manfaatPraktis ? $this->paragraphs($manfaatPraktis)
                : '<p>Secara praktis, penelitian ini diharapkan dapat memberikan manfaat bagi berbagai pihak yang terkait, termasuk para praktisi, pengambil kebijakan, dan masyarakat pada umumnya dalam memahami dan mengatasi permasalahan yang diteliti.</p>';
        }

        /* ── BAB II ── */
        if ($bab2) {
            $out .= '<div class="page-break"></div>';
            $out .= '<h1>BAB II<br>LANDASAN TEORI</h1>';
            $out .= '<h2>A. Kajian Teori</h2>';
            $out .= '<p>Pada bagian ini akan diuraikan berbagai teori dan konsep yang relevan dengan penelitian yang berjudul <strong>"' . $judul . '"</strong>. Kajian teori ini merupakan landasan ilmiah yang mendasari pelaksanaan penelitian.</p>';
            $out .= '<p>Teori-teori yang dikaji meliputi berbagai perspektif dari para ahli yang telah melakukan penelitian di bidang yang sama atau berkaitan. Dengan memahami teori-teori ini, penelitian dapat dilaksanakan secara sistematis dan ilmiah.</p>';
            $out .= '<h3>1. Definisi dan Konsep Dasar</h3>';
            $out .= '<p>Pemahaman yang mendalam mengenai definisi dan konsep dasar merupakan fondasi penting dalam pelaksanaan sebuah penelitian. Para ahli telah banyak mengemukakan definisi yang beragam namun saling melengkapi satu sama lain.</p>';
            $out .= '<h3>2. Teori Pendukung</h3>';
            $out .= '<p>Berbagai teori pendukung digunakan sebagai landasan dalam menganalisis dan menginterpretasikan data yang diperoleh selama penelitian berlangsung.</p>';
            $out .= '<h2>B. Penelitian yang Relevan</h2>';
            $out .= '<p>Terdapat beberapa penelitian terdahulu yang relevan dan dapat dijadikan sebagai referensi dalam penelitian ini. Penelitian-penelitian tersebut memberikan gambaran tentang perkembangan kajian di bidang yang sama serta menjadi landasan bagi peneliti untuk melakukan penelitian yang lebih komprehensif.</p>';
            $out .= '<h2>C. Kerangka Berpikir</h2>';
            $out .= '<p>Kerangka berpikir dalam penelitian ini dibangun berdasarkan sintesis dari berbagai teori dan hasil penelitian terdahulu yang relevan. Penelitian tentang <strong>"' . $judul . '"</strong> ini berangkat dari identifikasi masalah yang ada di lapangan.</p>';
            $out .= '<p>Berdasarkan kerangka berpikir tersebut, penelitian ini menggunakan pendekatan yang sistematis dan terstruktur untuk menjawab pertanyaan-pertanyaan penelitian yang telah dirumuskan sebelumnya.</p>';
        }

        /* ── BAB III ── */
        if ($bab3) {
            $out .= '<div class="page-break"></div>';
            $out .= '<h1>BAB III<br>METODOLOGI PENELITIAN</h1>';
            $out .= '<h2>A. Jenis Penelitian</h2>';
            $out .= '<p>Penelitian ini menggunakan pendekatan <strong>' . $jenisPenelitian . '</strong>. Pendekatan ini dipilih karena dianggap paling sesuai dengan tujuan penelitian dan jenis data yang akan dikumpulkan.</p>';
            $out .= '<p>Dengan menggunakan jenis penelitian ini, diharapkan penelitian dapat menghasilkan temuan-temuan yang valid, reliabel, dan dapat dipertanggungjawabkan secara ilmiah.</p>';
            $out .= '<h2>B. Subjek dan Objek Penelitian</h2>';
            $out .= '<p>Subjek penelitian ini adalah seluruh pihak yang terlibat dan berkaitan langsung dengan topik penelitian. Pemilihan subjek penelitian dilakukan secara <em>purposive sampling</em> dengan mempertimbangkan kriteria-kriteria tertentu yang relevan dengan tujuan penelitian.</p>';
            $out .= '<p>Adapun objek penelitian ini adalah hal-hal yang menjadi fokus kajian dan analisis dalam penelitian ini, yang secara langsung berkaitan dengan rumusan masalah yang telah ditetapkan sebelumnya.</p>';
            $out .= '<h2>C. Teknik Pengumpulan Data</h2>';
            $out .= $metodePengump ? $this->paragraphs($metodePengump)
                : '<p>Teknik pengumpulan data yang digunakan dalam penelitian ini meliputi beberapa metode, antara lain:</p><ol><li><strong>Observasi</strong>, yaitu pengamatan langsung terhadap objek penelitian untuk memperoleh data yang akurat dan relevan.</li><li><strong>Wawancara</strong>, yaitu pengumpulan data melalui tanya jawab langsung dengan subjek penelitian.</li><li><strong>Angket/Kuesioner</strong>, yaitu pengumpulan data dengan menggunakan instrumen berupa daftar pertanyaan yang diberikan kepada responden.</li><li><strong>Studi Dokumentasi</strong>, yaitu pengumpulan data melalui dokumen-dokumen yang berkaitan dengan penelitian.</li></ol>';
            $out .= '<h2>D. Instrumen Penelitian</h2>';
            $out .= '<p>Instrumen penelitian yang digunakan dalam penelitian ini telah melalui proses validasi dan reliabilitas untuk memastikan kualitas dan keabsahan data yang dikumpulkan. Instrumen-instrumen tersebut dirancang sesuai dengan kebutuhan penelitian dan mampu mengukur variabel-variabel yang telah ditetapkan.</p>';
            $out .= '<h2>E. Teknik Analisis Data</h2>';
            $out .= $teknikAnalisis ? $this->paragraphs($teknikAnalisis)
                : '<p>Data yang telah dikumpulkan selanjutnya dianalisis menggunakan teknik analisis yang sesuai dengan jenis penelitian dan tujuan yang ingin dicapai. Proses analisis data dilakukan secara sistematis dan mengikuti prosedur ilmiah yang telah ditetapkan.</p><p>Hasil analisis data kemudian diinterpretasikan untuk menjawab pertanyaan-pertanyaan penelitian dan mencapai tujuan penelitian yang telah dirumuskan.</p>';
            $out .= '<h2>F. Jadwal Penelitian</h2>';
            if ($jadwal) $out .= $this->paragraphs($jadwal);
            $out .= '<p>Penelitian ini direncanakan akan dilaksanakan sesuai dengan jadwal kegiatan berikut:</p>';

            /* Jadwal table — professional */
            $thead = $this->thRow([
                ['No', '30pt'], ['Kegiatan', ''],
                ['Bln 1', '45pt'], ['Bln 2', '45pt'],
                ['Bln 3', '45pt'], ['Bln 4', '45pt'],
                ['Bln 5', '45pt'],
            ]);
            $center = 'text-align:center;';
            $tbody = $this->tdRow(['1', 'Pengajuan Proposal', ['✓', $center], ['', $center], ['', $center], ['', $center], ['', $center]])
                . $this->tdRow(['2', 'Studi Literatur',    ['✓', $center], ['✓', $center], ['', $center], ['', $center], ['', $center]])
                . $this->tdRow(['3', 'Pengumpulan Data',   ['', $center], ['✓', $center], ['✓', $center], ['', $center], ['', $center]])
                . $this->tdRow(['4', 'Analisis Data',      ['', $center], ['', $center], ['✓', $center], ['✓', $center], ['', $center]])
                . $this->tdRow(['5', 'Penulisan Laporan',  ['', $center], ['', $center], ['', $center], ['✓', $center], ['✓', $center]])
                . $this->tdRow(['6', 'Revisi &amp; Finalisasi', ['', $center], ['', $center], ['', $center], ['', $center], ['✓', $center]]);
            $out .= $this->tableWrap($thead, $tbody);
        }

        /* ── BAB IV ── */
        if ($bab4) {
            $out .= '<div class="page-break"></div>';
            $out .= '<h1>BAB IV<br>HASIL DAN PEMBAHASAN</h1>';
            $out .= '<h2>A. Hasil Penelitian</h2>';
            $out .= '<p><em>[Bagian ini akan diisi setelah penelitian dilaksanakan dan data berhasil dikumpulkan serta diolah.]</em></p>';
            $out .= '<p>Pada bagian ini akan disajikan hasil penelitian yang diperoleh dari proses pengumpulan dan analisis data secara sistematis sesuai dengan rumusan masalah yang telah ditetapkan.</p>';
            $out .= '<h2>B. Pembahasan</h2>';
            $out .= '<p><em>[Pembahasan hasil penelitian akan dilakukan setelah seluruh data terkumpul dan dianalisis.]</em></p>';
            $out .= '<p>Pembahasan dilakukan dengan mengaitkan temuan penelitian dengan teori-teori yang telah dikemukakan pada Bab II serta membandingkan dengan hasil penelitian terdahulu yang relevan.</p>';
        }

        /* ── BAB V ── */
        if ($bab5) {
            $out .= '<div class="page-break"></div>';
            $out .= '<h1>BAB V<br>PENUTUP</h1>';
            $out .= '<h2>A. Kesimpulan</h2>';
            $out .= '<p>Berdasarkan rumusan masalah, tujuan penelitian, dan hasil pembahasan yang telah diuraikan, maka dapat ditarik kesimpulan sementara bahwa penelitian mengenai <strong>"' . $judul . '"</strong> diharapkan dapat memberikan temuan-temuan yang signifikan dan bermanfaat.</p>';
            $out .= '<p>Kesimpulan akhir penelitian akan dirumuskan secara komprehensif setelah seluruh tahapan penelitian selesai dilaksanakan dan data telah dianalisis secara menyeluruh.</p>';
            $out .= '<h2>B. Saran</h2>';
            $out .= '<p>Berdasarkan penelitian yang dilakukan, terdapat beberapa saran yang dapat dikemukakan:</p>';
            $out .= '<ol>'
                . '<li>Bagi institusi terkait, diharapkan agar dapat memanfaatkan hasil penelitian ini sebagai bahan pertimbangan dalam pengambilan kebijakan.</li>'
                . '<li>Bagi peneliti selanjutnya, diharapkan dapat mengembangkan penelitian ini dengan ruang lingkup yang lebih luas dan metode yang lebih beragam.</li>'
                . '<li>Bagi masyarakat umum, diharapkan agar dapat menjadikan hasil penelitian ini sebagai referensi dalam kehidupan sehari-hari.</li>'
                . '</ol>';
        }

        /* ── DAFTAR PUSTAKA ── */
        $out .= '<div class="page-break"></div>';
        $out .= '<h1>DAFTAR PUSTAKA</h1>';
        if ($pustaka) {
            $lines = array_filter(array_map('trim', explode("\n", $pustaka)));
            $out .= '<ol>';
            foreach ($lines as $line) {
                $out .= '<li>' . $this->e($line) . '</li>';
            }
            $out .= '</ol>';
        } else {
            $out .= '<p><em>Daftar pustaka akan dilengkapi sesuai dengan referensi yang digunakan. Format penulisan mengacu pada standar APA (American Psychological Association) edisi terbaru.</em></p>';
            $out .= '<p class="no-indent">Sugiyono. (2019). <em>Metode Penelitian Kuantitatif, Kualitatif dan R&amp;D</em>. Bandung: Alfabeta.</p>';
            $out .= '<p class="no-indent">Arikunto, S. (2016). <em>Prosedur Penelitian: Suatu Pendekatan Praktik</em>. Jakarta: Rineka Cipta.</p>';
        }

        return $out;
    }

    /* ─────────────────────────────────────
       FREELANCER / PROJECT
    ───────────────────────────────────── */

    private function buildFreelancerContent(array $d): string
    {
        $judulProyek    = $this->e($this->v($d, 'judul_proyek',          'Nama Proyek'));
        $namaFreelancer = $this->e($this->v($d, 'nama_freelancer',       'Freelancer'));
        $namaKlien      = $this->e($this->v($d, 'nama_klien',            'Klien'));
        $tanggal        = $this->e($this->v($d, 'tanggal_proposal',      date('d/m/Y')));
        $durasi         = $this->e($this->v($d, 'durasi_proyek',         '—'));
        $deskripsi      = $this->v($d, 'deskripsi_proyek',       '');
        $latarKlien     = $this->v($d, 'latar_belakang_klien',   '');
        $deliverables   = $this->v($d, 'deliverables',           '');
        $metodologi     = $this->v($d, 'metodologi_kerja',        '');
        $teknologi      = $this->e($this->v($d, 'teknologi_stack',        ''));
        $milestone      = $this->v($d, 'milestone',              '');
        $rincianBiaya   = $this->v($d, 'rincian_biaya',          '');
        $totalAnggaran  = $this->e($this->v($d, 'total_anggaran',         '—'));
        $skemaBayar     = $this->v($d, 'skema_pembayaran',       '');
        $profilTim      = $this->v($d, 'profil_tim',             '');
        $pengalaman     = $this->v($d, 'pengalaman_relevan',     '');
        $syaratKetentuan= $this->v($d, 'syarat_ketentuan',       '');
        $kontak         = $this->e($this->v($d, 'kontak_freelancer',      '—'));

        $out = '';

        $out .= '<h1>1. EXECUTIVE SUMMARY</h1>';
        $out .= '<p>Proposal ini dipersiapkan oleh <strong>' . $namaFreelancer . '</strong> untuk menyampaikan penawaran jasa profesional kepada <strong>' . $namaKlien . '</strong> dalam rangka pengerjaan proyek <strong>"' . $judulProyek . '"</strong>.</p>';
        $out .= $deskripsi ? $this->paragraphs($deskripsi) : '<p>Proyek ini dirancang untuk memberikan solusi yang efektif, efisien, dan berkualitas tinggi sesuai dengan kebutuhan dan ekspektasi klien. Kami berkomitmen untuk menyelesaikan proyek ini sesuai dengan <em>timeline</em>, anggaran, dan standar kualitas yang telah disepakati bersama.</p>';
        $out .= '<p>Estimasi durasi pengerjaan adalah <strong>' . $durasi . '</strong> dengan anggaran total sebesar <strong>' . $totalAnggaran . '</strong>.</p>';

        $out .= '<div class="page-break"></div>';
        $out .= '<h1>2. TENTANG KAMI</h1>';
        $out .= $profilTim ? $this->paragraphs($profilTim) : '<p><strong>' . $namaFreelancer . '</strong> adalah profesional yang berpengalaman dengan rekam jejak yang terbukti dan dedikasi tinggi terhadap kualitas pekerjaan.</p>';
        if ($pengalaman) { $out .= '<h2>Pengalaman Relevan</h2>' . $this->paragraphs($pengalaman); }

        $out .= '<h1>3. PEMAHAMAN PROYEK</h1>';
        $out .= $latarKlien ? $this->paragraphs($latarKlien) : '<p>Kami telah melakukan kajian mendalam terhadap kebutuhan <strong>' . $namaKlien . '</strong> dan memahami bahwa proyek ini bertujuan untuk menghadirkan solusi yang meningkatkan efisiensi dan produktivitas operasional klien.</p>';

        $out .= '<h1>4. SCOPE OF WORK</h1>';
        $out .= '<h2>4.1 Deliverables</h2>';
        $out .= $deliverables ? $this->bulletList($deliverables)
            : '<ol><li>Perencanaan dan dokumentasi kebutuhan proyek</li><li>Pengerjaan dan implementasi sesuai spesifikasi yang disepakati</li><li>Testing dan <em>quality assurance</em></li><li>Serah terima hasil pekerjaan beserta dokumentasi lengkap</li><li>Masa garansi dan <em>support</em> pasca-<em>delivery</em></li></ol>';
        $out .= '<h2>4.2 Metodologi Kerja</h2>';
        $out .= $metodologi ? $this->paragraphs($metodologi) : '<p>Proyek ini akan dikerjakan menggunakan metodologi yang terstruktur dan transparan. Setiap tahapan pengerjaan akan dikomunikasikan secara berkala kepada klien melalui laporan <em>progress</em> dan pertemuan rutin.</p>';
        if ($teknologi) { $out .= '<h2>4.3 Teknologi dan Tools</h2><p>' . $teknologi . '</p>'; }

        $out .= '<div class="page-break"></div>';
        $out .= '<h1>5. TIMELINE DAN MILESTONE</h1>';
        if ($milestone) $out .= $this->paragraphs($milestone);

        $thead = $this->thRow(['No', 'Fase', 'Kegiatan', 'Durasi', 'Deliverable']);
        $tbody = $this->tdRow(['1', 'Perencanaan', 'Brief &amp; dokumentasi kebutuhan', 'Minggu 1', 'Project Brief'])
            . $this->tdRow(['2', 'Desain', 'Wireframe / Konsep / Mockup', 'Minggu 2', 'Desain Draft'])
            . $this->tdRow(['3', 'Pengerjaan', 'Implementasi &amp; pengembangan', 'Minggu 3–4', 'Draft Final'])
            . $this->tdRow(['4', 'Review', 'Revisi &amp; penyempurnaan', 'Minggu 5', 'Revisi Selesai'])
            . $this->tdRow(['5', 'Serah Terima', 'Delivery &amp; dokumentasi', 'Minggu 6', 'Final Delivery']);
        $out .= $this->tableWrap($thead, $tbody);

        $out .= '<h1>6. ANGGARAN DAN PEMBAYARAN</h1>';
        $out .= '<h2>6.1 Rincian Biaya</h2>';
        if ($rincianBiaya) $out .= $this->paragraphs($rincianBiaya);
        $thead = $this->thRow(['No', 'Item Pekerjaan', 'Qty', 'Satuan', 'Total']);
        $tbody = $this->tdRow(['1', 'Perencanaan &amp; Analisis', '1', 'Paket', '[Harga]'])
            . $this->tdRow(['2', 'Desain &amp; Konsep', '1', 'Paket', '[Harga]'])
            . $this->tdRow(['3', 'Pengerjaan &amp; Implementasi', '1', 'Paket', '[Harga]'])
            . $this->tdRow(['4', 'Revisi (hingga 3x)', '3', 'Sesi', 'Termasuk'])
            . $this->tdRow([['<strong>TOTAL</strong>', '', '4'], ['<strong>' . $totalAnggaran . '</strong>']], true);
        $out .= $this->tableWrap($thead, $tbody);
        if ($skemaBayar) { $out .= '<h2>6.2 Skema Pembayaran</h2>' . $this->paragraphs($skemaBayar); }

        $out .= '<div class="page-break"></div>';
        $out .= '<h1>7. SYARAT DAN KETENTUAN</h1>';
        $out .= $syaratKetentuan ? $this->bulletList($syaratKetentuan)
            : '<ol><li>Proposal ini berlaku selama 14 (empat belas) hari kalender sejak tanggal penerbitan.</li><li>Proyek dimulai setelah pembayaran uang muka diterima dan Surat Perjanjian Kerja (SPK) ditandatangani kedua pihak.</li><li>Revisi yang termasuk dalam paket: maksimal 3 (tiga) kali putaran revisi sesuai brief awal.</li><li>Revisi di luar ketentuan akan dikenakan biaya tambahan yang disepakati sebelumnya.</li><li>Hak cipta hasil pekerjaan berpindah kepada klien setelah pembayaran lunas diterima.</li><li>Setiap perubahan scope of work setelah SPK ditandatangani harus melalui addendum tertulis.</li></ol>';

        $out .= '<h1>8. PENUTUP</h1>';
        $out .= '<p>Demikian proposal ini kami sampaikan dengan harapan dapat menjadi landasan kerjasama yang saling menguntungkan antara <strong>' . $namaFreelancer . '</strong> dan <strong>' . $namaKlien . '</strong>.</p>';
        $out .= '<p>Kami berkomitmen untuk memberikan hasil pekerjaan yang terbaik, tepat waktu, dan sesuai dengan ekspektasi klien. Untuk informasi lebih lanjut, silakan menghubungi kami melalui: <strong>' . $kontak . '</strong></p>';
        $out .= '<br>';
        $out .= '<p class="sign-right">Hormat kami,</p>';
        $out .= '<p class="sign-right"><span class="sign-space">&nbsp;</span></p>';
        $out .= '<p class="sign-right"><strong>' . $namaFreelancer . '</strong></p>';

        return $out;
    }

    /* ─────────────────────────────────────
       BISNIS / INVESTOR
    ───────────────────────────────────── */

    private function buildBisnisContent(array $d): string
    {
        $namaBisnis     = $this->e($this->v($d, 'nama_bisnis',          'Nama Bisnis'));
        $bidangUsaha    = $this->e($this->v($d, 'bidang_usaha',         '—'));
        $tahunBerdiri   = $this->e($this->v($d, 'tahun_berdiri',        date('Y')));
        $lokasi         = $this->e($this->v($d, 'lokasi_bisnis',        '—'));
        $visi           = $this->e($this->v($d, 'visi',                 ''));
        $misi           = $this->v($d, 'misi',                 '');
        $profilPendiri  = $this->v($d, 'profil_pendiri',       '');
        $deskriProduk   = $this->v($d, 'deskripsi_produk',     '');
        $nilaiProposisi = $this->v($d, 'nilai_proposisi',      '');
        $targetPasar    = $this->v($d, 'target_pasar',         '');
        $saluranDistrib = $this->v($d, 'saluran_distribusi',   '');
        $strength       = $this->v($d, 'strength',             '');
        $weakness       = $this->v($d, 'weakness',             '');
        $opportunity    = $this->v($d, 'opportunity',          '');
        $threat         = $this->v($d, 'threat',               '');
        $stratMarketing = $this->v($d, 'strategi_pemasaran',   '');
        $kompetitor     = $this->v($d, 'analisis_kompetitor',  '');
        $modalAwal      = $this->e($this->v($d, 'modal_awal',           '—'));
        $totalInvest    = $this->e($this->v($d, 'total_investasi',      '—'));
        $proyeksiPendpt = $this->v($d, 'proyeksi_pendapatan',  '');
        $modelBisnis    = $this->v($d, 'model_bisnis',         '');
        $bep            = $this->v($d, 'break_even',           '');

        $out = '';

        $out .= '<h1>1. EXECUTIVE SUMMARY</h1>';
        $out .= '<p><strong>' . $namaBisnis . '</strong> adalah sebuah usaha yang bergerak di bidang <strong>' . $bidangUsaha . '</strong>, berdiri sejak tahun <strong>' . $tahunBerdiri . '</strong> dan berdomisili di <strong>' . $lokasi . '</strong>.</p>';
        if ($visi) $out .= '<p>Dengan visi <em>"' . $visi . '"</em>, kami berkomitmen untuk menjadi pemain yang diperhitungkan di industri ini.</p>';
        $out .= $deskriProduk ? $this->paragraphs($deskriProduk) : '<p>Proposal bisnis ini disusun sebagai sarana untuk menyampaikan gambaran komprehensif mengenai bisnis kami kepada calon investor dan mitra strategis.</p>';
        $out .= '<p>Kebutuhan investasi yang kami ajukan sebesar <strong>' . $totalInvest . '</strong> akan digunakan untuk pengembangan kapasitas produksi, pemasaran, dan penguatan operasional bisnis.</p>';

        $out .= '<div class="page-break"></div>';
        $out .= '<h1>2. PROFIL PERUSAHAAN</h1>';
        if ($visi) { $out .= '<h2>Visi</h2><p>' . $visi . '</p>'; }
        $out .= '<h2>Misi</h2>';
        $out .= $misi ? $this->bulletList($misi) : '<ol><li>Menghadirkan produk/layanan berkualitas tinggi yang memenuhi kebutuhan pelanggan.</li><li>Membangun hubungan jangka panjang yang saling menguntungkan dengan seluruh pemangku kepentingan.</li><li>Berkontribusi positif terhadap perkembangan ekonomi dan kesejahteraan masyarakat.</li></ol>';
        $out .= '<h2>Profil Tim Pendiri</h2>';
        $out .= $profilPendiri ? $this->paragraphs($profilPendiri) : '<p>Tim pendiri terdiri dari individu-individu yang memiliki latar belakang pendidikan dan pengalaman kerja yang relevan di bidangnya. Dengan kombinasi keahlian yang saling melengkapi, tim ini memiliki kapabilitas yang dibutuhkan untuk mengelola dan mengembangkan bisnis secara optimal.</p>';

        $out .= '<h1>3. PRODUK DAN LAYANAN</h1>';
        if ($deskriProduk)   $out .= $this->paragraphs($deskriProduk);
        if ($nilaiProposisi) { $out .= '<h2>Nilai Proposisi (Value Proposition)</h2>' . $this->paragraphs($nilaiProposisi); }
        if ($modelBisnis)    { $out .= '<h2>Model Bisnis</h2>' . $this->paragraphs($modelBisnis); }

        $out .= '<h1>4. ANALISIS PASAR</h1>';
        if ($targetPasar)    { $out .= '<h2>Target Pasar</h2>' . $this->paragraphs($targetPasar); }
        if ($saluranDistrib) { $out .= '<h2>Strategi Go-to-Market</h2>' . $this->paragraphs($saluranDistrib); }

        $out .= '<div class="page-break"></div>';
        $out .= '<h1>5. ANALISIS SWOT</h1>';

        /* SWOT Table — 2×2 with professional styling */
        $swotHtml = '<table style="width:100%;border-collapse:collapse;margin:10pt 0 18pt;table-layout:fixed;">';
        $swotHtml .= '<thead><tr>'
            . '<th style="background:#1e5496;color:#fff;border:2pt solid #1e5496;padding:8pt 12pt;width:50%;text-align:center;">S — Strengths (Kekuatan)</th>'
            . '<th style="background:#c00000;color:#fff;border:2pt solid #c00000;padding:8pt 12pt;width:50%;text-align:center;">W — Weaknesses (Kelemahan)</th>'
            . '</tr></thead>';
        $swotHtml .= '<tbody><tr>'
            . '<td style="border:2pt solid #1e5496;padding:10pt 12pt;vertical-align:top;">' . ($strength ? nl2br($this->e($strength)) : '• Produk/layanan berkualitas tinggi<br>• Tim yang berpengalaman<br>• Inovasi yang terus berkembang') . '</td>'
            . '<td style="border:2pt solid #c00000;padding:10pt 12pt;vertical-align:top;">' . ($weakness ? nl2br($this->e($weakness)) : '• Modal yang masih terbatas<br>• Brand awareness yang perlu ditingkatkan<br>• SDM yang perlu dikembangkan') . '</td>'
            . '</tr>';
        $swotHtml .= '<tr>'
            . '<th style="background:#375623;color:#fff;border:2pt solid #375623;padding:8pt 12pt;text-align:center;">O — Opportunities (Peluang)</th>'
            . '<th style="background:#7b3f00;color:#fff;border:2pt solid #7b3f00;padding:8pt 12pt;text-align:center;">T — Threats (Ancaman)</th>'
            . '</tr>';
        $swotHtml .= '<tr>'
            . '<td style="border:2pt solid #375623;padding:10pt 12pt;vertical-align:top;">' . ($opportunity ? nl2br($this->e($opportunity)) : '• Pasar yang terus berkembang<br>• Perubahan perilaku konsumen yang menguntungkan<br>• Kemajuan teknologi yang mendukung') . '</td>'
            . '<td style="border:2pt solid #7b3f00;padding:10pt 12pt;vertical-align:top;">' . ($threat ? nl2br($this->e($threat)) : '• Persaingan yang semakin ketat<br>• Perubahan regulasi dan kebijakan<br>• Ketidakpastian kondisi ekonomi') . '</td>'
            . '</tr></tbody></table>';
        $out .= $swotHtml;

        if ($stratMarketing || $kompetitor) {
            $out .= '<h1>6. STRATEGI PEMASARAN DAN KOMPETITOR</h1>';
            if ($stratMarketing) { $out .= '<h2>Strategi Pemasaran</h2>' . $this->paragraphs($stratMarketing); }
            if ($kompetitor)     { $out .= '<h2>Analisis Kompetitor</h2>' . $this->paragraphs($kompetitor); }
        }

        $out .= '<div class="page-break"></div>';
        $out .= '<h1>7. PROYEKSI KEUANGAN</h1>';
        $out .= '<h2>Kebutuhan Modal</h2>';
        $thead = $this->thRow(['No', 'Kebutuhan', ['Jumlah (Rp)', 'text-align:right;']]);
        $tbody = $this->tdRow(['1', 'Modal Kerja (Operasional)',    ['[Nominal]', 'text-align:right;']])
            . $this->tdRow(['2', 'Pengadaan Aset / Peralatan',     ['[Nominal]', 'text-align:right;']])
            . $this->tdRow(['3', 'Pemasaran dan Promosi',          ['[Nominal]', 'text-align:right;']])
            . $this->tdRow(['4', 'Pengembangan SDM',               ['[Nominal]', 'text-align:right;']])
            . $this->tdRow([['<strong>Total Investasi Dibutuhkan</strong>', '', '2'], ['<strong>' . $totalInvest . '</strong>', 'text-align:right;']], true);
        $out .= $this->tableWrap($thead, $tbody);

        if ($proyeksiPendpt) { $out .= '<h2>Proyeksi Pendapatan</h2>' . $this->paragraphs($proyeksiPendpt); }

        $out .= '<h2>Proyeksi Keuangan 3 Tahun</h2>';
        $thead = $this->thRow(['Indikator', 'Tahun 1', 'Tahun 2', 'Tahun 3']);
        $center = 'text-align:center;';
        $tbody = $this->tdRow(['Proyeksi Pendapatan', ['[Rp.]', $center], ['[Rp.]', $center], ['[Rp.]', $center]])
            . $this->tdRow(['Biaya Operasional',    ['[Rp.]', $center], ['[Rp.]', $center], ['[Rp.]', $center]])
            . $this->tdRow(['Laba Bersih',          ['[Rp.]', $center], ['[Rp.]', $center], ['[Rp.]', $center]])
            . $this->tdRow(['Margin Keuntungan',    ['[%]', $center],   ['[%]', $center],   ['[%]', $center]]);
        $out .= $this->tableWrap($thead, $tbody);

        if ($bep) { $out .= '<h2>Break Even Point (BEP)</h2>' . $this->paragraphs($bep); }

        $out .= '<h1>8. PENUTUP DAN AJAKAN KEMITRAAN</h1>';
        $out .= '<p>Kami meyakini bahwa <strong>' . $namaBisnis . '</strong> memiliki potensi yang besar untuk berkembang dan memberikan keuntungan yang menarik bagi seluruh pemangku kepentingan.</p>';
        $out .= '<p>Kami mengundang Anda untuk bergabung sebagai mitra strategis dan bersama-sama membangun bisnis yang berkelanjutan, menguntungkan, dan memberikan dampak positif bagi masyarakat.</p>';

        return $out;
    }

    /* ─────────────────────────────────────
       EVENT / ACARA
    ───────────────────────────────────── */

    private function buildEventContent(array $d): string
    {
        $namaAcara       = $this->e($this->v($d, 'nama_acara',              'Nama Acara'));
        $temaAcara       = $this->e($this->v($d, 'tema_acara',              '—'));
        $penyelenggara   = $this->e($this->v($d, 'penyelenggara',           '—'));
        $tanggalAcara    = $this->e($this->v($d, 'tanggal_acara',           '—'));
        $lokasiAcara     = $this->e($this->v($d, 'lokasi_acara',            '—'));
        $targetPeserta   = $this->e($this->v($d, 'target_peserta',          '—'));
        $targetSegmen    = $this->v($d, 'target_segmen',            '');
        $narahubung      = $this->e($this->v($d, 'narahubung',              '—'));
        $latarAcara      = $this->v($d, 'latar_belakang_acara',    '');
        $tujuanAcara     = $this->v($d, 'tujuan_acara',            '');
        $konsepAcara     = $this->v($d, 'konsep_acara',            '');
        $manfaatAcara    = $this->v($d, 'manfaat_acara',           '');
        $panitia         = $this->v($d, 'struktur_panitia',        '');
        $rundown         = $this->v($d, 'rundown_acara',           '');
        $pembicara       = $this->v($d, 'pembicara_tamu',          '');
        $rincianAnggaran = $this->v($d, 'rincian_anggaran_event',  '');
        $totalAnggaran   = $this->e($this->v($d, 'total_anggaran_event',    '—'));
        $paketSponsor    = $this->v($d, 'paket_sponsorship',       '');
        $deadlineSponsor = $this->e($this->v($d, 'deadline_sponsor',        '—'));

        $out = '';

        $out .= '<h1>1. LATAR BELAKANG</h1>';
        $out .= $latarAcara ? $this->paragraphs($latarAcara)
            : '<p>Penyelenggaraan kegiatan <strong>' . $namaAcara . '</strong> ini dilatarbelakangi oleh kebutuhan nyata yang dirasakan oleh masyarakat luas. Kegiatan ini merupakan wujud kepedulian dan komitmen <strong>' . $penyelenggara . '</strong> dalam berkontribusi positif bagi komunitas dan lingkungan sekitar.</p>';

        $out .= '<h1>2. NAMA DAN TEMA KEGIATAN</h1>';
        $infoTbody = '<tr><td style="border:1pt solid #ccc;padding:7pt 12pt;font-weight:bold;width:35%;background:#f0f4fb;">Nama Kegiatan</td><td style="border:1pt solid #ccc;padding:7pt 12pt;">' . $namaAcara . '</td></tr>'
            . '<tr><td style="border:1pt solid #ccc;padding:7pt 12pt;font-weight:bold;background:#f0f4fb;">Tema</td><td style="border:1pt solid #ccc;padding:7pt 12pt;font-style:italic;">"' . $temaAcara . '"</td></tr>'
            . '<tr><td style="border:1pt solid #ccc;padding:7pt 12pt;font-weight:bold;background:#f0f4fb;">Penyelenggara</td><td style="border:1pt solid #ccc;padding:7pt 12pt;">' . $penyelenggara . '</td></tr>'
            . '<tr><td style="border:1pt solid #ccc;padding:7pt 12pt;font-weight:bold;background:#f0f4fb;">Tanggal Pelaksanaan</td><td style="border:1pt solid #ccc;padding:7pt 12pt;">' . $tanggalAcara . '</td></tr>'
            . '<tr><td style="border:1pt solid #ccc;padding:7pt 12pt;font-weight:bold;background:#f0f4fb;">Tempat Pelaksanaan</td><td style="border:1pt solid #ccc;padding:7pt 12pt;">' . $lokasiAcara . '</td></tr>'
            . '<tr><td style="border:1pt solid #ccc;padding:7pt 12pt;font-weight:bold;background:#f0f4fb;">Target Peserta</td><td style="border:1pt solid #ccc;padding:7pt 12pt;">' . $targetPeserta . ' orang</td></tr>'
            . '<tr><td style="border:1pt solid #ccc;padding:7pt 12pt;font-weight:bold;background:#f0f4fb;">Narahubung</td><td style="border:1pt solid #ccc;padding:7pt 12pt;">' . $narahubung . '</td></tr>';
        $out .= '<table style="width:100%;border-collapse:collapse;margin:10pt 0 18pt;"><tbody>' . $infoTbody . '</tbody></table>';

        $out .= '<h1>3. TUJUAN KEGIATAN</h1>';
        $out .= $tujuanAcara ? $this->bulletList($tujuanAcara)
            : '<ol><li>Menyelenggarakan kegiatan yang memberikan manfaat nyata bagi peserta dan masyarakat.</li><li>Mempertemukan berbagai pihak dalam satu forum yang produktif dan positif.</li><li>Membangun jejaring dan sinergi antar komunitas, organisasi, dan lembaga terkait.</li><li>Meningkatkan kualitas sumber daya manusia melalui program yang terstruktur dan bermakna.</li></ol>';

        $out .= '<h1>4. DESKRIPSI KEGIATAN</h1>';
        $out .= $konsepAcara ? $this->paragraphs($konsepAcara) : '<p>Kegiatan <strong>' . $namaAcara . '</strong> akan diselenggarakan dalam format yang inovatif dan interaktif, dirancang untuk menghadirkan pengalaman yang berkesan bagi seluruh peserta.</p>';
        if ($manfaatAcara) { $out .= '<h2>Manfaat Kegiatan</h2>' . $this->bulletList($manfaatAcara); }
        if ($targetSegmen) { $out .= '<h2>Sasaran Peserta</h2>' . $this->paragraphs($targetSegmen); }

        $out .= '<div class="page-break"></div>';
        $out .= '<h1>5. SUSUNAN ACARA (RUNDOWN)</h1>';
        if ($rundown) $out .= $this->paragraphs($rundown);

        $thead = $this->thRow([['Waktu', '100pt'], 'Kegiatan', ['PIC', '100pt']]);
        $tbody = $this->tdRow(['07.00 – 08.00', 'Registrasi Peserta', 'Sie Acara'])
            . $this->tdRow(['08.00 – 08.30', 'Pembukaan &amp; Sambutan', 'MC'])
            . $this->tdRow(['08.30 – 10.00', 'Acara Utama / Sesi I', 'Pembicara'])
            . $this->tdRow(['10.00 – 10.15', 'Coffee Break', 'Sie Konsumsi'])
            . $this->tdRow(['10.15 – 12.00', 'Sesi II / Workshop', 'Fasilitator'])
            . $this->tdRow(['12.00 – 13.00', 'Ishoma', '—'])
            . $this->tdRow(['13.00 – 15.00', 'Sesi III / Penutup', 'MC'])
            . $this->tdRow(['15.00', 'Penutupan &amp; Dokumentasi', 'Panitia']);
        $out .= $this->tableWrap($thead, $tbody);
        if ($pembicara) { $out .= '<h2>Pembicara / Tamu Undangan</h2>' . $this->bulletList($pembicara); }

        $out .= '<h1>6. SUSUNAN KEPANITIAAN</h1>';
        if ($panitia) $out .= $this->paragraphs($panitia);
        $thead = $this->thRow(['No', 'Jabatan', 'Nama', 'Tanggung Jawab']);
        $tbody = $this->tdRow(['1', 'Ketua Panitia', '[Nama]', 'Koordinasi umum'])
            . $this->tdRow(['2', 'Sekretaris', '[Nama]', 'Administrasi &amp; surat menyurat'])
            . $this->tdRow(['3', 'Bendahara', '[Nama]', 'Keuangan &amp; anggaran'])
            . $this->tdRow(['4', 'Sie Acara', '[Nama]', 'Rundown &amp; teknis acara'])
            . $this->tdRow(['5', 'Sie Humas', '[Nama]', 'Sponsorship &amp; publikasi'])
            . $this->tdRow(['6', 'Sie Dokumentasi', '[Nama]', 'Foto &amp; video']);
        $out .= $this->tableWrap($thead, $tbody);

        $out .= '<div class="page-break"></div>';
        $out .= '<h1>7. RENCANA ANGGARAN KEGIATAN</h1>';
        if ($rincianAnggaran) $out .= $this->paragraphs($rincianAnggaran);
        $thead = $this->thRow(['No', 'Item Kebutuhan', 'Qty', 'Harga Satuan', 'Total']);
        $tbody = $this->tdRow(['1', 'Sewa Venue',                '1',             '[Harga]', '[Total]'])
            . $this->tdRow(['2', 'Konsumsi Peserta',             $targetPeserta,  '[Harga]', '[Total]'])
            . $this->tdRow(['3', 'Dekorasi &amp; Properti',      '1',             '[Harga]', '[Total]'])
            . $this->tdRow(['4', 'Soundsystem &amp; Multimedia', '1',             '[Harga]', '[Total]'])
            . $this->tdRow(['5', 'Publikasi &amp; Dokumentasi',  '1',             '[Harga]', '[Total]'])
            . $this->tdRow([['<strong>TOTAL ANGGARAN</strong>', '', '4'], ['<strong>' . $totalAnggaran . '</strong>']], true);
        $out .= $this->tableWrap($thead, $tbody);

        $out .= '<h1>8. PAKET SPONSORSHIP</h1>';
        if ($paketSponsor) $out .= $this->paragraphs($paketSponsor);
        $thead = $this->thRow(['Benefit',
            ['Platinum', 'text-align:center;'],
            ['Gold', 'text-align:center;'],
            ['Silver', 'text-align:center;']]);
        $c = 'text-align:center;';
        $tbody = $this->tdRow(['Kontribusi Minimum',   ['[Rp.]', $c], ['[Rp.]', $c], ['[Rp.]', $c]])
            . $this->tdRow(['Logo di Banner Utama',    ['V (Besar)', $c], ['V (Sedang)', $c], ['V (Kecil)', $c]])
            . $this->tdRow(['Sebutan oleh MC',         ['V (3x)', $c], ['V (2x)', $c], ['V (1x)', $c]])
            . $this->tdRow(['Tiket VIP',               ['5 tiket', $c], ['3 tiket', $c], ['2 tiket', $c]]);
        $out .= $this->tableWrap($thead, $tbody);
        $out .= '<p><strong>Batas waktu konfirmasi sponsorship: ' . $deadlineSponsor . '</strong></p>';

        $out .= '<h1>9. PENUTUP</h1>';
        $out .= '<p>Demikian proposal kegiatan <strong>' . $namaAcara . '</strong> ini kami sampaikan. Besar harapan kami agar Bapak/Ibu/Perusahaan berkenan untuk memberikan dukungan dan partisipasinya dalam mewujudkan kegiatan ini.</p>';
        $out .= '<p>Untuk informasi lebih lanjut dan konfirmasi partisipasi, silakan menghubungi narahubung kami: <strong>' . $narahubung . '</strong></p>';
        $out .= '<br>';
        $out .= '<p class="sign-right">Hormat kami,<br>Panitia Pelaksana<br><strong>' . $namaAcara . '</strong></p>';
        $out .= '<p class="sign-right"><span class="sign-space">&nbsp;</span></p>';
        $out .= '<p class="sign-right"><strong>' . $penyelenggara . '</strong></p>';

        return $out;
    }

    /* ═══════════════════════════════════════════════════
       HELPERS
    ═══════════════════════════════════════════════════ */

    private function v(array $data, string $key, string $default = ''): string
    {
        $v = $data[$key] ?? $default;
        return (is_string($v) && trim($v) !== '') ? trim($v) : $default;
    }

    private function e(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function paragraphs(string $text): string
    {
        $lines = array_filter(array_map('trim', explode("\n", $text)));
        $out   = '';
        foreach ($lines as $line) {
            $out .= '<p>' . $this->e($line) . '</p>';
        }
        return $out ?: '<p>' . $this->e($text) . '</p>';
    }

    private function bulletList(string $text): string
    {
        $lines = array_filter(array_map('trim', explode("\n", $text)));
        if (empty($lines)) return '<p>' . $this->e($text) . '</p>';
        $out = '<ol>';
        foreach ($lines as $line) {
            $clean = ltrim($line, '-•*1234567890.) ');
            $out  .= '<li>' . $this->e($clean) . '</li>';
        }
        $out .= '</ol>';
        return $out;
    }

    private function getDocTitle(array $data, string $templateName): string
    {
        return $data['judul_proposal']
            ?? $data['judul_proyek']
            ?? $data['nama_acara']
            ?? $data['nama_bisnis']
            ?? $templateName;
    }
}