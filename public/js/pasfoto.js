/**
 * public/js/pasfoto.js
 * Smart Photo Studio — Unified Pas Foto + Background Remover
 * Self-contained (no ES module imports) — runs as classic <script> tag.
 *
 * Dependencies loaded via CDN before this file:
 *   Cropper.js v1.6.x  — crop engine (pas foto mode)
 *   jsPDF v2.5.x       — PDF export (pas foto mode)
 *   JSZip v3.x         — batch ZIP download (bgr multi mode)
 */

'use strict';

/* ─────────────────────────────────────────────────────────────
   CONSTANTS
───────────────────────────────────────────────────────────── */
const PROCESS_URL = '/bg/process';
const MAX_BYTES   = 20 * 1024 * 1024;       // 20 MB
const ACCEPTED    = new Set(['image/jpeg', 'image/jpg', 'image/png', 'image/webp']);

/**
 * Standard Indonesian pas foto sizes at 300 DPI.
 * (1 cm × 300 DPI / 2.54 cm/inch = 118 px/cm)
 */
const PHOTO_SIZES = {
  '2x3': { label: '2×3 cm', desc: 'KTP, SIM, Paspor',         w: 236,  h: 354,  ratio: 2/3, printW: 20, printH: 30 },
  '3x4': { label: '3×4 cm', desc: 'Ijazah, CPNS, Skripsi',    w: 354,  h: 472,  ratio: 3/4, printW: 30, printH: 40 },
  '4x6': { label: '4×6 cm', desc: 'Lamaran kerja, beasiswa',   w: 472,  h: 709,  ratio: 4/6, printW: 40, printH: 60 },
};

/** Preset background colours (pasfoto studio) */
const PF_BG_PRESETS = [
  { value: '#cc1414', label: 'Merah',       emoji: '🔴' },
  { value: '#0f52ba', label: 'Biru',        emoji: '🔵' },
  { value: '#ffffff', label: 'Putih',       emoji: '⚪' },
  { value: '#f3ede1', label: 'Krem',        emoji: '🟡' },
  { value: '#1a1a1a', label: 'Hitam',       emoji: '⚫' },
];

/** Preset background colours (bgremover result) */
const BGR_BG_PRESETS = [
  { value: 'transparent', label: 'Transparan' },
  { value: '#ffffff',     label: 'Putih' },
  { value: '#000000',     label: 'Hitam' },
  { value: '#cc1414',     label: 'Merah' },
  { value: '#0f52ba',     label: 'Biru' },
];

/* ─────────────────────────────────────────────────────────────
   STATE SINGLETON
───────────────────────────────────────────────────────────── */
const S = {
  /** 'pasfoto' | 'bgremover' | null */
  mode: null,

  /** Original uploaded File (single mode) */
  file: null,
  origUrl: null,   // object URL for original file

  /** AI result */
  aiBlob: null,
  aiUrl: null,

  /** Quality: 'small' (fast) | 'medium' (HD) */
  quality: 'medium',

  /** Pas Foto specific settings */
  pf: {
    size: '3x4',
    bgColor: '#cc1414',
    croppedAspectRatio: 3/4,  // ratio of the crop actually used
  },

  /** BG Remover specific settings */
  bgr: {
    bgColor: 'transparent',
  },

  /** Cropper.js instance */
  cropper: null,

  /** Result canvas for pasfoto (updated on setting change) */
  resultCanvas: null,

  /** Multi-batch mode */
  isMulti: false,
  results: new Map(),  // id → { blob, url, origUrl, name }
};

/* ─────────────────────────────────────────────────────────────
   VIEW ENGINE
───────────────────────────────────────────────────────────── */
const ALL_VIEWS = [
  'view-upload',
  'view-crop',
  'view-processing',
  'view-studio',
  'view-bgr',
  'view-multi',
];

function showView(name) {
  ALL_VIEWS.forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = (id === name) ? '' : 'none';
    el.setAttribute('aria-hidden', id !== name ? 'true' : 'false');
  });
  // Scroll app container into view
  const app = document.getElementById('pf-app');
  if (app) app.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/* ─────────────────────────────────────────────────────────────
   TOAST NOTIFICATION
───────────────────────────────────────────────────────────── */
let _toastTimer = null;
function toast(msg, type = 'success') {
  const el  = document.getElementById('pf-toast');
  const txt = document.getElementById('pf-toast-msg');
  if (!el) return;
  if (txt) txt.textContent = msg;
  el.className = `pf-toast pf-toast--${type} pf-toast--show`;
  clearTimeout(_toastTimer);
  _toastTimer = setTimeout(() => el.classList.remove('pf-toast--show'), 4200);
}

/* ─────────────────────────────────────────────────────────────
   GLOBAL LOADING OVERLAY
───────────────────────────────────────────────────────────── */
function showLoading(text = 'Memproses…') {
  const el  = document.getElementById('pf-loading');
  const txt = document.getElementById('pf-loading-text');
  if (el)  el.classList.add('pf-loading--active');
  if (txt) txt.textContent = text;
}
function hideLoading() {
  document.getElementById('pf-loading')?.classList.remove('pf-loading--active');
}

