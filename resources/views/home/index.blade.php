@extends('layouts.app')

@section('title', 'MediaTools — Tools Digital Gratis: Invoice, PDF, QR Code & Background Remover')
@section('meta_description', 'Platform tools produktivitas digital 100% gratis. Hapus background foto, konversi PDF, buat invoice, QR Code, password generator, dan 10+ tools lainnya. Tanpa daftar, langsung pakai.')
@section('meta_keywords', 'tools online gratis indonesia, invoice generator gratis, hapus background foto, konversi pdf word, qr code generator, media tools, password generator, link tree')

@section('content')

{{-- ================================================================
     HERO
================================================================ --}}
<section class="hero-section">
    <div class="hero-noise"></div>
    <div class="hero-grid"></div>
    <div class="hero-glow"></div>

    <div class="hero-content">
        <div class="hero-badge reveal">
            <span class="hero-badge-dot"></span>
            10+ Tools Gratis
        </div>

        <h1 class="hero-title reveal reveal-d1">
            Semua Tools Produktivitas<br>
            <span class="gradient-text">dalam Satu Tempat.</span>
        </h1>

        <p class="hero-subtitle reveal reveal-d2">
            Dari invoice profesional hingga QR Code bisnis — semua tersedia
            instan di browser Anda, tanpa instalasi, tanpa biaya.
        </p>

        <div class="hero-actions reveal reveal-d3">
            <a href="#tools" class="btn-hero-primary">
                <i class="fa-solid fa-grid-2" style="font-size:13px;"></i>
                Jelajahi Semua Tools
            </a>
        </div>

        <div class="hero-trust reveal reveal-d4">
            <span class="hero-trust-item"><i class="fa-solid fa-check"></i> Gratis selamanya</span>
            <span class="hero-trust-item"><i class="fa-solid fa-check"></i> Tanpa instalasi</span>
            <span class="hero-trust-item"><i class="fa-solid fa-check"></i> Privasi terjaga</span>
            <span class="hero-trust-item"><i class="fa-solid fa-check"></i> Dipercaya 10.000+ pengguna</span>
        </div>
    </div>
</section>

{{-- ================================================================
     STATS BAR
================================================================ --}}
<div class="stats-bar">
    <div class="stats-bar-inner">
        <div class="stat-cell reveal">
            <div class="stat-num" data-target="10" data-suffix="+">0+</div>
            <div class="stat-label">Tools Aktif</div>
        </div>
        <div class="stat-cell reveal reveal-d1">
            <div class="stat-num" data-target="100" data-suffix="Rb+">0</div>
            <div class="stat-label">File Diproses</div>
        </div>
        <div class="stat-cell reveal reveal-d2">
            <div class="stat-num" data-target="98.7" data-suffix="%">0%</div>
            <div class="stat-label">Uptime Server</div>
        </div>
        <div class="stat-cell reveal reveal-d3">
            <div class="stat-num" data-target="4.8" data-suffix="/5">0/5</div>
            <div class="stat-label">Rating Kepuasan</div>
        </div>
    </div>
</div>

