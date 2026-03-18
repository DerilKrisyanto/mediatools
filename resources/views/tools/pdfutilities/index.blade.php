@extends('layouts.app')
@section('title', 'PDF Utilities - Merge, Split & Compress PDF Gratis | MediaTools')
@section('meta_description', 'Gabungkan, pisahkan, dan kompres file PDF secara gratis langsung di browser. Tanpa upload ke server, privasi terjaga 100%.')
@section('content')

<link rel="stylesheet" href="{{ asset('css/pdfutilities.css') }}">

<div class="pdf-page selection:bg-[#a3e635] selection:text-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">

        {{-- ======= HEADER ======= --}}
        <header class="pdf-header">
            <div class="pdf-header-left">
                <div class="flex items-center gap-3 mb-2">
                    <span class="pdf-pro-badge">Pro Tool</span>
                    <h1 class="pdf-title">
                        PDF <span class="pdf-title-accent">TOOLKIT.</span>
                    </h1>
                </div>
                <p class="pdf-subtitle">Merge, split by range, or compress — instantly, no install, shared-hosting safe.</p>
            </div>

            <div class="pdf-header-right">
                <div class="pdf-stats-pill">
                    <span class="pdf-stats-dot"></span>
                    <span class="pdf-stats-label">3 Tools</span>
                    <span class="pdf-stats-divider">·</span>
                    <span class="pdf-stats-label">100% Browser-Based</span>
                </div>
                <a href="{{ route('tools.pdfutilities') }}" class="pdf-back-btn">
                    <i class="fa-solid fa-rotate-left text-[10px]"></i>
                    <span>Reset</span>
                </a>
            </div>
        </header>

        {{-- ======= MAIN GRID ======= --}}
        <main class="pdf-main-grid">

            {{-- ===== LEFT PANEL: CONTROLS ===== --}}
            <div class="pdf-panel-left">
                <div class="pdf-glass-card">

                    <div class="pdf-card-glow" aria-hidden="true"></div>

                    <div class="pdf-section-tag">
                        <span class="pdf-section-dot"></span>
                        Configuration
                    </div>

                    {{-- STEP 1: Feature Select --}}
                    <div class="pdf-step" id="step-feature">
                        <label class="pdf-label">01 — Select Operation</label>
                        <div class="pdf-feature-grid">
                            <button class="pdf-feat-btn" data-feature="merge" title="Merge multiple PDFs into one">
                                <div class="pdf-feat-icon">
                                    <i class="fa-solid fa-layer-group"></i>
                                </div>
                                <span class="pdf-feat-name">Merge</span>
                                <span class="pdf-feat-hint">Combine files</span>
                            </button>
                            <button class="pdf-feat-btn" data-feature="split" title="Extract specific page ranges">
                                <div class="pdf-feat-icon">
                                    <i class="fa-solid fa-scissors"></i>
                                </div>
                                <span class="pdf-feat-name">Split</span>
                                <span class="pdf-feat-hint">By page range</span>
                            </button>
                            <button class="pdf-feat-btn" data-feature="compress" title="Reduce PDF file size">
                                <div class="pdf-feat-icon">
                                    <i class="fa-solid fa-file-zipper"></i>
                                </div>
                                <span class="pdf-feat-name">Compress</span>
                                <span class="pdf-feat-hint">Reduce size</span>
                            </button>
                        </div>
                        <input type="hidden" id="selected-feature">
                    </div>

                    {{-- STEP 2: Upload --}}
                    <div class="pdf-step pdf-step-hidden" id="step-upload">
                        <label class="pdf-label" id="upload-label">02 — Upload PDF Files</label>
                        <div class="pdf-drop-zone" id="drop-zone">
                            <input type="file" id="pdf-files" accept=".pdf" class="pdf-file-input">
                            <div class="pdf-drop-inner" id="drop-placeholder">
                                <div class="pdf-drop-icon">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <p class="pdf-drop-title">Drop PDFs here</p>
                                <p class="pdf-drop-hint" id="file-limit-hint">or click to browse · max 10 files · 20 MB total</p>
                            </div>
                        </div>
                        <div id="file-list" class="pdf-file-list"></div>
                    </div>

                    {{-- STEP 3: Split Range --}}
                    <div class="pdf-step pdf-step-hidden" id="step-range">
                        <label class="pdf-label">03 — Page Range <span class="pdf-label-hint">(split only)</span></label>
                        <div class="pdf-range-row">
                            <div class="pdf-range-field">
                                <span class="pdf-range-prefix">From</span>
                                <input type="number" id="range-from" class="pdf-range-input" placeholder="1" min="1" value="1">
                            </div>
                            <div class="pdf-range-sep">→</div>
                            <div class="pdf-range-field">
                                <span class="pdf-range-prefix">To</span>
                                <input type="number" id="range-to" class="pdf-range-input" placeholder="5" min="1" value="5">
                            </div>
                        </div>
                        <p class="pdf-range-info">
                            <i class="fa-solid fa-circle-info text-[9px]"></i>
                            Pages outside the total count will be clamped automatically.
                        </p>
                    </div>

                    {{-- STEP 4: Process Button --}}
                    <div class="pdf-step pdf-step-hidden" id="step-process">
                        <button type="button" id="btn-process" class="pdf-btn-primary">
                            <i class="fa-solid fa-bolt"></i>
                            <span id="btn-process-label">Process Now</span>
                        </button>
                    </div>

                </div>
            </div>

            {{-- ===== RIGHT PANEL: PREVIEW / STATUS ===== --}}
            <div class="pdf-panel-right">
                <div class="pdf-preview-wrap">

                    {{-- Empty State --}}
                    <div class="pdf-empty-state" id="state-empty">
                        <div class="pdf-empty-icon">
                            <i class="fa-regular fa-file-pdf"></i>
                        </div>
                        <p class="pdf-empty-title">Nothing here yet.</p>
                        <p class="pdf-empty-sub">Select an operation on the left to get started.</p>
                        <div class="pdf-feature-tags">
                            <span class="pdf-tag"><i class="fa-solid fa-layer-group text-[10px]"></i> Merge</span>
                            <span class="pdf-tag"><i class="fa-solid fa-scissors text-[10px]"></i> Split</span>
                            <span class="pdf-tag"><i class="fa-solid fa-file-zipper text-[10px]"></i> Compress</span>
                        </div>
                    </div>

                    {{-- Active State --}}
                    <div class="pdf-active-state pdf-step-hidden" id="state-active">
                        <div class="pdf-active-icon-wrap" id="active-icon-wrap">
                            <i class="fa-solid fa-layer-group" id="active-icon"></i>
                        </div>
                        <p class="pdf-active-label" id="active-label">Merge PDF</p>
                        <p class="pdf-active-desc" id="active-desc">Upload multiple PDF files below. Drag to reorder before merging.</p>

                        <div class="pdf-info-grid" id="info-grid">
                            <div class="pdf-info-block">
                                <span class="pdf-info-num" id="info-file-count">0</span>
                                <span class="pdf-info-key">Files</span>
                            </div>
                            <div class="pdf-info-block">
                                <span class="pdf-info-num" id="info-total-size">0 KB</span>
                                <span class="pdf-info-key">Total Size</span>
                            </div>
                            <div class="pdf-info-block">
                                <span class="pdf-info-num" id="info-status">Ready</span>
                                <span class="pdf-info-key">Status</span>
                            </div>
                        </div>

                        <div class="pdf-tip-box" id="tip-box">
                            <i class="fa-solid fa-lightbulb text-[#a3e635] text-xs"></i>
                            <span id="tip-text">You can drag files to reorder them before merging.</span>
                        </div>
                    </div>

                    {{-- Processing State --}}
                    <div class="pdf-processing-state pdf-step-hidden" id="state-processing">
                        <div class="pdf-spinner-ring">
                            <div class="pdf-spinner-inner"></div>
                        </div>
                        <p class="pdf-proc-title">Processing…</p>
                        <p class="pdf-proc-sub">Please don't close this tab.</p>
                    </div>

                    {{-- Result State --}}
                    <div class="pdf-result-state pdf-step-hidden" id="state-result">
                        <div class="pdf-result-checkmark">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <p class="pdf-result-title">Done!</p>
                        <p class="pdf-result-sub">Your file is ready to download.</p>
                        <div class="pdf-result-meta" id="result-meta">
                            <span class="pdf-result-badge" id="result-badge-feature">Merge</span>
                        </div>
                        <div class="pdf-result-actions">
                            <a href="#" id="btn-download" class="pdf-btn-download" target="_blank">
                                <i class="fa-solid fa-download"></i>
                                <span>Download Result</span>
                            </a>
                            <button type="button" id="btn-reset" class="pdf-btn-reset">
                                <i class="fa-solid fa-rotate-left"></i>
                                <span>Start Over</span>
                            </button>
                        </div>
                        <p class="pdf-result-note">
                            <i class="fa-solid fa-clock text-[9px]"></i>
                            File auto-deleted after 15 seconds from download
                        </p>
                    </div>

                </div>
            </div>

        </main>
    </div>

    {{-- ======= TOAST ======= --}}
    <div id="pdf-toast" class="pdf-toast" role="alert" aria-live="polite">
        <div class="pdf-toast-icon" id="toast-icon">
            <i class="fa-solid fa-check" id="toast-ico"></i>
        </div>
        <div class="pdf-toast-body">
            <span class="pdf-toast-type" id="toast-type">Success</span>
            <span class="pdf-toast-msg" id="toast-msg">Operation complete.</span>
        </div>
    </div>

</div>

@push('scripts')
<script src="{{ asset('js/pdfutilities.js') }}"></script>
@endpush

@endsection