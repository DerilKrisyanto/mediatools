{{-- ============================================================
     MEDIATOOLS — NAVBAR
     resources/views/components/navbar.blade.php
     ============================================================ --}}

@php
/**
 * Kategori Tools — 4 kelompok logis yang mudah dipahami orang awam:
 *
 * 1. BUAT & KELOLA  → Invoice, Email Signature, QR Code, Link Tree
 *    (membuat sesuatu dari nol untuk keperluan bisnis & personal)
 *
 * 2. FILE & DOKUMEN → PDF Toolkit, File Converter, Image Converter
 *    (mengolah file yang sudah ada)
 *
 * 3. KONTEN & MEDIA → Background Remover, Media Downloader
 *    (bekerja dengan gambar & video)
 *
 * 4. KEAMANAN       → Password Generator
 *    (alat pelindung akun)
 */
$navCategories = [
    [
        'key'     => 'create',
        'label'   => 'Buat & Kelola',
        'icon'    => 'fa-wand-magic-sparkles',
        'color'   => 'lime',          // accent utama
        'desc'    => 'Buat dokumen, link, dan kode bisnis',
        'tools'   => [
            [
                'route'  => 'tools.invoice',
                'icon'   => 'fa-file-invoice-dollar',
                'name'   => 'Invoice Generator',
                'desc'   => 'Tagihan profesional, download PDF',
                'badge'  => null,
                'tags'   => ['invoice','tagihan','billing','faktur','kwitansi'],
            ],
            [
                'route'  => 'tools.signature',
                'icon'   => 'fa-signature',
                'name'   => 'Email Signature',
                'desc'   => 'Tanda tangan email & dokumen',
                'badge'  => null,
                'tags'   => ['signature','tanda tangan','email','branding'],
            ],
            [
                'route'  => 'tools.qr',
                'icon'   => 'fa-qrcode',
                'name'   => 'QR Code Generator',
                'desc'   => 'QR menu, WiFi, kontak, URL',
                'badge'  => 'Baru',
                'tags'   => ['qr','qrcode','barcode','scan','menu','wifi'],
            ],
            [
                'route'  => 'tools.linktree',
                'icon'   => 'fa-link',
                'name'   => 'Link Tree',
                'desc'   => 'Satu link untuk semua sosmedmu',
                'badge'  => 'Populer',
                'tags'   => ['link','linktree','bio','sosial','instagram','tiktok'],
            ],
        ],
    ],
    [
        'key'     => 'files',
        'label'   => 'File & Dokumen',
        'icon'    => 'fa-folder-open',
        'color'   => 'blue',
        'desc'    => 'Konversi, edit, dan kelola file',
        'tools'   => [
            [
                'route'  => 'tools.pdfutilities',
                'icon'   => 'fa-file-pdf',
                'name'   => 'PDF Toolkit',
                'desc'   => 'Merge, split, compress PDF',
                'badge'  => null,
                'tags'   => ['pdf','merge','split','compress','gabung','pisah'],
            ],
            [
                'route'  => 'tools.fileconverter',
                'icon'   => 'fa-rotate',
                'name'   => 'File Converter',
                'desc'   => 'PDF ↔ Word · Excel · PPT · JPG',
                'badge'  => null,
                'tags'   => ['pdf','word','excel','pptx','convert','konversi','jpg'],
            ],
            [
                'route'  => 'tools.imageconverter',
                'icon'   => 'fa-images',
                'name'   => 'Image Converter',
                'desc'   => 'Resize, kompres & ubah format foto',
                'badge'  => null,
                'tags'   => ['gambar','image','foto','resize','compress','webp','png','jpg'],
            ],
        ],
    ],
    [
        'key'     => 'media',
        'label'   => 'Konten & Media',
        'icon'    => 'fa-photo-film',
        'color'   => 'purple',
        'desc'    => 'Edit foto dan unduh konten',
        'tools'   => [
            [
                'route'  => 'tools.bgremover',
                'icon'   => 'fa-wand-sparkles',
                'name'   => 'Background Remover',
                'desc'   => 'Hapus background foto dengan AI',
                'badge'  => 'AI',
                'tags'   => ['background','hapus','foto','remover','transparent','ai'],
            ],
            [
                'route'  => 'tools.mediadownloader',
                'icon'   => 'fa-circle-down',
                'name'   => 'Media Downloader',
                'desc'   => 'Download YouTube, TikTok & IG',
                'badge'  => null,
                'tags'   => ['download','youtube','tiktok','instagram','video','mp3','mp4','reels'],
            ],
        ],
    ],
    [
        'key'     => 'security',
        'label'   => 'Keamanan',
        'icon'    => 'fa-shield-halved',
        'color'   => 'green',
        'desc'    => 'Lindungi akun dan data Anda',
        'tools'   => [
            [
                'route'  => 'tools.passwordgenerator',
                'icon'   => 'fa-key',
                'name'   => 'Password Generator',
                'desc'   => 'Buat kata sandi kuat & aman',
                'badge'  => null,
                'tags'   => ['password','kata sandi','keamanan','security','pin'],
            ],
        ],
    ],
];