{{-- ================================================================
     TOOLS SECTION
================================================================ --}}
<div id="tools" style="max-width:1280px;margin:0 auto;padding:64px 24px;">

    <div class="tools-section-header">
        <div>
            <div class="section-tag" style="margin-bottom:12px;">
                <i class="fa-solid fa-grid-2"></i> Koleksi Tools
            </div>
            <h2 class="tools-section-title reveal">Semua yang Anda Butuhkan</h2>
            <p class="tools-section-sub reveal reveal-d1">Pilih kategori atau gunakan search untuk menemukan tool yang tepat.</p>
        </div>
        <button onclick="openSearch()"
                style="display:inline-flex;align-items:center;gap:8px;padding:9px 18px;background:var(--bg-elevated);border:1px solid var(--border-strong);border-radius:var(--r-md);font-size:13px;font-weight:600;color:var(--text-2);cursor:pointer;transition:all 0.2s;flex-shrink:0;"
                onmouseover="this.style.borderColor='rgba(255,255,255,0.2)';this.style.color='var(--text-1)'"
                onmouseout="this.style.borderColor='var(--border-strong)';this.style.color='var(--text-2)'">
            <i class="fa-solid fa-magnifying-glass" style="font-size:11px;"></i>
            Cari tools...
        </button>
    </div>

    <div class="cat-tabs reveal reveal-d1" id="catTabs" role="tablist">
        <button class="cat-tab active" data-cat="all" onclick="filterTools('all')" role="tab" aria-selected="true">
            <i class="fa-solid fa-border-all"></i> Semua
        </button>
        <button class="cat-tab" data-cat="doc" onclick="filterTools('doc')" role="tab">
            <i class="fa-solid fa-file-lines"></i> Dokumen & Bisnis
        </button>
        <button class="cat-tab" data-cat="image" onclick="filterTools('image')" role="tab">
            <i class="fa-solid fa-image"></i> Gambar & Media
        </button>
        <button class="cat-tab" data-cat="social" onclick="filterTools('social')" role="tab">
            <i class="fa-solid fa-share-nodes"></i> Sosial & Link
        </button>
        <button class="cat-tab" data-cat="security" onclick="filterTools('security')" role="tab">
            <i class="fa-solid fa-shield-halved"></i> Keamanan & Branding
        </button>
    </div>

    @php
    $tools = [
        ['doc',     'fa-file-invoice-dollar', 'rgba(245,158,11,0.12)',  '#fbbf24',       'Invoice Generator',              'Buat tagihan profesional dengan template yang bisa dikustomisasi penuh.',             'BARU', 'invoice',           true],
        ['social',  'fa-link',                'rgba(139,92,246,0.12)',  '#a78bfa',       'LinkTree Builder',               'Satukan semua link penting di satu halaman landing yang elegan.',                   'HOT',  'linktree',          true],
        ['doc',     'fa-qrcode',              'rgba(59,130,246,0.12)',  '#60a5fa',       'QR Code Generator',              'QR Code custom untuk menu, pembayaran, kontak, atau URL bisnis Anda.',              null,   'qr',                true],
        ['security','fa-signature',           'rgba(163,230,53,0.10)', 'var(--accent)', 'Email Signature',                'Tanda tangan email profesional untuk Gmail, Outlook & semua email client.',          null,   'signature',         true],
        ['image',   'fa-scissors',            'rgba(244,63,94,0.12)',   '#fb7185',       'Background Remover',             'Hapus background foto otomatis dengan AI BiRefNet — presisi pada rambut.',           null,   'bgremover',         true],
        ['image',   'fa-image',               'rgba(59,130,246,0.12)',  '#60a5fa',       'Image Converter',                'Resize, compress & convert JPG/PNG/WebP langsung di browser, tanpa upload.',         null,   'imageconverter',    true],
        ['doc',     'fa-repeat',              'rgba(245,158,11,0.12)',  '#fbbf24',       'File Converter',                 'Konversi PDF ke Word, Excel, PPT & sebaliknya. Upload 5 file sekaligus.',            'HOT',  'fileconverter',     true],
        ['doc',     'fa-file-pdf',            'rgba(239,68,68,0.12)',   '#f87171',       'PDF Utilities',                  'Merge, split & compress PDF di browser — tanpa upload ke server.',                   'HOT',  'pdfutilities',      true],
        ['social',  'fa-cloud-arrow-down',    'rgba(139,92,246,0.12)',  '#a78bfa',       'Media Downloader',               'Download video & audio dari YouTube, TikTok, Instagram dalam hitungan detik.',       'BARU', 'mediadownloader',   true],
        ['security','fa-shield-halved',       'rgba(163,230,53,0.10)', 'var(--accent)', 'File Security & Privacy Scanner','Deteksi file berbahaya, malware tersembunyi, hingga ancaman lainnya, hapus metadata.','BARU', 'sanitizer',         true],
        ['security','fa-key',                 'rgba(163,230,53,0.10)', 'var(--accent)', 'Password Generator',             'Buat password kuat & unik secara instan — semua proses di browser Anda.',             null,   'passwordgenerator', true],
        ['doc',     'fa-id-card',             'rgba(100,116,139,0.12)', '#94a3b8',       'Business Card',                  'Kartu nama digital yang bisa dibagikan via link atau QR Code.',                      'SOON', '#',                 false],
    ];
    @endphp

    <div class="tools-grid" id="toolsGrid">
        @foreach($tools as $i => [$cat, $icon, $iconBg, $iconColor, $name, $desc, $badge, $href, $available])
        <a href="{{ $available ? route('tools.'.$href) : '#' }}"
           class="tool-card {{ !$available ? 'coming-soon' : '' }} reveal"
           data-cat="{{ $cat }}"
           style="transition-delay:{{ ($i % 5) * 0.05 }}s;"
           {{ !$available ? 'aria-disabled="true"' : '' }}>
            @if($badge)
            <span class="tool-card-badge {{ strtolower($badge) === 'pro' ? 'pro' : (strtolower($badge) === 'hot' ? 'hot' : 'new') }}">{{ $badge }}</span>
            @endif
            <div class="tool-card-icon" style="background:{{ $iconBg }};color:{{ $iconColor }};"><i class="fa-solid {{ $icon }}"></i></div>
            <p class="tool-card-name">{{ $name }}</p>
            <p class="tool-card-desc">{{ $desc }}</p>
            @if($available)
            <span class="tool-card-cta">Buka Tool <i class="fa-solid fa-arrow-right" style="font-size:10px;"></i></span>
            @else
            <span style="font-size:12px;color:var(--text-4);font-weight:600;">Segera hadir</span>
            @endif
        </a>
        @endforeach
    </div>

