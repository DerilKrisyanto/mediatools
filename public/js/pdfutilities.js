'use strict';

/* ============================================================
   PDF UTILITIES — MediaTools
   Merge / Split  → Client-side  (pdf-lib)
   Compress       → Server-side  (Ghostscript via XHR)
   Split-All      → Client-side  (pdf-lib + JSZip)
   ============================================================ */

const CDN = {
    pdfLib: 'https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js',
    jszip:  'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js',
};

function loadScript(src) {
    return new Promise((resolve, reject) => {
        if (document.querySelector(`script[src="${src}"]`)) return resolve();
        const s   = document.createElement('script');
        s.src     = src;
        s.onload  = resolve;
        s.onerror = () => reject(new Error('CDN yükleme hatası: ' + src));
        document.head.appendChild(s);
    });
}

/* ============================================================
   DOM REFERENCES
   ============================================================ */
const $  = id => document.getElementById(id);
const $$ = sel => document.querySelectorAll(sel);

// Panels
const panelFeature    = $('panelFeature');
const panelUpload     = $('panelUpload');
const panelConfig     = $('panelConfig');
const panelProcessing = $('panelProcessing');
const panelResult     = $('panelResult');
const allPanels       = [panelFeature, panelUpload, panelConfig, panelProcessing, panelResult];

// Upload panel
const fileInput     = $('pdfFiles');
const dropZone      = $('dropZone');
const fileListWrap  = $('fileListWrap');
const fileListEl    = $('fileList');
const fileCountEl   = $('fileCount');
const btnAddMore    = $('btnAddMore');
const btnToConfig   = $('btnToConfig');
const btnMergeProc  = $('btnMergeProcess');

// Config panel
const configSplit     = $('configSplit');
const configCompress  = $('configCompress');
const splitModeTabs   = $$('.pdf-mode-tab');
const splitRangeInput = $('splitRangeInput');
const rangeFrom       = $('rangeFrom');
const rangeTo         = $('rangeTo');
const splitPageCount  = $('splitPageCount');
const splitFileName   = $('splitFileName');
const compressFileName = $('compressFileName');
const compressFileSize = $('compressFileSize');
const compressLevels  = $$('.pdf-clevel');
const btnStartProc    = $('btnStartProcess');

// Processing panel
const procTitle      = $('procTitle');
const procSub        = $('procSub');
const progressWrap   = $('progressWrap');
const progressLbl    = $('progressLbl');
const progressPct    = $('progressPct');
const progressFill   = $('progressFill');
const ps1 = $('ps1'), ps2 = $('ps2'), ps3 = $('ps3'), ps4 = $('ps4');

// Result panel
const sizeCompare  = $('sizeCompare');
const scBefore     = $('scBefore');
const scAfter      = $('scAfter');
const scSaved      = $('scSaved');
const btnDownload  = $('btnDownload');
const btnDownloadWrap = $('btnDownloadWrap');
const resultSub    = $('resultSub');
const btnProcAgain = $('btnProcessAgain');
const btnChangeT   = $('btnChangeTool');

// Breadcrumb badges
const selectedBadgeText  = $('selectedBadgeText');
const selectedBadge2Text = $('selectedBadge2Text');
const selectedBadge2Icon = $('selectedBadge2Icon');
const configPanelTitle   = $('configPanelTitle');
const uploadPanelTitle   = $('uploadPanelTitle');
const btnNextLabel       = $('btnNextLabel');

// Stepper
const stepItems = $$('[data-step]');
const stepLines = $$('.pdf-step-line');

/* ============================================================
   STATE
   ============================================================ */
let state = {
    feature:      null,   // 'merge' | 'split' | 'compress'
    files:        [],     // File[]
    splitMode:    'range',// 'range' | 'all'
    compressLevel:'medium',
    totalPages:   0,
    resultUrl:    null,
    isZipResult:  false,
};

/* ============================================================
   UTILITIES
   ============================================================ */
function formatSize(bytes) {
    if (!bytes || bytes <= 0) return '—';
    if (bytes < 1024)       return bytes + ' B';
    if (bytes < 1_048_576)  return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1_048_576).toFixed(2) + ' MB';
}

