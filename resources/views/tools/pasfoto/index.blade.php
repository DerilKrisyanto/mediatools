@extends('layouts.app')

@section('title', 'Smart Photo Studio — Pas Foto & Background Remover | MediaTools')
@section('meta_description', 'Buat pas foto online & hapus background foto dengan AI BiRefNet dalam satu tools. Ukuran 2×3, 3×4, 4×6. Background merah, biru, putih. Export JPG & PDF. Gratis, tanpa daftar.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pasfoto.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
@endpush

@section('content')

{{-- ════ GLOBAL LOADING OVERLAY ════ --}}
<div class="pf-loading" id="pf-loading" role="status" aria-live="polite">
    <div class="pf-loading-ring"></div>
    <p class="pf-loading-text" id="pf-loading-text">Memproses…</p>
</div>

{{-- ════ TOAST ════ --}}
<div class="pf-toast" id="pf-toast" role="alert" aria-live="assertive">
    <i class="fa-solid fa-circle-check"></i>
    <span id="pf-toast-msg"></span>
</div>

{{-- ════════════════════════════════════════════════════════
     HERO
════════════════════════════════════════════════════════ --}}
<div class="pf-hero">
    <nav aria-label="Breadcrumb" class="flex justify-center mb-5">
        <ol class="flex items-center gap-2 text-xs text-gray-500">
            <li><a href="{{ url('/') }}" class="hover:text-[#a3e635] transition-colors">Home</a></li>
            <li><i class="fa-solid fa-chevron-right text-[10px]"></i></li>
            <li class="text-[#a3e635] font-semibold">Smart Photo Studio</li>
        </ol>
    </nav>

    <div class="pf-hero-badge">
        <i class="fa-solid fa-brain"></i> BiRefNet AI · Dual-Mode Studio
    </div>

    <h1>
        Smart <span class="gradient-text">Photo Studio</span> —<br>
        Pas Foto & Background Remover
    </h1>
    <p>
        Satu tools untuk dua kebutuhan: buat pas foto ukuran 2×3, 3×4, 4×6 dengan
        background pilihan — atau hapus background foto apapun dengan AI BiRefNet terbaik.
    </p>

    <div class="pf-hero-badges">
        @foreach([
            ['fa-brain',         'BiRefNet AI'],
            ['fa-scissors',      'Remove BG Presisi'],
            ['fa-camera',        'Pas Foto Instan'],
            ['fa-file-pdf',      'Export PDF'],
            ['fa-mobile-screen', 'Mobile Friendly'],
            ['fa-shield-halved', 'Privasi Aman'],
        ] as $b)
        <div class="pf-hero-badge-item">
            <i class="fa-solid {{ $b[0] }}"></i> {{ $b[1] }}
        </div>
        @endforeach
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     MAIN APP
════════════════════════════════════════════════════════ --}}
<div id="pf-app" role="main">

    {{-- ─────────────────────────────────────────
         VIEW: UPLOAD
    ───────────────────────────────────────── --}}
    <div id="view-upload" class="pf-view" style="display:block">

        {{-- Dropzone --}}
        <div class="dz-zone" id="dz-zone" role="button" tabindex="0"
             aria-label="Klik atau seret gambar ke sini">
            <input type="file" id="dz-input" hidden multiple
                   accept="image/jpeg,image/jpg,image/png,image/webp">

            <div class="dz-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
            <h2 class="dz-title">Seret &amp; lepas gambar di sini</h2>
            <p class="dz-sub">atau</p>
            <button class="btn-dz-browse" id="btn-dz-browse" type="button">
                <i class="fa-solid fa-folder-open"></i>
                Pilih File
            </button>
            <p class="dz-formats">JPG · PNG · WEBP &nbsp;·&nbsp; Maks. 20 MB · Ctrl+V untuk paste</p>
        </div>

        {{-- Upload preview (shown after file selected) --}}
        <div id="upload-preview" class="upload-preview"></div>

        {{-- Mode selector (shown after file selected) --}}
        <div id="mode-selector" class="mode-selector" role="group" aria-label="Pilih mode">

            <button id="btn-mode-pasfoto" class="mode-card" type="button">
                <div class="mode-card-icon"><i class="fa-solid fa-id-card"></i></div>
                <div>
                    <div class="mode-card-title">Buat Pas Foto</div>
                    <div class="mode-card-desc">
                        Crop otomatis · Pilih background (merah/biru/putih) ·
                        Ukuran 2×3, 3×4, 4×6 · Export JPG &amp; PDF cetak
                    </div>
                </div>
                <div class="mode-card-arrow">
                    <span>Mulai buat pas foto</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </div>
            </button>

            <button id="btn-mode-bgr" class="mode-card" type="button">
                <div class="mode-card-icon"><i class="fa-solid fa-scissors"></i></div>
                <div>
                    <div class="mode-card-title">Hapus Background</div>
                    <div class="mode-card-desc">
                        AI BiRefNet · Presisi pada rambut &amp; detail halus ·
                        Download PNG transparan atau dengan warna latar pilihan
                    </div>
                </div>
                <div class="mode-card-arrow">
                    <span>Hapus background foto</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </div>
            </button>

        </div>

        {{-- Quality toggle --}}
        <div class="upload-quality-wrap">
            <div class="pf-q-label"><i class="fa-solid fa-sliders"></i> Kualitas AI</div>
            <div class="pf-q-btns" id="upload-quality-btns">
                <button class="pf-q-btn" data-q="fast" type="button">
                    <i class="fa-solid fa-bolt"></i>
                    Cepat
                    <small>~5–10 dtk</small>
                </button>
                <button class="pf-q-btn active" data-q="high" type="button">
                    <i class="fa-solid fa-gem"></i>
                    HD
                    <small>~10–25 dtk</small>
                </button>
            </div>
        </div>

        {{-- Drag multiple tip --}}
        <p style="text-align:center;font-size:12px;color:#475569;margin-top:12px;">
            <i class="fa-solid fa-images" style="color:#a3e635"></i>
            Seret <strong style="color:#94a3b8">2+ gambar sekaligus</strong>
            untuk mode batch otomatis (background remover)
        </p>
    </div>

    {{-- ─────────────────────────────────────────
         VIEW: CROP  (Pas Foto only)
    ───────────────────────────────────────── --}}
    <div id="view-crop" class="pf-view pf-card">

        <div class="crop-header">
            <div>
                <div class="crop-header-title">
                    <i class="fa-solid fa-crop" style="color:#a3e635;margin-right:8px"></i>
                    Potong Foto
                </div>
                <div class="crop-header-sub">Atur posisi wajah dalam frame · Pilih ukuran pas foto</div>
            </div>
            <div class="crop-size-btns" id="crop-size-btns">
                @foreach([
                    ['2x3', '2×3 cm', 'KTP · SIM'],
                    ['3x4', '3×4 cm', 'Ijazah · CPNS'],
                    ['4x6', '4×6 cm', 'Lamaran'],
                ] as $sz)
                <button class="crop-size-btn {{ $sz[0] === '3x4' ? 'active' : '' }}"
                        data-size="{{ $sz[0] }}" type="button">
                    {{ $sz[1] }}
                    <small>{{ $sz[2] }}</small>
                </button>
                @endforeach
            </div>
        </div>

        <div class="crop-canvas-wrap">
            <img id="crop-img" src="" alt="Foto untuk dipotong" style="display:block;max-width:100%">
        </div>

        <div class="crop-toolbar">
            <span style="font-size:11px;color:#475569;margin-right:4px">Transformasi:</span>
            <button class="crop-tool-btn" id="btn-crop-rot-l" type="button" title="Putar kiri 90°">
                <i class="fa-solid fa-rotate-left"></i> Putar ←
            </button>
            <button class="crop-tool-btn" id="btn-crop-rot-r" type="button" title="Putar kanan 90°">
                <i class="fa-solid fa-rotate-right"></i> Putar →
            </button>
            <button class="crop-tool-btn" id="btn-crop-flip-h" type="button" title="Balik horizontal">
                <i class="fa-solid fa-left-right"></i> Balik
            </button>
        </div>

        <div class="crop-actions">
            <button class="btn-ghost" id="btn-crop-back" type="button">
                <i class="fa-solid fa-arrow-left text-xs"></i> Ganti Foto
            </button>
            <button class="btn-primary" id="btn-crop-process" type="button" style="margin-left:auto">
                <i class="fa-solid fa-wand-magic-sparkles text-sm"></i>
                Proses dengan AI
            </button>
        </div>
    </div>

    {{-- ─────────────────────────────────────────
         VIEW: PROCESSING
    ───────────────────────────────────────── --}}
    <div id="view-processing" class="pf-view pf-card">
        <div class="proc-wrap">

            <div class="proc-thumb-ring">
                <img id="proc-thumb" src="" alt="" class="proc-thumb">
                <div class="proc-ring-anim"></div>
            </div>

            <div class="proc-info">
                <div class="proc-title">AI sedang bekerja…</div>
                <div class="proc-subtitle">
                    BiRefNet memproses piksel per piksel untuk hasil terbaik
                </div>
            </div>

            <div class="proc-bar-wrap">
                <div class="proc-bar-bg">
                    <div class="proc-bar-fill" id="proc-fill"></div>
                </div>
                <div class="proc-pct-row">
                    <span class="proc-pct" id="proc-pct">0%</span>
                    <span class="proc-label" id="proc-label">Mempersiapkan…</span>
                </div>
            </div>

            <div class="proc-steps">
                @foreach(['Upload','Analisis AI','Segmentasi','Alpha Matte','Finalisasi'] as $s)
                <div class="proc-step-chip">
                    <i class="fa-solid fa-circle-dot" style="color:#a3e635;font-size:8px"></i>
                    {{ $s }}
                </div>
                @endforeach
            </div>

        </div>
    </div>

    {{-- ─────────────────────────────────────────
         VIEW: PAS FOTO STUDIO
    ───────────────────────────────────────── --}}
    <div id="view-studio" class="pf-view pf-card">
        <div class="studio-layout">

            {{-- Left: Preview --}}
            <div class="studio-preview-panel">
                <div>
                    <div class="opt-label">
                        <i class="fa-solid fa-eye" style="color:#a3e635"></i> Preview Real-Time
                    </div>
                    <div class="studio-preview-container" id="studio-preview-container">
                        <div class="studio-preview-loading">
                            <div class="pf-loading-ring" style="width:36px;height:36px"></div>
                            <span>Memuat preview…</span>
                        </div>
                    </div>
                </div>

                <div class="studio-dim-chip" id="studio-dim-chip">
                    <i class="fa-solid fa-ruler-combined" style="color:#a3e635;font-size:10px"></i>
                    354×472px · 3×4 cm
                </div>

                <div class="studio-preview-actions">
                    <button class="btn-ghost btn-sm" id="btn-studio-recrop" type="button">
                        <i class="fa-solid fa-crop text-xs"></i> Crop Ulang
                    </button>
                    <button class="btn-ghost btn-sm" id="btn-studio-reset" type="button">
                        <i class="fa-solid fa-arrow-rotate-left text-xs"></i> Foto Baru
                    </button>
                </div>
            </div>

            {{-- Right: Settings --}}
            <div class="studio-settings-panel">

                {{-- Background colour --}}
                <div>
                    <div class="opt-label">
                        <i class="fa-solid fa-palette" style="color:#a3e635"></i> Warna Background
                    </div>
                    <div class="studio-bg-swatches" id="studio-bg-swatches">
                        @php
                        $bgs = [
                            ['#cc1414', 'Merah'],
                            ['#0f52ba', 'Biru'],
                            ['#ffffff', 'Putih'],
                            ['#f3ede1', 'Krem'],
                            ['#1a1a1a', 'Hitam'],
                        ];
                        @endphp
                        @foreach($bgs as $i => [$hex, $lbl])
                        <button class="studio-swatch {{ $i === 0 ? 'active' : '' }}"
                                data-bg="{{ $hex }}"
                                title="{{ $lbl }}"
                                type="button"
                                style="background:{{ $hex }};{{ $hex === '#ffffff' ? 'border-color:rgba(255,255,255,0.2)' : '' }}">
                        </button>
                        @endforeach
                        <button class="studio-swatch swatch--custom"
                                data-bg="custom" id="swatch-custom"
                                title="Warna kustom" type="button">
                            <input type="color" id="studio-custom-color" value="#cc1414"
                                   aria-label="Pilih warna kustom">
                        </button>
                    </div>
                </div>

                {{-- Ukuran output --}}
                <div>
                    <div class="opt-label">
                        <i class="fa-solid fa-ruler" style="color:#a3e635"></i> Ukuran Output
                    </div>
                    <div class="studio-size-btns" id="studio-size-btns">
                        @foreach([
                            ['2x3', '2×3 cm', 'KTP, SIM'],
                            ['3x4', '3×4 cm', 'Ijazah'],
                            ['4x6', '4×6 cm', 'Lamaran'],
                        ] as $sz)
                        <button class="studio-size-btn {{ $sz[0] === '3x4' ? 'active' : '' }}"
                                data-size="{{ $sz[0] }}" type="button">
                            {{ $sz[1] }}
                            <small>{{ $sz[2] }}</small>
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Download JPG --}}
                <div>
                    <div class="opt-label">
                        <i class="fa-solid fa-download" style="color:#a3e635"></i> Unduh Foto
                    </div>
                    <div class="studio-dl-btns">
                        <button id="btn-studio-dl-jpg" class="btn-primary btn-studio-dl-jpg" type="button">
                            <i class="fa-solid fa-file-image text-sm"></i>
                            Unduh JPG
                        </button>
                    </div>
                </div>

                {{-- PDF export --}}
                <div>
                    <div class="opt-label">
                        <i class="fa-solid fa-file-pdf" style="color:#a3e635"></i> Cetak PDF (A4)
                    </div>
                    <div class="studio-pdf-row">
                        <select id="studio-pdf-copies" class="studio-pdf-copies" aria-label="Jumlah foto">
                            @foreach([2, 4, 6, 8, 12, 16, 24] as $n)
                            <option value="{{ $n }}" {{ $n === 4 ? 'selected' : '' }}>{{ $n }} foto</option>
                            @endforeach
                        </select>
                        <span class="studio-pdf-perpage" id="studio-pdf-per-page">Maks. 30 foto/hal A4</span>
                    </div>
                    <button id="btn-studio-dl-pdf" class="btn-outline btn-studio-dl-pdf" type="button" style="width:100%;margin-top:8px">
                        <i class="fa-solid fa-file-pdf text-sm"></i>
                        Buat &amp; Unduh PDF
                    </button>
                </div>

                {{-- Tips --}}
                <div style="padding:12px;background:rgba(163,230,53,0.04);border:1px solid rgba(163,230,53,0.12);border-radius:12px;font-size:11px;color:#64748b;line-height:1.6;">
                    <i class="fa-solid fa-lightbulb" style="color:#a3e635;margin-right:5px"></i>
                    <strong style="color:#94a3b8">Tips:</strong>
                    PDF dicetak pada kertas A4. Bawa ke tukang foto terdekat untuk cetak.
                    Resolusi 300 DPI — kualitas profesional siap cetak.
                </div>

            </div>
        </div>
    </div>

    {{-- ─────────────────────────────────────────
         VIEW: BGR SINGLE RESULT
    ───────────────────────────────────────── --}}
    <div id="view-bgr" class="pf-view pf-card">
        <div class="bgr-result-wrap">

            {{-- Title row --}}
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
                <div>
                    <div style="font-size:17px;font-weight:700;color:#f1f5f9">
                        <i class="fa-solid fa-check-circle" style="color:#a3e635;margin-right:8px"></i>
                        Berhasil! Background Dihapus
                    </div>
                    <div style="font-size:12px;color:#64748b;margin-top:3px">
                        Geser slider untuk bandingkan sebelum &amp; sesudah
                    </div>
                </div>
                <button id="btn-bgr-new" class="btn-ghost btn-sm" type="button">
                    <i class="fa-solid fa-plus text-xs"></i> Foto Baru
                </button>
            </div>

            {{-- Before / After Compare Slider --}}
            <div class="bgr-compare" id="bgr-compare">
                <img id="bgr-orig-img" src="" alt="Sebelum" class="bgr-before-layer">
                <div id="bgr-after-layer" class="bgr-after-layer">
                    <img id="bgr-res-img" src="" alt="Sesudah">
                </div>
                <div class="bgr-compare-labels">
                    <span class="bgr-compare-lbl">Sebelum</span>
                    <span class="bgr-compare-lbl">Sesudah</span>
                </div>
                <div class="bgr-handle" id="bgr-handle">
                    <div class="bgr-handle-knob">
                        <i class="fa-solid fa-arrows-left-right"></i>
                    </div>
                </div>
            </div>

            {{-- Controls: BG colour + actions --}}
            <div class="bgr-controls-row">

                <div class="bgr-bg-panel">
                    <div class="opt-label">
                        <i class="fa-solid fa-palette" style="color:#a3e635"></i>
                        Warna Latar (untuk JPG)
                    </div>
                    <div class="bgr-bg-swatches" id="bgr-bg-swatches">
                        @foreach([
                            ['transparent','Transparan','bgr-swatch--transparent'],
                            ['#ffffff','Putih',''],
                            ['#000000','Hitam',''],
                            ['#cc1414','Merah',''],
                            ['#0f52ba','Biru',''],
                        ] as [$val, $lbl, $cls])
                        <button class="bgr-swatch {{ $cls }} {{ $val === 'transparent' ? 'active' : '' }}"
                                data-bg="{{ $val }}" title="{{ $lbl }}" type="button"
                                @if($val !== 'transparent') style="background:{{ $val }}" @endif>
                        </button>
                        @endforeach
                    </div>
                </div>

                <div class="bgr-actions">
                    <button id="btn-bgr-dl-png" class="btn-primary" type="button">
                        <i class="fa-solid fa-download text-sm"></i>
                        PNG Transparan
                    </button>
                    <button id="btn-bgr-dl-jpg" class="btn-outline" type="button">
                        <i class="fa-solid fa-image text-sm"></i>
                        JPG + BG
                    </button>
                </div>
            </div>

            {{-- Switch to PasFoto hint --}}
            <div class="bgr-switch-hint">
                <i class="fa-solid fa-id-card"></i>
                <div>
                    <strong style="color:#94a3b8">Butuh pas foto?</strong>
                    <span style="margin-left:6px">
                        Buat pas foto resmi (2×3, 3×4, 4×6) dari foto ini dengan satu klik.
                    </span>
                    <button id="btn-bgr-to-pasfoto" class="btn-ghost btn-sm" type="button" style="margin-top:6px;display:block">
                        <i class="fa-solid fa-arrow-right text-xs"></i> Buat Pas Foto Sekarang
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- ─────────────────────────────────────────
         VIEW: MULTI BATCH
    ───────────────────────────────────────── --}}
    <div id="view-multi" class="pf-view pf-card">
        <div class="multi-header">
            <div>
                <div class="multi-header-title">
                    <i class="fa-solid fa-images" style="color:#a3e635;margin-right:8px"></i>
                    Batch Background Remover
                </div>
                <div class="multi-header-sub">AI memproses gambar satu per satu secara otomatis</div>
            </div>
            <div class="multi-bulk-actions">
                <button id="btn-multi-add" class="btn-ghost btn-sm" type="button">
                    <i class="fa-solid fa-plus text-xs"></i> Tambah
                </button>
                <button id="btn-multi-zip" class="btn-outline btn-sm" type="button" disabled>
                    <i class="fa-solid fa-file-zipper text-xs"></i> Unduh ZIP
                </button>
                <button id="btn-multi-new" class="btn-ghost btn-sm" type="button">
                    <i class="fa-solid fa-rotate-left text-xs"></i> Reset
                </button>
            </div>
        </div>

        <div class="multi-grid" id="multi-grid"></div>
    </div>

