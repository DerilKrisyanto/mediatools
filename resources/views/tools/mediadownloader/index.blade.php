@extends('layouts.app')

@section('title', 'Download Video YouTube TikTok Instagram Gratis — MediaTools')
@section('meta_description', 'Download video dan audio dari YouTube, TikTok, dan Instagram. Cukup paste URL, pilih format, langsung download. Gratis tanpa daftar, tanpa watermark.')
@section('meta_keywords', 'download video youtube gratis, download tiktok tanpa watermark, download video instagram, youtube downloader indonesia')

@push('json_ld')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "Media Downloader — MediaTools",
  "url": "https://mediatools.cloud/media-downloader",
  "applicationCategory": "MultimediaApplication",
  "operatingSystem": "Any",
  "offers": { "@type": "Offer", "price": "0", "priceCurrency": "IDR" },
  "description": "Download video YouTube, TikTok, Instagram gratis. Paste URL, langsung download.",
  "inLanguage": "id"
}
</script>
@endpush

@section('content')
<link rel="stylesheet" href="{{ asset('css/mediadownloader.css') }}">

<div class="md-page">
  <div class="md-container">

    {{-- HEADER --}}
    <header class="md-header">
      <div class="md-badge-row">
        <span class="md-badge-free">100% Gratis</span>
        <span class="md-badge-secure"><i class="fa-solid fa-shield-halved"></i> No Watermark</span>
        <span class="md-badge-secure"><i class="fa-solid fa-bolt"></i> Instan</span>
      </div>
      <h1 class="md-title">Media <span class="md-title-accent">Downloader.</span></h1>
      <p class="md-subtitle">Download video & audio dari YouTube, TikTok, dan Instagram. Cukup paste URL — selesai.</p>
    </header>

    {{-- PLATFORM TABS --}}
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

    {{-- MAIN CARD --}}
    <div class="md-card">
      <div class="md-card-glow"></div>

      {{-- URL INPUT --}}
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
                 autocomplete="off" spellcheck="false">
          <button class="md-paste-btn" id="btn-paste" title="Tempel dari clipboard">
            <i class="fa-regular fa-clipboard"></i>
            <span>Tempel</span>
          </button>
          <button class="md-clear-btn" id="btn-clear" title="Hapus">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
        <div class="md-url-examples" id="url-examples">
          <span class="md-example-label">Contoh:</span>
          <span class="md-example">youtube.com/watch?v=...</span>
          <span class="md-example">youtu.be/...</span>
          <span class="md-example">youtube.com/shorts/...</span>
        </div>
      </div>

      {{-- YOUTUBE OPTIONS --}}
      <div class="md-options-section" id="yt-options">
        <label class="md-label">Format Download</label>
        <div class="md-format-grid">
          <button class="md-format-btn active" data-format="mp4">
            <div class="md-format-icon md-format-icon--video">
              <i class="fa-solid fa-video"></i>
            </div>
            <div class="md-format-info">
              <span class="md-format-name">MP4 Video</span>
              <span class="md-format-desc">Video + Audio</span>
            </div>
            <div class="md-format-check"><i class="fa-solid fa-check"></i></div>
          </button>
          <button class="md-format-btn" data-format="mp3">
            <div class="md-format-icon md-format-icon--audio">
              <i class="fa-solid fa-music"></i>
            </div>
            <div class="md-format-info">
              <span class="md-format-name">MP3 Audio</span>
              <span class="md-format-desc">Hanya suara</span>
            </div>
            <div class="md-format-check"><i class="fa-solid fa-check"></i></div>
          </button>
        </div>

        {{-- Quality (YouTube MP4 only) --}}
        <div class="md-quality-section" id="quality-section">
          <label class="md-label">Kualitas Video</label>
          <div class="md-quality-grid">
            <button class="md-quality-btn" data-quality="144">144p</button>
            <button class="md-quality-btn" data-quality="360">360p</button>
            <button class="md-quality-btn active" data-quality="720">720p HD</button>
            <button class="md-quality-btn" data-quality="1080">1080p FHD</button>
          </div>
        </div>
      </div>

      {{-- TIKTOK OPTIONS --}}
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
              <span class="md-toggle-hint">Hanya download suara/musik</span>
            </div>
            <div class="md-toggle-wrap">
              <input type="checkbox" id="tt-audio-only" class="md-toggle-input">
              <div class="md-toggle-track"><div class="md-toggle-thumb"></div></div>
            </div>
          </label>
        </div>
      </div>

      {{-- INSTAGRAM OPTIONS --}}
      <div class="md-options-section md-hidden" id="ig-options">
        <div class="md-info-box">
          <i class="fa-solid fa-circle-info text-[#a3e635]"></i>
          <p>Mendukung Reels, foto post, dan video post Instagram yang bersifat publik.</p>
        </div>
      </div>

      {{-- OTHER OPTIONS --}}
      <div class="md-options-section md-hidden" id="other-options">
        <div class="md-info-box">
          <i class="fa-solid fa-circle-info text-[#a3e635]"></i>
          <p>Mendukung Twitter/X, Reddit, Pinterest, SoundCloud, Vimeo, Dailymotion, dan 20+ platform lainnya.</p>
        </div>
      </div>

      {{-- PROCESS BUTTON --}}
      <button type="button" id="btn-process" class="md-btn-process" disabled>
        <i class="fa-solid fa-download"></i>
        <span id="btn-process-label">Download Sekarang</span>
      </button>

      {{-- STATES --}}

      {{-- Processing --}}
      <div class="md-state md-hidden" id="state-processing">
        <div class="md-spinner-ring"><div class="md-spinner-inner"></div></div>
        <p class="md-state-title">Memproses...</p>
        <p class="md-state-sub" id="proc-detail">Mengambil informasi media</p>
        <div class="md-progress-wrap">
          <div class="md-progress-bar" id="progress-bar"></div>
        </div>
      </div>

      {{-- Result --}}
      <div class="md-state md-hidden" id="state-result">
        <div class="md-result-thumb-wrap">
          <img id="result-thumb" src="" alt="" class="md-result-thumb md-hidden">
          <div class="md-result-success-icon">
            <i class="fa-solid fa-check"></i>
          </div>
        </div>
        <p class="md-result-title" id="result-title">Siap Download!</p>
        <p class="md-result-sub" id="result-sub">File siap diunduh</p>

        {{-- Single download --}}
        <div id="result-single" class="md-result-actions md-hidden">
          <a href="#" id="btn-download-single" class="md-btn-download" target="_blank" download>
            <i class="fa-solid fa-download"></i>
            <span id="download-label">Download File</span>
          </a>
        </div>

        {{-- Picker (multiple items, e.g. Instagram carousel) --}}
        <div id="result-picker" class="md-picker-grid md-hidden"></div>

        <button type="button" id="btn-reset" class="md-btn-reset">
          <i class="fa-solid fa-rotate-left"></i>
          <span>Download Lagi</span>
        </button>
      </div>

      {{-- Error --}}
      <div class="md-state md-hidden" id="state-error">
        <div class="md-error-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <p class="md-state-title">Terjadi Kesalahan</p>
        <p class="md-state-sub" id="error-msg">URL tidak valid atau konten tidak bisa diakses.</p>
        <div class="md-error-tips" id="error-tips">
          <p class="md-tips-title"><i class="fa-solid fa-lightbulb"></i> Tips:</p>
          <ul class="md-tips-list" id="tips-list"></ul>
        </div>
        <button type="button" id="btn-retry" class="md-btn-reset">
          <i class="fa-solid fa-rotate-right"></i>
          <span>Coba Lagi</span>
        </button>
      </div>

    </div>{{-- /md-card --}}

    {{-- SUPPORTED PLATFORMS --}}
    <div class="md-platforms-section">
      <p class="md-platforms-title">Platform yang Didukung</p>
      <div class="md-platforms-grid">
        @foreach([
          ['fa-youtube','YouTube','Video, Shorts, Music'],
          ['fa-tiktok','TikTok','Video, Slideshow'],
          ['fa-instagram','Instagram','Reels, Foto, Video'],
          ['fa-twitter','Twitter/X','Video, GIF'],
          ['fa-reddit','Reddit','Video, GIF'],
          ['fa-pinterest','Pinterest','Video, Foto'],
        ] as [$icon, $name, $desc])
        <div class="md-platform-card">
          <i class="fa-brands {{ $icon }}"></i>
          <span class="md-pcard-name">{{ $name }}</span>
          <span class="md-pcard-desc">{{ $desc }}</span>
        </div>
        @endforeach
      </div>
    </div>

    {{-- INFO CARDS --}}
    <div class="md-info-cards">
      <div class="md-info-card">
        <div class="md-icard-icon"><i class="fa-solid fa-bolt"></i></div>
        <h3>Instan & Gratis</h3>
        <p>Tidak perlu daftar akun. Cukup paste URL dan download langsung dalam hitungan detik.</p>
      </div>
      <div class="md-info-card">
        <div class="md-icard-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <h3>Aman & Privat</h3>
        <p>Tidak ada file yang tersimpan di server kami. Konten diproses dan langsung diteruskan ke browser Anda.</p>
      </div>
      <div class="md-info-card">
        <div class="md-icard-icon"><i class="fa-solid fa-star"></i></div>
        <h3>Kualitas Terbaik</h3>
        <p>Download dalam resolusi asli hingga 1080p untuk video dan kualitas audio terbaik yang tersedia.</p>
      </div>
    </div>

  </div>
</div>

{{-- TOAST --}}
<div id="md-toast" class="md-toast">
  <i class="fa-solid fa-check md-toast-ico" id="toast-ico"></i>
  <span id="toast-msg">Berhasil!</span>
</div>

@push('scripts')
<script src="{{ asset('js/mediadownloader.js') }}"></script>
@endpush
@endsection