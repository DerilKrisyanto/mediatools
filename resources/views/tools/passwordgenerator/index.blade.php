@extends('layouts.app')

@section('title', 'Password Generator Online Gratis — Buat Password Kuat & Aman | MediaTools')
@section('meta_description', 'Buat password kuat dan unik secara instan. Semua proses di browser Anda — tidak ada data yang dikirim ke server. Gratis, aman, unlimited.')
@section('meta_keywords', 'password generator gratis, buat password kuat, random password generator, password aman online, generator kata sandi')

@section('content')
<link rel="stylesheet" href="{{ asset('css/passwordgenerator.css') }}">

<div class="pg-page">
  <div class="pg-container">

    {{-- HEADER --}}
    <header class="pg-header">
      <div class="pg-header-inner">
        <div class="pg-badge-row">
          <span class="pg-badge-free">100% Gratis</span>
          <span class="pg-badge-secure"><i class="fa-solid fa-shield-halved"></i> Zero Server</span>
        </div>
        <h1 class="pg-title">Password <span class="pg-title-accent">Generator.</span></h1>
        <p class="pg-subtitle">Buat password kuat & unik secara instan. Semua proses di browser Anda — tidak ada yang dikirim ke server.</p>
      </div>
    </header>

    {{-- MAIN CARD --}}
    <div class="pg-card">
      

      

      {{-- SETTINGS GRID --}}
      <div class="pg-settings">

        {{-- Length --}}
        <div class="pg-setting-block pg-setting-length">
          <div class="pg-setting-header">
            <label class="pg-setting-label">Panjang Password</label>
            <div class="pg-length-display">
              <button class="pg-len-btn" id="len-minus"><i class="fa-solid fa-minus"></i></button>
              <span class="pg-len-val" id="len-val">16</span>
              <button class="pg-len-btn" id="len-plus"><i class="fa-solid fa-plus"></i></button>
            </div>
          </div>
          <input type="range" class="pg-slider" id="length-slider" min="4" max="128" value="16" step="1">
          <div class="pg-len-presets">
            <button class="pg-len-preset" data-len="8">8</button>
            <button class="pg-len-preset" data-len="12">12</button>
            <button class="pg-len-preset active" data-len="16">16</button>
            <button class="pg-len-preset" data-len="24">24</button>
            <button class="pg-len-preset" data-len="32">32</button>
            <button class="pg-len-preset" data-len="64">64</button>
          </div>
        </div>

        {{-- Character Options --}}
        <div class="pg-setting-block">
          <label class="pg-setting-label">Jenis Karakter</label>
          <div class="pg-char-grid">
            <label class="pg-char-opt" id="opt-upper">
              <input type="checkbox" id="use-upper" checked>
              <div class="pg-char-inner">
                <span class="pg-char-sample">ABC</span>
                <span class="pg-char-name">Huruf Besar</span>
                <span class="pg-char-count">26 karakter</span>
              </div>
              <div class="pg-char-check"><i class="fa-solid fa-check"></i></div>
            </label>
            <label class="pg-char-opt" id="opt-lower">
              <input type="checkbox" id="use-lower" checked>
              <div class="pg-char-inner">
                <span class="pg-char-sample">abc</span>
                <span class="pg-char-name">Huruf Kecil</span>
                <span class="pg-char-count">26 karakter</span>
              </div>
              <div class="pg-char-check"><i class="fa-solid fa-check"></i></div>
            </label>
            <label class="pg-char-opt" id="opt-numbers">
              <input type="checkbox" id="use-numbers" checked>
              <div class="pg-char-inner">
                <span class="pg-char-sample">123</span>
                <span class="pg-char-name">Angka</span>
                <span class="pg-char-count">10 karakter</span>
              </div>
              <div class="pg-char-check"><i class="fa-solid fa-check"></i></div>
            </label>
            <label class="pg-char-opt" id="opt-symbols">
              <input type="checkbox" id="use-symbols">
              <div class="pg-char-inner">
                <span class="pg-char-sample">!@#</span>
                <span class="pg-char-name">Simbol</span>
                <span class="pg-char-count">32 karakter</span>
              </div>
              <div class="pg-char-check"><i class="fa-solid fa-check"></i></div>
            </label>
          </div>
        </div>

        {{-- Advanced Options --}}
        <div class="pg-setting-block">
          <label class="pg-setting-label">Opsi Lanjutan</label>
          <div class="pg-toggle-list">
            <label class="pg-toggle-row">
              <div class="pg-toggle-info">
                <span class="pg-toggle-name">Hindari karakter mirip</span>
                <span class="pg-toggle-hint">Hilangkan: 0, O, l, 1, I</span>
              </div>
              <div class="pg-toggle-wrap">
                <input type="checkbox" id="exclude-similar" class="pg-toggle-input">
                <div class="pg-toggle-track"><div class="pg-toggle-thumb"></div></div>
              </div>
            </label>
            <label class="pg-toggle-row">
              <div class="pg-toggle-info">
                <span class="pg-toggle-name">Mudah dibaca</span>
                <span class="pg-toggle-hint">Hanya huruf & angka jelas</span>
              </div>
              <div class="pg-toggle-wrap">
                <input type="checkbox" id="easy-read" class="pg-toggle-input">
                <div class="pg-toggle-track"><div class="pg-toggle-thumb"></div></div>
              </div>
            </label>
            <label class="pg-toggle-row">
              <div class="pg-toggle-info">
                <span class="pg-toggle-name">Minimal 1 dari setiap jenis</span>
                <span class="pg-toggle-hint">Pastikan semua jenis terpilih ada</span>
              </div>
              <div class="pg-toggle-wrap">
                <input type="checkbox" id="ensure-all" class="pg-toggle-input" checked>
                <div class="pg-toggle-track"><div class="pg-toggle-thumb"></div></div>
              </div>
            </label>
          </div>
        </div>

        {{-- Mode --}}
        <div class="pg-setting-block">
          <label class="pg-setting-label">Mode Generate</label>
          <div class="pg-mode-grid">
            <button class="pg-mode-btn active" data-mode="random">
              <i class="fa-solid fa-shuffle"></i>
              <span class="pg-mode-name">Acak</span>
              <span class="pg-mode-hint">Paling aman</span>
            </button>
            <button class="pg-mode-btn" data-mode="memorable">
              <i class="fa-solid fa-brain"></i>
              <span class="pg-mode-name">Mudah Diingat</span>
              <span class="pg-mode-hint">Kata + angka</span>
            </button>
            <button class="pg-mode-btn" data-mode="pin">
              <i class="fa-solid fa-hashtag"></i>
              <span class="pg-mode-name">PIN</span>
              <span class="pg-mode-hint">Angka saja</span>
            </button>
          </div>
        </div>

      </div>

      {{-- GENERATE BUTTON --}}
      <button type="button" class="pg-btn-generate" id="btn-generate">
        <i class="fa-solid fa-wand-magic-sparkles"></i>
        <span>Generate Password</span>
      </button>

      {{-- DIVIDER --}}
      <div class="pg-divider"></div>

      <div class="pg-card-glow"></div>

      {{-- OUTPUT DISPLAY --}}
      <div class="pg-output-wrap">
        <div class="pg-output-field" id="output-field">
          <span class="pg-output-text" id="output-text">Klik Generate</span>
          <div class="pg-output-actions">
            <button class="pg-icon-btn" id="btn-copy" title="Salin password" disabled>
              <i class="fa-regular fa-copy" id="copy-icon"></i>
            </button>
            <button class="pg-icon-btn" id="btn-refresh" title="Generate ulang">
              <i class="fa-solid fa-rotate-right" id="refresh-icon"></i>
            </button>
          </div>
        </div>

        {{-- STRENGTH METER --}}
        <div class="pg-strength-wrap" id="strength-wrap">
          <div class="pg-strength-bar-row">
            <div class="pg-strength-seg" id="seg-1"></div>
            <div class="pg-strength-seg" id="seg-2"></div>
            <div class="pg-strength-seg" id="seg-3"></div>
            <div class="pg-strength-seg" id="seg-4"></div>
            <div class="pg-strength-seg" id="seg-5"></div>
          </div>
          <div class="pg-strength-meta">
            <span class="pg-strength-label" id="strength-label">—</span>
            <span class="pg-strength-entropy" id="strength-entropy"></span>
          </div>
        </div>
      </div>

      {{-- BULK SECTION --}}
      <div class="pg-bulk-wrap">
        <button class="pg-bulk-toggle" id="bulk-toggle">
          <span>Generate Banyak Sekaligus</span>
          <i class="fa-solid fa-chevron-down" id="bulk-chevron"></i>
        </button>
        <div class="pg-bulk-body" id="bulk-body">
          <div class="pg-bulk-controls">
            <div class="pg-bulk-count-wrap">
              <label class="pg-setting-label" style="margin:0">Jumlah</label>
              <div class="pg-bulk-count-row">
                <button class="pg-len-btn" id="bulk-minus"><i class="fa-solid fa-minus"></i></button>
                <span class="pg-len-val" id="bulk-val">5</span>
                <button class="pg-len-btn" id="bulk-plus"><i class="fa-solid fa-plus"></i></button>
              </div>
            </div>
            <button class="pg-btn-bulk" id="btn-bulk-generate">
              <i class="fa-solid fa-layer-group"></i>
              <span>Generate</span>
            </button>
          </div>
          <div class="pg-bulk-list" id="bulk-list"></div>
          <button class="pg-btn-copy-all ic-hidden" id="btn-copy-all">
            <i class="fa-solid fa-copy"></i>
            <span>Salin Semua</span>
          </button>
        </div>
      </div>

    </div>

    {{-- INFO CARDS --}}
    <div class="pg-info-grid">
      <div class="pg-info-card">
        <div class="pg-info-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <h3>Mengapa Password Kuat?</h3>
        <p>Password dengan 16+ karakter campuran membutuhkan miliaran tahun untuk di-crack dengan brute force attack.</p>
      </div>
      <div class="pg-info-card">
        <div class="pg-info-icon"><i class="fa-solid fa-lock"></i></div>
        <h3>Privasi Terjamin</h3>
        <p>Semua password dibuat menggunakan <code>crypto.getRandomValues()</code> browser — tidak pernah menyentuh server kami.</p>
      </div>
      <div class="pg-info-card">
        <div class="pg-info-icon"><i class="fa-solid fa-key"></i></div>
        <h3>Tips Keamanan</h3>
        <p>Gunakan password manager seperti Bitwarden atau 1Password untuk menyimpan password unik di setiap akun.</p>
      </div>
    </div>

  </div>

  {{-- TOAST --}}
  <div id="pg-toast" class="pg-toast" role="alert">
    <i class="fa-solid fa-check pg-toast-ico"></i>
    <span id="pg-toast-msg">Password disalin!</span>
  </div>
</div>

@push('scripts')
<script src="{{ asset('js/passwordgenerator.js') }}"></script>
@endpush
@endsection