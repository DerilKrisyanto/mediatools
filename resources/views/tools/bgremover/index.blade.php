@extends('layouts.app')

@section('og_image', 'bgremover')
@section('title', 'Hapus Background & Buat Pas Foto Online Gratis — AI BiRefNet | MediaTools')
@section('meta_description', 'Hapus background foto otomatis dengan AI BiRefNet ATAU buat pas foto 2x3, 3x4, 4x6 dengan background merah/biru/hijau. Gratis, tanpa daftar.')
@section('meta_keywords', 'hapus background foto, remove background, background remover, buat pas foto online, pas foto 2x3, 3x4, 4x6')
@include('seo.bgremover')

@section('content')
<link rel="stylesheet" href="{{ asset('css/tools-base.css') }}">
<link rel="stylesheet" href="{{ asset('css/bgremover.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<script>document.body.classList.add('tlb-active');</script>

<div class="tlb-page tlb-purple" id="tlbPage_bgremover">

{{-- ════ TLB HEADER ════ --}}
<div class="tlb-header">
    <div class="tlb-header-inner">
        <div>
            <div class="tlb-header-label-row">
                <div class="tlb-header-icon">
                    <i class="fa-solid fa-scissors"></i>
                </div>
                <span class="tlb-header-site">MediaTools</span>
            </div>
            <div class="tlb-header-badges">
                <span class="tlb-hbadge"><i class="fa-solid fa-brain"></i> BiRefNet AI</span>
                <span class="tlb-hbadge"><i class="fa-solid fa-wand-magic-sparkles"></i> Alpha Matting</span>
                <span class="tlb-hbadge"><i class="fa-solid fa-id-card"></i> Pas Foto</span>
                <span class="tlb-hbadge"><i class="fa-solid fa-infinity"></i> Gratis</span>
            </div>
            <nav aria-label="Breadcrumb" class="flex justify-center mb-5">
                <ol class="flex items-center gap-2 text-xs" style="color:var(--text-3)">
                    <li><a href="{{ url('/') }}" style="color:var(--text-3);text-decoration:none;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text-3)'">Home</a></li>
                    <li style="margin:0 4px;font-size:9px;">›</li>
                    <li style="color:var(--accent);font-weight:600;">Hapus Background & Buat Pas Foto Online Gratis</li>
                </ol>
            </nav>
            <h1 class="tlb-header-title">Photo <span>Tools Pro.</span></h1>
            <p class="tlb-header-sub">Hapus background presisi dengan AI BiRefNet atau buat pas foto 2×3, 3×4, 4×6 siap cetak.</p>
        </div>
    </div>
</div>
<div class="tlb-header-curve"></div>

<div class="tlb-body">

