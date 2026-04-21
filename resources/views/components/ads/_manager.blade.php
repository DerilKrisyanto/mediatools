{{--
    ADS MANAGER — Global Script Injector
    ─────────────────────────────────────
    • Dipanggil 1x di layouts/app.blade.php (sebelum @stack scripts)
    • Hanya inject script di halaman tools, TIDAK di home/auth/profile
    • Adsterra : tidak butuh script global — dimuat langsung per slot
    • AdSense  : wajib load 1x di sini

    CARA TAMBAH TOOLS BARU:
    Tambahkan pattern URL-nya di array $toolsRoutes di bawah.
    Gunakan wildcard * untuk prefix dengan sub-route.
--}}

@php
    $adsEnabled = config('ads.enabled', false);
    $provider   = config('ads.provider', 'none');

    $toolsRoutes = [
        // ── Invoice ───────────────────────────
        'invoice',

        // ── Background Remover ────────────────
        'bg',
        'bg/*',

        // ── LinkTree ──────────────────────────
        'linktree',
        'linktree/*',

        // ── Email Signature ───────────────────
        'signature',
        'signature/*',

        // ── QR Code ───────────────────────────
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

        // ── Finance (auth-required) ───────────
        // Iklan tetap ditampilkan di halaman finance karena user sudah login
        'finance',
        'finance/*',

        // ── Fotobox ───────────────────────────
        'fotobox',

        /*
        |────────────────────────────────────────
        | TAMBAHKAN TOOLS BARU DI SINI
        | Contoh:
        | 'nama-tool',
        | 'nama-tool/*',
        |────────────────────────────────────────
        */
    ];

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
        @once
        <script async
            src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ config('ads.adsense.client_id') }}"
            crossorigin="anonymous">
        </script>
        @endonce
    @endif

@endif
