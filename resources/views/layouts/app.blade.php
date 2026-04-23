<!DOCTYPE html>
<html lang="id" prefix="og: https://ogp.me/ns#">
<head>
    @php
        $siteName     = config('app.name', 'MediaTools');
        $appUrl       = rtrim(config('app.url', 'https://mediatools.cloud'), '/');
        $currentUrl   = url()->current();

        /* ── Canonical: strip query string, enforce https, no trailing slash (except root) ── */
        $canonicalUrl = preg_replace('/^http:\/\//', 'https://', strtok($currentUrl, '?'));
        $canonicalUrl = ($canonicalUrl !== $appUrl) ? rtrim($canonicalUrl, '/') : $canonicalUrl;

        /* ── Page meta with smart fallbacks ── */
        $defaultTitle    = 'MediaTools — Tools Digital Gratis: Invoice, PDF, QR Code, Background Remover & Lebih';
        $defaultDesc     = 'Platform tools produktivitas digital 100% gratis untuk UMKM, freelancer, dan kreator Indonesia. Invoice, QR Code, hapus background foto, konversi PDF, dan banyak lagi.';
        $defaultKeywords = 'tools online gratis, invoice generator indonesia, hapus background foto, konversi pdf gratis, qr code generator, linktree gratis, password generator, media downloader';

        $pageTitle    = trim(View::yieldContent('title'))            ?: $defaultTitle;
        $pageDesc     = trim(View::yieldContent('meta_description')) ?: $defaultDesc;
        $pageKeywords = trim(View::yieldContent('meta_keywords'))    ?: $defaultKeywords;
        $pageRobots   = trim(View::yieldContent('robots'))           ?: 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

        $ogImageKey = View::hasSection('og_image') ? trim(View::yieldContent('og_image')) : 'home';
        $ogImageUrl = $appUrl . '/images/og/' . $ogImageKey . '.png';

        /* ── GA4 Measurement ID dari .env ── */
        $gaId = config('services.google_analytics_id', '');

        /* ── Global JSON-LD ── */
        $globalSchema = [
            [
                '@context'  => 'https://schema.org',
                '@type'     => 'WebSite',
                '@id'       => $appUrl . '/#website',
                'url'       => $appUrl,
                'name'      => $siteName,
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
                '@context' => 'https://schema.org',
                '@type'    => 'Organization',
                '@id'      => $appUrl . '/#organization',
                'name'     => $siteName,
                'url'      => $appUrl,
                'logo' => [
                    '@type'  => 'ImageObject',
                    'url'    => $appUrl . '/images/mediatools.jpeg',
                    'width'  => 512,
                    'height' => 512,
                ],
                'contactPoint' => [
                    '@type'             => 'ContactPoint',
                    'email'             => 'halo@mediatools.cloud',
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
    <meta name="description"        content="{{ $pageDesc }}">
    <meta name="keywords"           content="{{ $pageKeywords }}">
    <meta name="robots"             content="{{ $pageRobots }}">
    <meta name="author"             content="{{ $siteName }}">
    <meta name="theme-color"        content="#0b1a0b">
    <meta name="application-name"   content="{{ $siteName }}">
    <meta name="google-site-verification" content="W4l-4NDtoXzK2oMrmBmFZ1Yj9Os9jK1bEqbUUmBJi5o">

    {{-- ── Canonical (single, no duplicate) ── --}}
    <link rel="canonical" href="{{ $canonicalUrl }}">

    {{-- ── Alternate hreflang ── --}}
    <link rel="alternate" hreflang="id"       href="{{ $canonicalUrl }}">
    <link rel="alternate" hreflang="x-default" href="{{ $canonicalUrl }}">

    {{-- ── Open Graph ── --}}
    <meta property="og:title"        content="{{ $pageTitle }}">
    <meta property="og:description"  content="{{ $pageDesc }}">
    <meta property="og:type"         content="@yield('og_type', 'website')">
    <meta property="og:url"          content="{{ $canonicalUrl }}">
    <meta property="og:site_name"    content="{{ $siteName }}">
    <meta property="og:locale"       content="id_ID">
    <meta property="og:image"        content="{{ $ogImageUrl }}">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt"    content="{{ $pageTitle }}">

    {{-- ── Twitter Card ── --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:site"        content="@mediatoolsid">
    <meta name="twitter:creator"     content="@mediatoolsid">
    <meta name="twitter:title"       content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDesc }}">
    <meta name="twitter:image"       content="{{ $ogImageUrl }}">

    {{-- ── Favicons ── --}}
    <link rel="icon"             href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="icon"             href="{{ asset('images/mediatools.jpeg') }}" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('images/mediatools.jpeg') }}" sizes="180x180">
    <link rel="manifest"         href="{{ asset('site.webmanifest') }}">

    {{-- ── Resource Hints ── --}}
    <link rel="preconnect"    href="https://fonts.googleapis.com">
    <link rel="preconnect"    href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch"  href="//cdnjs.cloudflare.com">
    @if($gaId)
    <link rel="dns-prefetch"  href="//www.googletagmanager.com">
    <link rel="dns-prefetch"  href="//www.google-analytics.com">
    @endif

    {{-- ── Fonts ── --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- ── Icons ── --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    {{-- ── Tailwind (utility layer only) ── --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- ── Main CSS ── --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    {{-- ── Per-page SEO stack (before closing head, so it can override og/meta) ── --}}
    @stack('seo')

    <meta name="googlebot" content="index, follow, max-image-preview:large">
    <meta name="bingbot" content="index, follow">

    {{-- ── Global Structured Data ── --}}
    <script type="application/ld+json">
    {!! json_encode($globalSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    {{-- ── Per-page structured data ── --}}
    @stack('schema')

    {{-- ── Per-page styles ── --}}
    @stack('styles')

    {{-- OOGLE ANALYTICS --}}
    @if($gaId)
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $gaId }}', {
            // Nonaktifkan iklan Google Ads (privacy-friendly, hanya analytics)
            allow_google_signals: false,
            allow_ad_personalization_signals: false
        });
    </script>
    @endif
</head>
<body class="antialiased">

    @include('components.navbar')

    {{-- Skip to content for accessibility --}}
    <a href="#main-content"
       style="position:absolute;left:-9999px;top:4px;background:var(--accent);color:#0e1c0e;font-weight:700;padding:8px 16px;border-radius:8px;z-index:9999;font-size:13px;"
       onfocus="this.style.left='16px'"
       onblur="this.style.left='-9999px'">
        Lewati ke konten utama
    </a>

    <main id="main-content" role="main" style="padding-top:64px;">
        @yield('content')
    </main>

    @include('components.footer')

    {{-- ── Core JS ── --}}
    <script src="{{ asset('js/app.js') }}" defer></script>

    {{-- ── Global behaviours ── --}}
    <script>
    /* Reveal on scroll */
    (function () {
        if (!('IntersectionObserver' in window)) {
            document.querySelectorAll('.reveal').forEach(function(el){ el.classList.add('visible'); });
            return;
        }
        var io = new IntersectionObserver(function(entries) {
            entries.forEach(function(e) {
                if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); }
            });
        }, { threshold: 0.08 });
        document.querySelectorAll('.reveal').forEach(function(el){ io.observe(el); });
    })();

    /* Animated counters */
    function animateCounter(el) {
        var target   = parseFloat(el.dataset.target); if (isNaN(target)) return;
        var suffix   = el.dataset.suffix  || '';
        var prefix   = el.dataset.prefix  || '';
        var decimal  = String(target).includes('.');
        var duration = 1500;
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
            entries.forEach(function(e) { if (e.isIntersecting) { animateCounter(e.target); io.unobserve(e.target); } });
        }, { threshold: 0.5 });
        document.querySelectorAll('[data-target]').forEach(function(el){ io.observe(el); });
    })();

    /* FAQ */
    (function () {
        document.querySelectorAll('.faq-question').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var item   = btn.closest('.faq-item');
                var answer = item && item.querySelector('.faq-answer');
                var isOpen = item && item.classList.contains('open');
                document.querySelectorAll('.faq-item.open').forEach(function(el) {
                    el.classList.remove('open');
                    var a = el.querySelector('.faq-answer');
                    if (a) a.classList.remove('open');
                });
                if (!isOpen && item && answer) { item.classList.add('open'); answer.classList.add('open'); }
            });
        });
    })();

    /* Search overlay */
    (function () {
        var overlay    = document.getElementById('searchOverlay');
        var input      = document.getElementById('searchInput');
        var browse     = document.getElementById('searchBrowse');
        var results    = document.getElementById('searchResults');
        var grid       = document.getElementById('searchResultsGrid');
        var emptyEl    = document.getElementById('searchEmpty');
        var isOpen     = false;
        var focusIdx   = -1;
        var visible    = [];

        window.openSearch = function () {
            isOpen = true;
            overlay && overlay.classList.add('open');
            document.body.style.overflow = 'hidden';
            setTimeout(function () { input && input.focus(); }, 60);
            resetSearch();
        };
        window.closeSearch = function () {
            isOpen = false;
            overlay && overlay.classList.remove('open');
            document.body.style.overflow = '';
            if (input) input.value = '';
            resetSearch();
        };

        function resetSearch() {
            browse  && browse.classList.remove('hidden');
            results && results.classList.add('hidden');
            emptyEl && emptyEl.classList.add('hidden');
            focusIdx = -1; visible = [];
            document.querySelectorAll('.search-tool-row.focused').forEach(function(el){ el.classList.remove('focused'); });
        }

        if (input) {
            input.addEventListener('input', function () {
                var q = this.value.trim().toLowerCase();
                if (!q) { resetSearch(); return; }
                browse  && browse.classList.add('hidden');
                results && results.classList.remove('hidden');
                if (grid) grid.innerHTML = '';
                visible = []; focusIdx = -1;
                var all = document.querySelectorAll('#searchBrowse .search-tool-row');
                all.forEach(function (item) {
                    var name = (item.dataset.name || '').toLowerCase();
                    var tags = (item.dataset.tags || '').toLowerCase();
                    if (name.includes(q) || tags.includes(q)) {
                        var clone = item.cloneNode(true);
                        clone.addEventListener('click', closeSearch);
                        var nameEl = clone.querySelector('.search-tool-name');
                        if (nameEl) {
                            var re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&') + ')','gi');
                            nameEl.innerHTML = nameEl.textContent.replace(re,'<mark class="search-highlight">$1</mark>');
                        }
                        grid && grid.appendChild(clone);
                        visible.push(clone);
                    }
                });
                emptyEl && emptyEl.classList.toggle('hidden', visible.length > 0);
            });
        }

        document.addEventListener('keydown', function (e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') { e.preventDefault(); isOpen ? closeSearch() : openSearch(); return; }
            if (!isOpen) return;
            if (e.key === 'Escape') { closeSearch(); return; }
            var items = visible.length ? visible : Array.from(document.querySelectorAll('#searchBrowse .search-tool-row'));
            if (e.key === 'ArrowDown') { e.preventDefault(); focusIdx = Math.min(focusIdx+1, items.length-1); updateFocus(items); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); focusIdx = Math.max(focusIdx-1, 0); updateFocus(items); }
            else if (e.key === 'Enter' && focusIdx >= 0) { e.preventDefault(); items[focusIdx] && items[focusIdx].click(); }
        });

        function updateFocus(items) {
            items.forEach(function(el,i){ el.classList.toggle('focused', i===focusIdx); });
            if (items[focusIdx]) items[focusIdx].scrollIntoView({ block:'nearest' });
            input && input.focus();
        }
    })();
    </script>

    @include('components.ads._manager')

    @stack('scripts')
</body>
</html>