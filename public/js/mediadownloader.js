/**
 * MediaTools Media Downloader — mediadownloader.js  v3 PRO
 * =========================================================
 * Flow:
 *  YouTube:
 *    1. Paste URL → auto-detect platform
 *    2. Choose MP3 or MP4
 *       MP3 → process button enabled immediately
 *       MP4 → fetch real formats from server → user picks quality → process
 *    3. Download via token (server streams file)
 *
 *  TikTok / Instagram / Other:
 *    1. Paste URL → select platform
 *    2. Process button enabled → download via Cobalt proxy
 */

(function () {
    "use strict";

    /* ──────────────────────────────────────────────
       CONSTANTS
    ────────────────────────────────────────────── */
    const PLATFORM_CFG = {
        youtube: {
            hint:        'YouTube · Shorts · Music',
            placeholder: 'https://www.youtube.com/watch?v=...',
            examples:    ['youtube.com/watch?v=...', 'youtu.be/...', 'youtube.com/shorts/...'],
            icon:        'fa-brands fa-youtube',
        },
        tiktok: {
            hint:        'TikTok · Video · Audio',
            placeholder: 'https://www.tiktok.com/@user/video/...',
            examples:    ['tiktok.com/@user/video/...', 'vm.tiktok.com/...'],
            icon:        'fa-brands fa-tiktok',
        },
        instagram: {
            hint:        'Reels · Foto · Video',
            placeholder: 'https://www.instagram.com/reel/...',
            examples:    ['instagram.com/reel/...', 'instagram.com/p/...'],
            icon:        'fa-brands fa-instagram',
        },
        other: {
            hint:        'Twitter · Reddit · Pinterest · Vimeo',
            placeholder: 'https://twitter.com/...',
            examples:    ['twitter.com/...', 'reddit.com/...', 'pinterest.com/...'],
            icon:        'fa-solid fa-globe',
        },
    };

    const PLATFORM_PATTERNS = {
        youtube:   [/youtube\.com/, /youtu\.be/],
        tiktok:    [/tiktok\.com/, /vm\.tiktok/],
        instagram: [/instagram\.com/, /instagr\.am/],
    };

    const TIPS = {
        youtube:   ['Video harus bersifat publik (bukan private/unlisted)', 'YouTube Shorts dan Music juga didukung', 'Jika gagal, coba URL yang disalin langsung dari browser'],
        tiktok:    ['Akun TikTok harus bersifat publik', 'Link harus ke video spesifik, bukan halaman profil', 'Aktifkan "Tanpa Watermark" untuk hasil bersih'],
        instagram: ['Hanya konten dari akun publik', 'Salin link dari browser, bukan aplikasi IG', 'Reels, foto, dan video post didukung'],
        other:     ['Pastikan konten bisa diakses tanpa login', 'Gunakan link langsung dari browser desktop', 'Beberapa platform mungkin tidak selalu berhasil'],
    };

    /* ──────────────────────────────────────────────
       STATE
    ────────────────────────────────────────────── */
    const S = {
        platform:         'youtube',
        format:           null,      // 'mp3' | 'mp4' — null = not chosen yet
        quality:          null,      // selected height (720 etc)
        formatsLoaded:    false,
        loadingFormats:   false,
        processing:       false,
    };

    /* ──────────────────────────────────────────────
       DOM
    ────────────────────────────────────────────── */
    const $ = id => document.getElementById(id);

    const DOM = {
        platformBtns:   document.querySelectorAll('.md-platform-btn'),
        platformHint:   $('platform-hint'),
        inputIcon:      document.querySelector('#input-platform-icon i'),
        urlInput:       $('media-url'),
        btnPaste:       $('btn-paste'),
        btnClear:       $('btn-clear'),
        urlExamples:    $('url-examples'),

        // Sections
        ytFormatSec:    $('yt-format-section'),
        ytQualitySec:   $('yt-quality-section'),
        ttOptions:      $('tt-options'),
        igOptions:      $('ig-options'),
        otherOptions:   $('other-options'),

        // YouTube format buttons
        formatBtns:     document.querySelectorAll('.md-format-btn'),

        // Quality area
        qualityGrid:    $('quality-grid'),
        qualityHint:    $('quality-hint'),

        // Process
        btnProcess:     $('btn-process'),
        btnProcessLbl:  $('btn-process-label'),

        // States
        stateProc:      $('state-processing'),
        stateResult:    $('state-result'),
        stateError:     $('state-error'),

        procTitle:      $('proc-title'),
        procSub:        $('proc-sub'),
        progressBar:    $('progress-bar'),
        progressPct:    $('progress-pct'),
        progressStep:   $('progress-step'),

        resultTitle:    $('result-title'),
        resultSub:      $('result-sub'),
        resultSingle:   $('result-single'),
        btnDlSingle:    $('btn-download-single'),
        dlLabel:        $('download-label'),
        resultPicker:   $('result-picker'),
        btnReset:       $('btn-reset'),

        errorMsg:       $('error-msg'),
        tipsList:       $('tips-list'),
        btnRetry:       $('btn-retry'),

        toast:          $('md-toast'),
        toastMsg:       $('toast-msg'),
        toastIco:       $('toast-ico'),
    };

    /* ──────────────────────────────────────────────
       ROUTES
    ────────────────────────────────────────────── */
    const ROUTES = {
        process:  document.querySelector('meta[name="md-process-url"]')?.content  || '/media-downloader/process',
        download: document.querySelector('meta[name="md-download-url"]')?.content || '/media-downloader/download',
    };

    const CSRF = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    /* ──────────────────────────────────────────────
       HELPERS
    ────────────────────────────────────────────── */
    const show = el => el && el.classList.remove('md-hidden');
    const hide = el => el && el.classList.add('md-hidden');
    const esc  = s  => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    let _toastT = null;
    function toast(msg, type = 'ok', dur = 2800) {
        if (!DOM.toast) return;
        DOM.toastMsg.textContent = msg;
        if (DOM.toastIco) {
            DOM.toastIco.className = type === 'warn'
                ? 'fa-solid fa-triangle-exclamation md-toast-ico'
                : 'fa-solid fa-check md-toast-ico';
            DOM.toastIco.style.color = type === 'warn' ? '#fbbf24' : '#a3e635';
        }
        DOM.toast.classList.add('show');
        clearTimeout(_toastT);
        _toastT = setTimeout(() => DOM.toast.classList.remove('show'), dur);
    }

    function hideAllStates() {
        hide(DOM.stateProc);
        hide(DOM.stateResult);
        hide(DOM.stateError);
    }

    function autoDetect(url) {
        for (const [plat, patterns] of Object.entries(PLATFORM_PATTERNS)) {
            if (patterns.some(p => p.test(url))) return plat;
        }
        return null;
    }

    /* ──────────────────────────────────────────────
       PLATFORM SWITCHING
    ────────────────────────────────────────────── */
    function switchPlatform(p) {
        S.platform = p;
        S.format   = null;
        S.quality  = null;
        S.formatsLoaded = false;

        DOM.platformBtns.forEach(b => b.classList.toggle('active', b.dataset.platform === p));

        const cfg = PLATFORM_CFG[p];
        DOM.platformHint.textContent = cfg.hint;
        DOM.urlInput.placeholder     = cfg.placeholder;
        DOM.inputIcon.className      = cfg.icon;

        // Update examples
        DOM.urlExamples.innerHTML =
            `<span class="md-example-label">Contoh:</span>` +
            cfg.examples.map(e => `<span class="md-example">${esc(e)}</span>`).join('');

        // Show/hide option panels
        hide(DOM.ytFormatSec);
        hide(DOM.ytQualitySec);
        hide(DOM.ttOptions);
        hide(DOM.igOptions);
        hide(DOM.otherOptions);

        if (p === 'youtube')   show(DOM.ytFormatSec);
        if (p === 'tiktok')    show(DOM.ttOptions);
        if (p === 'instagram') show(DOM.igOptions);
        if (p === 'other')     show(DOM.otherOptions);

        updateProcessBtn();
    }

    DOM.platformBtns.forEach(btn => {
        btn.addEventListener('click', () => switchPlatform(btn.dataset.platform));
    });

    /* ──────────────────────────────────────────────
       FORMAT SELECTION (YouTube)
    ────────────────────────────────────────────── */
    DOM.formatBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const fmt = this.dataset.format;
            if (S.format === fmt) return;

            S.format  = fmt;
            S.quality = null;
            S.formatsLoaded = false;

            DOM.formatBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            if (fmt === 'mp3') {
                hide(DOM.ytQualitySec);
                updateProcessBtn();
            } else {
                // MP4 — need to fetch formats first if URL valid
                const url = DOM.urlInput.value.trim();
                if (url.length > 10) {
                    show(DOM.ytQualitySec);
                    fetchFormats(url);
                } else {
                    show(DOM.ytQualitySec);
                    renderQualitySkeleton();
                }
                updateProcessBtn();
            }
        });
    });

    /* ──────────────────────────────────────────────
       FETCH REAL FORMATS FROM SERVER
    ────────────────────────────────────────────── */
    async function fetchFormats(url) {
        if (S.loadingFormats) return;
        S.loadingFormats = true;
        S.formatsLoaded  = false;
        S.quality        = null;

        renderQualitySkeleton();
        updateProcessBtn();

        try {
            const resp = await fetch(ROUTES.process, {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF(),
                    'Accept':       'application/json',
                },
                body: JSON.stringify({ url, action: 'get_formats' }),
            });

            const data = await resp.json();

            if (data.status === 'formats') {
                renderQualities(data.qualities || [], data.title);
                S.formatsLoaded = true;
            } else {
                // Fallback qualities
                renderQualities([
                    { height: 1080, label: '1080p Full HD', filesize_fmt: null },
                    { height: 720,  label: '720p HD',       filesize_fmt: null },
                    { height: 480,  label: '480p',          filesize_fmt: null },
                    { height: 360,  label: '360p',          filesize_fmt: null },
                ], null);
                S.formatsLoaded = true;
                toast('Info format tidak tersedia — menggunakan pilihan standar.', 'warn');
            }
        } catch {
            renderQualities([
                { height: 1080, label: '1080p Full HD', filesize_fmt: null },
                { height: 720,  label: '720p HD',       filesize_fmt: null },
                { height: 480,  label: '480p',          filesize_fmt: null },
                { height: 360,  label: '360p',          filesize_fmt: null },
            ], null);
            S.formatsLoaded = true;
        } finally {
            S.loadingFormats = false;
            updateProcessBtn();
        }
    }

    function renderQualitySkeleton() {
        DOM.qualityGrid.innerHTML = [1,2,3,4].map(() =>
            `<div class="md-quality-skeleton"></div>`
        ).join('');
    }

    function renderQualities(qualities, title) {
        DOM.qualityGrid.innerHTML = '';

        if (title && DOM.qualityHint) {
            DOM.qualityHint.textContent = `Format tersedia untuk: "${title.substring(0, 50)}${title.length > 50 ? '…' : ''}"`;
        }

        qualities.forEach(q => {
            const btn = document.createElement('button');
            btn.className       = 'md-quality-btn';
            btn.dataset.quality = q.height;
            btn.type            = 'button';

            btn.innerHTML = `
                <div class="md-quality-badge">${esc(q.label.split(' ')[0])}</div>
                <div class="md-quality-meta">
                    <span class="md-quality-label">${esc(q.label)}</span>
                    ${q.filesize_fmt ? `<span class="md-quality-size">≈ ${esc(q.filesize_fmt)}</span>` : '<span class="md-quality-size">Ukuran tidak tersedia</span>'}
                </div>
                <div class="md-quality-radio"></div>
            `;

            btn.addEventListener('click', () => {
                S.quality = q.height;
                DOM.qualityGrid.querySelectorAll('.md-quality-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                updateProcessBtn();
            });

            DOM.qualityGrid.appendChild(btn);
        });

        // Auto-select first (best) quality
        const first = DOM.qualityGrid.querySelector('.md-quality-btn');
        if (first) {
            first.click();
        }
    }

    /* ──────────────────────────────────────────────
       URL INPUT
    ────────────────────────────────────────────── */
    DOM.urlInput.addEventListener('input', onUrlChange);
    DOM.urlInput.addEventListener('paste', () => setTimeout(onUrlChange, 60));

    let _formatDebounce = null;
    function onUrlChange() {
        const val = DOM.urlInput.value.trim();
        DOM.btnClear.classList.toggle('visible', val.length > 0);

        // Auto-detect platform
        if (val.length > 10) {
            const detected = autoDetect(val);
            if (detected && detected !== S.platform) {
                switchPlatform(detected);
            }
        }

        // If YouTube + MP4 format already selected, fetch formats on URL change
        if (S.platform === 'youtube' && S.format === 'mp4' && val.length > 10) {
            clearTimeout(_formatDebounce);
            _formatDebounce = setTimeout(() => {
                S.formatsLoaded = false;
                fetchFormats(val);
            }, 800);
        }

        updateProcessBtn();
    }

    DOM.btnPaste.addEventListener('click', async () => {
        try {
            const text = await navigator.clipboard.readText();
            if (text && text.startsWith('http')) {
                DOM.urlInput.value = text.trim();
                onUrlChange();
                toast('URL ditempel!');
            }
        } catch {
            toast('Izin clipboard ditolak — tempel manual (Ctrl+V)', 'warn');
        }
    });

    DOM.btnClear.addEventListener('click', () => {
        DOM.urlInput.value = '';
        DOM.btnClear.classList.remove('visible');
        S.format        = null;
        S.quality       = null;
        S.formatsLoaded = false;

        DOM.formatBtns.forEach(b => b.classList.remove('active'));
        if (DOM.qualityGrid) DOM.qualityGrid.innerHTML = '';
        if (S.platform === 'youtube') {
            hide(DOM.ytQualitySec);
        }

        updateProcessBtn();
    });

    /* ──────────────────────────────────────────────
       UPDATE PROCESS BUTTON
    ────────────────────────────────────────────── */
    function updateProcessBtn() {
        const url    = DOM.urlInput.value.trim();
        const hasUrl = url.length > 10;
        let   ready  = false;
        let   label  = 'Download Sekarang';

        if (!hasUrl) {
            label = 'Paste URL terlebih dahulu';
        } else if (S.platform === 'youtube') {
            if (!S.format) {
                label = 'Pilih format MP3 atau MP4';
            } else if (S.format === 'mp3') {
                ready = true;
                label = 'Download Audio MP3';
            } else {
                // MP4
                if (S.loadingFormats) {
                    label = 'Memuat format tersedia...';
                } else if (!S.quality) {
                    label = 'Pilih kualitas video';
                } else {
                    ready = true;
                    label = `Download MP4 ${S.quality}p`;
                }
            }
        } else {
            // TikTok / IG / Other — just need URL
            ready = true;
            const platformLabels = {
                tiktok:    'Download dari TikTok',
                instagram: 'Download dari Instagram',
                other:     'Download Sekarang',
            };
            label = platformLabels[S.platform] || 'Download Sekarang';
        }

        DOM.btnProcess.disabled        = !ready;
        DOM.btnProcessLbl.textContent  = label;
    }

    /* ──────────────────────────────────────────────
       PROCESS / DOWNLOAD
    ────────────────────────────────────────────── */
    DOM.btnProcess.addEventListener('click', doProcess);

    async function doProcess() {
        if (DOM.btnProcess.disabled || S.processing) return;
        S.processing = true;

        const url = DOM.urlInput.value.trim();
        hideAllStates();
        show(DOM.stateProc);
        DOM.btnProcess.disabled = true;
        DOM.btnProcess.classList.add('loading');

        try {
            if (S.platform === 'youtube') {
                await processYouTube(url);
            } else {
                await processOther(url);
            }
        } finally {
            S.processing = false;
            DOM.btnProcess.classList.remove('loading');
        }
    }

    /* ─── YouTube processing ─── */
    async function processYouTube(url) {
        const isAudio = S.format === 'mp3';

        setProgress(5, isAudio ? 'Memulai konversi audio...' : 'Memulai download video...');

        // Simulated progress phases (real progress comes from server response time)
        const phases = isAudio
            ? [
                { pct: 20, delay: 800,  msg: 'Mengambil info lagu...' },
                { pct: 45, delay: 2000, msg: 'Mendownload audio...' },
                { pct: 70, delay: 5000, msg: 'Mengkonversi ke MP3...' },
                { pct: 85, delay: 10000, msg: 'Memfinalisasi...' },
            ]
            : [
                { pct: 15, delay: 600,  msg: 'Mengambil info video...' },
                { pct: 35, delay: 2000, msg: 'Mendownload video stream...' },
                { pct: 55, delay: 5000, msg: 'Mendownload audio stream...' },
                { pct: 75, delay: 10000, msg: 'Menggabungkan video & audio...' },
                { pct: 88, delay: 20000, msg: 'Memfinalisasi...' },
            ];

        let cumDelay = 0;
        const timers = phases.map(({ pct, delay, msg }) => {
            cumDelay += delay;
            return setTimeout(() => {
                if (S.processing) setProgress(pct, msg);
            }, cumDelay);
        });

        try {
            const body = {
                url,
                action:       'process',
                downloadMode: isAudio ? 'audio' : 'video',
                quality:      S.quality || 720,
            };

            const resp = await fetch(ROUTES.process, {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF(),
                    'Accept':       'application/json',
                },
                body: JSON.stringify(body),
            });

            timers.forEach(clearTimeout);
            setProgress(100, 'Selesai!');
            await sleep(300);

            const data = await resp.json();

            if (data.status === 'ready' && data.token) {
                showResultDownload(data.token, data.type, data.filesize);
            } else {
                showError(data.message || 'Gagal memproses video.');
            }
        } catch {
            timers.forEach(clearTimeout);
            showError('Tidak dapat terhubung ke server. Periksa koneksi internet.');
        }
    }

    /* ─── TikTok / Instagram / Other ─── */
    async function processOther(url) {
        setProgress(10, 'Mengambil info media...');

        const body = { url, action: 'process' };

        if (S.platform === 'tiktok') {
            const noWm = document.getElementById('tt-no-watermark');
            const aoOnly = document.getElementById('tt-audio-only');
            body.removeTikTokWatermark = noWm?.checked ?? true;
            if (aoOnly?.checked) body.downloadMode = 'audio';
        }

        // Fake progress (server is fast, typically 2-5s)
        const t1 = setTimeout(() => setProgress(35, 'Memproses URL...'), 600);
        const t2 = setTimeout(() => setProgress(65, 'Menyiapkan file...'), 2000);
        const t3 = setTimeout(() => setProgress(85, 'Hampir selesai...'), 4000);

        try {
            const resp = await fetch(ROUTES.process, {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF(),
                    'Accept':       'application/json',
                },
                body: JSON.stringify(body),
            });

            [t1, t2, t3].forEach(clearTimeout);
            setProgress(100, 'Selesai!');
            await sleep(300);

            const data = await resp.json();

            if (data.status === 'ready' && data.token) {
                showResultDownload(data.token, data.type || 'video', null);
            } else if (data.status === 'picker') {
                showResultPicker(data.picker || []);
            } else {
                showError(data.message || 'Gagal memproses URL.');
            }
        } catch {
            [t1, t2, t3].forEach(clearTimeout);
            showError('Tidak dapat terhubung ke server. Periksa koneksi internet.');
        }
    }

    /* ──────────────────────────────────────────────
       PROGRESS HELPERS
    ────────────────────────────────────────────── */
    function setProgress(pct, msg) {
        if (DOM.progressBar)  DOM.progressBar.style.width  = pct + '%';
        if (DOM.progressPct)  DOM.progressPct.textContent  = pct + '%';
        if (DOM.progressStep) DOM.progressStep.textContent = msg || '';
    }

    function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }

    /* ──────────────────────────────────────────────
       RESULT STATES
    ────────────────────────────────────────────── */
    function showResultDownload(token, type, filesize) {
        hideAllStates();
        show(DOM.stateResult);
        hide(DOM.resultPicker);
        DOM.resultPicker.innerHTML = '';

        const isAudio = type === 'audio';
        DOM.resultTitle.textContent = isAudio ? 'Audio Siap!' : 'Video Siap!';
        DOM.resultSub.textContent   = filesize
            ? `Ukuran file: ${filesize} — klik untuk download`
            : 'File siap diunduh';

        const dlUrl = `${ROUTES.download}/${token}`;
        const ext   = isAudio ? 'mp3' : 'mp4';

        DOM.btnDlSingle.href                = dlUrl;
        DOM.btnDlSingle.setAttribute('download', `mediatools_${Date.now()}.${ext}`);
        DOM.dlLabel.textContent             = isAudio ? 'Download Audio MP3' : 'Download Video MP4';

        // After click, trigger auto-cleanup on server (fire & forget)
        DOM.btnDlSingle.onclick = () => {
            toast('Download dimulai!');
            // Re-enable process button for next download
            setTimeout(() => {
                S.processing = false;
                updateProcessBtn();
            }, 2000);
        };

        show(DOM.resultSingle);
        toast(isAudio ? 'Audio siap download!' : 'Video siap download!');
    }

    function showResultPicker(items) {
        hideAllStates();
        show(DOM.stateResult);
        hide(DOM.resultSingle);
        DOM.resultPicker.innerHTML = '';

        DOM.resultTitle.textContent = `${items.length} Item Ditemukan`;
        DOM.resultSub.textContent   = 'Klik item untuk download';

        items.forEach((item, i) => {
            const el = document.createElement('a');
            el.href      = `${ROUTES.download}/${item.token || '#'}`;
            el.className = 'md-picker-item';

            if (item.thumb) {
                el.innerHTML = `<img src="${esc(item.thumb)}" class="md-picker-thumb" alt="Item ${i+1}" loading="lazy">`;
            } else {
                el.innerHTML = `<div class="md-picker-thumb" style="display:flex;align-items:center;justify-content:center;height:160px;background:rgba(255,255,255,0.03);font-size:28px;color:#4b5563;"><i class="fa-solid fa-file-video"></i></div>`;
            }
            el.innerHTML += `<span class="md-picker-label"><i class="fa-solid fa-download" style="font-size:8px"></i> ${i+1}</span>`;

            if (item.url && !item.token) {
                el.href = item.url;
                el.target = '_blank';
                el.rel = 'noopener noreferrer';
            }

            DOM.resultPicker.appendChild(el);
        });

        show(DOM.resultPicker);
        toast(`${items.length} item ditemukan!`);
    }

    function showError(msg) {
        hideAllStates();
        show(DOM.stateError);
        DOM.errorMsg.textContent = msg;
        DOM.tipsList.innerHTML   = (TIPS[S.platform] || TIPS.other).map(t => `<li>${esc(t)}</li>`).join('');
        S.processing             = false;
        DOM.btnProcess.disabled  = false;
        updateProcessBtn();
    }

    /* ──────────────────────────────────────────────
       RESET
    ────────────────────────────────────────────── */
    function resetApp() {
        hideAllStates();
        setProgress(0, '');
        S.processing     = false;
        S.format         = null;
        S.quality        = null;
        S.formatsLoaded  = false;
        S.loadingFormats = false;

        DOM.formatBtns.forEach(b => b.classList.remove('active'));
        hide(DOM.ytQualitySec);
        if (DOM.qualityGrid) DOM.qualityGrid.innerHTML = '';
        DOM.resultPicker.innerHTML = '';

        updateProcessBtn();
    }

    DOM.btnReset.addEventListener('click', resetApp);
    DOM.btnRetry.addEventListener('click', resetApp);

    /* ──────────────────────────────────────────────
       INIT
    ────────────────────────────────────────────── */
    switchPlatform('youtube');

})();