</div>

{{-- ================================================================
     WHY US / FEATURES
================================================================ --}}
<section id="about" class="features-section">
    <div class="features-inner">
        <div class="text-center" style="margin-bottom:0;">
            <div class="section-tag" style="margin-bottom:12px;"><i class="fa-solid fa-star"></i> Mengapa MediaTools</div>
            <h2 style="font-size:clamp(1.5rem,3vw,2rem);font-weight:800;letter-spacing:-0.03em;margin-bottom:10px;" class="reveal">
                Dirancang untuk <span class="gradient-text">Kemudahan Anda.</span>
            </h2>
            <p style="color:var(--text-2);max-width:480px;margin:0 auto;font-size:14px;line-height:1.6;" class="reveal reveal-d1">
                Kami membangun tools yang benar-benar gratis, cepat, dan menghormati privasi data Anda.
            </p>
        </div>
        <div class="features-grid">
            @foreach([
                ['fa-bolt',          'Cepat & Instan',              'Proses selesai dalam hitungan detik. Tidak ada loading yang membuang waktu Anda.'],
                ['fa-mobile-screen', 'Responsif di Semua Perangkat','Gunakan di desktop, tablet, maupun smartphone dengan pengalaman yang sama baiknya.'],
                ['fa-shield-halved', 'Privasi Terjaga',             'File gambar & dokumen diproses dan langsung dihapus. Kami tidak menyimpan data Anda.'],
                ['fa-infinity',      '100% Gratis',                 'Semua berbasis browser. Tidak perlu mengunduh, menginstal. Fitur utama seluruh tools bisa digunakan secara gratis.'],
                ['fa-headset',       'Dukungan Responsif',          'Ada pertanyaan? Tim kami siap membantu via email & chat pada hari kerja.'],
            ] as $i => [$icon, $title, $desc])
            <div class="feature-card reveal" style="transition-delay:{{ $i * 0.07 }}s;">
                <div class="feature-icon"><i class="fa-solid {{ $icon }}"></i></div>
                <p class="feature-title">{{ $title }}</p>
                <p class="feature-desc">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ================================================================
     HOW IT WORKS