/* ─────────────────────────────────────────────────────────────
   PROCESSING PROGRESS BAR
───────────────────────────────────────────────────────────── */
function setProgress(pct, label) {
  const p    = Math.min(Math.round(pct), 100);
  const fill = document.getElementById('proc-fill');
  const pEl  = document.getElementById('proc-pct');
  const lEl  = document.getElementById('proc-label');
  if (fill) fill.style.width = p + '%';
  if (pEl)  pEl.textContent  = p + '%';
  if (lEl && label) lEl.textContent = label;
}

/* ─────────────────────────────────────────────────────────────
   AI BACKGROUND REMOVAL  (calls /bg/process — BiRefNet)
───────────────────────────────────────────────────────────── */
function removeBackground(file, quality, onProgress) {
  return new Promise((resolve, reject) => {
    const fd   = new FormData();
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    fd.append('image',   file);
    fd.append('quality', quality === 'small' ? 'fast' : 'high');

    /* Simulated progress ticks while server is running */
    const SIM = [
      [26, 'AI menganalisis komposisi gambar…'],
      [38, 'Mendeteksi subjek utama…'],
      [50, 'Membangun segmentation mask…'],
      [62, 'Memproses rambut & detail halus…'],
      [72, 'Memperbaiki tepi transparan…'],
      [80, 'Alpha matting area edge…'],
      [88, 'Mengoptimalkan kualitas output…'],
    ];
    let simPct = 25, stepIdx = 0;
    const simTick = setInterval(() => {
      if (stepIdx >= SIM.length) return;
      const [target, lbl] = SIM[stepIdx];
      if (simPct < target) { simPct += 0.5; onProgress(Math.min(simPct, target), lbl); }
      else stepIdx++;
    }, 110);
    const stopSim = () => clearInterval(simTick);

    const xhr        = new XMLHttpRequest();
    xhr.open('POST', PROCESS_URL, true);
    xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
    xhr.setRequestHeader('Accept', 'image/png, application/json');
    xhr.responseType = 'blob';
    xhr.timeout      = 180_000;

    xhr.upload.onprogress = e => {
      if (!e.lengthComputable) return;
      onProgress(2 + (e.loaded / e.total) * 22, `Mengunggah gambar… ${Math.round(e.loaded / e.total * 100)}%`);
    };

    xhr.onload = async () => {
      stopSim();
      if (xhr.status === 200) {
        const ct = xhr.getResponseHeader('Content-Type') ?? '';
        if (ct.startsWith('image/')) { onProgress(92, 'Memuat hasil…'); resolve(xhr.response); return; }
        try { const t = await xhr.response.text(); reject(new Error(JSON.parse(t).error ?? 'Server error')); }
        catch { reject(new Error(`Server error ${xhr.status}`)); }
      } else {
        try { const t = await xhr.response.text(); reject(new Error(JSON.parse(t).error ?? `HTTP ${xhr.status}`)); }
        catch { reject(new Error(`HTTP ${xhr.status}`)); }
      }
    };
    xhr.onerror   = () => { stopSim(); reject(new Error('Network error — tidak dapat menghubungi server')); };
    xhr.ontimeout = () => { stopSim(); reject(new Error('Timeout — server membutuhkan waktu terlalu lama')); };
    xhr.onabort   = () => { stopSim(); reject(new Error('Dibatalkan')); };
    xhr.send(fd);
  });
}

/* ─────────────────────────────────────────────────────────────
   CANVAS UTILITIES
───────────────────────────────────────────────────────────── */
function mkCanvas(w, h) {
  return Object.assign(document.createElement('canvas'), { width: w, height: h });
}

function blobToImg(blob) {
  return new Promise((res, rej) => {
    const url = URL.createObjectURL(blob);
    const img = new Image();
    img.onload  = () => { URL.revokeObjectURL(url); res(img); };
    img.onerror = () => { URL.revokeObjectURL(url); rej(new Error('Gagal memuat gambar')); };
    img.src = url;
  });
}

/**
 * Composite: fill bg color → draw transparent PNG → resize to target WxH.
 * Returns a canvas at exactly targetW × targetH.
 */
async function composite(transparentBlob, bgColor, targetW, targetH) {
  const img = await blobToImg(transparentBlob);
  const c   = mkCanvas(targetW, targetH);
  const ctx = c.getContext('2d');
  ctx.imageSmoothingEnabled = true;
  ctx.imageSmoothingQuality = 'high';
  if (bgColor && bgColor !== 'transparent') {
    ctx.fillStyle = bgColor;
    ctx.fillRect(0, 0, targetW, targetH);
  }
  ctx.drawImage(img, 0, 0, targetW, targetH);
  return c;
}

