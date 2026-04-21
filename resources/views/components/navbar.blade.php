{{-- ============================================================
     MEDIATOOLS — NAVBAR
     resources/views/components/navbar.blade.php
     ============================================================ --}}

<nav class="nav-root" id="mainNav" aria-label="Navigasi utama">
    <div class="nav-inner">

        {{-- ── LOGO ── --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2 group flex-shrink-0" aria-label="MediaTools Beranda">
            <div class="w-8 h-8 sm:w-9 sm:h-9 transition-transform duration-300 group-hover:scale-110 flex-shrink-0">
                <img src="{{ asset('images/icons-mediatools.png') }}" alt="MediaTools Logo" class="w-full h-full object-contain" loading="eager" width="36" height="36">
            </div>
            <span class="text-[15px] sm:text-[17px] font-extrabold tracking-tight text-white leading-none">
                MEDIA<span style="color:var(--accent)">TOOLS.</span>
            </span>
        </a>

        {{-- ── DESKTOP LINKS ── --}}
        <div class="nav-links" role="menubar">

            {{-- Tools Dropdown --}}
            @php
            $navTools = [
                ['tools.invoice',           'fa-file-invoice-dollar', 'Invoice Generator',   'Tagihan profesional',      'amber'],
                ['tools.linktree',          'fa-link',                'LinkTree Builder',    'Satu halaman semua link',  'amber'],
                ['tools.qr',                'fa-qrcode',              'QR Code Generator',   'QR Code custom & branded', 'amber'],
                ['tools.signature',         'fa-signature',           'Email Signature',     'Tanda tangan profesional', 'amber'],
                ['tools.bgremover',         'fa-scissors',            'Background Remover',  'Hapus bg foto otomatis',   'blue'],
                ['tools.imageconverter',    'fa-image',               'Image Converter',     'Resize, compress & convert','blue'],
                ['tools.fotobox',           'fa-camera-retro',        'FotoBox',             'Photo booth seru online!', 'purple'],
                ['tools.fileconverter',     'fa-repeat',              'File Converter',      'PDF, Word, Excel & lebih', 'blue'],
                ['tools.pdfutilities',      'fa-file-pdf',            'PDF Utilities',       'Merge, split & compress',  'blue'],
                ['tools.mediadownloader',   'fa-cloud-arrow-down',    'Media Downloader',    'YouTube, TikTok & IG',     'purple'],
                ['tools.passwordgenerator', 'fa-key',                 'Password Generator',  'Kata sandi kuat & aman',   'purple'],
                ['tools.sanitizer',         'fa-shield-halved',       'File Security & Privacy Scanner',  'Hapus EXIF, GPS & data tersembunyi',   'purple'],
                ['tools.finance',           'fa-chart-pie',           'Pencatatan Keuangan',      'Catat pemasukan & pengeluaran', 'amber'],
            ];
            @endphp

            <div class="relative" id="toolsWrap">
                <!-- <button id="toolsBtn"
                        class="nav-tools-btn"
                        onclick="toggleToolsMenu()"
                        aria-haspopup="true"
                        aria-expanded="false">
                    <i class="fa-solid fa-grid-2 text-[11px]" style="color:var(--text-3)"></i>
                    <span>Tools</span>
                    <i class="fa-solid fa-chevron-down nav-tools-chevron"></i>
                </button> -->

                <div id="toolsDropdown" class="nav-dropdown" role="menu">

                    {{-- Grid of tools --}}
                    <div class="nav-dropdown-grid">
                        @foreach($navTools as [$route, $icon, $name, $desc, $color])
                        <a href="{{ route($route) }}"
                           class="nav-tool-item"
                           role="menuitem">
                            <div class="nav-tool-icon {{ $color }}">
                                <i class="fa-solid {{ $icon }}"></i>
                            </div>
                            <div class="nav-tool-info">
                                <p>{{ $name }}</p>
                                <span>{{ $desc }}</span>
                            </div>
                        </a>
                        @endforeach
                    </div>

                    {{-- Footer --}}
                    <div class="nav-dropdown-footer">
                        <span style="font-size:12px; color:var(--text-3);">
                            <i class="fa-solid fa-sparkles" style="color:var(--accent); margin-right:5px;"></i>
                            10 tools aktif · terus bertambah
                        </span>
                        <button onclick="openSearch();"
                                style="display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:700;color:var(--accent);background:none;border:none;cursor:pointer;padding:6px 10px;border-radius:8px;transition:background 0.2s;"
                                onmouseover="this.style.background='rgba(163,230,53,0.1)'"
                                onmouseout="this.style.background='none'">
                            <i class="fa-solid fa-magnifying-glass text-[10px]"></i>
                            Cari tools
                        </button>
                    </div>

                </div>
            </div>

            <a href="{{ route('home') }}#tools"   class="nav-link">Semua Tools</a>
            <a href="{{ route('home') }}#about"   class="nav-link">Tentang Kami</a>
            <a href="{{ route('home') }}#contact"  class="nav-link">Kontak</a>

        </div>

        {{-- ── SEARCH ── --}}
        <button class="nav-search" onclick="openSearch()" aria-label="Cari tools">
            <i class="fa-solid fa-magnifying-glass" style="font-size:12px; color:var(--text-3);"></i>
            <span>Cari tools...</span>
            <kbd>⌘K</kbd>
        </button>

        {{-- ── RIGHT ACTIONS ── --}}
        <div class="nav-actions">
            @guest
                <a href="{{ route('login') }}" class="btn-ghost">Masuk</a>
                <a href="{{ route('register') }}" class="btn-primary">
                    Mulai Gratis
                    <i class="fa-solid fa-arrow-right" style="font-size:10px;"></i>
                </a>
            @endguest

            @auth
                <div class="nav-user-pill">
                    <div class="nav-user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    <span class="nav-user-name">{{ Str::limit(Auth::user()->name, 16) }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn-ghost" style="color:var(--text-3);"
                            title="Keluar">
                        <i class="fa-solid fa-right-from-bracket" style="font-size:12px;"></i>
                    </button>
                </form>
            @endauth
        </div>

        {{-- ── MOBILE TOGGLE ── --}}
        <button class="nav-mobile-toggle md:hidden"
                id="mobileToggle"
                onclick="toggleMobileMenu()"
                aria-label="Buka menu"
                style="display:none; margin-left:8px;">
            <i class="fa-solid fa-bars text-sm" id="menuIconOpen"></i>
            <i class="fa-solid fa-xmark text-sm" id="menuIconClose" style="display:none;"></i>
        </button>

    </div>
</nav>

{{-- ── MOBILE MENU ── --}}
<div id="mobileMenu" class="mobile-menu hidden" aria-label="Menu mobile">

    <p class="mobile-section-title">Navigasi</p>
    <a href="{{ route('home') }}" class="mobile-nav-link">
        <span class="icon"><i class="fa-solid fa-house" style="font-size:12px;color:var(--text-3)"></i></span>
        Beranda
    </a>
    <button onclick="closeMobileMenu(); openSearch();"
            class="mobile-nav-link"
            style="background:none;border:none;cursor:pointer;width:100%;text-align:left;">
        <span class="icon"><i class="fa-solid fa-magnifying-glass" style="font-size:12px;color:var(--text-3)"></i></span>
        Cari Tools...
    </button>

    <p class="mobile-section-title" style="margin-top:8px;">Tools</p>
    @foreach($navTools as [$route, $icon, $name, $desc, $color])
    <a href="{{ route($route) }}" class="mobile-nav-link">
        <span class="icon"><i class="fa-solid {{ $icon }}" style="font-size:12px;color:var(--text-3)"></i></span>
        {{ $name }}
    </a>
    @endforeach

    <p class="mobile-section-title" style="margin-top:8px;">Umum</p>
    <a href="{{ route('home') }}#about"   class="mobile-nav-link">
        <span class="icon"><i class="fa-solid fa-users" style="font-size:11px;color:var(--text-3)"></i></span>
        Tentang Kami
    </a>
    <a href="{{ route('home') }}#contact" class="mobile-nav-link">
        <span class="icon"><i class="fa-solid fa-envelope" style="font-size:11px;color:var(--text-3)"></i></span>
        Kontak
    </a>

    <div class="mobile-auth-row">
        @guest
            <a href="{{ route('login') }}"    class="btn-outline" style="text-align:center;justify-content:center;">Masuk</a>
            <a href="{{ route('register') }}" class="btn-primary">
                    Mulai Gratis
                    <i class="fa-solid fa-arrow-right" style="font-size:10px;"></i>
                </a>
        @endguest
        @auth
            <div style="display:flex;align-items:center;gap:10px;grid-column:1/-1;padding:12px;background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--r-lg);">
                <div class="nav-user-avatar" style="width:38px;height:38px;font-size:14px;">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div>
                    <p style="font-size:13px;font-weight:700;">{{ Auth::user()->name }}</p>
                    <p style="font-size:11px;color:var(--text-3);">{{ Auth::user()->email }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="grid-column:1/-1;">
                @csrf
                <button type="submit"
                        style="width:100%;padding:12px;border-radius:var(--r-md);background:rgba(239,68,68,0.08);color:#f87171;border:1px solid rgba(239,68,68,0.15);font-size:13px;font-weight:700;cursor:pointer;transition:all 0.2s;">
                    <i class="fa-solid fa-right-from-bracket" style="margin-right:6px;"></i>
                    Keluar dari Akun
                </button>
            </form>
        @endauth
    </div>
</div>

{{-- ================================================================
     SEARCH OVERLAY
================================================================ --}}
<div id="searchOverlay" class="search-overlay" role="dialog" aria-modal="true" aria-label="Cari Tools">

    <div class="search-backdrop" onclick="closeSearch()"></div>

    <div class="search-modal">

        {{-- Input --}}
        <div class="search-input-row">
            <i class="fa-solid fa-magnifying-glass search-input-icon"></i>
            <input type="text" id="searchInput"
                   class="search-input"
                   placeholder="Cari tools... (contoh: PDF, gambar, password)"
                   autocomplete="off" spellcheck="false">
            <span class="search-kbd" onclick="closeSearch()">Esc</span>
        </div>

        {{-- Body --}}
        <div class="search-body" id="searchBody">

            {{-- Browse (default) --}}
            <div id="searchBrowse">
                @php
                $searchCats = [
                    ['amber', 'fa-file-alt', 'Dokumen & Bisnis', [
                        ['tools.invoice',     'fa-file-invoice-dollar', 'amber', 'Invoice Generator',   'Buat tagihan profesional',            ['invoice','tagihan','billing']],
                        ['tools.pdfutilities','fa-file-pdf',            'amber',  'PDF Utilities',        'Merge, split & compress PDF',         ['pdf','merge','split','compress']],
                        ['tools.fileconverter','fa-repeat',             'amber',  'File Converter',       'Konversi PDF, Word, Excel, PPT',       ['pdf','word','excel','convert']],
                        ['tools.finance',    'fa-chart-pie',            'amber', 'Pencatatan Keuangan',       'Catat pemasukan & pengeluaran',               ['catatan keuangan','finance','catat pemasukan','catat pengeluaran']],
                    ]],
                    ['blue', 'fa-image', 'Gambar & Media', [
                        ['tools.imageconverter','fa-image',   'blue',  'Image Converter',    'Resize, compress & convert gambar',   ['gambar','image','foto','resize','compress','webp','png','jpg']],
                        ['tools.bgremover',    'fa-scissors', 'blue',  'Background Remover', 'Hapus background foto AI otomatis',   ['background','hapus','foto','remover','ai']],
                        ['tools.fotobox',      'fa-camera-retro','blue', 'FotoBox',            'Photo booth seru & gratis di browser',['fotobox','photo booth','selfie','foto','kamera','camera','booth']],
                    ]],
                    ['purple','fa-share-nodes','Sosial & Link', [
                        ['tools.mediadownloader','fa-cloud-arrow-down','purple','Media Downloader',   'Download YouTube, TikTok & Instagram', ['download','youtube','tiktok','instagram','video','mp3','mp4']],
                        ['tools.linktree',      'fa-link',             'purple','LinkTree Builder',   'Satu halaman untuk semua linkmu',      ['link','linktree','sosial','bio']],
                        ['tools.qr',            'fa-qrcode',           'purple','QR Code Generator',  'QR Code custom & branded',             ['qr','qrcode','barcode','scan']],
                    ]],
                    ['green','fa-shield-halved','Keamanan & Branding', [
                        ['tools.sanitizer','fa-shield-halved',    'green','File Security & Privacy Scanner','Hapus EXIF, GPS & data tersembunyi',   ['Hapus', 'exif','gps', 'data tersembunyi','keamanan','security']],
                        ['tools.passwordgenerator','fa-key',      'green','Password Generator','Kata sandi kuat & aman instan',        ['password','kata sandi','keamanan','security']],
                        ['tools.signature',       'fa-signature', 'green','Email Signature',   'Tanda tangan email profesional',       ['signature','email','tanda tangan','branding']],
                    ]],
                ];
                @endphp

                @foreach($searchCats as [$catColor, $catIcon, $catName, $catTools])
                <div style="margin-bottom:16px;">
                    <p class="search-cat-label">
                        <i class="fa-solid {{ $catIcon }}"></i>
                        {{ $catName }}
                    </p>
                    <div>
                        @foreach($catTools as [$route, $icon, $color, $name, $desc, $tags])
                        <a href="{{ route($route) }}"
                           class="search-tool-row"
                           data-name="{{ strtolower($name) }}"
                           data-tags="{{ implode(' ', $tags) }}"
                           onclick="closeSearch()">
                            <div class="search-tool-ico {{ $color }}">
                                <i class="fa-solid {{ $icon }}"></i>
                            </div>
                            <div style="flex:1;min-width:0;">
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

            {{-- Results --}}
            <div id="searchResults" class="hidden">
                <p class="search-cat-label">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Hasil Pencarian
                </p>
                <div id="searchResultsGrid"></div>
                <div id="searchEmpty" class="search-empty hidden">
                    <i class="fa-regular fa-face-meh"></i>
                    <p>Tidak ada tools yang cocok — coba kata lain.</p>
                </div>
            </div>

        </div>

        {{-- Hints --}}
        <div class="search-footer">
            <span><kbd style="padding:2px 6px;background:rgba(255,255,255,0.06);border:1px solid var(--border);border-radius:4px;font-size:10px;color:var(--text-3);">↵</kbd> Buka</span>
            <span><kbd style="padding:2px 6px;background:rgba(255,255,255,0.06);border:1px solid var(--border);border-radius:4px;font-size:10px;color:var(--text-3);">↑↓</kbd> Navigasi</span>
            <span><kbd style="padding:2px 6px;background:rgba(255,255,255,0.06);border:1px solid var(--border);border-radius:4px;font-size:10px;color:var(--text-3);">Esc</kbd> Tutup</span>
        </div>

    </div>
</div>

{{-- Inline responsive override --}}
<style>
@media (max-width: 768px) {
    #mobileToggle { display: flex !important; }
}
</style>

@push('scripts')
<script>
/* ── Close menu on outside mobile tap ── */
document.addEventListener('click', function(e) {
    var mobileMenu = document.getElementById('mobileMenu');
    var mobileToggle = document.getElementById('mobileToggle');
    if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
        if (!mobileMenu.contains(e.target) && !mobileToggle.contains(e.target)) {
            window.closeMobileMenu && window.closeMobileMenu();
        }
    }
});
</script>
@endpush