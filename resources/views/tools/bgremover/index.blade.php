@extends('layouts.app')

@section('title', 'Hapus Background Foto Online Gratis — AI Otomatis | MediaTools')
@section('meta_description', 'Hapus background foto secara otomatis dengan AI. Unggul pada rambut & detail halus. Edit manual dengan brush, download PNG transparan gratis tanpa daftar.')
@section('meta_keywords', 'hapus background foto gratis, remove background online, background remover indonesia, hapus latar foto ai, remove bg gratis')

@section('content')
<link rel="stylesheet" href="{{ asset('css/bgremover.css') }}">

<div class="bgr-page">

    {{-- ══ HEADER ══ --}}
    <div class="bgr-header">
        <div class="bgr-header-inner">
            <div class="bgr-badge-row">
                <span class="bgr-badge"><i class="fa-solid fa-brain"></i> BiRefNet AI</span>
                <span class="bgr-badge"><i class="fa-solid fa-wand-magic-sparkles"></i> Alpha Matting</span>
                <span class="bgr-badge"><i class="fa-solid fa-paintbrush"></i> Manual Brush</span>
                <span class="bgr-badge"><i class="fa-solid fa-infinity"></i> Gratis</span>
            </div>
            <h1 class="bgr-title">Background <span class="bgr-accent">Remover.</span></h1>
            <p class="bgr-subtitle">
                AI BiRefNet hapus background otomatis, unggul pada rambut &amp; detail halus.
                Poles hasilnya dengan brush interaktif sebelum download.
            </p>
        </div>
    </div>

    {{-- ══ VIEW: UPLOAD ══ --}}
    <div id="viewUpload" class="bgr-upload-view">
        <div class="bgr-upload-wrap">

            {{-- Dropzone --}}
            <div class="bgr-dropzone" id="dropzone">
                <input type="file" id="fileInput"
                       accept="image/jpeg,image/jpg,image/png,image/webp"
                       multiple hidden>
                <div class="bgr-dz-body">
                    <div class="bgr-dz-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                    <h3 class="bgr-dz-title">Drag & drop gambar ke sini</h3>
                    <p class="bgr-dz-sub">atau</p>
                    <button class="bgr-btn-browse" id="btnBrowse" type="button">
                        <i class="fa-solid fa-folder-open"></i> Pilih File
                    </button>
                    <p class="bgr-dz-formats">JPG · PNG · WEBP &nbsp;·&nbsp; Maks. 20 MB · Ctrl+V untuk paste</p>
                </div>
            </div>

            {{-- Options panel --}}
            <div class="bgr-options-panel">

                <div class="bgr-mode-pills">
                    <div class="bgr-mode-pill">
                        <i class="fa-solid fa-image"></i>
                        <div>
                            <strong>1 gambar</strong>
                            <span>Before/after + Editor brush</span>
                        </div>
                    </div>
                    <div class="bgr-mode-pill">
                        <i class="fa-solid fa-images"></i>
                        <div>
                            <strong>2+ gambar</strong>
                            <span>Batch AI + Edit tiap gambar</span>
                        </div>
                    </div>
                </div>

                <div class="bgr-opt-group">
                    <label class="bgr-opt-label"><i class="fa-solid fa-sliders"></i> Kualitas AI</label>
                    <div class="bgr-quality" id="qualityBtns">
                        <button class="bgr-q-btn active" data-q="fast" type="button">
                            <i class="fa-solid fa-bolt"></i>
                            <span>Cepat</span>
                            <small>~5–10 dtk</small>
                        </button>
                        <button class="bgr-q-btn" data-q="high" type="button">
                            <i class="fa-solid fa-gem"></i>
                            <span>HD</span>
                            <small>~10–20 dtk</small>
                        </button>
                    </div>
                </div>

                <div class="bgr-opt-group">
                    <label class="bgr-opt-label"><i class="fa-solid fa-palette"></i> Background Download</label>
                    <div class="bgr-swatches" id="bgSwatches">
                        <button class="bgr-swatch active" data-bg="transparent" title="Transparan" type="button">
                            <span class="bgr-sw-inner bgr-sw-transparent"></span>
                        </button>
                        <button class="bgr-swatch" data-bg="#ffffff" title="Putih" type="button">
                            <span class="bgr-sw-inner" style="background:#fff;border:1px solid #333"></span>
                        </button>
                        <button class="bgr-swatch" data-bg="#000000" title="Hitam" type="button">
                            <span class="bgr-sw-inner" style="background:#000"></span>
                        </button>
                        <button class="bgr-swatch" data-bg="#a3e635" title="Lime" type="button">
                            <span class="bgr-sw-inner" style="background:#a3e635"></span>
                        </button>
                        <button class="bgr-swatch" data-bg="#3b82f6" title="Biru" type="button">
                            <span class="bgr-sw-inner" style="background:#3b82f6"></span>
                        </button>
                        <button class="bgr-swatch" data-bg="#ef4444" title="Merah" type="button">
                            <span class="bgr-sw-inner" style="background:#ef4444"></span>
                        </button>
                        <label class="bgr-swatch bgr-swatch-custom" title="Warna kustom">
                            <i class="fa-solid fa-eye-dropper"></i>
                            <input type="color" id="customColor" value="#6366f1">
                        </label>
                    </div>
                </div>

                <div class="bgr-opt-group">
                    <label class="bgr-opt-label"><i class="fa-solid fa-file-image"></i> Format Output</label>
                    <div class="bgr-format" id="formatBtns">
                        <button class="bgr-f-btn active" data-fmt="png" type="button">PNG (Transparan)</button>
                        <button class="bgr-f-btn"        data-fmt="jpg" type="button">JPG + BG Warna</button>
                    </div>
                </div>

                <p class="bgr-cache-note">
                    <i class="fa-solid fa-server"></i>
                    Gambar diproses di server menggunakan AI BiRefNet &amp; Alpha Matting.
                    File dihapus otomatis setelah proses selesai.
                </p>

            </div>
        </div>
    </div>{{-- /#viewUpload --}}

    {{-- ══ VIEW: PROCESSING ══ --}}
    <div id="viewProcessing" class="bgr-processing-view" style="display:none">
        <div class="bgr-processing-card">
            <div class="bgr-processing-thumb-wrap">
                <img id="processingThumb" alt="" class="bgr-processing-thumb">
                <div class="bgr-processing-spinner-ring"></div>
            </div>
            <h3 class="bgr-processing-title">Memproses Gambar…</h3>
            <p id="progressLabel" class="bgr-processing-label">Mempersiapkan…</p>
            <div class="bgr-processing-bar-wrap">
                <div class="bgr-processing-bar">
                    <div class="bgr-processing-fill" id="progressFill" style="width:0%"></div>
                </div>
                <span id="progressPct" class="bgr-processing-pct">0%</span>
            </div>
            <p class="bgr-processing-hint">
                <i class="fa-solid fa-shield-halved"></i>
                File dihapus otomatis dari server setelah selesai diproses
            </p>
        </div>
    </div>

    {{-- ══ VIEW: RESULT (before / after comparison) ══ --}}
    <div id="viewResult" class="bgr-result-view-wrap" style="display:none">
        <div class="bgr-result-view">

            {{-- Result header --}}
            <div class="bgr-result-header">
                <div class="bgr-result-filename-wrap">
                    <i class="fa-solid fa-circle-check" style="color:var(--accent)"></i>
                    <span class="bgr-result-filename" id="resultFilename">—</span>
                </div>
                <div class="bgr-result-hdr-actions">
                    <button class="bgr-btn-result-edit"    id="btnResultEdit"        type="button">
                        <i class="fa-solid fa-paintbrush"></i>
                        <span>Edit Manual</span>
                    </button>
                    <button class="bgr-btn-dl-png"         id="btnResultDownloadPNG" type="button">
                        <i class="fa-solid fa-download"></i>
                        <span>PNG</span>
                    </button>
                    <button class="bgr-btn-dl-jpg"         id="btnResultDownloadJPG" type="button">
                        <i class="fa-solid fa-download"></i>
                        <span>JPG</span>
                    </button>
                    <button class="bgr-btn-back"           id="btnResultNew"          type="button">
                        <i class="fa-solid fa-plus"></i>
                        <span class="bgr-hide-sm">Gambar Baru</span>
                    </button>
                </div>
            </div>

            {{-- Before / After comparison slider --}}
            <div class="bgr-compare-outer">
                <div class="bgr-compare-wrap" id="compareWrap">

                    {{-- BEFORE pane --}}
                    <div class="bgr-compare-before" id="compareBefore">
                        <img id="compareOrigImg" alt="Original" draggable="false">
                        <div class="bgr-compare-label bgr-compare-label-l">
                            <i class="fa-solid fa-image"></i> Sebelum
                        </div>
                    </div>

                    {{-- AFTER pane (clip-path updated by JS) --}}
                    <div class="bgr-compare-after" id="compareAfter">
                        <img id="compareResultImg" alt="Hasil" draggable="false">
                        <div class="bgr-compare-label bgr-compare-label-r">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Sesudah
                        </div>
                    </div>

                    {{-- Draggable handle --}}
                    <div class="bgr-compare-handle" id="compareHandle">
                        <div class="bgr-compare-handle-line"></div>
                        <div class="bgr-compare-handle-btn">
                            <i class="fa-solid fa-left-right"></i>
                        </div>
                    </div>

                </div>
                <p class="bgr-compare-hint">
                    <i class="fa-solid fa-hand-pointer"></i>
                    Geser untuk membandingkan sebelum &amp; sesudah
                </p>
            </div>

        </div>
    </div>{{-- /#viewResult --}}

    {{-- ══ VIEW: EDITOR (brush) ══ --}}
    <div id="viewEditor" class="bgr-editor-view" style="display:none">

        {{-- Toolbar --}}
        <div class="bgr-toolbar" id="editorToolbar">

            <div class="bgr-tb-group">
                <button class="bgr-tool-btn" id="btnEditorBack" type="button" title="Kembali ke hasil">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span class="bgr-hide-sm">Kembali</span>
                </button>
            </div>

            <div class="bgr-tb-sep"></div>

            <div class="bgr-tb-group">
                <button class="bgr-tool-btn active" data-tool="remove" id="btnRemoveArea" type="button">
                    <i class="fa-solid fa-eraser"></i>
                    <span>Hapus Area</span>
                </button>
                <button class="bgr-tool-btn" data-tool="restore" id="btnRestoreArea" type="button">
                    <i class="fa-solid fa-paintbrush"></i>
                    <span>Pulihkan Area</span>
                </button>
            </div>

            <div class="bgr-tb-sep"></div>

            <div class="bgr-tb-group bgr-tb-brush">
                <i class="fa-solid fa-circle-dot bgr-tb-icon"></i>
                <input type="range" id="brushSizeSlider" min="3" max="150" value="30" class="bgr-brush-slider">
                <span id="brushSizeVal" class="bgr-brush-val">30px</span>
            </div>

            <div class="bgr-tb-sep bgr-tb-sep-md"></div>

            <div class="bgr-tb-group">
                <button class="bgr-tool-btn" id="btnUndo" type="button" disabled title="Undo (Ctrl+Z)">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span class="bgr-hide-sm">Undo</span>
                </button>
                <button class="bgr-tool-btn" id="btnRedo" type="button" disabled title="Redo (Ctrl+Y)">
                    <i class="fa-solid fa-rotate-right"></i>
                    <span class="bgr-hide-sm">Redo</span>
                </button>
                <button class="bgr-tool-btn bgr-btn-reset" id="btnEditReset" type="button">
                    <i class="fa-solid fa-arrow-rotate-left"></i>
                    <span class="bgr-hide-sm">Reset AI</span>
                </button>
            </div>

            <div class="bgr-tb-spacer"></div>

            <div class="bgr-tb-group">
                <button class="bgr-btn-dl-png" id="btnDownloadPNG" type="button">
                    <i class="fa-solid fa-download"></i>
                    <span>PNG</span>
                </button>
                <button class="bgr-btn-dl-jpg" id="btnDownloadJPG" type="button">
                    <i class="fa-solid fa-download"></i>
                    <span>JPG</span>
                </button>
            </div>

        </div>

        {{-- Canvas area --}}
        <div class="bgr-canvas-area">

            <div class="bgr-canvas-panel">
                <div class="bgr-panel-hdr">
                    <span class="bgr-panel-lbl">
                        <i class="fa-solid fa-image"></i> Original
                    </span>
                </div>
                <div class="bgr-canvas-frame bgr-frame-orig">
                    <canvas id="origCanvas"></canvas>
                </div>
            </div>

            <div class="bgr-canvas-panel">
                <div class="bgr-panel-hdr">
                    <span class="bgr-panel-lbl">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Hasil Edit
                    </span>
                    <span class="bgr-panel-hint">
                        <i class="fa-solid fa-hand-pointer"></i> Paint untuk edit
                    </span>
                </div>
                <div class="bgr-canvas-frame">
                    <div class="bgr-canvas-stack" id="canvasWrapper">
                        <canvas id="displayCanvas"></canvas>
                        <canvas id="overlayCanvas" class="bgr-overlay-canvas"></canvas>
                    </div>
                </div>
            </div>

        </div>

    </div>{{-- /#viewEditor --}}

    {{-- ══ VIEW: MULTI (batch results) ══ --}}
    <div id="viewMulti" class="bgr-multi-view" style="display:none">

        <div class="bgr-multi-header">
            <div class="bgr-multi-info">
                <i class="fa-solid fa-circle-info"></i>
                Mode batch — klik <strong>Edit</strong> pada kartu untuk brush editor.
            </div>
            <div class="bgr-multi-actions">
                <button class="bgr-btn-secondary" id="btnClearAll" type="button">
                    <i class="fa-solid fa-trash"></i> Hapus Semua
                </button>
                <button class="bgr-btn-secondary" id="btnAddMore" type="button">
                    <i class="fa-solid fa-plus"></i> Tambah
                </button>
                <button class="bgr-btn-zip" id="btnDownloadZip" type="button">
                    <i class="fa-solid fa-file-zipper"></i> Download ZIP
                </button>
            </div>
        </div>

        <div class="bgr-results" id="multiGrid"></div>

    </div>{{-- /#viewMulti --}}

    {{-- ══ HOW IT WORKS ══ --}}
    <div class="bgr-howto">
        <div class="bgr-howto-grid">
            <div class="bgr-howto-item">
                <div class="bgr-howto-num">1</div>
                <h4>Upload Gambar</h4>
                <p>Drag, klik, atau paste (Ctrl+V). Satu gambar aktifkan before/after + editor; banyak gambar masuk batch.</p>
            </div>
            <div class="bgr-howto-item">
                <div class="bgr-howto-num">2</div>
                <h4>AI Hapus Background</h4>
                <p>Server memproses dengan BiRefNet + Alpha Matting untuk detail rambut yang presisi.</p>
            </div>
            <div class="bgr-howto-item">
                <div class="bgr-howto-num">3</div>
                <h4>Bandingkan & Edit</h4>
                <p>Geser slider before/after untuk memeriksa hasil. Klik Edit Manual jika perlu poles dengan brush.</p>
            </div>
            <div class="bgr-howto-item">
                <div class="bgr-howto-num">4</div>
                <h4>Download</h4>
                <p>Unduh PNG transparan atau JPG dengan background warna pilihan Anda.</p>
            </div>
        </div>
    </div>

</div>{{-- /.bgr-page --}}

{{-- Toast --}}
<div id="bgrToast" class="bgr-toast">
    <i class="fa-solid fa-circle-check"></i>
    <span id="bgrToastMsg"></span>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
@vite(['resources/js/bgremover/index.js'])
@endpush