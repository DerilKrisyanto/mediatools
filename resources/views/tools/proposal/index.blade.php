@extends('layouts.app')

@section('og_image', 'proposal')
@section('title', 'Proposal Builder — Buat Proposal Profesional Gratis | MediaTools')
@section('meta_description', 'Buat proposal tugas akhir, project freelancer, bisnis, dan event dalam hitungan menit. Template lengkap, download DOCX atau PDF langsung siap pakai.')
@section('meta_keywords', 'proposal builder, buat proposal, template proposal tugas akhir, proposal freelancer, proposal bisnis, proposal event, generator proposal gratis')

{{-- @include('seo.proposal') --}}

@section('content')

{{-- ── URL routes untuk JS (tidak inline JS) ── --}}
<meta name="pb-generate-url" content="{{ route('tools.proposal.generate') }}">
<meta name="pb-download-url" content="{{ route('tools.proposal.download') }}">
<meta name="pb-serve-url"    content="{{ url('proposal/serve') }}">

<link rel="stylesheet" href="{{ asset('css/proposal.css') }}">
<link rel="stylesheet" href="{{ asset('css/ads.css') }}">

{{-- Inject URL ke window sebelum JS diload --}}
<script>
    window.PB_GENERATE_URL = document.querySelector('meta[name="pb-generate-url"]').content;
    window.PB_DOWNLOAD_URL = document.querySelector('meta[name="pb-download-url"]').content;
    window.PB_SERVE_URL    = document.querySelector('meta[name="pb-serve-url"]').content;
</script>

