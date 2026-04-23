@extends('layouts.app')

@section('og_image', 'imageconverter')
@section('title', 'Resize Kompres & Konversi Gambar Gratis — JPG PNG WebP | MediaTools')
@section('meta_description', 'Resize, kompres, dan konversi gambar JPG PNG WebP langsung di browser. Tanpa upload ke server, privasi 100% terjaga. Gratis unlimited.')
@section('meta_keywords', 'resize gambar online, konversi gambar, image converter gratis, kompres foto online, ubah format gambar, jpg to png, png to jpg, jpg to webp, image resize online, compress gambar gratis, webp to jpg, png to webp, image compressor, ubah ukuran gambar, konversi foto online')
@include('seo.imageconverter')

@section('content')
<link rel="stylesheet" href="{{ asset('css/tools-base.css') }}">
<script>document.body.classList.add('tlb-active');</script>

<div class="tlb-page tlb-blue" id="tlbPage_imageconverter">

{{-- ════ TLB HEADER ════ --}}
<div class="tlb-header">
    <div class="tlb-header-inner">
        <div>
            <nav aria-label="Breadcrumb" class="flex justify-left mb-5">
                  <ol class="flex items-center gap-2 text-xs" style="color:var(--text-3)">
                      <li><a href="{{ url('/') }}" style="color:var(--text-3);text-decoration:none;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text-3)'">Home</a></li>
                      <li style="margin:0 4px;font-size:9px;">›</li>
                      <li style="color:var(--accent);font-weight:600;">Image Converter</li>
                  </ol>
              </nav>
            <div class="tlb-header-badges">
                <span class="tlb-hbadge"><i class="fa-solid fa-expand-arrows-alt"></i> Resize</span>
                <span class="tlb-hbadge"><i class="fa-solid fa-compress-arrows-alt"></i> Kompres</span>
                <span class="tlb-hbadge"><i class="fa-solid fa-arrows-rotate"></i> Konversi Format</span>
                <span class="tlb-hbadge"><i class="fa-solid fa-lock"></i> Zero Server</span>
            </div>
            <h1 class="tlb-header-title">Image <span>Converter.</span></h1>
            <p class="tlb-header-sub">Resize, kompres & konversi gambar JPG · PNG · WebP langsung di browser. Zero server, privasi 100%.</p>
        </div>
    </div>
</div>
<div class="tlb-header-curve"></div>

<div class="tlb-body">
{{-- ═══ ADS SLOT ═══ --}}
<div class="ads-slot-header no-print" style="margin-bottom:20px;">@include('components.ads.banner-header')</div>

<link rel="stylesheet" href="{{ asset('css/imageconverter.css') }}">
<link rel="stylesheet" href="{{ asset('css/ads.css') }}">

