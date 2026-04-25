{{-- ============================================================
     MEDIATOOLS — FOOTER
     resources/views/components/footer.blade.php
     ============================================================ --}}

<footer>

    {{-- ══ NEWSLETTER ══ --}}
    <div class="footer-newsletter">
        <div class="footer-newsletter-inner">
            <div class="footer-newsletter-text">
                <div style="display:inline-flex;align-items:center;gap:6px;margin-bottom:6px;">
                    <span style="width:6px;height:6px;border-radius:50%;background:var(--accent);animation:pulse-dot 2.5s infinite;display:inline-block;"></span>
                    <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.12em;color:var(--accent);">Newsletter</span>
                </div>
                <h3>Tips & update terbaru <span class="gradient-text">langsung ke inbox.</span></h3>
                <p>Bergabung dengan <strong style="color:var(--text-2)">5.000+</strong> subscriber · Tidak ada spam · Berhenti kapan saja.</p>
            </div>
            <form class="footer-newsletter-form" onsubmit="handleNewsletterSubmit(event)" novalidate>
                @csrf
                <input type="email"
                       name="newsletter_email"
                       placeholder="email@kamu.com"
                       required
                       class="footer-newsletter-input"
                       autocomplete="email">
                <button type="submit" class="footer-newsletter-btn" id="newsletterBtn">
                    <span id="newsletterBtnText">Gabung</span>
                </button>
            </form>
        </div>
    </div>

    {{-- ══ MAIN GRID ══ --}}
    <div class="footer-main">

        {{-- Brand --}}
        <div class="footer-brand">
            <a href="{{ route('home') }}" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
                <img src="{{ asset('images/icons-mediatools.png') }}"
                     alt="MediaTools"
                     style="width:28px;height:28px;border-radius:7px;">
                <span class="nav-logo-text">MEDIA<em style="font-style:normal;color:var(--accent)">TOOLS.</em></span>
            </a>
            <p class="footer-brand-desc">
                Platform tools produktivitas digital 100% gratis.
                Invoice, QR Code, PDF, hapus background, dan banyak lagi.
                Dibuat untuk UMKM, freelancer, dan kreator Indonesia.
            </p>

            <div class="footer-badges">
                @foreach([
                    ['fa-shield-halved','Privasi Terjaga'],
                    ['fa-bolt','Tanpa Instalasi'],
                    ['fa-infinity','100% Gratis'],
                ] as [$icon, $label])
                <span class="footer-badge">
                    <i class="fa-solid {{ $icon }}" style="color:var(--accent);font-size:9px;"></i>
                    {{ $label }}
                </span>
                @endforeach
            </div>

            <div class="footer-socials">
                @foreach([
                    ['fa-instagram','https://www.instagram.com/halo.mediatools/','Instagram'],
                    ['fa-tiktok','#','TikTok'],
                    ['fa-github','#','GitHub'],
                ] as [$icon, $href, $label])
                <a href="{{ $href }}" class="footer-social" aria-label="{{ $label }}">
                    <i class="fa-brands {{ $icon }}"></i>
                </a>
                @endforeach
            </div>
        </div>

        {{-- Tools Poluler --}}
        <div class="footer-col">
            <h4>Tools Populer & Baru</h4>
            <div class="footer-links">
                @foreach([
                    ['tools.linktree',          'LinkTree Builder'],
                    ['tools.fileconverter',     'File Converter'],
                    ['tools.pdfutilities',      'PDF Utilities'],
                    ['tools.mediadownloader',   'Media Downloader'],
                    ['tools.invoice',           'Invoice Generator'],
                    ['tools.sanitizer',         'File Security & Privacy Scanner'],
                    ['tools.finance',           'Pencatatan Keuangan'],
                    ['tools.fotobox',           'FotoBox Photo Booth'],
                ] as [$route, $label])
                <a href="{{ route($route) }}" class="footer-link">{{ $label }}</a>
                @endforeach
            </div>
        </div>

        {{-- All Tools --}}
        <div class="footer-col">
            <h4>Semua Tools</h4>
            <div class="footer-links">
                @foreach([
                    ['tools.linktree',          'LinkTree Builder'],
                    ['tools.fileconverter',     'File Converter'],
                    ['tools.pdfutilities',      'PDF Utilities'],
                    ['tools.bgremover',         'Background Remover'],
                    ['tools.mediadownloader',   'Media Downloader'],
                    ['tools.imageconverter',    'Image Converter'],
                    ['tools.invoice',           'Invoice Generator'],
                    ['tools.sanitizer',         'File Security & Privacy Scanner'],
                    ['tools.passwordgenerator', 'Password Generator'],
                    ['tools.qr',                'QR Code Generator'],
                    ['tools.signature',         'Email Signature'],
                    ['tools.finance',           'Pencatatan Keuangan'],
                    ['tools.fotobox',           'FotoBox Photo Booth'],
                ] as [$route, $label])
                <a href="{{ route($route) }}" class="footer-link">{{ $label }}</a>
                @endforeach
            </div>
        </div>

        {{-- Support --}}
        <div class="footer-col">
            <h4>Perusahaan</h4>
            <div class="footer-links">
                @foreach([
                    [route('home').'#about',   'Tentang Kami'],
                    [route('home').'#contact', 'Hubungi Kami'],
                ] as [$href, $label])
                <a href="{{ $href }}" class="footer-link">{{ $label }}</a>
                @endforeach
            </div>

            {{-- Status pill --}}
            <div class="footer-status">
                <span class="footer-status-dot"></span>
                Semua sistem normal
            </div>
        </div>

    </div>

    {{-- ══ BOTTOM BAR ══ --}}
    <div style="border-top:1px solid var(--border);">
        <div class="footer-bottom">
            <div class="footer-legal">
                @foreach([
                    ['#','Kebijakan Privasi'],
                    ['#','Syarat & Ketentuan'],
                    ['#','Cookie'],
                    [route('sitemap'),'Sitemap'],
                ] as [$href, $label])
                <a href="{{ $href }}" class="footer-legal-link">{{ $label }}</a>
                @endforeach
            </div>
            <p class="footer-copy">
                © {{ date('Y') }} MediaTools Indonesia -
                Dibuat dengan <i class="fa-solid fa-heart" style="color:#f87171;margin:0 2px;"></i> untuk Produktivitas harian anda.
            </p>
        </div>
    </div>

</footer>

@push('scripts')
<script>
function handleNewsletterSubmit(e) {
    e.preventDefault();
    var input  = e.target.querySelector('input[type="email"]');
    var btn    = document.getElementById('newsletterBtn');
    var label  = document.getElementById('newsletterBtnText');
    if (!input || !input.value.trim()) return;

    label.textContent = '✓ Berhasil!';
    btn.disabled      = true;
    btn.style.background = '#22c55e';
    input.value       = '';

    setTimeout(function () {
        label.textContent    = 'Gabung';
        btn.disabled         = false;
        btn.style.background = '';
    }, 3000);
}
</script>
@endpush