================================================================ --}}
<section class="steps-section">
    <div class="section-tag" style="margin-bottom:12px;"><i class="fa-solid fa-list-ol"></i> Cara Pakai</div>
    <h2 style="font-size:clamp(1.5rem,3vw,2rem);font-weight:800;letter-spacing:-0.03em;margin-bottom:8px;" class="reveal">Mulai dalam 4 Langkah</h2>
    <p style="color:var(--text-2);font-size:14px;" class="reveal reveal-d1">Semudah itu — tidak perlu panduan panjang.</p>
    <div class="steps-grid">
        @foreach([
            ['Pilih Tool yang Tepat',  'Temukan tool dari 10+ pilihan kami yang terus bertambah. Gunakan filter kategori atau search.'],
            ['Input atau Upload File', 'Isi formulir sederhana, atau drag & drop file yang ingin Anda proses.'],
            ['Preview & Sesuaikan',    'Lihat hasilnya secara real-time dan sesuaikan sesuai kebutuhan Anda.'],
            ['Download & Gunakan',     'Unduh hasil dalam format yang langsung siap pakai. Gratis, tanpa watermark.'],
        ] as $i => [$title, $desc])
        <div class="step-item reveal" style="transition-delay:{{ $i * 0.1 }}s;">
            <div class="step-number">{{ $i + 1 }}</div>
            <p class="step-title">{{ $title }}</p>
            <p class="step-desc">{{ $desc }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- ================================================================
     TESTIMONIALS
================================================================ --}}
<section class="testimonials-section">
    <div class="testimonials-inner">

        <div class="text-center" style="margin-bottom:0;">
            <div class="section-tag" style="margin-bottom:12px;">
                <i class="fa-solid fa-comments"></i> Ulasan Pengguna
            </div>
            <h2 style="font-size:clamp(1.5rem,3vw,2rem);font-weight:800;letter-spacing:-0.03em;" class="reveal">
                Dipercaya Ribuan Pengguna
            </h2>
            <p style="color:var(--text-2);margin-top:8px;font-size:14px;" class="reveal reveal-d1">
                Inilah yang mereka katakan tentang MediaTools.
            </p>
        </div>

        <div class="testimonials-grid">
            @php
            $reviews = [
                ['Aditya P.', 'Freelance Designer',    'https://i.pravatar.cc/80?u=11', 5, 'Awalnya coba-coba pakai invoice generator di sini, ternyata kepakai terus. Tinggal isi data, langsung jadi. Cuma kadang saya butuh lebih banyak pilihan template 😄'],
                ['Sinta R.',  'Content Creator',       'https://i.pravatar.cc/80?u=22', 5, 'Pakai Linktreenya buat sosmed, lumayan membantu sih. Cara buatnya mudah dan cepat (nggak ribet). Tampilannya juga keren untuk skala gratis ya'],
                ['Budi S.',   'UMKM Kuliner',          'https://i.pravatar.cc/80?u=33', 4, 'QR code untuk menu cukup membantu di warung saya. Pelanggan jadi nggak perlu tanya-tanya. Cuma sempat bingung di awal setting-nya, tapi setelah itu oke'],
                ['Rina M.',   'HR',                     'https://i.pravatar.cc/80?u=44', 5, 'Saya pakai background remover untuk foto karyawan. Hasilnya rapi, terutama bagian rambut. Nggak nyangka bisa sebagus ini tanpa edit manual'],
                ['Deni K.',   'Digital Marketing',      'https://i.pravatar.cc/80?u=55', 5, 'Sering merge PDF proposal di sini. Praktis banget karena nggak perlu install apa-apa. Tinggal upload, selesai.'],
                ['Maya T.',   'Graphic Designer',      'https://i.pravatar.cc/80?u=66', 4, 'Image converter-nya simpel, cocok buat compress gambar web. Ukuran jadi lebih ringan, walau kadang kualitas sedikit turun (wajar sih)'],
                ['Astrid',   'Freelancer MUA',         'https://i.pravatar.cc/80?u=66', 4, 'Untuk Pencatatan Keuangan lumayan bagus, meskipun hanya berfokus pada pemasukan dan pengeluaran tapi untuk freelancer sy rasa ini sudah cukup'],
            ];
            @endphp

            @foreach($reviews as $i => [$name, $role, $avatar, $rating, $text])
            <div class="testimonial-card reveal" style="transition-delay:{{ $i * 0.08 }}s;">
                <div class="testimonial-stars">
                    @for($s=0; $s < $rating; $s++)
                        <i class="fa-solid fa-star testimonial-star"></i>
                    @endfor
                </div>
                <p class="testimonial-text">"{{ $text }}"</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar"
                        style="width:38px;height:38px;border-radius:50%;background:var(--accent-dim);border:1px solid var(--accent-border);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:var(--accent);">
                        {{ strtoupper(substr($name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="testimonial-name">{{ $name }}</p>
                        <p class="testimonial-role">{{ $role }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</section>

{{-- ================================================================
     CONTACT
================================================================ --}}
<section id="contact" class="contact-section">

    <div style="margin-bottom:40px;">
        <div class="section-tag" style="margin-bottom:12px;"><i class="fa-solid fa-envelope"></i> Kontak</div>
        <h2 style="font-size:clamp(1.5rem,3vw,2rem);font-weight:800;letter-spacing:-0.03em;" class="reveal">Ada Pertanyaan?</h2>
        <p style="color:var(--text-2);margin-top:8px;font-size:14px;" class="reveal reveal-d1">Tim kami siap membantu Anda kapan saja.</p>
    </div>

    <div class="contact-card reveal">

        {{-- Info side --}}
        <div class="contact-info">
            <div>
                <h3 style="font-size:18px;font-weight:700;margin-bottom:8px;">Hubungi Kami</h3>
                <p style="font-size:13px;color:var(--text-2);line-height:1.65;">Punya pertanyaan, masukan, atau ingin berkolaborasi? Kami senang mendengar dari Anda.</p>
            </div>
            <div style="display:flex;flex-direction:column;gap:16px;">
                @foreach([
                    ['fa-envelope',     'Email Support',  'halo@mediatools.cloud'],
                    ['fa-location-dot', 'Lokasi',         'Jakarta, Indonesia'],
                    ['fa-clock',        'Jam Dukungan',   'Senin–Jumat, 09.00–17.00 WIB'],
                ] as [$icon, $label, $val])
                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fa-solid {{ $icon }}"></i></div>
                    <div>
                        <p class="contact-info-label">{{ $label }}</p>
                        <p class="contact-info-val">{{ $val }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            <div>
                <p style="font-size:11px;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-4);margin-bottom:10px;font-weight:700;">Ikuti Kami</p>
                <div style="display:flex;gap:8px;">
                    @foreach([['fa-instagram','#'],['fa-x-twitter','#'],['fa-linkedin-in','#'],['fa-tiktok','#']] as [$icon, $href])
                    <a href="{{ $href }}"
                       style="width:34px;height:34px;border-radius:8px;background:var(--bg-overlay);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--text-3);font-size:13px;text-decoration:none;transition:all 0.2s;"
                       onmouseover="this.style.color='var(--accent)';this.style.borderColor='var(--accent-border)'"
                       onmouseout="this.style.color='var(--text-3)';this.style.borderColor='var(--border)'">
                        <i class="fa-brands {{ $icon }}"></i>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Form side --}}
        <div class="contact-form-side">
            @auth
            {{-- ── PERBAIKAN UTAMA:
                 1. action + method="POST" sebagai fallback jika JS gagal
                 2. JS menangkap submit, preventDefault, lalu AJAX
                 3. Tidak ada lagi form GET ke URL yang sama
            ── --}}
            <form id="contactForm"
                  action="{{ route('contact.send') }}"
                  method="POST"
                  novalidate>
                @csrf

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                    <div>
                        <label class="form-label" for="contact-name">Nama Lengkap</label>
                        <input id="contact-name"  type="text"  name="name"
                               class="form-input"
                               placeholder="Budi Santoso"
                               value="{{ Auth::user()->name }}"
                               required>
                    </div>
                    <div>
                        <label class="form-label" for="contact-email">Email Balasan</label>
                        <input id="contact-email" type="email" name="email"
                               class="form-input"
                               placeholder="budi@email.com"
                               value="{{ Auth::user()->email }}"
                               required>
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label class="form-label" for="contact-subject">Topik</label>
                    <select id="contact-subject" name="subject" class="form-input" required>
                        <option value="">Pilih topik...</option>
                        <option>Pertanyaan Umum</option>
                        <option>Masalah Teknis</option>
                        <option>Kerjasama Bisnis</option>
                        <option>Saran &amp; Masukan</option>
                    </select>
                </div>

                <div style="margin-bottom:20px;">
                    <label class="form-label" for="contact-message">Pesan</label>
                    <textarea id="contact-message" name="message" rows="4"
                              class="form-input" style="resize:none;"
                              placeholder="Ceritakan apa yang ingin Anda sampaikan..."
                              required minlength="10"></textarea>
                </div>

                {{-- Alert area --}}
                <div id="contactAlert"
                     style="display:none;padding:12px 16px;border-radius:var(--r-md);font-size:13px;font-weight:500;line-height:1.5;margin-bottom:16px;">
                </div>

                <button type="submit" id="contactSubmitBtn"
                        style="display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 24px;background:var(--accent);color:#0a0a0b;font-size:14px;font-weight:700;border-radius:var(--r-md);border:none;cursor:pointer;transition:all 0.2s;width:100%;">
                    <i class="fa-solid fa-paper-plane" style="font-size:12px;" id="contactBtnIcon"></i>
                    <span id="contactBtnText">Kirim Pesan</span>
                </button>
            </form>
            @else
            {{-- Guest gate --}}
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;text-align:center;padding:20px 0;gap:20px;">
                <div style="width:64px;height:64px;border-radius:16px;background:var(--accent-dim);border:1px solid var(--accent-border);display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-lock" style="font-size:24px;color:var(--accent);"></i>
                </div>
                <div>
                    <h3 style="font-size:17px;font-weight:700;margin-bottom:8px;">Login untuk Mengirim Pesan</h3>
                    <p style="font-size:13px;color:var(--text-2);line-height:1.65;max-width:320px;">
                        Kami membutuhkan identitas akun Anda agar pesan dapat kami balas ke email yang benar dan terverifikasi.
                    </p>
                </div>
                <div style="display:flex;flex-direction:column;gap:10px;width:100%;max-width:280px;">
                    <a href="{{ route('login') }}"
                       class="btn-hero-primary"
                       style="justify-content:center;width:100%;padding:11px 20px;font-size:14px;">
                        <i class="fa-solid fa-right-to-bracket" style="font-size:12px;"></i>
                        Masuk ke Akun
                    </a>
                    <a href="{{ route('register') }}"
                       class="btn-hero-secondary"
                       style="justify-content:center;width:100%;padding:11px 20px;font-size:14px;">
                        Daftar Gratis — Mudah &amp; Cepat
                    </a>
                </div>
                <p style="font-size:12px;color:var(--text-4);">
                    <i class="fa-solid fa-shield-halved" style="color:var(--accent);margin-right:4px;font-size:10px;"></i>
                    Data Anda aman. Tidak ada spam.
                </p>
            </div>
            @endauth
        </div>

    </div>
</section>

{{-- ================================================================
     FAQ
================================================================ --}}
<section class="faq-section">
    <div style="text-align:center;margin-bottom:0;">
        <div class="section-tag" style="margin-bottom:12px;display:inline-flex;"><i class="fa-solid fa-circle-question"></i> FAQ</div>
        <h2 style="font-size:clamp(1.5rem,3vw,2rem);font-weight:800;letter-spacing:-0.03em;" class="reveal">Pertanyaan Populer</h2>
        <p style="color:var(--text-2);margin-top:8px;font-size:14px;" class="reveal reveal-d1">Semua yang perlu Anda ketahui tentang MediaTools.</p>
    </div>
    <div class="faq-list reveal reveal-d2">
        @foreach([
            ['Apakah semua tools benar-benar gratis?',
             'Ya. Semua fitur utama tools kami dapat digunakan gratis tanpa kartu kredit. Beberapa tool premium seperti LinkTree memiliki paket berbayar untuk akses fitur eksklusif, namun dasarnya tetap gratis.'],
            ['Bagaimana keamanan file yang saya upload?',
             'Privasi Anda adalah prioritas kami. File yang diproses di server (seperti background remover & PDF utilities) dihapus otomatis setelah proses selesai. Tool berbasis browser (image converter, password generator) bahkan tidak pernah meninggalkan perangkat Anda.'],
            ['Apakah saya perlu membuat akun?',
             'Tidak, sebagian besar tools bisa langsung digunakan tanpa daftar. Akun gratis diperlukan untuk tools yang menyimpan data seperti LinkTree dan Email Signature.'],
            ['Apakah MediaTools bekerja di smartphone?',
             'Ya! Semua tools didesain mobile-first dan responsif penuh. Pengalaman di smartphone setara dengan di desktop.'],
            ['Apakah ada batasan penggunaan?',
             'Umumnya tidak ada batasan harian untuk tool gratis. Beberapa tool memiliki batas ukuran file (misalnya 20MB per gambar) untuk menjaga performa server tetap optimal bagi semua pengguna.'],
        ] as $i => [$q, $a])
        <div class="faq-item {{ $i === 0 ? 'open' : '' }}">
            <div class="faq-question">
                <span>{{ $q }}</span>
                <span class="faq-icon"><i class="fa-solid fa-plus" style="font-size:10px;"></i></span>
            </div>
            <div class="faq-answer {{ $i === 0 ? 'open' : '' }}">
                <div class="faq-answer-inner">{{ $a }}</div>
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- ================================================================
     CTA
================================================================ --}}
<section class="cta-section">
    <div class="cta-card reveal">
        <div class="cta-glow"></div>
        <div class="section-tag" style="display:inline-flex;margin-bottom:20px;position:relative;">
            <i class="fa-solid fa-rocket"></i> Mulai Sekarang
        </div>
        <h2 class="cta-title">Siap Mengubah Cara<br><span class="gradient-text">Anda Bekerja?</span></h2>
        <p class="cta-sub">Bergabung dengan 10.000+ pengguna yang telah merasakan manfaat MediaTools. Gratis, tanpa kartu kredit, mulai dalam 30 detik.</p>
        <div class="cta-actions">
            <a href="{{ route('register') }}" class="btn-hero-primary" style="font-size:15px;padding:13px 28px;">
                <i class="fa-solid fa-bolt" style="font-size:12px;"></i> Daftar Sekarang
            </a>
            <a href="#tools" class="btn-hero-secondary" style="font-size:15px;padding:13px 28px;">
                Lihat Semua Tools <i class="fa-solid fa-arrow-right" style="font-size:12px;"></i>
            </a>
        </div>
        <p class="cta-note">Tanpa kartu kredit · Tanpa kontrak · Batalkan kapan saja</p>
    </div>
</section>

@push('scripts')
<script src="{{ asset('js/home.js') }}"></script>
@endpush
@endsection