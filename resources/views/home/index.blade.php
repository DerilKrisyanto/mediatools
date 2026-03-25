@extends('layouts.app')
@section('title', 'MediaTools — Tools Digital Gratis: Invoice, PDF, Background Remover & QR Code')
@section('meta_description', 'Platform tools produktivitas digital 100% gratis — Hapus background foto, konversi PDF, buat invoice, QR Code, dan 10+ tools lainnya. Tanpa daftar, langsung pakai di browser.')
@section('meta_keywords', 'tools online gratis indonesia, invoice generator gratis, hapus background foto, konversi pdf word, qr code generator, media tools')
@section('og_image', asset('images/og-home.jpg'))

@section('title', 'MediaTools | Solusi Produktivitas Digital')

@section('content')

{{-- ================================================
    HERO SECTION
    ================================================ --}}
<section class="relative min-h-screen flex items-center pt-28 pb-20 px-6 overflow-hidden">

    {{-- Background decorations --}}
    <div class="hero-grid-bg"></div>
    <div class="blob" style="width:600px;height:600px;top:-100px;left:-200px;opacity:0.6;"></div>
    <div class="blob" style="width:400px;height:400px;bottom:0;right:-100px;opacity:0.4;"></div>

    <div class="max-w-7xl mx-auto w-full">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-16">

            {{-- Left: Text --}}
            <div class="lg:w-1/2 space-y-8 relative z-10">
                <div class="reveal">
                    <div class="hero-badge">
                        <span class="dot"></span>
                        All-in-One Media Suite
                    </div>
                </div>

                <div class="reveal reveal-delay-1">
                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-extrabold leading-[1.08] tracking-tight">
                        Optimalkan Kerja Anda Dengan
                        <span class="gradient-text"> Media Tools.</span>
                    </h1>
                    <h1>Optimalkan Kerja Anda Dengan <span class="gradient-text"> Media Tools.</span></h1>
                    <h1>Tools Digital Gratis untuk <span class="gradient-text">Produktivitas Anda.</span></h1>
                </div>

                <div class="reveal reveal-delay-2">
                    <p class="text-gray-400 text-lg md:text-xl leading-relaxed max-w-md">
                        Satu platform untuk semua kebutuhan produktivitas digital harian Anda — dari invoice profesional hingga QR Code bisnis, semuanya tersedia instan.
                    </p>

                    <p class="text-gray-500 text-sm leading-relaxed max-w-md mt-2">
                        Invoice Generator · PDF Converter · Background Remover · QR Code Generator ·
                        File Converter · Password Generator — semua gratis, langsung di browser.
                    </p>
                </div>

                <div class="reveal reveal-delay-3 flex flex-wrap gap-4">
                    <a href="#tools" class="btn-primary px-8 py-4 text-base">
                        <span>Jelajahi Alat</span>
                        <i class="fa-solid fa-arrow-right text-sm"></i>
                    </a>
                    <a href="#about" class="btn-outline px-8 py-4 text-base">
                        <i class="fa-solid fa-play text-xs"></i>
                        <span>Pelajari Fitur</span>
                    </a>
                </div>

                <div class="reveal reveal-delay-4 flex items-center gap-5 pt-2">
                    <div class="flex -space-x-3">
                        @foreach([1,2,3,4] as $i)
                        <img src="https://i.pravatar.cc/80?u={{ $i + 10 }}"
                             class="w-10 h-10 rounded-full border-2 border-[#040f0f] object-cover"
                             alt="pengguna">
                        @endforeach
                    </div>
                    <div>
                        <div class="flex gap-0.5 mb-1">
                            @for($i = 0; $i < 5; $i++)
                                <i class="fa-solid fa-star star text-xs"></i>
                            @endfor
                        </div>
                        <p class="text-sm text-gray-500">
                            Dipercaya <span class="text-white font-bold">10,000+</span> pengguna aktif
                        </p>
                    </div>
                </div>
            </div>

            {{-- Right: Visual --}}
            <div class="lg:w-1/2 relative flex justify-center items-center mt-8 lg:mt-0">
                {{-- Floating cards --}}
                <div class="hero-float-card" style="top:60px; left:-20px;">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-[#a3e635]/20 flex items-center justify-center">
                            <i class="fa-solid fa-file-invoice text-[#a3e635] text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white">Invoice Dibuat</p>
                            <p class="text-[11px] text-gray-400">2 menit yang lalu</p>
                        </div>
                        <span class="text-[#a3e635] text-xs font-bold ml-2">✓</span>
                    </div>
                </div>

                <div class="hero-float-card delay" style="bottom:80px; right:-10px;">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-[#a3e635]/20 flex items-center justify-center">
                            <i class="fa-solid fa-chart-line text-[#a3e635] text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white">Produktivitas</p>
                            <div class="flex items-center gap-1 mt-0.5">
                                <div class="h-1 w-20 bg-white/10 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#a3e635] rounded-full" style="width:82%"></div>
                                </div>
                                <span class="text-[#a3e635] text-[10px] font-bold">82%</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Main phone mockup --}}
                <div class="relative w-72 z-0">
                    <div class="hero-phone p-5">
                        {{-- Phone notch --}}
                        <div class="w-20 h-1.5 bg-white/10 rounded-full mx-auto mb-6"></div>

                        {{-- App header --}}
                        <div class="flex items-center justify-between mb-5">
                            <div>
                                <p class="text-[11px] text-gray-500 uppercase tracking-wider">Dashboard</p>
                                <p class="font-bold text-sm">MediaTools</p>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-[#a3e635]/20 flex items-center justify-center">
                                <i class="fa-solid fa-bell text-[#a3e635] text-xs"></i>
                            </div>
                        </div>

                        {{-- Stats row --}}
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="bg-[#a3e635]/10 rounded-2xl p-3">
                                <p class="text-[10px] text-gray-400 mb-1">Tools Digunakan</p>
                                <p class="text-xl font-extrabold text-[#a3e635]">50+</p>
                            </div>
                            <div class="bg-white/5 rounded-2xl p-3">
                                <p class="text-[10px] text-gray-400 mb-1">File Terproses</p>
                                <p class="text-xl font-extrabold">1M+</p>
                            </div>
                        </div>

                        {{-- Tool list --}}
                        <div class="space-y-2">
                            @foreach([
                                ['fa-file-invoice','Invoice Generator','Buat tagihan'],
                                ['fa-qrcode','QR Business Kit','Generate QR'],
                                ['fa-link','Link Tree','Satu halaman'],
                            ] as $tool)
                            <div class="flex items-center gap-3 p-2.5 rounded-xl bg-white/[0.03] border border-white/5">
                                <div class="w-8 h-8 rounded-lg bg-[#a3e635]/20 flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid {{ $tool[0] }} text-[#a3e635] text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold truncate">{{ $tool[1] }}</p>
                                    <p class="text-[10px] text-gray-500">{{ $tool[2] }}</p>
                                </div>
                                <i class="fa-solid fa-chevron-right text-gray-600 text-[10px]"></i>
                            </div>
                            @endforeach
                        </div>

                        {{-- Bottom action --}}
                        <div class="mt-4 bg-[#a3e635] rounded-2xl py-3 text-center">
                            <p class="text-[#040f0f] font-bold text-sm">Mulai Sekarang</p>
                        </div>
                    </div>

                    {{-- Glow behind phone --}}
                    <div class="absolute inset-0 bg-[#a3e635]/5 rounded-[2.5rem] blur-3xl -z-10 scale-110"></div>
                </div>
            </div>

        </div>
    </div>
</section>


{{-- ================================================
    STATS SECTION
    ================================================ --}}
<div class="stats-wrap">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4">
            <div class="stat-item reveal">
                <div class="stat-number" data-target="50" data-suffix="+">0+</div>
                <div class="stat-label">Media Tools</div>
            </div>
            <div class="stat-item reveal reveal-delay-1">
                <div class="stat-number" data-target="1" data-suffix="M+" data-prefix="">0</div>
                <div class="stat-label">File Terproses</div>
            </div>
            <div class="stat-item reveal reveal-delay-2">
                <div class="stat-number" data-target="99.9" data-suffix="%">0%</div>
                <div class="stat-label">Uptime Server</div>
            </div>
            <div class="stat-item reveal reveal-delay-3">
                <div class="stat-number" data-target="4.9" data-suffix="/5">0/5</div>
                <div class="stat-label">Rating Kepuasan</div>
            </div>
        </div>
    </div>
</div>


{{-- ================================================
    TOOLS GRID
    ================================================ --}}
<section id="tools" class="py-28 px-6">
    <div class="max-w-7xl mx-auto">

        <div class="text-center mb-16 space-y-4">
            <div class="flex justify-center reveal">
                <div class="section-label"><i class="fa-solid fa-toolbox"></i> Koleksi Alat</div>
            </div>
            <h2 class="text-4xl md:text-5xl font-extrabold tracking-tight reveal reveal-delay-1">
                Semua yang Anda Butuhkan,<br>
                <span class="gradient-text">Dalam Satu Tempat</span>
            </h2>
            <p class="text-gray-400 max-w-xl mx-auto text-lg reveal reveal-delay-2">
                Pilih alat yang tepat untuk meningkatkan profesionalitas dan efisiensi kerja Anda secara instan.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
            $tools = [
                [
                    'icon'  => 'fa-file-invoice',
                    'title' => 'Invoice Generator',
                    'desc'  => 'Buat tagihan profesional dengan template yang dapat dikustomisasi penuh dalam hitungan detik.',
                    'badge' => null,
                    'href'  => 'invoice',
                ],
                [
                    'icon'  => 'fa-link',
                    'title' => 'Link Tree One-Page',
                    'desc'  => 'Satukan semua link media sosial dan portofolio Anda dalam satu halaman arahan yang elegan.',
                    'badge' => 'Populer',
                    'href'  => 'linktree',
                ],
                [
                    'icon'  => 'fa-qrcode',
                    'title' => 'QR Business Kit',
                    'desc'  => 'Buat QR Code untuk menu, pembayaran, atau kontak bisnis dengan desain yang modern dan branded.',
                    'badge' => 'Baru',
                    'href'  => 'qr',
                ],
                [
                    'icon'  => 'fa-signature',
                    'title' => 'Email Signature',
                    'desc'  => 'Desain tanda tangan email yang impresif untuk membangun kesan profesional di setiap komunikasi.',
                    'badge' => null,
                    'href'  => 'signature',
                ],
                [
                    'icon'  => 'fa-id-card',
                    'title' => 'Digital Business Card',
                    'desc'  => 'Kartu nama digital yang bisa dibagikan lewat link atau QR Code, tanpa perlu cetak fisik.',
                    'badge' => null,
                    'href'  => '#',
                ],
                [
                    'icon'  => 'fa-image',
                    'title' => 'Image Converter',
                    'desc'  => 'Konversi format gambar, kompres ukuran file, dan resize resolusi langsung di browser Anda.',
                    'badge' => null,
                    'href'  => '#',
                ],
                [
                    'icon'  => 'fa-file-pdf',
                    'title' => 'PDF Toolkit',
                    'desc'  => 'Merge, split, compress, atau konversi PDF ke Word/Excel dengan mudah dan aman.',
                    'badge' => 'Pro',
                    'href'  => '#',
                ],
                [
                    'icon'  => 'fa-chart-bar',
                    'title' => 'Proposal Builder',
                    'desc'  => 'Buat proposal bisnis dan presentasi klien yang terstruktur rapi dengan template siap pakai.',
                    'badge' => 'Pro',
                    'href'  => '#',
                ],
            ];
            @endphp

            @foreach($tools as $i => $tool)
            <div class="tool-card p-7 group reveal" style="transition-delay: {{ ($i % 4) * 0.08 }}s">
                <div class="flex items-start justify-between mb-6">
                    <div class="tool-icon-wrap">
                        <i class="fa-solid {{ $tool['icon'] }}"></i>
                    </div>
                    @if($tool['badge'])
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full
                        {{ $tool['badge'] === 'Pro' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' :
                           ($tool['badge'] === 'Baru' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' :
                           'bg-[#a3e635]/10 text-[#a3e635] border border-[#a3e635]/20') }}">
                        {{ $tool['badge'] }}
                    </span>
                    @endif
                </div>
                <h3 class="text-lg font-bold mb-2 leading-snug">{{ $tool['title'] }}</h3>
                <p class="text-gray-400 text-sm leading-relaxed mb-6 flex-1">{{ $tool['desc'] }}</p>
                <a href="{{ $tool['href'] }}" class="tool-arrow">
                    <span>Coba Alat</span>
                    <i class="fa-solid fa-arrow-right-long text-xs"></i>
                </a>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-12 reveal">
            <a href="#" class="btn-outline px-8 py-3.5 text-sm">
                <span>Lihat Semua Tools</span>
                <i class="fa-solid fa-grid-2 text-xs"></i>
            </a>
        </div>
    </div>
</section>


{{-- ================================================
    ABOUT / WHY US
    ================================================ --}}
<section id="about" class="py-28 px-6 relative overflow-hidden">
    <div class="blob" style="width:500px;height:500px;top:0;right:-150px;opacity:0.4;"></div>

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col lg:flex-row items-center gap-20">

            {{-- Left: Features grid --}}
            <div class="lg:w-1/2">
                <div class="flex justify-start mb-6 reveal">
                    <div class="section-label"><i class="fa-solid fa-star"></i> Mengapa Kami</div>
                </div>
                <h2 class="text-4xl md:text-5xl font-extrabold leading-tight mb-4 reveal reveal-delay-1">
                    Membantu Anda<br>Fokus pada <span class="gradient-text">Karya.</span>
                </h2>
                <p class="text-gray-400 mb-10 text-lg leading-relaxed reveal reveal-delay-2">
                    MediaTools lahir dari kegelisahan akan sulitnya akses alat produktivitas yang simpel, cepat, dan terintegrasi. Kami percaya teknologi harusnya memudahkan, bukan membingungkan.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach([
                        ['fa-bolt','Cepat & Instan','Proses file dalam hitungan detik, tanpa loading lama.'],
                        ['fa-mobile-screen','Responsif Penuh','Gunakan di desktop maupun smartphone dengan mulus.'],
                        ['fa-cloud-arrow-up','Tanpa Instalasi','Semua berbasis web. Tidak perlu mengunduh apapun.'],
                        ['fa-shield-halved','Privasi Aman','File otomatis terhapus dalam 24 jam setelah diproses.'],
                    ] as $i => $feat)
                    <div class="feature-card reveal" style="transition-delay:{{ $i * 0.1 }}s">
                        <div class="w-10 h-10 rounded-xl bg-[#a3e635]/10 flex items-center justify-center mb-4">
                            <i class="fa-solid {{ $feat[0] }} text-[#a3e635]"></i>
                        </div>
                        <h4 class="font-bold mb-1.5">{{ $feat[1] }}</h4>
                        <p class="text-gray-400 text-sm leading-relaxed">{{ $feat[2] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Right: How it works --}}
            <div class="lg:w-1/2 space-y-0 reveal reveal-delay-2">
                <div class="bg-[#0b2323] border border-white/5 rounded-[2rem] p-8 md:p-10">
                    <h3 class="text-2xl font-bold mb-8">Cara Penggunaan</h3>

                    <div class="space-y-0">
                        @foreach([
                            ['Pilih Alat Media','Temukan alat yang sesuai dari katalog 50+ tools kami yang terus berkembang.'],
                            ['Input Data Anda','Isi formulir sederhana atau unggah file yang ingin Anda proses.'],
                            ['Preview & Sesuaikan','Lihat hasilnya secara real-time dan sesuaikan sesuai kebutuhan.'],
                            ['Download & Gunakan','Unduh hasil kerja Anda dalam format yang siap pakai langsung.'],
                        ] as $i => $step)
                        <div class="flex gap-5 step-item {{ !$loop->last ? 'mb-0' : '' }}">
                            <div class="flex flex-col items-center">
                                <div class="step-number">{{ $i + 1 }}</div>
                                @if(!$loop->last)
                                <div class="step-connector"></div>
                                @endif
                            </div>
                            <div class="pb-8 {{ $loop->last ? 'pb-0' : '' }} pt-2">
                                <h5 class="font-bold mb-1">{{ $step[0] }}</h5>
                                <p class="text-gray-400 text-sm leading-relaxed">{{ $step[1] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>


{{-- ================================================
    TESTIMONIALS
    ================================================ --}}
<section class="py-28 px-6 relative overflow-hidden" style="background: rgba(255,255,255,0.01);">
    <div class="blob" style="width:400px;height:400px;bottom:-100px;left:-100px;opacity:0.35;"></div>

    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
            <div class="flex justify-center mb-4 reveal">
                <div class="section-label"><i class="fa-solid fa-comments"></i> Ulasan Pengguna</div>
            </div>
            <h2 class="text-4xl md:text-5xl font-extrabold tracking-tight reveal reveal-delay-1">
                Dipercaya oleh <span class="gradient-text">Ribuan Pengguna</span>
            </h2>
            <p class="text-gray-400 mt-4 text-lg reveal reveal-delay-2">Inilah yang mereka katakan tentang pengalaman menggunakan MediaTools.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @php
            $testimonials = [
                [
                    'name'   => 'Aditya Pratama',
                    'role'   => 'Freelance Designer',
                    'avatar' => 'https://i.pravatar.cc/100?u=aditya',
                    'rating' => 5,
                    'text'   => 'Invoice Generator-nya luar biasa! Dalam 5 menit saya sudah bisa kirim tagihan profesional ke klien. Tampilannya bersih dan bisa dikustomisasi sesuai brand saya.',
                ],
                [
                    'name'   => 'Sinta Rahayu',
                    'role'   => 'Content Creator',
                    'avatar' => 'https://i.pravatar.cc/100?u=sinta',
                    'rating' => 5,
                    'text'   => 'Link Tree-nya jauh lebih keren dari aplikasi serupa. Saya bisa setting tampilan sesuai estetika feed Instagram saya. Followers pun jadi lebih mudah menemukan semua konten saya.',
                ],
                [
                    'name'   => 'Budi Santoso',
                    'role'   => 'Pemilik UMKM',
                    'avatar' => 'https://i.pravatar.cc/100?u=budi',
                    'rating' => 5,
                    'text'   => 'QR Code untuk menu restoran saya terlihat sangat profesional. Pelanggan sering memuji betapa modernnya usaha saya. Padahal bikinnya cuma hitungan menit!',
                ],
            ];
            @endphp

            @foreach($testimonials as $i => $t)
            <div class="testimonial-card reveal" style="transition-delay:{{ $i * 0.12 }}s">
                <div class="quote-icon">"</div>
                <div class="flex gap-1 mb-4">
                    @for($s = 0; $s < $t['rating']; $s++)
                        <i class="fa-solid fa-star star"></i>
                    @endfor
                </div>
                <p class="text-gray-300 text-sm leading-relaxed mb-6">{{ $t['text'] }}</p>
                <div class="flex items-center gap-3">
                    <img src="{{ $t['avatar'] }}" alt="{{ $t['name'] }}"
                         class="w-11 h-11 rounded-full object-cover border-2 border-[#a3e635]/20">
                    <div>
                        <p class="font-bold text-sm">{{ $t['name'] }}</p>
                        <p class="text-gray-500 text-xs">{{ $t['role'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ================================================
    CONTACT SECTION
    ================================================ --}}
<section id="contact" class="py-28 px-6 relative overflow-hidden">
    <div class="blob" style="width:500px;height:500px;top:-100px;right:-150px;opacity:0.3;"></div>

    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-14">
            <div class="flex justify-center mb-4 reveal">
                <div class="section-label"><i class="fa-solid fa-envelope"></i> Kontak</div>
            </div>
            <h2 class="text-4xl md:text-5xl font-extrabold tracking-tight reveal reveal-delay-1">
                Ada <span class="gradient-text">Pertanyaan?</span>
            </h2>
            <p class="text-gray-400 mt-4 text-lg reveal reveal-delay-2">Tim kami siap membantu Anda kapan saja.</p>
        </div>

        <div class="bg-[#0b2323] rounded-[2.5rem] border border-white/5 overflow-hidden shadow-2xl reveal">
            <div class="flex flex-col md:flex-row">

                {{-- Info Side --}}
                <div class="md:w-2/5 p-10 lg:p-12 bg-gradient-to-br from-[#a3e635]/8 to-transparent space-y-10 border-b md:border-b-0 md:border-r border-white/5">
                    <div>
                        <h3 class="text-2xl font-bold mb-3">Hubungi Kami</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">Punya pertanyaan, masukan, atau ingin berkolaborasi? Kami sangat senang mendengar dari Anda.</p>
                    </div>

                    <div class="space-y-5">
                        @foreach([
                            ['fa-envelope','Email Support','halo@mediatools.id'],
                            ['fa-location-dot','Kantor Pusat','Jakarta, Indonesia'],
                            ['fa-comments','Live Chat','Senin – Jumat (09.00–17.00 WIB)'],
                        ] as $info)
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-white/5 rounded-2xl flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid {{ $info[0] }} accent-text"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider mb-0.5">{{ $info[1] }}</p>
                                <p class="font-semibold text-sm">{{ $info[2] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="pt-4">
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-4">Ikuti Kami</p>
                        <div class="flex gap-3">
                            @foreach([
                                ['fa-instagram','#'],
                                ['fa-twitter','#'],
                                ['fa-linkedin-in','#'],
                                ['fa-tiktok','#'],
                            ] as $soc)
                            <a href="{{ $soc[1] }}"
                               class="w-10 h-10 bg-white/5 rounded-xl flex items-center justify-center text-gray-400 hover:bg-[#a3e635]/20 hover:text-[#a3e635] transition-all duration-300 hover:scale-110">
                                <i class="fa-brands {{ $soc[0] }} text-sm"></i>
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Form Side --}}
                <div class="md:w-3/5 p-10 lg:p-12">
                    <form class="space-y-6" action="#" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="form-group">
                                <label for="name">Nama Lengkap</label>
                                <input id="name" type="text" name="name"
                                       class="form-input" placeholder="Budi Santoso">
                            </div>
                            <div class="form-group">
                                <label for="email">Alamat Email</label>
                                <input id="email" type="email" name="email"
                                       class="form-input" placeholder="budi@email.com">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subjek</label>
                            <select id="subject" name="subject" class="form-input">
                                <option value="">Pilih topik...</option>
                                <option>Pertanyaan Umum</option>
                                <option>Masalah Teknis</option>
                                <option>Kerjasama Bisnis</option>
                                <option>Saran & Masukan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="message">Pesan Anda</label>
                            <textarea id="message" name="message" rows="4"
                                      class="form-input resize-none"
                                      placeholder="Ceritakan apa yang ingin Anda sampaikan..."></textarea>
                        </div>
                        <button type="submit" class="btn-primary w-full py-4 text-base">
                            <i class="fa-solid fa-paper-plane text-sm"></i>
                            <span>Kirim Pesan Sekarang</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</section>


{{-- ================================================
    FAQ SECTION
    ================================================ --}}
<section class="py-28 px-6" style="background: rgba(255,255,255,0.01);">
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-14">
            <div class="flex justify-center mb-4 reveal">
                <div class="section-label"><i class="fa-solid fa-circle-question"></i> FAQ</div>
            </div>
            <h2 class="text-4xl md:text-5xl font-extrabold tracking-tight reveal reveal-delay-1">
                Pertanyaan <span class="gradient-text">Populer</span>
            </h2>
            <p class="text-gray-400 mt-4 text-lg reveal reveal-delay-2">Semua hal yang perlu Anda ketahui tentang MediaTools.</p>
        </div>

        <div class="space-y-3 reveal reveal-delay-2">
            @foreach([
                [
                    'q' => 'Apakah semua alat ini benar-benar gratis?',
                    'a' => 'Ya, fitur dasar seluruh alat kami dapat digunakan secara gratis tanpa perlu kartu kredit. Kami juga menyediakan paket Pro untuk akses fitur premium, template eksklusif, dan penggunaan tanpa batas harian.',
                ],
                [
                    'q' => 'Bagaimana keamanan data yang saya upload?',
                    'a' => 'Privasi Anda adalah prioritas utama kami. Semua file yang diunggah akan diproses secara lokal atau di server terenkripsi kami, dan akan dihapus otomatis dalam 24 jam. Kami tidak pernah menyimpan atau menjual data Anda.',
                ],
                [
                    'q' => 'Apakah saya perlu membuat akun untuk menggunakan tools?',
                    'a' => 'Tidak, Anda bisa langsung menggunakan sebagian besar tools tanpa mendaftar. Namun, dengan membuat akun gratis, Anda dapat menyimpan histori pekerjaan, mengakses template tersimpan, dan mendapatkan fitur kolaborasi tim.',
                ],
                [
                    'q' => 'Apakah MediaTools bekerja di smartphone?',
                    'a' => 'Tentu! Semua tools kami didesain secara mobile-first dan responsif penuh. Anda bisa menggunakannya langsung dari browser smartphone tanpa perlu mengunduh aplikasi apapun.',
                ],
                [
                    'q' => 'Bagaimana cara upgrade ke paket Pro?',
                    'a' => 'Klik tombol "Upgrade ke Pro" di dashboard Anda. Kami menerima pembayaran via kartu kredit/debit, transfer bank, dan dompet digital populer. Langganan dapat dibatalkan kapan saja.',
                ],
            ] as $i => $faq)
            <div class="faq-item {{ $i === 0 ? 'open' : '' }}">
                <div class="faq-question">
                    <span>{{ $faq['q'] }}</span>
                    <div class="faq-icon"><i class="fa-solid fa-plus"></i></div>
                </div>
                <div class="faq-body {{ $i === 0 ? 'open' : '' }}">
                    <div class="faq-body-inner">{{ $faq['a'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ================================================
    CTA SECTION
    ================================================ --}}
<section class="py-20 px-6 pb-28">
    <div class="max-w-5xl mx-auto reveal">
        <div class="cta-card p-12 md:p-20 text-center">
            {{-- Decorative blobs --}}
            <div class="blob" style="width:400px;height:400px;top:-50px;left:-100px;opacity:0.25;z-index:1;"></div>
            <div class="blob" style="width:300px;height:300px;bottom:-50px;right:-80px;opacity:0.2;z-index:1;"></div>

            <div class="relative z-10 space-y-8">
                <div class="flex justify-center">
                    <div class="section-label"><i class="fa-solid fa-rocket"></i> Mulai Sekarang</div>
                </div>
                <h2 class="text-4xl md:text-6xl font-extrabold leading-tight tracking-tight">
                    Siap Mengubah<br>
                    <span class="gradient-text">Cara Anda Bekerja?</span>
                </h2>
                <p class="text-gray-400 max-w-lg mx-auto text-lg leading-relaxed">
                    Bergabunglah dengan lebih dari 10.000 pengguna dan profesional yang telah merasakan manfaat MediaTools. Gratis, tanpa kartu kredit.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" class="btn-primary px-10 py-4 text-base">
                        <i class="fa-solid fa-bolt"></i>
                        <span>Daftar Gratis Sekarang</span>
                    </a>
                    <a href="#tools" class="btn-outline px-10 py-4 text-base">
                        <span>Lihat Semua Tools</span>
                        <i class="fa-solid fa-arrow-right text-sm"></i>
                    </a>
                </div>
                <p class="text-gray-600 text-sm">Tidak perlu kartu kredit · Batal kapan saja · Akses instan</p>
            </div>
        </div>
    </div>
</section>

@endsection