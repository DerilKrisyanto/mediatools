@extends('layouts.app')

@section('og_image', 'signature')
@section('title', 'Email Signature Generator Gratis — Tanda Tangan Email Profesional | MediaTools')
@section('meta_description', 'Buat tanda tangan email profesional dalam hitungan menit. 3 template siap pakai (Klasik, Modern, Elegan), copy-paste ke Gmail, Outlook & semua email client. HTML email signature gratis unlimited.')
@section('meta_keywords', 'email signature gratis, buat tanda tangan email, email signature generator, tanda tangan email profesional, signature email gratis, gmail signature template, professional email signature, html email signature, outlook email signature, email signature maker, buat signature email, template tanda tangan email, email footer profesional, signature gmail, email signature creator')
@include('seo.signature')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/signature.css') }}">
<link rel="stylesheet" href="{{ asset('css/ads.css') }}">
@endpush

@section('content')
<link rel="stylesheet" href="{{ asset('css/tools-base.css') }}">
<script>document.body.classList.add('tlb-active');</script>

<div class="tlb-page tlb-pink" id="tlbPage_signature">

{{-- ════ TLB HEADER ════ --}}
<div class="tlb-header">
    <div class="tlb-header-inner">
        <div>
            <div class="tlb-header-label-row">
                <div class="tlb-header-icon">
                    <i class="fa-solid fa-signature"></i>
                </div>
                <span class="tlb-header-site">MediaTools</span>
            </div>
            <div class="tlb-header-badges">
                <span class="tlb-hbadge"><i class="fa-solid fa-layer-group"></i> 3 Template</span>
                <span class="tlb-hbadge"><i class="fa-solid fa-brands fa-google"></i> Gmail Ready</span>
                <span class="tlb-hbadge"><i class="fa-solid fa-envelope"></i> Outlook Ready</span>
                <span class="tlb-hbadge"><i class="fa-solid fa-code"></i> HTML Export</span>
            </div>
            <nav aria-label="Breadcrumb" class="flex justify-center mb-5">
                <ol class="flex items-center gap-2 text-xs" style="color:var(--text-3)">
                    <li><a href="{{ url('/') }}" style="color:var(--text-3);text-decoration:none;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text-3)'">Home</a></li>
                    <li style="margin:0 4px;font-size:9px;">›</li>
                    <li style="color:var(--accent);font-weight:600;">Email Signature Generator Gratis — Tanda Tangan Email Profesional</li>
                </ol>
            </nav>
            <h1 class="tlb-header-title">Signature <span>Studio.</span></h1>
            <p class="tlb-header-sub">Bangun identitas email profesional dalam hitungan menit. 3 template, copy-paste ke Gmail & Outlook.</p>
        </div>
    </div>
</div>
<div class="tlb-header-curve"></div>

<div class="tlb-body">
{{-- ═══ ADS SLOT ═══ --}}
<div class="ads-slot-header no-print" style="margin-bottom:20px;">@include('components.ads.banner-header')</div>

<div class="sig-shell selection:bg-[#a3e635] selection:text-black">
    {{-- ═══ SLOT 1: HEADER BANNER 728×90 ═══ --}}

