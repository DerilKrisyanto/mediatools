@extends('layouts.app')

@section('title', 'FotoBox Online — Photo Booth Seru 6 Foto Gratis | MediaTools')
@section('meta_description', 'Photo booth online gratis langsung di browser! Ambil 6 foto otomatis dengan hitungan mundur, pilih dari 12 template keren & lucu, atur urutan foto, lalu download hasilnya. 100% gratis, privasi terjaga, tanpa upload ke server.')
@section('meta_keywords', 'fotobox online gratis, photo booth browser, selfie booth indonesia, foto strip template lucu, photo booth tanpa app, kamera selfie online, foto kawaii online, template foto lucu')
@section('og_image', 'fotobox')

@include('seo.fotobox')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Pacifico&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/fotobox.css') }}">
<link rel="stylesheet" href="{{ asset('css/ads.css') }}">
@endpush

@section('content')
<div id="fb-app">

{{-- ═══════════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════════ --}}
<div class="fb-page-hdr">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="fb-page-hdr__icon">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                    <div class="fb-page-hdr__badge">
                        <i class="fa-solid fa-circle" style="font-size:6px;animation:pulse-dot 2s infinite;"></i>
                        MediaTools
                    </div>
                </div>
                <h1>FotoBox Online 📸</h1>
                <p>Photo booth seru langsung di browsermu — gratis, tanpa instalasi, privasi terjaga.</p>
                <div class="fb-page-hdr__stats">
                    <span class="fb-page-hdr__stat"><i class="fa-solid fa-camera"></i> 6 Foto Otomatis</span>
                    <span class="fb-page-hdr__stat"><i class="fa-solid fa-palette"></i> 12 Template Pilihan</span>
                    <span class="fb-page-hdr__stat"><i class="fa-solid fa-shield-halved"></i> Privasi 100% Aman</span>
                    <span class="fb-page-hdr__stat"><i class="fa-solid fa-bolt"></i> Instan & Gratis</span>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="fb-page-hdr-curve"></div>

