<footer class="relative overflow-hidden" style="background: var(--secondary-bg); border-top: 1px solid var(--border);">

    {{-- Decorative top glow line --}}
    <div class="glow-line" style="top:0;"></div>

    {{-- Background blob --}}
    <div class="blob" style="width:500px;height:500px;bottom:-200px;left:-150px;opacity:0.3;"></div>

    {{-- ══ NEWSLETTER BAND ══ --}}
    <div class="relative z-10 border-b" style="border-color: var(--border);">
        <div class="max-w-7xl mx-auto px-6 py-10">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <h3 class="text-lg font-extrabold text-white">
                        Tips & update fitur terbaru —
                        <span class="gradient-text">langsung ke inbox Anda.</span>
                    </h3>
                    <p class="text-gray-500 text-sm mt-1">Bergabung dengan 5.000+ subscriber. Bisa berhenti kapan saja.</p>
                </div>
                <form class="flex w-full md:w-auto gap-0 min-w-[320px]" onsubmit="handleNewsletterSubmit(event)">
                    @csrf
                    <input type="email"
                           name="newsletter_email"
                           placeholder="email@kamu.com"
                           required
                           class="form-input flex-1 rounded-r-none border-r-0 text-sm"
                           style="border-radius: var(--radius-md) 0 0 var(--radius-md);">
                    <button type="submit"
                            class="btn-primary px-6 text-sm font-bold flex-shrink-0"
                            style="border-radius: 0 var(--radius-md) var(--radius-md) 0;">
                        Gabung
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ══ MAIN FOOTER GRID ══ --}}
    <div class="relative z-10 max-w-7xl mx-auto px-6 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8">

            {{-- Brand Column (5 cols) --}}
            <div class="lg:col-span-4 space-y-6">
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

                <p class="text-gray-400 text-sm leading-relaxed max-w-xs">
                    Platform serba guna untuk kebutuhan produktivitas digital Anda — invoice, QR Code, link tree, dan banyak lagi. Gratis untuk semua orang.
                </p>

                {{-- Trust badges --}}
                <div class="flex flex-wrap gap-3">
                    @foreach([
                        ['fa-shield-halved','Data Aman'],
                        ['fa-bolt','Akses Cepat'],
                        ['fa-star','4.9/5 Rating'],
                    ] as $badge)
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                text-[11px] font-bold text-gray-400
                                border" style="border-color: var(--border); background: rgba(255,255,255,0.02);">
                        <i class="fa-solid {{ $badge[0] }} text-[#a3e635] text-[10px]"></i>
                        {{ $badge[1] }}
                    </div>
                    @endforeach
                </div>

                {{-- Social links --}}
                <div class="flex gap-2">
                    @foreach([
                        ['fa-instagram','#','Instagram'],
                        ['fa-x-twitter','#','Twitter/X'],
                        ['fa-linkedin-in','#','LinkedIn'],
                        ['fa-tiktok','#','TikTok'],
                        ['fa-github','#','GitHub'],
                    ] as $s)
                    <a href="{{ $s[1] }}"
                       aria-label="{{ $s[2] }}"
                       class="w-9 h-9 rounded-xl flex items-center justify-center
                              text-gray-500 transition-all duration-300
                              hover:text-[#a3e635] hover:-translate-y-1"
                       style="background: rgba(255,255,255,0.04); border: 1px solid var(--border);">
                        <i class="fa-brands {{ $s[0] }} text-sm"></i>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Spacer --}}
            <div class="hidden lg:block lg:col-span-1"></div>

            {{-- Tools Column (3 cols) --}}
            <div class="lg:col-span-2">
                <h4 class="text-white font-bold mb-5 text-sm tracking-wide">Alat Populer</h4>
                <ul class="space-y-3">
                    @foreach([
                        ['tools.invoice',   'Invoice Generator'],
                        ['tools.qr',        'QR Code Generator'],
                        ['tools.bgremover', 'Background Remover'],
                        ['tools.linktree',  'LinkTree Builder'],
                        ['tools.signature', 'Email Signature'],
                    ] as [$route, $label])
                    <li>
                        <a href="{{ route($route) }}" class="footer-link flex items-center gap-2 group/link">
                            <span class="w-1 h-1 rounded-full bg-gray-600 group-hover/link:bg-[#a3e635] transition-colors"></span>
                            {{ $label }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Company Column (2 cols) --}}
            <div class="lg:col-span-2">
                <h4 class="text-white font-bold mb-5 text-sm tracking-wide">Perusahaan</h4>
                <ul class="space-y-3">
                    @foreach([
                        ['#about',   'Tentang Kami'],
                        ['#contact', 'Hubungi Kami'],
                        ['#',        'Blog & Edukasi'],
                        ['#',        'Update Fitur'],
                        ['#',        'Karir'],
                    ] as [$href, $label])
                    <li>
                        <a href="{{ $href }}" class="footer-link flex items-center gap-2 group/link">
                            <span class="w-1 h-1 rounded-full bg-gray-600 group-hover/link:bg-[#a3e635] transition-colors"></span>
                            {{ $label }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Support Column (2 cols) --}}
            <div class="lg:col-span-3">
                <h4 class="text-white font-bold mb-5 text-sm tracking-wide">Dukungan</h4>
                <ul class="space-y-3">
                    @foreach([
                        ['#', 'Pusat Bantuan'],
                        ['#', 'Dokumentasi API'],
                        ['#', 'Status Server'],
                        ['#', 'Laporan Bug'],
                    ] as [$href, $label])
                    <li>
                        <a href="{{ $href }}" class="footer-link flex items-center gap-2 group/link">
                            <span class="w-1 h-1 rounded-full bg-gray-600 group-hover/link:bg-[#a3e635] transition-colors"></span>
                            {{ $label }}
                        </a>
                    </li>
                    @endforeach
                </ul>

                {{-- Live status badge --}}
                <div class="mt-6 inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold"
                     style="background: rgba(163,230,53,0.06); border: 1px solid rgba(163,230,53,0.15);">
                    <span class="w-2 h-2 rounded-full bg-[#a3e635] animate-pulse"></span>
                    <span class="text-gray-300">Semua sistem beroperasi normal</span>
                </div>
            </div>

        </div>
    </div>

    {{-- ══ BOTTOM BAR ══ --}}
    <div class="relative z-10 border-t" style="border-color: var(--border);">
        <div class="max-w-7xl mx-auto px-6 py-6
                    flex flex-col sm:flex-row items-center justify-between gap-4">

            {{-- Legal links --}}
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                @foreach([
                    ['#','Kebijakan Privasi'],
                    ['#','Syarat & Ketentuan'],
                    ['#','Kebijakan Cookie'],
                ] as [$href, $label])
                <a href="{{ $href }}"
                   class="text-xs text-gray-600 hover:text-gray-300 transition-colors">
                    {{ $label }}
                </a>
                @endforeach
            </div>

            {{-- Copyright --}}
            <p class="text-xs text-gray-600 text-center">
                © {{ date('Y') }} MediaTools Indonesia. Dibuat dengan
                <i class="fa-solid fa-heart text-red-500 mx-0.5"></i>
                untuk dari Indonesia.
            </p>

        </div>
    </div>

</footer>

@push('scripts')
<script>
function handleNewsletterSubmit(e) {
    e.preventDefault();
    const form  = e.target;
    const input = form.querySelector('input[type="email"]');
    const btn   = form.querySelector('button[type="submit"]');
    const email = input.value.trim();
    if (!email) return;

    // Optimistic UI feedback
    const original = btn.innerHTML;
    btn.innerHTML   = '<i class="fa-solid fa-check text-xs"></i><span>Berhasil!</span>';
    btn.disabled    = true;
    input.value     = '';

    setTimeout(() => {
        btn.innerHTML = original;
        btn.disabled  = false;
    }, 3000);

    // TODO: replace with actual AJAX / Livewire call
    // fetch('/newsletter/subscribe', { method:'POST', ... })
}
</script>
@endpush