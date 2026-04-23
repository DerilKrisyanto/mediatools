@extends('layouts.app')

@section('og_image', 'sanitizer')
@section('title', 'File Security & Privacy Scanner — Deteksi Backdoor, Hapus Metadata | MediaTools')
@section('meta_description', 'Scan file gambar & PDF untuk mendeteksi skrip berbahaya, backdoor, dan malware tersembunyi. Bersihkan ancaman sekaligus hapus metadata privasi dalam satu proses.')
@section('meta_keywords', 'Privacy & Media Sanitizer, Alat Kebersihan Digital, Scan file, mendeteksi skrip berbahaya, mendeteksi malware tersembunyi, mendeteksi ancaman file, hapus metadata')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/sanitizer.css') }}">
<link rel="stylesheet" href="{{ asset('css/ads.css') }}">
@endpush

@include('seo.sanitizer')

@section('content')
<link rel="stylesheet" href="{{ asset('css/tools-base.css') }}">
<script>document.body.classList.add('tlb-active');</script>

<div class="tlb-page tlb-orange" id="tlbPage_sanitizer">

{{-- ════ TLB HEADER ════ --}}
<div class="tlb-header">
    <div class="tlb-header-inner">
        <div>
            <nav aria-label="Breadcrumb" class="flex justify-left mb-5">
                <ol class="flex items-center gap-2 text-xs" style="color:var(--text-3)">
                    <li><a href="{{ url('/') }}" style="color:var(--text-3);text-decoration:none;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text-3)'">Home</a></li>
                    <li style="margin:0 4px;font-size:9px;">›</li>
                    <li style="color:var(--accent);font-weight:600;">File Security & Privacy Scanner</li>
                </ol>
            </nav>
            <div class="tlb-header-badges">
                <span class="tlb-hbadge"><i class="fa-solid fa-bug-slash"></i> Deteksi Malware</span>
                <span class="tlb-hbadge"><i class="fa-solid fa-eraser"></i> Hapus Metadata</span>
                <span class="tlb-hbadge"><i class="fa-solid fa-camera-slash"></i> EXIF Cleaner</span>
                <span class="tlb-hbadge"><i class="fa-solid fa-lock"></i> Privasi Terjaga</span>
            </div>
            <h1 class="tlb-header-title">File Security <span>Scanner.</span></h1>
            <p class="tlb-header-sub">Scan file untuk mendeteksi backdoor, malware & metadata tersembunyi. Bersihkan dalam satu proses.</p>
        </div>
    </div>
</div>
<div class="tlb-header-curve"></div>

<div class="tlb-body">
{{-- ═══ ADS SLOT ═══ --}}
<div class="ads-slot-header no-print" style="margin-bottom:20px;">@include('components.ads.banner-header')</div>

<script>
    window.__sanitizerScanUrl     = "{{ route('tools.sanitizer.scan') }}";
    window.__sanitizerProcessUrl  = "{{ route('tools.sanitizer.process') }}";
    window.__sanitizerDownloadUrl = "{{ url('/sanitizer/download') }}/__TOKEN__";
</script>


{{-- ================================================================
     HERO
     ================================================================ --}}


{{-- ================================================================
     MAIN TOOL
     ================================================================ --}}
