@extends('layouts.app')

@section('og_image', 'qr')
@section('title', 'QR Code Generator Gratis — Buat QR Code Custom Bisnis Online | MediaTools')
@section('meta_description', 'Buat QR Code custom gratis untuk menu restoran, pembayaran, kontak, WiFi, dan URL bisnis. Download PNG resolusi tinggi, kustom warna & logo, tanpa watermark, tanpa daftar.')
@section('meta_keywords', 'qr code generator gratis, buat qr code, qr code maker, qr code free, qr code online, buat qr code bisnis, qr code custom logo, qr code menu restoran, generate qr code online, qr code pembayaran, qr code url, qr code wifi, buat qr code custom, qr code download png, qr code creator')
@include('seo.qr')

@section('content')
<link rel="stylesheet" href="{{ asset('css/tools-base.css') }}">
<script>document.body.classList.add('tlb-active');</script>

<div class="tlb-page tlb-lime" id="tlbPage_qr">

{{-- ════ TLB HEADER ════ --}}
<div class="tlb-header">
    <div class="tlb-header-inner">
        <div>
            <div class="tlb-header-label-row">
                <div class="tlb-header-icon">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <span class="tlb-header-site">MediaTools</span>
            </div>
            <div class="tlb-header-badges">
                <span class="tlb-hbadge"><i class="fa-solid fa-palette"></i> Custom Warna</span>
                <span class="tlb-hbadge"><i class="fa-solid fa-image"></i> Logo Support</span>
                <span class="tlb-hbadge"><i class="fa-solid fa-download"></i> PNG HD</span>
                <span class="tlb-hbadge"><i class="fa-solid fa-infinity"></i> 100% Gratis</span>
            </div>
            <nav aria-label="Breadcrumb" class="flex justify-center mb-5">
                <ol class="flex items-center gap-2 text-xs" style="color:var(--text-3)">
                    <li><a href="{{ url('/') }}" style="color:var(--text-3);text-decoration:none;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text-3)'">Home</a></li>
                    <li style="margin:0 4px;font-size:9px;">›</li>
                    <li style="color:var(--accent);font-weight:600;">QR Code Generator Gratis — Buat QR Code Custom Bisnis Online</li>
                </ol>
            </nav>
            <h1 class="tlb-header-title">QR Code <span>Generator.</span></h1>
            <p class="tlb-header-sub">Buat QR Code custom untuk menu, pembayaran, kontak, WiFi & URL bisnis. Download PNG resolusi tinggi.</p>
        </div>
    </div>
</div>
<div class="tlb-header-curve"></div>

<div class="tlb-body">
{{-- ═══ ADS SLOT ═══ --}}
<div class="ads-slot-header no-print" style="margin-bottom:20px;">@include('components.ads.banner-header')</div>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/qr.css') }}">
<link rel="stylesheet" href="{{ asset('css/ads.css') }}">

