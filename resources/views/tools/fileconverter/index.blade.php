@extends('layouts.app')

@section('og_image', 'fileconverter')
@section('title', 'File Converter Online Gratis — PDF Word Excel JPG PowerPoint | MediaTools')
@section('meta_description', 'Konversi PDF ke Word, Word ke PDF, Excel ke PDF, JPG ke PDF dan sebaliknya secara gratis. Upload 5 file sekaligus, hasil instan, privasi terjaga.')
@section('meta_keywords', 'convert pdf to word','convert word to pdf','pdf to word, word to pdf, konversi pdf, compress pdf, excel to pdf, jpg to pdf, pdf converter gratis, convert pdf online, ilovepdf alternative, konversi file online, pdf ke word gratis, word ke pdf, powerpoint to pdf, pdf to jpg, merge pdf')

@include('seo.fileconverter')

@section('content')
{{-- Pass routes to JS without inline JS --}}
<meta name="fc-process-url"  content="{{ route('tools.fileconverter.process') }}">
<meta name="fc-download-url" content="{{ url('file-converter/download') }}">

<link rel="stylesheet" href="{{ asset('css/tools-base.css') }}">
<link rel="stylesheet" href="{{ asset('css/fileconverter.css') }}">
<link rel="stylesheet" href="{{ asset('css/ads.css') }}">

<script>document.body.classList.add('tlb-active');</script>