<section class="py-8 px-6 relative">
    <div class="max-w-3xl mx-auto space-y-5">

        {{-- Alert box --}}
        <div id="alertBox" class="alert-error hidden" role="alert" aria-live="polite">
            <i class="fa-solid fa-circle-exclamation flex-shrink-0 mt-0.5"></i>
            <span data-msg></span>
        </div>

        {{-- Step indicator --}}
        <div class="step-bar">
            <div class="step-item active">
                <div class="step-num">1</div>
                <span class="step-label hidden sm:inline">Upload</span>
            </div>
            <div class="step-item">
                <div class="step-num">2</div>
                <span class="step-label hidden sm:inline">Scan</span>
            </div>
            <div class="step-item">
                <div class="step-num">3</div>
                <span class="step-label hidden sm:inline">Review</span>
            </div>
            <div class="step-item">
                <div class="step-num">4</div>
                <span class="step-label hidden sm:inline">Selesai</span>
            </div>
        </div>


        {{-- ============================================================
             PANEL 1: UPLOAD
             ============================================================ --}}
        <div id="panel-upload">

            <div class="upload-zone" id="uploadZone" role="button" tabindex="0"
                 aria-label="Drag and drop files here or click to select">

                <input type="file"
                       name="files[]"
                       id="fileInput"
                       multiple
                       accept=".jpg,.jpeg,.png,.webp,.pdf"
                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                       aria-hidden="true">

                {{-- Upload progress overlay --}}
                <div class="upload-overlay hidden" id="uploadRing" aria-hidden="true">
                    <div class="upload-ring">
                        <svg viewBox="0 0 60 60" width="60" height="60">
                            <circle class="upload-ring__track" cx="30" cy="30" r="26"/>
                            <circle class="upload-ring__fill" id="ringFill" cx="30" cy="30" r="26"/>
                        </svg>
                    </div>
                    <p class="text-xs font-bold text-[#a3e635]" id="overlayPct">0%</p>
                </div>

                <div class="upload-icon-ring" aria-hidden="true">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>

                <div class="text-center space-y-1 pointer-events-none">
                    <p class="font-bold text-sm">Seret &amp; lepas file di sini</p>
                    <p class="text-sm text-gray-400">atau <span class="text-[#a3e635] font-semibold">klik untuk memilih</span></p>
                </div>

                <p class="text-xs text-gray-500 pointer-events-none text-center">
                    <strong class="text-gray-400">JPG · PNG · WebP · PDF</strong>
                    &nbsp;&middot;&nbsp; Maks. <strong class="text-gray-400">20MB</strong>/file
                    &nbsp;&middot;&nbsp; Hingga <strong class="text-gray-400">10 file</strong>
                </p>
            </div>

            <div id="fileQueue" class="file-queue mt-4 hidden" role="list"></div>

            <button type="button"
                    id="scanBtn"
                    class="btn-primary mt-4 w-full py-4 text-base font-extrabold rounded-2xl"
                    disabled>
                <i class="fa-solid fa-magnifying-glass-chart"></i>
                <span>Scan &amp; Analisis File</span>
            </button>

            <p class="text-center text-xs text-gray-600 mt-2">
                File diproses di server terenkripsi kami &middot; Dihapus otomatis setelah selesai
            </p>
        </div>


        {{-- ============================================================
             PANEL 2: SCANNING ANIMATION
             ============================================================ --}}
        <div id="panel-scanning" class="hidden">
            <div class="scan-animation">
                <div class="scan-radar">
                    <svg viewBox="0 0 80 80" width="80" height="80">
                        <circle cx="40" cy="40" r="35" fill="none" stroke="rgba(163,230,53,0.08)" stroke-width="1"/>
                        <circle cx="40" cy="40" r="24" fill="none" stroke="rgba(163,230,53,0.12)" stroke-width="1"/>
                        <circle cx="40" cy="40" r="12" fill="none" stroke="rgba(163,230,53,0.18)" stroke-width="1"/>
                        <path d="M40 40 L40 5" stroke="#a3e635" stroke-width="2" stroke-linecap="round" opacity="0.8"/>
                        <path d="M40 40 L40 5 A35 35 0 0 1 75 40" fill="rgba(163,230,53,0.05)" stroke="none"/>
                        <circle cx="40" cy="40" r="4" fill="#a3e635"/>
                    </svg>
                </div>

                <div>
                    <p class="font-bold text-base">Memindai file Anda...</p>
                    <p class="text-sm text-gray-400 mt-1">Menganalisis konten untuk mendeteksi ancaman tersembunyi</p>
                </div>

                <div id="scan-file-list" class="scan-file-list w-full max-w-sm">
                    {{-- Populated by JS --}}
                </div>
            </div>
        </div>


        {{-- ============================================================
             PANEL 3: SCAN RESULTS
             ============================================================ --}}
        <div id="panel-results" class="hidden space-y-4">

            <div class="results-summary">
                <div class="summary-card total">
                    <div class="summary-num" id="res-total">0</div>
                    <div class="summary-label">Total File</div>
                </div>
                <div class="summary-card safe">
                    <div class="summary-num text-[#a3e635]" id="res-safe">0</div>
                    <div class="summary-label">Aman</div>
                </div>
                <div class="summary-card threat">
                    <div class="summary-num text-[#f87171]" id="res-threat">0</div>
                    <div class="summary-label">Ancaman</div>
                </div>
            </div>

            <div id="file-cards" class="space-y-3">
                {{-- Rendered by JS --}}
            </div>

            <div class="process-action-bar">
                <p class="text-xs text-gray-400 leading-relaxed">
                    <i class="fa-solid fa-circle-info text-[#a3e635] mr-1"></i>
                    File akan <strong class="text-white">dibersihkan dari ancaman</strong> dan
                    <strong class="text-white">metadata privasi</strong> (EXIF, GPS, Author) akan dihapus.
                    Centang file yang ingin diproses.
                </p>
                <button type="button"
                        id="processBtn"
                        class="btn-primary py-3.5 text-sm font-extrabold rounded-xl"
                        disabled>
                    <i class="fa-solid fa-broom"></i>
                    <span>Bersihkan &amp; Download</span>
                </button>

                {{-- ──────────────────────────────────────────────────────────────
                     BUG FIX #4 — Duplicate id="resetBtn"
                     OLD: Panel-download also had id="resetBtn" + onclick that
                          called .click() on itself → caused infinite loop.
                     FIX: Only ONE element with id="resetBtn" exists (here).
                          Panel-download uses location.reload() instead.
                     ────────────────────────────────────────────────────────────── --}}
                <button type="button"
                        id="resetBtn"
                        class="btn-outline py-2.5 text-xs font-bold rounded-xl">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    <span>Upload File Baru</span>
                </button>
            </div>
        </div>


        {{-- ============================================================
             PANEL 4: DOWNLOAD READY
             ============================================================ --}}
        <div id="panel-download" class="hidden space-y-4">

            <div class="download-card">
                <div class="w-16 h-16 rounded-full bg-[#a3e635]/15 border border-[#a3e635]/25
                            flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-circle-check text-[#a3e635] text-2xl"></i>
                </div>
                <h3 class="text-xl font-extrabold text-white mb-2">File Siap Diunduh!</h3>
                <p class="text-sm text-gray-400 mb-1" id="dlSummary">
                    File berhasil dibersihkan dari ancaman dan metadata.
                </p>
                <p class="text-xs text-gray-600 mb-6 font-mono" id="dlFilename"></p>

                <a href="#"
                   id="dlBtn"
                   class="btn-primary px-8 py-3.5 text-sm font-extrabold rounded-xl inline-flex"
                   download>
                    <i class="fa-solid fa-download"></i>
                    <span>Unduh File Bersih</span>
                </a>
            </div>

            <div class="text-center">
                <button onclick="location.reload()"
                        class="text-xs text-gray-500 hover:text-[#a3e635] transition-colors">
                    <i class="fa-solid fa-rotate-left text-[10px] mr-1"></i>
                    Proses File Baru
                </button>
            </div>

        </div>


        {{-- ============================================================
             HOW IT WORKS
             ============================================================ --}}
        <div class="bg-[#0b2323] border border-white/5 rounded-2xl p-6 space-y-4 mt-2">
            <h2 class="font-bold text-sm text-gray-300 uppercase tracking-wider">Cara Kerja</h2>
            <div class="space-y-4">
                @foreach([
                    ['fa-upload',                '1. Upload File',              'Pilih hingga 10 file JPG, PNG, WebP, atau PDF (maks. 20MB per file).'],
                    ['fa-magnifying-glass-chart', '2. Scan Ancaman',             'Engine kami menganalisis konten biner file untuk menemukan backdoor PHP/Python, skrip tersembunyi, dan anomali.'],
                    ['fa-triangle-exclamation',  '3. Review Laporan',           'Lihat laporan per file: jenis ancaman, tingkat keparahan, dan detail teknis. Centang file yang ingin dibersihkan.'],
                    ['fa-broom',                 '4. Bersihkan &amp; Download', 'Sistem membuang semua ancaman dan metadata (EXIF, GPS, Author) sekaligus. Unduh file bersih yang aman.'],
                ] as [$icon, $title, $desc])
                <div class="flex gap-3 items-start">
                    <div class="w-7 h-7 rounded-lg bg-[#a3e635]/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fa-solid {{ $icon }} text-[#a3e635] text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold">{!! $title !!}</p>
                        <p class="text-xs text-gray-400 leading-relaxed mt-0.5">{!! $desc !!}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</section>


{{-- ================================================================
     WHAT IS DETECTED
     ================================================================ --}}
<section class="py-8 px-6">
    <div class="max-w-3xl mx-auto space-y-5">

        <div class="bg-[#0b2323] border border-white/5 rounded-2xl p-6">
            <h2 class="font-bold text-sm text-gray-300 uppercase tracking-wider mb-4">Apa yang Dideteksi &amp; Dihapus?</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div>
                    <p class="text-xs font-bold text-red-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i class="fa-solid fa-bug text-[10px]"></i> Ancaman Keamanan
                    </p>
                    <ul class="space-y-2">
                        @foreach([
                            ['fa-code',                 'PHP Backdoor &amp; Webshell'],
                            ['fa-snake',                'Skrip Python Berbahaya'],
                            ['fa-terminal',             'Shell Command Execution'],
                            ['fa-triangle-exclamation', 'JPEG Polyglot Attack'],
                            ['fa-file-code',            'PDF JavaScript Tersembunyi'],
                            ['fa-rocket',               'PDF Launch &amp; Auto-Execute'],
                            ['fa-lock-open',            'Encoded &amp; Obfuscated Payload'],
                            ['fa-shield-slash',         'Known Webshell Signatures'],
                        ] as [$ico, $lbl])
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i class="fa-solid {{ $ico }} text-red-400/70 w-3.5 flex-shrink-0"></i>
                            {!! $lbl !!}
                        </li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <p class="text-xs font-bold text-[#a3e635] uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i class="fa-solid fa-user-secret text-[10px]"></i> Metadata Privasi
                    </p>
                    <ul class="space-y-2">
                        @foreach([
                            ['fa-location-dot', 'Koordinat GPS &amp; Lokasi'],
                            ['fa-camera',       'Model &amp; Serial Number Kamera'],
                            ['fa-calendar',     'Tanggal &amp; Waktu Pengambilan'],
                            ['fa-user',         'Nama Author / Fotografer'],
                            ['fa-gear',         'Pengaturan Eksposur &amp; ISO'],
                            ['fa-copyright',    'Hak Cipta Tersembunyi'],
                            ['fa-user-pen',     'Author &amp; Organisasi PDF'],
                            ['fa-sitemap',      'XMP &amp; Dublin Core Metadata'],
                        ] as [$ico, $lbl])
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i class="fa-solid {{ $ico }} text-gray-500 w-3.5 flex-shrink-0"></i>
                            {!! $lbl !!}
                        </li>
                        @endforeach
                    </ul>
                </div>

            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach([
                ['fa-server',      'Dev/Sysadmin',    'Verifikasi file upload user'],
                ['fa-newspaper',   'Jurnalis',        'Lindungi sumber &amp; lokasi'],
                ['fa-briefcase',   'Freelancer',      'Kirim karya tanpa metadata'],
                ['fa-user-shield', 'Privasi Pribadi', 'Posting foto tanpa jejak'],
            ] as [$ico, $title, $sub])
            <div class="bg-[#0b2323] border border-white/5 rounded-xl p-4 text-center hover:border-[#a3e635]/20 transition-colors">
                <i class="fa-solid {{ $ico }} text-[#a3e635] text-lg mb-2 block"></i>
                <p class="text-xs font-bold">{{ $title }}</p>
                <p class="text-[10px] text-gray-500 mt-0.5 leading-snug">{!! $sub !!}</p>
            </div>
            @endforeach
        </div>

    </div>
</section>


{{-- ================================================================
     FAQ
     ================================================================ --}}
<section class="faq-section" itemscope itemtype="https://schema.org/FAQPage">

    <div style="text-align:center;margin-bottom:0;">
        <div class="section-tag" style="margin-bottom:12px;display:inline-flex;">
            <i class="fa-solid fa-circle-question"></i> FAQ
        </div>
        <h2 style="font-size:clamp(1.5rem,3vw,2rem);font-weight:800;letter-spacing:-0.03em;" class="reveal">
            Security & Privacy Tool
        </h2>
        <p style="color:var(--text-2);margin-top:8px;font-size:14px;" class="reveal reveal-d1">
            Semua yang perlu Anda ketahui tentang alat ini.
        </p>
    </div>

    <div class="font-bold text-sm text-gray-300 uppercase tracking-wider mb-4">
        @foreach([
            ['Bagaimana hacker menyembunyikan skrip dalam file gambar?',
             'Teknik umum adalah "polyglot file" — file yang valid sebagai dua format sekaligus. Misalnya, file JPEG yang berisi PHP script di akhirnya (setelah EOI marker). Saat server memproses upload, file diterima sebagai gambar, tapi bisa dieksekusi sebagai PHP. Tools kami mendeteksi pola ini dan menghapusnya.'],
            ['Apa yang dimaksud "bersihkan" ancaman?',
             'Proses pembersihan me-render ulang file melalui library resmi (Pillow untuk gambar, pikepdf untuk PDF). Library ini hanya membaca data piksel/konten murni dan menulis ulang file bersih, sehingga semua data appended, embedded script, dan metadata otomatis terbuang.'],
            ['Apakah kualitas gambar berubah setelah diproses?',
             'Tidak signifikan. Untuk JPEG kami menggunakan quality=95 (mendekati lossless). PNG dan WebP menggunakan re-encode dengan pengaturan default. Resolusi dan ukuran file tidak berubah secara berarti.'],
            ['Apakah file saya dikirim ke pihak ketiga?',
             'Tidak. Seluruh proses berjalan di server MediaTools yang terenkripsi. File tidak pernah dikirim ke layanan eksternal. File sementara dihapus otomatis setelah token download diklaim.'],
            ['Format apa yang didukung?',
             'JPG/JPEG, PNG, WebP (gambar), dan PDF (dokumen). Hingga 10 file sekaligus, maksimal 20MB per file. Untuk multiple file, hasilnya dipaketkan dalam ZIP.'],
        ] as $i => [$q, $a])

        <div class="faq-item {{ $i === 0 ? 'open' : '' }}"
             itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">

            <div class="faq-question">
                <span itemprop="name">{{ $q }}</span>
                <span class="faq-icon">
                    <i class="fa-solid fa-plus" style="font-size:10px;"></i>
                </span>
            </div>

            <div class="faq-answer {{ $i === 0 ? 'open' : '' }}"
                 itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">

                <div class="faq-answer-inner" itemprop="text">
                    {{ $a }}
                </div>

            </div>
        </div>

        @endforeach
    </div>

</section>

{{-- ═══ SLOT 3: RESULT BANNER 300×250 ═══ --}}
<div class="ads-slot-result no-print">
    @include('components.ads.banner-result')
</div>

{{-- ═══ SLOT 4: NATIVE BANNER ═══ --}}
<div class="ads-slot-native no-print">
    @include('components.ads.banner-content')
</div>

{{-- ================================================================
     CTA
     ================================================================ --}}
<section class="py-10 px-6 pb-20">
    <div class="max-w-3xl mx-auto text-center space-y-4">
        <p class="text-gray-400 text-sm">Butuh tools untuk produktivitas lainnya?</p>
        <a href="{{ url('/') }}#tools" class="btn-outline px-8 py-3 text-sm inline-flex">
            <i class="fa-solid fa-grid-2 text-xs"></i>
            <span>Lihat Semua 10+ Media Tools</span>
        </a>
    </div>
</section>

@endsection

@push('scripts')
<script src="{{ asset('js/sanitizer.js') }}"></script>
@endpush

</div>{{-- /.tlb-body --}}
</div>{{-- /.tlb-page --}}