<div class="min-h-screen bg-[#020d0d] text-white px-4 selection:bg-[#a3e635] selection:text-black">
    {{-- ═══ SLOT 1: HEADER BANNER 728×90 ═══ --}}

    <div class="max-w-7xl mx-auto">

        <main class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
            <!-- Left Section: Configuration -->
            <div class="lg:col-span-5 order-2 lg:order-1">
                <div class="glass-card p-8 rounded-[2.5rem] relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-6 opacity-10 pointer-events-none">
                        <i class="fa-solid fa-pen-nib text-6xl"></i>
                    </div>

                    <h2 class="text-sm font-black mb-10 uppercase tracking-[0.3em] text-white/40 flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-[#a3e635] animate-pulse"></span>
                        Configuration
                    </h2>

                    <div class="space-y-6">
                        <div class="form-group">
                            <label class="label-style">QR Links / Content</label>
                            <div class="relative group">
                                <input type="text" id="qr-content"
                                    value="{{ $lastQr->content ?? '' }}" 
                                    placeholder="https://yourwebsite.com" class="input-field pr-12">
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-white/20 group-focus-within:text-[#a3e635] transition-colors">
                                    <i class="fa-solid fa-link text-sm"></i>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-5">
                            <div class="form-group">
                                <label class="label-style">Main Color</label>
                                <div class="flex items-center gap-3 bg-white/5 p-2 rounded-xl border border-white/10">
                                    <input type="color" id="qr-color-dark" class="w-10 h-10 bg-transparent border-none cursor-pointer rounded-lg shadow-sm" value="#a3e635">
                                    <span class="text-[10px] font-mono text-gray-500 uppercase tracking-tighter">Solid</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="label-style">Backgrounds</label>
                                <div class="flex items-center gap-3 bg-white/5 p-2 rounded-xl border border-white/10">
                                    <input type="color" id="qr-color-light" class="w-10 h-10 bg-transparent border-none cursor-pointer rounded-lg shadow-sm" value="#ffffff">
                                    <span class="text-[10px] font-mono text-gray-500 uppercase tracking-tighter">BG Color</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="label-style">Module Visual Style</label>
                            <div class="grid grid-cols-3 gap-3">
                                <button data-type="dots" data-val="square" class="style-opt active">
                                    <i class="fa-solid fa-square-full mb-1 block"></i> Square
                                </button>
                                <button data-type="dots" data-val="dots" class="style-opt">
                                    <i class="fa-solid fa-circle mb-1 block"></i> Dots
                                </button>
                                <button data-type="dots" data-val="rounded" class="style-opt">
                                    <i class="fa-solid fa-shapes mb-1 block"></i> Rounded
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="label-style">Branding Logo (Upload)</label>
                            <div class="relative group">
                                <input type="file" accept="image/*" id="qr-logo-file" class="input-field pr-12 hidden">
                                <label for="qr-logo-file" class="input-field flex items-center justify-between cursor-pointer hover:border-[#a3e635]/50 transition-all">
                                    <span id="file-name" class="text-gray-500 truncate">Select your logo...</span>
                                    <i class="fa-solid fa-camera-retro text-white/20"></i>
                                </label>
                            </div>
                        </div>

                        <div class="flex flex-col gap-4 pt-4">
                            <button id="btn-download" class="btn-save shadow-[0_20px_40px_-10px_rgba(163,230,53,0.3)]">
                                <i class="fa-solid fa-file-export mr-2"></i> Download PNG
                            </button>
                            @auth
                                <button id="btn-sync" class="w-full py-4 rounded-2xl bg-white/5 border border-white/10 text-[11px] font-black uppercase tracking-widest hover:bg-white/10 transition-all">
                                    <i class="fa-solid fa-cloud-arrow-up mr-2 text-[#a3e635]"></i> Sync to Cloud
                                </button>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Section: Preview -->
            <div class="lg:col-span-7 order-1 lg:order-2">
                <div class="sticky top-28 space-y-8">
                    <div class="glass-card p-1 lg:p-14 rounded-[3rem] border-[#a3e635]/10 bg-gradient-to-br from-[#0a1a1a] to-[#020d0d] flex flex-col items-center justify-center min-h-[600px]">
                        
                        <div class="w-full flex items-center justify-between mb-10 px-8 lg:px-0">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full bg-[#a3e635] shadow-[0_0_10px_#a3e635]"></div>
                                <span class="text-[10px] font-black tracking-[0.4em] text-white/30 uppercase">Live Rendering</span>
                            </div>
                            <div class="flex gap-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-white/5 border border-white/10"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-white/5 border border-white/10"></div>
                            </div>
                        </div>

                        <div id="qr-preview-container" class="relative z-10 p-6 bg-white rounded-[2.5rem] shadow-[0_40px_100px_-20px_rgba(163,230,53,0.3)] transition-all duration-500 hover:scale-[1.05]">
                            <!-- QR Rendered Here -->
                        </div>

                        <div class="mt-16 text-center space-y-3">
                            <div class="inline-block px-4 py-1.5 bg-[#a3e635]/5 rounded-full border border-[#a3e635]/10">
                                <span class="text-[10px] font-black tracking-[0.4em] text-[#a3e635] uppercase">Scan to Verify</span>
                            </div>
                            <p class="text-sm text-gray-600 font-medium px-10">High-resolution QR ready for both digital and print usage.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Toast UI -->
    <div id="toast" class="toast-style flex items-center gap-4">
        <div class="h-10 w-10 bg-[#020d0d] rounded-full flex items-center justify-center border border-[#a3e635]/20">
            <i class="fa-solid fa-check text-[#a3e635]"></i>
        </div>
        <div class="flex flex-col">
            <span class="text-[10px] uppercase font-black tracking-widest text-black/40">Notification</span>
            <span id="toast-msg" class="text-sm font-bold leading-tight">Success!</span>
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

@push('scripts')
    <script type="text/javascript" src="https://unpkg.com/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>
    <script src="{{ asset('js/qr.js') }}"></script>
@endpush
@endsection

</div>{{-- /.tlb-body --}}
</div>{{-- /.tlb-page --}}
