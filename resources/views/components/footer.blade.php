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
                <h3 style="color:white">Tips & update terbaru <span class="gradient-text">langsung ke inbox.</span></h3>
                <p>Bergabung dengan <strong style="color:var(--text-2)">5.000+</strong> subscriber · Tidak ada spam · Berhenti kapan saja.</p>
            </div>

            {{-- Guest: tampilkan tombol daftar; Auth: tampilkan form subscribe --}}
            @guest
                <div style="display:flex;flex-direction:column;gap:8px;min-width:280px;max-width:360px;flex:1;">
                    <p style="font-size:12px;color:var(--text-3);line-height:1.5;">
                        <i class="fa-solid fa-lock" style="color:var(--accent);margin-right:4px;font-size:10px;"></i>
                        Daftar akun gratis untuk subscribe newsletter kami.
                    </p>
                    <div style="display:flex;gap:8px;">
                        <a href="{{ route('register') }}"
                           class="btn-primary"
                           style="flex:1;justify-content:center;padding:9px 18px;font-size:13px;">
                            Daftar Gratis
                        </a>
                        <a href="{{ route('login') }}"
                           class="btn-outline"
                           style="padding:9px 18px;font-size:13px;">
                            Masuk
                        </a>
                    </div>
                </div>
            @endguest

            @auth
                <form class="footer-newsletter-form" id="newsletterForm" novalidate>
                    @csrf
                    <input type="email"
                           name="newsletter_email"
                           id="footerNewsletterEmail"
                           placeholder="{{ Auth::user()->email }}"
                           value="{{ Auth::user()->email }}"
                           required
                           class="footer-newsletter-input"
                           autocomplete="email">
                    <button type="submit" class="footer-newsletter-btn" id="newsletterBtn">
                        <span id="newsletterBtnText">Gabung</span>
                    </button>
                </form>
            @endauth
        </div>
    </div>

    {{-- ══ MAIN GRID ══ --}}
    <div class="footer-main">

        {{-- Brand --}}
        <div class="footer-brand">
            <a href="{{ route('home') }}" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
                <img src="{{ asset('images/icons.png') }}"
                     alt="MediaTools"
                     style="width:25px;height:15px;border-radius:5px;">
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
                    ['fa-instagram','#','Instagram'],
                    ['fa-x-twitter','#','Twitter/X'],
                    ['fa-linkedin-in','#','LinkedIn'],
                    ['fa-tiktok','#','TikTok'],
                    ['fa-github','#','GitHub'],
                ] as [$icon, $href, $label])
                <a href="{{ $href }}" class="footer-social" aria-label="{{ $label }}">
                    <i class="fa-brands {{ $icon }}"></i>
                </a>
                @endforeach
            </div>
        </div>

        {{-- Tools --}}
        <div class="footer-col">
            <h4>Tools Populer</h4>
            <div class="footer-links">
                @foreach([
                    ['tools.invoice',           'Invoice Generator'],
                    ['tools.qr',                'QR Code Generator'],
                    ['tools.bgremover',         'Background Remover'],
                    ['tools.linktree',          'LinkTree Builder'],
                    ['tools.signature',         'Email Signature'],
                    ['tools.pdfutilities',      'PDF Utilities'],
                    ['tools.imageconverter',    'Image Converter'],
                    ['tools.fileconverter',     'File Converter'],
                    ['tools.mediadownloader',   'Media Downloader'],
                    ['tools.passwordgenerator', 'Password Generator'],
                ] as [$route, $label])
                <a href="{{ route($route) }}" class="footer-link">{{ $label }}</a>
                @endforeach
            </div>
        </div>

        {{-- Company --}}
        <div class="footer-col">
            <h4>Perusahaan</h4>
            <div class="footer-links">
                @foreach([
                    [route('home').'#about',   'Tentang Kami'],
                    [route('home').'#contact', 'Hubungi Kami'],
                    ['#', 'Blog & Tutorial'],
                    ['#', 'Update Fitur'],
                    ['#', 'Roadmap'],
                    ['#', 'Karir'],
                ] as [$href, $label])
                <a href="{{ $href }}" class="footer-link">{{ $label }}</a>
                @endforeach
            </div>
        </div>

        {{-- Support --}}
        <div class="footer-col">
            <h4>Dukungan</h4>
            <div class="footer-links">
                @foreach([
                    [route('home').'#contact', 'Pusat Bantuan'],
                    ['#', 'Dokumentasi API'],
                    ['#', 'Status Server'],
                    ['#', 'Laporan Bug'],
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
                © {{ date('Y') }} MediaTools Indonesia ·
                Dibuat dengan <i class="fa-solid fa-heart" style="color:#f87171;margin:0 2px;"></i> untuk Indonesia
            </p>
        </div>
    </div>

</footer>

@push('scripts')
<script>
(function () {
    /* ── Newsletter form (Auth only — guest sudah di-handle Blade) ── */
    var form = document.getElementById('newsletterForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn   = document.getElementById('newsletterBtn');
        var label = document.getElementById('newsletterBtnText');
        var email = document.getElementById('footerNewsletterEmail');
        if (!email || !email.value.trim()) return;

        label.textContent = '...';
        btn.disabled = true;

        fetch('{{ route("newsletter.subscribe") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ newsletter_email: email.value.trim() }),
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            }
            label.textContent    = '✓ Berhasil!';
            btn.style.background = '#22c55e';
            setTimeout(function () {
                label.textContent    = 'Gabung';
                btn.disabled         = false;
                btn.style.background = '';
            }, 3000);
        })
        .catch(function () {
            label.textContent = 'Error';
            btn.disabled      = false;
            setTimeout(function () { label.textContent = 'Gabung'; }, 2000);
        });
    });
})();
</script>
@endpush