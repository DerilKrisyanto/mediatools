{{--
    ADS MANAGER — Global Script Injector
    ─────────────────────────────────────
    • Dipanggil 1x di layouts/app.blade.php (sebelum @stack scripts)
    • Hanya inject script di halaman tools, TIDAK di home/auth/profile
    • Adsterra : tidak butuh script global — dimuat langsung per slot
    • AdSense  : wajib load 1x di sini

    SUMBER ROUTE (routes/web.php) — update jika ada tools baru:
    ┌─────────────────────────┬────────────────────────────┐
    │ Route                   │ Pattern                    │
    ├─────────────────────────┼────────────────────────────┤
    │ /invoice                │ invoice                    │
    │ /bg, /bg/process        │ bg, bg/*                   │
    │ /linktree, /linktree/*  │ linktree, linktree/*       │
    │ /signature, /sign...    │ signature, signature/*     │
    │ /qr, /qr/*              │ qr, qr/*                   │
    │ /pdfutilities, /pdf...  │ pdfutilities, pdfutilities*│
    │ /imageconverter         │ imageconverter             │
    │ /password-generator     │ password-generator         │
    │ /media-downloader, /*   │ media-downloader*          │
    │ /file-converter, /*     │ file-converter*            │
    │ /sanitizer, /*          │ sanitizer, sanitizer/*     │
    └─────────────────────────┴────────────────────────────┘

    CARA TAMBAH TOOLS BARU:
    Tambahkan pattern URL-nya di array $toolsRoutes di bawah.
    Gunakan wildcard * untuk prefix dengan sub-route.
--}}

@php
    $adsEnabled = config('ads.enabled', false);
    $provider   = config('ads.provider', 'none');

    /*
    |──────────────────────────────────────────────────────────
    | DAFTAR PATTERN HALAMAN TOOLS
    | Sesuaikan dengan routes/web.php
    | Gunakan '*' untuk wildcard (misal: 'bg/*' cocok dengan
    | /bg/process, /bg/download/abc, dll)
    |──────────────────────────────────────────────────────────
    */
    $toolsRoutes = [

        // ── Invoice ───────────────────────────
        'invoice',

        // ── Background Remover (prefix: /bg) ──
        'bg',
        'bg/*',

        // ── LinkTree (prefix: /linktree) ──────
        'linktree',
        'linktree/*',

        // ── Email Signature (prefix: /signature)
        'signature',
        'signature/*',

        // ── QR Code (prefix: /qr) ─────────────
        'qr',
        'qr/*',

        // ── PDF Utilities ─────────────────────
        'pdfutilities',
        'pdfutilities/*',

        // ── Image Converter ───────────────────
        'imageconverter',

        // ── Password Generator ────────────────
        'password-generator',

        // ── Media Downloader ──────────────────
        'media-downloader',
        'media-downloader/*',

        // ── File Converter ────────────────────
        'file-converter',
        'file-converter/*',

        // ── Metadata & Privacy Sanitizer ──────
        'sanitizer',
        'sanitizer/*',

        /*
        |────────────────────────────────────────
        | TAMBAHKAN TOOLS BARU DI SINI
        | Contoh:
        | 'nama-tool',
        | 'nama-tool/*',
        |────────────────────────────────────────
        */
    ];

    // Deteksi apakah request saat ini cocok dengan salah satu pattern
    $isToolsPage = false;
    foreach ($toolsRoutes as $pattern) {
        if (request()->is($pattern)) {
            $isToolsPage = true;
            break;
        }
    }
@endphp

@if($adsEnabled && $provider !== 'none' && $isToolsPage)

    @if($provider === 'adsense')
        {{--
            Google AdSense: wajib load script global 1x.
            Script ini harus ada sebelum tag <ins class="adsbygoogle">
            di semua slot. @once memastikan tidak double-load.
        --}}
        @once
        <script async
            src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ config('ads.adsense.client_id') }}"
            crossorigin="anonymous">
        </script>
        @endonce
    @endif

    {{--
        Adsterra: TIDAK butuh script global di sini.
        Script Adsterra dimuat langsung di masing-masing slot blade:
        - banner-header.blade.php
        - banner-content.blade.php
        - banner-sidebar.blade.php
        - banner-result.blade.php
    --}}

@endif