// Warna per kategori (badge + icon)
$colorMap = [
    'lime'   => ['bg' => 'rgba(163,230,53,0.12)',  'text' => '#a3e635', 'badge' => 'rgba(163,230,53,0.15)'],
    'blue'   => ['bg' => 'rgba(59,130,246,0.12)',  'text' => '#60a5fa', 'badge' => 'rgba(59,130,246,0.15)'],
    'purple' => ['bg' => 'rgba(139,92,246,0.12)',  'text' => '#a78bfa', 'badge' => 'rgba(139,92,246,0.15)'],
    'green'  => ['bg' => 'rgba(34,197,94,0.12)',   'text' => '#4ade80', 'badge' => 'rgba(34,197,94,0.15)'],
];
@endphp

<nav class="fixed w-full z-50 glass-nav" id="mainNav">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-[68px] flex items-center justify-between">

        {{-- ── LOGO ── --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 group flex-shrink-0">
            <div class="w-9 h-9 flex items-center justify-center transition-transform duration-300 group-hover:scale-110">
                <img src="{{ asset('images/icons-mediatools.png') }}"
                     alt="MediaTools Logo"
                     class="w-full h-full object-contain">
            </div>
            <span class="text-[17px] font-extrabold tracking-tight text-white leading-none">
                MEDIA<span style="color:var(--accent)">TOOLS.</span>
            </span>
        </a>

        {{-- ── DESKTOP MENU ── --}}
        <div class="hidden lg:flex items-center gap-1">

            <a href="{{ route('home') }}"
               class="nav-link px-3 py-2 rounded-lg hover:bg-white/5 transition-colors">
               Beranda
            </a>

            {{-- ── MEGA DROPDOWN ── --}}
            <div class="relative" id="toolsMenu">
                <button id="toolsBtn" onclick="toggleToolsMenu()"
                        class="nav-link flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-white/5 transition-colors"
                        aria-haspopup="true" aria-expanded="false">
                    <span>Semua Tools</span>
                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-300" id="toolsArrow"></i>
                </button>

                {{-- MEGA MENU PANEL --}}
                <div id="toolsDropdown" class="nav-mega-menu" role="menu">
                    <div class="nav-mega-inner">

                        {{-- Header --}}
                        <div class="nav-mega-header">
                            <p class="nav-mega-title">Pilih Tools yang Kamu Butuhkan</p>
                            <a href="{{ route('home') }}#tools" class="nav-mega-see-all" onclick="closeDropdown()">
                                Lihat semua <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>

                        {{-- 4-Column Grid --}}
                        <div class="nav-mega-grid">
                            @foreach($navCategories as $cat)
                            @php $c = $colorMap[$cat['color']]; @endphp
                            <div class="nav-mega-col">
                                {{-- Category Header --}}
                                <div class="nav-cat-head">
                                    <div class="nav-cat-icon" style="background:{{ $c['bg'] }}; color:{{ $c['text'] }};">
                                        <i class="fa-solid {{ $cat['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <p class="nav-cat-name">{{ $cat['label'] }}</p>
                                        <p class="nav-cat-desc">{{ $cat['desc'] }}</p>
                                    </div>
                                </div>

                                {{-- Tools List --}}
                                <div class="nav-cat-tools">
                                    @foreach($cat['tools'] as $tool)
                                    <a href="{{ route($tool['route']) }}"
                                       class="nav-tool-item"
                                       role="menuitem"
                                       onclick="closeDropdown()">
                                        <div class="nav-tool-icon" style="background:{{ $c['bg'] }}; color:{{ $c['text'] }};">
                                            <i class="fa-solid {{ $tool['icon'] }}"></i>
                                        </div>
                                        <div class="nav-tool-text">
                                            <span class="nav-tool-name">{{ $tool['name'] }}</span>
                                            <span class="nav-tool-desc">{{ $tool['desc'] }}</span>
                                        </div>
                                        @if($tool['badge'])
                                        <span class="nav-tool-badge"
                                              style="background:{{ $c['badge'] }}; color:{{ $c['text'] }};">
                                            {{ $tool['badge'] }}
                                        </span>
                                        @endif
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Footer Bar --}}
                        <div class="nav-mega-footer">
                            <button onclick="openSearch(); closeDropdown();" class="nav-mega-search-btn">
                                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                                <span>Cari tools spesifik...</span>
                                <kbd>⌘K</kbd>
                            </button>
                            <div class="nav-mega-stats">
                                <span><i class="fa-solid fa-bolt text-[#a3e635] text-[10px]"></i> 10 Tools Aktif</span>
                                <span><i class="fa-solid fa-shield-halved text-[#a3e635] text-[10px]"></i> 100% Gratis</span>
                                <span><i class="fa-solid fa-lock text-[#a3e635] text-[10px]"></i> Privasi Terjaga</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>{{-- /toolsMenu --}}

            <a href="{{ route('home') }}#about"
               class="nav-link px-3 py-2 rounded-lg hover:bg-white/5 transition-colors">
               Tentang
            </a>
            <a href="{{ route('home') }}#contact"
               class="nav-link px-3 py-2 rounded-lg hover:bg-white/5 transition-colors">
               Kontak
            </a>
        </div>

        {{-- ── RIGHT: SEARCH + AUTH ── --}}
        <div class="hidden lg:flex items-center gap-2">

            <button onclick="openSearch()"
                    class="nav-search-pill">
                <i class="fa-solid fa-magnifying-glass text-xs text-gray-500"></i>
                <span>Cari tools...</span>
                <kbd>⌘K</kbd>
            </button>

            @guest
                <a href="{{ route('login') }}"
                   class="nav-link px-4 py-2 rounded-xl hover:bg-white/5 transition-colors text-sm">
                   Masuk
                </a>
                <a href="{{ route('register') }}"
                   class="btn-primary px-5 py-2.5 text-sm rounded-xl font-bold">
                    <span>Daftar Gratis</span>
                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                </a>
            @endguest

            @auth
                <div class="flex items-center gap-2 pl-3 border-l" style="border-color:var(--border)">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0"
                         style="background:var(--accent-dim); color:var(--accent);">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <span class="text-sm font-semibold text-white">
                        {{ Str::limit(Auth::user()->name, 12) }}
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 text-sm font-semibold text-gray-500
                                   hover:text-red-400 transition-colors px-3 py-2 rounded-xl
                                   hover:bg-red-500/10">
                        <i class="fa-solid fa-right-from-bracket text-xs"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            @endauth
        </div>

        {{-- ── MOBILE: ICONS ── --}}
        <div class="lg:hidden flex items-center gap-2">
            <button onclick="openSearch()"
                    class="w-10 h-10 rounded-xl flex items-center justify-center text-gray-400
                           hover:text-white hover:bg-white/8 transition-colors"
                    style="background:rgba(255,255,255,0.04); border:1px solid var(--border);">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </button>
            <button id="mobileMenuBtn" onclick="toggleMobileMenu()"
                    class="w-10 h-10 rounded-xl flex items-center justify-center text-white
                           hover:bg-white/10 transition-colors"
                    style="background:rgba(255,255,255,0.04); border:1px solid var(--border);"
                    aria-label="Toggle menu">
                <i class="fa-solid fa-bars text-sm" id="menuIcon"></i>
            </button>
        </div>
    </div>

    {{-- ── MOBILE MENU ── --}}
    <div id="mobileMenu" class="mobile-menu hidden" aria-hidden="true">

        {{-- Search --}}
        <button onclick="openSearch(); toggleMobileMenu();"
                class="mobile-menu-link w-full text-left">
            <div class="mobile-menu-icon" style="background:rgba(255,255,255,0.05);">
                <i class="fa-solid fa-magnifying-glass text-gray-400 text-xs"></i>
            </div>
            <span>Cari Tools...</span>
            <kbd class="ml-auto text-[10px] text-gray-600 px-1.5 py-0.5 rounded"
                 style="background:rgba(255,255,255,0.05);">⌘K</kbd>
        </button>

        <a href="{{ route('home') }}" class="mobile-menu-link">
            <div class="mobile-menu-icon" style="background:rgba(255,255,255,0.05);">
                <i class="fa-solid fa-house text-gray-400 text-xs"></i>
            </div>
            <span>Beranda</span>
        </a>

        {{-- Tools Accordion --}}
        <div class="mobile-acc-wrap">
            <button onclick="toggleMobileTools()"
                    class="mobile-acc-btn">
                <div class="flex items-center gap-3">
                    <div class="mobile-menu-icon" style="background:var(--accent-dim);">
                        <i class="fa-solid fa-toolbox text-xs" style="color:var(--accent)"></i>
                    </div>
                    <span>Semua Tools</span>
                </div>
                <div class="mobile-acc-arrow">
                    <i class="fa-solid fa-chevron-down text-[11px] transition-transform duration-300" id="mobileToolsArrow"></i>
                </div>
            </button>

            {{-- Mobile Tools — grouped --}}
            <div id="mobileToolsMenu" class="mobile-acc-body hidden">
                @foreach($navCategories as $cat)
                @php $c = $colorMap[$cat['color']]; @endphp
                <div class="mobile-cat-group">
                    <p class="mobile-cat-label">
                        <i class="fa-solid {{ $cat['icon'] }}" style="color:{{ $c['text'] }};"></i>
                        {{ $cat['label'] }}
                    </p>
                    @foreach($cat['tools'] as $tool)
                    <a href="{{ route($tool['route']) }}"
                       class="mobile-tool-link">
                        <div class="mobile-tool-icon" style="background:{{ $c['bg'] }}; color:{{ $c['text'] }};">
                            <i class="fa-solid {{ $tool['icon'] }} text-xs"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white leading-none">{{ $tool['name'] }}</p>
                            <p class="text-[11px] text-gray-500 mt-0.5">{{ $tool['desc'] }}</p>
                        </div>
                        @if($tool['badge'])
                        <span class="mobile-tool-badge"
                              style="background:{{ $c['badge'] }}; color:{{ $c['text'] }};">
                            {{ $tool['badge'] }}
                        </span>
                        @endif
                    </a>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>{{-- /tools accordion --}}

        <a href="{{ route('home') }}#about" class="mobile-menu-link">
            <div class="mobile-menu-icon" style="background:rgba(255,255,255,0.05);">
                <i class="fa-solid fa-circle-info text-gray-400 text-xs"></i>
            </div>
            <span>Tentang Kami</span>
        </a>
        <a href="{{ route('home') }}#contact" class="mobile-menu-link">
            <div class="mobile-menu-icon" style="background:rgba(255,255,255,0.05);">
                <i class="fa-solid fa-envelope text-gray-400 text-xs"></i>
            </div>
            <span>Kontak</span>
        </a>

        {{-- Auth --}}
        <div class="pt-3 mt-1 border-t" style="border-color:var(--border);">
            @guest
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('login') }}"
                   class="btn-outline text-center py-3 rounded-xl text-sm font-bold">Masuk</a>
                <a href="{{ route('register') }}"
                   class="btn-primary text-center py-3 rounded-xl text-sm font-bold">Daftar Gratis</a>
            </div>
            @endguest
            @auth
            <div class="flex items-center gap-3 mb-3 p-3 rounded-2xl"
                 style="background:rgba(255,255,255,0.04); border:1px solid var(--border);">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm"
                     style="background:var(--accent-dim); color:var(--accent);">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-bold text-white">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full py-3 rounded-xl font-bold border text-sm
                               text-red-400 hover:bg-red-500/10 transition-colors
                               flex items-center justify-center gap-2"
                        style="background:rgba(239,68,68,0.06); border-color:rgba(239,68,68,0.15);">
                    <i class="fa-solid fa-right-from-bracket text-xs"></i> Keluar
                </button>
            </form>
            @endauth
        </div>

    </div>{{-- /mobileMenu --}}
