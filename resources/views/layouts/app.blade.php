<!DOCTYPE html>
<html lang="id" prefix="og: https://ogp.me/ns#">
<head>
    @php
        $siteName     = config('app.name', 'MediaTools');
        $appUrl       = rtrim(config('app.url'), '/');
        $currentUrl   = url()->current();
        $canonicalUrl = strtok($currentUrl, '?');
        $canonicalUrl = preg_replace('/^http:\/\//', 'https://', $canonicalUrl);
        $canonicalUrl = ($canonicalUrl !== $appUrl) ? rtrim($canonicalUrl, '/') : $canonicalUrl;

        $defaultTitle   = 'MediaTools — Tools Digital Gratis: Invoice, PDF, QR Code & Lebih';
        $defaultDesc    = 'Platform tools produktivitas digital 100% gratis untuk UMKM, freelancer, dan kreator Indonesia. Invoice, QR Code, PDF, hapus background foto, dan banyak lagi.';
        $defaultKeywords= 'tools online gratis, invoice generator, hapus background foto, konversi pdf, qr code generator, link tree, password generator, indonesia';

        $pageTitle    = trim(View::yieldContent('title'))            ?: $defaultTitle;
        $pageDesc     = trim(View::yieldContent('meta_description')) ?: $defaultDesc;
        $pageKeywords = trim(View::yieldContent('meta_keywords'))    ?: $defaultKeywords;

        $ogImageKey = View::hasSection('og_image') ? trim(View::yieldContent('og_image')) : 'home';
        $ogImageUrl = $appUrl . '/images/og/' . $ogImageKey . '.png';

        $globalSchemas = [
            [
                '@context'  => 'https://schema.org',
                '@type'     => 'WebSite',
                '@id'       => $appUrl . '/#website',
                'url'       => $appUrl,
                'name'      => $siteName,
                'description' => $defaultDesc,
                'inLanguage'=> 'id-ID',
                'publisher' => ['@id' => $appUrl . '/#organization'],
                'potentialAction' => [
                    '@type'       => 'SearchAction',
                    'target'      => ['@type' => 'EntryPoint', 'urlTemplate' => $appUrl . '/?s={search_term_string}'],
                    'query-input' => 'required name=search_term_string',
                ],
            ],
            [
                '@context' => 'https://schema.org',
                '@type'    => 'Organization',
                '@id'      => $appUrl . '/#organization',
                'name'     => $siteName,
                'url'      => $appUrl,
                'logo'     => [
                    '@type'  => 'ImageObject',
                    'url'    => $appUrl . '/images/icons-mediatools.png',
                    'width'  => 512,
                    'height' => 512,
                ],
                'contactPoint' => [
                    '@type'             => 'ContactPoint',
                    'email'             => 'halo@mediatools.id',
                    'contactType'       => 'customer support',
                    'areaServed'        => 'ID',
                    'availableLanguage' => 'Indonesian',
                ],
            ],
        ];
    @endphp

    {{-- ── Core ── --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ── SEO ── --}}
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDesc }}">
    <meta name="keywords"    content="{{ $pageKeywords }}">
    <meta name="robots"      content="@yield('robots', 'index, follow, max-image-preview:large, max-snippet:-1')">
    <meta name="author"           content="{{ $siteName }}">
    <meta name="theme-color"      content="#0a0a0b">
    <meta name="application-name" content="{{ $siteName }}">
    <meta name="google-site-verification" content="W4l-4NDtoXzK2oMrmBmFZ1Yj9Os9jK1bEqbUUmBJi5o">

    <link rel="canonical" href="{{ $canonicalUrl }}">

    {{-- ── Open Graph ── --}}
    <meta property="og:title"       content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDesc }}">
    <meta property="og:type"        content="@yield('og_type', 'website')">
    <meta property="og:url"         content="{{ $canonicalUrl }}">
    <meta property="og:site_name"   content="{{ $siteName }}">
    <meta property="og:locale"      content="id_ID">
    <meta property="og:image"       content="{{ $ogImageUrl }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height"content="630">
    <meta property="og:image:alt"   content="{{ $pageTitle }}">

    {{-- ── Twitter ── --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:site"        content="@mediatools">
    <meta name="twitter:title"       content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDesc }}">
    <meta name="twitter:image"       content="{{ $ogImageUrl }}">

    {{-- ── Favicons ── --}}
    <link rel="icon"             href="{{ asset('favicon.ico') }}">
    <link rel="icon"             type="image/png" sizes="32x32" href="{{ asset('images/icons-mediatools.png') }}">
    <link rel="apple-touch-icon" sizes="180x180"               href="{{ asset('images/icons-mediatools.png') }}">
    <link rel="manifest"         href="{{ asset('site.webmanifest') }}">

    {{-- ── Preconnect ── --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">

    {{-- ── Fonts — Geist via Google or CDN fallback ── --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- ── Icons ── --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    {{-- ── Tailwind (utility escape hatch) ── --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- ── Main CSS ── --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    {{-- Per-page SEO stack --}}
    @stack('seo')

    {{-- Global JSON-LD --}}
    <script type="application/ld+json">
    {!! json_encode($globalSchemas, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    {{-- Per-page styles --}}
    @stack('styles')
</head>
<body class="antialiased">

    @include('components.navbar')

    <main id="main-content" role="main" style="padding-top:60px;">
        @yield('content')
    </main>

    @include('components.footer')

    {{-- Core JS ── must come before page-specific scripts --}}
    <script src="{{ asset('js/app.js') }}" defer></script>

    {{-- Global behaviours ── inline so they run regardless of defer --}}
    <script>
    /* ── Reveal on scroll ── */
    (function () {
        if (!('IntersectionObserver' in window)) {
            document.querySelectorAll('.reveal').forEach(function(el){ el.classList.add('visible'); });
            return;
        }
        var io = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.reveal').forEach(function(el){ io.observe(el); });
    })();

    /* ── Animated counters ── */
    function animateCounter(el) {
        var target  = parseFloat(el.dataset.target);
        if (isNaN(target)) return;
        var suffix  = el.dataset.suffix || '';
        var prefix  = el.dataset.prefix || '';
        var decimal = String(target).includes('.');
        var duration = 1600;
        var start    = performance.now();
        function step(now) {
            var p = Math.min((now - start) / duration, 1);
            var e = 1 - Math.pow(1 - p, 3);
            el.textContent = prefix + (decimal ? (e*target).toFixed(1) : Math.floor(e*target)) + suffix;
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }
    (function () {
        if (!('IntersectionObserver' in window)) return;
        var io = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        document.querySelectorAll('[data-target]').forEach(function(el){ io.observe(el); });
    })();

    /* ── FAQ accordion ── */
    (function () {
        document.querySelectorAll('.faq-question').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var item   = btn.closest('.faq-item');
                var answer = item && item.querySelector('.faq-answer');
                var isOpen = item && item.classList.contains('open');
                /* Close all */
                document.querySelectorAll('.faq-item.open').forEach(function(el) {
                    el.classList.remove('open');
                    var a = el.querySelector('.faq-answer');
                    if (a) a.classList.remove('open');
                });
                /* Open clicked */
                if (!isOpen && item && answer) {
                    item.classList.add('open');
                    answer.classList.add('open');
                }
            });
        });
    })();
    </script>

    {{-- Per-page scripts --}}
    @stack('scripts')
</body>
</html>