function formatName(name, max = 38) {
    return name.length > max ? name.slice(0, max - 3) + '…' : name;
}

function showPanel(panel) {
    allPanels.forEach(p => p.classList.add('pdf-hidden'));
    panel.classList.remove('pdf-hidden');
}

function setStep(n) {
    stepItems.forEach(item => {
        const s = parseInt(item.dataset.step);
        item.classList.remove('active', 'done');
        if (s < n)  item.classList.add('done');
        if (s === n) item.classList.add('active');
    });
    stepLines.forEach((line, i) => {
        line.classList.toggle('done', i + 1 < n);
    });
}

function toast(msg, isError = false) {
    const el      = $('pdfToast');
    const ico     = $('pdfToastIco');
    const typeEl  = $('pdfToastType');
    const msgEl   = $('pdfToastMsg');
    el.classList.remove('toast-error', 'show');
    if (isError) {
        el.classList.add('toast-error');
        ico.className      = 'fa-solid fa-triangle-exclamation fa-xs';
        typeEl.textContent = 'Error';
    } else {
        ico.className      = 'fa-solid fa-check fa-xs';
        typeEl.textContent = 'Sukses';
    }
    msgEl.textContent = msg;
    void el.offsetWidth; // reflow
    el.classList.add('show');
    setTimeout(() => el.classList.remove('show'), 2800);
}

function updateProgress(pct, label) {
    if (progressFill)  progressFill.style.width = pct + '%';
    if (progressPct)   progressPct.textContent   = pct + '%';
    if (label && progressLbl) progressLbl.textContent = label;
}

function setStep4Sub(step, done = false) {
    [ps1, ps2, ps3, ps4].forEach((el, i) => {
        if (!el) return;
        el.classList.remove('active', 'done');
        if (i + 1 < step)  el.classList.add('done');
        if (i + 1 === step && !done) el.classList.add('active');
        if (done)          el.classList.add('done');
    });
}

/* ============================================================
   STEP 1 — FEATURE SELECT
   ============================================================ */
$$('.pdf-feat').forEach(btn => {
    btn.addEventListener('click', () => {
        state.feature = btn.dataset.feature;
        state.files   = [];
        renderFileList();
        fileListWrap.classList.add('pdf-hidden');

        // Adjust file input for multi/single
        fileInput.multiple = (state.feature === 'merge');
        fileInput.removeAttribute('value');

        // Upload panel labels
        const labels = {
            merge:    { badge: 'Merge PDF',    title: 'Upload file PDF yang ingin digabung', hint: 'Pilih beberapa file sekaligus · Urutan bisa diatur' },
            split:    { badge: 'Split PDF',    title: 'Upload file PDF yang ingin dipecah',  hint: 'Pilih 1 file PDF · Maks. 100MB' },
            compress: { badge: 'Compress PDF', title: 'Upload file PDF yang ingin dikompres',hint: 'Pilih 1 file PDF · Maks. 100MB' },
        };
        const l = labels[state.feature];
        selectedBadgeText.textContent         = l.badge;
        if (uploadPanelTitle) uploadPanelTitle.textContent = l.title;
        const dzHint = $('dzHint');
        if (dzHint) dzHint.textContent        = l.hint;

        // Button visibility on upload panel
        if (state.feature === 'merge') {
            btnMergeProc.classList.add('pdf-hidden');
            btnToConfig.classList.add('pdf-hidden');
            btnAddMore.classList.remove('pdf-hidden');
        } else {
            btnMergeProc.classList.add('pdf-hidden');
            btnToConfig.classList.add('pdf-hidden');
            btnAddMore.classList.add('pdf-hidden');
        }

        showPanel(panelUpload);
        setStep(2);
    });
});

/* ============================================================
   STEP 2 — UPLOAD
   ============================================================ */

// Click on drop zone (not on file input directly — avoids double-trigger)
dropZone.addEventListener('click', e => {
    if (e.target === fileInput) return;
    fileInput.click();
});

fileInput.addEventListener('change', e => {
    addFiles(Array.from(e.target.files));
    e.target.value = ''; // reset so same file can be re-added
});

