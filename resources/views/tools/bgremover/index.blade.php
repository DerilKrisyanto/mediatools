@extends('layouts.app')
@section('title', 'Background Remover - Hapus Background Foto Otomatis | MediaTools')
@section('meta_description', 'Hapus background foto secara otomatis dan instan. Cocok untuk foto produk, profil, dan konten media sosial. Gratis, tanpa instalasi.')
@section('content')

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/bgremover.css') }}">

<main class="tool-wrapper">
    <div class="container">
        <!-- Header Section -->
        <div class="tool-header">
            <h1 class="tool-title">Remove Background <span class="text-accent">Instantly</span></h1>
            <p class="tool-subtitle">
                Upload your image and let our AI remove the background in seconds.
                Fast, accurate, and high quality.
            </p>
        </div>

        <!-- Mode Switcher -->
        <div class="mode-container">
            <div class="mode-switch">
                <button class="mode-btn active" data-mode="single">
                    <i class="fa-solid fa-image"></i>
                    <span>Single Image</span>
                </button>
                <button class="mode-btn" data-mode="multi">
                    <i class="fa-solid fa-images"></i>
                    <span>Multiple Images</span>
                </button>
            </div>
        </div>

        <!-- Main Workspace -->
        <div class="workspace-card">
            <!-- IDLE STATE -->
            <div class="state state-idle active">
                <div class="dropzone" id="dropzone">
                    <div class="dropzone-content">
                        <div class="icon-circle">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <h3>Drop your image here</h3>
                        <p>Supports JPG, PNG, JPEG (Max 10MB)</p>
                        <div class="button-group">
                            <button class="btn-primary-neon">
                                <i class="fa-solid fa-upload"></i>Browse File
                            </button>
                        </div>
                        <input type="file" id="fileInput" accept="image/png, image/jpeg, image/jpg" hidden>
                    </div>
                </div>
            </div>

            <!-- PREVIEW STATE -->
            <div class="state state-preview">
                <div class="preview-container">
                    <div class="preview-info">
                        <span id="fileName" class="status-badge"></span>
                    </div>
                    
                    <div id="previewList" class="preview-grid">
                        <!-- Dynamic Content -->
                    </div>

                    <div class="button-group">
                        <button class="btn-outline" id="changeImage">
                            <i class="fa-solid fa-rotate-left"></i> Change
                        </button>
                        <button class="btn-primary-neon" id="removeBg">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Remove Background
                        </button>
                    </div>
                </div>
            </div>

            <!-- PROCESSING STATE -->
            <div class="state state-processing">
                <div class="processing-content">
                    <div class="ai-loader-container">
                        <div class="ai-loader"></div>
                        <div class="ai-pulse"></div>
                    </div>
                    <h3>AI is working</h3>
                    <p>Detecting subject and removing background...</p>
                </div>
            </div>

            <!-- RESULT STATE -->
            <div class="state state-result">
                <div class="result-header">
                    <div class="success-icon">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <h2>Background Removed!</h2>
                </div>

                <div id="resultList" class="result-scroll-area">
                    <!-- Dynamic Result Rows -->
                </div>

                <div class="button-group">
                    <button class="btn-outline" id="processAnother">
                        <i class="fa-solid fa-plus"></i> Process Another
                    </button>
                    <button class="btn-primary-neon" id="downloadBtn">
                        <i class="fa-solid fa-download"></i> Download All
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Tool Tips -->
        <div class="tool-footer-info">
            <div class="info-item">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Privacy focused: Images are deleted after 1 hour.</span>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script src="{{ asset('js/bgremover.js') }}"></script>
@endpush