/**
 * sanitizer.js — MediaTools File Security & Privacy Scanner
 * ──────────────────────────────────────────────────────────
 * Multi-phase flow:
 *   1. Upload files → Click "Scan"
 *   2. XHR scan → render per-file threat report
 *   3. User reviews, unchecks files to skip
 *   4. Click "Bersihkan & Download" → XHR process → token download
 */
(function () {
    'use strict';

    // ── Config ────────────────────────────────────────────────────────────────
    const MAX_FILES = 10;
    const MAX_SIZE  = 20 * 1024 * 1024;
    const ALLOWED   = new Set(['image/jpeg', 'image/png', 'image/webp', 'application/pdf']);
    const EXT_MAP   = {
        'application/pdf': { cls: 'pdf',  icon: 'fa-file-pdf' },
        'image/webp':      { cls: 'webp', icon: 'fa-image'    },
    };

    // ── DOM ───────────────────────────────────────────────────────────────────
    const zone       = document.getElementById('uploadZone');
    const fileInput  = document.getElementById('fileInput');
    const queueEl    = document.getElementById('fileQueue');
    const scanBtn    = document.getElementById('scanBtn');
    const alertBox   = document.getElementById('alertBox');
    const uploadRing = document.getElementById('uploadRing');
    const ringFill   = document.getElementById('ringFill');
    const overlayPct = document.getElementById('overlayPct');

    // Panels
    const panelUpload   = document.getElementById('panel-upload');
    const panelScanning = document.getElementById('panel-scanning');
    const panelResults  = document.getElementById('panel-results');
    const panelDownload = document.getElementById('panel-download');

    // Result elements
    const resTotal    = document.getElementById('res-total');
    const resSafe     = document.getElementById('res-safe');
    const resThreat   = document.getElementById('res-threat');
    const fileCardsCt = document.getElementById('file-cards');
    const processBtn  = document.getElementById('processBtn');
    const dlBtn       = document.getElementById('dlBtn');
    const dlFilename  = document.getElementById('dlFilename');
    const dlSummary   = document.getElementById('dlSummary');

    if (!zone || !fileInput) return;

    // ── State ─────────────────────────────────────────────────────────────────
    let selectedFiles = [];
    let sessionKey    = null;
    let scannedFiles  = [];
    let downloadUrl   = null;

    // ── Step indicator ────────────────────────────────────────────────────────
    function setStep(n) {
        document.querySelectorAll('.step-item').forEach((el, i) => {
            el.classList.remove('active', 'done');
            if (i + 1 < n) el.classList.add('done');
            if (i + 1 === n) el.classList.add('active');
        });
    }

    // ── Panel switch ──────────────────────────────────────────────────────────
    function showPanel(name) {
        [panelUpload, panelScanning, panelResults, panelDownload]
            .forEach(p => p && p.classList.add('hidden'));
        const target = document.getElementById(`panel-${name}`);
        if (target) target.classList.remove('hidden');
    }

    // ── Drag & drop ───────────────────────────────────────────────────────────
    zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', ()  => zone.classList.remove('dragover'));
    zone.addEventListener('drop',      e  => {
        e.preventDefault();
        zone.classList.remove('dragover');
        addFiles([...e.dataTransfer.files]);
    });
    fileInput.addEventListener('change', () => {
        addFiles([...fileInput.files]);
        fileInput.value = '';
    });

    // ── File validation ───────────────────────────────────────────────────────
    function addFiles(files) {
        files.forEach(file => {
            if (selectedFiles.length >= MAX_FILES)
                return toast(`Maksimal ${MAX_FILES} file sekaligus.`, 'error');
            if (!ALLOWED.has(file.type))
                return toast(`Format tidak didukung: ${esc(file.name)}`, 'error');
            if (file.size > MAX_SIZE)
                return toast(`File terlalu besar (maks. 20 MB): ${esc(file.name)}`, 'error');
            if (selectedFiles.some(f => f.name === file.name && f.size === file.size)) return;
            selectedFiles.push(file);
        });
        renderQueue();
    }

    function renderQueue() {
        const has = selectedFiles.length > 0;
        queueEl.classList.toggle('hidden', !has);
        if (scanBtn) scanBtn.disabled = !has;

        if (!has) { queueEl.innerHTML = ''; return; }

        queueEl.innerHTML = '';
        selectedFiles.forEach((file, idx) => {
            const { cls, icon } = EXT_MAP[file.type] || { cls: '', icon: 'fa-image' };
            const size = fmtSize(file.size);

            const el = document.createElement('div');
            el.className = 'file-item';
            el.id = `fi-${idx}`;
            el.innerHTML = `
                <div class="file-type-icon ${cls}"><i class="fa-solid ${icon}"></i></div>
                <div class="file-meta">
                    <div class="file-name">${esc(file.name)}</div>
                    <div class="file-size">${size}</div>
                </div>
                <button type="button" class="btn-remove" data-idx="${idx}" aria-label="Hapus">
                    <i class="fa-solid fa-xmark"></i>
                </button>`;
            queueEl.appendChild(el);
        });

        queueEl.querySelectorAll('.btn-remove').forEach(btn => {
            btn.addEventListener('click', () => {
                selectedFiles.splice(+btn.dataset.idx, 1);
                renderQueue();
            });
        });
    }

    // ── PHASE 1: Scan ─────────────────────────────────────────────────────────
    if (scanBtn) {
        scanBtn.addEventListener('click', () => {
            if (!selectedFiles.length) return;
            hideAlert();
            startScan();
        });
    }

    function startScan() {
        showPanel('scanning');
        setStep(2);

        // Animate the scan file list
        const scanList = document.getElementById('scan-file-list');
        if (scanList) {
            scanList.innerHTML = selectedFiles.map(f => `
                <div class="scan-file-row">
                    <div class="scan-dot"></div>
                    <span class="text-gray-400 truncate flex-1">${esc(f.name)}</span>
                    <span class="text-[10px] text-gray-600">${fmtSize(f.size)}</span>
                </div>`).join('');
        }

        const fd   = new FormData();
        selectedFiles.forEach(f => fd.append('files[]', f));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrf) fd.append('_token', csrf);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', window.__sanitizerScanUrl, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');

        // Show ring during upload
        if (uploadRing) uploadRing.classList.remove('hidden');
        xhr.upload.addEventListener('progress', e => {
            if (!e.lengthComputable) return;
            setRing(Math.round(e.loaded / e.total * 100));
            if (overlayPct) overlayPct.textContent = Math.round(e.loaded / e.total * 100) + '%';
        });

        xhr.addEventListener('load', () => {
            if (uploadRing) uploadRing.classList.add('hidden');
            let json = {};
            try { json = JSON.parse(xhr.responseText); } catch (_) {}

            if (xhr.status >= 200 && xhr.status < 300 && json.success) {
                sessionKey   = json.session_key;
                scannedFiles = json.files;
                renderResults(json);
            } else {
                showPanel('upload');
                setStep(1);
                showAlert(json.message || 'Terjadi kesalahan saat memindai.');
            }
        });

        xhr.addEventListener('error', () => {
            if (uploadRing) uploadRing.classList.add('hidden');
            showPanel('upload');
            setStep(1);
            showAlert('Koneksi gagal. Periksa jaringan Anda.');
        });

        xhr.send(fd);
    }

    // ── Render scan results ───────────────────────────────────────────────────
    function renderResults(data) {
        if (resTotal)  resTotal.textContent  = data.summary.total;
        if (resSafe)   resSafe.textContent   = data.summary.safe;
        if (resThreat) resThreat.textContent = data.summary.threats;

        if (fileCardsCt) {
            fileCardsCt.innerHTML = '';
            data.files.forEach(f => fileCardsCt.appendChild(buildFileCard(f)));
        }

        // Enable process button
        if (processBtn) processBtn.disabled = false;

        showPanel('results');
        setStep(3);
    }

    function buildFileCard(file) {
        const hasThreat = !file.safe && file.threat_count > 0;
        const card      = document.createElement('div');
        card.className  = `result-file-card ${hasThreat ? 'has-threats' : 'is-safe'}`;
        card.dataset.id = file.id;

        const sevBadge = hasThreat
            ? `<span class="sev-badge ${file.severity}">${sevLabel(file.severity)}</span>`
            : `<span class="sev-badge safe"><i class="fa-solid fa-shield-check text-[9px]"></i> Aman</span>`;

        card.innerHTML = `
            <div class="result-file-header" onclick="toggleThreatDetail(${file.id})">
                <div class="result-file-status-dot"></div>
                <div class="result-file-name flex-1 min-w-0">${esc(file.name)}</div>
                <span class="result-file-size text-xs text-gray-500">${file.size_fmt}</span>
                ${sevBadge}
                ${hasThreat ? `<span class="text-xs text-gray-500 font-bold ml-1">${file.threat_count} ancaman</span>` : ''}
                ${hasThreat ? `<i class="fa-solid fa-chevron-down text-[10px] text-gray-500 transition-transform" id="chev-${file.id}"></i>` : ''}
            </div>
            ${hasThreat ? buildThreatList(file) : ''}
            <div class="file-include-toggle">
                <input type="checkbox" id="inc-${file.id}" data-file-id="${file.id}" checked>
                <label for="inc-${file.id}">
                    Sertakan dalam proses pembersihan &amp; hapus metadata
                </label>
            </div>`;

        return card;
    }

    function buildThreatList(file) {
        const rows = file.threats.map(t => `
            <div class="threat-row">
                <div class="threat-icon ${t.severity}">
                    <i class="fa-solid ${t.icon || 'fa-bug'}"></i>
                </div>
                <div>
                    <div class="threat-type">${esc(t.type)}</div>
                    <div class="threat-detail">${esc(t.detail)}</div>
                </div>
                <span class="sev-badge ${t.severity} ml-auto flex-shrink-0">${sevLabel(t.severity)}</span>
            </div>`).join('');

        return `<div class="threat-detail-list" id="threats-${file.id}" style="display:none">${rows}</div>`;
    }

    window.toggleThreatDetail = function (id) {
        const el   = document.getElementById(`threats-${id}`);
        const chev = document.getElementById(`chev-${id}`);
        if (!el) return;
        const open = el.style.display !== 'none';
        el.style.display   = open ? 'none' : 'block';
        if (chev) chev.style.transform = open ? '' : 'rotate(180deg)';
    };

    // ── PHASE 2: Process ──────────────────────────────────────────────────────
    if (processBtn) {
        processBtn.addEventListener('click', () => {
            const checked = [...document.querySelectorAll('[data-file-id]:checked')]
                .map(cb => parseInt(cb.dataset.fileId, 10));

            if (!checked.length) {
                toast('Pilih minimal satu file untuk diproses.', 'error');
                return;
            }

            startProcess(checked);
        });
    }

    function startProcess(fileIds) {
        if (processBtn) {
            processBtn.disabled  = true;
            processBtn.innerHTML = `<i class="fa-solid fa-circle-notch spin"></i><span>Memproses...</span>`;
        }

        // Animate statuses in file queue (if still visible)
        animateProcessing(fileIds);

        const fd = new FormData();
        fd.append('session_key', sessionKey);
        fileIds.forEach(id => fd.append('file_ids[]', id));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrf) fd.append('_token', csrf);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', window.__sanitizerProcessUrl, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.addEventListener('load', () => {
            let json = {};
            try { json = JSON.parse(xhr.responseText); } catch (_) {}

            if (xhr.status >= 200 && xhr.status < 300 && json.success) {
                downloadUrl = window.__sanitizerDownloadUrl.replace('__TOKEN__', json.token);
                showDownloadPanel(json);
            } else {
                if (processBtn) {
                    processBtn.disabled  = false;
                    processBtn.innerHTML = `<i class="fa-solid fa-broom"></i><span>Bersihkan &amp; Download</span>`;
                }
                showAlert(json.message || 'Terjadi kesalahan saat memproses.');
                toast(json.message || 'Gagal memproses.', 'error');
            }
        });

        xhr.addEventListener('error', () => {
            if (processBtn) {
                processBtn.disabled  = false;
                processBtn.innerHTML = `<i class="fa-solid fa-broom"></i><span>Bersihkan &amp; Download</span>`;
            }
            showAlert('Koneksi gagal.');
        });

        xhr.send(fd);
    }

    function animateProcessing(ids) {
        // Animate each selected file card header
        ids.forEach(id => {
            const card   = document.querySelector(`.result-file-card[data-id="${id}"]`);
            const header = card?.querySelector('.result-file-header');
            if (header) {
                const dot = header.querySelector('.result-file-status-dot');
                if (dot) {
                    dot.style.background = '#f59e0b';
                    dot.style.animation  = 'blink-dot 0.8s ease-in-out infinite';
                }
            }
        });
    }

    function showDownloadPanel(data) {
        setStep(4);
        showPanel('download');

        if (dlFilename) dlFilename.textContent = data.filename;

        const errCount = (data.errors || []).length;
        if (dlSummary) {
            dlSummary.textContent = errCount
                ? `${data.count} file berhasil, ${errCount} file gagal.`
                : `${data.count} file berhasil dibersihkan. Semua ancaman dan metadata privasi telah dihapus.`;
        }

        // Trigger download
        if (dlBtn) {
            dlBtn.href = downloadUrl;
            dlBtn.click();
        }
    }

    // ── Reset ─────────────────────────────────────────────────────────────────
    const resetBtn = document.getElementById('resetBtn');
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            selectedFiles = [];
            sessionKey    = null;
            scannedFiles  = [];
            downloadUrl   = null;
            renderQueue();
            hideAlert();
            showPanel('upload');
            setStep(1);
            if (processBtn) {
                processBtn.disabled  = true;
                processBtn.innerHTML = `<i class="fa-solid fa-broom"></i><span>Bersihkan &amp; Download</span>`;
            }
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    function setRing(pct) {
        if (!ringFill) return;
        const c = 164;
        ringFill.style.strokeDashoffset = c - (c * pct / 100);
    }

    function showAlert(msg) {
        if (!alertBox) return;
        alertBox.classList.remove('hidden');
        const el = alertBox.querySelector('[data-msg]');
        if (el) el.textContent = msg;
    }

    function hideAlert() {
        if (alertBox) alertBox.classList.add('hidden');
    }

    function toast(msg, type = 'info') {
        const el = document.createElement('div');
        el.className = `san-toast ${type}`;
        const ico = type === 'error' ? 'fa-circle-exclamation'
                  : type === 'success' ? 'fa-circle-check' : 'fa-circle-info';
        el.innerHTML = `<i class="fa-solid ${ico} flex-shrink-0"></i><span>${esc(msg)}</span>`;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3800);
    }

    function sevLabel(sev) {
        return { critical: 'KRITIS', high: 'TINGGI', medium: 'SEDANG', low: 'RENDAH', safe: 'AMAN' }[sev] || sev;
    }

    function fmtSize(bytes) {
        return bytes < 1048576
            ? (bytes / 1024).toFixed(1) + ' KB'
            : (bytes / 1048576).toFixed(2) + ' MB';
    }

    function esc(str) {
        return String(str).replace(/[&<>"']/g, c =>
            ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    // Init
    setStep(1);

})();