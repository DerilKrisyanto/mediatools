'use strict';

document.addEventListener('DOMContentLoaded', function () {

    const MAX_FILES = 5;
    const MAX_SIZE  = 50 * 1024 * 1024; // 50 MB

    /* =========================================================
       TIMEOUT CONFIG
    ========================================================= */
    const TIMEOUT_MAP = {
        pdf_to_word:   10 * 60 * 1000,
        pdf_to_excel:  10 * 60 * 1000,
        pdf_to_ppt:    10 * 60 * 1000,
        pdf_to_jpg:     5 * 60 * 1000,
        pdf_to_png:     5 * 60 * 1000,
        word_to_pdf:    3 * 60 * 1000,
        excel_to_pdf:   3 * 60 * 1000,
        ppt_to_pdf:     3 * 60 * 1000,
    };
    const DEFAULT_TIMEOUT = 2 * 60 * 1000;

    function getTimeout(type) {
        return TIMEOUT_MAP[type] ?? DEFAULT_TIMEOUT;
    }

    /* =========================================================
       PROGRESS STAGES — per conversion type
       Each stage: { label, target_pct, duration_ms }
       This simulates a realistic multi-step pipeline
    ========================================================= */
    const PROGRESS_STAGES = {
        pdf_to_word: [
            { label: 'Mengupload file ke server…',        pct: 8,  ms: 1500 },
            { label: 'Memvalidasi & memperbaiki PDF…',    pct: 18, ms: 3000 },
            { label: 'Mendeteksi teks & tabel PDF…',      pct: 30, ms: 4000 },
            { label: 'LibreOffice memproses PDF → ODT…',  pct: 50, ms: 8000 },
            { label: 'Mengkonversi ODT → DOCX…',          pct: 72, ms: 5000 },
            { label: 'Memvalidasi format output…',        pct: 82, ms: 2000 },
        ],
        pdf_to_excel: [
            { label: 'Mengupload file ke server…',        pct: 8,  ms: 1500 },
            { label: 'Memvalidasi & memperbaiki PDF…',    pct: 18, ms: 3000 },
            { label: 'Mendeteksi struktur tabel…',        pct: 32, ms: 4000 },
            { label: 'LibreOffice memproses PDF → ODS…',  pct: 55, ms: 9000 },
            { label: 'Mengkonversi ke XLSX…',             pct: 72, ms: 5000 },
            { label: 'Memvalidasi format output…',        pct: 82, ms: 2000 },
        ],
        pdf_to_ppt: [
            { label: 'Mengupload file ke server…',        pct: 8,  ms: 1500 },
            { label: 'Memvalidasi PDF…',                  pct: 18, ms: 2000 },
            { label: 'Menganalisis slide PDF…',           pct: 32, ms: 4000 },
            { label: 'LibreOffice memproses slide…',      pct: 58, ms: 10000 },
            { label: 'Mengkonversi ke PPTX…',             pct: 72, ms: 5000 },
            { label: 'Memvalidasi format output…',        pct: 82, ms: 2000 },
        ],
        word_to_pdf: [
            { label: 'Mengupload file ke server…',        pct: 10, ms: 1200 },
            { label: 'Membuka dokumen Word…',             pct: 30, ms: 3000 },
            { label: 'LibreOffice mengrender ke PDF…',    pct: 65, ms: 5000 },
            { label: 'Menyimpan hasil PDF…',              pct: 82, ms: 2000 },
        ],
        excel_to_pdf: [
            { label: 'Mengupload file ke server…',        pct: 10, ms: 1200 },
            { label: 'Membuka spreadsheet…',              pct: 30, ms: 3000 },
            { label: 'LibreOffice mengrender ke PDF…',    pct: 65, ms: 5000 },
            { label: 'Menyimpan hasil PDF…',              pct: 82, ms: 2000 },
        ],
        ppt_to_pdf: [
            { label: 'Mengupload file ke server…',        pct: 10, ms: 1200 },
            { label: 'Membuka presentasi…',               pct: 30, ms: 3000 },
            { label: 'LibreOffice mengrender slide…',     pct: 65, ms: 6000 },
            { label: 'Menyimpan hasil PDF…',              pct: 82, ms: 2000 },
        ],
        pdf_to_jpg: [
            { label: 'Mengupload file ke server…',        pct: 10, ms: 1200 },
            { label: 'Ghostscript memroses PDF…',         pct: 35, ms: 4000 },
            { label: 'Mengkonversi halaman ke JPG…',      pct: 65, ms: 5000 },
            { label: 'Menyimpan gambar output…',          pct: 82, ms: 2000 },
        ],
        pdf_to_png: [
            { label: 'Mengupload file ke server…',        pct: 10, ms: 1200 },
            { label: 'Ghostscript memroses PDF…',         pct: 35, ms: 4000 },
            { label: 'Mengkonversi halaman ke PNG…',      pct: 65, ms: 5000 },
            { label: 'Menyimpan gambar output…',          pct: 82, ms: 2000 },
        ],
        _default: [
            { label: 'Mengupload file ke server…',        pct: 12, ms: 1200 },
            { label: 'Memproses file…',                   pct: 40, ms: 2000 },
            { label: 'Mengkonversi format…',              pct: 72, ms: 2000 },
            { label: 'Menyimpan hasil…',                  pct: 82, ms: 1000 },
        ],
    };

    /* =========================================================
       CONVERSION CONFIG
    ========================================================= */
    const TYPE_CONFIG = {
        jpg_to_pdf:   { accept: 'image/jpeg,image/jpg,.jpg,.jpeg', hint: 'JPG, JPEG', label: 'Konversi JPG → PDF',   server: true },
        png_to_pdf:   { accept: 'image/png,.png',                  hint: 'PNG',       label: 'Konversi PNG → PDF',   server: true },
        webp_to_pdf:  { accept: 'image/webp,.webp',                hint: 'WEBP',      label: 'Konversi WebP → PDF',  server: true },
        word_to_pdf:  { accept: '.doc,.docx',                      hint: 'DOC, DOCX', label: 'Konversi Word → PDF',  server: true },
        excel_to_pdf: { accept: '.xls,.xlsx',                      hint: 'XLS, XLSX', label: 'Konversi Excel → PDF', server: true },
        ppt_to_pdf:   { accept: '.ppt,.pptx',                      hint: 'PPT, PPTX', label: 'Konversi PPT → PDF',   server: true },
        pdf_to_word:  { accept: 'application/pdf,.pdf', hint: 'PDF', label: 'Konversi PDF → Word',  server: true },
        pdf_to_excel: { accept: 'application/pdf,.pdf', hint: 'PDF', label: 'Konversi PDF → Excel', server: true },
        pdf_to_ppt:   { accept: 'application/pdf,.pdf', hint: 'PDF', label: 'Konversi PDF → PPT',   server: true },
        pdf_to_jpg:   { accept: 'application/pdf,.pdf', hint: 'PDF', label: 'Konversi PDF → JPG',   server: true },
        pdf_to_png:   { accept: 'application/pdf,.pdf', hint: 'PDF', label: 'Konversi PDF → PNG',   server: true },
        jpg_to_png:   { accept: 'image/jpeg,image/jpg,.jpg,.jpeg', hint: 'JPG, JPEG', label: 'Konversi JPG → PNG',  server: true },
        png_to_jpg:   { accept: 'image/png,.png',                  hint: 'PNG',       label: 'Konversi PNG → JPG',  server: true },
        jpg_to_webp:  { accept: 'image/jpeg,image/jpg,.jpg,.jpeg', hint: 'JPG, JPEG', label: 'Konversi JPG → WebP', server: true },
        png_to_webp:  { accept: 'image/png,.png',                  hint: 'PNG',       label: 'Konversi PNG → WebP', server: true },
        webp_to_jpg:  { accept: 'image/webp,.webp',                hint: 'WEBP',      label: 'Konversi WebP → JPG', server: true },
        webp_to_png:  { accept: 'image/webp,.webp',                hint: 'WEBP',      label: 'Konversi WebP → PNG', server: true },
    };

    const ERROR_TIPS = {
        pdf_to_word:  'Jika masih gagal, pastikan: (1) PDF tidak terproteksi password, (2) LibreOffice terinstall, (3) Coba PDF yang lebih kecil.',
        pdf_to_excel: 'PDF dengan tabel kompleks mungkin perlu diekstrak manual. Coba PDF → JPG dulu untuk melihat isi halaman.',
        pdf_to_ppt:   'Pastikan PDF tidak dikunci atau terproteksi. Coba file PDF yang lebih sederhana.',
        word_to_pdf:  'Pastikan file Word tidak terproteksi password. Format yang didukung: .doc dan .docx.',
        excel_to_pdf: 'Pastikan file Excel tidak terproteksi. Format yang didukung: .xls dan .xlsx.',
        ppt_to_pdf:   'Pastikan file PowerPoint tidak terproteksi. Format: .ppt dan .pptx.',
    };

    const ICON_MAP = {
        pdf:  'fa-file-pdf',
        doc:  'fa-file-word',   docx: 'fa-file-word',
        xls:  'fa-file-excel',  xlsx: 'fa-file-excel',
        ppt:  'fa-file-powerpoint', pptx: 'fa-file-powerpoint',
        jpg:  'fa-image', jpeg: 'fa-image', png: 'fa-image', webp: 'fa-image',
    };

    const MULTIPAGE_TYPES = new Set(['pdf_to_jpg', 'pdf_to_png']);

    const OUTPUT_EXT_MAP = {
        jpg_to_pdf: 'pdf', png_to_pdf: 'pdf', webp_to_pdf: 'pdf',
        word_to_pdf: 'pdf', excel_to_pdf: 'pdf', ppt_to_pdf: 'pdf',
        pdf_to_word: 'docx', pdf_to_excel: 'xlsx', pdf_to_ppt: 'pptx',
        pdf_to_jpg: 'jpg', pdf_to_png: 'png',
        jpg_to_png: 'png', png_to_jpg: 'jpg',
        jpg_to_webp: 'webp', png_to_webp: 'webp',
        webp_to_jpg: 'jpg', webp_to_png: 'png',
    };

    /* =========================================================
       STATE
    ========================================================= */
    let selectedType  = null;
    let fileObjects   = [];
    let stageTimers   = [];    // replaces single progressTimer
    let isConverting  = false;
    let activeAbort   = null;
    let startTime     = null;

    /* =========================================================
       DOM
    ========================================================= */
    const $ = id => document.getElementById(id);

    const catBtns      = document.querySelectorAll('.fc-cat-btn');
    const typeGroups   = document.querySelectorAll('.fc-type-group');
    const typeBtns     = document.querySelectorAll('.fc-type-btn');
    const mainCard     = $('fc-main-card');
    const dropZone     = $('drop-zone');
    const fileInput    = $('file-input');
    const fileListEl   = $('file-list');
    const btnAddMore   = $('btn-add-more');
    const addCount     = $('add-count');
    const acceptedHint = $('accepted-hint');
    const btnConvert   = $('btn-convert');
    const btnConvLbl   = $('btn-convert-label');

    const stProc    = $('state-processing');
    const stResult  = $('state-result');
    const stError   = $('state-error');
    const procTitle = $('proc-title');
    const procSub   = $('proc-sub');
    const progBar   = $('progress-bar');

    const resultTitle    = $('result-title');
    const resultSub      = $('result-sub');
    const resultFilesEl  = $('result-files');
    const btnDownloadAll = $('btn-download-all');
    const btnReset       = $('btn-reset');
    const errorMsgEl     = $('error-msg');
    const btnRetry       = $('btn-retry');
    const toastEl        = $('fc-toast');
    const toastMsgEl     = $('fc-toast-msg');

    /* =========================================================
       HELPERS
    ========================================================= */
    const show = el => el && el.classList.remove('fc-hidden');
    const hide = el => el && el.classList.add('fc-hidden');

    function showToast(msg, isError = false, dur = 3500) {
        toastMsgEl.textContent    = msg;
        toastEl.style.borderColor = isError ? 'rgba(248,113,113,0.4)' : '';
        toastEl.style.color       = isError ? '#f87171' : '';
        toastEl.classList.add('show');
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => {
            toastEl.classList.remove('show');
            toastEl.style.borderColor = '';
            toastEl.style.color       = '';
        }, dur);
    }

    function formatSize(bytes) {
        if (bytes < 1024)    return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(2) + ' MB';
    }

    function formatMs(ms) {
        if (ms < 60000) return Math.round(ms / 1000) + ' detik';
        const m = Math.floor(ms / 60000);
        const s = Math.round((ms % 60000) / 1000);
        return s > 0 ? `${m} menit ${s} detik` : `${m} menit`;
    }

    function formatElapsed(ms) {
        if (ms < 1000) return '<1 detik';
        if (ms < 60000) return Math.round(ms / 1000) + ' detik';
        const m = Math.floor(ms / 60000);
        const s = Math.round((ms % 60000) / 1000);
        return `${m}:${String(s).padStart(2, '0')}`;
    }

    function getIcon(filename) {
        const ext = (filename.split('.').pop() || '').toLowerCase();
        return ICON_MAP[ext] || 'fa-file';
    }

    function hideAllStates() { hide(stProc); hide(stResult); hide(stError); }

    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /* =========================================================
       REALISTIC PROGRESS — Stage-based animation
       Each stage advances the bar to its target_pct over its duration_ms,
       then the next stage starts. This produces a much more realistic
       progress feel rather than random jitter.
    ========================================================= */
    function getStages(type, fileIndex, totalFiles) {
        const stages = PROGRESS_STAGES[type] || PROGRESS_STAGES._default;
        // If processing multiple files, scale each stage to a segment of the total
        if (totalFiles > 1) {
            const segStart = (fileIndex / totalFiles) * 82;
            const segEnd   = ((fileIndex + 1) / totalFiles) * 82;
            return stages.map(s => ({
                label: s.label,
                pct:   segStart + (s.pct / 82) * (segEnd - segStart),
                ms:    s.ms,
            }));
        }
        return stages;
    }

    function clearStageTimers() {
        stageTimers.forEach(t => clearTimeout(t));
        stageTimers = [];
    }

    function runStageProgress(type, fileIndex, totalFiles, fileLabel) {
        clearStageTimers();
        const stages = getStages(type, fileIndex, totalFiles);
        let elapsed  = 0;

        stages.forEach((stage, i) => {
            const delay = elapsed;
            const t = setTimeout(() => {
                // Animate bar smoothly to target pct
                progBar.style.transition = `width ${Math.min(stage.ms * 0.8, 2000)}ms cubic-bezier(0.4, 0, 0.2, 1)`;
                progBar.style.width      = stage.pct + '%';

                // Update label with both stage info and elapsed time
                const elapsed_now = Date.now() - startTime;
                const suffix = elapsed_now > 3000
                    ? ` · ${formatElapsed(elapsed_now)} berlalu`
                    : '';
                procSub.textContent = `${stage.label}${suffix}`;

                if (totalFiles > 1) {
                    procTitle.textContent = `Mengkonversi file ${fileIndex + 1}/${totalFiles}: ${fileLabel}`;
                }
            }, delay);
            stageTimers.push(t);
            elapsed += stage.ms;
        });

        // Tick the elapsed time display every 2s for stages that take long
        const tickInterval = setInterval(() => {
            if (!isConverting) { clearInterval(tickInterval); return; }
            const elapsed_now = Date.now() - startTime;
            // Update subtitle if it's currently showing a stage label
            if (procSub.textContent.includes('·')) {
                procSub.textContent = procSub.textContent.replace(
                    /· .+ berlalu/,
                    `· ${formatElapsed(elapsed_now)} berlalu`
                );
            }
        }, 2000);
        stageTimers.push(tickInterval);
    }

    function finishProgress() {
        clearStageTimers();
        progBar.style.transition = 'width 0.4s ease';
        progBar.style.width      = '100%';
    }

    function resetProgress() {
        clearStageTimers();
        progBar.style.transition = 'none';
        progBar.style.width      = '0%';
    }

    /* =========================================================
       CATEGORY TABS
    ========================================================= */
    catBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            if (isConverting) return;
            catBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const cat = this.dataset.cat;
            typeGroups.forEach(g => g.dataset.cat === cat ? show(g) : hide(g));
            typeBtns.forEach(b => b.classList.remove('active'));
            selectedType = null;
            hide(mainCard);
            resetFiles();
        });
    });

    /* =========================================================
       TYPE SELECTION
    ========================================================= */
    typeBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            if (isConverting) return;
            selectedType = this.dataset.type;
            typeBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const cfg = TYPE_CONFIG[selectedType];
            if (!cfg) return;

            fileInput.accept         = cfg.accept;
            fileInput.multiple       = true;
            acceptedHint.textContent = 'Format: ' + cfg.hint;
            btnConvLbl.textContent   = cfg.label;

            updateTimeoutHint();
            updateSelectedPill();

            show(mainCard);
            mainCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            resetFiles();
        });
    });

    function updateSelectedPill() {
        let pill = document.getElementById('fc-selected-pill');
        if (!pill) {
            pill           = document.createElement('div');
            pill.id        = 'fc-selected-pill';
            pill.className = 'fc-selected-pill';
            const stepUpload = document.getElementById('step-upload');
            if (stepUpload) stepUpload.insertBefore(pill, stepUpload.firstChild);
        }
        const cfg = selectedType ? TYPE_CONFIG[selectedType] : null;
        if (cfg) {
            pill.style.display = 'inline-flex';
            pill.innerHTML     = `<i class="fa-solid fa-bolt"></i> ${cfg.label}`;
        } else {
            pill.style.display = 'none';
        }
    }

    function updateTimeoutHint() {
        const hintEl = document.querySelector('#state-processing p:last-of-type');
        if (!hintEl || !selectedType) return;
        const t = getTimeout(selectedType);
        const label = selectedType.startsWith('pdf_to_')
            ? `PDF conversion memerlukan waktu hingga ${formatMs(t)} — proses berjalan di server`
            : `Proses maksimal ${formatMs(t)}`;
        hintEl.innerHTML = `<i class="fa-solid fa-clock"></i> ${label}`;
    }

    /* =========================================================
       FILE HANDLING
    ========================================================= */
    fileInput.addEventListener('change', function () {
        addFiles(Array.from(this.files));
        this.value = '';
    });

    dropZone.addEventListener('dragover', e => {
        if (isConverting) return;
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });
    dropZone.addEventListener('dragleave', e => {
        if (isConverting) return;
        if (!dropZone.contains(e.relatedTarget)) dropZone.classList.remove('drag-over');
    });
    dropZone.addEventListener('drop', e => {
        if (isConverting) return;
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        addFiles(Array.from(e.dataTransfer.files));
    });
    dropZone.addEventListener('click', e => {
        if (isConverting) return;
        if (e.target === fileInput) return;
        fileInput.click();
    });
    dropZone.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); fileInput.click(); }
    });

    document.addEventListener('paste', e => {
        if (isConverting) return;
        if (!mainCard || mainCard.classList.contains('fc-hidden')) return;
        const items = e.clipboardData?.items;
        if (!items) return;
        const files = [];
        for (const item of items) {
            if (item.kind === 'file') { const f = item.getAsFile(); if (f) files.push(f); }
        }
        if (files.length) addFiles(files);
    });

    btnAddMore.addEventListener('click', () => fileInput.click());

    function addFiles(newFiles) {
        let added = 0;
        for (const f of newFiles) {
            if (fileObjects.length >= MAX_FILES) {
                showToast(`Maksimal ${MAX_FILES} file sekaligus.`, true);
                break;
            }
            if (f.size > MAX_SIZE) {
                showToast(`"${f.name}" terlalu besar. Maks ${formatSize(MAX_SIZE)}.`, true);
                continue;
            }
            fileObjects.push({ file: f, id: Date.now() + Math.random(), status: 'pending', resultFiles: [] });
            added++;
        }
        renderFileList();
        validateForm();
        if (added > 0 && fileObjects.length === 1) {
            setTimeout(() => btnConvert.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 300);
        }
    }

    function removeFile(id) {
        fileObjects = fileObjects.filter(o => o.id !== id);
        renderFileList();
        validateForm();
    }

    function resetFiles() {
        fileObjects = [];
        renderFileList();
        validateForm();
        hideAllStates();
        resetProgress();
    }

    function renderFileList() {
        fileListEl.innerHTML = '';
        if (!fileObjects.length) { hide(btnAddMore); return; }

        fileObjects.forEach(obj => {
            const statusLabels = { pending: 'Menunggu', processing: 'Memproses…', done: 'Selesai ✓', error: 'Gagal ✗' };
            const row          = document.createElement('div');
            row.className      = 'fc-file-row';
            row.dataset.id     = obj.id;

            const displayName = obj.status === 'done' && selectedType
                ? buildDisplayName(obj.file.name, OUTPUT_EXT_MAP[selectedType] || '')
                : obj.file.name;

            row.innerHTML = `
                <div class="fc-file-row-icon">
                    <i class="fa-solid ${getIcon(displayName)}"></i>
                </div>
                <div class="fc-file-row-info">
                    <div class="fc-file-row-name" title="${escHtml(displayName)}">${escHtml(displayName)}</div>
                    <div class="fc-file-row-size">${formatSize(obj.file.size)}</div>
                </div>
                <span class="fc-file-row-status ${obj.status}">${statusLabels[obj.status] || ''}</span>
                ${obj.status === 'pending'
                    ? `<button class="fc-file-row-remove" data-id="${obj.id}" aria-label="Hapus file">
                           <i class="fa-solid fa-xmark"></i>
                       </button>`
                    : ''}
            `;
            fileListEl.appendChild(row);
        });

        fileListEl.querySelectorAll('.fc-file-row-remove').forEach(btn => {
            btn.addEventListener('click', () => removeFile(parseFloat(btn.dataset.id)));
        });

        if (fileObjects.length < MAX_FILES) {
            show(btnAddMore);
            addCount.textContent = `${fileObjects.length}/${MAX_FILES}`;
        } else {
            hide(btnAddMore);
        }
    }

    function buildDisplayName(originalName, targetExt) {
        if (!targetExt) return originalName;
        const base = originalName.replace(/\.[^.]+$/, '');
        return `${base} by MediaTools.${targetExt}`;
    }

    function updateFileStatus(id, status) {
        const obj = fileObjects.find(o => o.id === id);
        if (obj) obj.status = status;

        const row = fileListEl.querySelector(`[data-id="${id}"]`);
        if (!row) return;

        const badge  = row.querySelector('.fc-file-row-status');
        const labels = { pending: 'Menunggu', processing: 'Memproses…', done: 'Selesai ✓', error: 'Gagal ✗' };
        if (badge) { badge.className = `fc-file-row-status ${status}`; badge.textContent = labels[status] || ''; }

        if (status !== 'pending') {
            row.querySelector('.fc-file-row-remove')?.remove();
        }
    }

    function validateForm() {
        btnConvert.disabled = !selectedType || fileObjects.length === 0;
    }

    function setProcessingUI(isBusy) {
        document.body.classList.toggle('fc-processing', isBusy);
        if (dropZone)   dropZone.style.pointerEvents = isBusy ? 'none' : '';
        if (fileInput)  fileInput.disabled   = isBusy;
        if (btnAddMore) btnAddMore.disabled  = isBusy;
        catBtns.forEach(b => b.disabled = isBusy);
        typeBtns.forEach(b => b.disabled = isBusy);
        document.querySelectorAll('.fc-file-row-remove').forEach(b => b.disabled = isBusy);
    }

    /* =========================================================
       CONVERT — with realistic stage-based progress
    ========================================================= */
    btnConvert.addEventListener('click', async function () {
        if (!selectedType || !fileObjects.length || isConverting) return;

        isConverting = true;
        startTime    = Date.now();
        setProcessingUI(true);
        hideAllStates();
        resetProgress();
        show(stProc);
        btnConvert.disabled = true;

        updateTimeoutHint();

        const total      = fileObjects.length;
        const allResults = [];

        procTitle.textContent = total > 1
            ? `Mengkonversi ${total} file…`
            : `Mengkonversi: ${fileObjects[0].file.name}`;
        procSub.textContent = 'Memulai proses…';

        // Start stage progress for first file
        runStageProgress(selectedType, 0, total, fileObjects[0]?.file.name || '');

        try {
            for (let i = 0; i < fileObjects.length; i++) {
                const obj     = fileObjects[i];
                const timeout = getTimeout(selectedType);

                updateFileStatus(obj.id, 'processing');

                // Switch stage progress to this file
                if (i > 0) {
                    runStageProgress(selectedType, i, total, obj.file.name);
                }

                try {
                    const formData = new FormData();
                    formData.append('file', obj.file);
                    formData.append('conversion_type', selectedType);
                    formData.append('_token', csrfToken());

                    const controller = new AbortController();
                    activeAbort      = controller;
                    const timeoutId  = setTimeout(() => controller.abort(), timeout);

                    const res = await fetch('/file-converter/process', {
                        method:  'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
                        body:    formData,
                        signal:  controller.signal,
                    });

                    clearTimeout(timeoutId);
                    activeAbort = null;

                    let data;
                    try { data = await res.json(); }
                    catch { throw new Error('Respons server tidak valid / bukan JSON.'); }

                    if (!res.ok || !data.success) {
                        throw new Error(data.message || `Server error (HTTP ${res.status})`);
                    }

                    updateFileStatus(obj.id, 'done');
                    obj.resultFiles = Array.isArray(data.files) ? data.files : [];
                    allResults.push({ originalName: obj.file.name, files: obj.resultFiles });

                    // Per-file success: show brief label
                    if (total > 1) {
                        procSub.textContent = `File ${i + 1}/${total} selesai ✓ (${formatElapsed(Date.now() - startTime)})`;
                    }

                } catch (err) {
                    let msg = 'Konversi gagal.';
                    if (err.name === 'AbortError') {
                        const t = formatMs(timeout);
                        msg = `Timeout setelah ${t}. File terlalu besar, kompleks, atau LibreOffice tidak responsif.`;
                        if (['pdf_to_word', 'pdf_to_excel', 'pdf_to_ppt'].includes(selectedType)) {
                            msg += ' Coba file yang lebih kecil, atau pastikan LibreOffice terinstall.';
                        }
                    } else if (err.message) {
                        msg = err.message;
                    }

                    updateFileStatus(obj.id, 'error');
                    allResults.push({ originalName: obj.file.name, files: [], error: msg });
                    console.error('Convert error:', obj.file.name, err);
                }
            }

            // All done
            clearStageTimers();
            const totalElapsed = Date.now() - startTime;
            finishProgress();

            procSub.textContent = `Selesai dalam ${formatElapsed(totalElapsed)}`;
            await delay(400); // small pause to let bar reach 100%

            const successCount = allResults.filter(r => r.files.length > 0).length;
            if (successCount === 0) {
                showError(allResults.find(r => r.error)?.error || 'Semua file gagal dikonversi.');
            } else {
                showResults(allResults, total, successCount, totalElapsed);
            }

        } catch (fatalErr) {
            console.error('Fatal error:', fatalErr);
            showError(fatalErr.message || 'Terjadi kesalahan sistem.');
        } finally {
            isConverting = false;
            activeAbort  = null;
            setProcessingUI(false);
            btnConvert.disabled = fileObjects.length === 0;
            setTimeout(() => { if (!isConverting) resetProgress(); }, 1200);
        }
    });

    function delay(ms) { return new Promise(res => setTimeout(res, ms)); }

    /* =========================================================
       SHOW RESULTS
    ========================================================= */
    function showResults(allResults, total, successCount, elapsedMs) {
        hideAllStates();
        show(stResult);
        resultFilesEl.innerHTML = '';

        const timeLabel = elapsedMs ? ` dalam ${formatElapsed(elapsedMs)}` : '';
        resultTitle.textContent = successCount === total
            ? `${total} File Berhasil Dikonversi${timeLabel}!`
            : `${successCount} dari ${total} File Berhasil`;

        const isMultiPage = MULTIPAGE_TYPES.has(selectedType);
        resultSub.textContent = isMultiPage
            ? 'Setiap halaman PDF tersimpan sebagai file gambar terpisah'
            : 'Klik Download di setiap file untuk mengunduh';

        const allOutputFiles = [];

        allResults.forEach(result => {
            if (result.files.length === 0) {
                const row = document.createElement('div');
                row.className = 'fc-result-file-row';
                row.style.borderColor = 'rgba(248,113,113,0.3)';
                row.innerHTML = `
                    <div class="fc-result-file-icon" style="background:rgba(248,113,113,0.1);color:#f87171;">
                        <i class="fa-solid fa-xmark"></i>
                    </div>
                    <div class="fc-result-file-info">
                        <div class="fc-result-file-name">${escHtml(result.originalName)}</div>
                        <div class="fc-result-file-size" style="color:#f87171;">${escHtml(result.error || 'Gagal dikonversi')}</div>
                    </div>`;
                resultFilesEl.appendChild(row);
                return;
            }

            result.files.forEach((serverFilename) => {
                allOutputFiles.push(serverFilename);
                const downloadUrl = `/file-converter/download/${encodeURIComponent(serverFilename)}`;
                const ext         = (serverFilename.split('.').pop() || '').toUpperCase();

                const row = document.createElement('div');
                row.className = 'fc-result-file-row';
                row.innerHTML = `
                    <div class="fc-result-file-icon">
                        <i class="fa-solid ${getIcon(serverFilename)}"></i>
                    </div>
                    <div class="fc-result-file-info">
                        <div class="fc-result-file-name" title="${escHtml(serverFilename)}">${escHtml(serverFilename)}</div>
                        <div class="fc-result-file-size">${ext} · Siap diunduh</div>
                    </div>
                    <a href="${downloadUrl}"
                       download="${escHtml(serverFilename)}"
                       class="fc-result-file-dl"
                       onclick="this.innerHTML='<i class=\\'fa-solid fa-check\\'></i> Diunduh'">
                        <i class="fa-solid fa-download" style="font-size:10px"></i>
                        Download
                    </a>`;
                resultFilesEl.appendChild(row);
            });
        });

        if (allOutputFiles.length > 1) {
            show(btnDownloadAll);
            btnDownloadAll._files = allOutputFiles;
        } else {
            hide(btnDownloadAll);
        }

        showToast(`${successCount} file berhasil dikonversi!`);
    }

    /* =========================================================
       SHOW ERROR
    ========================================================= */
    function showError(msg) {
        clearStageTimers();
        hideAllStates();
        show(stError);

        let cleanMsg = msg
            .replace(/HOME=\S+/g, '')
            .replace(/XDG_\w+=\S+/g, '')
            .replace(/env:UserInstallation=\S+/g, '')
            .trim();
        cleanMsg = cleanMsg.length > 400 ? cleanMsg.slice(0, 400) + '…' : cleanMsg;
        errorMsgEl.textContent = cleanMsg;

        const existingTip = document.getElementById('fc-err-tip');
        if (existingTip) existingTip.remove();

        const tip = selectedType ? ERROR_TIPS[selectedType] : null;
        if (tip) {
            const tipEl       = document.createElement('div');
            tipEl.id          = 'fc-err-tip';
            tipEl.className   = 'fc-error-tip';
            tipEl.innerHTML   = `<i class="fa-solid fa-lightbulb"></i> ${escHtml(tip)}`;
            stError.appendChild(tipEl);
        }
    }

    /* =========================================================
       DOWNLOAD ALL (ZIP)
    ========================================================= */
    btnDownloadAll.addEventListener('click', async function () {
        const files = this._files || [];
        if (!files.length) return;

        const origHtml = this.innerHTML;
        this.disabled  = true;
        this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>Membuat ZIP…</span>';

        try {
            const zip = new JSZip();
            for (const filename of files) {
                const url = `/file-converter/download/${encodeURIComponent(filename)}`;
                const res = await fetch(url);
                if (!res.ok) continue;
                zip.file(filename, await res.blob());
            }

            const zipBlob = await zip.generateAsync({
                type: 'blob',
                compression: 'DEFLATE',
                compressionOptions: { level: 6 },
            });

            const blobUrl = URL.createObjectURL(zipBlob);
            const a       = document.createElement('a');
            a.href        = blobUrl;
            a.download    = `mediatools_converted_${Date.now()}.zip`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            setTimeout(() => URL.revokeObjectURL(blobUrl), 30_000);
            showToast('ZIP berhasil dibuat dan diunduh!');
        } catch {
            showToast('Gagal membuat ZIP. Download satu per satu.', true);
        }

        this.disabled  = false;
        this.innerHTML = origHtml;
    });

    /* =========================================================
       RESET
    ========================================================= */
    function doReset() {
        if (isConverting) return;
        clearStageTimers();
        hideAllStates();
        resetProgress();
        resetFiles();
        if (btnDownloadAll) btnDownloadAll._files = [];
    }

    btnReset  && btnReset.addEventListener('click',  doReset);
    btnRetry  && btnRetry.addEventListener('click',  doReset);

});