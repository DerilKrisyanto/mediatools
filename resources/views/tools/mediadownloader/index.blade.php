@extends('layouts.app')

@section('og_image', 'mediadownloader')
@section('title', 'Download Video YouTube TikTok Instagram MP4 MP3 Gratis Tanpa Watermark | MediaTools')
@section('meta_description', 'Download video YouTube 1080p HD, convert YouTube ke MP3, TikTok tanpa watermark, Instagram Reels gratis. Cukup paste URL — langsung download. Alternatif terbaik SaveFrom.net & SnapTik.')
@section('meta_keywords', 'download video youtube, youtube downloader gratis, youtube to mp3, download tiktok tanpa watermark, download video instagram, youtube mp3 downloader, savefrom alternative, snaptik alternative, download reels instagram, download video online gratis, yt downloader, youtube 1080p download, tiktok video download, instagram video download')
@include('seo.mediadownloader')

{{-- Routes for JS --}}
<meta name="md-process-url"  content="{{ route('tools.mediadownloader.process') }}">
<meta name="md-download-url" content="{{ url('media-downloader/download') }}">

@section('content')
<link rel="stylesheet" href="{{ asset('css/tools-base.css') }}">
<script>document.body.classList.add('tlb-active');</script>

<div class="tlb-page tlb-violet" id="tlbPage_mediadownloader">

{{-- ════ TLB HEADER ════ --}}
<div class="tlb-header">
    <div class="tlb-header-inner">
        <div>
            <nav aria-label="Breadcrumb" class="flex justify-left mb-5">
                <ol class="flex items-center gap-2 text-xs" style="color:var(--text-3)">
                    <li><a href="{{ url('/') }}" style="color:var(--text-3);text-decoration:none;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text-3)'">Home</a></li>
                    <li style="margin:0 4px;font-size:9px;">›</li>
                    <li style="color:var(--accent);font-weight:600;">Media Downloader</li>
                </ol>
            </nav>
            <div class="tlb-header-badges">
                <span class="tlb-hbadge"><i class="fa-solid fa-brands fa-youtube"></i> YouTube</span>
                <span class="tlb-hbadge"><i class="fa-solid fa-brands fa-tiktok"></i> TikTok</span>
                <span class="tlb-hbadge"><i class="fa-solid fa-brands fa-instagram"></i> Instagram</span>
                <span class="tlb-hbadge"><i class="fa-solid fa-star"></i> Hingga 1080p</span>
            </div>
            <h1 class="tlb-header-title">Media <span>Downloader.</span></h1>
            <p class="tlb-header-sub">Download video & audio dari YouTube, TikTok, Instagram, dan 20+ platform. Cukup paste URL — selesai.</p>
        </div>
    </div>
</div>
<div class="tlb-header-curve"></div>

<div class="tlb-body">
{{-- ═══ ADS SLOT ═══ --}}
<div class="ads-slot-header no-print" style="margin-bottom:20px;">@include('components.ads.banner-header')</div>

<link rel="stylesheet" href="{{ asset('css/mediadownloader.css') }}">
<link rel="stylesheet" href="{{ asset('css/ads.css') }}">