function canvasToBlob(canvas, mime = 'image/jpeg', quality = 0.93) {
  return new Promise((res, rej) => {
    canvas.toBlob(b => b ? res(b) : rej(new Error('Canvas toBlob failed')), mime, quality);
  });
}

function triggerDownload(url, filename) {
  const a = Object.assign(document.createElement('a'), { href: url, download: filename, style: 'display:none' });
  document.body.appendChild(a); a.click(); a.remove();
}

/* ─────────────────────────────────────────────────────────────
   INIT — DOMContentLoaded
───────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    initUploadView();
    initCropView();

    if (typeof initStudioView === 'function') initStudioView();
    if (typeof initBgrView === 'function') initBgrView();
    if (typeof initMultiView === 'function') initMultiView();
});

/* ═══════════════════════════════════════════════════════════════
   UPLOAD VIEW
═══════════════════════════════════════════════════════════════ */
function initUploadView() {
  const dz    = document.getElementById('dz-zone');
  const input = document.getElementById('dz-input');
  const btn   = document.getElementById('btn-dz-browse');
  if (!dz || !input) return;

  /* Click → browse */
  dz.addEventListener('click', () => input.click());
  btn?.addEventListener('click', e => { e.stopPropagation(); input.click(); });
  input.addEventListener('change', e => { dispatchFiles([...e.target.files]); e.target.value = ''; });

  /* Drag & Drop */
  ['dragenter', 'dragover'].forEach(ev =>
    dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.add('dz--drag'); }));
  ['dragleave', 'dragend'].forEach(ev =>
    dz.addEventListener(ev, () => dz.classList.remove('dz--drag')));
  dz.addEventListener('drop', e => {
    e.preventDefault();
    dz.classList.remove('dz--drag');
    dispatchFiles([...e.dataTransfer.files]);
  });

  /* Paste */
  document.addEventListener('paste', e => {
    const files = [...(e.clipboardData?.files ?? [])].filter(f => ACCEPTED.has(f.type));
    if (files.length) dispatchFiles(files);
  });

  /* Mode buttons */
    document.getElementById('btn-mode-pasfoto')?.addEventListener('click', () => {
    if (!S.file) {
        toast('Silakan upload foto terlebih dahulu', 'error');
        return;
    }
    S.mode = 'pasfoto';
    startCropView();
    });
    document.getElementById('btn-mode-bgr')?.addEventListener('click', () => {
    if (!S.file) {
        toast('Silakan upload foto terlebih dahulu', 'error');
        return;
    }
    S.mode = 'bgremover';
    startBgrFlow(S.file);
    });

    document.getElementById('pasfoto')?.addEventListener('click', () => {
    document.getElementById('btn-mode-pasfoto')?.click();
    });
    document.getElementById('bgremover')?.addEventListener('click', () => {
    document.getElementById('btn-mode-bgr')?.click();
    });

  /* Quality toggle on upload panel */
  document.getElementById('upload-quality-btns')?.addEventListener('click', e => {
    const b = e.target.closest('[data-q]');
    if (!b) return;
    document.querySelectorAll('#upload-quality-btns [data-q]').forEach(x => x.classList.remove('active'));
    b.classList.add('active');
    S.quality = b.dataset.q === 'high' ? 'medium' : 'small';
  });
}

/** Validate and route incoming files */
function dispatchFiles(files) {
  const valid = files.filter(f => {
    if (!ACCEPTED.has(f.type)) { toast(`Format .${f.name.split('.').pop()} tidak didukung`, 'error'); return false; }
    if (f.size > MAX_BYTES)    { toast(`${shortName(f.name)} melebihi 20 MB`, 'error');               return false; }
    return true;
  });
  if (!valid.length) return;

  if (valid.length >= 2) {
    /* Multi-batch: always BGRemover */
    S.mode    = 'bgremover';
    S.isMulti = true;
    startMultiFlow(valid);
  } else {
    S.file    = valid[0];
    S.isMulti = false;
    showUploadPreview(valid[0]);
  }
}

