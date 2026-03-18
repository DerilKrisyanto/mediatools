'use strict';

document.addEventListener('DOMContentLoaded', function () {

    /* =========================================================
       STATE
    ========================================================= */
    let currentOp    = 'convert';
    let fileObjects  = [];       // { file, id, objectUrl, originalSize }
    let resultBlobs  = [];       // { blob, filename }
    let targetFmt    = 'image/jpeg';
    let compressQ    = 0.80;
    let convertQ     = 0.85;
    let resizeQ      = 0.90;
    let compressTarget = 80;
    let ratioLocked  = true;
    let srcRatio     = null;     // w/h of first image

    /* =========================================================
       DOM
    ========================================================= */
    const opBtns        = document.querySelectorAll('.ic-op-btn');
    const dropZone      = document.getElementById('drop-zone');
    const fileInput     = document.getElementById('img-input');
    const fileListEl    = document.getElementById('file-list');
    const stepProcess   = document.getElementById('step-process');
    const btnProcess    = document.getElementById('btn-process');
    const btnProcessLbl = document.getElementById('btn-process-label');

    // Panels
    const panelConvert  = document.getElementById('panel-convert');
    const panelCompress = document.getElementById('panel-compress');
    const panelResize   = document.getElementById('panel-resize');

    // Convert
    const fmtBtns       = document.querySelectorAll('.ic-fmt-btn');
    const qualitySlider = document.getElementById('quality-slider');
    const qualityVal    = document.getElementById('quality-val');

    // Compress
    const targetBtns    = document.querySelectorAll('.ic-target-btn');
    const cqSlider      = document.getElementById('compress-quality-slider');
    const cqVal         = document.getElementById('compress-quality-val');

    // Resize
    const resizeW       = document.getElementById('resize-w');
    const resizeH       = document.getElementById('resize-h');
    const lockBtn       = document.getElementById('lock-ratio');
    const lockIcon      = document.getElementById('lock-icon');
    const presetBtns    = document.querySelectorAll('.ic-preset');
    const rqSlider      = document.getElementById('resize-quality-slider');
    const rqVal         = document.getElementById('resize-quality-val');

    // States
    const stateEmpty      = document.getElementById('state-empty');
    const statePreview    = document.getElementById('state-preview');
    const stateProcessing = document.getElementById('state-processing');
    const stateResult     = document.getElementById('state-result');
    const previewGrid     = document.getElementById('preview-grid');
    const previewCount    = document.getElementById('preview-count');
    const progressBar     = document.getElementById('progress-bar');
    const procSub         = document.getElementById('proc-sub');

    // Result
    const resultSub       = document.getElementById('result-sub');
    const resultStats     = document.getElementById('result-stats');
    const btnDownloadAll  = document.getElementById('btn-download-all');
    const btnDownloadSingle = document.getElementById('btn-download-single');
    const btnReset        = document.getElementById('btn-reset');

    // Toast
    const toast    = document.getElementById('ic-toast');
    const toastIco = document.getElementById('toast-ico');
    const toastType= document.getElementById('toast-type');
    const toastMsg = document.getElementById('toast-msg');

    /* =========================================================
       HELPERS
    ========================================================= */
    const show = el => el.classList.remove('ic-hidden');
    const hide = el => el.classList.add('ic-hidden');

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(2) + ' MB';
    }

    function extFromMime(mime) {
        return { 'image/jpeg': 'jpg', 'image/png': 'png', 'image/webp': 'webp' }[mime] || 'jpg';
    }

    function showToast(type, msg) {
        toast.classList.remove('toast-error');
        if (type === 'error') {
            toast.classList.add('toast-error');
            toastIco.className = 'fa-solid fa-circle-exclamation';
            toastType.textContent = 'Error';
        } else {
            toastIco.className = 'fa-solid fa-check';
            toastType.textContent = 'Sukses';
        }
        toastMsg.textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 4000);
    }

    function showPanel(name) {
        [stateEmpty, statePreview, stateProcessing, stateResult].forEach(hide);
        ({ empty: stateEmpty, preview: statePreview, processing: stateProcessing, result: stateResult })[name];
        show({ empty: stateEmpty, preview: statePreview, processing: stateProcessing, result: stateResult }[name]);
    }

    /* =========================================================
       OPERATION TABS
    ========================================================= */
    opBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            currentOp = this.dataset.op;
            opBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            hide(panelConvert); hide(panelCompress); hide(panelResize);
            if (currentOp === 'convert')  show(panelConvert);
            if (currentOp === 'compress') show(panelCompress);
            if (currentOp === 'resize')   show(panelResize);

            updateProcessBtn();
        });
    });

    /* =========================================================
       FORMAT BUTTONS
    ========================================================= */
    fmtBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            targetFmt = this.dataset.fmt;
            fmtBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    /* =========================================================
       QUALITY SLIDERS
    ========================================================= */
    qualitySlider.addEventListener('input', function () {
        convertQ = this.value / 100;
        qualityVal.textContent = this.value + '%';
    });

    cqSlider.addEventListener('input', function () {
        compressQ = this.value / 100;
        cqVal.textContent = this.value + '%';
    });

    rqSlider.addEventListener('input', function () {
        resizeQ = this.value / 100;
        rqVal.textContent = this.value + '%';
    });

    /* =========================================================
       COMPRESS TARGET BUTTONS
    ========================================================= */
    targetBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            compressTarget = parseInt(this.dataset.target);
            compressQ = compressTarget / 100;
            cqSlider.value = compressTarget;
            cqVal.textContent = compressTarget + '%';
            targetBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    /* =========================================================
       RESIZE
    ========================================================= */
    lockBtn.addEventListener('click', function () {
        ratioLocked = !ratioLocked;
        this.classList.toggle('active', ratioLocked);
        lockIcon.className = ratioLocked ? 'fa-solid fa-link' : 'fa-solid fa-link-slash';
    });

    resizeW.addEventListener('input', function () {
        if (ratioLocked && srcRatio && this.value) {
            resizeH.value = Math.round(this.value / srcRatio);
        }
    });

    resizeH.addEventListener('input', function () {
        if (ratioLocked && srcRatio && this.value) {
            resizeW.value = Math.round(this.value * srcRatio);
        }
    });

    presetBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            resizeW.value = this.dataset.w;
            resizeH.value = this.dataset.h;
            ratioLocked = false;
            lockBtn.classList.remove('active');
            lockIcon.className = 'fa-solid fa-link-slash';
        });
    });

    /* =========================================================
       FILE INPUT / DROP
    ========================================================= */
    fileInput.addEventListener('change', function () {
        addFiles(Array.from(this.files));
        this.value = '';
    });

    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const imgs = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
        if (!imgs.length) { showToast('error', 'Hanya file gambar yang diterima.'); return; }
        addFiles(imgs);
    });

    async function addFiles(newFiles) {
        for (const f of newFiles) {
            if (fileObjects.length >= 10) { showToast('error', 'Maksimal 10 file.'); break; }
            const objectUrl = URL.createObjectURL(f);
            fileObjects.push({ file: f, id: Date.now() + Math.random(), objectUrl });
        }

        // Set aspect ratio dari file pertama
        if (fileObjects.length === 1) {
            srcRatio = await getImageRatio(fileObjects[0].file);
        }

        renderFileList();
        renderPreview();
        show(stepProcess);
        updateProcessBtn();
    }

    function getImageRatio(file) {
        return new Promise(resolve => {
            const img = new Image();
            img.onload = () => {
                resolve(img.width / img.height);
                URL.revokeObjectURL(img.src);
            };
            img.src = URL.createObjectURL(file);
        });
    }

    function renderFileList() {
        fileListEl.innerHTML = '';
        fileObjects.forEach((obj, idx) => {
            const row = document.createElement('div');
            row.className = 'ic-file-row';
            row.innerHTML = `
                <img src="${obj.objectUrl}" class="ic-file-thumb" alt="">
                <div class="ic-file-info">
                    <div class="ic-file-name" title="${obj.file.name}">${obj.file.name}</div>
                    <div class="ic-file-meta">${formatSize(obj.file.size)} · ${obj.file.type.split('/')[1].toUpperCase()}</div>
                </div>
                <button class="ic-file-remove" data-idx="${idx}"><i class="fa-solid fa-xmark"></i></button>
            `;
            fileListEl.appendChild(row);
        });

        fileListEl.querySelectorAll('.ic-file-remove').forEach(btn => {
            btn.addEventListener('click', function () {
                const i = parseInt(this.dataset.idx);
                URL.revokeObjectURL(fileObjects[i].objectUrl);
                fileObjects.splice(i, 1);
                renderFileList();
                renderPreview();
                if (!fileObjects.length) {
                    hide(stepProcess);
                    showPanel('empty');
                }
            });
        });
    }

    function renderPreview() {
        if (!fileObjects.length) { showPanel('empty'); return; }
        previewGrid.innerHTML = '';
        previewCount.textContent = fileObjects.length + ' file';
        fileObjects.forEach(obj => {
            const item = document.createElement('div');
            item.className = 'ic-prev-item';
            item.innerHTML = `
                <img src="${obj.objectUrl}" class="ic-prev-img" alt="">
                <div class="ic-prev-overlay">${obj.file.name}</div>
            `;
            previewGrid.appendChild(item);
        });
        showPanel('preview');
    }

    function updateProcessBtn() {
        const labels = { convert: 'Convert Sekarang', compress: 'Compress Sekarang', resize: 'Resize Sekarang' };
        btnProcessLbl.textContent = labels[currentOp] || 'Proses Sekarang';
    }

    /* =========================================================
       PROCESS
    ========================================================= */
    btnProcess.addEventListener('click', async function () {
        if (!fileObjects.length) return;
        btnProcess.disabled = true;
        resultBlobs = [];
        showPanel('processing');

        const total = fileObjects.length;
        let totalOriginal = 0;
        let totalResult = 0;

        for (let i = 0; i < total; i++) {
            const obj = fileObjects[i];
            procSub.textContent = `${i + 1} / ${total} file — ${obj.file.name}`;
            progressBar.style.width = ((i / total) * 100) + '%';

            try {
                const blob = await processImage(obj.file);
                const baseName = obj.file.name.replace(/\.[^.]+$/, '');
                let ext, mime;

                if (currentOp === 'convert') {
                    mime = targetFmt;
                    ext  = extFromMime(targetFmt);
                } else {
                    mime = 'image/jpeg';
                    ext  = 'jpg';
                }

                resultBlobs.push({ blob, filename: `${baseName}_${currentOp}.${ext}` });
                totalOriginal += obj.file.size;
                totalResult   += blob.size;

            } catch (err) {
                console.error('Error processing', obj.file.name, err);
                showToast('error', `Gagal memproses: ${obj.file.name}`);
            }
        }

        progressBar.style.width = '100%';

        // Tampilkan hasil
        const saved = totalOriginal > 0
            ? Math.round((1 - totalResult / totalOriginal) * 100)
            : 0;

        resultStats.innerHTML = `
            <span class="ic-stat-chip"><i class="fa-solid fa-images"></i> ${resultBlobs.length} file</span>
            <span class="ic-stat-chip"><i class="fa-solid fa-weight-hanging"></i> ${formatSize(totalResult)}</span>
            ${saved > 0 ? `<span class="ic-stat-chip"><i class="fa-solid fa-arrow-trend-down"></i> Hemat ${saved}%</span>` : ''}
        `;

        resultSub.textContent = resultBlobs.length === 1
            ? 'File siap diunduh.'
            : `${resultBlobs.length} file siap diunduh sebagai ZIP.`;

        if (resultBlobs.length === 1) {
            hide(btnDownloadAll);
            show(btnDownloadSingle);
            btnDownloadSingle.onclick = () => downloadFile(resultBlobs[0].blob, resultBlobs[0].filename);
        } else {
            show(btnDownloadAll);
            hide(btnDownloadSingle);
        }

        btnProcess.disabled = false;
        showPanel('result');
        showToast('success', `${resultBlobs.length} gambar berhasil diproses!`);
    });

    /* =========================================================
       IMAGE PROCESSING (Canvas API)
    ========================================================= */
    function processImage(file) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            const url = URL.createObjectURL(file);

            img.onload = () => {
                URL.revokeObjectURL(url);

                let targetW = img.width;
                let targetH = img.height;
                let quality = convertQ;
                let mime    = targetFmt;

                if (currentOp === 'compress') {
                    quality = compressQ;
                    mime    = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
                } else if (currentOp === 'resize') {
                    quality = resizeQ;
                    mime    = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
                    const w = parseInt(resizeW.value);
                    const h = parseInt(resizeH.value);
                    if (w && h) { targetW = w; targetH = h; }
                    else if (w) { targetH = Math.round(w / (img.width / img.height)); targetW = w; }
                    else if (h) { targetW = Math.round(h * (img.width / img.height)); targetH = h; }
                }

                const canvas = document.createElement('canvas');
                canvas.width  = targetW;
                canvas.height = targetH;
                const ctx = canvas.getContext('2d');

                // Smooth scaling
                ctx.imageSmoothingEnabled  = true;
                ctx.imageSmoothingQuality  = 'high';

                // White background for JPEG (no alpha)
                if (mime === 'image/jpeg') {
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, targetW, targetH);
                }

                ctx.drawImage(img, 0, 0, targetW, targetH);

                canvas.toBlob(blob => {
                    if (!blob) { reject(new Error('Canvas toBlob failed')); return; }
                    resolve(blob);
                }, mime, quality);
            };

            img.onerror = () => { URL.revokeObjectURL(url); reject(new Error('Failed to load image')); };
            img.src = url;
        });
    }

    /* =========================================================
       DOWNLOAD
    ========================================================= */
    function downloadFile(blob, filename) {
        const url = URL.createObjectURL(blob);
        const a   = document.createElement('a');
        a.href     = url;
        a.download = filename;
        a.click();
        setTimeout(() => URL.revokeObjectURL(url), 10000);
    }

    btnDownloadAll.addEventListener('click', async function () {
        if (!resultBlobs.length) return;
        this.disabled = true;
        this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>Membuat ZIP…</span>';

        try {
            const zip = new JSZip();
            resultBlobs.forEach(r => zip.file(r.filename, r.blob));
            const zipBlob = await zip.generateAsync({ type: 'blob', compression: 'DEFLATE', compressionOptions: { level: 6 } });
            downloadFile(zipBlob, `mediatools_${currentOp}_${Date.now()}.zip`);
        } catch (err) {
            showToast('error', 'Gagal membuat ZIP.');
        }

        this.disabled = false;
        this.innerHTML = '<i class="fa-solid fa-download"></i><span>Download Semua (ZIP)</span>';
    });

    /* =========================================================
       RESET
    ========================================================= */
    btnReset.addEventListener('click', function () {
        fileObjects.forEach(o => URL.revokeObjectURL(o.objectUrl));
        fileObjects  = [];
        resultBlobs  = [];
        fileListEl.innerHTML = '';
        previewGrid.innerHTML = '';
        hide(stepProcess);
        showPanel('empty');
        progressBar.style.width = '0%';
        btnProcess.disabled = false;
    });

    /* Init */
    updateProcessBtn();
});