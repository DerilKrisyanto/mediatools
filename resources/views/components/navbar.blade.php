<nav class="fixed w-full z-50 glass-nav" id="mainNav">
    <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">

        {{-- ── LOGO ── --}}
        <a href="{{ route('home') }}" class="flex items-center gap-3 group flex-shrink-0">
            <div class="w-10 h-10 flex items-center justify-center transition-transform duration-300 group-hover:scale-110">
                <img src="{{ asset('images/icons.png') }}" 
                    alt="Media Tools Logo" 
                    class="w-full h-full object-contain filter-drop-shadow">
            </div>
            <span class="text-xl font-bold tracking-tight text-white leading-none">
                MEDIA<span class="text-[#a3e635]">TOOLS.</span>
            </span>
        </a>

        {{-- ── DESKTOP MENU ── --}}
        <div class="hidden md:flex items-center gap-8">
            <a href="{{ route('home') }}" class="nav-link">Beranda</a>

            {{-- Tools Dropdown (ringkas, hanya kategori) --}}
            <div class="relative" id="toolsMenu">
                <button id="toolsBtn" onclick="toggleToolsMenu()"
                        class="nav-link flex items-center gap-2 group/btn"
                        aria-haspopup="true" aria-expanded="false">
                    <span>Tools</span>
                    <span class="w-5 h-5 bg-white/5 rounded-md flex items-center justify-center
                                 group-hover/btn:bg-[#a3e635]/20 transition-colors">
                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-300" id="toolsArrow"></i>
                    </span>
                </button>

                <div id="toolsDropdown" class="dropdown-menu" role="menu">
                    <div class="px-3 pt-2 pb-3 mb-1 border-b border-white/5">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Kategori Tools</p>
                    </div>

                    {{-- Kategori dengan ikon --}}
                    @foreach([
                        ['fa-file-alt',      'Dokumen & Bisnis', [['tools.invoice','fa-file-invoice-dollar','Invoice Generator'],['tools.pdfutilities','fa-file-pdf','PDF Utilities']]],
                        ['fa-image',         'Gambar & Media',   [['tools.fileconverter','fa-refresh','Files Converter'],['tools.imageconverter','fa-image','Image Converter'],['tools.bgremover','fa-scissors','Background Remover']]],
                        ['fa-share-alt',     'Sosial & Link',    [['tools.mediadownloader','fa-cloud-download','Media Downloader'],['tools.linktree','fa-link','LinkTree Builder'],['tools.qr','fa-qrcode','QR Code Generator']]],
                        ['fa-shield-halved', 'Keamanan',         [['tools.passwordgenerator','fa-key','Password Generator']]],
                        ['fa-pen-nib',       'Branding',         [['tools.signature','fa-signature','Email Signature']]],
                    ] as [$catIcon, $catName, $items])
                    <div class="mb-1">
                        <p class="px-3 py-1.5 text-[10px] font-bold text-gray-500 uppercase tracking-widest flex items-center gap-2">
                            <i class="fa-solid {{ $catIcon }} text-[9px]"></i> {{ $catName }}
                        </p>
                        @foreach($items as [$route, $icon, $label])
                        <a href="{{ route($route) }}" class="tool-item" role="menuitem">
                            <div class="icon-wrap"><i class="fa-solid {{ $icon }}"></i></div>
                            <div><p>{{ $label }}</p></div>
                        </a>
                        @endforeach
                    </div>
                    @endforeach

                    <div class="mt-2 pt-2 border-t border-white/5 px-2">
                        <button onclick="openSearch()"
                           class="w-full flex items-center justify-between px-3 py-2 rounded-xl
                                  text-[#a3e635] text-xs font-bold
                                  hover:bg-[#a3e635]/10 transition-colors">
                            <span>Cari semua tools...</span>
                            <span class="flex items-center gap-1">
                                <kbd class="px-1.5 py-0.5 bg-white/5 rounded text-[9px] text-gray-400">⌘K</kbd>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <a href="#about" class="nav-link">Tentang</a>
            <a href="#contact" class="nav-link">Kontak</a>
        </div>

        {{-- ── RIGHT: SEARCH + AUTH ── --}}
        <div class="hidden md:flex items-center gap-3">

            {{-- Search Button --}}
            <button onclick="openSearch()"
                    class="flex items-center gap-2.5 px-3.5 py-2 rounded-xl
                           bg-white/[0.04] border border-white/[0.06]
                           text-gray-400 hover:text-white hover:border-white/10
                           hover:bg-white/[0.07] transition-all duration-200
                           text-sm font-medium group">
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                <span class="text-xs">Cari tools...</span>
                <kbd class="ml-1 px-1.5 py-0.5 bg-white/5 rounded text-[10px] text-gray-500
                            group-hover:bg-white/10 transition-colors">⌘K</kbd>
            </button>

            @guest
                <a href="{{ route('login') }}" class="nav-link text-gray-400 hover:text-white px-4 py-2">Masuk</a>
                <a href="{{ route('register') }}" class="btn-primary px-5 py-2.5 text-sm rounded-xl font-bold">
                    <span>Daftar</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            @endguest

            @auth
                <div class="flex items-center gap-3 pl-3 border-l border-white/10">
                    <div class="w-8 h-8 rounded-full bg-[#a3e635]/20 flex items-center justify-center text-[#a3e635] font-bold text-sm">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <span class="text-sm font-semibold text-white leading-none">
                        {{ Str::limit(Auth::user()->name, 14) }}
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 text-sm font-semibold
                                   text-gray-400 hover:text-red-400 transition-colors px-3 py-2
                                   hover:bg-red-500/10 rounded-xl">
                        <i class="fa-solid fa-right-from-bracket text-xs"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            @endauth
        </div>

        {{-- ── MOBILE: SEARCH ICON + HAMBURGER ── --}}
        <div class="md:hidden flex items-center gap-2">
            <button onclick="openSearch()"
                    class="w-10 h-10 rounded-xl bg-white/5 border border-white/8
                           flex items-center justify-center text-gray-400
                           hover:text-white hover:bg-white/10 transition-colors">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </button>
            <button id="mobileMenuBtn" onclick="toggleMobileMenu()"
                    class="w-10 h-10 rounded-xl bg-white/5 border border-white/8
                           flex items-center justify-center text-white
                           hover:bg-white/10 transition-colors"
                    aria-label="Toggle menu">
                <i class="fa-solid fa-bars text-sm" id="menuIcon"></i>
            </button>
        </div>
    </div>

    {{-- ── MOBILE MENU ── --}}
    <div id="mobileMenu" class="mobile-menu hidden">
        <a href="{{ route('home') }}" class="flex items-center gap-3 px-1 py-2 rounded-xl text-white font-semibold hover:bg-white/5 hover:text-[#a3e635] transition-colors">
            <i class="fa-solid fa-house text-xs w-4 text-center text-gray-500"></i>
            Beranda
        </a>

        {{-- Search di mobile --}}
        <button onclick="openSearch(); toggleMobileMenu();"
                class="flex items-center gap-3 px-1 py-2 rounded-xl text-white font-semibold hover:bg-white/5 hover:text-[#a3e635] transition-colors w-full text-left">
            <i class="fa-solid fa-magnifying-glass text-xs w-4 text-center text-gray-500"></i>
            Cari Tools...
        </button>

        <div>
            <button onclick="toggleMobileTools()"
                    class="w-full flex items-center justify-between px-1 py-2 rounded-xl text-white font-semibold hover:bg-white/5 transition-colors">
                <span class="flex items-center gap-3">
                    <i class="fa-solid fa-toolbox text-xs w-4 text-center text-gray-500"></i>
                    Tools
                </span>
                <span class="w-6 h-6 bg-white/5 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-300" id="mobileToolsArrow"></i>
                </span>
            </button>
            <div id="mobileToolsMenu" class="mobile-tools hidden mt-2">
                @foreach([
                    ['tools.invoice',           'fa-file-invoice-dollar', 'Invoice Generator'],
                    ['tools.pdfutilities',       'fa-file-pdf',            'PDF Utilities'],
                    ['tools.fileconverter',     'fa-refresh',               'Files Converter'],
                    ['tools.imageconverter',     'fa-image',               'Image Converter'],
                    ['tools.bgremover',          'fa-scissors',            'Background Remover'],
                    ['tools.mediadownloader',    'fa-cloud-download',       'Media Downloader'],
                    ['tools.linktree',           'fa-link',                'LinkTree Builder'],
                    ['tools.qr',                 'fa-qrcode',              'QR Code Generator'],
                    ['tools.passwordgenerator',  'fa-key',                 'Password Generator'],
                    ['tools.signature',          'fa-signature',           'Email Signature'],
                ] as [$route, $icon, $label])
                <a href="{{ route($route) }}" class="flex items-center gap-3 py-2 rounded-lg text-gray-400 hover:text-[#a3e635] transition-colors text-sm">
                    <i class="fa-solid {{ $icon }} text-[11px] w-4 text-center"></i>
                    {{ $label }}
                </a>
                @endforeach
            </div>
        </div>

        <a href="#about" class="flex items-center gap-3 px-1 py-2 rounded-xl text-white font-semibold hover:bg-white/5 hover:text-[#a3e635] transition-colors">
            <i class="fa-solid fa-circle-info text-xs w-4 text-center text-gray-500"></i>
            Tentang
        </a>
        <a href="#contact" class="flex items-center gap-3 px-1 py-2 rounded-xl text-white font-semibold hover:bg-white/5 hover:text-[#a3e635] transition-colors">
            <i class="fa-solid fa-envelope text-xs w-4 text-center text-gray-500"></i>
            Kontak
        </a>

        <div class="pt-4 mt-2 border-t border-white/8">
            @guest
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('login') }}" class="btn-outline text-center py-3 rounded-xl text-sm font-bold">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary text-center py-3 rounded-xl text-sm font-bold">Daftar</a>
                </div>
            @endguest
            @auth
                <div class="flex items-center gap-3 mb-4 p-3 bg-white/5 rounded-2xl">
                    <div class="w-9 h-9 rounded-xl bg-[#a3e635]/20 flex items-center justify-center text-[#a3e635] font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-white">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full py-3 rounded-xl bg-red-500/10 text-red-400 font-bold border border-red-500/15 text-sm hover:bg-red-500/20 transition-colors flex items-center justify-center gap-2">
                        <i class="fa-solid fa-right-from-bracket text-xs"></i> Keluar dari Akun
                    </button>
                </form>
            @endauth
        </div>
    </div>
</nav>

{{-- ══════════════════════════════════════
     SEARCH OVERLAY
══════════════════════════════════════ --}}
<div id="search-overlay" class="search-overlay" role="dialog" aria-modal="true" aria-label="Cari Tools">
    <div class="search-backdrop" onclick="closeSearch()"></div>
    <div class="search-modal">

        {{-- Search Input --}}
        <div class="search-input-wrap">
            <i class="fa-solid fa-magnifying-glass search-input-icon"></i>
            <input type="text" id="search-input"
                   class="search-input"
                   placeholder="Cari tools... (contoh: PDF, password, gambar)"
                   autocomplete="off" spellcheck="false">
            <button onclick="closeSearch()" class="search-close-btn" aria-label="Tutup">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Results / Browse --}}
        <div class="search-body" id="search-body">

            {{-- Default: semua tools per kategori --}}
            <div id="search-browse">
                @php
                $categories = [
                    [
                        'icon'  => 'fa-file-alt',
                        'name'  => 'Dokumen & Bisnis',
                        'color' => 'amber',
                        'tools' => [
                            ['tools.invoice',     'fa-file-invoice-dollar', 'Invoice Generator',  'Buat tagihan profesional dalam detik',      ['invoice','tagihan','billing']],
                            ['tools.pdfutilities','fa-file-pdf',            'PDF Utilities',       'Merge, split, dan compress PDF',             ['pdf','merge','split','compress']],
                        ],
                    ],
                    [
                        'icon'  => 'fa-image',
                        'name'  => 'Gambar & Media',
                        'color' => 'blue',
                        'tools' => [
                            ['tools.fileconverter','fa-refresh',   'Files Converter',    'Konversi PDF, Word, Excel, PPTX, JPG',  ['pdf','word','exel','pptx','image']],
                            ['tools.imageconverter','fa-image',   'Image Converter',    'Resize, compress & convert JPG/PNG/WebP',  ['gambar','image','foto','convert','resize','compress','webp','png','jpg']],
                            ['tools.bgremover',    'fa-scissors', 'Background Remover', 'Hapus background foto otomatis',           ['background','hapus','foto','remover']],
                        ],
                    ],
                    [
                        'icon'  => 'fa-share-alt',
                        'name'  => 'Sosial & Link',
                        'color' => 'purple',
                        'tools' => [
                            ['tools.mediadownloader', 'fa-cloud-download', 'Media Downloader','Download YouTube, TikTok & Instagram',['download','youtube','tiktok','instagram','video','mp3','mp4']],
                            ['tools.linktree','fa-link',   'LinkTree Builder',    'Satu halaman untuk semua linkmu',       ['link','linktree','sosial','bio']],
                            ['tools.qr',      'fa-qrcode', 'QR Code Generator',   'Buat QR Code custom untuk bisnis',      ['qr','qrcode','barcode','scan']],
                        ],
                    ],
                    [
                        'icon'  => 'fa-shield-halved',
                        'name'  => 'Keamanan',
                        'color' => 'green',
                        'tools' => [
                            ['tools.passwordgenerator','fa-key','Password Generator','Buat password kuat & aman secara instan',['password','kata sandi','keamanan','security']],
                        ],
                    ],
                    [
                        'icon'  => 'fa-pen-nib',
                        'name'  => 'Branding & Identitas',
                        'color' => 'rose',
                        'tools' => [
                            ['tools.signature','fa-signature','Email Signature','Tanda tangan email profesional',['signature','email','tanda tangan','branding']],
                        ],
                    ],
                ];
                @endphp

                @foreach($categories as $cat)
                <div class="search-cat-section">
                    <div class="search-cat-label">
                        <i class="fa-solid {{ $cat['icon'] }}"></i>
                        {{ $cat['name'] }}
                    </div>
                    <div class="search-cat-grid">
                        @foreach($cat['tools'] as [$route, $icon, $name, $desc, $tags])
                        <a href="{{ route($route) }}"
                           class="search-tool-item"
                           data-name="{{ strtolower($name) }}"
                           data-tags="{{ implode(' ', $tags) }}"
                           onclick="closeSearch()">
                            <div class="search-tool-icon search-tool-icon--{{ $cat['color'] }}">
                                <i class="fa-solid {{ $icon }}"></i>
                            </div>
                            <div class="search-tool-info">
                                <p class="search-tool-name">{{ $name }}</p>
                                <p class="search-tool-desc">{{ $desc }}</p>
                            </div>
                            <i class="fa-solid fa-arrow-right search-tool-arrow"></i>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Search results (hidden by default) --}}
            <div id="search-results" class="hidden">
                <div class="search-cat-label">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Hasil Pencarian
                </div>
                <div id="search-results-grid" class="search-cat-grid"></div>
                <div id="search-empty" class="search-empty hidden">
                    <i class="fa-regular fa-face-confused text-3xl text-gray-600 mb-3"></i>
                    <p class="text-gray-400 font-semibold">Tidak ada tools yang cocok</p>
                    <p class="text-gray-600 text-sm mt-1">Coba kata kunci lain atau lihat semua tools di atas</p>
                </div>
            </div>

        </div>

        {{-- Footer hint --}}
        <div class="search-footer">
            <span><kbd>↵</kbd> Open</span>
            <span><kbd>↑↓</kbd> Navigation</span>
            <span><kbd>Esc</kbd> Close</span>
        </div>

    </div>