<div class="pb-page">
    <div class="pb-grid-bg"></div>

    {{-- ═══ SLOT 1: HEADER BANNER ═══ --}}
    <div class="ads-slot-header no-print">
        @include('components.ads.banner-header')
    </div>

    <div class="pb-container">

        {{-- ════════════════════════════════════════════
             VIEW: LANDING
        ════════════════════════════════════════════ --}}
        <div id="view-landing" class="pb-view active">

            <div class="pb-header">
                <div class="pb-badge-row">
                    <span class="pb-badge" style="background:var(--adim);border:1px solid var(--bda);color:var(--accent);">
                        <i class="fa-solid fa-file-contract" style="font-size:10px;"></i> Proposal Builder
                    </span>
                    <span class="pb-badge" style="background:rgba(255,255,255,0.04);border:1px solid var(--border);color:var(--text-muted);">
                        <i class="fa-solid fa-download" style="font-size:10px;"></i> Download DOCX &amp; PDF
                    </span>
                    <span class="pb-badge" style="background:rgba(255,255,255,0.04);border:1px solid var(--border);color:var(--text-muted);">
                        <i class="fa-solid fa-bolt" style="font-size:10px;"></i> Instan
                    </span>
                </div>
                <h1 class="pb-hero-title">
                    Buat <span class="gradient-text">Proposal Profesional</span><br>
                    Siap Download &amp; Langsung Pakai
                </h1>
                <p class="pb-hero-sub">
                    Template terstruktur untuk mahasiswa, freelancer, bisnis, dan event.<br>
                    Isi data — unduh <strong>.docx</strong> atau <strong>.pdf</strong> — selesai.
                </p>
            </div>

            {{-- Template Cards --}}
            <div class="pb-template-grid">

                <div class="pb-tpl-card" data-template="mahasiswa">
                    <div class="pb-tpl-icon pb-tpl-icon--mahasiswa">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div class="pb-tpl-badge pb-tpl-badge--mahasiswa">Mahasiswa</div>
                    <h3 class="pb-tpl-name">Proposal Tugas Akhir</h3>
                    <p class="pb-tpl-desc">Template skripsi, TA, atau tesis S1/S2. Struktur lengkap: cover, kata pengantar, BAB I–V, hingga daftar pustaka.</p>
                    <div class="pb-tpl-features">
                        <span><i class="fa-solid fa-check"></i> Cover + Sampul</span>
                        <span><i class="fa-solid fa-check"></i> BAB I – V</span>
                        <span><i class="fa-solid fa-check"></i> Daftar Isi</span>
                    </div>
                    <div class="pb-tpl-arrow">Mulai Buat <i class="fa-solid fa-arrow-right-long" style="font-size:11px;"></i></div>
                </div>

                <div class="pb-tpl-card" data-template="freelancer">
                    <div class="pb-tpl-icon pb-tpl-icon--freelancer">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>
                    <div class="pb-tpl-badge pb-tpl-badge--freelancer">Freelancer</div>
                    <h3 class="pb-tpl-name">Proposal Project</h3>
                    <p class="pb-tpl-desc">Untuk freelancer dan agensi kreatif. Scope of work, timeline, rincian biaya, dan syarat ketentuan profesional.</p>
                    <div class="pb-tpl-features">
                        <span><i class="fa-solid fa-check"></i> Scope of Work</span>
                        <span><i class="fa-solid fa-check"></i> Timeline</span>
                        <span><i class="fa-solid fa-check"></i> Rincian Biaya</span>
                    </div>
                    <div class="pb-tpl-arrow">Mulai Buat <i class="fa-solid fa-arrow-right-long" style="font-size:11px;"></i></div>
                </div>

                <div class="pb-tpl-card" data-template="bisnis">
                    <div class="pb-tpl-icon pb-tpl-icon--bisnis">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div class="pb-tpl-badge pb-tpl-badge--bisnis">Bisnis</div>
                    <h3 class="pb-tpl-name">Proposal Bisnis</h3>
                    <p class="pb-tpl-desc">Pitching ke investor atau mitra. Profil perusahaan, analisis SWOT, model bisnis, dan proyeksi keuangan.</p>
                    <div class="pb-tpl-features">
                        <span><i class="fa-solid fa-check"></i> Executive Summary</span>
                        <span><i class="fa-solid fa-check"></i> Analisis SWOT</span>
                        <span><i class="fa-solid fa-check"></i> Proyeksi Keuangan</span>
                    </div>
                    <div class="pb-tpl-arrow">Mulai Buat <i class="fa-solid fa-arrow-right-long" style="font-size:11px;"></i></div>
                </div>

                <div class="pb-tpl-card" data-template="event">
                    <div class="pb-tpl-icon pb-tpl-icon--event">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <div class="pb-tpl-badge pb-tpl-badge--event">Event</div>
                    <h3 class="pb-tpl-name">Proposal Event</h3>
                    <p class="pb-tpl-desc">Kepanitiaan, sponsorship, atau izin acara. Rundown, struktur panitia, anggaran, dan paket sponsor lengkap.</p>
                    <div class="pb-tpl-features">
                        <span><i class="fa-solid fa-check"></i> Rundown Acara</span>
                        <span><i class="fa-solid fa-check"></i> Rencana Anggaran</span>
                        <span><i class="fa-solid fa-check"></i> Paket Sponsor</span>
                    </div>
                    <div class="pb-tpl-arrow">Mulai Buat <i class="fa-solid fa-arrow-right-long" style="font-size:11px;"></i></div>
                </div>

            </div>{{-- /template-grid --}}

            {{-- Feature Row --}}
            <div class="pb-feat-row">
                <div class="pb-feat-item">
                    <div class="pb-feat-icon"><i class="fa-solid fa-file-word"></i></div>
                    <div>
                        <div class="pb-feat-title">Download DOCX Asli</div>
                        <div class="pb-feat-desc">Buka &amp; edit langsung di Microsoft Word</div>
                    </div>
                </div>
                <div class="pb-feat-item">
                    <div class="pb-feat-icon"><i class="fa-solid fa-file-pdf"></i></div>
                    <div>
                        <div class="pb-feat-title">Download PDF Siap Cetak</div>
                        <div class="pb-feat-desc">Format A4 sesuai standar institusi Indonesia</div>
                    </div>
                </div>
                <div class="pb-feat-item">
                    <div class="pb-feat-icon"><i class="fa-solid fa-clock"></i></div>
                    <div>
                        <div class="pb-feat-title">5–10 Menit Selesai</div>
                        <div class="pb-feat-desc">Isi form — generate — langsung download</div>
                    </div>
                </div>
                <div class="pb-feat-item">
                    <div class="pb-feat-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <div>
                        <div class="pb-feat-title">Data Anda Aman</div>
                        <div class="pb-feat-desc">File dihapus otomatis setelah 2 jam</div>
                    </div>
                </div>
            </div>

        </div>{{-- /view-landing --}}


        {{-- ════════════════════════════════════════════
             VIEW: WIZARD
             ⚠ ID di sini HARUS cocok persis dengan proposal.js:
               sb-icon, sb-tpl-name, sb-tpl-sub, sb-steps-list,
               pb-main-panel, pb-step-panels, pb-progress-fill,
               pb-progress-label, wizard-back, wizard-next
        ════════════════════════════════════════════ --}}
        <div id="view-wizard" class="pb-view">
            <div class="pb-wizard-layout">

                {{-- Sidebar --}}
                <aside class="pb-sidebar">
                    <div class="pb-sidebar-header">
                        <button id="sb-back-btn" class="pb-sb-back" type="button">
                            <i class="fa-solid fa-arrow-left" style="font-size:11px;"></i>
                            Ganti Template
                        </button>

                        {{-- ⚠ sb-icon, sb-tpl-name, sb-tpl-sub -- diisi oleh buildWizard() --}}
                        <div class="pb-sb-tpl-meta">
                            <span id="sb-icon" class="pb-sb-icon"></span>
                            <div>
                                <div id="sb-tpl-name" class="pb-sb-tpl-name">—</div>
                                <div id="sb-tpl-sub" class="pb-sb-tpl-sub">—</div>
                            </div>
                        </div>
                    </div>

                    {{-- ⚠ sb-steps-list -- diisi oleh buildWizard() --}}
                    <ul id="sb-steps-list" class="pb-steps-list"></ul>

                    <div class="pb-sb-tip">
                        <i class="fa-solid fa-circle-info" style="color:var(--accent);font-size:11px;flex-shrink:0;margin-top:2px;"></i>
                        <span>Kolom bertanda <span style="color:#f87171;">*</span> wajib diisi. Sisanya opsional — template otomatis terisi teks standar.</span>
                    </div>
                </aside>

                {{-- Wizard Main Panel --}}
                <div class="pb-wizard-panel" id="pb-main-panel">

                    {{-- Progress bar -- ⚠ pb-progress-fill, pb-progress-label --}}
                    <div class="pb-wizard-progress-wrap">
                        <div class="pb-wizard-progress-track">
                            <div id="pb-progress-fill" class="pb-progress-fill"></div>
                        </div>
                        <span id="pb-progress-label" class="pb-progress-label">Langkah 1</span>
                    </div>

                    {{-- ⚠ pb-step-panels -- diisi oleh buildWizard() --}}
                    <div id="pb-step-panels" class="pb-step-panels-wrap"></div>

                    {{-- Navigation --}}
                    <div class="pb-form-nav">
                        <button id="wizard-back" class="pb-nav-back" type="button" style="display:none;">
                            <i class="fa-solid fa-arrow-left" style="font-size:11px;"></i> Kembali
                        </button>
                        <button id="wizard-next" class="pb-nav-next" type="button">
                            Lanjut <i class="fa-solid fa-arrow-right" style="font-size:11px;"></i>
                        </button>
                    </div>

                </div>{{-- /wizard-panel --}}

            </div>{{-- /wizard-layout --}}
        </div>{{-- /view-wizard --}}


        {{-- ════════════════════════════════════════════
             VIEW: GENERATING
             ⚠ pb-gen-progress-fill, pb-gen-steps (container diisi JS)
        ════════════════════════════════════════════ --}}
        <div id="view-generating" class="pb-view">
            <div class="pb-generating-wrap">
                <div class="pb-generating-card">
                    <div class="pb-gen-pulse-ring"></div>
                    <div class="pb-gen-icon">
                        <i class="fa-solid fa-file-contract"></i>
                    </div>
                    <h2 class="pb-gen-title">Membuat Proposal Anda</h2>
                    <p class="pb-gen-sub">Menyusun struktur dan mengisi konten setiap bagian...</p>

                    <div class="pb-gen-progress-wrap">
                        <div id="pb-gen-progress-fill" class="pb-gen-progress-fill"></div>
                    </div>

                    {{-- ⚠ pb-gen-steps -- innerHTML diisi oleh runGeneratingAnimation() --}}
                    <div id="pb-gen-steps" class="pb-gen-steps-list"></div>
                </div>
            </div>
        </div>{{-- /view-generating --}}


        {{-- ════════════════════════════════════════════
             VIEW: PREVIEW
        ════════════════════════════════════════════ --}}
        <div id="view-preview" class="pb-view">

            {{-- Toolbar --}}
            <div class="pb-preview-toolbar">
                <div class="pb-preview-title-wrap">
                    <i class="fa-solid fa-file-contract" style="color:var(--accent);font-size:14px;"></i>
                    <span id="pb-preview-title-text" class="pb-preview-title-text">Proposal</span>
                </div>
                <div class="pb-preview-actions">
                    <button id="pb-btn-edit" class="pb-btn-edit" type="button">
                        <i class="fa-solid fa-pen-to-square" style="font-size:11px;"></i>
                        Edit Data
                    </button>
                    <button id="pb-btn-new" class="pb-btn-new" type="button">
                        <i class="fa-solid fa-rotate-left" style="font-size:11px;"></i>
                        Buat Baru
                    </button>
                </div>
            </div>

            {{-- Preview Frame --}}
            <div class="pb-preview-frame-wrap">
                <div class="pb-preview-frame-bar">
                    <div class="pb-frame-dot" style="background:#ef4444;"></div>
                    <div class="pb-frame-dot" style="background:#f59e0b;"></div>
                    <div class="pb-frame-dot" style="background:#22c55e;"></div>
                    <span style="font-size:11px;color:#6b7280;margin-left:8px;">Preview Proposal</span>
                </div>
                <iframe id="pb-preview-frame" class="pb-preview-frame"
                        title="Preview Proposal" sandbox="allow-same-origin"></iframe>
            </div>

            {{-- Download Card --}}
            <div class="pb-download-opts">
                <div class="pb-download-opts-text">
                    <strong>Proposal siap diunduh!</strong><br>
                    Pilih format. File dikonversi di server &amp; langsung terunduh ke perangkat Anda.
                </div>
                <div class="pb-download-actions">
                    <button id="pb-btn-dl-word" class="pb-btn-dl-word" type="button"
                            title="Download sebagai file Word (.docx) — bisa diedit langsung di Microsoft Word">
                        <i class="fa-solid fa-file-word"></i>
                        <span>Download Word (.docx)</span>
                    </button>
                    <button id="pb-btn-dl-pdf" class="pb-btn-dl-pdf" type="button"
                            title="Download sebagai PDF siap cetak">
                        <i class="fa-solid fa-file-pdf"></i>
                        <span>Download PDF</span>
                    </button>
                </div>
            </div>

            {{-- Info konversi --}}
            <div class="pb-convert-notice">
                <i class="fa-solid fa-circle-info" style="color:var(--accent);font-size:11px;flex-shrink:0;margin-top:1px;"></i>
                <span>
                    Konversi menggunakan <strong>LibreOffice</strong> di server — menghasilkan <strong>.docx</strong>
                    atau <strong>.pdf</strong> asli, bukan HTML. Proses memerlukan <strong>10–30 detik</strong>.
                </span>
            </div>

            {{-- ═══ SLOT 3: RESULT AD ═══ --}}
            <div class="ads-slot-result no-print" style="margin-top:24px;">
                @include('components.ads.banner-result')
            </div>

        </div>{{-- /view-preview --}}

    </div>{{-- /pb-container --}}
</div>{{-- /pb-page --}}

{{-- Toast --}}
<div id="pb-toast" class="pb-toast" role="alert" aria-live="assertive">
    <i id="pb-toast-ico" class="fa-solid fa-circle-check"></i>
    <span id="pb-toast-msg">Berhasil!</span>
</div>

@push('scripts')
<script src="{{ asset('js/proposal.js') }}"></script>
@endpush

@endsection