<div class="tlb-page tlb-blue" id="tlbPage_fileconverter">

  {{-- ════ TLB HEADER ════ --}}
  <div class="tlb-header">
      <div class="tlb-header-inner">
          <div>
              <nav aria-label="Breadcrumb" class="flex justify-left mb-5">
                  <ol class="flex items-center gap-2 text-xs" style="color:var(--text-3)">
                      <li><a href="{{ url('/') }}" style="color:var(--text-3);text-decoration:none;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text-3)'">Home</a></li>
                      <li style="margin:0 4px;font-size:9px;">›</li>
                      <li style="color:var(--accent);font-weight:600;">File Converter</li>
                  </ol>
              </nav>
              <div class="tlb-header-badges">
                  <span class="tlb-hbadge"><i class="fa-solid fa-file-word"></i> PDF ↔ Word</span>
                  <span class="tlb-hbadge"><i class="fa-solid fa-file-excel"></i> Excel → PDF</span>
                  <span class="tlb-hbadge"><i class="fa-solid fa-file-image"></i> JPG → PDF</span>
                  <span class="tlb-hbadge"><i class="fa-solid fa-layer-group"></i> Multi-File</span>
              </div>
              <h1 class="tlb-header-title">File <span>Converter.</span></h1>
              <p class="tlb-header-sub">Konversi PDF, Word, Excel, PowerPoint & gambar dua arah. Upload 5 file sekaligus — hasil instan.</p>
          </div>
      </div>
  </div>
  <div class="tlb-header-curve"></div>

  <div class="tlb-body">
    <div class="fc-page">
      {{-- ═══ SLOT 1: HEADER BANNER 728×90 ═══ --}}
      <div class="ads-slot-header no-print">
          @include('components.ads.banner-header')
      </div>
      <div class="fc-container">

        {{-- ── HEADER ── --}}
        <header class="fc-header">
          <div class="fc-badge-row">
            <span class="fc-badge-free">100% Gratis</span>
            <span class="fc-badge-info"><i class="fa-solid fa-shield-halved"></i> Privasi Aman</span>
            <span class="fc-badge-info"><i class="fa-solid fa-layer-group"></i> Multi-File</span>
            <span class="fc-badge-info"><i class="fa-solid fa-bolt"></i> Instan</span>
          </div>
          <h1 class="fc-title">File <span class="fc-title-accent">Converter.</span></h1>
          <p class="fc-subtitle">
            Konversi dokumen dua arah — PDF, Word, Excel, PowerPoint &amp; gambar.
            Upload hingga 5 file sekaligus, hasil langsung bisa didownload.
          </p>
        </header>

        {{-- ── CATEGORY TABS ── --}}
        <div class="fc-cat-tabs" role="tablist" aria-label="Pilih kategori konversi">
          <button class="fc-cat-btn active" data-cat="to-pdf" role="tab" aria-selected="true">
            <i class="fa-solid fa-file-pdf"></i>
            <span>→ PDF</span>
          </button>
          <button class="fc-cat-btn" data-cat="from-pdf" role="tab" aria-selected="false">
            <i class="fa-solid fa-file-pdf"></i>
            <span>PDF →</span>
          </button>
          <button class="fc-cat-btn" data-cat="image" role="tab" aria-selected="false">
            <i class="fa-solid fa-images"></i>
            <span>Gambar</span>
          </button>
        </div>

        {{-- ── CONVERSION TYPE GRID ── --}}
        <div class="fc-type-section">
          <label class="fc-section-label">Pilih Jenis Konversi</label>

          {{-- → PDF --}}
          <div class="fc-type-group" data-cat="to-pdf">
            @php $toPdf = [
              ['jpg_to_pdf',  'fa-image',          'JPG → PDF',   'Gabung gambar jadi PDF',      'blue',   'JPG, JPEG'],
              ['png_to_pdf',  'fa-image',          'PNG → PDF',   'Gambar PNG ke PDF',            'blue',   'PNG'],
              ['word_to_pdf', 'fa-file-word',      'Word → PDF',  'DOC/DOCX ke PDF',              'blue',   'DOC, DOCX'],
              ['excel_to_pdf','fa-file-excel',     'Excel → PDF', 'XLS/XLSX ke PDF',              'green',  'XLS, XLSX'],
              ['ppt_to_pdf',  'fa-file-powerpoint','PPT → PDF',   'PPT/PPTX ke PDF',              'orange', 'PPT, PPTX'],
            ]; @endphp
            <div class="fc-type-grid">
              @foreach($toPdf as [$val,$icon,$lbl,$desc,$color,$fmt])
              <button class="fc-type-btn" data-type="{{ $val }}" data-fmt="{{ $fmt }}"
                      aria-label="{{ $lbl }}: {{ $desc }}">
                <div class="fc-type-icon fc-type-icon--{{ $color }}">
                  <i class="fa-solid {{ $icon }}"></i>
                </div>
                <div class="fc-type-info">
                  <span class="fc-type-name">{{ $lbl }}</span>
                  <span class="fc-type-desc">{{ $desc }}</span>
                </div>
                <div class="fc-type-check"><i class="fa-solid fa-check"></i></div>
              </button>
              @endforeach
            </div>
          </div>

          {{-- PDF → --}}
          <div class="fc-type-group fc-hidden" data-cat="from-pdf">
            @php $fromPdf = [
              ['pdf_to_word',  'fa-file-word',      'PDF → Word',  'Ekstrak teks ke DOCX',        'blue',   'PDF'],
              ['pdf_to_excel', 'fa-file-excel',     'PDF → Excel', 'Tabel PDF ke XLSX',            'green',  'PDF'],
              ['pdf_to_ppt',   'fa-file-powerpoint','PDF → PPT',   'Slide PDF ke PPTX',            'orange', 'PDF'],
              ['pdf_to_jpg',   'fa-image',          'PDF → JPG',   'Tiap halaman jadi JPG',        'red',    'PDF'],
              ['pdf_to_png',   'fa-image',          'PDF → PNG',   'Tiap halaman jadi PNG',        'red',    'PDF'],
            ]; @endphp
            <div class="fc-type-grid">
              @foreach($fromPdf as [$val,$icon,$lbl,$desc,$color,$fmt])
              <button class="fc-type-btn" data-type="{{ $val }}" data-fmt="{{ $fmt }}"
                      aria-label="{{ $lbl }}: {{ $desc }}">
                <div class="fc-type-icon fc-type-icon--{{ $color }}">
                  <i class="fa-solid {{ $icon }}"></i>
                </div>
                <div class="fc-type-info">
                  <span class="fc-type-name">{{ $lbl }}</span>
                  <span class="fc-type-desc">{{ $desc }}</span>
                </div>
                <div class="fc-type-check"><i class="fa-solid fa-check"></i></div>
              </button>
              @endforeach
            </div>
            <div style="margin-top:10px;padding:10px 14px;background:rgba(251,191,36,0.06);border:1px solid rgba(251,191,36,0.18);border-radius:12px;font-size:11px;color:#6b7280;display:flex;gap:8px;align-items:flex-start;">
              <i class="fa-solid fa-circle-info" style="color:#fbbf24;margin-top:1px;flex-shrink:0;"></i>
              <span>
                <strong style="color:#9ca3af;">PDF → Word/Excel/PPT</strong>
                membutuhkan PDF berteks (bukan hasil scan). Untuk PDF scan gunakan
                <strong style="color:#9ca3af;">PDF → JPG</strong> lalu edit manual.
              </span>
            </div>
          </div>

          {{-- Gambar --}}
          <div class="fc-type-group fc-hidden" data-cat="image">
            @php $image = [
              ['jpg_to_png', 'fa-image', 'JPG → PNG', 'Konversi format gambar',    'blue',   'JPG, JPEG'],
              ['png_to_jpg', 'fa-image', 'PNG → JPG', 'Kompres ke JPEG',           'blue',   'PNG'],
              ['jpg_to_webp','fa-image', 'JPG → WebP','Format web modern',         'green',  'JPG, JPEG'],
              ['png_to_webp','fa-image', 'PNG → WebP','Format web modern',         'green',  'PNG'],
              ['webp_to_jpg','fa-image', 'WebP → JPG','WebP ke JPEG',              'orange', 'WEBP'],
              ['webp_to_png','fa-image', 'WebP → PNG','WebP ke PNG',               'orange', 'WEBP'],
            ]; @endphp
            <div class="fc-type-grid">
              @foreach($image as [$val,$icon,$lbl,$desc,$color,$fmt])
              <button class="fc-type-btn" data-type="{{ $val }}" data-fmt="{{ $fmt }}"
                      aria-label="{{ $lbl }}: {{ $desc }}">
                <div class="fc-type-icon fc-type-icon--{{ $color }}">
                  <i class="fa-solid {{ $icon }}"></i>
                </div>
                <div class="fc-type-info">
                  <span class="fc-type-name">{{ $lbl }}</span>
                  <span class="fc-type-desc">{{ $desc }}</span>
                </div>
                <div class="fc-type-check"><i class="fa-solid fa-check"></i></div>
              </button>
              @endforeach
            </div>
          </div>
        </div>{{-- /fc-type-section --}}

        {{-- ── MAIN CARD ── --}}
        <div class="fc-card fc-hidden" id="fc-main-card">
          <!-- <div class="fc-card-glow"></div> -->

          {{-- Upload Step --}}
          <div class="fc-step" id="step-upload">
            <div class="fc-step-label-row">
              <label class="fc-label">
                Upload File
                <span class="fc-multi-badge">maks. 5 file</span>
              </label>
              <span class="fc-accepted-hint" id="accepted-hint">Format: —</span>
            </div>

            <div class="fc-drop-zone" id="drop-zone" tabindex="0"
                aria-label="Klik atau drag file ke sini">
              <input type="file" id="file-input" class="fc-file-input" accept="" multiple
                    aria-label="Pilih file untuk dikonversi">
              <div class="fc-drop-inner" id="drop-placeholder">
                <div class="fc-drop-icon">
                  <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <p class="fc-drop-title">Drag &amp; drop file di sini</p>
                <p class="fc-drop-hint">atau klik untuk browse · maks. 5 file · 50 MB per file</p>
                <p class="fc-drop-tap-hint">
                  <i class="fa-solid fa-hand-pointer"></i> Ketuk untuk pilih file
                </p>
              </div>
            </div>

            {{-- File List --}}
            <div class="fc-file-list" id="file-list" role="list"></div>

            {{-- Add More Button --}}
            <button class="fc-add-more fc-hidden" id="btn-add-more" type="button"
                    aria-label="Tambah file lagi">
              <i class="fa-solid fa-plus"></i>
              <span>Tambah File Lagi</span>
              <span class="fc-add-count" id="add-count">0/5</span>
            </button>
          </div>

          {{-- Convert Button --}}
          <button type="button" id="btn-convert" class="fc-btn-convert" disabled
                  aria-label="Mulai konversi">
            <i class="fa-solid fa-rotate"></i>
            <span id="btn-convert-label">Pilih jenis konversi dahulu</span>
          </button>

          {{-- Processing State --}}
          <div class="fc-state fc-hidden" id="state-processing" role="status" aria-live="polite">
            <div class="fc-spinner-ring"></div>
            <p class="fc-state-title" id="proc-title">Mengkonversi file...</p>
            <div class="fc-progress-wrap">
              <div class="fc-progress-bar" id="progress-bar"></div>
            </div>
            <p class="fc-state-sub" id="proc-sub">Memproses file...</p>
            <p style="font-size:10.5px;color:#4b5563;margin-top:10px;">
              <i class="fa-solid fa-clock"></i>
              PDF → Word/Excel/PPT memerlukan 10–60 detik tergantung ukuran dan kompleksitas file
            </p>
          </div>

          {{-- Result State --}}
          <div class="fc-state fc-hidden" id="state-result" role="status" aria-live="polite">
            <div class="fc-result-icon"><i class="fa-solid fa-check"></i></div>
            <p class="fc-result-title" id="result-title">Konversi Selesai!</p>
            <p class="fc-result-sub" id="result-sub">File siap diunduh</p>

            <div class="fc-result-files" id="result-files" role="list"></div>

            <button type="button" id="btn-download-all" class="fc-btn-download fc-hidden">
              <i class="fa-solid fa-file-zipper"></i>
              <span>Download Semua (ZIP)</span>
            </button>

            <button type="button" id="btn-reset" class="fc-btn-reset">
              <i class="fa-solid fa-rotate-left"></i>
              <span>Konversi File Lain</span>
            </button>
          </div>

          {{-- Error State --}}
          <div class="fc-state fc-hidden" id="state-error" role="alert">
            <div class="fc-error-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <p class="fc-state-title">Konversi Gagal</p>
            <p class="fc-state-sub" id="error-msg">Terjadi kesalahan.</p>
            <button type="button" id="btn-retry" class="fc-btn-reset" style="margin-top:14px;">
              <i class="fa-solid fa-rotate-right"></i>
              <span>Coba Lagi</span>
            </button>
          </div>

        </div>{{-- /fc-card --}}

        {{-- ── INFO CARDS ── --}}
        <div class="fc-info-grid">
          <div class="fc-info-card">
            <div class="fc-info-icon"><i class="fa-solid fa-layer-group"></i></div>
            <h3>Multi-File Sekaligus</h3>
            <p>Upload hingga 5 file sekaligus. Setiap file bisa didownload individual atau semua sekaligus (ZIP).</p>
          </div>
          <div class="fc-info-card">
            <div class="fc-info-icon"><i class="fa-solid fa-lock"></i></div>
            <h3>Privasi Terjaga</h3>
            <p>File dihapus otomatis dari server setelah 1 jam. Tidak ada yang menyimpan atau mengakses dokumen Anda.</p>
          </div>
          <div class="fc-info-card">
            <div class="fc-info-icon"><i class="fa-solid fa-infinity"></i></div>
            <h3>Gratis Tanpa Batas</h3>
            <p>Tidak perlu daftar akun atau berlangganan. Gunakan sepuasnya secara gratis untuk semua jenis konversi.</p>
          </div>
        </div>

      </div>

      {{-- ═══ ADS SLOT: HEADER ═══ --}}
      <div class="ads-slot-header no-print" style="margin-bottom:20px;">@include('components.ads.banner-header')</div>
      {{-- ═══ ADS SLOT: NATIVE BANNER ═══ --}}
      <div class="ads-slot-native no-print">@include('components.ads.banner-content')</div>

    </div>
    {{-- Toast Notification --}}
    <div id="fc-toast" class="fc-toast" role="alert" aria-live="assertive">
      <i class="fa-solid fa-check fc-toast-ico" style="color:#a3e635;"></i>
      <span id="fc-toast-msg">Berhasil!</span>
    </div>
  </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="{{ asset('js/fileconverter.js') }}"></script>
@endpush

@include('components.tools.seo-section', ['tool' => 'fileconverter'])

@endsection