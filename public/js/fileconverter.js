'use strict';

document.addEventListener('DOMContentLoaded', function () {

    const MAX_FILES = 5;
    const MAX_SIZE  = 10 * 1024 * 1024; // 10MB

    /* =========================================================
       CONVERSION CONFIG
    ========================================================= */
    const TYPE_CONFIG = {
        // → PDF
        jpg_to_pdf:   { accept: 'image/jpeg,image/jpg',   hint: 'JPG, JPEG',  multi: true,  label: 'Konversi JPG → PDF'   },
        png_to_pdf:   { accept: 'image/png',              hint: 'PNG',        multi: true,  label: 'Konversi PNG → PDF'   },
        word_to_pdf:  { accept: '.doc,.docx',             hint: 'DOC, DOCX',  multi: true,  label: 'Konversi Word → PDF'  },
        excel_to_pdf: { accept: '.xls,.xlsx',             hint: 'XLS, XLSX',  multi: true,  label: 'Konversi Excel → PDF' },
        ppt_to_pdf:   { accept: '.ppt,.pptx',             hint: 'PPT, PPTX',  multi: true,  label: 'Konversi PPT → PDF'   },
        // PDF →
        pdf_to_word:  { accept: 'application/pdf,.pdf',   hint: 'PDF',        multi: true,  label: 'Konversi PDF → Word'  },
        pdf_to_excel: { accept: 'application/pdf,.pdf',   hint: 'PDF',        multi: true,  label: 'Konversi PDF → Excel' },
        pdf_to_ppt:   { accept: 'application/pdf,.pdf',   hint: 'PDF',        multi: true,  label: 'Konversi PDF → PPT'   },
        pdf_to_jpg:   { accept: 'application/pdf,.pdf',   hint: 'PDF',        multi: true,  label: 'Konversi PDF → JPG'   },
        pdf_to_png:   { accept: 'application/pdf,.pdf',   hint: 'PDF',        multi: true,  label: 'Konversi PDF → PNG'   },
        // Image
        jpg_to_png:   { accept: 'image/jpeg,image/jpg',   hint: 'JPG, JPEG',  multi: true,  label: 'Konversi JPG → PNG'   },
        png_to_jpg:   { accept: 'image/png',              hint: 'PNG',        multi: true,  label: 'Konversi PNG → JPG'   },
        jpg_to_webp:  { accept: 'image/jpeg,image/jpg',   hint: 'JPG, JPEG',  multi: true,  label: 'Konversi JPG → WebP'  },
        png_to_webp:  { accept: 'image/png',              hint: 'PNG',        multi: true,  label: 'Konversi PNG → WebP'  },
        webp_to_jpg:  { accept: 'image/webp,.webp',       hint: 'WEBP',       multi: true,  label: 'Konversi WebP → JPG'  },
        webp_to_png:  { accept: 'image/webp,.webp',       hint: 'WEBP',       multi: true,  label: 'Konversi WebP → PNG'  },
    };

    const ICON_MAP = {
        pdf: 'fa-file-pdf', doc: 'fa-file-word', docx: 'fa-file-word',
        xls: 'fa-file-excel', xlsx: 'fa-file-excel',
        ppt: 'fa-file-powerpoint', pptx: 'fa-file-powerpoint',
        jpg: 'fa-image', jpeg: 'fa-image', png: 'fa-image', webp: 'fa-image',
    };

    /* =========================================================
       STATE
    ========================================================= */
    let selectedType  = null;
    let fileObjects   = []; // { file, id, status, resultFiles }
    let progressTimer = null;

    /* =========================================================
       DOM
    ========================================================= */
    const catBtns       = document.querySelectorAll('.fc-cat-btn');
    const typeGroups    = document.querySelectorAll('.fc-type-group');
    const typeBtns      = document.querySelectorAll('.fc-type-btn');
    const mainCard      = document.getElementById('fc-main-card');
    const dropZone      = document.getElementById('drop-zone');
    const fileInput     = document.getElementById('file-input');
    const fileListEl    = document.getElementById('file-list');
    const btnAddMore    = document.getElementById('btn-add-more');
    const addCount      = document.getElementById('add-count');
    const acceptedHint  = document.getElementById('accepted-hint');
    const btnConvert    = document.getElementById('btn-convert');
    const btnConvertLbl = document.getElementById('btn-convert-label');

    const stateProcessing = document.getElementById('state-processing');
    const stateResult     = document.getElementById('state-result');
    const stateError      = document.getElementById('state-error');
    const procTitle       = document.getElementById('proc-title');
    const procSub         = document.getElementById('proc-sub');
    const progressBar     = document.getElementById('progress-bar');

    const resultTitle    = document.getElementById('result-title');
    const resultSub      = document.getElementById('result-sub');
    const resultFilesEl  = document.getElementById('result-files');
    const btnDownloadAll = document.getElementById('btn-download-all');
    const btnReset       = document.getElementById('btn-reset');
    const errorMsg       = document.getElementById('error-msg');
    const btnRetry       = document.getElementById('btn-retry');
    const toast          = document.getElementById('fc-toast');
    const toastMsg       = document.getElementById('fc-toast-msg');

    /* =========================================================
       HELPERS
    ========================================================= */
    const show = el => el.classList.remove('fc-hidden');
    const hide = el => el.classList.add('fc-hidden');

    function showToast(msg, dur = 2500) {
        toastMsg.textContent = msg;
        toast.classList.add('show');
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => toast.classList.remove('show'), dur);
    }

    function formatSize(b) {
        if (b < 1024) return b + ' B';
        if (b < 1048576) return (b/1024).toFixed(1) + ' KB';
        return (b/1048576).toFixed(2) + ' MB';
    }

    function getIcon(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        return ICON_MAP[ext] || 'fa-file';
    }

    function hideAllStates() {
        hide(stateProcessing); hide(stateResult); hide(stateError);
    }

    function startProgress() {
        progressBar.style.width = '5%';
        progressBar.style.transition = 'width 0.4s ease';
        let w = 5;
        progressTimer = setInterval(() => {
            const s = w < 40 ? 10 : w < 70 ? 5 : w < 88 ? 1.5 : 0;
            w = Math.min(w + s * Math.random(), 88);
            progressBar.style.width = w + '%';
        }, 600);
    }

    function finishProgress() {
        clearInterval(progressTimer);
        progressBar.style.transition = 'width 0.25s ease';
        progressBar.style.width = '100%';
    }

    function resetProgress() {
        clearInterval(progressTimer);
        progressBar.style.transition = 'none';
        progressBar.style.width = '0%';
    }

    /* =========================================================
       CATEGORY TABS
    ========================================================= */
    catBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            catBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const cat = this.dataset.cat;
            typeGroups.forEach(g => {
                g.dataset.cat === cat ? show(g) : hide(g);
            });
            // Reset selection
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
            selectedType = this.dataset.type;
            typeBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const cfg = TYPE_CONFIG[selectedType];
            fileInput.accept = cfg.accept;
            fileInput.multiple = true;
            acceptedHint.textContent = 'Format: ' + cfg.hint;
            btnConvertLbl.textContent = cfg.label;

            show(mainCard);
            mainCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            resetFiles();
        });
    });

    /* =========================================================
       FILE HANDLING
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
        addFiles(Array.from(e.dataTransfer.files));
    });

    btnAddMore.addEventListener('click', () => fileInput.click());

    function addFiles(newFiles) {
        for (const f of newFiles) {
            if (fileObjects.length >= MAX_FILES) {
                showToast(`Maksimal ${MAX_FILES} file.`);
                break;
            }
            if (f.size > MAX_SIZE) {
                showToast(`"${f.name}" terlalu besar. Maks. 10 MB.`);
                continue;
            }
            fileObjects.push({ file: f, id: Date.now() + Math.random(), status: 'pending', resultFiles: [] });
        }
        renderFileList();
        validateForm();
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
            const row = document.createElement('div');
            row.className = 'fc-file-row';
            row.dataset.id = obj.id;

            const statusLabel = { pending: 'Menunggu', processing: 'Proses...', done: 'Selesai', error: 'Gagal' }[obj.status] || '';

            row.innerHTML = `
                <div class="fc-file-row-icon"><i class="fa-solid ${getIcon(obj.file.name)}"></i></div>
                <div class="fc-file-row-info">
                    <div class="fc-file-row-name" title="${obj.file.name}">${obj.file.name}</div>
                    <div class="fc-file-row-size">${formatSize(obj.file.size)}</div>
                </div>
                <span class="fc-file-row-status ${obj.status}">${statusLabel}</span>
                ${obj.status === 'pending'
                    ? `<button class="fc-file-row-remove" data-id="${obj.id}"><i class="fa-solid fa-xmark"></i></button>`
                    : ''}
            `;
            fileListEl.appendChild(row);
        });

        fileListEl.querySelectorAll('.fc-file-row-remove').forEach(btn => {
            btn.addEventListener('click', () => removeFile(parseFloat(btn.dataset.id)));
        });

        // Show/hide "add more"
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
        // Update DOM langsung tanpa re-render seluruh list
        const row = fileListEl.querySelector(`[data-id="${id}"]`);
        if (row) {
            const badge = row.querySelector('.fc-file-row-status');
            if (badge) {
                const labels = { pending: 'Menunggu', processing: 'Proses...', done: 'Selesai', error: 'Gagal' };
                badge.className = `fc-file-row-status ${status}`;
                badge.textContent = labels[status] || '';
            }
            // Hapus tombol remove saat sudah diproses
            if (status !== 'pending') {
                const rem = row.querySelector('.fc-file-row-remove');
                if (rem) rem.remove();
            }
        }
    }

    function validateForm() {
        btnConvert.disabled = !selectedType || fileObjects.length === 0;
    }

    /* =========================================================
       CONVERT — proses satu per satu
    ========================================================= */
    btnConvert.addEventListener('click', async function () {
        if (!selectedType || !fileObjects.length) return;

        hideAllStates();
        resetProgress();
        show(stateProcessing);
        btnConvert.disabled = true;
        startProgress();

        const csrf = document.querySelector('meta[name="csrf-token"]');
        const total = fileObjects.length;
        let doneCount = 0;
        let allResults = []; // { originalName, resultFiles[] }

        procTitle.textContent = `Mengkonversi ${total} file...`;
        procSub.textContent   = `Memproses 0 / ${total} file`;

        for (let i = 0; i < fileObjects.length; i++) {
            const obj = fileObjects[i];
            updateFileStatus(obj.id, 'processing');
            procSub.textContent = `Memproses ${i + 1} / ${total} — ${obj.file.name}`;

            // Update progress bar per file
            progressBar.style.transition = 'width 0.3s ease';
            progressBar.style.width = Math.round(((i + 0.5) / total) * 88) + '%';

            const formData = new FormData();
            formData.append('file', obj.file);
            formData.append('conversion_type', selectedType);

            try {
                const res = await fetch('/file-converter/process', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf ? csrf.content : '',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Konversi gagal.');
                }

                updateFileStatus(obj.id, 'done');
                obj.resultFiles = data.files || [];
                allResults.push({ originalName: obj.file.name, files: obj.resultFiles });
                doneCount++;

            } catch (err) {
                updateFileStatus(obj.id, 'error');
                allResults.push({ originalName: obj.file.name, files: [], error: err.message });
            }
        }

        finishProgress();

        // Tampilkan hasil
        const successCount = allResults.filter(r => r.files.length > 0).length;

        if (successCount === 0) {
            // Semua gagal
            showError(allResults[0]?.error || 'Semua file gagal dikonversi.');
        } else {
            showResults(allResults, total, successCount);
        }

        btnConvert.disabled = false;
    });

    /* =========================================================
       SHOW RESULTS
    ========================================================= */
    function showResults(allResults, total, successCount) {
        hideAllStates();
        show(stateResult);
        resultFilesEl.innerHTML = '';

        resultTitle.textContent = successCount === total
            ? `${total} File Berhasil Dikonversi!`
            : `${successCount} dari ${total} File Berhasil`;

        resultSub.textContent = 'Klik tombol download di setiap file';

        // Kumpulkan semua file hasil untuk ZIP
        const allOutputFiles = [];

        allResults.forEach(result => {
            if (result.files.length === 0) {
                // File yang gagal
                const row = document.createElement('div');
                row.className = 'fc-result-file-row';
                row.style.borderColor = 'rgba(248,113,113,0.3)';
                row.innerHTML = `
                    <div class="fc-result-file-icon" style="background:rgba(248,113,113,0.1);color:#f87171;">
                        <i class="fa-solid fa-xmark"></i>
                    </div>
                    <div class="fc-result-file-info">
                        <div class="fc-result-file-name">${result.originalName}</div>
                        <div class="fc-result-file-size" style="color:#f87171;">${result.error || 'Gagal dikonversi'}</div>
                    </div>
                `;
                resultFilesEl.appendChild(row);
                return;
            }

            result.files.forEach(filename => {
                allOutputFiles.push(filename);
                const fileUrl = `/file-converter/download/${filename}`;
                const ext = filename.split('.').pop().toUpperCase();

                const row = document.createElement('div');
                row.className = 'fc-result-file-row';
                row.innerHTML = `
                    <div class="fc-result-file-icon">
                        <i class="fa-solid ${getIcon(filename)}"></i>
                    </div>
                    <div class="fc-result-file-info">
                        <div class="fc-result-file-name">${result.originalName} → ${ext}</div>
                        <div class="fc-result-file-size">${filename}</div>
                    </div>
                    <a href="${fileUrl}" download="${filename}" class="fc-result-file-dl">
                        <i class="fa-solid fa-download" style="font-size:10px"></i>
                        Download
                    </a>
                `;
                resultFilesEl.appendChild(row);
            });
        });

        // Tombol Download All ZIP — tampil jika > 1 output file
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
        show(stateError);
        errorMsg.textContent = msg || 'Terjadi kesalahan.';
    }

    /* =========================================================
       DOWNLOAD ALL ZIP
    ========================================================= */
    btnDownloadAll.addEventListener('click', async function () {
        const files = this._files || [];
        if (!files.length) return;

        this.disabled = true;
        this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>Membuat ZIP...</span>';

        try {
            const zip = new JSZip();
            for (const filename of files) {
                const res  = await fetch(`/file-converter/download/${filename}`);
                const blob = await res.blob();
                zip.file(filename, blob);
            }
            const zipBlob = await zip.generateAsync({ type: 'blob', compression: 'DEFLATE', compressionOptions: { level: 6 } });
            const url = URL.createObjectURL(zipBlob);
            const a   = document.createElement('a');
            a.href = url; a.download = `mediatools_converted_${Date.now()}.zip`;
            a.click();
            setTimeout(() => URL.revokeObjectURL(url), 10000);
            showToast('ZIP berhasil dibuat!');
        } catch {
            showToast('Gagal membuat ZIP. Download satu per satu.');
        }

        this.disabled = false;
        this.innerHTML = '<i class="fa-solid fa-file-zipper"></i><span>Download Semua (ZIP)</span>';
    });

    /* =========================================================
       RESET
    ========================================================= */
    function doReset() {
        hideAllStates();
        resetProgress();
        resetFiles();
        btnDownloadAll._files = [];
    }

    btnReset.addEventListener('click', doReset);
    btnRetry.addEventListener('click', doReset);
});