// Drag & drop on the zone
dropZone.addEventListener('dragover', e => {
    e.preventDefault();
    dropZone.classList.add('drag-over');
});
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    const dropped = Array.from(e.dataTransfer.files).filter(f => f.type === 'application/pdf' || f.name.endsWith('.pdf'));
    if (!dropped.length) { toast('Hanya file PDF yang diterima.', true); return; }
    addFiles(dropped);
});

function addFiles(newFiles) {
    const maxSize = 100 * 1024 * 1024; // 100MB
    const valid   = newFiles.filter(f => {
        if (f.size > maxSize) { toast(`${f.name} melebihi batas 100MB.`, true); return false; }
        return true;
    });
    if (!valid.length) return;

    if (state.feature === 'merge') {
        state.files = [...state.files, ...valid];
    } else {
        state.files = [valid[0]]; // single file for split/compress
    }

    renderFileList();
    fileListWrap.classList.remove('pdf-hidden');
    updateUploadButtons();
}

function updateUploadButtons() {
    const hasFiles = state.files.length > 0;
    if (!hasFiles) {
        btnMergeProc.classList.add('pdf-hidden');
        btnToConfig.classList.add('pdf-hidden');
        return;
    }

    if (state.feature === 'merge') {
        btnMergeProc.classList.remove('pdf-hidden');
        btnToConfig.classList.add('pdf-hidden');
        const span = btnMergeProc.querySelector('span');
        if (span) span.textContent = `Gabung ${state.files.length} File Sekarang`;
    } else {
        btnToConfig.classList.remove('pdf-hidden');
        btnMergeProc.classList.add('pdf-hidden');
        if (btnNextLabel) {
            btnNextLabel.textContent = state.feature === 'split' ? 'Atur Halaman' : 'Pilih Level Kompresi';
        }
    }
}

/* ─── Render file list with drag-to-reorder ─── */
function renderFileList() {
    fileListEl.innerHTML = '';
    state.files.forEach((f, i) => {
        const row = document.createElement('div');
        row.className         = 'pdf-file-row';
        row.draggable         = (state.feature === 'merge'); // only reorderable for merge
        row.dataset.index     = i;
        row.innerHTML = `
            ${state.feature === 'merge' ? `<span class="pdf-drag-handle" title="Seret untuk atur urutan">
                <i class="fa-solid fa-grip-vertical"></i></span>` : ''}
            <i class="fa-solid fa-file-pdf pdf-file-ico"></i>
            <span class="pdf-file-name" title="${f.name}">${formatName(f.name)}</span>
            <span class="pdf-file-size">${formatSize(f.size)}</span>
            <button class="pdf-file-del" data-del="${i}" title="Hapus file">
                <i class="fa-solid fa-xmark"></i>
            </button>`;
        fileListEl.appendChild(row);
    });

    // Delete buttons
    fileListEl.querySelectorAll('[data-del]').forEach(btn => {
        btn.addEventListener('click', e => {
            e.stopPropagation();
            const idx = parseInt(btn.dataset.del);
            state.files.splice(idx, 1);
            renderFileList();
            if (!state.files.length) {
                fileListWrap.classList.add('pdf-hidden');
                btnMergeProc.classList.add('pdf-hidden');
                btnToConfig.classList.add('pdf-hidden');
            } else {
                updateUploadButtons();
            }
        });
    });

    // Drag-to-reorder (merge only)
    if (state.feature === 'merge') {
        initDragReorder();
    }

    const n = state.files.length;
    if (fileCountEl) fileCountEl.textContent = n + (n === 1 ? ' file dipilih' : ' file dipilih');
}

/* ─── Native HTML5 drag reorder ─── */
let dragSrc = null;