function showUploadPreview(file) {
  const prev   = document.getElementById('upload-preview');
  const mode   = document.getElementById('mode-selector');
  const dz     = document.getElementById('dz-zone');

  if (prev) {
    const url = URL.createObjectURL(file);
    prev.innerHTML = `<img src="${esc(url)}" alt="preview" class="upload-preview-img">
      <div class="upload-preview-label"><i class="fa-solid fa-image"></i> ${esc(shortName(file.name))}</div>`;
    prev.style.display = 'block';
    prev.dataset.url   = url;   // will revoke on reset
  }
  if (dz)   dz.classList.add('dz--compact');
  if (mode) mode.style.display = 'flex';
  mode?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function resetUploadView() {
  // Revoke preview url
  const prev = document.getElementById('upload-preview');
  if (prev?.dataset.url) { URL.revokeObjectURL(prev.dataset.url); prev.dataset.url = ''; }
  if (prev) { prev.innerHTML = ''; prev.style.display = 'none'; }

  const mode = document.getElementById('mode-selector');
  if (mode) mode.style.display = 'none';

  document.getElementById('dz-zone')?.classList.remove('dz--compact');
  S.file = S.aiBlob = S.mode = null;
  if (S.aiUrl)   { URL.revokeObjectURL(S.aiUrl);   S.aiUrl   = null; }
  if (S.origUrl) { URL.revokeObjectURL(S.origUrl); S.origUrl = null; }

  showView('view-upload');
}

/* ═══════════════════════════════════════════════════════════════
   CROP VIEW  (Pasfoto only — Cropper.js)
═══════════════════════════════════════════════════════════════ */
function initCropView() {
  /* Size buttons — set Cropper aspect ratio */
  document.getElementById('crop-size-btns')?.addEventListener('click', e => {
    const b = e.target.closest('[data-size]');
    if (!b || !S.cropper) return;
    document.querySelectorAll('#crop-size-btns [data-size]').forEach(x => x.classList.remove('active'));
    b.classList.add('active');
    S.pf.size = b.dataset.size;
    S.cropper.setAspectRatio(PHOTO_SIZES[S.pf.size].ratio);
    toast(`Ukuran diganti ke ${PHOTO_SIZES[S.pf.size].label}`);
  });

  document.getElementById('btn-crop-process')?.addEventListener('click', doProcessCrop);
  document.getElementById('btn-crop-back')?.addEventListener('click', () => {
    destroyCropper();
    resetUploadView();
  });

  /* Cropper rotate helpers */
  document.getElementById('btn-crop-rot-l')?.addEventListener('click', () => S.cropper?.rotate(-90));
  document.getElementById('btn-crop-rot-r')?.addEventListener('click', () => S.cropper?.rotate(90));
  document.getElementById('btn-crop-flip-h')?.addEventListener('click', () => {
    const d = S.cropper?.getData();
    if (d) S.cropper?.scale(-S.cropper.imageData.scaleX || -1, 1);
    else S.cropper?.scaleX(-1);
  });
}

function startCropView() {
  showView('view-crop');
  const img = document.getElementById('crop-img');
  if (!img || !S.file) return;

  destroyCropper();
  const url = URL.createObjectURL(S.file);
  img.src = url;
  img.onload = () => {
    URL.revokeObjectURL(url);
    const sz = PHOTO_SIZES[S.pf.size];
    S.cropper = new Cropper(img, {
      aspectRatio:  sz.ratio,
      viewMode:     1,
      dragMode:     'move',
      autoCropArea: 0.85,
      responsive:   true,
      background:   true,
      modal:        true,
      guides:       true,
      center:       true,
      highlight:    false,
      cropBoxMovable:    true,
      cropBoxResizable:  true,
    });
  };
}

function destroyCropper() {
  if (S.cropper) { try { S.cropper.destroy(); } catch (_) {} S.cropper = null; }
}

async function doProcessCrop() {
  if (!S.cropper) return;

  const sz = PHOTO_SIZES[S.pf.size];
  S.pf.croppedAspectRatio = sz.ratio;

  /* Get high-resolution cropped canvas (2× for better AI input) */
  const croppedCanvas = S.cropper.getCroppedCanvas({
    width:                sz.w * 2,
    height:               sz.h * 2,
    imageSmoothingQuality: 'high',
  });

  destroyCropper();

  /* Transition to processing view */
  showView('view-processing');
  const thumb = document.getElementById('proc-thumb');
  if (thumb) thumb.src = URL.createObjectURL(S.file);
  setProgress(0, 'Mempersiapkan gambar…');

  /* Convert canvas → blob → send to AI */
  let croppedBlob;
  try {
    croppedBlob = await canvasToBlob(croppedCanvas, 'image/png');
  } catch (err) {
    toast('Gagal memotong gambar: ' + err.message, 'error');
    startCropView();
    return;
  }

  try {
    setProgress(3, 'Mengirim ke AI BiRefNet…');
    const aiBlob = await removeBackground(croppedBlob, S.quality, (pct, lbl) => setProgress(pct, lbl));

    if (S.aiUrl) URL.revokeObjectURL(S.aiUrl);
    S.aiBlob = aiBlob;
    S.aiUrl  = URL.createObjectURL(aiBlob);

    setProgress(100, 'Selesai!');
    await sleep(350);
    showStudio();

  } catch (err) {
    console.error('[PasFoto AI]', err);
    toast(err.message?.slice(0, 80) ?? 'Gagal memproses gambar', 'error');
    startCropView();
  }
}

/* ═══════════════════════════════════════════════════════════════
   PASFOTO STUDIO VIEW
═══════════════════════════════════════════════════════════════ */
function initStudioView() {
  /* Background swatches */
  document.getElementById('studio-bg-swatches')?.addEventListener('click', async e => {
    const sw = e.target.closest('[data-bg]');
    if (!sw) return;
    document.querySelectorAll('#studio-bg-swatches [data-bg]').forEach(s => s.classList.remove('active'));
    sw.classList.add('active');
    S.pf.bgColor = sw.dataset.bg;
    document.getElementById('studio-custom-color').value = sw.dataset.bg !== 'custom' ? sw.dataset.bg : S.pf.bgColor;
    if (S.aiBlob) await refreshStudioPreview();
  });

  /* Custom colour picker */
  document.getElementById('studio-custom-color')?.addEventListener('input', async e => {
    S.pf.bgColor = e.target.value;
    document.querySelectorAll('#studio-bg-swatches [data-bg]').forEach(s => s.classList.remove('active'));
    document.getElementById('swatch-custom')?.classList.add('active');
    if (S.aiBlob) await refreshStudioPreview();
  });

  /* Size buttons */
  document.getElementById('studio-size-btns')?.addEventListener('click', async e => {
    const b = e.target.closest('[data-size]');
    if (!b) return;
    const newSize = PHOTO_SIZES[b.dataset.size];
    /* Check if same aspect ratio as crop */
    const sameRatio = Math.abs(newSize.ratio - S.pf.croppedAspectRatio) < 0.01;
    if (!sameRatio) {
      // Different ratio – ask user to re-crop
      if (!confirm(`Ukuran ${newSize.label} membutuhkan rasio crop berbeda (${b.dataset.size === '3x4' ? '3:4' : '2:3'}). Kembali ke tampilan crop?`)) return;
      S.pf.size = b.dataset.size;
      showView('view-crop');
      startCropView();
      return;
    }
    document.querySelectorAll('#studio-size-btns [data-size]').forEach(x => x.classList.remove('active'));
    b.classList.add('active');
    S.pf.size = b.dataset.size;
    if (S.aiBlob) await refreshStudioPreview();
  });

  /* Download JPG */
  document.getElementById('btn-studio-dl-jpg')?.addEventListener('click', downloadStudioJpg);

  /* Download PDF */
  document.getElementById('btn-studio-dl-pdf')?.addEventListener('click', downloadStudioPdf);

  /* Crop ulang */
  document.getElementById('btn-studio-recrop')?.addEventListener('click', () => {
    showView('view-crop');
    startCropView();
  });

  /* Reset / Foto Baru */
  document.getElementById('btn-studio-reset')?.addEventListener('click', resetUploadView);
}

async function showStudio() {
  showView('view-studio');
  /* Set active size button */
  document.querySelectorAll('#studio-size-btns [data-size]').forEach(b => {
    b.classList.toggle('active', b.dataset.size === S.pf.size);
    /* Dim incompatible sizes */
    const sz        = PHOTO_SIZES[b.dataset.size];
    const compatible = Math.abs(sz.ratio - S.pf.croppedAspectRatio) < 0.01;
    b.classList.toggle('incompatible', !compatible);
  });
  await refreshStudioPreview();
}

async function refreshStudioPreview() {
  const container = document.getElementById('studio-preview-container');
  if (!container || !S.aiBlob) return;

  const sz = PHOTO_SIZES[S.pf.size];

  /* Compute display dimensions (max 320px wide) */
  const maxW = Math.min(320, container.clientWidth || 320);
  const dispW = maxW;
  const dispH = Math.round(maxW / sz.ratio);

  /* Composite at display size for fast preview */
  const canvas = await composite(S.aiBlob, S.pf.bgColor, dispW, dispH);
  canvas.className = 'studio-preview-canvas';

  container.innerHTML = '';
  container.appendChild(canvas);
  S.resultCanvas = canvas;

  /* Update info chips */
  const chip = document.getElementById('studio-dim-chip');
  if (chip) chip.textContent = `${sz.w}×${sz.h}px · ${sz.label}`;

  /* Update PDF copies select min */
  updatePdfCopiesPreview();
}

function updatePdfCopiesPreview() {
  const copies = parseInt(document.getElementById('studio-pdf-copies')?.value ?? '4');
  const sz     = PHOTO_SIZES[S.pf.size];
  const cols   = Math.floor((210 - 20 + 3) / (sz.printW + 3));
  const rows   = Math.floor((297 - 20 + 3) / (sz.printH + 3));
  const max    = cols * rows;
  const lbl    = document.getElementById('studio-pdf-per-page');
  if (lbl) lbl.textContent = `Maks. ${max} foto/hal A4`;
}

async function downloadStudioJpg() {
  if (!S.aiBlob) return;
  const btn  = document.getElementById('btn-studio-dl-jpg');
  const orig = btn?.innerHTML;
  if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>'; }

  try {
    const sz     = PHOTO_SIZES[S.pf.size];
    const canvas = await composite(S.aiBlob, S.pf.bgColor, sz.w, sz.h);
    const blob   = await canvasToBlob(canvas, 'image/jpeg', 0.93);
    const url    = URL.createObjectURL(blob);
    triggerDownload(url, `pasfoto_${S.pf.size}_${Date.now()}.jpg`);
    setTimeout(() => URL.revokeObjectURL(url), 6000);
    toast('✓ JPG berhasil diunduh!');
  } catch (err) {
    toast('Gagal unduh JPG: ' + err.message, 'error');
  } finally {
    if (btn) { btn.disabled = false; btn.innerHTML = orig; }
  }
}

async function downloadStudioPdf() {
  if (!S.aiBlob) return;
  const btn  = document.getElementById('btn-studio-dl-pdf');
  const orig = btn?.innerHTML;
  if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> PDF…'; }

  try {
    if (!window.jspdf) { toast('jsPDF belum dimuat. Refresh halaman.', 'error'); return; }
    const { jsPDF }  = window.jspdf;
    const copies     = parseInt(document.getElementById('studio-pdf-copies')?.value ?? '4');
    const sz         = PHOTO_SIZES[S.pf.size];

    /* Render at 300 DPI (actual size) */
    const canvas     = await composite(S.aiBlob, S.pf.bgColor, sz.w, sz.h);
    const imgData    = canvas.toDataURL('image/jpeg', 0.95);

    const doc        = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
    const margin     = 10, gap = 2;
    const pageW      = 210, pageH = 297;
    const cols       = Math.floor((pageW - margin * 2 + gap) / (sz.printW + gap));
    const rows       = Math.floor((pageH - margin * 2 + gap) / (sz.printH + gap));

    let x = margin, y = margin, colIdx = 0, placed = 0;
    for (let i = 0; i < copies; i++) {
      doc.addImage(imgData, 'JPEG', x, y, sz.printW, sz.printH);
      placed++;
      colIdx++;
      if (colIdx >= cols) {
        colIdx = 0;
        x      = margin;
        y     += sz.printH + gap;
        if (y + sz.printH > pageH - margin && i < copies - 1) {
          doc.addPage();
          y = margin;
        }
      } else {
        x += sz.printW + gap;
      }
    }

    doc.save(`pasfoto_${S.pf.size}_${copies}foto.pdf`);
    toast(`✓ PDF ${copies} foto berhasil dibuat!`);

  } catch (err) {
    console.error('[PDF]', err);
    toast('Gagal buat PDF: ' + err.message, 'error');
  } finally {
    if (btn) { btn.disabled = false; btn.innerHTML = orig; }
  }
}

/* ═══════════════════════════════════════════════════════════════
   BGR SINGLE FLOW
═══════════════════════════════════════════════════════════════ */
async function startBgrFlow(file) {
  if (S.origUrl) URL.revokeObjectURL(S.origUrl);
  S.origUrl = URL.createObjectURL(file);

  showView('view-processing');
  const thumb = document.getElementById('proc-thumb');
  if (thumb) thumb.src = S.origUrl;
  setProgress(0, 'Mempersiapkan…');

  try {
    const aiBlob = await removeBackground(file, S.quality, (pct, lbl) => setProgress(pct, lbl));

    if (S.aiUrl) URL.revokeObjectURL(S.aiUrl);
    S.aiBlob = aiBlob;
    S.aiUrl  = URL.createObjectURL(aiBlob);

    setProgress(100, 'Selesai!');
    await sleep(350);
    showBgrResult();

  } catch (err) {
    console.error('[BGR single]', err);
    toast(err.message?.slice(0, 80) ?? 'Gagal', 'error');
    resetUploadView();
  }
}

/* ═══════════════════════════════════════════════════════════════
   BGR RESULT VIEW
═══════════════════════════════════════════════════════════════ */
function initBgrView() {
  /* BG swatches for BGR mode */
  document.getElementById('bgr-bg-swatches')?.addEventListener('click', e => {
    const sw = e.target.closest('[data-bg]');
    if (!sw) return;
    document.querySelectorAll('#bgr-bg-swatches [data-bg]').forEach(s => s.classList.remove('active'));
    sw.classList.add('active');
    S.bgr.bgColor = sw.dataset.bg;
  });

  /* Download PNG */
  document.getElementById('btn-bgr-dl-png')?.addEventListener('click', () => {
    if (!S.aiBlob) return;
    const url  = URL.createObjectURL(S.aiBlob);
    const name = stripExt(S.file?.name ?? 'image') + '_nobg.png';
    triggerDownload(url, name);
    setTimeout(() => URL.revokeObjectURL(url), 6000);
    toast('✓ PNG transparan diunduh!');
  });

  /* Download JPG with bg */
  document.getElementById('btn-bgr-dl-jpg')?.addEventListener('click', async () => {
    if (!S.aiBlob) return;
    const btn  = document.getElementById('btn-bgr-dl-jpg');
    const orig = btn?.innerHTML;
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>'; }
    try {
      const img = await blobToImg(S.aiBlob);
      const c   = mkCanvas(img.naturalWidth, img.naturalHeight);
      const ctx = c.getContext('2d');
      ctx.fillStyle = S.bgr.bgColor !== 'transparent' ? S.bgr.bgColor : '#ffffff';
      ctx.fillRect(0, 0, c.width, c.height);
      ctx.drawImage(img, 0, 0);
      const blob = await canvasToBlob(c, 'image/jpeg', 0.93);
      const url  = URL.createObjectURL(blob);
      triggerDownload(url, stripExt(S.file?.name ?? 'image') + '_nobg.jpg');
      setTimeout(() => URL.revokeObjectURL(url), 6000);
      toast('✓ JPG diunduh!');
    } catch (err) {
      toast('Gagal: ' + err.message, 'error');
    } finally {
      if (btn) { btn.disabled = false; btn.innerHTML = orig; }
    }
  });

  /* Foto baru */
  document.getElementById('btn-bgr-new')?.addEventListener('click', resetUploadView);

  /* Switch to PasFoto mode with same image */
  document.getElementById('btn-bgr-to-pasfoto')?.addEventListener('click', () => {
    if (!S.file) return;
    S.mode = 'pasfoto';
    /* Reset AI result so it re-processes with crop */
    if (S.aiUrl) { URL.revokeObjectURL(S.aiUrl); S.aiUrl = null; }
    S.aiBlob = null;
    startCropView();
  });
}

function showBgrResult() {
  showView('view-bgr');
  const origImg = document.getElementById('bgr-orig-img');
  const resImg  = document.getElementById('bgr-res-img');
  if (origImg) origImg.src = S.origUrl;
  if (resImg)  resImg.src  = S.aiUrl;
  initCompareSlider('bgr-compare', 'bgr-after-layer', 'bgr-handle');
}

/* Compare Slider — reusable */
function initCompareSlider(wrapId, afterId, handleId) {
  const wrap   = document.getElementById(wrapId);
  const after  = document.getElementById(afterId);
  const handle = document.getElementById(handleId);
  if (!wrap) return;

  let dragging = false;
  const update = clientX => {
    const r   = wrap.getBoundingClientRect();
    const pct = Math.max(0, Math.min(100, ((clientX - r.left) / r.width) * 100));
    if (after)  after.style.clipPath = `inset(0 ${100 - pct}% 0 0)`;
    if (handle) handle.style.left    = pct + '%';
  };
  update(50);

  wrap.addEventListener('mousedown',  e => { dragging = true; update(e.clientX); });
  wrap.addEventListener('touchstart', e => { dragging = true; update(e.touches[0].clientX); }, { passive: true });
  document.addEventListener('mouseup',   () => { dragging = false; });
  document.addEventListener('touchend',  () => { dragging = false; });
  document.addEventListener('mousemove', e => { if (dragging) update(e.clientX); });
  document.addEventListener('touchmove', e => { if (dragging) update(e.touches[0].clientX); }, { passive: true });
}

/* ═══════════════════════════════════════════════════════════════
   MULTI BATCH FLOW  (BGR only, 2+ files)
═══════════════════════════════════════════════════════════════ */
function initMultiView() {
  document.getElementById('btn-multi-new')?.addEventListener('click', () => {
    S.results.forEach(r => { URL.revokeObjectURL(r.url); URL.revokeObjectURL(r.origUrl); });
    S.results.clear();
    resetUploadView();
  });

  document.getElementById('btn-multi-add')?.addEventListener('click', () =>
    document.getElementById('dz-input')?.click()
  );

  document.getElementById('btn-multi-zip')?.addEventListener('click', multiDownloadZip);
}

async function startMultiFlow(files) {
  showView('view-multi');
  S.results.clear();
  const grid = document.getElementById('multi-grid');
  if (grid) grid.innerHTML = '';

  for (const file of files) {
    const id      = uid();
    const origUrl = URL.createObjectURL(file);
    const card    = buildMultiCard(id, file.name, origUrl);
    grid?.appendChild(card);

    try {
      const aiBlob = await removeBackground(file, S.quality, (pct, lbl) => updateCardProgress(id, pct, lbl));
      const aiUrl  = URL.createObjectURL(aiBlob);
      S.results.set(id, { blob: aiBlob, url: aiUrl, origUrl, name: file.name });
      setCardDone(id, origUrl, aiUrl, file.name);
      toast(`✓ ${shortName(file.name)}`);
    } catch (err) {
      console.error('[Multi]', err);
      setCardError(id, err?.message ?? 'Error');
      toast(`Gagal: ${shortName(file.name)}`, 'error');
    }
  }

  /* Update ZIP button state */
  const zipBtn = document.getElementById('btn-multi-zip');
  if (zipBtn) zipBtn.disabled = !S.results.size;
}

function buildMultiCard(id, name, origUrl) {
  const d  = document.createElement('div');
  d.id     = `mc-${id}`;
  d.className = 'mc-card mc-card--processing';
  d.innerHTML = `
    <div class="mc-thumb" id="mc-thumb-${id}">
      <img src="${esc(origUrl)}" alt="${esc(shortName(name))}">
      <div class="mc-overlay" id="mc-overlay-${id}">
        <div class="mc-spinner"></div>
        <div class="mc-prog-text" id="mc-pct-${id}">0%</div>
        <div class="mc-step-text" id="mc-step-${id}">Memulai…</div>
        <div class="mc-bar"><div class="mc-bar-fill" id="mc-fill-${id}" style="width:0%"></div></div>
      </div>
    </div>
    <div class="mc-meta">
      <p class="mc-name" title="${esc(name)}">${esc(shortName(name, 22))}</p>
    </div>`;
  return d;
}

function updateCardProgress(id, pct, label) {
  const p = Math.min(Math.round(pct), 100);
  const pctEl  = document.getElementById(`mc-pct-${id}`);
  const fillEl = document.getElementById(`mc-fill-${id}`);
  const stepEl = document.getElementById(`mc-step-${id}`);
  if (pctEl)  pctEl.textContent  = p + '%';
  if (fillEl) fillEl.style.width = p + '%';
  if (stepEl && label) stepEl.textContent = label;
}

function setCardDone(id, origUrl, aiUrl, name) {
  const card    = document.getElementById(`mc-${id}`);
  const overlay = document.getElementById(`mc-overlay-${id}`);
  const thumb   = document.getElementById(`mc-thumb-${id}`);

  overlay?.remove();
  card?.classList.remove('mc-card--processing');

  if (thumb) {
    thumb.innerHTML = `
      <div class="mc-mini-cmp">
        <div class="mc-mini-before"><img src="${esc(origUrl)}" loading="lazy"><span>Sebelum</span></div>
        <div class="mc-mini-after checker-bg"><img src="${esc(aiUrl)}" loading="lazy"><span>Sesudah</span></div>
      </div>`;
  }

  const meta = card?.querySelector('.mc-meta');
  if (meta) {
    meta.innerHTML = `
      <p class="mc-name" title="${esc(name)}">${esc(shortName(name, 22))}</p>
      <div class="mc-actions">
        <a href="${esc(aiUrl)}" download="${esc(stripExt(name))}_nobg.png" class="mc-btn-dl"
           onclick="window.toast('✓ ' + '${esc(shortName(name))}')">
          <i class="fa-solid fa-download"></i> PNG
        </a>
        <button class="mc-btn-del" onclick="window._pfDelCard('${id}')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>`;
  }
}

function setCardError(id, msg) {
  const overlay = document.getElementById(`mc-overlay-${id}`);
  if (overlay) overlay.innerHTML = `
    <div class="mc-error">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <span>${esc((msg || '').slice(0, 60))}</span>
    </div>`;
}

window._pfDelCard = function(id) {
  const r = S.results.get(id);
  if (r) { URL.revokeObjectURL(r.url); URL.revokeObjectURL(r.origUrl); }
  S.results.delete(id);
  document.getElementById(`mc-${id}`)?.remove();
  if (!S.results.size) resetUploadView();
};

async function multiDownloadZip() {
  if (!S.results.size) return;
  if (!window.JSZip) { toast('JSZip belum tersedia. Refresh halaman.', 'error'); return; }

  const btn  = document.getElementById('btn-multi-zip');
  const orig = btn?.innerHTML;
  if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Membuat ZIP…'; }

  try {
    const zip = new window.JSZip();
    for (const [, r] of S.results)
      zip.file(stripExt(r.name) + '_nobg.png', await r.blob.arrayBuffer());
    const blob = await zip.generateAsync({ type: 'blob', compression: 'DEFLATE', compressionOptions: { level: 6 } });
    const url  = URL.createObjectURL(blob);
    triggerDownload(url, `mediatools_nobg_${Date.now()}.zip`);
    setTimeout(() => URL.revokeObjectURL(url), 12_000);
    toast(`✓ ZIP (${S.results.size} file) siap diunduh!`);
  } catch (err) {
    toast('Gagal buat ZIP: ' + err.message, 'error');
  } finally {
    if (btn) { btn.disabled = false; btn.innerHTML = orig; }
  }
}

/* ─────────────────────────────────────────────────────────────
   EXPOSE GLOBALLY (for onclick attributes + external use)
───────────────────────────────────────────────────────────── */
window.toast = toast;

/* ─────────────────────────────────────────────────────────────
   UTILITIES
───────────────────────────────────────────────────────────── */
const sleep    = ms => new Promise(r => setTimeout(r, ms));
const uid      = ()  => `${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
const stripExt = n   => n.replace(/\.[^/.]+$/, '');
const esc      = s   => String(s).replace(/[&<>"']/g, c =>
  ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' })[c]);
function shortName(name, max = 24) {
  if (name.length <= max) return name;
  const ext = name.includes('.') ? '.' + name.split('.').pop() : '';
  return name.slice(0, max - ext.length - 3) + '…' + ext;
}