</nav>


{{-- ══════════════════════════════════════════
     SEARCH OVERLAY (⌘K)
══════════════════════════════════════════ --}}
<div id="search-overlay" class="search-overlay" role="dialog" aria-modal="true" aria-label="Cari Tools">
    <div class="search-backdrop" onclick="closeSearch()"></div>
    <div class="search-modal">

        {{-- Input --}}
        <div class="search-input-wrap">
            <i class="fa-solid fa-magnifying-glass search-input-icon"></i>
            <input type="text" id="search-input"
                   class="search-input"
                   placeholder="Cari tools... (contoh: hapus background, PDF, password)"
                   autocomplete="off" spellcheck="false">
            <button onclick="closeSearch()" class="search-close-btn" aria-label="Tutup">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Browse / Results --}}
        <div class="search-body" id="search-body">

            {{-- Default Browse --}}
            <div id="search-browse">
                @foreach($navCategories as $cat)
                @php $c = $colorMap[$cat['color']]; @endphp
                <div class="search-cat-section">
                    <div class="search-cat-label"
                         style="color:{{ $c['text'] }};">
                        <i class="fa-solid {{ $cat['icon'] }}"></i>
                        {{ $cat['label'] }}
                    </div>
                    <div class="search-cat-grid">
                        @foreach($cat['tools'] as $tool)
                        <a href="{{ route($tool['route']) }}"
                           class="search-tool-item"
                           data-name="{{ strtolower($tool['name']) }}"
                           data-tags="{{ implode(' ', $tool['tags']) }}"
                           onclick="closeSearch()">
                            <div class="search-tool-icon"
                                 style="background:{{ $c['bg'] }}; color:{{ $c['text'] }};">
                                <i class="fa-solid {{ $tool['icon'] }}"></i>
                            </div>
                            <div class="search-tool-info">
                                <p class="search-tool-name">{{ $tool['name'] }}</p>
                                <p class="search-tool-desc">{{ $tool['desc'] }}</p>
                            </div>
                            <i class="fa-solid fa-arrow-right search-tool-arrow"></i>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Search Results --}}
            <div id="search-results" class="hidden">
                <div class="search-cat-label">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Hasil Pencarian
                </div>
                <div id="search-results-grid" class="search-cat-grid"></div>
                <div id="search-empty" class="search-empty hidden">
                    <i class="fa-regular fa-face-confused text-3xl text-gray-600 mb-3 block"></i>
                    <p class="text-gray-400 font-semibold">Tools tidak ditemukan</p>
                    <p class="text-gray-600 text-sm mt-1">Coba kata lain seperti "PDF", "gambar", atau "download"</p>
                </div>
            </div>

        </div>

        <div class="search-footer">
            <span><kbd>↵</kbd> Buka</span>
            <span><kbd>↑↓</kbd> Navigasi</span>
            <span><kbd>Esc</kbd> Tutup</span>
        </div>
    </div>