function initDragReorder() {
    const rows = fileListEl.querySelectorAll('.pdf-file-row');
    rows.forEach(row => {
        row.addEventListener('dragstart', e => {
            dragSrc = row;
            row.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', row.dataset.index);
        });
        row.addEventListener('dragend', () => {
            row.classList.remove('dragging');
            fileListEl.querySelectorAll('.pdf-file-row').forEach(r => r.classList.remove('drag-over-row'));
        });
        row.addEventListener('dragover', e => {
            e.preventDefault();
            if (row === dragSrc) return;
            fileListEl.querySelectorAll('.pdf-file-row').forEach(r => r.classList.remove('drag-over-row'));
            row.classList.add('drag-over-row');
        });
        row.addEventListener('drop', e => {
            e.preventDefault();
            if (row === dragSrc) return;
            const fromIdx = parseInt(dragSrc.dataset.index);
            const toIdx   = parseInt(row.dataset.index);
            const moved   = state.files.splice(fromIdx, 1)[0];
            state.files.splice(toIdx, 0, moved);
            renderFileList();
            updateUploadButtons();
        });
    });
}

// "Tambah File" button (merge only)
if (btnAddMore) {
    btnAddMore.addEventListener('click', () => fileInput.click());
}

/* ─── Navigate back ─── */
$('btnBackToFeature').addEventListener('click', () => {
    showPanel(panelFeature);
    setStep(1);
});

/* ============================================================
   STEP 3 — CONFIG PANEL
   ============================================================ */

// From upload → config (split / compress)
btnToConfig.addEventListener('click', async () => {
    if (!state.files.length) { toast('Upload file terlebih dahulu.', true); return; }
    await showConfigPanel();
});

// From upload → process (merge direct)
btnMergeProc.addEventListener('click', () => {
    if (state.files.length < 2) { toast('Minimal 2 file PDF untuk digabung.', true); return; }
    startProcess();
});

async function showConfigPanel() {
    // Show correct sub-config
    if (state.feature === 'split') {
        configSplit.classList.remove('pdf-hidden');
        configCompress.classList.add('pdf-hidden');
        if (configPanelTitle) configPanelTitle.textContent = 'Atur pemisahan halaman';
        if (selectedBadge2Text) selectedBadge2Text.textContent = 'Split PDF';
        if (selectedBadge2Icon) selectedBadge2Icon.className = 'fa-solid fa-scissors fa-xs';

        // Show file info
        const f = state.files[0];
        if (splitFileName) splitFileName.textContent = formatName(f.name, 30);
        if (splitPageCount) splitPageCount.textContent = 'Menghitung halaman…';

        // Load pdf-lib and get page count
        try {
            await loadScript(CDN.pdfLib);
            const { PDFDocument } = window.PDFLib;
            const bytes = await f.arrayBuffer();
            const doc   = await PDFDocument.load(bytes, { ignoreEncryption: true });
            state.totalPages = doc.getPageCount();
            if (splitPageCount) splitPageCount.textContent = state.totalPages + ' halaman';
            if (rangeTo) rangeTo.value = state.totalPages;
            if (rangeFrom) rangeFrom.max = state.totalPages;
            if (rangeTo)   rangeTo.max   = state.totalPages;
        } catch {
            if (splitPageCount) splitPageCount.textContent = '? halaman';
        }

    } else { // compress
        configCompress.classList.remove('pdf-hidden');
        configSplit.classList.add('pdf-hidden');
        if (configPanelTitle) configPanelTitle.textContent = 'Pilih level kompresi';
        if (selectedBadge2Text) selectedBadge2Text.textContent = 'Compress PDF';
        if (selectedBadge2Icon) selectedBadge2Icon.className = 'fa-solid fa-file-zipper fa-xs';

        const f = state.files[0];
        if (compressFileName) compressFileName.textContent = formatName(f.name, 30);
        if (compressFileSize) compressFileSize.textContent = formatSize(f.size);
    }

    showPanel(panelConfig);
    setStep(3);
}

// Back from config → upload
$('btnBackToUpload').addEventListener('click', () => {
    showPanel(panelUpload);
    setStep(2);
});

/* ─── Split mode tabs ─── */
splitModeTabs.forEach(tab => {
    tab.addEventListener('click', () => {
        splitModeTabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        state.splitMode = tab.dataset.mode;

        const isRange = (state.splitMode === 'range');
        if (splitRangeInput) {
            splitRangeInput.style.display = isRange ? '' : 'none';
        }
        if (btnStartProc) {
            const sp = btnStartProc.querySelector('span');
            if (sp) sp.textContent = isRange ? 'Pisah Halaman' : 'Pisah Semua Halaman';
        }
    });
});

