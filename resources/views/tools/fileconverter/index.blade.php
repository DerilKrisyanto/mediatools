@extends('layouts.app')

@section('title', 'File Converter - Konversi PDF, Word, Excel, JPG Online Gratis | MediaTools')
@section('meta_description', 'Konversi dokumen dua arah: PDF↔Word, PDF↔Excel, PDF↔JPG, Word↔PDF, Excel↔PDF. Multi-file hingga 5 file sekaligus. Gratis, privasi terjaga.')

@section('content')
<link rel="stylesheet" href="{{ asset('css/fileconverter.css') }}">

<div class="fc-page">
  <div class="fc-container">

    {{-- HEADER --}}
    <header class="fc-header">
      <div class="fc-badge-row">
        <span class="fc-badge-free">100% Gratis</span>
        <span class="fc-badge-info"><i class="fa-solid fa-shield-halved"></i> Privasi Aman</span>
        <span class="fc-badge-info"><i class="fa-solid fa-layer-group"></i> Multi-File</span>
      </div>
      <h1 class="fc-title">File <span class="fc-title-accent">Converter.</span></h1>
      <p class="fc-subtitle">Konversi dokumen dua arah — PDF, Word, Excel, PowerPoint & gambar. Upload hingga 5 file sekaligus.</p>
    </header>

    {{-- CATEGORY TABS --}}
    <div class="fc-cat-tabs">
      <button class="fc-cat-btn active" data-cat="to-pdf">
        <span>ALL TYPES → PDF</span>
        <i class="fa-solid fa-file-pdf"></i>
      </button>
      <button class="fc-cat-btn" data-cat="from-pdf">
        <i class="fa-solid fa-file-pdf"></i>
        <span>PDF → ALL TYPES</span>
      </button>
      <button class="fc-cat-btn" data-cat="image">
        <i class="fa-solid fa-images"></i>
        <span>Gambar</span>
      </button>
    </div>

    {{-- CONVERSION TYPE GRID --}}
    <div class="fc-type-section">
      <label class="fc-section-label" id="type-section-label">Pilih Jenis Konversi</label>

      {{-- TO PDF --}}
      <div class="fc-type-group" data-cat="to-pdf">
        @php $toPdf = [
          ['jpg_to_pdf',  'fa-image',          'JPG → PDF',   'Gabung gambar jadi PDF',  'blue',   'JPG, JPEG'],
          ['png_to_pdf',  'fa-image',          'PNG → PDF',   'Gambar PNG ke PDF',        'blue',   'PNG'],
          ['word_to_pdf', 'fa-file-word',      'Word → PDF',  'DOCX/DOC ke PDF',          'blue',   'DOC, DOCX'],
          ['excel_to_pdf','fa-file-excel',     'Excel → PDF', 'XLSX/XLS ke PDF',          'green',  'XLS, XLSX'],
          ['ppt_to_pdf',  'fa-file-powerpoint','PPT → PDF',   'PPTX/PPT ke PDF',          'orange', 'PPT, PPTX'],
        ]; @endphp
        <div class="fc-type-grid">
          @foreach($toPdf as [$val,$icon,$lbl,$desc,$color,$fmt])
          <button class="fc-type-btn" data-type="{{ $val }}" data-fmt="{{ $fmt }}">
            <div class="fc-type-icon fc-type-icon--{{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
            <div class="fc-type-info">
              <span class="fc-type-name">{{ $lbl }}</span>
              <span class="fc-type-desc">{{ $desc }}</span>
            </div>
            <div class="fc-type-check"><i class="fa-solid fa-check"></i></div>
          </button>
          @endforeach
        </div>
      </div>

      {{-- FROM PDF --}}
      <div class="fc-type-group fc-hidden" data-cat="from-pdf">
        @php $fromPdf = [
          ['pdf_to_word',  'fa-file-word',      'PDF → Word',  'Ekstrak teks ke DOCX',    'blue',   'PDF'],
          ['pdf_to_excel', 'fa-file-excel',     'PDF → Excel', 'Tabel PDF ke XLSX',        'green',  'PDF'],
          ['pdf_to_ppt',   'fa-file-powerpoint','PDF → PPT',   'Slide PDF ke PPTX',        'orange', 'PDF'],
          ['pdf_to_jpg',   'fa-image',          'PDF → JPG',   'Tiap halaman jadi gambar', 'red',    'PDF'],
          ['pdf_to_png',   'fa-image',          'PDF → PNG',   'Tiap halaman jadi PNG',    'red',    'PDF'],
        ]; @endphp
        <div class="fc-type-grid">
          @foreach($fromPdf as [$val,$icon,$lbl,$desc,$color,$fmt])
          <button class="fc-type-btn" data-type="{{ $val }}" data-fmt="{{ $fmt }}">
            <div class="fc-type-icon fc-type-icon--{{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
            <div class="fc-type-info">
              <span class="fc-type-name">{{ $lbl }}</span>
              <span class="fc-type-desc">{{ $desc }}</span>
            </div>
            <div class="fc-type-check"><i class="fa-solid fa-check"></i></div>
          </button>
          @endforeach
        </div>
      </div>

      {{-- IMAGE --}}
      <div class="fc-type-group fc-hidden" data-cat="image">
        @php $image = [
          ['jpg_to_png', 'fa-image', 'JPG → PNG', 'Konversi format gambar',  'blue',  'JPG, JPEG'],
          ['png_to_jpg', 'fa-image', 'PNG → JPG', 'Kompres ke JPEG',         'blue',  'PNG'],
          ['jpg_to_webp','fa-image', 'JPG → WebP','Format web modern',       'green', 'JPG, JPEG'],
          ['png_to_webp','fa-image', 'PNG → WebP','Format web modern',       'green', 'PNG'],
          ['webp_to_jpg','fa-image', 'WebP → JPG','WebP ke JPEG',            'orange','WEBP'],
          ['webp_to_png','fa-image', 'WebP → PNG','WebP ke PNG',             'orange','WEBP'],
        ]; @endphp
        <div class="fc-type-grid">
          @foreach($image as [$val,$icon,$lbl,$desc,$color,$fmt])
          <button class="fc-type-btn" data-type="{{ $val }}" data-fmt="{{ $fmt }}">
            <div class="fc-type-icon fc-type-icon--{{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
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

    {{-- MAIN CARD --}}
    <div class="fc-card fc-hidden" id="fc-main-card">
      <div class="fc-card-glow"></div>

      {{-- Upload --}}
      <div class="fc-step" id="step-upload">
        <div class="fc-step-label-row">
          <label class="fc-label">Upload File <span class="fc-multi-badge">maks. 5 file</span></label>
          <span class="fc-accepted-hint" id="accepted-hint">Mendukung: JPG, PNG</span>
        </div>

        <div class="fc-drop-zone" id="drop-zone">
          <input type="file" id="file-input" class="fc-file-input" accept="" multiple>
          <div class="fc-drop-inner" id="drop-placeholder">
            <div class="fc-drop-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
            <p class="fc-drop-title">Drop file di sini</p>
            <p class="fc-drop-hint">atau klik untuk browse · maks. 5 file · 10 MB per file</p>
          </div>
        </div>

        {{-- File List --}}
        <div class="fc-file-list" id="file-list"></div>

        {{-- Add more --}}
        <button class="fc-add-more fc-hidden" id="btn-add-more">
          <i class="fa-solid fa-plus"></i>
          <span>Tambah File Lagi</span>
          <span class="fc-add-count" id="add-count">0/5</span>
        </button>
      </div>

      {{-- Convert Button --}}
      <button type="button" id="btn-convert" class="fc-btn-convert" disabled>
        <i class="fa-solid fa-rotate"></i>
        <span id="btn-convert-label">Konversi Sekarang</span>
      </button>

      {{-- Processing --}}
      <div class="fc-state fc-hidden" id="state-processing">
        <div class="fc-spinner-ring"><div class="fc-spinner-inner"></div></div>
        <p class="fc-state-title" id="proc-title">Mengkonversi file...</p>
        <div class="fc-progress-wrap"><div class="fc-progress-bar" id="progress-bar"></div></div>
        <p class="fc-state-sub" id="proc-sub">Memproses 0 / 0 file</p>
      </div>

      {{-- Result --}}
      <div class="fc-state fc-hidden" id="state-result">
        <div class="fc-result-icon"><i class="fa-solid fa-check"></i></div>
        <p class="fc-result-title" id="result-title">Konversi Selesai!</p>
        <p class="fc-result-sub" id="result-sub">File siap diunduh</p>

        <div class="fc-result-files" id="result-files"></div>

        <button type="button" id="btn-download-all" class="fc-btn-download fc-hidden">
          <i class="fa-solid fa-file-zipper"></i>
          <span>Download Semua (ZIP)</span>
        </button>

        <button type="button" id="btn-reset" class="fc-btn-reset">
          <i class="fa-solid fa-rotate-left"></i><span>Konversi Lagi</span>
        </button>
      </div>

      {{-- Error --}}
      <div class="fc-state fc-hidden" id="state-error">
        <div class="fc-error-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <p class="fc-state-title">Konversi Gagal</p>
        <p class="fc-state-sub" id="error-msg">Terjadi kesalahan.</p>
        <button type="button" id="btn-retry" class="fc-btn-reset">
          <i class="fa-solid fa-rotate-right"></i><span>Coba Lagi</span>
        </button>
      </div>

    </div>{{-- /fc-card --}}

    {{-- INFO --}}
    <div class="fc-info-grid">
      <div class="fc-info-card">
        <div class="fc-info-icon"><i class="fa-solid fa-layer-group"></i></div>
        <h3>Multi-File Sekaligus</h3>
        <p>Upload hingga 5 file sekaligus. Setiap file diproses dan bisa didownload individual atau sebagai ZIP.</p>
      </div>
      <div class="fc-info-card">
        <div class="fc-info-icon"><i class="fa-solid fa-lock"></i></div>
        <h3>Privasi Terjaga</h3>
        <p>File dihapus otomatis dari server setelah 15 menit. Tidak ada yang menyimpan dokumen Anda.</p>
      </div>
      <div class="fc-info-card">
        <div class="fc-info-icon"><i class="fa-solid fa-infinity"></i></div>
        <h3>Gratis Tanpa Batas</h3>
        <p>Tidak perlu daftar akun atau berlangganan. Gunakan sepuasnya secara gratis.</p>
      </div>
    </div>

  </div>
</div>

<div id="fc-toast" class="fc-toast">
  <i class="fa-solid fa-check fc-toast-ico"></i>
  <span id="fc-toast-msg">Berhasil!</span>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="{{ asset('js/fileconverter.js') }}"></script>
@endpush
@endsection