{{-- ============================================================
     MEDIATOOLS — NAVBAR (Fixed, Scrollable Mega Menu, Mobile-First)
     resources/views/components/navbar.blade.php
     ============================================================ --}}

@php
$navCategories = [
    [
        'key'   => 'create',
        'label' => 'Buat & Kelola',
        'icon'  => 'fa-wand-magic-sparkles',
        'color' => 'lime',
        'desc'  => 'Buat dokumen, link, dan kode bisnis',
        'tools' => [
            ['route'=>'tools.invoice',   'icon'=>'fa-file-invoice-dollar','name'=>'Invoice Generator',  'desc'=>'Tagihan profesional, download PDF',     'badge'=>null,    'tags'=>['invoice','tagihan','billing','faktur','kwitansi']],
            ['route'=>'tools.signature', 'icon'=>'fa-signature',          'name'=>'Email Signature',    'desc'=>'Tanda tangan email & dokumen',           'badge'=>null,    'tags'=>['signature','tanda tangan','email','branding']],
            ['route'=>'tools.qr',        'icon'=>'fa-qrcode',             'name'=>'QR Code Generator',  'desc'=>'QR menu, WiFi, kontak, URL',             'badge'=>'Baru',  'tags'=>['qr','qrcode','barcode','scan','menu','wifi']],
            ['route'=>'tools.linktree',  'icon'=>'fa-link',               'name'=>'Link Tree',          'desc'=>'Satu link untuk semua sosmedmu',         'badge'=>'Populer','tags'=>['link','linktree','bio','sosial','instagram','tiktok']],
        ],
    ],
    [
        'key'   => 'files',
        'label' => 'File & Dokumen',
        'icon'  => 'fa-folder-open',
        'color' => 'blue',
        'desc'  => 'Konversi, edit, dan kelola file',
        'tools' => [
            ['route'=>'tools.pdfutilities',  'icon'=>'fa-file-pdf','name'=>'PDF Toolkit',     'desc'=>'Merge, split, compress PDF',           'badge'=>null,'tags'=>['pdf','merge','split','compress','gabung','pisah']],
            ['route'=>'tools.fileconverter', 'icon'=>'fa-rotate',  'name'=>'File Converter',  'desc'=>'PDF Word Excel PPT JPG',               'badge'=>null,'tags'=>['pdf','word','excel','pptx','convert','konversi','jpg']],
            ['route'=>'tools.imageconverter','icon'=>'fa-images',  'name'=>'Image Converter', 'desc'=>'Resize, kompres & ubah format foto',   'badge'=>null,'tags'=>['gambar','image','foto','resize','compress','webp','png','jpg']],
        ],
    ],
    [
        'key'   => 'media',
        'label' => 'Konten & Media',
        'icon'  => 'fa-photo-film',
        'color' => 'purple',
        'desc'  => 'Edit foto dan unduh konten',
        'tools' => [
            ['route'=>'tools.bgremover',      'icon'=>'fa-wand-sparkles','name'=>'Background Remover','desc'=>'Hapus background foto dengan AI',  'badge'=>'AI', 'tags'=>['background','hapus','foto','remover','transparent','ai']],
            ['route'=>'tools.mediadownloader','icon'=>'fa-circle-down',  'name'=>'Media Downloader',  'desc'=>'Download YouTube, TikTok & IG',   'badge'=>null, 'tags'=>['download','youtube','tiktok','instagram','video','mp3','mp4','reels']],
        ],
    ],
    [
        'key'   => 'security',
        'label' => 'Keamanan',
        'icon'  => 'fa-shield-halved',
        'color' => 'green',
        'desc'  => 'Lindungi akun dan data Anda',
        'tools' => [
            ['route'=>'tools.passwordgenerator','icon'=>'fa-key','name'=>'Password Generator','desc'=>'Buat kata sandi kuat & aman','badge'=>null,'tags'=>['password','kata sandi','keamanan','security','pin']],
        ],
    ],
];