/* ─── Compress level buttons ─── */
compressLevels.forEach(btn => {
    btn.addEventListener('click', () => {
        compressLevels.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        state.compressLevel = btn.dataset.level;
    });
});

/* ─── Start process ─── */
if (btnStartProc) {
    btnStartProc.addEventListener('click', startProcess);
}

/* ============================================================
   PROCESSING
   ============================================================ */
async function startProcess() {
    showPanel(panelProcessing);
    setStep(4);

    // Reset progress UI
    [ps1, ps2, ps3, ps4].forEach(el => el && el.classList.remove('active', 'done'));
    updateProgress(0, 'Memulai proses…');
    progressWrap.classList.remove('pdf-hidden');

    try {
        if (state.feature === 'compress') {
            await processCompress();
        } else {
            await processClient();
        }
    } catch (err) {
        console.error(err);
        toast(err.message || 'Proses gagal.', true);
        showPanel(state.feature === 'merge' ? panelUpload : panelConfig);
        setStep(state.feature === 'merge' ? 2 : 3);
    }
}

/* ============================================================
   CLIENT: MERGE / SPLIT
   ============================================================ */
async function processClient() {
    // Step 1: load library
    setStep4Sub(1);
    procTitle.textContent = 'Memuat library…';
    procSub.textContent   = 'Mengambil PDF engine dari CDN.';
    updateProgress(5, 'Memuat library…');

    await loadScript(CDN.pdfLib);
    const { PDFDocument } = window.PDFLib;

    setStep4Sub(2);
    updateProgress(20, 'Membaca file PDF…');
    procTitle.textContent = 'Membaca file…';

    let finalBytes;
    state.isZipResult = false;

    if (state.feature === 'merge') {
        await simulateDelay(200);
        setStep4Sub(3);
        updateProgress(40, 'Menggabungkan halaman…');
        procTitle.textContent = 'Menggabungkan PDF…';

        const merged = await PDFDocument.create();
        const total  = state.files.reduce((s, f) => s + f.size, 0);
        let   done   = 0;

        for (const f of state.files) {
            const buf  = await f.arrayBuffer();
            const doc  = await PDFDocument.load(buf, { ignoreEncryption: true });
            const pages = await merged.copyPages(doc, doc.getPageIndices());
            pages.forEach(p => merged.addPage(p));
            done += f.size;
            updateProgress(40 + Math.round((done / total) * 40), 'Memproses file…');
        }

        updateProgress(85, 'Menyimpan dokumen…');
        setStep4Sub(4);
        procTitle.textContent = 'Menyimpan hasil…';
        await simulateDelay(150);
        finalBytes = await merged.save();

    } else {
        // SPLIT
        const buf = await state.files[0].arrayBuffer();
        const src = await PDFDocument.load(buf, { ignoreEncryption: true });
        const total = src.getPageCount();

        if (state.splitMode === 'all') {
            // Load JSZip
            setStep4Sub(3);
            updateProgress(40, 'Memuat JSZip…');
            await loadScript(CDN.jszip);
            procTitle.textContent = 'Memisahkan halaman…';

            const zip = new window.JSZip();
            const folder = zip.folder('halaman');

            for (let i = 0; i < total; i++) {
                const single = await PDFDocument.create();
                const [page] = await single.copyPages(src, [i]);
                single.addPage(page);
                const bytes = await single.save();
                folder.file(`halaman_${String(i + 1).padStart(3, '0')}.pdf`, bytes);
                updateProgress(40 + Math.round(((i + 1) / total) * 45), `Halaman ${i + 1} / ${total}`);
            }

            setStep4Sub(4);
            updateProgress(90, 'Membuat arsip ZIP…');
            procTitle.textContent = 'Membuat ZIP…';
            await simulateDelay(100);

            const zipBlob = await zip.generateAsync({ type: 'blob', compression: 'DEFLATE', compressionOptions: { level: 6 } });
            const url = URL.createObjectURL(zipBlob);

            state.isZipResult = true;
            return finalize(url, `split_all_${total}_halaman.zip`, null, null);

        } else {
            // Range mode
            setStep4Sub(3);
            updateProgress(50, 'Memotong halaman…');
            procTitle.textContent = 'Memotong halaman…';

            let from = Math.max(1, parseInt(rangeFrom.value) || 1);
            let to   = Math.min(total, parseInt(rangeTo.value) || total);
            if (from > to) [from, to] = [to, from];

            const newDoc = await PDFDocument.create();
            const indices = Array.from({ length: to - from + 1 }, (_, i) => i + from - 1);
            const pages   = await newDoc.copyPages(src, indices);
            pages.forEach(p => newDoc.addPage(p));

            setStep4Sub(4);
            updateProgress(85, 'Menyimpan hasil…');
            await simulateDelay(150);
            finalBytes = await newDoc.save();
        }
    }

    updateProgress(100, 'Selesai!');
    setStep4Sub(4, true);
    await simulateDelay(300);

    const blob = new Blob([finalBytes], { type: 'application/pdf' });
    const url  = URL.createObjectURL(blob);
    finalize(url, state.feature === 'merge' ? 'merged_mediatools.pdf' : 'split_mediatools.pdf', null, null);
}