<div class="ic-page">
  {{-- ═══ SLOT 1: HEADER BANNER 728×90 ═══ --}}

  <div class="max-w-7xl mx-auto px-4 sm:px-6">

    {{-- HEADER --}}

    {{-- OPERATION TABS --}}
    <div class="ic-ops-row">
      <button class="ic-op-btn active" data-op="convert">
        <i class="fa-solid fa-repeat"></i>
        <span class="ic-op-name">Convert</span>
        <span class="ic-op-hint">Ubah format</span>
      </button>
      <button class="ic-op-btn" data-op="compress">
        <i class="fa-solid fa-compress-arrows-alt"></i>
        <span class="ic-op-name">Compress</span>
        <span class="ic-op-hint">Perkecil ukuran</span>
      </button>
      <button class="ic-op-btn" data-op="resize">
        <i class="fa-solid fa-crop-simple"></i>
        <span class="ic-op-name">Resize</span>
        <span class="ic-op-hint">Ubah dimensi</span>
      </button>
    </div>

    {{-- MAIN GRID --}}
    <div class="ic-main-grid">

      {{-- LEFT: CONFIG --}}
      <div class="ic-panel-left">
        <div class="ic-glass-card">
          <div class="ic-card-glow"></div>
          <div class="ic-section-tag">
            <span class="ic-section-dot"></span> Konfigurasi
          </div>

          {{-- UPLOAD ZONE --}}
          <div class="ic-step" id="step-upload">
            <label class="ic-label">01 — Upload Gambar</label>
            <div class="ic-drop-zone" id="drop-zone">
              <input type="file" id="img-input" accept="image/jpeg,image/png,image/webp,image/gif,image/bmp" multiple class="ic-file-input">
              <div class="ic-drop-inner">
                <div class="ic-drop-icon"><i class="fa-solid fa-images"></i></div>
                <p class="ic-drop-title">Drop gambar di sini</p>
                <p class="ic-drop-hint">atau klik untuk browse · JPG, PNG, WebP, GIF, BMP · max 10 file</p>
              </div>
            </div>
            <div id="file-list" class="ic-file-list"></div>
          </div>

          {{-- CONVERT OPTIONS --}}
          <div class="ic-step" id="panel-convert">
            <label class="ic-label">02 — Format Tujuan</label>
            <div class="ic-format-grid">
              <button class="ic-fmt-btn active" data-fmt="image/jpeg">
                <span class="ic-fmt-ext">JPG</span>
                <span class="ic-fmt-desc">Foto & web</span>
              </button>
              <button class="ic-fmt-btn" data-fmt="image/png">
                <span class="ic-fmt-ext">PNG</span>
                <span class="ic-fmt-desc">Transparan</span>
              </button>
              <button class="ic-fmt-btn" data-fmt="image/webp">
                <span class="ic-fmt-ext">WebP</span>
                <span class="ic-fmt-desc">Web modern</span>
              </button>
            </div>
            <div class="ic-quality-wrap">
              <div class="ic-quality-header">
                <label class="ic-label" style="margin:0">Kualitas Output</label>
                <span class="ic-quality-val" id="quality-val">85%</span>
              </div>
              <input type="range" id="quality-slider" class="ic-slider" min="10" max="100" value="85" step="5">
              <div class="ic-quality-hints">
                <span>Kecil</span><span>Seimbang</span><span>Terbaik</span>
              </div>
            </div>
          </div>

          {{-- COMPRESS OPTIONS --}}
          <div class="ic-step ic-hidden" id="panel-compress">
            <label class="ic-label">02 — Target Ukuran</label>
            <div class="ic-target-grid">
              <button class="ic-target-btn active" data-target="80">
                <span class="ic-target-pct">80%</span>
                <span class="ic-target-desc">Ringan</span>
              </button>
              <button class="ic-target-btn" data-target="60">
                <span class="ic-target-pct">60%</span>
                <span class="ic-target-desc">Sedang</span>
              </button>
              <button class="ic-target-btn" data-target="40">
                <span class="ic-target-pct">40%</span>
                <span class="ic-target-desc">Agresif</span>
              </button>
              <button class="ic-target-btn" data-target="20">
                <span class="ic-target-pct">20%</span>
                <span class="ic-target-desc">Minimum</span>
              </button>
            </div>
            <div class="ic-quality-wrap" style="margin-top:16px">
              <div class="ic-quality-header">
                <label class="ic-label" style="margin:0">Kualitas Manual</label>
                <span class="ic-quality-val" id="compress-quality-val">80%</span>
              </div>
              <input type="range" id="compress-quality-slider" class="ic-slider" min="10" max="100" value="80" step="5">
              
            </div>
            <div class="ic-quality-wrap" style="margin-top:16px">
                <div class="ic-quality-header">
                    <label class="ic-label" style="margin:0">Kecilkan kualitas gambar untuk mendapatkan hasil compress yang maksimal.</label>
                </div>
            </div>
          </div>

          {{-- RESIZE OPTIONS --}}
          <div class="ic-step ic-hidden" id="panel-resize">
            <label class="ic-label">02 — Dimensi Baru</label>
            <div class="ic-resize-row">
              <div class="ic-resize-field">
                <span class="ic-resize-prefix">W</span>
                <input type="number" id="resize-w" class="ic-resize-input" placeholder="800" min="1">
                <span class="ic-resize-suffix">px</span>
              </div>
              <button class="ic-lock-btn active" id="lock-ratio" title="Kunci rasio aspek">
                <i class="fa-solid fa-link" id="lock-icon"></i>
              </button>
              <div class="ic-resize-field">
                <span class="ic-resize-prefix">H</span>
                <input type="number" id="resize-h" class="ic-resize-input" placeholder="600" min="1">
                <span class="ic-resize-suffix">px</span>
              </div>
            </div>
            <div class="ic-preset-row">
              <span class="ic-label" style="font-size:10px;margin-bottom:8px;display:block">Preset Cepat</span>
              <div class="ic-presets">
                <button class="ic-preset" data-w="1920" data-h="1080">FHD</button>
                <button class="ic-preset" data-w="1280" data-h="720">HD</button>
                <button class="ic-preset" data-w="1080" data-h="1080">IG Square</button>
                <button class="ic-preset" data-w="1080" data-h="1920">IG Story</button>
                <button class="ic-preset" data-w="800" data-h="600">Web</button>
                <button class="ic-preset" data-w="400" data-h="400">Thumb</button>
              </div>
            </div>
            <div class="ic-quality-wrap">
              <div class="ic-quality-header">
                <label class="ic-label" style="margin:0">Kualitas Output</label>
                <span class="ic-quality-val" id="resize-quality-val">90%</span>
              </div>
              <input type="range" id="resize-quality-slider" class="ic-slider" min="10" max="100" value="90" step="5">
            </div>
          </div>

          {{-- PROCESS BTN --}}
          <div class="ic-step ic-hidden" id="step-process">
            <button type="button" id="btn-process" class="ic-btn-primary">
              <i class="fa-solid fa-bolt"></i>
              <span id="btn-process-label">Proses Sekarang</span>
            </button>
          </div>

        </div>
      </div>

      {{-- RIGHT: PREVIEW --}}
      <div class="ic-panel-right">
        <div class="ic-preview-wrap" id="preview-wrap">

          {{-- Empty --}}
          <div id="state-empty" class="ic-state-empty">
            <div class="ic-empty-icon"><i class="fa-regular fa-image"></i></div>
            <p class="ic-empty-title">Belum ada gambar</p>
            <p class="ic-empty-sub">Upload gambar di kiri untuk memulai. Semua proses terjadi di browser Anda.</p>
            <div class="ic-badge-row">
              <span class="ic-badge"><i class="fa-solid fa-shield-halved"></i> Privasi 100%</span>
              <span class="ic-badge"><i class="fa-solid fa-bolt"></i> Instan</span>
              <span class="ic-badge"><i class="fa-solid fa-infinity"></i> Gratis</span>
            </div>
          </div>

          {{-- Preview grid --}}
          <div id="state-preview" class="ic-state-preview ic-hidden">
            <div class="ic-preview-header">
              <p class="ic-preview-title" id="preview-title">Preview</p>
              <span class="ic-preview-count" id="preview-count">0 file</span>
            </div>
            <div class="ic-preview-grid" id="preview-grid"></div>
          </div>

          {{-- Processing --}}
          <div id="state-processing" class="ic-state-processing ic-hidden">
            <div class="ic-spinner-ring"><div class="ic-spinner-inner"></div></div>
            <p class="ic-proc-title">Memproses gambar…</p>
            <div class="ic-progress-bar-wrap">
              <div class="ic-progress-bar" id="progress-bar" style="width:0%"></div>
            </div>
            <p class="ic-proc-sub" id="proc-sub">0 / 0 file</p>
          </div>

          {{-- Result --}}
          <div id="state-result" class="ic-state-result ic-hidden">
            <div class="ic-result-checkmark"><i class="fa-solid fa-check"></i></div>
            <p class="ic-result-title">Selesai!</p>
            <p class="ic-result-sub" id="result-sub">File siap diunduh.</p>
            <div class="ic-result-stats" id="result-stats"></div>
            <div class="ic-result-actions">
              <button type="button" id="btn-download-all" class="ic-btn-download">
                <i class="fa-solid fa-download"></i>
                <span>Download Semua (ZIP)</span>
              </button>
              <button type="button" id="btn-download-single" class="ic-btn-download-outline ic-hidden">
                <i class="fa-solid fa-file-arrow-down"></i>
                <span>Download File</span>
              </button>
              <button type="button" id="btn-reset" class="ic-btn-reset">
                <i class="fa-solid fa-rotate-left"></i>
                <span>Proses Lagi</span>
              </button>
            </div>
            <p class="ic-result-note"><i class="fa-solid fa-lock text-[9px]"></i> Gambar tidak dikirim ke server manapun</p>
          </div>

        </div>
      </div>

    </div>
  </div>

  {{-- TOAST --}}
  <div id="ic-toast" class="ic-toast" role="alert">
    <div class="ic-toast-icon"><i id="toast-ico" class="fa-solid fa-check"></i></div>
    <div class="ic-toast-body">
      <span id="toast-type" class="ic-toast-type">Sukses</span>
      <span id="toast-msg" class="ic-toast-msg">Operasi berhasil.</span>
    </div>
  </div>

  {{-- ═══ ADS SLOT: HEADER ═══ --}}
  <div class="ads-slot-header no-print" style="margin-bottom:5px;">@include('components.ads.banner-header')</div>
  <div class="ads-slot-header no-print" style="margin-bottom:5px;">@include('components.ads.banner-header')</div>
  {{-- ═══ ADS SLOT: NATIVE BANNER ═══ --}}
  <div class="ads-slot-native no-print">@include('components.ads.banner-content')</div>
  
</div>

@push('scripts')
{{-- JSZip untuk bundle download --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="{{ asset('js/imageconverter.js') }}"></script>
@endpush
@endsection

</div>{{-- /.tlb-body --}}
</div>{{-- /.tlb-page --}}