$colorMap = [
    'lime'   => ['bg'=>'rgba(163,230,53,0.12)',  'text'=>'#a3e635', 'badge'=>'rgba(163,230,53,0.18)'],
    'blue'   => ['bg'=>'rgba(59,130,246,0.12)',  'text'=>'#60a5fa', 'badge'=>'rgba(59,130,246,0.18)'],
    'purple' => ['bg'=>'rgba(139,92,246,0.12)',  'text'=>'#a78bfa', 'badge'=>'rgba(139,92,246,0.18)'],
    'green'  => ['bg'=>'rgba(34,197,94,0.12)',   'text'=>'#4ade80', 'badge'=>'rgba(34,197,94,0.18)'],
];
@endphp

{{-- ══ NAVBAR ══ --}}
<nav class="glass-nav fixed top-0 left-0 right-0 z-50" id="mainNav" role="navigation" aria-label="Navigasi utama">
<div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-3">

    {{-- LOGO --}}
    <a href="{{ route('home') }}" class="flex items-center gap-2 group flex-shrink-0" aria-label="MediaTools Beranda">
        <div class="w-8 h-8 sm:w-9 sm:h-9 transition-transform duration-300 group-hover:scale-110 flex-shrink-0">
            <img src="{{ asset('images/icons-mediatools.png') }}" alt="MediaTools Logo" class="w-full h-full object-contain" loading="eager" width="36" height="36">
        </div>
        <span class="text-[15px] sm:text-[17px] font-extrabold tracking-tight text-white leading-none">
            MEDIA<span style="color:var(--accent)">TOOLS.</span>
        </span>
    </a>

    {{-- ── DESKTOP MENU (lg+) ── --}}
    <div class="hidden lg:flex items-center gap-1 flex-1 justify-center">
        <a href="{{ route('home') }}" class="nav-link px-3 py-2 rounded-lg hover:bg-white/5 transition-colors">Beranda</a>

        {{-- Mega Dropdown --}}
        <div id="toolsMenu" class="relative">
            <button id="toolsBtn"
                    type="button"
                    onclick="MT.toggleDropdown()"
                    class="nav-link flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-white/5 transition-colors"
                    aria-haspopup="true"
                    aria-expanded="false"
                    aria-controls="toolsDropdown">
                <span>Semua Tools</span>
                <i id="toolsArrow" class="fa-solid fa-chevron-down text-[10px] transition-transform duration-300"></i>
            </button>

            {{-- MEGA MENU — fully scrollable --}}
            <div id="toolsDropdown" class="nav-mega-menu" role="menu" aria-label="Daftar semua tools">
                <div class="nav-mega-inner">

                    <div class="nav-mega-header">
                        <p class="nav-mega-title">Pilih Tools yang Kamu Butuhkan</p>
                        <a href="{{ route('home') }}#tools" class="nav-mega-see-all" onclick="MT.closeDropdown()">
                            Lihat semua <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>

                    {{-- Scrollable grid body --}}
                    <div class="nav-mega-scroll-body">
                        <div class="nav-mega-grid">
                            @foreach($navCategories as $cat)
                            @php $c = $colorMap[$cat['color']]; @endphp
                            <div class="nav-mega-col">
                                <div class="nav-cat-head">
                                    <div class="nav-cat-icon" style="background:{{ $c['bg'] }};color:{{ $c['text'] }};">
                                        <i class="fa-solid {{ $cat['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <p class="nav-cat-name">{{ $cat['label'] }}</p>
                                        <p class="nav-cat-desc">{{ $cat['desc'] }}</p>
                                    </div>
                                </div>
                                <div class="nav-cat-tools">
                                    @foreach($cat['tools'] as $tool)
                                    <a href="{{ route($tool['route']) }}"
                                       class="nav-tool-item"
                                       role="menuitem"
                                       onclick="MT.closeDropdown()">
                                        <div class="nav-tool-icon" style="background:{{ $c['bg'] }};color:{{ $c['text'] }};">
                                            <i class="fa-solid {{ $tool['icon'] }}"></i>
                                        </div>
                                        <div class="nav-tool-text">
                                            <span class="nav-tool-name">{{ $tool['name'] }}</span>
                                            <span class="nav-tool-desc">{{ $tool['desc'] }}</span>
                                        </div>
                                        @if($tool['badge'])
                                        <span class="nav-tool-badge" style="background:{{ $c['badge'] }};color:{{ $c['text'] }};">{{ $tool['badge'] }}</span>
                                        @endif
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="nav-mega-footer">
                        <button type="button" onclick="MT.openSearch(); MT.closeDropdown();" class="nav-mega-search-btn">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                            <span>Cari tools spesifik...</span>
                            <kbd>⌘K</kbd>
                        </button>
                        <div class="nav-mega-stats">
                            <span><i class="fa-solid fa-bolt" style="color:var(--accent);font-size:10px;"></i> 10 Tools Aktif</span>
                            <span><i class="fa-solid fa-shield-halved" style="color:var(--accent);font-size:10px;"></i> 100% Gratis</span>
                            <span><i class="fa-solid fa-lock" style="color:var(--accent);font-size:10px;"></i> Privasi Terjaga</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <a href="{{ route('home') }}#about"   class="nav-link px-3 py-2 rounded-lg hover:bg-white/5 transition-colors">Tentang</a>
        <a href="{{ route('home') }}#contact" class="nav-link px-3 py-2 rounded-lg hover:bg-white/5 transition-colors">Kontak</a>
    </div>

    {{-- ── DESKTOP RIGHT: SEARCH + AUTH ── --}}
    <div class="hidden lg:flex items-center gap-2 flex-shrink-0">
        <button type="button" onclick="MT.openSearch()" class="nav-search-pill" aria-label="Cari tools (Ctrl+K)">
            <i class="fa-solid fa-magnifying-glass text-xs"></i>
            <span>Cari tools...</span>
            <kbd>⌘K</kbd>
        </button>

        @guest
            <a href="{{ route('login') }}"    class="nav-link px-4 py-2 rounded-xl hover:bg-white/5 transition-colors text-sm">Masuk</a>
            <a href="{{ route('register') }}" class="btn-primary px-5 py-2.5 text-sm rounded-xl">
                <span>Daftar Gratis</span>
                <i class="fa-solid fa-arrow-right text-[11px]"></i>
            </a>
        @endguest

        @auth
            <div class="flex items-center gap-2 pl-3 border-l" style="border-color:var(--border)">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0"
                     style="background:var(--accent-dim);color:var(--accent);">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <span class="text-sm font-semibold text-white">{{ Str::limit(Auth::user()->name, 12) }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex items-center gap-1.5 text-sm font-semibold text-gray-500
                               hover:text-red-400 transition-colors px-3 py-2 rounded-xl hover:bg-red-500/10">
                    <i class="fa-solid fa-right-from-bracket text-xs"></i>
                    <span>Keluar</span>
                </button>
            </form>
        @endauth
    </div>

    {{-- ── MOBILE: SEARCH + HAMBURGER ── --}}
    <div class="lg:hidden flex items-center gap-2 flex-shrink-0">
        <button type="button" onclick="MT.openSearch()" class="mobile-nav-icon-btn" aria-label="Cari tools">
            <i class="fa-solid fa-magnifying-glass text-sm"></i>
        </button>
        <button type="button"
                id="mobileMenuBtn"
                onclick="MT.toggleMobileMenu()"
                class="mobile-nav-icon-btn"
                aria-label="Buka menu navigasi"
                aria-expanded="false"
                aria-controls="mobileMenu">
            <i class="fa-solid fa-bars text-sm" id="menuIcon"></i>
        </button>
    </div>

</div>
</nav>

{{-- Spacer agar konten tidak tertutup navbar --}}
<div class="h-16" aria-hidden="true"></div>


{{-- ══ MOBILE MENU PANEL — Full-height, scrollable ══ --}}
<div id="mobileMenu"
     class="mobile-menu-panel"
     aria-hidden="true"
     role="dialog"
     aria-modal="true"
     aria-label="Menu navigasi">

    {{-- Scrollable inner --}}
    <div class="mobile-menu-inner" id="mobileMenuInner">

        {{-- Search shortcut --}}
        <div class="mobile-search-bar"
             role="button"
             tabindex="0"
             onclick="MT.openSearch(); MT.closeMobileMenu();"
             onkeydown="if(event.key==='Enter'){MT.openSearch();MT.closeMobileMenu();}">
            <i class="fa-solid fa-magnifying-glass text-gray-500 text-sm flex-shrink-0"></i>
            <span class="text-gray-500 text-sm flex-1">Cari tools... (PDF, QR, invoice...)</span>
            <kbd class="mobile-kbd">⌘K</kbd>
        </div>

        {{-- Nav links --}}
        <div class="mobile-nav-section">
            <a href="{{ route('home') }}" class="mobile-nav-link" onclick="MT.closeMobileMenu()">
                <div class="mobile-nav-link-icon" style="background:rgba(255,255,255,0.05);">
                    <i class="fa-solid fa-house text-gray-400 text-xs"></i>
                </div>
                <span>Beranda</span>
            </a>
            <a href="{{ route('home') }}#about" class="mobile-nav-link" onclick="MT.closeMobileMenu()">
                <div class="mobile-nav-link-icon" style="background:rgba(255,255,255,0.05);">
                    <i class="fa-solid fa-circle-info text-gray-400 text-xs"></i>
                </div>
                <span>Tentang Kami</span>
            </a>
            <a href="{{ route('home') }}#contact" class="mobile-nav-link" onclick="MT.closeMobileMenu()">
                <div class="mobile-nav-link-icon" style="background:rgba(255,255,255,0.05);">
                    <i class="fa-solid fa-envelope text-gray-400 text-xs"></i>
                </div>
                <span>Kontak</span>
            </a>
        </div>

        {{-- Tools header --}}
        <div class="mobile-section-title">
            <i class="fa-solid fa-toolbox" style="color:var(--accent);"></i>
            <span>Semua Tools</span>
            <span class="mobile-section-count">10 tools</span>
        </div>

        {{-- Category accordions — each is independently scrollable via CSS --}}
        @foreach($navCategories as $catIdx => $cat)
        @php $c = $colorMap[$cat['color']]; @endphp
        <div class="mobile-cat-accordion {{ $catIdx === 0 ? 'is-open' : '' }}" id="mob-acc-{{ $cat['key'] }}">

            <button type="button"
                    class="mobile-cat-btn"
                    onclick="MT.toggleCat('{{ $cat['key'] }}')"
                    aria-expanded="{{ $catIdx === 0 ? 'true' : 'false' }}"
                    aria-controls="mob-body-{{ $cat['key'] }}">
                <div class="flex items-center gap-3">
                    <div class="mobile-cat-btn-icon" style="background:{{ $c['bg'] }};color:{{ $c['text'] }};">
                        <i class="fa-solid {{ $cat['icon'] }} text-xs"></i>
                    </div>
                    <div>
                        <p class="mobile-cat-btn-name">{{ $cat['label'] }}</p>
                        <p class="mobile-cat-btn-count">{{ count($cat['tools']) }} tools</p>
                    </div>
                </div>
                <div class="mobile-cat-chevron" id="mob-chev-{{ $cat['key'] }}"
                     style="{{ $catIdx === 0 ? 'transform:rotate(180deg)' : '' }}">
                    <i class="fa-solid fa-chevron-down text-[11px] text-gray-500"></i>
                </div>
            </button>

            <div id="mob-body-{{ $cat['key'] }}"
                 class="mobile-cat-body {{ $catIdx === 0 ? 'open' : '' }}">
                <div class="mobile-cat-tools-grid">
                    @foreach($cat['tools'] as $tool)
                    <a href="{{ route($tool['route']) }}" class="mobile-tool-card" onclick="MT.closeMobileMenu()">
                        <div class="mobile-tool-card-icon" style="background:{{ $c['bg'] }};color:{{ $c['text'] }};">
                            <i class="fa-solid {{ $tool['icon'] }}"></i>
                        </div>
                        <div class="mobile-tool-card-text">
                            <span class="mobile-tool-card-name">{{ $tool['name'] }}</span>
                            <span class="mobile-tool-card-desc">{{ $tool['desc'] }}</span>
                        </div>
                        @if($tool['badge'])
                        <span class="mobile-tool-card-badge" style="background:{{ $c['badge'] }};color:{{ $c['text'] }};">{{ $tool['badge'] }}</span>
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>

        </div>
        @endforeach

        {{-- Auth --}}
        <div class="mobile-auth-section">
            @guest
            <div class="grid grid-cols-2 gap-2.5">
                <a href="{{ route('login') }}"    class="mobile-auth-btn-outline">Masuk</a>
                <a href="{{ route('register') }}" class="mobile-auth-btn-primary">
                    <i class="fa-solid fa-bolt text-xs"></i> Daftar Gratis
                </a>
            </div>
            @endguest
            @auth
            <div class="mobile-user-card">
                <div class="mobile-user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-2.5">
                @csrf
                <button type="submit" class="mobile-logout-btn">
                    <i class="fa-solid fa-right-from-bracket text-xs"></i> Keluar dari Akun
                </button>
            </form>
            @endauth
        </div>

        {{-- Bottom padding for safe area --}}
        <div class="h-6" aria-hidden="true"></div>

    </div>
</div>


{{-- ══ SEARCH OVERLAY ══ --}}
<div id="search-overlay" class="search-overlay" role="dialog" aria-modal="true" aria-label="Cari Tools">

    <div class="search-backdrop" onclick="MT.closeSearch()"></div>

    <div class="search-modal">
        <div class="search-input-wrap">
            <i class="fa-solid fa-magnifying-glass search-input-icon"></i>
            <input type="text" id="search-input" class="search-input"
                   placeholder="Cari tools... (PDF, QR, password...)"
                   autocomplete="off" spellcheck="false" inputmode="search"
                   aria-label="Cari tools">
            <button type="button" onclick="MT.closeSearch()" class="search-close-btn" aria-label="Tutup pencarian">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="search-body">

            <div id="search-browse">
                @foreach($navCategories as $cat)
                @php $c = $colorMap[$cat['color']]; @endphp
                <div class="search-cat-section">
                    <div class="search-cat-label" style="color:{{ $c['text'] }};">
                        <i class="fa-solid {{ $cat['icon'] }}"></i> {{ $cat['label'] }}
                    </div>
                    <div class="search-cat-grid">
                        @foreach($cat['tools'] as $tool)
                        <a href="{{ route($tool['route']) }}"
                           class="search-tool-item"
                           data-name="{{ strtolower($tool['name']) }}"
                           data-tags="{{ implode(' ', $tool['tags']) }}"
                           onclick="MT.closeSearch()">
                            <div class="search-tool-icon" style="background:{{ $c['bg'] }};color:{{ $c['text'] }};">
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

            <div id="search-results" class="hidden">
                <div class="search-cat-label" style="color:var(--accent);">
                    <i class="fa-solid fa-magnifying-glass"></i> Hasil Pencarian
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


{{-- ══ SCRIPTS ══ --}}
@push('scripts')
<script>
(function () {
    'use strict';

    var $ = function (id) { return document.getElementById(id); };

    var state = {
        dropOpen:     false,
        mobileOpen:   false,
        openCat:      'create',
        searchOpen:   false,
        focusedIdx:   -1,
        visibleItems: [],
    };

    /* ── Desktop Dropdown ── */
    function openDropdown() {
        state.dropOpen = true;
        var m = $('toolsDropdown'), b = $('toolsBtn'), a = $('toolsArrow');
        if (m) m.classList.add('show');
        if (a) a.style.transform = 'rotate(180deg)';
        if (b) b.setAttribute('aria-expanded', 'true');
    }
    function closeDropdown() {
        state.dropOpen = false;
        var m = $('toolsDropdown'), b = $('toolsBtn'), a = $('toolsArrow');
        if (m) m.classList.remove('show');
        if (a) a.style.transform = '';
        if (b) b.setAttribute('aria-expanded', 'false');
    }
    function toggleDropdown() { state.dropOpen ? closeDropdown() : openDropdown(); }

    document.addEventListener('click', function (e) {
        if (!state.dropOpen) return;
        var tm = $('toolsMenu');
        if (tm && !tm.contains(e.target)) closeDropdown();
    });

    /* ── Mobile Menu ── */
    function openMobileMenu() {
        state.mobileOpen = true;
        var p = $('mobileMenu'), b = $('mobileMenuBtn'), i = $('menuIcon');
        if (p) { p.classList.add('open'); p.setAttribute('aria-hidden', 'false'); }
        if (b) b.setAttribute('aria-expanded', 'true');
        if (i) i.className = 'fa-solid fa-xmark text-sm';
        document.body.style.overflow = 'hidden';
    }
    function closeMobileMenu() {
        state.mobileOpen = false;
        var p = $('mobileMenu'), b = $('mobileMenuBtn'), i = $('menuIcon');
        if (p) { p.classList.remove('open'); p.setAttribute('aria-hidden', 'true'); }
        if (b) b.setAttribute('aria-expanded', 'false');
        if (i) i.className = 'fa-solid fa-bars text-sm';
        document.body.style.overflow = '';
    }
    function toggleMobileMenu() { state.mobileOpen ? closeMobileMenu() : openMobileMenu(); }

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 1024 && state.mobileOpen) closeMobileMenu();
    }, { passive: true });

    /* ── Mobile Category Accordion ── */
    function toggleCat(key) {
        var body = $('mob-body-' + key);
        var chev = $('mob-chev-' + key);
        var acc  = $('mob-acc-'  + key);
        var btn  = acc ? acc.querySelector('.mobile-cat-btn') : null;
        if (!body) return;

        var isOpen = body.classList.contains('open');

        /* close previously open */
        if (state.openCat && state.openCat !== key) {
            var pb  = $('mob-body-' + state.openCat);
            var pc  = $('mob-chev-' + state.openCat);
            var pa  = $('mob-acc-'  + state.openCat);
            var pbt = pa ? pa.querySelector('.mobile-cat-btn') : null;
            if (pb)  pb.classList.remove('open');
            if (pc)  pc.style.transform = '';
            if (pa)  pa.classList.remove('is-open');
            if (pbt) pbt.setAttribute('aria-expanded', 'false');
            state.openCat = null;
        }

        if (isOpen) {
            body.classList.remove('open');
            if (chev) chev.style.transform = '';
            if (acc)  acc.classList.remove('is-open');
            if (btn)  btn.setAttribute('aria-expanded', 'false');
            state.openCat = null;
        } else {
            body.classList.add('open');
            if (chev) chev.style.transform = 'rotate(180deg)';
            if (acc)  acc.classList.add('is-open');
            if (btn)  btn.setAttribute('aria-expanded', 'true');
            state.openCat = key;

            /* Scroll accordion into view with small offset */
            setTimeout(function () {
                if (acc) {
                    acc.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }, 320);
        }
    }

    /* ── Scroll Effect ── */
    var nav = $('mainNav');
    if (nav) {
        window.addEventListener('scroll', function () {
            nav.classList.toggle('scrolled', window.scrollY > 40);
        }, { passive: true });
    }

    /* ── Search ── */
    var overlay     = $('search-overlay');
    var searchInput = $('search-input');
    var browseEl    = $('search-browse');
    var resultsEl   = $('search-results');
    var resultsGrid = $('search-results-grid');
    var emptyEl     = $('search-empty');
    var allItems    = document.querySelectorAll('#search-browse .search-tool-item');

    function openSearch() {
        state.searchOpen = true;
        if (overlay) overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        resetSearch();
        setTimeout(function () { if (searchInput) searchInput.focus(); }, 100);
    }
    function closeSearch() {
        state.searchOpen = false;
        if (overlay) overlay.classList.remove('open');
        document.body.style.overflow = state.mobileOpen ? 'hidden' : '';
        if (searchInput) searchInput.value = '';
        resetSearch();
    }
    function resetSearch() {
        if (browseEl)    browseEl.classList.remove('hidden');
        if (resultsEl)   resultsEl.classList.add('hidden');
        if (emptyEl)     emptyEl.classList.add('hidden');
        if (resultsGrid) resultsGrid.innerHTML = '';
        state.focusedIdx   = -1;
        state.visibleItems = [];
        document.querySelectorAll('.search-tool-item.focused')
            .forEach(function (el) { el.classList.remove('focused'); });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            if (!q) { resetSearch(); return; }
            if (browseEl)    browseEl.classList.add('hidden');
            if (resultsEl)   resultsEl.classList.remove('hidden');
            if (resultsGrid) resultsGrid.innerHTML = '';
            state.visibleItems = [];
            state.focusedIdx   = -1;

            allItems.forEach(function (item) {
                var name = (item.dataset.name || '').toLowerCase();
                var tags = (item.dataset.tags || '').toLowerCase();
                if (name.indexOf(q) !== -1 || tags.indexOf(q) !== -1) {
                    var clone = item.cloneNode(true);
                    clone.classList.remove('focused');
                    clone.addEventListener('click', closeSearch);
                    var nameEl = clone.querySelector('.search-tool-name');
                    if (nameEl) {
                        var re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&') + ')', 'gi');
                        nameEl.innerHTML = nameEl.textContent.replace(re,
                            '<mark class="search-highlight">$1</mark>');
                    }
                    if (resultsGrid) resultsGrid.appendChild(clone);
                    state.visibleItems.push(clone);
                }
            });
            if (emptyEl) emptyEl.classList.toggle('hidden', state.visibleItems.length > 0);
        });
    }

    document.addEventListener('keydown', function (e) {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            state.searchOpen ? closeSearch() : openSearch();
            return;
        }
        if (e.key === 'Escape') {
            if (state.searchOpen)  { closeSearch();   return; }
            if (state.dropOpen)    { closeDropdown();  return; }
            if (state.mobileOpen)  { closeMobileMenu(); return; }
        }
        if (!state.searchOpen) return;
        var items = state.visibleItems.length
            ? state.visibleItems
            : Array.from(document.querySelectorAll('#search-browse .search-tool-item'));
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            state.focusedIdx = Math.min(state.focusedIdx + 1, items.length - 1);
            updateFocus(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            state.focusedIdx = Math.max(state.focusedIdx - 1, 0);
            updateFocus(items);
        } else if (e.key === 'Enter' && state.focusedIdx >= 0) {
            e.preventDefault();
            if (items[state.focusedIdx]) items[state.focusedIdx].click();
        }
    });

    function updateFocus(items) {
        items.forEach(function (el, i) { el.classList.toggle('focused', i === state.focusedIdx); });
        if (items[state.focusedIdx]) items[state.focusedIdx].scrollIntoView({ block: 'nearest' });
        if (searchInput) searchInput.focus();
    }

    /* ── Expose public API ── */
    window.MT = {
        openDropdown:     openDropdown,
        closeDropdown:    closeDropdown,
        toggleDropdown:   toggleDropdown,
        openMobileMenu:   openMobileMenu,
        closeMobileMenu:  closeMobileMenu,
        toggleMobileMenu: toggleMobileMenu,
        toggleCat:        toggleCat,
        openSearch:       openSearch,
        closeSearch:      closeSearch,
    };

    /* Legacy compat */
    window.toggleMobileMenu = toggleMobileMenu;
    window.toggleToolsMenu  = toggleDropdown;
    window.closeDropdown    = closeDropdown;
    window.openSearch       = openSearch;
    window.closeSearch      = closeSearch;

})();
</script>
@endpush