</div>

{{-- ── NAVBAR SCRIPTS ── --}}
@push('scripts')
<script>
(function () {
    /* ---- Dropdown desktop ---- */
    const btn      = document.getElementById('toolsBtn');
    const dropdown = document.getElementById('toolsDropdown');
    const arrow    = document.getElementById('toolsArrow');
    let   open     = false;

    function closeDropdown() {
        open = false;
        dropdown.classList.remove('show');
        arrow.style.transform = 'rotate(0deg)';
        btn && btn.setAttribute('aria-expanded', 'false');
    }

    window.toggleToolsMenu = function () {
        open = !open;
        dropdown.classList.toggle('show', open);
        arrow.style.transform = open ? 'rotate(180deg)' : 'rotate(0deg)';
        btn && btn.setAttribute('aria-expanded', String(open));
    };

    document.addEventListener('click', function (e) {
        if (open && !document.getElementById('toolsMenu').contains(e.target)) closeDropdown();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDropdown();
    });

    /* ---- Mobile menu ---- */
    let mobileOpen = false;
    window.toggleMobileMenu = function () {
        mobileOpen = !mobileOpen;
        const menu = document.getElementById('mobileMenu');
        const icon = document.getElementById('menuIcon');
        menu.classList.toggle('hidden', !mobileOpen);
        icon.className = mobileOpen ? 'fa-solid fa-xmark text-sm' : 'fa-solid fa-bars text-sm';
        if (!mobileOpen) closeMobileTools();
    };

    let toolsOpen = false;
    function closeMobileTools() {
        toolsOpen = false;
        const sub = document.getElementById('mobileToolsMenu');
        const arr = document.getElementById('mobileToolsArrow');
        sub  && sub.classList.add('hidden');
        arr  && (arr.style.transform = 'rotate(0deg)');
    }
    window.toggleMobileTools = function () {
        toolsOpen = !toolsOpen;
        const sub = document.getElementById('mobileToolsMenu');
        const arr = document.getElementById('mobileToolsArrow');
        sub.classList.toggle('hidden', !toolsOpen);
        arr.style.transform = toolsOpen ? 'rotate(180deg)' : 'rotate(0deg)';
    };

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 768 && mobileOpen) toggleMobileMenu();
    });
    document.querySelectorAll('#mobileMenu a').forEach(function (link) {
        link.addEventListener('click', function () { if (mobileOpen) toggleMobileMenu(); });
    });

    /* ════════════════════════════════════
       SEARCH OVERLAY
    ════════════════════════════════════ */
    const overlay      = document.getElementById('search-overlay');
    const searchInput  = document.getElementById('search-input');
    const browseEl     = document.getElementById('search-browse');
    const resultsEl    = document.getElementById('search-results');
    const resultsGrid  = document.getElementById('search-results-grid');
    const emptyEl      = document.getElementById('search-empty');
    const allItems     = document.querySelectorAll('.search-tool-item');

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

    /* Live search */
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
                // Highlight match
                const nameEl = clone.querySelector('.search-tool-name');
                if (nameEl) {
                    const re = new RegExp(`(${q})`, 'gi');
                    nameEl.innerHTML = nameEl.textContent.replace(re, '<mark class="search-highlight">$1</mark>');
                }
                resultsGrid.appendChild(clone);
                visibleItems.push(clone);
            }
        });

        emptyEl.classList.toggle('hidden', visibleItems.length > 0);
    });

    /* Keyboard navigation */
    document.addEventListener('keydown', function (e) {
        /* Open with Cmd/Ctrl + K */
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