/* ============================================================
   SERVER: COMPRESS  (XHR for upload progress + simulated processing)
   ============================================================ */
function processCompress() {
    return new Promise((resolve, reject) => {
        setStep4Sub(1);
        procTitle.textContent = 'Mengunggah file…';
        procSub.textContent   = 'Mengirim ke server Ghostscript.';
        updateProgress(0, 'Memulai upload…');

        const formData = new FormData();
        formData.append('pdf', state.files[0]);
        formData.append('mode', state.compressLevel);

        const xhr = new XMLHttpRequest();
        xhr.responseType = 'blob';

        // Upload progress (0–40%)
        xhr.upload.addEventListener('progress', e => {
            if (!e.lengthComputable) return;
            const pct = Math.round((e.loaded / e.total) * 40);
            updateProgress(pct, `Mengunggah… ${formatSize(e.loaded)} / ${formatSize(e.total)}`);
            if (pct >= 15) setStep4Sub(2);
        });

        // Server processing simulation (40–90%) once upload completes
        let serverInterval;
        xhr.upload.addEventListener('load', () => {
            setStep4Sub(3);
            procTitle.textContent = 'Ghostscript memproses…';
            procSub.textContent   = 'Mengompres gambar & font…';
            let pct = 42;
            serverInterval = setInterval(() => {
                pct = Math.min(pct + Math.random() * 3, 88);
                updateProgress(Math.floor(pct), 'Ghostscript memproses…');
            }, 250);
        });

        xhr.addEventListener('load', () => {
            clearInterval(serverInterval);

            if (xhr.status !== 200) {
                // Try to parse error from blob
                const reader = new FileReader();
                reader.onload = () => {
                    try {
                        const data = JSON.parse(reader.result);
                        reject(new Error(data.error || 'Server error ' + xhr.status));
                    } catch {
                        reject(new Error('Server error ' + xhr.status));
                    }
                };
                reader.readAsText(xhr.response);
                return;
            }

            setStep4Sub(4);
            updateProgress(95, 'Menyiapkan unduhan…');

            const origSize  = parseInt(xhr.getResponseHeader('X-Original-Size'))  || state.files[0].size;
            const compSize  = parseInt(xhr.getResponseHeader('X-Compressed-Size')) || 0;
            const blob      = xhr.response;
            const url       = URL.createObjectURL(blob);

            setTimeout(() => {
                updateProgress(100, 'Selesai!');
                setStep4Sub(4, true);
                setTimeout(() => finalize(url, 'compressed_mediatools.pdf', origSize, compSize), 350);
                resolve();
            }, 300);
        });

        xhr.addEventListener('error', () => {
            clearInterval(serverInterval);
            reject(new Error('Koneksi gagal. Periksa jaringan Anda.'));
        });

        xhr.addEventListener('timeout', () => {
            clearInterval(serverInterval);
            reject(new Error('Request timeout. File terlalu besar atau server sibuk.'));
        });

        xhr.timeout = 120_000; // 2 minutes

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        xhr.open('POST', '/pdfutilities/compress');
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken ? csrfToken.content : '');
        xhr.send(formData);
    });
}