<div class="max-w-7xl mx-auto px-4 sm:px-6">

    {{-- ═══ TOP BAR ═══ --}}

    {{-- ═══ MAIN GRID ═══ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        {{-- ─── LEFT: EDITOR ─── --}}
        <div class="lg:col-span-5 order-2 lg:order-1 space-y-5">

            <form id="signature-form"
                  action="{{ route('tools.signature.store') }}"
                  onsubmit="event.preventDefault(); @auth saveSignature(); @else window.location.href='{{ route('login') }}'; @endauth">

                {{-- ── Identity Card ── --}}
                <div class="sig-glass p-7 relative overflow-hidden">
                    <div class="absolute top-5 right-5 opacity-[0.06] pointer-events-none select-none">
                        <i class="fa-solid fa-id-card text-7xl"></i>
                    </div>

                    <div class="sig-section-title">
                        <span class="dot"></span>
                        Identitas Profesional
                    </div>

                    <div class="space-y-4">

                        {{-- Name --}}
                        <div>
                            <label class="sig-label" for="name">Nama Lengkap</label>
                            <input type="text" id="name" name="name"
                                   class="sig-input"
                                   value="{{ $signature->name ?? (Auth::user()->name ?? '') }}"
                                   placeholder="Contoh: Budi Santoso">
                        </div>

                        {{-- Job + Company --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="sig-label" for="job_title">Jabatan</label>
                                <input type="text" id="job_title" name="job_title"
                                       class="sig-input"
                                       value="{{ $signature->job_title ?? '' }}"
                                       placeholder="Lead Designer">
                            </div>
                            <div>
                                <label class="sig-label" for="company">Perusahaan</label>
                                <input type="text" id="company" name="company"
                                       class="sig-input"
                                       value="{{ $signature->company ?? '' }}"
                                       placeholder="MediaTools ID">
                            </div>
                        </div>

                        {{-- Avatar --}}
                        <div>
                            <label class="sig-label">Foto / Logo</label>
                            <div class="flex items-center gap-5">
                                <div class="sig-avatar-wrap">
                                    <img id="avatar-preview"
                                         src="{{ $signature->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name ?? 'User').'&background=a3e635&color=0f172a&bold=true&size=128' }}"
                                         class="sig-avatar-img" alt="Avatar">
                                    <label for="avatarInput" class="sig-avatar-overlay" title="Ubah foto">
                                        <i class="fa-solid fa-camera text-white text-sm"></i>
                                    </label>
                                </div>
                                <input type="file" id="avatarInput" name="avatar_file" accept="image/*"
                                       class="hidden" style="display:none;">
                                <input type="hidden" id="avatar_base64" name="avatar_base64">
                                <div style="font-size:11px;color:rgba(255,255,255,.35);line-height:1.6;">
                                    Klik foto untuk mengganti.<br>
                                    Format: PNG / JPG · Maks 2MB.<br>
                                    Rekomendasi: <strong style="color:rgba(255,255,255,.5);">200×200px</strong>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ── Contact Card ── --}}
                <div class="sig-glass p-7 relative overflow-hidden">
                    <div class="absolute top-5 right-5 opacity-[0.06] pointer-events-none select-none">
                        <i class="fa-solid fa-address-book text-7xl"></i>
                    </div>

                    <div class="sig-section-title">
                        <span class="dot"></span>
                        Informasi Kontak
                    </div>

                    <div class="space-y-4">

                        {{-- Email --}}
                        <div>
                            <label class="sig-label" for="email">Email Bisnis</label>
                            <input type="email" id="email" name="email"
                                   class="sig-input"
                                   value="{{ $signature->email ?? (Auth::user()->email ?? '') }}"
                                   placeholder="nama@perusahaan.com">
                        </div>

                        {{-- Phone + Website --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="sig-label" for="phone">Nomor Telepon</label>
                                <input type="text" id="phone" name="phone"
                                       class="sig-input"
                                       value="{{ $signature->phone ?? '' }}"
                                       placeholder="+62 812 3456 7890">
                            </div>
                            <div>
                                <label class="sig-label" for="website">Website</label>
                                <input type="text" id="website" name="website"
                                       class="sig-input"
                                       value="{{ $signature->website ?? '' }}"
                                       placeholder="www.domain.id">
                            </div>
                        </div>

                        {{-- LinkedIn --}}
                        <div>
                            <label class="sig-label" for="linkedin">LinkedIn</label>
                            <input type="text" id="linkedin" name="linkedin"
                                   class="sig-input"
                                   value="{{ $signature->linkedin ?? '' }}"
                                   placeholder="linkedin.com/in/nama-anda">
                        </div>

                        {{-- Address --}}
                        <div>
                            <label class="sig-label" for="address">Alamat Kantor <span style="color:rgba(255,255,255,.2);font-weight:600;">(Opsional)</span></label>
                            <input type="text" id="address" name="address"
                                   class="sig-input"
                                   value="{{ $signature->address ?? '' }}"
                                   placeholder="Jl. Sudirman No.1, Jakarta">
                        </div>

                    </div>
                </div>

                {{-- ── Design Card ── --}}
                <div class="sig-glass p-7 relative overflow-hidden">
                    <div class="absolute top-5 right-5 opacity-[0.06] pointer-events-none select-none">
                        <i class="fa-solid fa-palette text-7xl"></i>
                    </div>

                    <div class="sig-section-title">
                        <span class="dot"></span>
                        Template & Warna
                    </div>

                    {{-- Template Selector --}}
                    <label class="sig-label" style="margin-bottom:12px;">Pilih Template</label>
                    <div class="sig-tpl-selector" style="margin-bottom:24px;">

                        {{-- Template 1: Klasik --}}
                        <button type="button" class="sig-tpl-btn active" data-tpl="1" onclick="setTemplate(1)">
                            <div class="sig-tpl-thumb thumb-1">
                                <div class="thumb-1-line" style="background:#a3e635;"></div>
                                <div class="thumb-1-text">
                                    <div class="thumb-bar" style="background:#1e293b;width:70%;"></div>
                                    <div class="thumb-bar" style="background:#94a3b8;width:50%;"></div>
                                    <div class="thumb-bar" style="background:#94a3b8;width:60%;"></div>
                                </div>
                            </div>
                            <span class="sig-tpl-name">Klasik</span>
                        </button>

                        {{-- Template 2: Modern --}}
                        <button type="button" class="sig-tpl-btn" data-tpl="2" onclick="setTemplate(2)">
                            <div class="sig-tpl-thumb thumb-2">
                                <div class="thumb-2-header" style="background:#a3e635;"></div>
                                <div class="thumb-2-body">
                                    <div style="width:20px;height:20px;border-radius:50%;background:#e2e8f0;flex-shrink:0;"></div>
                                    <div style="flex:1;display:flex;flex-direction:column;gap:3px;">
                                        <div class="thumb-bar" style="background:#1e293b;width:80%;"></div>
                                        <div class="thumb-bar" style="background:#94a3b8;width:60%;"></div>
                                    </div>
                                </div>
                            </div>
                            <span class="sig-tpl-name">Modern</span>
                        </button>

                        {{-- Template 3: Elegan --}}
                        <button type="button" class="sig-tpl-btn" data-tpl="3" onclick="setTemplate(3)">
                            <div class="sig-tpl-thumb thumb-3" style="flex-direction:column;gap:0;padding:5px;">
                                <div style="width:24px;height:24px;border-radius:50%;background:#e2e8f0;margin:0 auto 3px;border:2px solid #a3e635;flex-shrink:0;"></div>
                                <div style="display:flex;flex-direction:column;gap:2px;align-items:center;width:100%;">
                                    <div class="thumb-bar" style="background:#1e293b;width:65%;"></div>
                                    <div class="thumb-bar" style="background:#a3e635;width:40%;"></div>
                                    <div class="thumb-bar" style="background:#e2e8f0;width:80%;"></div>
                                </div>
                            </div>
                            <span class="sig-tpl-name">Elegan</span>
                        </button>

                    </div>

                    {{-- Color Picker --}}
                    <label class="sig-color-label">Warna Aksen</label>
                    <div class="sig-colors" id="sig-colors">
                        {{-- Built by JS --}}
                    </div>

                </div>

                {{-- ── Save Button ── --}}
                <button type="submit" id="btn-save" class="sig-save-btn">
                    @auth
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan Signature</span>
                    @else
                        <i class="fa-solid fa-lock"></i>
                        <span>Masuk untuk Menyimpan</span>
                    @endauth
                </button>

            </form>
        </div>

        {{-- ─── RIGHT: PREVIEW ─── --}}
        <div class="lg:col-span-7 order-1 lg:order-2">
            <div class="sig-preview-panel">

                {{-- Preview Panel --}}
                <div class="sig-preview-wrap">

                    {{-- Topbar --}}
                    <div class="sig-preview-bar">
                        <div class="sig-live-indicator">
                            <span class="sig-live-dot"></span>
                            Live Preview
                        </div>
                        <div class="flex gap-2">
                            <div style="width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);"></div>
                            <div style="width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);"></div>
                            <div style="width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);"></div>
                        </div>
                    </div>

                    {{-- Signature Canvas --}}
                    <div class="sig-preview-bg">
                        <div id="signature-content">
                            {{-- Rendered by signature.js --}}
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="sig-actions">
                        <button type="button" onclick="downloadSignature()" class="sig-action-btn primary">
                            <i class="fa-solid fa-download"></i>
                            Download PNG
                        </button>
                        <button type="button" onclick="window.print()" class="sig-action-btn secondary">
                            <i class="fa-solid fa-print"></i>
                            Print
                        </button>
                        <button type="button" onclick="copyHTML()" class="sig-action-btn copy-btn">
                            <i class="fa-solid fa-code"></i>
                            Salin HTML untuk Gmail / Outlook
                        </button>
                    </div>

                </div>

                {{-- Deployment Guide --}}
                <div class="sig-guide">
                    <div class="sig-guide-icon">
                        <i class="fa-solid fa-lightbulb text-[#040f0f] text-lg"></i>
                    </div>
                    <div>
                        <div class="sig-guide-title">Cara Pasang di Email</div>
                        <div class="sig-steps">
                            <div class="sig-step">Klik <b>"Salin HTML"</b> di atas untuk menyalin kode signature.</div>
                            <div class="sig-step">Buka <b>Gmail → Pengaturan → Umum → Tanda tangan</b>, klik edit.</div>
                            <div class="sig-step">Klik ikon <b>"&lt;&gt; Source code"</b> lalu tempel HTML yang disalin.</div>
                            <div class="sig-step">Klik <b>Simpan perubahan</b> di bagian bawah halaman. Selesai! ⚡</div>
                        </div>
                        <p class="sig-guide-text" style="margin-top:10px;">
                            Semua style sudah inline sehingga kompatibel dengan Gmail, Outlook, Apple Mail, dan semua email client populer.
                        </p>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
</div>

{{-- ── Toast ── --}}
<div id="toast" style="font-family:'Plus Jakarta Sans',sans-serif;">
    <div id="toast-icon-box" style="width:32px;height:32px;border-radius:50%;background:rgba(0,0,0,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:12px;">
        <i class="fa-solid fa-check"></i>
    </div>
    <span id="toast-message">Notifikasi</span>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="{{ asset('js/signature.js') }}"></script>
@endpush

</div>{{-- /.tlb-body --}}
</div>{{-- /.tlb-page --}}