<div class="md-page">
  {{-- ═══ SLOT 1: HEADER BANNER 728×90 ═══ --}}

  <div class="md-container">

    {{-- ── HEADER ── --}}

    {{-- ── PLATFORM TABS ── --}}
    <div class="md-platform-tabs">
      <button class="md-platform-btn active" data-platform="youtube">
        <i class="fa-brands fa-youtube"></i>
        <span>YouTube</span>
      </button>
      <button class="md-platform-btn" data-platform="tiktok">
        <i class="fa-brands fa-tiktok"></i>
        <span>TikTok</span>
      </button>
      <button class="md-platform-btn" data-platform="instagram">
        <i class="fa-brands fa-instagram"></i>
        <span>Instagram</span>
      </button>
      <button class="md-platform-btn" data-platform="other">
        <i class="fa-solid fa-globe"></i>
        <span>Lainnya</span>
      </button>
    </div>

    {{-- ── MAIN CARD ── --}}
    <div class="md-card">
      {{-- URL Input --}}
      <div class="md-input-section">
        <div class="md-input-label-row">
          <label class="md-label">URL Video / Postingan</label>
          <span class="md-platform-hint" id="platform-hint">YouTube · Shorts · Music</span>
        </div>
        <div class="md-input-wrap">
          <div class="md-input-icon" id="input-platform-icon">
            <i class="fa-brands fa-youtube"></i>
          </div>
          <input type="url" id="media-url" class="md-input"
                 placeholder="https://www.youtube.com/watch?v=..."
                 autocomplete="off" spellcheck="false"
                 aria-label="Masukkan URL video">
          <button class="md-paste-btn" id="btn-paste" type="button">
            <i class="fa-regular fa-clipboard"></i>
            <span>Tempel</span>
          </button>
          <button class="md-clear-btn" id="btn-clear" type="button" aria-label="Hapus URL">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
        <div class="md-url-examples" id="url-examples">
          <span class="md-example-label">Contoh:</span>
          <span class="md-example">youtube.com/watch?v=...</span>
          <span class="md-example">youtu.be/...</span>
        </div>
      </div>

      {{-- ── YOUTUBE: Format Picker ── --}}
      <div class="md-format-section" id="yt-format-section">
        <label class="md-label">Pilih Format Download</label>
        <div class="md-format-grid">
          <button class="md-format-btn" data-format="mp3" type="button">
            <div class="md-format-icon md-format-icon--audio">
              <i class="fa-solid fa-music"></i>
            </div>
            <div class="md-format-info">
              <span class="md-format-name">MP3 Audio</span>
              <span class="md-format-desc">Hanya suara · Kualitas tinggi</span>
            </div>
            <div class="md-format-check"><i class="fa-solid fa-check"></i></div>
          </button>
          <button class="md-format-btn" data-format="mp4" type="button">
            <div class="md-format-icon md-format-icon--video">
              <i class="fa-solid fa-video"></i>
            </div>
            <div class="md-format-info">
              <span class="md-format-name">MP4 Video</span>
              <span class="md-format-desc">Video + Audio · Hingga 1080p</span>
            </div>
            <div class="md-format-check"><i class="fa-solid fa-check"></i></div>
          </button>
        </div>
      </div>

      {{-- ── YOUTUBE: Quality Picker (MP4 only, shown dynamically) ── --}}
      <div class="md-hidden" id="yt-quality-section" style="margin-bottom:16px;">
        <label class="md-label">Pilih Kualitas Video</label>
        <p class="md-quality-hint" id="quality-hint" style="font-size:11px;color:#6b7280;margin-bottom:10px;">
          <i class="fa-solid fa-spinner fa-spin" style="font-size:10px;"></i>
          Memuat kualitas yang tersedia...
        </p>
        <div class="md-quality-grid" id="quality-grid">
          {{-- Populated by JS --}}
          <div class="md-quality-skeleton"></div>
          <div class="md-quality-skeleton"></div>
          <div class="md-quality-skeleton"></div>
        </div>
      </div>

      {{-- ── TIKTOK Options ── --}}
      <div class="md-options-section md-hidden" id="tt-options">
        <label class="md-label">Opsi TikTok</label>
        <div class="md-toggle-list">
          <label class="md-toggle-row">
            <div class="md-toggle-info">
              <span class="md-toggle-name">Tanpa Watermark</span>
              <span class="md-toggle-hint">Download tanpa logo TikTok</span>
            </div>
            <div class="md-toggle-wrap">
              <input type="checkbox" id="tt-no-watermark" class="md-toggle-input" checked>
              <div class="md-toggle-track"><div class="md-toggle-thumb"></div></div>
            </div>
          </label>
          <label class="md-toggle-row">
            <div class="md-toggle-info">
              <span class="md-toggle-name">Audio Saja</span>
              <span class="md-toggle-hint">Download hanya suara/musik</span>
            </div>
            <div class="md-toggle-wrap">
              <input type="checkbox" id="tt-audio-only" class="md-toggle-input">
              <div class="md-toggle-track"><div class="md-toggle-thumb"></div></div>
            </div>
          </label>
        </div>
      </div>

      {{-- ── INSTAGRAM Options ── --}}
      <div class="md-options-section md-hidden" id="ig-options">
        <div class="md-info-box">
          <i class="fa-solid fa-circle-info"></i>
          <p>Mendukung Reels, foto post, video, dan carousel Instagram dari akun <strong>publik</strong>.</p>
        </div>
      </div>

      {{-- ── OTHER Options ── --}}
      <div class="md-options-section md-hidden" id="other-options">
        <div class="md-info-box">
          <i class="fa-solid fa-circle-info"></i>
          <p>Mendukung Twitter/X, Reddit, Pinterest, SoundCloud, Vimeo, Dailymotion, dan <strong>20+ platform</strong> lainnya.</p>
        </div>
      </div>

      {{-- ── PROCESS BUTTON ── --}}
      <button type="button" id="btn-process" class="md-btn-process" disabled>
        <i class="fa-solid fa-download"></i>
        <span id="btn-process-label">Pilih format terlebih dahulu</span>
      </button>

      {{-- ── PROCESSING STATE ── --}}
      <div class="md-state md-hidden" id="state-processing" role="status" aria-live="polite">
        <div class="md-spinner-wrap"></div>
        <p class="md-state-title" id="proc-title">Memproses...</p>
        <div class="md-progress-section">
          <div class="md-progress-wrap">
            <div class="md-progress-bar" id="progress-bar"></div>
          </div>
          <div class="md-progress-label">
            <span id="progress-step">Memulai...</span>
            <span class="md-progress-pct" id="progress-pct">0%</span>
          </div>
        </div>
        <p style="font-size:10.5px;color:#4b5563;margin-top:12px;">
          <i class="fa-solid fa-clock"></i>
          YouTube 1080p memerlukan 30–120 detik tergantung panjang video
        </p>
      </div>

      {{-- ── RESULT STATE ── --}}
      <div class="md-state md-hidden" id="state-result" role="status" aria-live="polite">
        <div class="md-result-icon"><i class="fa-solid fa-check"></i></div>
        <p class="md-result-title" id="result-title">Siap Download!</p>
        <p class="md-result-sub"   id="result-sub">File siap diunduh</p>

        <div id="result-single" class="md-result-actions md-hidden">
          <a href="#" id="btn-download-single" class="md-btn-download">
            <i class="fa-solid fa-download"></i>
            <span id="download-label">Download File</span>
          </a>
        </div>
        {{-- Tambahkan di dekat tombol download --}}
        @if(config('ads.enabled') && config('ads.provider') === 'adsterra')
        <div style="margin: 16px 0; text-align: center;">
            <a href="https://www.profitablecpmratenetwork.com/vsbz3jzuj?key=99b8473f252ffac89b5ae2a83110d670"
              target="_blank"
              rel="noopener"
              style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:rgba(163,230,53,0.1);border:1px solid rgba(163,230,53,0.2);border-radius:10px;font-size:12px;color:#a3e635;text-decoration:none;"
              onclick="window.open(this.href,'_blank'); return false;">
                <i class="fa-solid fa-download"></i>
                Download via Mirror Server
            </a>
        </div>
        @endif

        <div id="result-picker" class="md-picker-grid md-hidden"></div>

        <button type="button" id="btn-reset" class="md-btn-reset" style="margin-top:14px;">
          <i class="fa-solid fa-rotate-left"></i>
          <span>Download Lagi</span>
        </button>
      </div>

      {{-- ── ERROR STATE ── --}}
      <div class="md-state md-hidden" id="state-error" role="alert">
        <div class="md-error-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <p class="md-state-title">Terjadi Kesalahan</p>
        <p class="md-state-sub" id="error-msg">URL tidak valid atau konten tidak bisa diakses.</p>
        <div class="md-error-tips">
          <p class="md-tips-title"><i class="fa-solid fa-lightbulb"></i> Tips:</p>
          <ul class="md-tips-list" id="tips-list"></ul>
        </div>
        <button type="button" id="btn-retry" class="md-btn-reset" style="margin-top:12px;">
          <i class="fa-solid fa-rotate-right"></i>
          <span>Coba Lagi</span>
        </button>
      </div>

    </div>{{-- /md-card --}}

    {{-- ── INFO CARDS ── --}}
    <div class="md-info-cards">
      <div class="md-info-card">
        <div class="md-icard-icon"><i class="fa-solid fa-bolt"></i></div>
        <h3>Cepat & Gratis</h3>
        <p>Tidak perlu daftar akun. Paste URL dan download dalam hitungan detik.</p>
      </div>
      <div class="md-info-card">
        <div class="md-icard-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <h3>Privasi Aman</h3>
        <p>File otomatis terhapus setelah didownload. Tidak ada yang tersimpan di server.</p>
      </div>
      <div class="md-info-card">
        <div class="md-icard-icon"><i class="fa-solid fa-star"></i></div>
        <h3>Kualitas Asli</h3>
        <p>Download dalam resolusi asli hingga 1080p FHD, atau audio MP3 kualitas tinggi.</p>
      </div>
    </div>

  </div>
  {{-- ═══ SLOT 3: RESULT BANNER 300×250 ═══ --}}
  <div class="ads-slot-result no-print">
      @include('components.ads.banner-result')
  </div>

  {{-- ═══ SLOT 4: NATIVE BANNER ═══ --}}
  <div class="ads-slot-native no-print">
      @include('components.ads.banner-content')
  </div>
</div>

{{-- Toast --}}
<div id="md-toast" class="md-toast" role="alert" aria-live="assertive">
  <i class="fa-solid fa-check md-toast-ico" id="toast-ico"></i>
  <span id="toast-msg">Berhasil!</span>
</div>

@push('scripts')
<script src="{{ asset('js/mediadownloader.js') }}"></script>
@endpush
@endsection

</div>{{-- /.tlb-body --}}
</div>{{-- /.tlb-page --}}