/* ============================================================
   RESULT PANEL
   ============================================================ */
function finalize(url, filename, origSize, compSize) {
    state.resultUrl = url;

    // Revoke any previous object URL
    if (btnDownload.dataset.prevUrl) {
        URL.revokeObjectURL(btnDownload.dataset.prevUrl);
    }
    btnDownload.dataset.prevUrl = url;
    btnDownload.href             = url;
    btnDownload.download         = filename;

    // Zip vs PDF download label
    const dlLabel = btnDownload.querySelector('span');
    if (state.isZipResult) {
        if (dlLabel) dlLabel.textContent = 'Download ZIP';
        btnDownload.querySelector('i').className = 'fa-solid fa-file-zipper fa-xs';
    } else {
        if (dlLabel) dlLabel.textContent = 'Download PDF';
        btnDownload.querySelector('i').className = 'fa-solid fa-download fa-xs';
    }

    // Result sub-text
    if (resultSub) {
        const labels = {
            merge:    'PDF berhasil digabung dan siap diunduh.',
            split:    state.isZipResult
                          ? 'Semua halaman berhasil dipisah dalam satu file ZIP.'
                          : 'Halaman terpilih berhasil dipisah.',
            compress: 'PDF berhasil dikompres dengan Ghostscript.',
        };
        resultSub.textContent = labels[state.feature] || 'File siap diunduh.';
    }

    // Size comparison (compress only)
    if (origSize && compSize && state.feature === 'compress') {
        sizeCompare.classList.remove('pdf-hidden');
        scBefore.textContent = formatSize(origSize);
        scAfter.textContent  = formatSize(compSize);
        const saved = ((origSize - compSize) / origSize * 100);
        scSaved.textContent  = saved >= 0 ? saved.toFixed(1) + '% lebih kecil' : 'ukuran bertambah';
        if (saved < 0) {
            const badge = $('scBadge');
            if (badge) badge.style.borderColor = 'rgba(248,113,113,0.3)';
        }
    } else {
        sizeCompare.classList.add('pdf-hidden');
    }

    // Result note for compress (server processes) vs others (client)
    const resultNote = $('resultNoteText');
    if (resultNote) {
        if (state.feature === 'compress') {
            resultNote.textContent = 'File diproses di server dan dihapus segera setelah diunduh.';
        } else {
            resultNote.textContent = 'File tidak pernah meninggalkan browser Anda — privasi 100%.';
        }
    }

    showPanel(panelResult);
    setStep(4);
    toast('Proses selesai! Siap diunduh.');
}

/* ─── Result actions ─── */
if (btnProcAgain) {
    btnProcAgain.addEventListener('click', () => {
        state.files  = [];
        state.isZipResult = false;
        renderFileList();
        fileListWrap.classList.add('pdf-hidden');
        btnMergeProc.classList.add('pdf-hidden');
        btnToConfig.classList.add('pdf-hidden');
        showPanel(panelUpload);
        setStep(2);
    });
}

if (btnChangeT) {
    btnChangeT.addEventListener('click', () => {
        state.files   = [];
        state.feature = null;
        state.isZipResult = false;
        showPanel(panelFeature);
        setStep(1);
    });
}

/* ============================================================
   HELPERS
   ============================================================ */
function simulateDelay(ms) {
    return new Promise(r => setTimeout(r, ms));
}

/* ============================================================
   RANGE INPUT VALIDATION
   ============================================================ */
if (rangeFrom && rangeTo) {
    rangeFrom.addEventListener('change', () => {
        const v = Math.max(1, Math.min(parseInt(rangeFrom.value) || 1, state.totalPages));
        rangeFrom.value = v;
        if (parseInt(rangeTo.value) < v) rangeTo.value = v;
    });
    rangeTo.addEventListener('change', () => {
        const max = state.totalPages || 9999;
        const v   = Math.max(1, Math.min(parseInt(rangeTo.value) || 1, max));
        rangeTo.value = v;
        if (parseInt(rangeFrom.value) > v) rangeFrom.value = v;
    });
}