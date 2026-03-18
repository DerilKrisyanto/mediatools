'use strict';

// Load pdf-lib dari CDN
const PDF_LIB_CDN = 'https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js';

function loadScript(src) {
    return new Promise((resolve, reject) => {
        if (document.querySelector(`script[src="${src}"]`)) { resolve(); return; }
        const s = document.createElement('script');
        s.src = src; s.onload = resolve; s.onerror = reject;
        document.head.appendChild(s);
    });
}

document.addEventListener('DOMContentLoaded', function () {

    let currentFeature = null;
    let fileObjects = [];
    let dragSrcIdx = null;

    const featBtns       = document.querySelectorAll('.pdf-feat-btn');
    const selectedInput  = document.getElementById('selected-feature');
    const stepUpload     = document.getElementById('step-upload');
    const stepRange      = document.getElementById('step-range');
    const stepProcess    = document.getElementById('step-process');
    const uploadLabel    = document.getElementById('upload-label');
    const fileInput      = document.getElementById('pdf-files');
    const dropZone       = document.getElementById('drop-zone');
    const fileListEl     = document.getElementById('file-list');
    const fileLimitHint  = document.getElementById('file-limit-hint');
    const rangeFrom      = document.getElementById('range-from');
    const rangeTo        = document.getElementById('range-to');
    const btnProcess     = document.getElementById('btn-process');
    const btnProcessLbl  = document.getElementById('btn-process-label');
    const stateEmpty     = document.getElementById('state-empty');
    const stateActive    = document.getElementById('state-active');
    const stateProcessing= document.getElementById('state-processing');
    const stateResult    = document.getElementById('state-result');
    const activeIcon     = document.getElementById('active-icon');
    const activeLabel    = document.getElementById('active-label');
    const activeDesc     = document.getElementById('active-desc');
    const infoFileCount  = document.getElementById('info-file-count');
    const infoTotalSize  = document.getElementById('info-total-size');
    const infoStatus     = document.getElementById('info-status');
    const tipText        = document.getElementById('tip-text');
    const btnDownload    = document.getElementById('btn-download');
    const btnReset       = document.getElementById('btn-reset');
    const resultBadgeFeature = document.getElementById('result-badge-feature');
    const toast          = document.getElementById('pdf-toast');
    const toastIco       = document.getElementById('toast-ico');
    const toastType      = document.getElementById('toast-type');
    const toastMsg       = document.getElementById('toast-msg');

    const FEATURES = {
        merge: {
            icon: 'fa-layer-group', label: 'Merge PDF',
            desc: 'Upload multiple PDF files. Drag to reorder before merging.',
            tip: 'Drag files in the list below to reorder them.',
            multiple: true, showRange: false,
            processLabel: 'Merge Now',
            uploadLabel: '02 — Upload PDF Files (multiple)',
            limitHint: 'or click to browse · max 10 files · no size limit',
        },
        split: {
            icon: 'fa-scissors', label: 'Split PDF',
            desc: 'Upload one PDF and specify the page range to extract.',
            tip: 'Enter start and end page numbers to extract.',
            multiple: false, showRange: true,
            processLabel: 'Split Now',
            uploadLabel: '02 — Upload PDF File (single)',
            limitHint: 'or click to browse · single file',
        },
        compress: {
            icon: 'fa-file-zipper', label: 'Compress PDF',
            desc: 'Re-save PDF with optimized compression.',
            tip: 'Best results with text-heavy PDFs.',
            multiple: false, showRange: false,
            processLabel: 'Compress Now',
            uploadLabel: '02 — Upload PDF File (single)',
            limitHint: 'or click to browse · single file',
        },
    };

    /* ---- Helpers ---- */
    function show(el) { el.classList.remove('pdf-step-hidden'); }
    function hide(el) { el.classList.add('pdf-step-hidden'); }
    function formatSize(b) {
        if (b < 1024) return b + ' B';
        if (b < 1048576) return (b/1024).toFixed(1) + ' KB';
        return (b/1048576).toFixed(2) + ' MB';
    }
    function totalSize() { return fileObjects.reduce((a,o) => a + o.file.size, 0); }

    function showToast(type, message) {
        toast.classList.remove('toast-error');
        if (type === 'error') {
            toast.classList.add('toast-error');
            toastIco.className = 'fa-solid fa-circle-exclamation';
            toastType.textContent = 'Error';
        } else {
            toastIco.className = 'fa-solid fa-check';
            toastType.textContent = 'Success';
        }
        toastMsg.textContent = message;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 4000);
    }

    function showPanel(name) {
        [stateEmpty, stateActive, stateProcessing, stateResult].forEach(hide);
        ({ empty: stateEmpty, active: stateActive, processing: stateProcessing, result: stateResult })[name];
        show({ empty: stateEmpty, active: stateActive, processing: stateProcessing, result: stateResult }[name]);
    }

    function updateActivePanel() {
        if (!currentFeature) return;
        const cfg = FEATURES[currentFeature];
        activeIcon.className = 'fa-solid ' + cfg.icon;
        activeLabel.textContent = cfg.label;
        activeDesc.textContent = cfg.desc;
        tipText.textContent = cfg.tip;
        infoFileCount.textContent = fileObjects.length;
        infoTotalSize.textContent = fileObjects.length ? formatSize(totalSize()) : '0 KB';
        infoStatus.textContent = fileObjects.length ? 'Ready' : 'Waiting';
    }

    /* ---- Feature Select ---- */
    featBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            currentFeature = this.dataset.feature;
            selectedInput.value = currentFeature;
            featBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const cfg = FEATURES[currentFeature];
            fileInput.multiple = cfg.multiple;
            uploadLabel.textContent = cfg.uploadLabel;
            fileLimitHint.textContent = cfg.limitHint;
            show(stepUpload);
            cfg.showRange ? show(stepRange) : hide(stepRange);
            fileObjects = [];
            renderFileList();
            hide(stepProcess);
            showPanel('active');
            updateActivePanel();
            btnProcessLbl.textContent = cfg.processLabel;
        });
    });

    /* ---- File Input ---- */
    fileInput.addEventListener('change', function () {
        addFiles(Array.from(this.files));
        this.value = '';
    });

    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const files = Array.from(e.dataTransfer.files).filter(f => f.type === 'application/pdf');
        if (!files.length) { showToast('error', 'Please drop PDF files only.'); return; }
        addFiles(files);
    });

    function addFiles(newFiles) {
        const cfg = FEATURES[currentFeature];
        if (!cfg.multiple) {
            fileObjects = [{ file: newFiles[0], id: Date.now() }];
        } else {
            newFiles.forEach(f => {
                if (fileObjects.length >= 10) { showToast('error', 'Maximum 10 files.'); return; }
                fileObjects.push({ file: f, id: Date.now() + Math.random() });
            });
        }
        renderFileList();
        updateActivePanel();
        fileObjects.length ? show(stepProcess) : hide(stepProcess);
    }

    /* ---- File List ---- */
    function renderFileList() {
        fileListEl.innerHTML = '';
        if (!fileObjects.length) return;
        fileObjects.forEach((obj, idx) => {
            const row = document.createElement('div');
            row.className = 'pdf-file-row';
            row.draggable = true;
            row.dataset.idx = idx;
            row.innerHTML = `
                <span class="pdf-file-drag-handle"><i class="fa-solid fa-grip-vertical"></i></span>
                <span class="pdf-file-icon"><i class="fa-solid fa-file-pdf"></i></span>
                <span class="pdf-file-name" title="${obj.file.name}">${obj.file.name}</span>
                <span class="pdf-file-size">${formatSize(obj.file.size)}</span>
                <button class="pdf-file-remove" data-idx="${idx}"><i class="fa-solid fa-xmark"></i></button>
            `;
            row.addEventListener('dragstart', e => { dragSrcIdx = idx; row.classList.add('dragging'); e.dataTransfer.effectAllowed = 'move'; });
            row.addEventListener('dragend', () => row.classList.remove('dragging'));
            row.addEventListener('dragover', e => { e.preventDefault(); });
            row.addEventListener('drop', e => {
                e.preventDefault();
                if (dragSrcIdx === null || dragSrcIdx === idx) return;
                const moved = fileObjects.splice(dragSrcIdx, 1)[0];
                fileObjects.splice(idx, 0, moved);
                dragSrcIdx = null;
                renderFileList();
                updateActivePanel();
            });
            fileListEl.appendChild(row);
        });
        fileListEl.querySelectorAll('.pdf-file-remove').forEach(btn => {
            btn.addEventListener('click', function () {
                fileObjects.splice(parseInt(this.dataset.idx), 1);
                renderFileList();
                updateActivePanel();
                if (!fileObjects.length) hide(stepProcess);
            });
        });
    }

    /* ---- PROCESS (Browser-side dengan pdf-lib) ---- */
    btnProcess.addEventListener('click', async function () {
        if (!currentFeature || !fileObjects.length) return;
        btnProcess.disabled = true;
        showPanel('processing');

        try {
            await loadScript(PDF_LIB_CDN);
            const { PDFDocument } = PDFLib;
            let resultBytes;

            if (currentFeature === 'merge') {
                resultBytes = await mergePDFs(PDFDocument);
            } else if (currentFeature === 'split') {
                const from = Math.max(1, parseInt(rangeFrom.value) || 1);
                const to   = Math.max(from, parseInt(rangeTo.value) || 5);
                resultBytes = await splitPDF(PDFDocument, from, to);
            } else if (currentFeature === 'compress') {
                resultBytes = await compressPDF(PDFDocument);
            }

            // Buat Blob dan URL download
            const blob = new Blob([resultBytes], { type: 'application/pdf' });
            const url  = URL.createObjectURL(blob);
            const fname = `mediatools_${currentFeature}_${Date.now()}.pdf`;

            btnDownload.href = url;
            btnDownload.download = fname;
            btnDownload.target = '_self'; // download langsung, tidak buka tab baru

            resultBadgeFeature.textContent = currentFeature.charAt(0).toUpperCase() + currentFeature.slice(1);
            showPanel('result');
            showToast('success', 'File processed successfully!');

        } catch (err) {
            console.error(err);
            showPanel('active');
            updateActivePanel();
            showToast('error', err.message || 'Processing failed.');
        }

        btnProcess.disabled = false;
    });

    /* ---- pdf-lib Operations ---- */
    async function readFile(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload  = e => resolve(e.target.result);
            reader.onerror = reject;
            reader.readAsArrayBuffer(file);
        });
    }

    async function mergePDFs(PDFDocument) {
        const merged = await PDFDocument.create();
        for (const obj of fileObjects) {
            const bytes  = await readFile(obj.file);
            const srcDoc = await PDFDocument.load(bytes, { ignoreEncryption: true });
            const pages  = await merged.copyPages(srcDoc, srcDoc.getPageIndices());
            pages.forEach(p => merged.addPage(p));
        }
        return await merged.save();
    }

    async function splitPDF(PDFDocument, from, to) {
        const bytes  = await readFile(fileObjects[0].file);
        const srcDoc = await PDFDocument.load(bytes, { ignoreEncryption: true });
        const total  = srcDoc.getPageCount();

        // Clamp ke range valid (0-indexed)
        const start = Math.min(from - 1, total - 1);
        const end   = Math.min(to - 1, total - 1);

        const newDoc = await PDFDocument.create();
        const indices = [];
        for (let i = start; i <= end; i++) indices.push(i);

        const pages = await newDoc.copyPages(srcDoc, indices);
        pages.forEach(p => newDoc.addPage(p));
        return await newDoc.save();
    }

    async function compressPDF(PDFDocument) {
        const bytes  = await readFile(fileObjects[0].file);
        const srcDoc = await PDFDocument.load(bytes, { ignoreEncryption: true });
        const newDoc = await PDFDocument.create();
        const pages  = await newDoc.copyPages(srcDoc, srcDoc.getPageIndices());
        pages.forEach(p => newDoc.addPage(p));
        // pdf-lib secara default menggunakan kompresi deflate saat save
        return await newDoc.save({ useObjectStreams: true });
    }

    /* ---- Reset ---- */
    btnDownload.addEventListener('click', function () {
        // Bebaskan object URL setelah beberapa detik
        const url = this.href;
        setTimeout(() => URL.revokeObjectURL(url), 60000);
    });

    btnReset.addEventListener('click', function () {
        location.reload();
    });

});