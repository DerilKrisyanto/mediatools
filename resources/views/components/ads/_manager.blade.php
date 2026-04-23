{{--
    ADS MANAGER — Global Script Injector
    ─────────────────────────────────────
    • Dipanggil 1x di layouts/app.blade.php
    • Inject: Adsterra Popunder (tertinggi CPM) + Social Bar
    • Adsterra banner dimuat langsung per slot blade
    CARA TAMBAH TOOLS BARU: tambahkan URL di $toolsRoutes
--}}

@php
    $adsEnabled = config('ads.enabled', false);
    $provider   = config('ads.provider', 'none');

    $toolsRoutes = [
        'invoice',
        'bg', 'bg/*',
        'linktree', 'linktree/*',
        'signature', 'signature/*',
        'qr', 'qr/*',
        'pdfutilities', 'pdfutilities/*',
        'imageconverter',
        'password-generator',
        'media-downloader', 'media-downloader/*',
        'file-converter', 'file-converter/*',
        'sanitizer', 'sanitizer/*',
        'finance', 'finance/*',
        'fotobox',
        'pasfoto',
        // TAMBAH TOOLS BARU DI SINI
    ];

    $isToolsPage = false;
    foreach ($toolsRoutes as $pattern) {
        if (request()->is($pattern)) { $isToolsPage = true; break; }
    }
@endphp

@if($adsEnabled && $provider !== 'none' && $isToolsPage)

    @if($provider === 'adsterra')
        {{-- ══ ADSTERRA POPUNDER — FORMAT TERTINGGI CPM ══
             Muncul 1x per sesi per user saat pertama masuk tools page
             GANTI KEY dengan key Popunder dari dashboard Adsterra kamu jika ingin di aktifkan
        --}}
        <!-- <script type="text/javascript">
            // Popunder: tampil 1x per sesi, tidak ganggu UX
            (function(){
                var d = document.createElement('script');
                d.type = 'text/javascript';
                d.async = true;
                // GANTI INI: dapatkan URL invoke dari Adsterra > Direct Links > Popunder
                d.src = '//pl29229491.profitablecpmratenetwork.com/79/4d/c2/794dc2cd071e8a600e89f16cf14332b0.js';
                document.head.appendChild(d);
            })();
        </script> -->

        {{-- ══ ADSTERRA SOCIAL BAR — STICKY FOOTER, CPM BAGUS ══
             Banner kecil sticky di bawah layar, tidak menutup konten
             GANTI KEY dengan key Social Bar dari dashboard Adsterra jika ingin di aktifkan
        --}}
        <!-- <script type='text/javascript'
            src='https://pl29229705.profitablecpmratenetwork.com/41/c7/04/41c704db942d55da8f8c9f3e83440567.js'
            async>
        </script> -->


    @elseif($provider === 'adsense')
        @once
        <script async
            src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ config('ads.adsense.client_id') }}"
            crossorigin="anonymous">
        </script>
        @endonce
    @endif

@endif