{{-- ═══════════════════════════════════════════
     MAIN LAYOUT
═══════════════════════════════════════════ --}}
<div class="max-w-7xl mx-auto px-6 pb-16" style="padding-top:24px;">
    <div class="ads-layout">

        {{-- ── Main Content ── --}}
        <div class="ads-layout__main">

            {{-- ════════════════════════════════
                 SCREEN 1 — LANDING
            ════════════════════════════════ --}}
            <div id="scr-land" class="fb-screen active">
                <div class="fb-land-card">
                    <div class="lb-orb lb-o1"></div>
                    <div class="lb-orb lb-o2"></div>
                    <div class="lb-orb lb-o3"></div>
                    <div class="lb-grid"></div>

                    <span class="fb-cam-ico">📸</span>
                    <h2 class="lb-title">Welcome!</h2>
                    <p class="lb-sub">
                        Serunya <em>photo booth</em> kini hadir langsung di browsermu!<br>
                        6 foto otomatis · 12 template keren · Download gratis ✨
                    </p>
                    <div class="lb-chips">
                        <span class="lb-chip">📷 6 Foto Otomatis</span>
                        <span class="lb-chip">🎨 12 Template Pilihan</span>
                        <span class="lb-chip">⚡ Instan & Gratis</span>
                        <span class="lb-chip">🔒 Privasi Terjaga</span>
                    </div>
                    <button class="btn-fb-start" onclick="FB.start()">
                        <i class="fa-solid fa-camera"></i>
                        Mulai Sesi Foto!
                    </button>
                    <p class="lb-note">
                        <span><i class="fa-solid fa-shield-halved" style="color:#ff6b9d;"></i> Foto tidak pernah dikirim ke server</span>
                        <span><i class="fa-solid fa-bolt" style="color:#c17ff5;"></i> Proses 100% di browser</span>
                        <span><i class="fa-solid fa-mobile-screen" style="color:#6bb5ff;"></i> Bisa di HP & laptop</span>
                    </p>
                </div>
            </div>

            {{-- ════════════════════════════════
                 SCREEN 2 — CAMERA
            ════════════════════════════════ --}}
            <div id="scr-cam" class="fb-screen">
                <div class="fb-dark-box">
                    <div class="cam-hdr">
                        <button class="btn-abort" onclick="FB.abort()">
                            <i class="fa-solid fa-arrow-left" style="font-size:10px;"></i> Keluar
                        </button>
                        <div class="prog-wrap"><div class="prog-fill" id="progFill"></div></div>
                        <span class="cap-lbl" id="capLbl">0 / 6</span>
                    </div>

                    <div class="vid-frame">
                        <video id="fbVid" playsinline muted autoplay></video>

                        {{-- Permission pending --}}
                        <div class="ov-base" id="ovPerm">
                            <span class="ov-ico">📷</span>
                            <h3>Izinkan Akses Kamera</h3>
                            <p>FotoBox butuh kameramu. Klik <strong style="color:#ff6b9d;">Allow / Izinkan</strong> di popup browser ya!</p>
                            <div class="spin-ring"></div>
                        </div>

                        {{-- Permission denied --}}
                        <div class="ov-base" id="ovDeny" style="display:none;">
                            <span class="ov-ico">🚫</span>
                            <h3 style="color:#ff6b9d;">Akses Kamera Ditolak</h3>
                            <p>Izinkan akses kamera di pengaturan browser, lalu refresh dan coba lagi.</p>
                            <button onclick="FB.abort()"
                                    style="margin-top:4px;padding:10px 26px;background:linear-gradient(135deg,#ff6b9d,#c17ff5);color:white;border:none;border-radius:99px;font-size:14px;font-weight:800;cursor:pointer;font-family:'Nunito',sans-serif;">
                                Kembali
                            </button>
                        </div>

                        {{-- Countdown --}}
                        <div class="ov-cd" id="ovCd">
                            <span class="cd-num" id="cdNum">3</span>
                        </div>

                        <div class="flash-fx" id="flashFx"></div>
                        <div class="save-pill" id="savePill">💾 Menyimpan...</div>
                        <div class="status-txt" id="statusTxt"></div>
                        <div class="shot-badge" id="shotBadge" style="display:none;">
                            <i class="fa-solid fa-camera" style="font-size:10px;"></i>
                            <span id="shotNum">0</span>/6
                        </div>
                    </div>

                    <div class="thumb-strip" id="thumbStrip"></div>
                </div>
            </div>

            {{-- ════════════════════════════════
                 SCREEN 3 — TEMPLATE SELECT
            ════════════════════════════════ --}}
            <div id="scr-tpl" class="fb-screen">
                <div class="fb-dark-box">
                    <div class="fb-sec-hdr">
                        <div class="fb-tag">🎨 Pilih Template</div>
                        <h2 class="fb-ttl">Mau tampilan seperti apa?</h2>
                        <p class="fb-sub">Pilih dari 12 template keren — atur foto di langkah berikutnya ✨</p>
                    </div>

                    <div class="tpl-grid" id="tplGrid"></div>

                    <div style="text-align:center;">
                        <button class="btn-next" id="btnArr" onclick="FB.goArrange()">
                            Atur Foto <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════
                 SCREEN 4 — ARRANGE
            ════════════════════════════════ --}}
            <div id="scr-arr" class="fb-screen">
                <div class="fb-dark-box">
                    <div class="fb-sec-hdr" style="margin-bottom:16px;">
                        <div class="fb-tag">✋ Atur Foto</div>
                        <h2 class="fb-ttl">Pilih & susun fotomu!</h2>
                    </div>

                    <div class="arr-tip" id="arrTip">
                        Klik <strong style="color:#ff6b9d;">nomor slot</strong> di preview → lalu klik <strong style="color:#c17ff5;">foto yang kamu mau</strong> 👆
                    </div>

                    <div class="arr-grid">
                        <div class="arr-panel">
                            <div class="arr-panel-hdr">
                                <i class="fa-solid fa-layer-group" style="font-size:10px;"></i> Preview Template
                            </div>
                            <div class="arr-prev-wrap" id="arrWrap">
                                <canvas id="arrCvs"></canvas>
                            </div>
                        </div>
                        <div class="arr-panel">
                            <div class="arr-panel-hdr">
                                <i class="fa-solid fa-images" style="font-size:10px;"></i> 6 Foto Kamu
                            </div>
                            <div class="pick-grid" id="pickGrid"></div>
                        </div>
                    </div>

                    <div class="arr-btn-row">
                        <button class="btn-next rdy" onclick="FB.goTemplates()">
                            <i class="fa-solid fa-arrow-left"></i> Ganti Template
                        </button>
                        <button class="btn-next rdy" onclick="FB.renderResult()">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Buat Foto!
                        </button>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════
                 SCREEN 5 — RESULT
            ════════════════════════════════ --}}
            <div id="scr-res" class="fb-screen">
                <div class="fb-dark-box">
                    <div class="fb-sec-hdr" style="margin-bottom:24px;">
                        <div class="fb-tag">🎉 Foto Siap!</div>
                        <h2 class="fb-ttl">Wow, kereeeen! 🌟</h2>
                        <p class="fb-sub">Fotomu sudah siap — download dan bagikan ke teman-teman!</p>
                    </div>

                    <div class="res-frame">
                        <canvas id="resCvs"></canvas>
                    </div>

                    <div class="res-btns">
                        <a id="dlBtn" class="btn-dl" download="fotobox-mediatools.jpg">
                            <i class="fa-solid fa-download"></i> Download Foto
                        </a>
                        <button class="btn-sec" onclick="FB.reset()">
                            <i class="fa-solid fa-rotate-right"></i> Mulai Ulang
                        </button>
                    </div>

                    <p class="res-share">
                        📸 Jangan lupa tag <strong style="color:#ff6b9d;">@mediatoolsid</strong> kalau share ke Instagram!
                    </p>
                </div>
            </div>

        </div>{{-- /ads-layout__main --}}

    </div>{{-- /ads-layout --}}
</div>{{-- /max-w-7xl --}}

</div>{{-- /fb-app --}}

{{-- Render overlay --}}
<div class="render-ov" id="renderOv">
    <div class="render-spin"></div>
    <p class="render-lbl">Sedang merender foto...</p>
    <p class="render-sub">Tunggu sebentar ya ✨</p>
</div>

{{-- Hidden capture canvas --}}
<canvas id="capCvs" style="display:none;"></canvas>

@endsection

@push('scripts')
<script src="{{ asset('js/fotobox.js') }}"></script>
@endpush