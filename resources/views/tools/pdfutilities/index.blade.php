@extends('layouts.app')

@section('og_image', 'pdfutilities')
@section('title', 'PDF Tools Gratis — Merge Split Compress PDF Online | MediaTools')
@section('meta_description', 'Gabung (merge), pisah (split), dan kompres PDF langsung di browser — tanpa upload ke server untuk merge & split. Compress menggunakan Ghostscript server-side. Gratis, privasi terjaga, alternatif iLovePDF terbaik.')
@section('meta_keywords', 'merge pdf gratis, split pdf online, compress pdf, gabung pdf, ilovepdf alternative, gabung pdf online, kompres pdf, pdf tools gratis, pisah pdf, combine pdf online, merge pdf online, pdf merge split, kompres ukuran pdf, pdf utilities, ilovepdf indonesia')
@include('seo.pdfutilities')

@section('content')
<link rel="stylesheet" href="{{ asset('css/pdfutilities.css') }}">

<div class="pdf-page">
<div class="pdf-wrap">

    {{-- ── HERO ── --}}
    <header class="pdf-hero">
        <div class="pdf-hero-badges">
            <span class="pdf-chip pdf-chip--yellow"><i class="fa-solid fa-star fa-xs"></i> Pro Tool</span>
            <span class="pdf-chip"><i class="fa-solid fa-lock fa-xs"></i> Merge/Split Privasi 100%</span>
            <span class="pdf-chip pdf-chip--green"><i class="fa-solid fa-server fa-xs"></i> Compress via Ghostscript</span>
        </div>
        <h1 class="pdf-hero-title">PDF <span class="pdf-accent">Utilities.</span></h1>
        <p class="pdf-hero-sub">
            Merge & Split PDF — langsung di browser, zero upload.<br>
            Compress PDF — Ghostscript server-side, file dihapus instan.
        </p>
    </header>

    {{-- ── STEPPER ── --}}
    <div class="pdf-stepper" id="pdfStepper">
        <div class="pdf-step-item active" data-step="1">
            <div class="pdf-step-circle">1</div>
            <span class="pdf-step-label">Pilih Fitur</span>
        </div>
        <div class="pdf-step-line"></div>
        <div class="pdf-step-item" data-step="2">
            <div class="pdf-step-circle">2</div>
            <span class="pdf-step-label">Upload</span>
        </div>
        <div class="pdf-step-line"></div>
        <div class="pdf-step-item" data-step="3">
            <div class="pdf-step-circle">3</div>
            <span class="pdf-step-label">Konfigurasi</span>
        </div>
        <div class="pdf-step-line"></div>
        <div class="pdf-step-item" data-step="4">
            <div class="pdf-step-circle"><i class="fa-solid fa-check fa-xs"></i></div>
            <span class="pdf-step-label">Download</span>
        </div>
    </div>

    {{-- ── WORKSPACE CARD ── --}}
    <div class="pdf-card" id="pdfCard">
        <div class="pdf-card-glow"></div>

        {{-- ══════════════════════════════════
             PANEL 1 — PILIH FITUR
             ══════════════════════════════════ --}}
        <div class="pdf-panel" id="panelFeature">
            <p class="pdf-panel-eyebrow">
                <i class="fa-solid fa-circle-dot"></i> Langkah 1 — Pilih operasi yang ingin dilakukan
            </p>
            <h2 class="pdf-panel-title">Apa yang ingin Anda lakukan?</h2>

            <div class="pdf-feature-grid">

                {{-- Merge --}}
                <button class="pdf-feat" data-feature="merge">
                    <div class="pdf-feat-glow pdf-feat-glow--blue"></div>
                    <div class="pdf-feat-ico pdf-feat-ico--blue">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div class="pdf-feat-body">
                        <span class="pdf-feat-name">Merge PDF</span>
                        <span class="pdf-feat-desc">Gabungkan beberapa file PDF menjadi satu dokumen. Atur urutan dengan drag & drop.</span>
                        <div class="pdf-feat-meta">
                            <span class="pdf-feat-tag"><i class="fa-solid fa-lock"></i> 100% di browser</span>
                            <span class="pdf-feat-tag"><i class="fa-solid fa-bolt"></i> Instan, tanpa upload</span>
                        </div>
                    </div>
                    <div class="pdf-feat-arrow"><i class="fa-solid fa-arrow-right"></i></div>
                </button>

                {{-- Split --}}
                <button class="pdf-feat" data-feature="split">
                    <div class="pdf-feat-glow pdf-feat-glow--orange"></div>
                    <div class="pdf-feat-ico pdf-feat-ico--orange">
                        <i class="fa-solid fa-scissors"></i>
                    </div>
                    <div class="pdf-feat-body">
                        <span class="pdf-feat-name">Split PDF</span>
                        <span class="pdf-feat-desc">Pisahkan halaman berdasarkan rentang, atau tiap halaman jadi file terpisah (ZIP).</span>
                        <div class="pdf-feat-meta">
                            <span class="pdf-feat-tag"><i class="fa-solid fa-lock"></i> 100% di browser</span>
                            <span class="pdf-feat-tag"><i class="fa-solid fa-file-zipper"></i> Split-all → ZIP</span>
                        </div>
                    </div>
                    <div class="pdf-feat-arrow"><i class="fa-solid fa-arrow-right"></i></div>
                </button>

                {{-- Compress --}}
                <button class="pdf-feat" data-feature="compress">
                    <div class="pdf-feat-glow pdf-feat-glow--green"></div>
                    <div class="pdf-feat-ico pdf-feat-ico--green">
                        <i class="fa-solid fa-file-zipper"></i>
                    </div>
                    <div class="pdf-feat-body">
                        <span class="pdf-feat-name">Compress PDF</span>
                        <span class="pdf-feat-desc">Kurangi ukuran file PDF secara drastis menggunakan Ghostscript. Hasil lebih kecil dari iLovePDF.</span>
                        <div class="pdf-feat-meta">
                            <span class="pdf-feat-tag pdf-feat-tag--server"><i class="fa-solid fa-server"></i> Ghostscript server-side</span>
                            <span class="pdf-feat-tag"><i class="fa-solid fa-trash"></i> File dihapus instan</span>
                        </div>
                    </div>
                    <div class="pdf-feat-arrow"><i class="fa-solid fa-arrow-right"></i></div>
                </button>

            </div>

            <div class="pdf-trust-row">
                <span><i class="fa-solid fa-browser"></i> Merge &amp; Split tanpa upload ke server</span>
                <span><i class="fa-solid fa-ghost"></i> Compress via Ghostscript engine</span>
                <span><i class="fa-solid fa-trash-can"></i> File compress dihapus otomatis</span>
            </div>
        </div>

        {{-- ══════════════════════════════════
             PANEL 2 — UPLOAD
             ══════════════════════════════════ --}}
        <div class="pdf-panel pdf-hidden" id="panelUpload">
            <div class="pdf-panel-nav">
                <button class="pdf-selected-badge" id="btnBackToFeature">
                    <i class="fa-solid fa-arrow-left fa-xs"></i>
                    <span id="selectedBadgeText">Ganti Fitur</span>
                </button>
            </div>

            <p class="pdf-panel-eyebrow">
                <i class="fa-solid fa-circle-dot"></i> Langkah 2 — Upload
            </p>
            <h2 class="pdf-panel-title" id="uploadPanelTitle">Upload file PDF</h2>

            {{-- Drop zone --}}
            <div class="pdf-dropzone" id="dropZone">
                <input type="file" id="pdfFiles" accept=".pdf" class="pdf-file-input">
                <div class="pdf-dz-body">
                    <div class="pdf-dz-icon-wrap">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <p class="pdf-dz-title">Drag &amp; drop file PDF di sini</p>
                    <p class="pdf-dz-hint" id="dzHint">atau klik untuk memilih file</p>
                    <div class="pdf-dz-formats">
                        <span class="pdf-format-tag"><i class="fa-solid fa-file-pdf fa-xs"></i> PDF</span>
                        <span class="pdf-format-tag">Maks. 100MB per file</span>
                    </div>
                </div>
            </div>

            {{-- File list --}}
            <div id="fileListWrap" class="pdf-hidden">
                <div class="pdf-filelist-header">
                    <span class="pdf-filelist-count" id="fileCount">0 file dipilih</span>
                    <button class="pdf-btn-addmore pdf-hidden" id="btnAddMore">
                        <i class="fa-solid fa-plus fa-xs"></i> Tambah File
                    </button>
                </div>
                <div class="pdf-filelist" id="fileList"></div>
            </div>

            {{-- Merge: langsung proses --}}
            <button class="pdf-btn-primary pdf-hidden" id="btnMergeProcess">
                <i class="fa-solid fa-layer-group fa-xs"></i>
                <span>Gabung 0 File Sekarang</span>
            </button>

            {{-- Split / Compress: lanjut ke config --}}
            <button class="pdf-btn-primary pdf-hidden" id="btnToConfig">
                <i class="fa-solid fa-arrow-right fa-xs"></i>
                <span id="btnNextLabel">Lanjutkan</span>
            </button>
        </div>

        {{-- ══════════════════════════════════
             PANEL 3 — KONFIGURASI
             ══════════════════════════════════ --}}
        <div class="pdf-panel pdf-hidden" id="panelConfig">
            <div class="pdf-panel-nav">
                <button class="pdf-btn-back" id="btnBackToUpload">
                    <i class="fa-solid fa-arrow-left fa-xs"></i> Kembali
                </button>
                <div class="pdf-selected-badge" id="selectedBadge2">
                    <i class="fa-solid fa-scissors fa-xs" id="selectedBadge2Icon"></i>
                    <span id="selectedBadge2Text">Split PDF</span>
                </div>
            </div>

            <p class="pdf-panel-eyebrow">
                <i class="fa-solid fa-circle-dot"></i> Langkah 3 — Konfigurasi
            </p>
            <h2 class="pdf-panel-title" id="configPanelTitle">Atur opsi proses</h2>

            {{-- ── SPLIT CONFIG ── --}}
            <div id="configSplit">
                <div class="pdf-config-info" id="splitPageInfo">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span id="splitFileName">—</span>
                    <span class="pdf-config-info-sep">·</span>
                    <span id="splitPageCount">Menghitung halaman…</span>
                </div>

                <label class="pdf-label">Mode Pemisahan</label>
                <div class="pdf-mode-tabs" id="splitModeTabs">
                    <button class="pdf-mode-tab active" data-mode="range">
                        <i class="fa-solid fa-cut fa-xs"></i>
                        <div>
                            <span>Rentang Halaman</span>
                            <small>Ambil halaman dari … sampai …</small>
                        </div>
                    </button>
                    <button class="pdf-mode-tab" data-mode="all">
                        <i class="fa-solid fa-copy fa-xs"></i>
                        <div>
                            <span>Pisah Semua</span>
                            <small>Tiap halaman → file terpisah (ZIP)</small>
                        </div>
                    </button>
                </div>

                <div id="splitRangeInput">
                    <label class="pdf-label pdf-label--mt">Rentang Halaman</label>
                    <div class="pdf-range-row">
                        <div class="pdf-range-box">
                            <span class="pdf-range-lbl">Dari Halaman</span>
                            <input type="number" id="rangeFrom" class="pdf-range-input"
                                   value="1" min="1" placeholder="1">
                        </div>
                        <div class="pdf-range-sep">
                            <i class="fa-solid fa-arrow-right"></i>
                        </div>
                        <div class="pdf-range-box">
                            <span class="pdf-range-lbl">Sampai Halaman</span>
                            <input type="number" id="rangeTo" class="pdf-range-input"
                                   value="5" min="1" placeholder="5">
                        </div>
                    </div>
                    <p class="pdf-range-note">
                        <i class="fa-solid fa-circle-info fa-xs"></i>
                        Nomor halaman di luar range total akan disesuaikan otomatis.
                    </p>
                </div>
            </div>

            {{-- ── COMPRESS CONFIG ── --}}
            <div id="configCompress" class="pdf-hidden">
                <div class="pdf-config-info" id="compressFileInfo">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span id="compressFileName">—</span>
                    <span class="pdf-config-info-sep">·</span>
                    <span id="compressFileSize">—</span>
                </div>

                <label class="pdf-label">Level Kompresi</label>
                <div class="pdf-compress-grid">

                    <button class="pdf-clevel" data-level="low">
                        <div class="pdf-clevel-left">
                            <div class="pdf-clevel-bars">
                                <span class="pdf-bar pdf-bar--on  pdf-bar--s"></span>
                                <span class="pdf-bar pdf-bar--on  pdf-bar--m"></span>
                                <span class="pdf-bar pdf-bar--on  pdf-bar--l"></span>
                            </div>
                            <div>
                                <span class="pdf-clevel-name">Ringan</span>
                                <span class="pdf-clevel-dpi">200 DPI · JPEG 82</span>
                            </div>
                        </div>
                        <div class="pdf-clevel-right">
                            <span class="pdf-clevel-reduction">~25–35% lebih kecil</span>
                            <span class="pdf-clevel-quality">Kualitas tinggi</span>
                        </div>
                    </button>

                    <button class="pdf-clevel active" data-level="medium">
                        <div class="pdf-clevel-badge">Recommended</div>
                        <div class="pdf-clevel-left">
                            <div class="pdf-clevel-bars">
                                <span class="pdf-bar pdf-bar--on  pdf-bar--s"></span>
                                <span class="pdf-bar pdf-bar--on  pdf-bar--m"></span>
                                <span class="pdf-bar pdf-bar--off pdf-bar--l"></span>
                            </div>
                            <div>
                                <span class="pdf-clevel-name">Sedang</span>
                                <span class="pdf-clevel-dpi">120 DPI · JPEG 60</span>
                            </div>
                        </div>
                        <div class="pdf-clevel-right">
                            <span class="pdf-clevel-reduction">~50–60% lebih kecil</span>
                            <span class="pdf-clevel-quality">Seimbang</span>
                        </div>
                    </button>

                    <button class="pdf-clevel" data-level="high">
                        <div class="pdf-clevel-left">
                            <div class="pdf-clevel-bars">
                                <span class="pdf-bar pdf-bar--on  pdf-bar--s"></span>
                                <span class="pdf-bar pdf-bar--off pdf-bar--m"></span>
                                <span class="pdf-bar pdf-bar--off pdf-bar--l"></span>
                            </div>
                            <div>
                                <span class="pdf-clevel-name">Tinggi</span>
                                <span class="pdf-clevel-dpi">72 DPI · JPEG 30</span>
                            </div>
                        </div>
                        <div class="pdf-clevel-right">
                            <span class="pdf-clevel-reduction">~70–80% lebih kecil</span>
                            <span class="pdf-clevel-quality">Ukuran terkecil</span>
                        </div>
                    </button>

                </div>

                <div class="pdf-infobox">
                    <i class="fa-solid fa-ghost"></i>
                    <p>
                        Kompresi menggunakan <strong>Ghostscript</strong> di server kami — re-encode seluruh gambar,
                        subset font, dan hapus metadata. File Anda <strong>dihapus segera</strong> setelah
                        respons dikirim, tidak pernah tersimpan di disk kami.
                        Untuk PDF berisi banyak gambar atau scan, hasilnya bisa
                        <strong>jauh lebih kecil dari iLovePDF</strong>.
                    </p>
                </div>
            </div>

            <button class="pdf-btn-primary" id="btnStartProcess">
                <i class="fa-solid fa-bolt fa-xs"></i>
                <span id="btnStartLabel">Mulai Proses</span>
            </button>
        </div>

        {{-- ══════════════════════════════════
             PANEL 4 — PROCESSING
             ══════════════════════════════════ --}}
        <div class="pdf-panel pdf-hidden" id="panelProcessing">
            <div class="pdf-proc-body">
                <div class="pdf-proc-anim">
                    <div class="pdf-proc-ring"></div>
                    <div class="pdf-proc-dot"></div>
                </div>
                <h2 class="pdf-proc-title" id="procTitle">Memproses…</h2>
                <p class="pdf-proc-sub" id="procSub">Harap tunggu, jangan tutup tab ini.</p>

                <div class="pdf-progress-wrap" id="progressWrap">
                    <div class="pdf-progress-meta">
                        <span class="pdf-progress-lbl" id="progressLbl">Memulai…</span>
                        <span class="pdf-progress-pct" id="progressPct">0%</span>
                    </div>
                    <div class="pdf-progress-track">
                        <div class="pdf-progress-fill" id="progressFill" style="width:0%"></div>
                    </div>
                </div>

                <div class="pdf-proc-steps" id="procSteps">
                    <div class="pdf-proc-step active" id="ps1">
                        <div class="pdf-ps-dot"></div>
                        <span>Memuat library</span>
                    </div>
                    <div class="pdf-proc-step" id="ps2">
                        <div class="pdf-ps-dot"></div>
                        <span>Membaca file</span>
                    </div>
                    <div class="pdf-proc-step" id="ps3">
                        <div class="pdf-ps-dot"></div>
                        <span>Memproses</span>
                    </div>
                    <div class="pdf-proc-step" id="ps4">
                        <div class="pdf-ps-dot"></div>
                        <span>Menyimpan</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════
             PANEL 5 — RESULT
             ══════════════════════════════════ --}}
        <div class="pdf-panel pdf-hidden" id="panelResult">
            <div class="pdf-result-body">
                <div class="pdf-result-check">
                    <i class="fa-solid fa-check"></i>
                </div>

                <h2 class="pdf-result-title">Selesai!</h2>
                <p class="pdf-result-sub" id="resultSub">File Anda siap diunduh.</p>

                {{-- Size comparison — compress only --}}
                <div class="pdf-size-compare pdf-hidden" id="sizeCompare">
                    <div class="pdf-sc-item">
                        <span class="pdf-sc-lbl">Ukuran Asli</span>
                        <span class="pdf-sc-val pdf-sc-before" id="scBefore">—</span>
                    </div>
                    <div class="pdf-sc-arrow">
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                    <div class="pdf-sc-item">
                        <span class="pdf-sc-lbl">Setelah Kompres</span>
                        <span class="pdf-sc-val pdf-sc-after" id="scAfter">—</span>
                    </div>
                    <div class="pdf-sc-badge" id="scBadge">
                        <i class="fa-solid fa-arrow-trend-down fa-xs"></i>
                        <span id="scSaved">—</span>
                    </div>
                </div>

                <div class="pdf-result-actions" id="btnDownloadWrap">
                    <a href="#" id="btnDownload" class="pdf-btn-primary pdf-btn-download"
                       download="mediatools_result.pdf">
                        <i class="fa-solid fa-download fa-xs"></i>
                        <span>Download PDF</span>
                    </a>
                    <div class="pdf-result-secondary-row">
                        <button class="pdf-btn-ghost" id="btnProcessAgain">
                            <i class="fa-solid fa-rotate-right fa-xs"></i>
                            Proses File Lain
                        </button>
                        <button class="pdf-btn-ghost" id="btnChangeTool">
                            <i class="fa-solid fa-grid-2 fa-xs"></i>
                            Ganti Fitur
                        </button>
                    </div>
                </div>

                <p class="pdf-result-note">
                    <i class="fa-solid fa-shield-halved fa-xs"></i>
                    <span id="resultNoteText">File tidak pernah meninggalkan browser Anda — privasi 100%.</span>
                </p>
            </div>
        </div>

    </div>{{-- /pdf-card --}}

    {{-- ── HOW IT WORKS ── --}}
    <section class="pdf-howto">
        <h2 class="pdf-howto-title">Cara Kerja</h2>
        <div class="pdf-howto-grid">
            <div class="pdf-hw-item">
                <div class="pdf-hw-num">1</div>
                <h4>Pilih Fitur</h4>
                <p>Merge, Split, atau Compress — tiap fitur punya cara kerja berbeda</p>
            </div>
            <div class="pdf-hw-item">
                <div class="pdf-hw-num">2</div>
                <h4>Upload PDF</h4>
                <p>Drag &amp; drop atau klik pilih file. Merge mendukung banyak file sekaligus</p>
            </div>
            <div class="pdf-hw-item">
                <div class="pdf-hw-num">3</div>
                <h4>Konfigurasi</h4>
                <p>Atur rentang halaman (Split) atau level kompresi (Compress)</p>
            </div>
            <div class="pdf-hw-item">
                <div class="pdf-hw-num">4</div>
                <h4>Download</h4>
                <p>Unduh PDF atau ZIP hasil proses — selesai dalam hitungan detik</p>
            </div>
        </div>
    </section>

</div>{{-- /pdf-wrap --}}
</div>{{-- /pdf-page --}}

{{-- Toast --}}
<div class="pdf-toast" id="pdfToast">
    <div class="pdf-toast-icon" id="pdfToastIcon">
        <i class="fa-solid fa-check fa-xs" id="pdfToastIco"></i>
    </div>
    <div class="pdf-toast-body">
        <span class="pdf-toast-type" id="pdfToastType">Sukses</span>
        <span class="pdf-toast-msg" id="pdfToastMsg">Berhasil!</span>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/pdfutilities.js') }}"></script>
@endpush
@endsection