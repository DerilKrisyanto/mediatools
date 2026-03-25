<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- ── SEO Core ── -->
    <title>@yield('title', 'MediaTools — Tools Digital Gratis untuk UMKM & Freelancer Indonesia')</title>
    <meta name="description"
          content="@yield('meta_description', 'Platform tools produktivitas digital gratis — Invoice Generator, PDF Converter, Background Remover, QR Code, dan 10+ tools lainnya. Tanpa daftar, langsung pakai.')">
    <meta name="keywords"
          content="@yield('meta_keywords', 'tools online gratis, konversi pdf, hapus background foto, invoice generator, qr code generator, indonesia')">
    <meta name="robots" content="index, follow">
    <meta name="author" content="MediaTools">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- ── Open Graph ── -->
    <meta property="og:type"        content="website">
    <meta property="og:site_name"   content="MediaTools">
    <meta property="og:title"       content="@yield('og_title', @yield('title', 'MediaTools'))">
    <meta property="og:description" content="@yield('og_description', @yield('meta_description', 'Tools digital gratis untuk semua kebutuhan produktivitas Anda.'))">
    <meta property="og:url"         content="{{ url()->current() }}">
    <meta property="og:image"       content="@yield('og_image', asset('images/og-default.jpg'))">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale"      content="id_ID">

    <!-- ── Twitter Card ── -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="@yield('og_title', @yield('title', 'MediaTools'))">
    <meta name="twitter:description" content="@yield('og_description', @yield('meta_description', 'Tools digital gratis.'))">
    <meta name="twitter:image"       content="@yield('og_image', asset('images/og-default.jpg'))">

    <!-- ── JSON-LD: WebSite + SearchAction ── -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": "MediaTools",
      "url": "https://mediatools.cloud",
      "description": "Platform tools produktivitas digital gratis untuk UMKM, freelancer, dan kreator Indonesia.",
      "inLanguage": "id",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "https://mediatools.cloud/?q={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    }
    </script>

    <!-- ── JSON-LD: Organization ── -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "MediaTools",
      "url": "https://mediatools.cloud",
      "logo": "{{ asset('images/icons-mediatools.png') }}",
      "contactPoint": {
        "@type": "ContactPoint",
        "email": "halo@mediatools.id",
        "contactType": "customer support",
        "availableLanguage": "Indonesian"
      },
      "sameAs": []
    }
    </script>

    <!-- ── Per-page JSON-LD (tools inject via @push) ── -->
    @stack('json_ld')

    <!-- ── Verification ── -->
    <meta name="google-site-verification" content="W4l-4NDtoXzK2oMrmBmFZ1Yj9Os9jK1bEqbUUmBJi5o">

    <!-- ── Fonts ── -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- ── Icons ── -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <!-- ── Tailwind ── -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- ── Favicon ── -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icons-mediatools.png') }}">

    <!-- ── CSS ── -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('styles')
</head>
<body class="antialiased">

    @include('components.navbar')

    <main>
        @yield('content')
    </main>

    @include('components.footer')

    <!-- Core JS -->
    <script src="{{ asset('js/app.js') }}"></script>

    <!-- Scroll Reveal & Misc -->
    <script>
    // Navbar scroll effect
    (function(){
        const nav = document.querySelector('.glass-nav');
        if(!nav) return;
        window.addEventListener('scroll', ()=>{
            nav.classList.toggle('scrolled', window.scrollY > 40);
        }, { passive: true });
    })();

    // Intersection Observer for .reveal elements
    (function(){
        const els = document.querySelectorAll('.reveal');
        if(!els.length) return;
        const io = new IntersectionObserver((entries)=>{
            entries.forEach(e=>{
                if(e.isIntersecting){
                    e.target.classList.add('visible');
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.12 });
        els.forEach(el => io.observe(el));
    })();

    // Animated counter
    function animateCounter(el){
        const target = parseFloat(el.dataset.target);
        const suffix = el.dataset.suffix || '';
        const prefix = el.dataset.prefix || '';
        const isDecimal = String(target).includes('.');
        const duration = 1800;
        const start = performance.now();
        function step(now){
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const value = eased * target;
            el.textContent = prefix + (isDecimal ? value.toFixed(1) : Math.floor(value)) + suffix;
            if(progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    (function(){
        const counters = document.querySelectorAll('[data-target]');
        if(!counters.length) return;
        const io = new IntersectionObserver((entries)=>{
            entries.forEach(e=>{
                if(e.isIntersecting){
                    animateCounter(e.target);
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(el => io.observe(el));
    })();

    // FAQ Accordion
    (function(){
        document.querySelectorAll('.faq-question').forEach(btn=>{
            btn.addEventListener('click', ()=>{
                const item = btn.closest('.faq-item');
                const body = item.querySelector('.faq-body');
                const isOpen = item.classList.contains('open');
                // close all
                document.querySelectorAll('.faq-item.open').forEach(i=>{
                    i.classList.remove('open');
                    i.querySelector('.faq-body').classList.remove('open');
                });
                if(!isOpen){
                    item.classList.add('open');
                    body.classList.add('open');
                }
            });
        });
    })();
    </script>

    @stack('scripts')
</body>
</html>