</div>


{{-- ── NAVBAR SCRIPTS ── --}}
@push('scripts')
<script>
(function () {
    /* ─── Desktop Mega Dropdown ─── */
    const btn      = document.getElementById('toolsBtn');
    const dropdown = document.getElementById('toolsDropdown');
    const arrow    = document.getElementById('toolsArrow');
    let   open     = false;

    window.closeDropdown = function () {
        open = false;
        dropdown.classList.remove('show');
        arrow.style.transform = '';
        btn && btn.setAttribute('aria-expanded', 'false');
    };

    window.toggleToolsMenu = function () {
        open = !open;
        dropdown.classList.toggle('show', open);
        arrow.style.transform = open ? 'rotate(180deg)' : '';
        btn && btn.setAttribute('aria-expanded', String(open));
    };

    document.addEventListener('click', function (e) {
        if (open && !document.getElementById('toolsMenu').contains(e.target)) {
            closeDropdown();
        }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDropdown();
    });

    /* ─── Mobile Menu ─── */
    let mobileOpen = false;
    window.toggleMobileMenu = function () {
        mobileOpen = !mobileOpen;
        const menu = document.getElementById('mobileMenu');
        const icon = document.getElementById('menuIcon');
        menu.classList.toggle('hidden', !mobileOpen);
        menu.setAttribute('aria-hidden', String(!mobileOpen));
        icon.className = mobileOpen
            ? 'fa-solid fa-xmark text-sm'
            : 'fa-solid fa-bars text-sm';
        if (!mobileOpen) closeMobileTools();
    };

    let toolsOpen = false;
    function closeMobileTools() {
        toolsOpen = false;
        const sub = document.getElementById('mobileToolsMenu');
        const arr = document.getElementById('mobileToolsArrow');
        sub && sub.classList.add('hidden');
        arr && (arr.style.transform = '');
    }
    window.toggleMobileTools = function () {
        toolsOpen = !toolsOpen;
        const sub = document.getElementById('mobileToolsMenu');
        const arr = document.getElementById('mobileToolsArrow');
        sub.classList.toggle('hidden', !toolsOpen);
        arr.style.transform = toolsOpen ? 'rotate(180deg)' : '';
    };

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 1024 && mobileOpen) toggleMobileMenu();
    });
    document.querySelectorAll('#mobileMenu a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (mobileOpen) toggleMobileMenu();
        });
    });

    /* ─── Navbar scroll effect ─── */
    const nav = document.getElementById('mainNav');
    window.addEventListener('scroll', function () {
        nav.classList.toggle('scrolled', window.scrollY > 40);
    }, { passive: true });

    /* ─── Search Overlay ─── */
    const overlay     = document.getElementById('search-overlay');
    const searchInput = document.getElementById('search-input');
    const browseEl    = document.getElementById('search-browse');
    const resultsEl   = document.getElementById('search-results');
    const resultsGrid = document.getElementById('search-results-grid');
    const emptyEl     = document.getElementById('search-empty');
    const allItems    = document.querySelectorAll('.search-tool-item');

    let searchOpen = false;
    let focusedIdx = -1;
    let visibleItems = [];

    window.openSearch = function () {
        searchOpen = true;
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        setTimeout(() => searchInput.focus(), 80);
        resetSearch();
    };
    window.closeSearch = function () {
        searchOpen = false;
        overlay.classList.remove('open');
        document.body.style.overflow = '';
        searchInput.value = '';
        resetSearch();
    };

    function resetSearch() {
        browseEl.classList.remove('hidden');
        resultsEl.classList.add('hidden');
        emptyEl.classList.add('hidden');
        focusedIdx = -1;
        visibleItems = [];
        document.querySelectorAll('.search-tool-item.focused')
            .forEach(el => el.classList.remove('focused'));
    }

    searchInput.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        if (!q) { resetSearch(); return; }

        browseEl.classList.add('hidden');
        resultsEl.classList.remove('hidden');
        resultsGrid.innerHTML = '';
        visibleItems = [];
        focusedIdx = -1;

        allItems.forEach(item => {
            const name = item.dataset.name || '';
            const tags = item.dataset.tags || '';
            if (name.includes(q) || tags.includes(q)) {
                const clone = item.cloneNode(true);
                clone.addEventListener('click', closeSearch);
                const nameEl = clone.querySelector('.search-tool-name');
                if (nameEl) {
                    const re = new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi');
                    nameEl.innerHTML = nameEl.textContent.replace(re,
                        '<mark class="search-highlight">$1</mark>');
                }
                resultsGrid.appendChild(clone);
                visibleItems.push(clone);
            }
        });

        emptyEl.classList.toggle('hidden', visibleItems.length > 0);
    });

    document.addEventListener('keydown', function (e) {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            searchOpen ? closeSearch() : openSearch();
            return;
        }
        if (!searchOpen) return;
        if (e.key === 'Escape') { closeSearch(); return; }

        const items = visibleItems.length
            ? visibleItems
            : Array.from(document.querySelectorAll('#search-browse .search-tool-item'));

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            focusedIdx = Math.min(focusedIdx + 1, items.length - 1);
            updateFocus(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            focusedIdx = Math.max(focusedIdx - 1, 0);
            updateFocus(items);
        } else if (e.key === 'Enter' && focusedIdx >= 0) {
            e.preventDefault();
            items[focusedIdx]?.click();
        }
    });

    function updateFocus(items) {
        items.forEach((el, i) => el.classList.toggle('focused', i === focusedIdx));
        items[focusedIdx]?.scrollIntoView({ block: 'nearest' });
        searchInput.focus();
    }
})();
</script>
@endpush