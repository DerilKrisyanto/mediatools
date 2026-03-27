'use strict';

document.addEventListener('DOMContentLoaded', function () {

    const MAX_FILES = 5;
    const MAX_SIZE  = 50 * 1024 * 1024; // 50MB (sinkron dengan controller)

    /* =========================================================
       CONVERSION CONFIG
    ========================================================= */
    const TYPE_CONFIG = {
        // → PDF
        jpg_to_pdf:   { accept: 'image/jpeg,image/jpg,.jpg,.jpeg', hint: 'JPG, JPEG', label: 'Konversi JPG → PDF',  server: true  },
        png_to_pdf:   { accept: 'image/png,.png',                  hint: 'PNG',       label: 'Konversi PNG → PDF',  server: true  },
        word_to_pdf:  { accept: '.doc,.docx',                      hint: 'DOC, DOCX', label: 'Konversi Word → PDF', server: true  },
        excel_to_pdf: { accept: '.xls,.xlsx',                      hint: 'XLS, XLSX', label: 'Konversi Excel → PDF', server: true },
        ppt_to_pdf:   { accept: '.ppt,.pptx',                      hint: 'PPT, PPTX', label: 'Konversi PPT → PDF',  server: true  },
        // PDF →
        pdf_to_word:  { accept: 'application/pdf,.pdf', hint: 'PDF', label: 'Konversi PDF → Word',  server: true },
        pdf_to_excel: { accept: 'application/pdf,.pdf', hint: 'PDF', label: 'Konversi PDF → Excel', server: true },
        pdf_to_ppt:   { accept: 'application/pdf,.pdf', hint: 'PDF', label: 'Konversi PDF → PPT',   server: true },
        pdf_to_jpg:   { accept: 'application/pdf,.pdf', hint: 'PDF', label: 'Konversi PDF → JPG',   server: true },
        pdf_to_png:   { accept: 'application/pdf,.pdf', hint: 'PDF', label: 'Konversi PDF → PNG',   server: true },
        // Image ↔ Image
        jpg_to_png:   { accept: 'image/jpeg,image/jpg,.jpg,.jpeg', hint: 'JPG, JPEG', label: 'Konversi JPG → PNG',  server: true },
        png_to_jpg:   { accept: 'image/png,.png',                  hint: 'PNG',       label: 'Konversi PNG → JPG',  server: true },
        jpg_to_webp:  { accept: 'image/jpeg,image/jpg,.jpg,.jpeg', hint: 'JPG, JPEG', label: 'Konversi JPG → WebP', server: true },
        png_to_webp:  { accept: 'image/png,.png',                  hint: 'PNG',       label: 'Konversi PNG → WebP', server: true },
        webp_to_jpg:  { accept: 'image/webp,.webp',                hint: 'WEBP',      label: 'Konversi WebP → JPG', server: true },
        webp_to_png:  { accept: 'image/webp,.webp',                hint: 'WEBP',      label: 'Konversi WebP → PNG', server: true },
    };

    // Pesan error yang lebih informatif per tipe
    const ERROR_TIPS = {
        pdf_to_word:  'PDF berbasis scan/gambar tidak bisa langsung dikonversi ke Word. Coba PDF → JPG untuk mengekstrak isi halaman.',
        pdf_to_excel: 'PDF dengan tabel yang kompleks mungkin perlu diekstrak secara manual. Coba PDF → JPG terlebih dahulu.',
        pdf_to_ppt:   'PDF yang terproteksi password tidak dapat dikonversi. Pastikan PDF tidak dikunci.',
        word_to_pdf:  'Pastikan file Word tidak terproteksi password dan formatnya valid (.doc atau .docx).',
        excel_to_pdf: 'Pastikan file Excel tidak terproteksi password. Format yang didukung: .xls dan .xlsx.',
        ppt_to_pdf:   'Pastikan file PowerPoint tidak terproteksi. Format yang didukung: .ppt dan .pptx.',
    };

    const ICON_MAP = {
        pdf:  'fa-file-pdf',
        doc:  'fa-file-word',   docx: 'fa-file-word',
        xls:  'fa-file-excel',  xlsx: 'fa-file-excel',
        ppt:  'fa-file-powerpoint', pptx: 'fa-file-powerpoint',
        jpg:  'fa-image', jpeg: 'fa-image', png: 'fa-image', webp: 'fa-image',
    };

    // Label output yang lebih informatif untuk PDF multi-halaman
    const MULTIPAGE_TYPES = new Set(['pdf_to_jpg', 'pdf_to_png']);

    /* =========================================================
       STATE
    ========================================================= */
    let selectedType  = null;
    let fileObjects   = [];
    let progressTimer = null;
    let isConverting  = false;

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

    const stProc   = $('state-processing');
    const stResult = $('state-result');
    const stError  = $('state-error');
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

    function showToast(msg, isError = false, dur = 3000) {
        toastMsgEl.textContent = msg;
        toastEl.style.borderColor = isError ? 'rgba(248,113,113,0.4)' : '';
        toastEl.style.color = isError ? '#f87171' : '';
        toastEl.classList.add('show');
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => {
            toastEl.classList.remove('show');
            toastEl.style.borderColor = '';
            toastEl.style.color = '';
        }, dur);
    }

    function formatSize(bytes) {
        if (bytes < 1024)     return bytes + ' B';
        if (bytes < 1048576)  return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(2) + ' MB';
    }

    function getIcon(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        return ICON_MAP[ext] || 'fa-file';
    }

    function hideAllStates() {
        hide(stProc); hide(stResult); hide(stError);
    }

    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    /* Progress bar */
    function startProgress() {
        disableUI();
        progBar.style.width = '5%';
        progBar.style.transition = 'width 0.5s ease';
        let w = 5;
        progressTimer = setInterval(() => {
            const step = w < 35 ? 8 : w < 65 ? 3 : w < 82 ? 1 : 0;
            w = Math.min(w + step * Math.random(), 82);
            progBar.style.width = w + '%';
        }, 700);
    }

    function finishProgress() {
        enableUI();
        clearInterval(progressTimer);
        progBar.style.transition = 'width 0.3s ease';
        progBar.style.width = '100%';
    }

    function resetProgress() {
        clearInterval(progressTimer);
        progBar.style.transition = 'none';
        progBar.style.width = '0%';
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

            fileInput.accept   = cfg.accept;
            fileInput.multiple = true;
            acceptedHint.textContent = 'Format: ' + cfg.hint;
            btnConvLbl.textContent   = cfg.label;

            // Perbarui selected pill
            updateSelectedPill();

            show(mainCard);
            mainCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            resetFiles();
        });
    });

    function updateSelectedPill() {
        let pill = document.getElementById('fc-selected-pill');
        if (!pill) {
            pill = document.createElement('div');
            pill.id = 'fc-selected-pill';
            pill.className = 'fc-selected-pill';
            const stepUpload = document.getElementById('step-upload');
            if (stepUpload) stepUpload.insertBefore(pill, stepUpload.firstChild);
        }

        const cfg = selectedType ? TYPE_CONFIG[selectedType] : null;
        if (cfg) {
            pill.style.display = 'inline-flex';
            pill.innerHTML = `<i class="fa-solid fa-bolt"></i> ${cfg.label}`;
        } else {
            pill.style.display = 'none';
        }
    }

    /* =========================================================
       FILE HANDLING
    ========================================================= */
    fileInput.addEventListener('change', function () {
        addFiles(Array.from(this.files));
        this.value = '';
    });

    // Drag & drop
    dropZone.addEventListener('dragover', e => {
        if (isConverting) return;
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });
    dropZone.addEventListener('dragleave', e => {
        if (isConverting) return;
        if (!dropZone.contains(e.relatedTarget)) {
            dropZone.classList.remove('drag-over');
        }
    });
    dropZone.addEventListener('drop', e => {
        if (isConverting) return;
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        addFiles(Array.from(e.dataTransfer.files));
    });

    // Paste dari clipboard (Ctrl+V)
    document.addEventListener('paste', e => {
        if (isConverting) return;
        if (!mainCard || mainCard.classList.contains('fc-hidden')) return;
        const items = e.clipboardData?.items;
        if (!items) return;
        const files = [];
        for (const item of items) {
            if (item.kind === 'file') {
                const f = item.getAsFile();
                if (f) files.push(f);
            }
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
                showToast(`"${f.name}" terlalu besar. Maksimal ${formatSize(MAX_SIZE)}.`, true);
                continue;
            }
            fileObjects.push({
                file:        f,
                id:          Date.now() + Math.random(),
                status:      'pending',
                resultFiles: [],
            });
            added++;
        }
        renderFileList();
        validateForm();

        if (added > 0 && fileObjects.length === 1) {
            // Scroll ke convert button setelah file pertama dipilih
            setTimeout(() => {
                btnConvert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 300);
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
        if (!fileObjects.length) {
            hide(btnAddMore);
            return;
        }

        fileObjects.forEach(obj => {
            const statusLabels = { pending: 'Menunggu', processing: 'Proses...', done: 'Selesai', error: 'Gagal' };
            const row = document.createElement('div');
            row.className = 'fc-file-row';
            row.dataset.id = obj.id;
            row.innerHTML = `
                <div class="fc-file-row-icon">
                    <i class="fa-solid ${getIcon(obj.file.name)}"></i>
                </div>
                <div class="fc-file-row-info">
                    <div class="fc-file-row-name" title="${escHtml(obj.file.name)}">${escHtml(obj.file.name)}</div>
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

    function updateFileStatus(id, status) {
        const obj = fileObjects.find(o => o.id === id);
        if (obj) obj.status = status;

        const row = fileListEl.querySelector(`[data-id="${id}"]`);
        if (!row) return;

        const badge  = row.querySelector('.fc-file-row-status');
        const labels = { pending: 'Menunggu', processing: 'Proses...', done: 'Selesai', error: 'Gagal' };

        if (badge) {
            badge.className = `fc-file-row-status ${status}`;
            badge.textContent = labels[status] || '';
        }

        if (status !== 'pending') {
            const rem = row.querySelector('.fc-file-row-remove');
            if (rem) rem.remove();
        }
    }

    function validateForm() {
        btnConvert.disabled = !selectedType || fileObjects.length === 0;
    }

    function disableUI() {
        dropZone.style.pointerEvents = 'none';
        fileInput.disabled = true;
        btnAddMore.disabled = true;
    }

    function enableUI() {
        dropZone.style.pointerEvents = '';
        fileInput.disabled = false;
        btnAddMore.disabled = false;
    }



    /* =========================================================
       CONVERT — proses berurutan
    ========================================================= */
    btnConvert.addEventListener('click', async function () {
        if (!selectedType || !fileObjects.length || isConverting) return;

        isConverting = true;
        setProcessingUI(true);

        hideAllStates();
        resetProgress();
        show(stProc);

        btnConvert.disabled = true;
        startProgress();

        const total = fileObjects.length;
        const allResults = [];

        procTitle.textContent = `Mengkonversi ${total} file...`;
        procSub.textContent   = `Memulai proses...`;

        try {
            for (let i = 0; i < fileObjects.length; i++) {
                const obj = fileObjects[i];

                updateFileStatus(obj.id, 'processing');
                procSub.textContent = `Proses ${i + 1}/${total} — ${obj.file.name}`;

                // Progress per file (smooth increment)
                const pct = Math.round(((i + 0.3) / total) * 82);
                progBar.style.transition = 'width 0.4s ease';
                progBar.style.width = pct + '%';

                try {
                    const formData = new FormData();
                    formData.append('file', obj.file);
                    formData.append('conversion_type', selectedType);
                    formData.append('_token', csrfToken());

                    // Timeout dinamis (PDF → Office lebih lama)
                    const isHeavy = selectedType.startsWith('pdf_to_');
                    const timeout = isHeavy ? 180000 : 90000; // 3 menit vs 1.5 menit

                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), timeout);

                    const res = await fetch('/file-converter/process', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken(),
                            'Accept': 'application/json',
                        },
                        body: formData,
                        signal: controller.signal,
                    });

                    clearTimeout(timeoutId);

                    let data;
                    try {
                        data = await res.json();
                    } catch {
                        throw new Error('Respons server tidak valid / bukan JSON.');
                    }

                    if (!res.ok || !data.success) {
                        throw new Error(data.message || `Server error (HTTP ${res.status})`);
                    }

                    // SUCCESS
                    updateFileStatus(obj.id, 'done');

                    obj.resultFiles = Array.isArray(data.files) ? data.files : [];

                    allResults.push({
                        originalName: obj.file.name,
                        files: obj.resultFiles
                    });

                } catch (err) {
                    let msg = 'Konversi gagal.';

                    if (err.name === 'AbortError') {
                        msg = 'Timeout: proses terlalu lama (file terlalu besar / OCR berat).';
                    } else if (err.message) {
                        msg = err.message;
                    }

                    updateFileStatus(obj.id, 'error');

                    allResults.push({
                        originalName: obj.file.name,
                        files: [],
                        error: msg
                    });

                    console.error('Convert error:', obj.file.name, err);
                }
            }

            // Finish progress
            finishProgress();

            const successCount = allResults.filter(r => r.files.length > 0).length;

            if (successCount === 0) {
                showError(
                    allResults.find(r => r.error)?.error ||
                    'Semua file gagal dikonversi.'
                );
            } else {
                showResults(allResults, total, successCount);
            }

        } catch (fatalErr) {
            console.error('Fatal error:', fatalErr);

            showError(
                fatalErr.message ||
                'Terjadi kesalahan sistem saat memproses file.'
            );

        } finally {
            // 🔥 WAJIB: restore UI
            isConverting = false;
            setProcessingUI(false);

            btnConvert.disabled = fileObjects.length === 0;

            // Safety reset progress jika stuck
            setTimeout(() => {
                if (!isConverting) {
                    resetProgress();
                }
            }, 800);
        }
    });

    /* =========================================================
       SHOW RESULTS
    ========================================================= */
    function showResults(allResults, total, successCount) {
        hideAllStates();
        show(stResult);
        resultFilesEl.innerHTML = '';

        resultTitle.textContent = successCount === total
            ? `${total} File Berhasil Dikonversi!`
            : `${successCount} dari ${total} File Berhasil`;

        const isMultiPage = MULTIPAGE_TYPES.has(selectedType);
        resultSub.textContent = isMultiPage
            ? 'Setiap halaman PDF tersimpan sebagai file gambar terpisah'
            : 'Klik tombol Download di setiap file';

        const allOutputFiles = [];

        allResults.forEach(result => {
            if (result.files.length === 0) {
                // File gagal
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
                    </div>
                `;
                resultFilesEl.appendChild(row);
                return;
            }

            result.files.forEach((filename, pageIdx) => {
                allOutputFiles.push(filename);
                const fileUrl  = `/file-converter/download/${encodeURIComponent(filename)}`;
                const ext      = filename.split('.').pop().toUpperCase();
                const label    = isMultiPage
                    ? `${result.originalName} — Hal. ${pageIdx + 1}`
                    : `${result.originalName} → ${ext}`;

                const row = document.createElement('div');
                row.className = 'fc-result-file-row';
                row.innerHTML = `
                    <div class="fc-result-file-icon">
                        <i class="fa-solid ${getIcon(filename)}"></i>
                    </div>
                    <div class="fc-result-file-info">
                        <div class="fc-result-file-name" title="${escHtml(label)}">${escHtml(label)}</div>
                        <div class="fc-result-file-size">${ext} • Klik download</div>
                    </div>
                    <a href="${fileUrl}" download="${escHtml(filename)}"
                       class="fc-result-file-dl"
                       onclick="this.textContent='...'" >
                        <i class="fa-solid fa-download" style="font-size:10px"></i>
                        Download
                    </a>
                `;
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

    function showError(msg) {
        hideAllStates();
        show(stError);

        // Bersihkan pesan teknis
        let cleanMsg = msg;
        cleanMsg = cleanMsg.replace(/HOME=\S+/g, '').replace(/XDG_\w+=\S+/g, '').trim();
        cleanMsg = cleanMsg.length > 300 ? cleanMsg.slice(0, 300) + '…' : cleanMsg;

        errorMsgEl.textContent = cleanMsg;

        // Tampilkan tip spesifik per tipe
        const tip = selectedType ? ERROR_TIPS[selectedType] : null;
        const existingTip = document.getElementById('fc-err-tip');
        if (existingTip) existingTip.remove();

        if (tip) {
            const tipEl = document.createElement('div');
            tipEl.id = 'fc-err-tip';
            tipEl.className = 'fc-error-tip';
            tipEl.innerHTML = `<i class="fa-solid fa-lightbulb"></i> ${escHtml(tip)}`;
            stError.appendChild(tipEl);
        }
    }

    function setProcessingUI(isBusy) {
        document.body.classList.toggle('fc-processing', isBusy);

        if (dropZone) dropZone.style.pointerEvents = isBusy ? 'none' : '';
        if (fileInput) fileInput.disabled = isBusy;
        if (btnAddMore) btnAddMore.disabled = isBusy;

        catBtns.forEach(btn => btn.disabled = isBusy);
        typeBtns.forEach(btn => btn.disabled = isBusy);

        document.querySelectorAll('.fc-file-row-remove').forEach(btn => {
            btn.disabled = isBusy;
        });
    }

    /* =========================================================
       DOWNLOAD ALL (ZIP)
    ========================================================= */
    btnDownloadAll.addEventListener('click', async function () {
        const files = this._files || [];
        if (!files.length) return;

        const origHtml = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>Membuat ZIP...</span>';

        try {
            const zip = new JSZip();

            for (const filename of files) {
                const url = `/file-converter/download/${encodeURIComponent(filename)}`;
                const res = await fetch(url);
                if (!res.ok) continue;
                const blob = await res.blob();
                zip.file(filename, blob);
            }

            const zipBlob = await zip.generateAsync({
                type: 'blob',
                compression: 'DEFLATE',
                compressionOptions: { level: 6 },
            });

            const blobUrl = URL.createObjectURL(zipBlob);
            const a = document.createElement('a');
            a.href     = blobUrl;
            a.download = `mediatools_converted_${Date.now()}.zip`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            setTimeout(() => URL.revokeObjectURL(blobUrl), 30_000);

            showToast('ZIP berhasil dibuat dan diunduh!');
        } catch (err) {
            showToast('Gagal membuat ZIP. Silakan download satu per satu.', true);
        }

        this.disabled = false;
        this.innerHTML = origHtml;
    });

    /* =========================================================
       RESET
    ========================================================= */
    function doReset() {
        if (isConverting) return;
        hideAllStates();
        resetProgress();
        resetFiles();
        if (btnDownloadAll) btnDownloadAll._files = [];
    }

    btnReset && btnReset.addEventListener('click', doReset);
    btnRetry && btnRetry.addEventListener('click', doReset);

    /* =========================================================
       UTILITY
    ========================================================= */
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
});