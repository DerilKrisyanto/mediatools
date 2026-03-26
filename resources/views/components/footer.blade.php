{{-- ============================================================
     MEDIATOOLS — FOOTER
     resources/views/components/footer.blade.php
     ============================================================ --}}

<footer class="relative overflow-hidden" style="background:var(--secondary-bg); border-top:1px solid var(--border);">

    {{-- Decorative glows --}}
    <div class="glow-line" style="top:0;"></div>
    <div class="blob" style="width:600px;height:600px;bottom:-300px;left:-200px;opacity:0.25;pointer-events:none;"></div>
    <div class="blob" style="width:400px;height:400px;top:-100px;right:-100px;opacity:0.15;pointer-events:none;"></div>

    {{-- ══ NEWSLETTER BAND ══ --}}
    <div class="relative z-10 border-b" style="border-color:var(--border);">
        <div class="max-w-7xl mx-auto px-6 py-10">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="text-center md:text-left">
                    <div class="inline-flex items-center gap-2 mb-2">
                        <span class="w-2 h-2 rounded-full animate-pulse" style="background:var(--accent);"></span>
                        <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--accent);">Newsletter</span>
                    </div>
                    <h3 class="text-lg font-extrabold text-white">
                        Tips & update fitur terbaru —
                        <span class="gradient-text">langsung ke inbox Anda.</span>
                    </h3>
                    <p class="text-gray-500 text-sm mt-1">
                        Bergabung dengan <strong class="text-gray-400">5.000+</strong> subscriber.
                        Tidak ada spam. Berhenti kapan saja.
                    </p>
                </div>
                <form class="flex w-full md:w-auto gap-0 min-w-[320px]"
                      onsubmit="handleNewsletterSubmit(event)" novalidate>
                    @csrf
                    <input type="email"
                           name="newsletter_email"
                           placeholder="email@kamu.com"
                           required
                           class="form-input flex-1 text-sm"
                           style="border-radius:var(--radius-md) 0 0 var(--radius-md); border-right:none;">
                    <button type="submit"
                            id="newsletter-btn"
                            class="btn-primary px-6 text-sm font-bold flex-shrink-0"
                            style="border-radius:0 var(--radius-md) var(--radius-md) 0;">
                        <span id="newsletter-btn-text">Gabung</span>
                        <i class="fa-solid fa-arrow-right text-xs" id="newsletter-btn-icon"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ══ MAIN FOOTER GRID ══ --}}
    <div class="relative z-10 max-w-7xl mx-auto px-6 py-16">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-6">

            {{-- ── BRAND COLUMN (4 cols) ── --}}
            <div class="sm:col-span-2 lg:col-span-4 space-y-6">

                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 group w-fit">
                    <div class="w-9 h-9 transition-transform duration-300 group-hover:scale-110">
                        <img src="{{ asset('images/icons-mediatools.png') }}"
                             alt="MediaTools Logo"
                             class="w-full h-full object-contain">
                    </div>
                    <span class="text-[17px] font-extrabold tracking-tight text-white">
                        MEDIA<span style="color:var(--accent)">TOOLS.</span>
                    </span>
                </a>

                <p class="text-gray-400 text-sm leading-relaxed max-w-[280px]">
                    Platform serba guna untuk kebutuhan produktivitas digital Anda.
                    Invoice, QR Code, hapus background foto, konversi PDF, dan banyak lagi —
                    semua gratis untuk semua orang.
                </p>

                {{-- Trust badges --}}
                <div class="flex flex-wrap gap-2">
                    @foreach([
                        ['fa-shield-halved', 'Data Aman'],
                        ['fa-bolt',          'Tanpa Instalasi'],
                        ['fa-star',          'Rating 4.9/5'],
                        ['fa-infinity',      '100% Gratis'],
                    ] as [$icon, $label])
                    <span class="footer-badge">
                        <i class="fa-solid {{ $icon }}" style="color:var(--accent); font-size:9px;"></i>
                        {{ $label }}
                    </span>
                    @endforeach
                </div>

                {{-- Social --}}
                <div>
                    <p class="text-xs font-bold text-gray-600 uppercase tracking-widest mb-3">Ikuti Kami</p>
                    <div class="flex gap-2">
                        @foreach([
                            ['fa-instagram', '#', 'Instagram'],
                            ['fa-x-twitter', '#', 'Twitter/X'],
                            ['fa-linkedin-in','#', 'LinkedIn'],
                            ['fa-tiktok',    '#', 'TikTok'],
                            ['fa-github',    '#', 'GitHub'],
                        ] as [$icon, $href, $label])
                        <a href="{{ $href }}"
                           aria-label="{{ $label }}"
                           class="footer-social-btn">
                            <i class="fa-brands {{ $icon }} text-sm"></i>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Spacer --}}
            <div class="hidden lg:block lg:col-span-1"></div>

            {{-- ── TOOLS COLUMNS (split per category) ── --}}

            {{-- Buat & Kelola --}}
            <div class="lg:col-span-2">
                <h4 class="footer-col-title">
                    <i class="fa-solid fa-wand-magic-sparkles" style="color:var(--accent); font-size:10px;"></i>
                    Buat & Kelola
                </h4>
                <ul class="footer-link-list">
                    @foreach([
                        ['tools.invoice',   'Invoice Generator'],
                        ['tools.signature', 'Email Signature'],
                        ['tools.qr',        'QR Code Generator'],
                        ['tools.linktree',  'Link Tree'],
                    ] as [$route, $label])
                    <li>
                        <a href="{{ route($route) }}" class="footer-link group/fl">
                            <span class="footer-link-dot group-hover/fl:bg-[#a3e635]"></span>
                            {{ $label }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- File & Dokumen --}}
            <div class="lg:col-span-2">
                <h4 class="footer-col-title">
                    <i class="fa-solid fa-folder-open" style="color:#60a5fa; font-size:10px;"></i>
                    File & Dokumen
                </h4>
                <ul class="footer-link-list">
                    @foreach([
                        ['tools.pdfutilities',  'PDF Toolkit'],
                        ['tools.fileconverter', 'File Converter'],
                        ['tools.imageconverter','Image Converter'],
                    ] as [$route, $label])
                    <li>
                        <a href="{{ route($route) }}" class="footer-link group/fl">
                            <span class="footer-link-dot group-hover/fl:bg-[#60a5fa]"></span>
                            {{ $label }}
                        </a>
                    </li>
                    @endforeach
                </ul>

                {{-- Extra separator --}}
                <h4 class="footer-col-title mt-8">
                    <i class="fa-solid fa-photo-film" style="color:#a78bfa; font-size:10px;"></i>
                    Konten & Media
                </h4>
                <ul class="footer-link-list">
                    @foreach([
                        ['tools.bgremover',       'Background Remover'],
                        ['tools.mediadownloader', 'Media Downloader'],
                        ['tools.passwordgenerator','Password Generator'],
                    ] as [$route, $label])
                    <li>
                        <a href="{{ route($route) }}" class="footer-link group/fl">
                            <span class="footer-link-dot group-hover/fl:bg-[#a78bfa]"></span>
                            {{ $label }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Support & Company --}}
            <div class="lg:col-span-3">
                <h4 class="footer-col-title">
                    <i class="fa-solid fa-building" style="color:var(--text-muted); font-size:10px;"></i>
                    Perusahaan
                </h4>
                <ul class="footer-link-list">
                    @foreach([
                        [route('home') . '#about',   'Tentang Kami'],
                        [route('home') . '#contact', 'Hubungi Kami'],
                        ['#',                        'Blog & Tutorial'],
                        ['#',                        'Update Fitur'],
                        ['#',                        'Roadmap'],
                    ] as [$href, $label])
                    <li>
                        <a href="{{ $href }}" class="footer-link group/fl">
                            <span class="footer-link-dot"></span>
                            {{ $label }}
                        </a>
                    </li>
                    @endforeach
                </ul>

                <h4 class="footer-col-title mt-8">
                    <i class="fa-solid fa-headset" style="color:var(--text-muted); font-size:10px;"></i>
                    Dukungan
                </h4>
                <ul class="footer-link-list">
                    @foreach([
                        [route('home') . '#contact', 'Pusat Bantuan'],
                        ['#',                        'Dokumentasi API'],
                        ['#',                        'Status Server'],
                        ['#',                        'Laporan Bug'],
                    ] as [$href, $label])
                    <li>
                        <a href="{{ $href }}" class="footer-link group/fl">
                            <span class="footer-link-dot"></span>
                            {{ $label }}
                        </a>
                    </li>
                    @endforeach
                </ul>

                {{-- Server status pill --}}
                <div class="footer-status-pill mt-6">
                    <span class="footer-status-dot"></span>
                    <span>Semua sistem beroperasi normal</span>
                </div>
            </div>

        </div>
    </div>

    {{-- ══ BOTTOM BAR ══ --}}
    <div class="relative z-10 border-t" style="border-color:var(--border);">
        <div class="max-w-7xl mx-auto px-6 py-5
                    flex flex-col sm:flex-row items-center justify-between gap-3">

            {{-- Legal links --}}
            <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5">
                @foreach([
                    ['#', 'Kebijakan Privasi'],
                    ['#', 'Syarat & Ketentuan'],
                    ['#', 'Kebijakan Cookie'],
                    [route('sitemap'), 'Sitemap'],
                ] as [$href, $label])
                <a href="{{ $href }}"
                   class="text-xs text-gray-600 hover:text-gray-400 transition-colors">
                    {{ $label }}
                </a>
                @endforeach
            </div>

            {{-- Copyright --}}
            <p class="text-xs text-gray-600 text-center">
                © {{ date('Y') }} MediaTools Indonesia.
                Dibuat dengan <i class="fa-solid fa-heart text-red-500 mx-0.5"></i> untuk Indonesia.
            </p>

        </div>
    </div>

</footer>

@push('scripts')
<script>
function handleNewsletterSubmit(e) {
    e.preventDefault();
    const form   = e.target;
    const input  = form.querySelector('input[type="email"]');
    const btn    = document.getElementById('newsletter-btn');
    const label  = document.getElementById('newsletter-btn-text');
    const icon   = document.getElementById('newsletter-btn-icon');
    if (!input.value.trim()) return;

    // Optimistic UI
    label.textContent   = 'Berhasil!';
    icon.className      = 'fa-solid fa-check text-xs';
    btn.disabled        = true;
    btn.style.opacity   = '0.85';
    input.value         = '';

    setTimeout(() => {
        label.textContent = 'Gabung';
        icon.className    = 'fa-solid fa-arrow-right text-xs';
        btn.disabled      = false;
        btn.style.opacity = '';
    }, 3500);
}
</script>
@endpush