{{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     ROOT PAGE WRAPPER
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
<div class="bgr-root" id="bgrRoot">

  {{-- ━━ BACK TO MODE BUTTON (shown when user is in a flow, hidden at mode-select) ━━ --}}
  {{-- FIX: This element was completely missing from the original blade.           --}}
  {{-- ui.js calls getElementById('btnBackToMode') and sets .hidden on it.        --}}
  <button id="btnBackToMode" class="bgr-back-btn" type="button" hidden>
    <i class="fa-solid fa-grid-2"></i>
    <span>Ganti Mode</span>
  </button>

  {{-- ━━ STEP INDICATOR (shown when user is in a flow) ━━ --}}
  {{-- FIX: This element was completely missing — only the HTML comment existed.  --}}
  {{-- ui.js calls getElementById('bgrStepper') and calls stepIndicator1..4.     --}}
  <div class="bgr-stepper" id="bgrStepper" hidden>
    <div class="bgr-stepper-inner">
      <div class="bgr-step" id="stepIndicator1">
        <div class="bgr-step-num">1</div>
        <span class="bgr-step-label" id="stepLabel1">Upload</span>
      </div>
      <div class="bgr-step-line"></div>
      <div class="bgr-step" id="stepIndicator2">
        <div class="bgr-step-num">2</div>
        <span class="bgr-step-label" id="stepLabel2">Konfigurasi</span>
      </div>
      <div class="bgr-step-line"></div>
      <div class="bgr-step" id="stepIndicator3">
        <div class="bgr-step-num">3</div>
        <span class="bgr-step-label" id="stepLabel3">Proses</span>
      </div>
      <div class="bgr-step-line"></div>
      <div class="bgr-step" id="stepIndicator4">
        <div class="bgr-step-num"><i class="fa-solid fa-check"></i></div>
        <span class="bgr-step-label" id="stepLabel4">Download</span>
      </div>
    </div>
  </div>

  {{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       VIEW: MODE SELECT
  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
  <div id="viewModeSelect" class="bgr-view bgr-view-mode">
    <div class="bgr-mode-heading">
      <h2>Pilih yang ingin Anda lakukan</h2>
      <p>Dua tools powerful dalam satu halaman — pilih sesuai kebutuhan Anda.</p>
    </div>

    <div class="bgr-mode-grid">
      {{-- Card: BG Remover --}}
      <div class="bgr-mode-card" data-mode="bgr">
        <div class="bgr-mc-badge green"><i class="fa-solid fa-brain"></i> AI BiRefNet</div>
        <div class="bgr-mc-icon">
          <i class="fa-solid fa-wand-magic-sparkles"></i>
        </div>
        <h3 class="bgr-mc-title">Hapus Background</h3>
        <p class="bgr-mc-desc">AI hapus latar otomatis dengan detail rambut &amp; tepi yang presisi. Poles manual dengan brush interaktif.</p>
        <ul class="bgr-mc-features">
          <li><i class="fa-solid fa-check"></i> BiRefNet AI + Alpha Matting</li>
          <li><i class="fa-solid fa-check"></i> Editor brush manual (hapus / pulihkan)</li>
          <li><i class="fa-solid fa-check"></i> Batch hingga 5 foto sekaligus</li>
          <li><i class="fa-solid fa-check"></i> Download PNG transparan / JPG / PDF</li>
        </ul>
        <button class="bgr-mc-btn primary" id="btnModeBgr" type="button">
          <i class="fa-solid fa-wand-magic-sparkles"></i>
          <span>Hapus Background</span>
          <i class="fa-solid fa-arrow-right bgr-mc-arrow"></i>
        </button>
      </div>

      {{-- Card: Pas Foto --}}
      <div class="bgr-mode-card" data-mode="pf">
        <div class="bgr-mc-badge blue"><i class="fa-solid fa-id-card"></i> Dokumen Resmi</div>
        <div class="bgr-mc-icon blue">
          <i class="fa-solid fa-id-card"></i>
        </div>
        <h3 class="bgr-mc-title">Buat Pas Foto</h3>
        <p class="bgr-mc-desc">Pas foto profesional untuk CPNS, lamaran kerja, ijazah, KTP. Background solid bersih dijamin presisi AI.</p>
        <ul class="bgr-mc-features">
          <li><i class="fa-solid fa-check"></i> Ukuran 2×3, 3×4, 4×6 cm (standar Indonesia)</li>
          <li><i class="fa-solid fa-check"></i> Background merah, biru, hijau, putih</li>
          <li><i class="fa-solid fa-check"></i> AI BiRefNet (hasil pro, bukan flood fill)</li>
          <li><i class="fa-solid fa-check"></i> Download JPG + PDF A4 siap cetak</li>
        </ul>
        <button class="bgr-mc-btn secondary" id="btnModePf" type="button">
          <i class="fa-solid fa-id-card"></i>
          <span>Buat Pas Foto</span>
          <i class="fa-solid fa-arrow-right bgr-mc-arrow"></i>
        </button>
      </div>
    </div>

    <div class="bgr-mode-footer">
      <span><i class="fa-solid fa-shield-halved"></i> File dihapus otomatis dari server</span>
      <span><i class="fa-solid fa-lock"></i> Tidak ada data tersimpan</span>
      <span><i class="fa-solid fa-bolt"></i> Didukung BiRefNet AI</span>
    </div>
  </div>

  {{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       ═══ BG REMOVER FLOW ═══
  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}

  {{-- ━━ [BGR] STEP 1: UPLOAD ━━ --}}
  <div id="viewUpload" class="bgr-view bgr-view-upload" hidden>
    <div class="bgr-upload-layout">

      {{-- Left: Dropzone --}}
      <div class="bgr-upload-main">
        <div class="bgr-section-title">
          <span class="bgr-step-pill">1</span>
          Upload Gambar
          <span class="bgr-badge-sm">Maks. 5 file · 20 MB/file</span>
        </div>

        <div class="bgr-dropzone" id="dropzone">
          <input type="file" id="fileInput" accept="image/jpeg,image/jpg,image/png,image/webp" multiple hidden>
          <div class="bgr-dz-content">
            <div class="bgr-dz-icon-wrap">
              <i class="fa-solid fa-cloud-arrow-up"></i>
            </div>
            <h3>Drag &amp; drop gambar di sini</h3>
            <p>atau</p>
            <button class="bgr-btn-browse" id="btnBrowse" type="button">
              <i class="fa-solid fa-folder-open"></i> Pilih File
            </button>
            <p class="bgr-dz-formats">JPG · PNG · WEBP &nbsp;·&nbsp; Ctrl+V untuk paste dari clipboard</p>
          </div>
          <div class="bgr-dz-dragover-overlay">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <span>Lepaskan untuk upload</span>
          </div>
        </div>

        {{-- File list preview --}}
        <div class="bgr-file-list" id="bgrFileList" hidden></div>

        {{-- Process button --}}
        <button class="bgr-btn-process" id="btnBgrProcess" type="button" disabled hidden>
          <i class="fa-solid fa-wand-magic-sparkles"></i>
          <span id="btnBgrProcessLabel">Proses Gambar</span>
          <i class="fa-solid fa-arrow-right"></i>
        </button>
      </div>

      {{-- Right: Options --}}
      <div class="bgr-upload-options">
        <div class="bgr-options-card">
          <div class="bgr-opt-section">
            <label class="bgr-opt-label">
              <i class="fa-solid fa-sliders"></i> Kualitas AI
            </label>
            <div class="bgr-quality-btns" id="qualityBtns">
              <button class="bgr-q-btn active" data-q="fast" type="button">
                <div class="bgr-q-icon"><i class="fa-solid fa-bolt"></i></div>
                <div class="bgr-q-text">
                  <strong>Cepat</strong>
                  <small>~5–10 detik</small>
                </div>
              </button>
              <button class="bgr-q-btn" data-q="high" type="button">
                <div class="bgr-q-icon"><i class="fa-solid fa-gem"></i></div>
                <div class="bgr-q-text">
                  <strong>HD Quality</strong>
                  <small>~10–20 detik</small>
                </div>
              </button>
            </div>
          </div>

          <div class="bgr-opt-divider"></div>

          <div class="bgr-opt-section">
            <label class="bgr-opt-label">
              <i class="fa-solid fa-palette"></i> Background Default
            </label>
            <p class="bgr-opt-hint">Pilih background untuk preview dan download JPG</p>
            <div class="bgr-swatches" id="bgSwatches">
              <button class="bgr-swatch active" data-bg="transparent" title="Transparan" type="button">
                <span class="bgr-sw-checker"></span>
                <span class="bgr-sw-label">PNG</span>
              </button>
              <button class="bgr-swatch" data-bg="#ffffff" title="Putih" type="button">
                <span class="bgr-sw-dot" style="background:#fff;border:1px solid #ddd;"></span>
                <span class="bgr-sw-label">Putih</span>
              </button>
              <button class="bgr-swatch" data-bg="#cc0000" title="Merah" type="button">
                <span class="bgr-sw-dot" style="background:#cc0000;"></span>
                <span class="bgr-sw-label">Merah</span>
              </button>
              <button class="bgr-swatch" data-bg="#0047ab" title="Biru" type="button">
                <span class="bgr-sw-dot" style="background:#0047ab;"></span>
                <span class="bgr-sw-label">Biru</span>
              </button>
              <button class="bgr-swatch" data-bg="#006400" title="Hijau" type="button">
                <span class="bgr-sw-dot" style="background:#006400;"></span>
                <span class="bgr-sw-label">Hijau</span>
              </button>
              <button class="bgr-swatch" data-bg="#000000" title="Hitam" type="button">
                <span class="bgr-sw-dot" style="background:#000;"></span>
                <span class="bgr-sw-label">Hitam</span>
              </button>
              <label class="bgr-swatch" title="Warna kustom">
                <span class="bgr-sw-custom"><i class="fa-solid fa-eye-dropper"></i></span>
                <span class="bgr-sw-label">Custom</span>
                <input type="color" id="customColor" value="#6366f1" style="display:none;">
              </label>
            </div>
          </div>

          <div class="bgr-opt-divider"></div>

          <div class="bgr-opt-note">
            <i class="fa-solid fa-circle-info"></i>
            <span>Upload 1 file → perbandingan before/after + editor brush<br>
            Upload 2–5 file → batch proses, download zip atau PDF</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ━━ [BGR] STEP 2: PROCESSING ━━ --}}
  <div id="viewProcessing" class="bgr-view bgr-view-processing" hidden>
    <div class="bgr-proc-card" id="bgrProcCard">
      <div class="bgr-proc-thumb-wrap">
        <img id="processingThumb" src="" alt="" class="bgr-proc-thumb">
        <div class="bgr-proc-spinner"></div>
      </div>
      <div class="bgr-proc-info">
        <h3 class="bgr-proc-title" id="processingTitle">Memproses gambar…</h3>
        <p class="bgr-proc-label" id="progressLabel">Mempersiapkan…</p>
        <div class="bgr-proc-bar-wrap">
          <div class="bgr-proc-bar">
            <div class="bgr-proc-fill" id="progressFill" style="width:0%"></div>
          </div>
          <span class="bgr-proc-pct" id="progressPct">0%</span>
        </div>
        <div class="bgr-proc-steps-mini" id="procStepsMini">
          <div class="bgr-proc-mini-step active" id="pmStep1"><i class="fa-solid fa-upload"></i> Upload</div>
          <div class="bgr-proc-mini-step" id="pmStep2"><i class="fa-solid fa-brain"></i> AI Analisis</div>
          <div class="bgr-proc-mini-step" id="pmStep3"><i class="fa-solid fa-scissors"></i> Hapus BG</div>
          <div class="bgr-proc-mini-step" id="pmStep4"><i class="fa-solid fa-check"></i> Selesai</div>
        </div>
        <p class="bgr-proc-security">
          <i class="fa-solid fa-shield-halved"></i>
          File diproses aman di server terenkripsi, dihapus otomatis setelah selesai
        </p>
      </div>
    </div>
  </div>

  {{-- ━━ [BGR] STEP 3: RESULT (single image — before/after comparison) ━━ --}}
  <div id="viewResult" class="bgr-view bgr-view-result" hidden>

    <div class="bgr-result-header">
      <div class="bgr-result-file-info">
        <i class="fa-solid fa-circle-check" style="color:var(--bgr-green,#16a34a)"></i>
        <span class="bgr-result-fname" id="resultFilename">—</span>
      </div>
      <div class="bgr-result-actions">
        <button class="bgr-btn-edit" id="btnResultEdit" type="button">
          <i class="fa-solid fa-paintbrush"></i>
          <span>Edit Manual</span>
        </button>
        <button class="bgr-btn-dl-png" id="btnResultDownloadPNG" type="button">
          <i class="fa-solid fa-download"></i>
          <span>PNG</span>
        </button>
        <button class="bgr-btn-dl-jpg" id="btnResultDownloadJPG" type="button">
          <i class="fa-solid fa-download"></i>
          <span>JPG</span>
        </button>
        <button class="bgr-btn-new" id="btnResultNew" type="button">
          <i class="fa-solid fa-plus"></i>
          <span class="bgr-hide-sm">Gambar Baru</span>
        </button>
      </div>
    </div>

    {{-- Before / After comparison slider --}}
    <div class="bgr-compare-outer">
      <div class="bgr-compare-wrap" id="compareWrap">
        <div class="bgr-compare-before" id="compareBefore">
          <img id="compareOrigImg" alt="Original" draggable="false">
          <span class="bgr-compare-label left">
            <i class="fa-solid fa-image"></i> Sebelum
          </span>
        </div>
        <div class="bgr-compare-after" id="compareAfter">
          <img id="compareResultImg" alt="Hasil" draggable="false">
          <span class="bgr-compare-label right">
            <i class="fa-solid fa-wand-magic-sparkles"></i> Sesudah
          </span>
        </div>
        <div class="bgr-compare-handle" id="compareHandle">
          <div class="bgr-compare-line"></div>
          <button class="bgr-compare-knob" type="button">
            <i class="fa-solid fa-left-right"></i>
          </button>
        </div>
      </div>
      <p class="bgr-compare-hint">
        <i class="fa-solid fa-hand-pointer"></i>
        Geser garis tengah untuk membandingkan sebelum &amp; sesudah
      </p>
    </div>
  </div>

  {{-- ━━ [BGR] EDITOR (brush) ━━ --}}
  <div id="viewEditor" class="bgr-view bgr-view-editor" hidden>

    <div class="bgr-editor-toolbar" id="editorToolbar">
      <div class="bgr-tb-group">
        <button class="bgr-tb-btn" id="btnEditorBack" type="button">
          <i class="fa-solid fa-arrow-left"></i>
          <span class="bgr-hide-sm">Kembali</span>
        </button>
      </div>

      <div class="bgr-tb-sep"></div>

      <div class="bgr-tb-group">
        <button class="bgr-tb-btn tool-btn active" data-tool="remove" id="btnRemoveArea" type="button">
          <i class="fa-solid fa-eraser"></i>
          <span>Hapus</span>
        </button>
        <button class="bgr-tb-btn tool-btn" data-tool="restore" id="btnRestoreArea" type="button">
          <i class="fa-solid fa-paintbrush"></i>
          <span>Pulihkan</span>
        </button>
      </div>

      <div class="bgr-tb-sep"></div>

      <div class="bgr-tb-group bgr-tb-brush">
        <i class="fa-solid fa-circle-dot bgr-tb-icon"></i>
        <input type="range" id="brushSizeSlider" min="3" max="150" value="30" class="bgr-brush-range">
        <span id="brushSizeVal" class="bgr-brush-val">30px</span>
      </div>

      <div class="bgr-tb-sep"></div>

      <div class="bgr-tb-group">
        <button class="bgr-tb-btn" id="btnUndo" type="button" disabled title="Undo (Ctrl+Z)">
          <i class="fa-solid fa-rotate-left"></i>
          <span class="bgr-hide-sm">Undo</span>
        </button>
        <button class="bgr-tb-btn" id="btnRedo" type="button" disabled title="Redo (Ctrl+Y)">
          <i class="fa-solid fa-rotate-right"></i>
          <span class="bgr-hide-sm">Redo</span>
        </button>
        <button class="bgr-tb-btn bgr-tb-btn-reset" id="btnEditReset" type="button">
          <i class="fa-solid fa-arrow-rotate-left"></i>
          <span class="bgr-hide-sm">Reset AI</span>
        </button>
      </div>

      <div class="bgr-tb-spacer"></div>

      <div class="bgr-tb-group">
        <button class="bgr-btn-dl-png sm" id="btnDownloadPNG" type="button">
          <i class="fa-solid fa-download"></i> PNG
        </button>
        <button class="bgr-btn-dl-jpg sm" id="btnDownloadJPG" type="button">
          <i class="fa-solid fa-download"></i> JPG
        </button>
      </div>
    </div>

    <div class="bgr-editor-panels">
      <div class="bgr-editor-panel">
        <div class="bgr-editor-panel-hdr">
          <span><i class="fa-solid fa-image"></i> Original</span>
        </div>
        <div class="bgr-editor-canvas-frame bgr-checker">
          <canvas id="origCanvas"></canvas>
        </div>
      </div>

      <div class="bgr-editor-panel">
        <div class="bgr-editor-panel-hdr">
          <span><i class="fa-solid fa-wand-magic-sparkles"></i> Hasil Edit</span>
          <span class="bgr-editor-hint">
            <i class="fa-solid fa-hand-pointer"></i> Klik &amp; paint untuk edit
          </span>
        </div>
        <div class="bgr-editor-canvas-frame bgr-checker">
          <div class="bgr-canvas-stack" id="canvasWrapper">
            <canvas id="displayCanvas"></canvas>
            <canvas id="overlayCanvas" class="bgr-overlay-canvas"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ━━ [BGR] MULTI (batch results) ━━ --}}
  <div id="viewMulti" class="bgr-view bgr-view-multi" hidden>
    <div class="bgr-multi-header">
      <p class="bgr-multi-info">
        <i class="fa-solid fa-circle-info"></i>
        Klik <strong>Edit</strong> pada foto untuk poles dengan brush editor
      </p>
      <div class="bgr-multi-actions">
        <button class="bgr-btn-ghost" id="btnClearAll" type="button">
          <i class="fa-solid fa-trash"></i> Hapus Semua
        </button>
        <button class="bgr-btn-ghost" id="btnAddMore" type="button">
          <i class="fa-solid fa-plus"></i> Tambah Foto
        </button>
        <button class="bgr-btn-dl-zip" id="btnDownloadZip" type="button">
          <i class="fa-solid fa-file-zipper"></i> Download ZIP
        </button>
        <button class="bgr-btn-dl-zip secondary" id="btnDownloadPdfAll" type="button">
          <i class="fa-solid fa-file-pdf"></i> Download PDF
        </button>
      </div>
    </div>
    <div class="bgr-multi-grid" id="multiGrid"></div>
  </div>

  {{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       ═══ PAS FOTO FLOW ═══
  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}

  {{-- ━━ [PF] STEP 1: UPLOAD ━━ --}}
  <div id="viewPfUpload" class="bgr-view bgr-view-pf-upload" hidden>
    <div class="bgr-pf-upload-wrap">

      <div class="bgr-section-header">
        <span class="bgr-step-pill">1</span>
        <div>
          <h2>Upload Foto</h2>
          <p>Upload foto wajah Anda — gunakan foto dengan pencahayaan baik dan latar belakang kontras.</p>
        </div>
      </div>

      <div class="bgr-dropzone pf" id="pfDropzone">
        <input type="file" id="pfFileInput" accept="image/jpeg,image/jpg,image/png,image/webp" hidden>
        <div class="bgr-dz-content">
          <div class="bgr-dz-icon-wrap blue">
            <i class="fa-solid fa-id-card"></i>
          </div>
          <h3>Drag &amp; drop foto di sini</h3>
          <p>atau</p>
          <button class="bgr-btn-browse secondary" id="pfBtnBrowse" type="button">
            <i class="fa-solid fa-folder-open"></i> Pilih Foto
          </button>
          <p class="bgr-dz-formats">JPG · PNG · WEBP &nbsp;·&nbsp; Maks. 20 MB · Satu foto saja</p>
        </div>
      </div>

      <div class="bgr-pf-tips">
        <div class="bgr-tip-item"><i class="fa-solid fa-sun"></i><span>Pencahayaan merata &amp; terang</span></div>
        <div class="bgr-tip-item"><i class="fa-solid fa-user"></i><span>Wajah menghadap kamera</span></div>
        <div class="bgr-tip-item"><i class="fa-solid fa-image"></i><span>Latar belakang kontras</span></div>
        <div class="bgr-tip-item"><i class="fa-solid fa-expand"></i><span>Resolusi minimal 500×500px</span></div>
      </div>
    </div>
  </div>

  {{-- ━━ [PF] STEP 2: CROP & CONFIGURE ━━ --}}
  <div id="viewPfCrop" class="bgr-view bgr-view-pf-crop" hidden>
    <div class="bgr-pf-crop-layout">

      <div class="bgr-pf-crop-left">
        <div class="bgr-section-title">
          <span class="bgr-step-pill">2</span>
          Sesuaikan Posisi Foto
          <span class="bgr-badge-sm">Preview langsung</span>
        </div>
        <p class="bgr-crop-guide">Atur posisi dan ukuran crop agar wajah berada di tengah dan proporsional sesuai standar pas foto.</p>
        <div class="bgr-crop-container bgr-checker">
          <img id="pfCropImg" alt="Foto untuk di-crop" style="max-width:100%;display:block;">
        </div>
        <button class="bgr-btn-back-sm" id="pfBackToUpload" type="button">
          <i class="fa-solid fa-arrow-left"></i> Ganti Foto
        </button>
      </div>

      <div class="bgr-pf-crop-right">

        <div class="bgr-pf-preview-card">
          <div class="bgr-pf-preview-label">
            <i class="fa-solid fa-eye"></i> Live Preview
          </div>
          <div class="bgr-pf-preview-frame bgr-checker" id="pfPreviewFrame">
            <canvas id="pfLivePreview" width="120" height="160"></canvas>
          </div>
          <p class="bgr-pf-preview-hint">Background akan diganti AI setelah proses</p>
        </div>

        <div class="bgr-pf-config-section">
          <label class="bgr-opt-label"><i class="fa-solid fa-ruler"></i> Ukuran Pas Foto</label>
          <div class="bgr-pf-size-grid" id="pfSizePicker">
            <button class="bgr-pf-size-btn" data-size="2x3" type="button">
              <span class="bgr-size-label">2×3</span>
              <span class="bgr-size-sub">KTP, SIM, Paspor</span>
            </button>
            <button class="bgr-pf-size-btn active" data-size="3x4" type="button">
              <span class="bgr-size-label">3×4</span>
              <span class="bgr-size-sub">CPNS, Ijazah</span>
            </button>
            <button class="bgr-pf-size-btn" data-size="4x6" type="button">
              <span class="bgr-size-label">4×6</span>
              <span class="bgr-size-sub">Lamaran Kerja</span>
            </button>
          </div>
        </div>

        <div class="bgr-pf-config-section">
          <label class="bgr-opt-label"><i class="fa-solid fa-palette"></i> Warna Latar Belakang</label>
          <div class="bgr-pf-bg-grid" id="pfBgPicker">
            <button class="bgr-pf-bg-btn active" data-bg="merah" type="button">
              <span class="bgr-bg-swatch" style="background:#cc0000;"></span><span>Merah</span>
            </button>
            <button class="bgr-pf-bg-btn" data-bg="biru" type="button">
              <span class="bgr-bg-swatch" style="background:#0047ab;"></span><span>Biru</span>
            </button>
            <button class="bgr-pf-bg-btn" data-bg="hijau" type="button">
              <span class="bgr-bg-swatch" style="background:#006400;"></span><span>Hijau</span>
            </button>
            <button class="bgr-pf-bg-btn" data-bg="putih" type="button">
              <span class="bgr-bg-swatch" style="background:#ffffff;border:1px solid #ddd;"></span><span>Putih</span>
            </button>
          </div>
        </div>

        <div class="bgr-pf-config-section">
          <label class="bgr-opt-label"><i class="fa-solid fa-sliders"></i> Kualitas AI</label>
          <div class="bgr-quality-btns" id="pfQualityBtns">
            <button class="bgr-q-btn active" data-q="medium" type="button">
              <div class="bgr-q-icon"><i class="fa-solid fa-bolt"></i></div>
              <div class="bgr-q-text"><strong>Standar</strong><small>~10 detik</small></div>
            </button>
            <button class="bgr-q-btn" data-q="portrait" type="button">
              <div class="bgr-q-icon"><i class="fa-solid fa-gem"></i></div>
              <div class="bgr-q-text"><strong>Ultra HD</strong><small>~20 detik</small></div>
            </button>
          </div>
        </div>

        <button class="bgr-btn-process" id="pfBtnProcess" type="button">
          <i class="fa-solid fa-wand-magic-sparkles"></i>
          <span>Buat Pas Foto Sekarang</span>
          <i class="fa-solid fa-arrow-right"></i>
        </button>
      </div>
    </div>
  </div>

  {{-- ━━ [PF] STEP 3: PROCESSING ━━ --}}
  <div id="viewPfProcessing" class="bgr-view bgr-view-processing" hidden>
    <div class="bgr-proc-card pf">
      <div class="bgr-proc-thumb-wrap">
        <img id="pfProcessingThumb" src="" alt="" class="bgr-proc-thumb">
        <div class="bgr-proc-spinner blue"></div>
      </div>
      <div class="bgr-proc-info">
        <h3 class="bgr-proc-title">Membuat Pas Foto…</h3>
        <p class="bgr-proc-label" id="pfProgressLabel">Mempersiapkan…</p>
        <div class="bgr-proc-bar-wrap">
          <div class="bgr-proc-bar">
            <div class="bgr-proc-fill blue" id="pfProgressFill" style="width:0%"></div>
          </div>
          <span class="bgr-proc-pct" id="pfProgressPct">0%</span>
        </div>
        <div class="bgr-proc-steps-list">
          <div class="bgr-proc-step-item" id="pfProcStep1">
            <div class="bgr-proc-step-dot"></div><span>Memotong foto ke ukuran yang dipilih</span>
          </div>
          <div class="bgr-proc-step-item" id="pfProcStep2">
            <div class="bgr-proc-step-dot"></div><span>AI BiRefNet menghapus background</span>
          </div>
          <div class="bgr-proc-step-item" id="pfProcStep3">
            <div class="bgr-proc-step-dot"></div><span>Menerapkan warna latar belakang</span>
          </div>
          <div class="bgr-proc-step-item" id="pfProcStep4">
            <div class="bgr-proc-step-dot"></div><span>Optimasi kualitas akhir</span>
          </div>
        </div>
        <p class="bgr-proc-security">
          <i class="fa-solid fa-shield-halved"></i>
          AI BiRefNet hapus background presisi hingga detail rambut — file tidak disimpan di server
        </p>
      </div>
    </div>
  </div>

  {{-- ━━ [PF] STEP 4: RESULT ━━ --}}
  <div id="viewPfResult" class="bgr-view bgr-view-pf-result" hidden>
    <div class="bgr-pf-result-layout">

      <div class="bgr-pf-result-left">
        <div class="bgr-pf-result-preview bgr-checker" id="pfResultPreviewWrap">
          <img id="pfResultImg" alt="Hasil Pas Foto" class="bgr-pf-result-img">
        </div>
        <div class="bgr-pf-result-chips">
          <span class="bgr-chip green" id="pfChipSize">3×4 cm</span>
          <span class="bgr-chip" id="pfChipBytes">—</span>
          <span class="bgr-chip blue" id="pfChipBg">Merah</span>
        </div>
        <div class="bgr-pf-result-bg-section">
          <label class="bgr-opt-label" style="font-size:10px;">
            <i class="fa-solid fa-palette"></i> Ganti Background
          </label>
          <div class="bgr-pf-bg-grid sm" id="pfResultBgPicker">
            <button class="bgr-pf-bg-btn active" data-bg="merah" type="button">
              <span class="bgr-bg-swatch sm" style="background:#cc0000;"></span><span>Merah</span>
            </button>
            <button class="bgr-pf-bg-btn" data-bg="biru" type="button">
              <span class="bgr-bg-swatch sm" style="background:#0047ab;"></span><span>Biru</span>
            </button>
            <button class="bgr-pf-bg-btn" data-bg="hijau" type="button">
              <span class="bgr-bg-swatch sm" style="background:#006400;"></span><span>Hijau</span>
            </button>
            <button class="bgr-pf-bg-btn" data-bg="putih" type="button">
              <span class="bgr-bg-swatch sm" style="background:#fff;border:1px solid #aaa;"></span><span>Putih</span>
            </button>
          </div>
        </div>
      </div>

      <div class="bgr-pf-result-right">
        <div class="bgr-pf-result-title">
          <div class="bgr-pf-success-badge">
            <i class="fa-solid fa-circle-check"></i>
            Pas Foto Berhasil Dibuat!
          </div>
          <p>Pilih format dan jumlah foto untuk download.</p>
        </div>

        <div class="bgr-dl-section">
          <div class="bgr-dl-section-label"><i class="fa-solid fa-image"></i> Download sebagai Gambar</div>
          <div class="bgr-dl-img-btns">
            <button class="bgr-btn-dl-img" id="pfDlJpg" type="button">
              <i class="fa-solid fa-download"></i>
              <div><strong>JPG</strong><span>Kualitas tinggi</span></div>
            </button>
            <button class="bgr-btn-dl-img" id="pfDlPng" type="button">
              <i class="fa-solid fa-download"></i>
              <div><strong>PNG</strong><span>Lossless</span></div>
            </button>
          </div>
        </div>

        <div class="bgr-dl-section">
          <div class="bgr-dl-section-label"><i class="fa-solid fa-file-pdf"></i> Download sebagai PDF A4 Siap Cetak</div>
          <p class="bgr-dl-pdf-hint">Pilih jumlah foto dalam satu lembar A4</p>
          <div class="bgr-pdf-count-grid" id="pfPdfCountGrid">{{-- Filled by JS --}}</div>
          <button class="bgr-btn-dl-pdf" id="pfDlPdf" type="button">
            <i class="fa-solid fa-file-pdf"></i>
            <span id="pfDlPdfLabel">Download PDF (<span id="pfPdfCountLabel">4</span> foto)</span>
          </button>
        </div>

        <div class="bgr-pf-result-footer">
          <button class="bgr-btn-ghost" id="pfBackToCrop" type="button">
            <i class="fa-solid fa-crop-simple"></i> Edit Crop
          </button>
          <button class="bgr-btn-ghost" id="pfNewPhoto" type="button">
            <i class="fa-solid fa-plus"></i> Foto Baru
          </button>
        </div>
      </div>
    </div>
  </div>

</div>{{-- /#bgrRoot --}}

{{-- ━━ TOAST ━━ --}}
<div id="bgrToast" class="bgr-toast" role="alert">
  <div class="bgr-toast-icon" id="bgrToastIcon">
    <i class="fa-solid fa-check" id="bgrToastIco"></i>
  </div>
  <span id="bgrToastMsg">Berhasil!</span>
</div>

{{-- ═══ SLOT 4: NATIVE BANNER ═══ --}}
<div class="ads-slot-native no-print">@include('components.ads.banner-content')</div>

</div>{{-- /.tlb-body --}}
</div>{{-- /.tlb-page --}}

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
@vite(['resources/js/bgremover/index.js'])
@endpush