'use strict';

document.addEventListener('DOMContentLoaded', function () {

    /* =========================================================
       COBALT INSTANCE CONFIG
       Diambil dari window variable yang di-set di blade
    ========================================================= */

    /* =========================================================
       PLATFORM CONFIG
    ========================================================= */
    const PLATFORM_CONFIG = {
        youtube: {
            hint:        'YouTube · Shorts · Music',
            placeholder: 'https://www.youtube.com/watch?v=...',
            examples:    ['youtube.com/watch?v=...', 'youtu.be/...', 'youtube.com/shorts/...'],
            icon:        'fa-youtube',
            patterns:    [/youtube\.com/, /youtu\.be/],
        },
        tiktok: {
            hint:        'TikTok · Video · Audio',
            placeholder: 'https://www.tiktok.com/@user/video/...',
            examples:    ['tiktok.com/@user/video/...', 'vm.tiktok.com/...'],
            icon:        'fa-tiktok',
            patterns:    [/tiktok\.com/, /vm\.tiktok/],
        },
        instagram: {
            hint:        'Reels · Foto · Video',
            placeholder: 'https://www.instagram.com/reel/...',
            examples:    ['instagram.com/reel/...', 'instagram.com/p/...'],
            icon:        'fa-instagram',
            patterns:    [/instagram\.com/],
        },
        other: {
            hint:        'Twitter · Reddit · Pinterest · dll',
            placeholder: 'https://twitter.com/...',
            examples:    ['twitter.com/...', 'reddit.com/...', 'pinterest.com/...'],
            icon:        'fa-globe',
            patterns:    [],
        },
    };

    const PLATFORM_TIPS = {
        youtube: [
            'Pastikan video bersifat publik (bukan private atau unlisted)',
            'Untuk audio, pilih format "MP3 Audio" sebelum klik download',
            'YouTube Shorts dan YouTube Music juga didukung',
        ],
        tiktok: [
            'Pastikan akun TikTok bersifat publik',
            'Aktifkan opsi "Tanpa Watermark" untuk hasil yang bersih',
            'Link harus dari postingan spesifik, bukan halaman profil',
        ],
        instagram: [
            'Hanya konten dari akun publik yang dapat didownload',
            'Salin link langsung dari browser, bukan dari aplikasi',
            'Instagram Reels dan foto carousel didukung',
        ],
        other: [
            'Pastikan konten bersifat publik dan dapat diakses umum',
            'Gunakan link langsung dari browser desktop untuk hasil terbaik',
            'Beberapa platform mungkin tidak selalu berhasil',
        ],
    };

    const ERROR_MAP = {
        'error.api.link.invalid':        'URL tidak valid. Pastikan link benar.',
        'error.api.link.unsupported':    'Platform ini belum didukung.',
        'error.api.content.unavailable': 'Konten tidak tersedia atau telah dihapus.',
        'error.api.content.private':     'Konten ini bersifat privat.',
        'error.api.rate_exceeded':       'Terlalu banyak permintaan. Coba lagi sebentar.',
        'error.api.youtube.decipher':    'YouTube memblokir sementara. Coba lagi.',
        'error.api.youtube.login':       'Konten ini memerlukan login YouTube.',
        'error.api.youtube.age':         'Konten ini dibatasi usia.',
        'error.api.fetch.fail':          'Gagal mengambil data. Coba lagi.',
        'error.api.auth.jwt.invalid':    'Token verifikasi tidak valid. Segarkan halaman.',
        'error.api.auth.turnstile':      'Verifikasi Turnstile gagal. Segarkan halaman dan coba lagi.',
    };

    /* =========================================================
       STATE
    ========================================================= */
    let currentPlatform  = 'youtube';
    let currentFormat    = 'mp4';
    let currentQuality   = '720';
    let progressTimer    = null;

    /* =========================================================
       DOM REFS
    ========================================================= */
    const platformBtns      = document.querySelectorAll('.md-platform-btn');
    const platformHint      = document.getElementById('platform-hint');
    const inputPlatformIcon = document.getElementById('input-platform-icon').querySelector('i');
    const urlInput          = document.getElementById('media-url');
    const btnPaste          = document.getElementById('btn-paste');
    const btnClear          = document.getElementById('btn-clear');
    const urlExamples       = document.getElementById('url-examples');

    const ytOptions      = document.getElementById('yt-options');
    const ttOptions      = document.getElementById('tt-options');
    const igOptions      = document.getElementById('ig-options');
    const otherOptions   = document.getElementById('other-options');
    const qualitySection = document.getElementById('quality-section');

    const formatBtns  = document.querySelectorAll('.md-format-btn');
    const qualityBtns = document.querySelectorAll('.md-quality-btn');

    const btnProcess    = document.getElementById('btn-process');
    const btnProcessLbl = document.getElementById('btn-process-label');

    const stateProcessing = document.getElementById('state-processing');
    const stateResult     = document.getElementById('state-result');
    const stateError      = document.getElementById('state-error');
    const procDetail      = document.getElementById('proc-detail');
    const progressBar     = document.getElementById('progress-bar');

    const resultTitle       = document.getElementById('result-title');
    const resultSub         = document.getElementById('result-sub');
    const resultSingle      = document.getElementById('result-single');
    const btnDownloadSingle = document.getElementById('btn-download-single');
    const downloadLabel     = document.getElementById('download-label');
    const resultPicker      = document.getElementById('result-picker');
    const btnReset          = document.getElementById('btn-reset');

    const errorMsg  = document.getElementById('error-msg');
    const errorTips = document.getElementById('error-tips');
    const tipsList  = document.getElementById('tips-list');
    const btnRetry  = document.getElementById('btn-retry');

    const toast    = document.getElementById('md-toast');
    const toastMsg = document.getElementById('toast-msg');

    /* =========================================================
       HELPERS
    ========================================================= */
    const show = el => el.classList.remove('md-hidden');
    const hide = el => el.classList.add('md-hidden');

    function showToast(msg, duration = 2500) {
        toastMsg.textContent = msg;
        toast.classList.add('show');
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => toast.classList.remove('show'), duration);
    }

    function hideAllStates() {
        hide(stateProcessing);
        hide(stateResult);
        hide(stateError);
    }

    function startProgress(label) {
        procDetail.textContent = label;
        progressBar.style.width = '5%';
        progressBar.style.transition = 'width 0.4s ease';
        let w = 5;
        progressTimer = setInterval(() => {
            const step = w < 40 ? 10 : w < 70 ? 5 : w < 88 ? 1.5 : 0;
            w = Math.min(w + step * Math.random(), 88);
            progressBar.style.width = w + '%';
        }, 500);
    }

    function finishProgress() {
        clearInterval(progressTimer);
        progressBar.style.transition = 'width 0.3s ease';
        progressBar.style.width = '100%';
    }

    function resetProgress() {
        clearInterval(progressTimer);
        progressBar.style.transition = 'none';
        progressBar.style.width = '0%';
    }

    function updateUrlExamples() {
        const cfg = PLATFORM_CONFIG[currentPlatform];
        urlExamples.innerHTML =
            `<span class="md-example-label">Contoh:</span>` +
            cfg.examples.map(e => `<span class="md-example">${e}</span>`).join('');
    }

    function autoDetectPlatform(url) {
        for (const [key, cfg] of Object.entries(PLATFORM_CONFIG)) {
            if (cfg.patterns.some(p => p.test(url))) return key;
        }
        return null;
    }

    function validateUrl() {
        const val = urlInput.value.trim();
        btnClear.classList.toggle('visible', val.length > 0);
        btnProcess.disabled = val.length < 10;

        if (val.length > 10) {
            const detected = autoDetectPlatform(val);
            if (detected && detected !== currentPlatform) {
                switchPlatform(detected);
            }
        }
    }

    
    /* =========================================================
       PLATFORM SWITCHING
    ========================================================= */
    function switchPlatform(platform) {
        currentPlatform = platform;
        platformBtns.forEach(b =>
            b.classList.toggle('active', b.dataset.platform === platform)
        );
        const cfg = PLATFORM_CONFIG[platform];
        platformHint.textContent    = cfg.hint;
        urlInput.placeholder        = cfg.placeholder;
        inputPlatformIcon.className = `fa-brands ${cfg.icon}`;

        hide(ytOptions); hide(ttOptions); hide(igOptions); hide(otherOptions);
        if (platform === 'youtube')   show(ytOptions);
        if (platform === 'tiktok')    show(ttOptions);
        if (platform === 'instagram') show(igOptions);
        if (platform === 'other')     show(otherOptions);

        updateUrlExamples();
        updateProcessLabel();
    }

    platformBtns.forEach(btn => {
        btn.addEventListener('click', () => switchPlatform(btn.dataset.platform));
    });

    /* =========================================================
       FORMAT & QUALITY
    ========================================================= */
    formatBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            currentFormat = this.dataset.format;
            formatBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFormat === 'mp4' ? show(qualitySection) : hide(qualitySection);
        });
    });

    qualityBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            currentQuality = this.dataset.quality;
            qualityBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    function updateProcessLabel() {
        const labels = {
            youtube:   'Download dari YouTube',
            tiktok:    'Download dari TikTok',
            instagram: 'Download dari Instagram',
            other:     'Download Sekarang',
        };
        btnProcessLbl.textContent = labels[currentPlatform] || 'Download Sekarang';
    }

    /* =========================================================
       URL INPUT EVENTS
    ========================================================= */
    urlInput.addEventListener('input', validateUrl);
    urlInput.addEventListener('paste', () => setTimeout(validateUrl, 50));

    btnPaste.addEventListener('click', async () => {
        try {
            const text = await navigator.clipboard.readText();
            if (text && text.trim()) {
                urlInput.value = text.trim();
                validateUrl();
                showToast('URL ditempelkan!');
            }
        } catch {
            urlInput.focus();
            showToast('Tekan Ctrl+V untuk menempelkan URL');
        }
    });

    btnClear.addEventListener('click', () => {
        urlInput.value = '';
        validateUrl();
        urlInput.focus();
    });

    /* =========================================================
       BUILD PAYLOAD
    ========================================================= */
    function buildPayload(url) {
        const payload = { url };

        if (currentPlatform === 'youtube') {
            if (currentFormat === 'mp3') {
                payload.downloadMode = 'audio';
                payload.audioFormat  = 'mp3';
            } else {
                payload.downloadMode = 'auto';
                payload.videoQuality = currentQuality;
            }
        }

        if (currentPlatform === 'tiktok') {
            const audioOnly = document.getElementById('tt-audio-only')?.checked ?? false;
            payload.tiktokFullAudio = true;
            if (audioOnly) payload.downloadMode = 'audio';
        }

        return payload;
    }

    /* =========================================================
       PROXY REQUEST — Laravel forward ke Cobalt dengan token
    ========================================================= */
    async function callViaProxy(payload) {
        const csrf = document.querySelector('meta[name="csrf-token"]');
        const res  = await fetch('/media-downloader/process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': csrf ? csrf.content : '',
            },
            body: JSON.stringify(payload),
        });

        let data;
        try {
            data = await res.json();
        } catch {
            throw new Error(`Server mengembalikan respons tidak valid (HTTP ${res.status})`);
        }

        if (!res.ok) {
            throw new Error(data?.message || `Server error: HTTP ${res.status}`);
        }

        return data;
    }

    /* =========================================================
       PROSES DOWNLOAD
    ========================================================= */
    btnProcess.addEventListener('click', async function () {
        const url = urlInput.value.trim();
        if (!url) return;

        hideAllStates();
        resetProgress();
        show(stateProcessing);
        btnProcess.disabled = true;
        startProgress('Menghubungi server...');

        try {
            procDetail.textContent = 'Sedang memproses URL Anda...';
            const payload = buildPayload(url);
            const data    = await callViaProxy(payload);

            finishProgress();

            if (data.status === 'error') {
                const code = data.error?.code || data.message || 'error.unknown';
                showError(code);
                return;
            }

            handleCobaltResponse(data);

        } catch (err) {
            finishProgress();
            showError(err.message);
        }
    });

    /* =========================================================
       HANDLE COBALT RESPONSE
    ========================================================= */
    function handleCobaltResponse(data) {
        hideAllStates();
        show(stateResult);

        hide(resultSingle);
        hide(resultPicker);
        resultPicker.innerHTML = '';

        if (data.status === 'redirect' || data.status === 'tunnel') {
            let ext = 'mp4';
            if (currentFormat === 'mp3') ext = 'mp3';
            else if (currentPlatform === 'tiktok' && document.getElementById('tt-audio-only')?.checked) ext = 'mp3';

            const filename = `mediatools_${currentPlatform}_${Date.now()}.${ext}`;

            resultTitle.textContent = 'Siap Download!';
            resultSub.textContent   = `File ${ext.toUpperCase()} siap — klik tombol di bawah`;

            // Gunakan proxy download Laravel — bukan buka URL Cobalt langsung
            btnDownloadSingle.href = '#';
            btnDownloadSingle.removeAttribute('download');
            btnDownloadSingle.removeAttribute('target');

            // Simpan data untuk dipakai saat klik
            btnDownloadSingle.dataset.proxyUrl  = data.url;
            btnDownloadSingle.dataset.filename  = filename;
            btnDownloadSingle.dataset.proxyMode = 'fetch'; // gunakan fetch download

            downloadLabel.textContent = ext === 'mp3' ? 'Download Audio MP3' : 'Download Video';
            show(resultSingle);
            showToast('File siap didownload!');

        } else if (data.status === 'picker') {
            const items = data.picker || [];
            resultTitle.textContent = `${items.length} Item Tersedia`;
            resultSub.textContent   = 'Klik item untuk mendownload';

            items.forEach((item, i) => {
                const a = document.createElement('a');
                a.href      = '#';
                a.className = 'md-picker-item';
                a.innerHTML = item.thumb
                    ? `<img src="${item.thumb}" class="md-picker-thumb" alt="Item ${i + 1}" loading="lazy">`
                    : `<div class="md-picker-thumb" style="display:flex;align-items:center;justify-content:center;font-size:28px;color:var(--text-muted)"><i class="fa-solid fa-file-video"></i></div>`;
                a.innerHTML += `<span class="md-picker-label"><i class="fa-solid fa-download" style="font-size:8px"></i> Item ${i + 1}</span>`;

                // Download via proxy
                a.addEventListener('click', function (e) {
                    e.preventDefault();
                    triggerProxyDownload(item.url, `mediatools_item_${i + 1}.mp4`);
                });

                resultPicker.appendChild(a);
            });

            show(resultPicker);
            showToast(`${items.length} item ditemukan!`);

        } else if (data.status === 'youtube_external') {
            hideAllStates();
            show(stateResult);
            hide(resultSingle);
            hide(resultPicker);
            resultPicker.innerHTML = '';

            resultTitle.textContent = 'Pilih Layanan Download';
            resultSub.textContent   = data.message || 'YouTube memerlukan layanan khusus.';

            const services = data.services || [];
            services.forEach((svc) => {
                const a = document.createElement('a');
                a.href      = svc.url;
                a.target    = '_blank';
                a.rel       = 'noopener noreferrer';
                a.className = 'md-picker-item';
                a.style.cssText = 'flex-direction:row;gap:12px;align-items:center;padding:14px 16px;';
                a.innerHTML = `
                    <div style="width:38px;height:38px;border-radius:10px;background:var(--accent-dim);
                                display:flex;align-items:center;justify-content:center;
                                color:var(--accent);font-size:16px;flex-shrink:0;">
                        <i class="fa-solid fa-external-link-alt"></i>
                    </div>
                    <div style="flex:1;text-align:left;">
                        <p style="font-size:13px;font-weight:700;color:var(--text-primary);margin:0 0 2px">${svc.name}</p>
                        <p style="font-size:11px;color:var(--text-muted);margin:0">${svc.desc}</p>
                    </div>
                    <i class="fa-solid fa-arrow-right" style="font-size:11px;color:var(--text-muted)"></i>
                `;
                resultPicker.appendChild(a);
            });

            show(resultPicker);

        } else {
            showError('Respons dari server tidak dikenali.');
        }
    }

    /* =========================================================
    DIRECT DOWNLOAD — fetch langsung dari browser ke URL Cobalt
    URL Cobalt terikat ke session/IP, tidak bisa di-proxy server
    ========================================================= */
    async function triggerProxyDownload(sourceUrl, filename) {

        // Ubah tombol jadi loading state
        const originalLabel = downloadLabel.textContent;
        downloadLabel.textContent = 'Menyiapkan...';
        btnDownloadSingle.style.opacity       = '0.7';
        btnDownloadSingle.style.pointerEvents = 'none';

        try {
            // Fetch langsung dari browser ke URL Cobalt
            // Tidak melalui Laravel karena URL-nya terikat ke IP/session user
            const res = await fetch(sourceUrl, {
                method:  'GET',
                headers: { 'Accept': '*/*' },
            });

            if (!res.ok) {
                throw new Error(`HTTP ${res.status}`);
            }

            downloadLabel.textContent = 'Mengunduh...';

            // Stream ke blob lalu trigger download
            const blob = await res.blob();
            const url  = URL.createObjectURL(blob);
            const a    = document.createElement('a');
            a.href     = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);

            // Bebaskan memori setelah beberapa detik
            setTimeout(() => URL.revokeObjectURL(url), 30000);

            showToast('Download dimulai!');

        } catch (err) {
            console.error('Download error:', err);

            // Jika fetch gagal (CORS atau expired), buka URL langsung sebagai fallback
            showToast('Membuka link download...', 3000);
            const a    = document.createElement('a');
            a.href     = sourceUrl;
            a.target   = '_blank';
            a.rel      = 'noopener noreferrer';
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);

        } finally {
            downloadLabel.textContent         = originalLabel;
            btnDownloadSingle.style.opacity       = '';
            btnDownloadSingle.style.pointerEvents = '';
        }
    }

    // Event listener untuk tombol download utama
    btnDownloadSingle.addEventListener('click', function (e) {
        e.preventDefault();
        const sourceUrl = this.dataset.proxyUrl;
        const filename  = this.dataset.filename;
        if (sourceUrl && filename) {
            triggerProxyDownload(sourceUrl, filename);
        }
    });

    /* =========================================================
       ERROR HANDLER
    ========================================================= */
    function showError(msg) {
        hideAllStates();
        show(stateError);
        errorMsg.textContent = ERROR_MAP[msg] || msg || 'Terjadi kesalahan. Silakan coba lagi.';
        tipsList.innerHTML = (PLATFORM_TIPS[currentPlatform] || PLATFORM_TIPS.other)
            .map(t => `<li>${t}</li>`).join('');
        show(errorTips);
    }

    /* =========================================================
       RESET
    ========================================================= */
    function resetApp() {
        hideAllStates();
        resetProgress();
        btnProcess.disabled = urlInput.value.trim().length < 10;
    }

    btnReset.addEventListener('click', resetApp);
    btnRetry.addEventListener('click', () => {
        resetApp();
    });

    /* =========================================================
       INIT
    ========================================================= */
    switchPlatform('youtube');
    updateUrlExamples();
});