<!DOCTYPE html>
<html lang="id" prefix="og: https://ogp.me/ns#">
<head>
    @php
        $siteName       = config('app.name', 'MediaTools');
        $appUrl         = rtrim(config('app.url'), '/');
        $currentUrl     = url()->current();

        // Strip query strings from canonical — prevents duplicate/querystring canonicals
        $canonicalUrl   = strtok($currentUrl, '?');
        // Force HTTPS
        $canonicalUrl   = preg_replace('/^http:\/\//', 'https://', $canonicalUrl);
        // Remove trailing slash except for root
        $canonicalUrl   = ($canonicalUrl !== $appUrl) ? rtrim($canonicalUrl, '/') : $canonicalUrl;

        $defaultTitle   = $siteName . ' | All-in-One Media Suite — Tools Gratis untuk Semua';
        $defaultDesc    = 'MediaTools — platform tools produktivitas digital gratis untuk UMKM, freelancer, dan developer Indonesia. Invoice, QR Code, PDF, hapus background foto, dan banyak lagi.';
        $defaultKeyword = 'tools online gratis, konversi pdf, hapus background foto, invoice generator, qr code generator, link tree, indonesia, umkm';

        $pageTitle      = trim(View::yieldContent('title')) ?: $defaultTitle;
        $pageDesc       = trim(View::yieldContent('meta_description')) ?: $defaultDesc;
        $pageKeywords   = trim(View::yieldContent('meta_keywords')) ?: $defaultKeyword;

        $ogImageKey     = View::hasSection('og_image') ? trim(View::yieldContent('og_image')) : 'home';
        $ogImageUrl     = $appUrl . '/images/og/' . $ogImageKey . '.png';

        $globalSchemas = [
            [
                '@context'    => 'https://schema.org',
                '@type'       => 'WebSite',
                '@id'         => $appUrl . '/#website',
                'url'         => $appUrl,
                'name'        => $siteName,
                'alternateName' => $siteName . ' Indonesia',
                'description' => $defaultDesc,
                'inLanguage'  => 'id-ID',
                'publisher'   => ['@id' => $appUrl . '/#organization'],
                'potentialAction' => [
                    '@type'       => 'SearchAction',
                    'target'      => ['@type' => 'EntryPoint', 'urlTemplate' => $appUrl . '/?s={search_term_string}'],
                    'query-input' => 'required name=search_term_string',
                ],
            ],
            [
                '@context'    => 'https://schema.org',
                '@type'       => 'Organization',
                '@id'         => $appUrl . '/#organization',
                'name'        => $siteName,
                'url'         => $appUrl,
                'logo'        => [
                    '@type'  => 'ImageObject',
                    'url'    => $appUrl . '/images/icons-mediatools.png',
                    'width'  => 512,
                    'height' => 512,
                ],
                'sameAs' => [
                    'https://www.instagram.com/mediatools',
                    'https://www.tiktok.com/@mediatools',
                    'https://x.com/mediatools',
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

    {{-- ── Essential Meta ── --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ── SEO Core ── --}}
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDesc }}">
    <meta name="keywords"    content="{{ $pageKeywords }}">
    {{--
        robots: "max-image-preview:large" → Google shows large images in results
                "max-snippet:-1"          → No limit on snippet length
                pages that should NOT be indexed can override this per-page
                via @section('robots', 'noindex, nofollow')
    --}}
    <meta name="robots" content="@yield('robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')">
    <meta name="author"           content="{{ $siteName }}">
    <meta name="generator"        content="Laravel Blade">
    <meta name="theme-color"      content="#040f0f">
    <meta name="application-name" content="{{ $siteName }}">
    <meta name="apple-mobile-web-app-title"   content="{{ $siteName }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <meta name="referrer"         content="strict-origin-when-cross-origin">
    <meta name="google-site-verification" content="W4l-4NDtoXzK2oMrmBmFZ1Yj9Os9jK1bEqbUUmBJi5o">

    {{--
        CANONICAL — single, clean, HTTPS URL without query strings.
        This is the primary fix for:
        • "Duplikat, tanpa ada versi kanonis pilihan pengguna"
        • "Google memilih versi kanonis yang berbeda"
        • "Halaman alternatif dengan tag kanonis yang tepat"
    --}}
    <link rel="canonical" href="{{ $canonicalUrl }}">

    {{-- ── Open Graph ── --}}
    <meta property="og:title"            content="{{ $pageTitle }}">
    <meta property="og:description"      content="{{ $pageDesc }}">
    <meta property="og:type"             content="@yield('og_type', 'website')">
    <meta property="og:url"              content="{{ $canonicalUrl }}">
    <meta property="og:site_name"        content="{{ $siteName }}">
    <meta property="og:locale"           content="id_ID">
    <meta property="og:image"            content="{{ $ogImageUrl }}">
    <meta property="og:image:secure_url" content="{{ $ogImageUrl }}">
    <meta property="og:image:width"      content="1200">
    <meta property="og:image:height"     content="630">
    <meta property="og:image:alt"        content="{{ $pageTitle }}">

    {{-- ── Twitter Card ── --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:site"        content="@mediatools">
    <meta name="twitter:creator"     content="@mediatools">
    <meta name="twitter:title"       content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDesc }}">
    <meta name="twitter:image"       content="{{ $ogImageUrl }}">
    <meta name="twitter:image:alt"   content="{{ $pageTitle }}">

    {{-- ── Favicons & PWA ── --}}
    <link rel="icon"             href="{{ asset('favicon.ico') }}">
    <link rel="icon"             type="image/png" sizes="32x32" href="{{ asset('images/icons-mediatools.png') }}">
    <link rel="apple-touch-icon" sizes="180x180"               href="{{ asset('images/icons-mediatools.png') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    {{-- ── Preconnect / DNS prefetch ── --}}
    <link rel="preconnect"    href="https://fonts.googleapis.com">
    <link rel="preconnect"    href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch"  href="//fonts.googleapis.com">
    <link rel="dns-prefetch"  href="//cdnjs.cloudflare.com">

    {{-- ── Fonts ── --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- ── Icons ── --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    {{-- ── Tailwind ── --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- ── App CSS ── --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    {{-- Per-page extra SEO (breadcrumbs, tool schema, etc.) --}}
    @stack('seo')

    {{-- ── Global JSON-LD ── --}}
    <script type="application/ld+json">
    {!! json_encode($globalSchemas, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    {{-- Per-page styles --}}
    @stack('styles')
</head>
<body class="antialiased">

    @include('components.navbar')

    <main id="main-content" role="main">
        @yield('content')
    </main>

    @include('components.footer')

    {{-- Core JS (deferred) --}}
    <script src="{{ asset('js/app.js') }}" defer></script>

    <script>
    /* ── Navbar scroll effect ── */
    (function () {
        var nav = document.getElementById('mainNav');
        if (!nav) return;
        window.addEventListener('scroll', function () {
            nav.classList.toggle('scrolled', window.scrollY > 40);
        }, { passive: true });
    })();

    /* ── Reveal on scroll ── */
    (function () {
        var els = document.querySelectorAll('.reveal');
        if (!els.length || !('IntersectionObserver' in window)) {
            // Fallback: just make visible immediately
            els.forEach(function (el) { el.classList.add('visible'); });
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        els.forEach(function (el) { io.observe(el); });
    })();

    /* ── Animated counters ── */
    function animateCounter(el) {
        var target   = parseFloat(el.dataset.target);
        if (isNaN(target)) return;
        var suffix   = el.dataset.suffix || '';
        var prefix   = el.dataset.prefix || '';
        var isDecimal = String(target).includes('.');
        var duration = 1800;
        var start    = performance.now();
        function step(now) {
            var progress = Math.min((now - start) / duration, 1);
            var eased    = 1 - Math.pow(1 - progress, 3);
            var value    = eased * target;
            el.textContent = prefix + (isDecimal ? value.toFixed(1) : Math.floor(value)) + suffix;
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }
    (function () {
        var counters = document.querySelectorAll('[data-target]');
        if (!counters.length || !('IntersectionObserver' in window)) return;
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(function (el) { io.observe(el); });
    })();

    /* ── FAQ Accordion ── */
    (function () {
        var buttons = document.querySelectorAll('.faq-question');
        if (!buttons.length) return;
        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var item = btn.closest('.faq-item');
                if (!item) return;
                var body   = item.querySelector('.faq-body');
                if (!body) return;
                var isOpen = item.classList.contains('open');
                document.querySelectorAll('.faq-item.open').forEach(function (openItem) {
                    openItem.classList.remove('open');
                    var ob = openItem.querySelector('.faq-body');
                    if (ob) ob.classList.remove('open');
                });
                if (!isOpen) {
                    item.classList.add('open');
                    body.classList.add('open');
                }
            });
        });
    })();
    </script>

    {{-- Per-page scripts --}}
    @stack('scripts')
</body>
</html>