</div>{{-- /#pf-app --}}


{{-- ════════════════════════════════════════════════════════
     HOW IT WORKS
════════════════════════════════════════════════════════ --}}
<section class="pf-howto">
    <div class="pf-howto-inner reveal">
        <div style="text-align:center;margin-bottom:4px">
            <div class="section-label" style="display:inline-flex">
                <i class="fa-solid fa-circle-question"></i> Cara Penggunaan
            </div>
        </div>
        <h2 style="text-align:center;font-size:clamp(1.4rem,4vw,2rem);font-weight:800;color:#f1f5f9;margin-bottom:8px">
            Dua Mode, Satu Platform
        </h2>
        <p style="text-align:center;color:#64748b;font-size:14px;max-width:480px;margin:0 auto">
            Pilih mode sesuai kebutuhan Anda setelah mengupload foto.
        </p>

        <div class="pf-howto-grid">

            {{-- Pas Foto mode --}}
            <div>
                <div class="howto-col-title">
                    <i class="fa-solid fa-id-card"></i> Mode Pas Foto
                </div>
                @foreach([
                    ['Upload Foto', 'Seret & lepas atau klik tombol pilih file. Format JPG, PNG, WEBP.'],
                    ['Pilih Mode Pas Foto', 'Klik kartu "Buat Pas Foto" yang muncul setelah file dipilih.'],
                    ['Crop & Pilih Ukuran', 'Atur posisi wajah dengan Cropper. Pilih ukuran 2×3, 3×4, atau 4×6.'],
                    ['AI Hapus Background', 'BiRefNet memproses foto hasil crop secara otomatis.'],
                    ['Atur & Download', 'Pilih warna background, preview real-time, unduh JPG atau PDF cetak A4.'],
                ] as $i => [$title, $desc])
                <div class="howto-step">
                    <div class="howto-step-num">{{ $i + 1 }}</div>
                    <div>
                        <div class="howto-step-title">{{ $title }}</div>
                        <div class="howto-step-desc">{{ $desc }}</div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- BG Remover mode --}}
            <div>
                <div class="howto-col-title">
                    <i class="fa-solid fa-scissors"></i> Mode Hapus Background
                </div>
                @foreach([
                    ['Upload Foto (1 atau Banyak)', 'Satu foto: mode single. Dua foto atau lebih: mode batch otomatis.'],
                    ['Pilih Mode Hapus Background', 'Klik kartu "Hapus Background" untuk foto tunggal.'],
                    ['AI Proses Otomatis', 'BiRefNet menghapus background dengan presisi tinggi, termasuk rambut.'],
                    ['Bandingkan Hasilnya', 'Gunakan slider before/after interaktif untuk melihat perbedaan.'],
                    ['Unduh PNG atau JPG', 'PNG transparan atau JPG dengan warna latar pilihan Anda.'],
                ] as $i => [$title, $desc])
                <div class="howto-step">
                    <div class="howto-step-num">{{ $i + 1 }}</div>
                    <div>
                        <div class="howto-step-title">{{ $title }}</div>
                        <div class="howto-step-desc">{{ $desc }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</section>

@endsection

@push('scripts')
{{-- Cropper.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
{{-- jsPDF --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
{{-- JSZip (for batch download) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
{{-- Main tool script --}}
<script src="{{ asset('js/pasfoto.js') }}" defer></script>
@endpush