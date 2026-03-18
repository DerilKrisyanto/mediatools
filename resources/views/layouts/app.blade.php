<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MediaTools | All-in-One Media Suite')</title>
    <meta name="description" content="Satu platform untuk semua kebutuhan media digital Anda. Invoice, QR Code, Link Tree, Signature, dan banyak lagi.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'MediaTools — platform tools produktivitas digital gratis untuk UMKM, freelancer, dan developer Indonesia.')">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph untuk social sharing -->
    <meta property="og:title" content="@yield('title', 'MediaTools')">
    <meta property="og:description" content="@yield('meta_description', 'Tools produktivitas digital gratis')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom CSS -->
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