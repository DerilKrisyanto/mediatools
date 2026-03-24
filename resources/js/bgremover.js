/**
 * ============================================================
 *  MediaTools — Background Remover
 *  resources/js/bgremover.js  (Vite entry point)
 *
 *  AI Engine : @imgly/background-removal v1.4.5 (RMBG-1.4 / ONNX)
 *              + Luminance-Aware Alpha Recovery (LAAR)
 *
 *  Editor    : Canvas-based manual touch-up (single image only)
 *              Remove Area · Restore Area · Brush Size · Undo · Redo
 *
 *  Multi mode: AI only — no editor — grid results with ZIP download
 * ============================================================
 */

import { removeBackground } from '@imgly/background-removal';

/* ══════════════════════════════════════════════════════
   CONSTANTS
══════════════════════════════════════════════════════ */
const MAX_BYTES      = 20 * 1024 * 1024;  // 20 MB
const ACCEPTED       = new Set(['image/jpeg','image/jpg','image/png','image/webp']);
const MAX_UNDO       = 25;
const MAX_EDITOR_DIM = 1800;   // px — scale very large images for editor perf

/* ── LAAR tuning ── */
const LAAR = {
  blurRadius    : 22,    // soft-expansion zone radius (px)
  zoneThreshold : 28,    // minimum zone-gradient value (0–255) to consider
  lumThreshold  : 120,   // minimum pixel luminance to restore as foreground
  alphaBoost    : 3.2,   // boost before clamping to 255
  alphaMin      : 105,   // minimum restored alpha
  coreAlpha     : 45,    // raw alpha ≥ this = confirmed foreground
};

/* ══════════════════════════════════════════════════════
   APP STATE
══════════════════════════════════════════════════════ */
const S = {
  results : new Map(),    // id → { blob, objectUrl, originalName }
  bg      : 'transparent',
  model   : 'small',      // 'small' (INT8) | 'medium' (FP32)
  format  : 'png',
};

/* ── Editor state ── */
const ED = {
  active      : false,
  W           : 0,
  H           : 0,
  origRGBA    : null,   // Uint8ClampedArray(W*H*4) — frozen original
  initAlpha   : null,   // Uint8Array(W*H) — AI result alpha, for reset
  curAlpha    : null,   // Uint8Array(W*H) — current editable alpha
  checker     : null,   // Uint8Array(W*H) — checkerboard background
  undoStack   : [],
  redoStack   : [],
  tool        : 'remove',  // 'remove' | 'restore'
  brushSize   : 30,
  isDrawing   : false,
  lastX       : -1,
  lastY       : -1,
  rafId       : null,
  pendingRect : null,   // { x0,y0,x1,y1 } dirty region for rAF
  origFileName: '',
  // DOM (set in initEditor)
  displayCanvas: null,
  brushOverlay : null,
};

/* ══════════════════════════════════════════════════════
   DOM REFS
══════════════════════════════════════════════════════ */
const EL = {
  dropzone: document.getElementById('dropzone'),
  fileInput: document.getElementById('fileInput'),
  btnBrowse: document.getElementById('btnBrowse'),
};

function bindDOM() {
  [
    // Layout
    'bgrMain','bgrRight','bgrEmpty','bgrResults','bgrBulkActions',
    // Upload
    'dropzone','fileInput','btnBrowse',
    // Controls
    'bgSwatches','qualityBtns','formatBtns','customColor',
    // Bulk
    'btnClearAll','btnAddMore','btnDownloadZip',
    // Editor view
    'editorView','editorToolbar','multiNotice',
    'btnRemoveArea','btnRestoreArea',
    'brushSizeSlider','brushSizeVal',
    'btnUndo','btnRedo','btnEditReset',
    'btnDownloadPNG','btnDownloadJPG','btnProcessAnother',
    'origCanvas','displayCanvas','brushOverlay','canvasWrapper',
    // Toast
    'bgrToast','bgrToastMsg',
  ].forEach(id => { EL[id] = document.getElementById(id); });
}

/* ══════════════════════════════════════════════════════
   BOOT
══════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  bindDOM();
  setupDropzone();
  setupControls();
  setupBulkActions();
  setupEditorUI();
  uiShowMain();
  uiEmpty(true);
  uiBulk(false);
});

/* ══════════════════════════════════════════════════════
   DROPZONE
══════════════════════════════════════════════════════ */
function setupDropzone() {
  if (!EL || !EL.dropzone || !EL.fileInput || !EL.btnBrowse) {
    console.error('Dropzone element tidak ditemukan', EL);
    return;
  }

  EL.dropzone.addEventListener('click', () => EL.fileInput.click());

  EL.btnBrowse.addEventListener('click', e => {
    e.stopPropagation();
    EL.fileInput.click();
  });

  EL.fileInput.addEventListener('change', e => {
    handleFiles([...e.target.files]);
    EL.fileInput.value = '';
  });

  ['dragenter','dragover'].forEach(ev =>
    EL.dropzone.addEventListener(ev, e => {
      e.preventDefault();
      EL.dropzone.classList.add('drag-over');
    })
  );

  ['dragleave','dragend'].forEach(ev =>
    EL.dropzone.addEventListener(ev, () =>
      EL.dropzone.classList.remove('drag-over')
    )
  );

  EL.dropzone.addEventListener('drop', e => {
    e.preventDefault();
    EL.dropzone.classList.remove('drag-over');

    if (!e.dataTransfer) return;

    handleFiles([...e.dataTransfer.files].filter(f => ACCEPTED.has(f.type)));
  });

  document.addEventListener('paste', e => {
    if (!e.clipboardData) return;

    const imgs = [...e.clipboardData.files].filter(f => ACCEPTED.has(f.type));
    if (imgs.length) handleFiles(imgs);
  });
}

/* ══════════════════════════════════════════════════════
   CONTROLS
══════════════════════════════════════════════════════ */
function setupControls() {
  EL.bgSwatches.addEventListener('click', e => {
    const sw = e.target.closest('[data-bg]');
    if (!sw) return;
    EL.bgSwatches.querySelectorAll('.bgr-swatch').forEach(s => s.classList.remove('active'));
    sw.classList.add('active');
    S.bg = sw.dataset.bg;
  });
  EL.customColor.addEventListener('input', e => {
    S.bg = e.target.value;
    EL.bgSwatches.querySelectorAll('.bgr-swatch').forEach(s => s.classList.remove('active'));
    e.target.closest('.bgr-swatch').classList.add('active');
  });
  EL.qualityBtns.addEventListener('click', e => {
    const btn = e.target.closest('[data-q]');
    if (!btn) return;
    EL.qualityBtns.querySelectorAll('.bgr-q-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    S.model = btn.dataset.q === 'high' ? 'medium' : 'small';
  });
  EL.formatBtns.addEventListener('click', e => {
    const btn = e.target.closest('[data-fmt]');
    if (!btn) return;
    EL.formatBtns.querySelectorAll('.bgr-f-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    S.format = btn.dataset.fmt;
  });
}

/* ══════════════════════════════════════════════════════
   BULK ACTIONS (multi-mode)
══════════════════════════════════════════════════════ */
function setupBulkActions() {
  EL.btnClearAll.addEventListener('click', () => {
    S.results.forEach(r => URL.revokeObjectURL(r.objectUrl));
    S.results.clear();
    EL.bgrResults.innerHTML = '';
    uiEmpty(true); uiBulk(false);
    toast('Semua gambar dihapus');
  });
  EL.btnAddMore.addEventListener('click', () => EL.fileInput.click());
  EL.btnDownloadZip.addEventListener('click', multiDownloadZip);
}

/* ══════════════════════════════════════════════════════
   EDITOR CONTROLS SETUP
══════════════════════════════════════════════════════ */
function setupEditorUI() {
  /* Tool buttons */
  EL.editorToolbar?.addEventListener('click', e => {
    const btn = e.target.closest('[data-tool]');
    if (!btn) return;
    EL.editorToolbar.querySelectorAll('.bgr-tool-btn[data-tool]').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    ED.tool = btn.dataset.tool;
  });

  /* Brush size */
  EL.brushSizeSlider?.addEventListener('input', e => {
    ED.brushSize = +e.target.value;
    if (EL.brushSizeVal) EL.brushSizeVal.textContent = ED.brushSize + 'px';
  });

  /* Undo / Redo */
  EL.btnUndo?.addEventListener('click', editorUndo);
  EL.btnRedo?.addEventListener('click', editorRedo);

  /* Keyboard shortcuts */
  document.addEventListener('keydown', e => {
    if (!ED.active) return;
    const ctrl = e.ctrlKey || e.metaKey;
    if (ctrl && e.key === 'z' && !e.shiftKey) { e.preventDefault(); editorUndo(); }
    if (ctrl && (e.key === 'y' || (e.key === 'z' && e.shiftKey))) { e.preventDefault(); editorRedo(); }
  });

  /* Reset */
  EL.btnEditReset?.addEventListener('click', () => {
    if (confirm('Reset semua perubahan ke hasil AI?')) editorReset();
  });

  /* Download */
  EL.btnDownloadPNG?.addEventListener('click', () => editorDownload('png'));
  EL.btnDownloadJPG?.addEventListener('click', () => editorDownload('jpg'));

  /* Process Another */
  EL.btnProcessAnother?.addEventListener('click', exitEditor);
}

/* ══════════════════════════════════════════════════════
   FILE HANDLER — routes to single or multi flow
══════════════════════════════════════════════════════ */
async function handleFiles(files) {
  const valid = files.filter(f => {
    if (!ACCEPTED.has(f.type)) { toast(`.${extOf(f.name)} tidak didukung`, 'error'); return false; }
    if (f.size > MAX_BYTES)    { toast(`${shortName(f.name)} melebihi 20 MB`, 'error'); return false; }
    return true;
  });
  if (!valid.length) return;

  if (valid.length === 1) {
    await processSingleFlow(valid[0]);
  } else {
    await processMultiFlow(valid);
  }
}

/* ══════════════════════════════════════════════════════
   SINGLE IMAGE FLOW  →  AI  →  Editor
══════════════════════════════════════════════════════ */
async function processSingleFlow(file) {
  uiShowMain();
  uiEmpty(false);
  uiBulk(false);

  const id   = uid();
  const card = buildCard(id, file.name);
  EL.bgrResults.appendChild(card);

  const laarPng = await runAI(id, file);
  if (!laarPng) return;

  await sleep(250);
  await openEditor(file, laarPng);
}

/* ══════════════════════════════════════════════════════
   MULTI-IMAGE FLOW  →  AI grid  (no editor)
══════════════════════════════════════════════════════ */
async function processMultiFlow(files) {
  toast('Mode batch aktif — editor hanya tersedia untuk 1 gambar', 'error');
  uiShowMain();
  uiEmpty(false);
  uiBulk(true);

  for (const file of files) {
    const id   = uid();
    const card = buildCard(id, file.name);
    EL.bgrResults.appendChild(card);
    const laarPng = await runAI(id, file);
    if (!laarPng) continue;

    const objectUrl = URL.createObjectURL(laarPng);
    S.results.set(id, { blob: laarPng, objectUrl, originalName: file.name });

    const ui  = getCardUI(id);
    const img = new Image();
    img.src = objectUrl; img.alt = file.name; img.loading = 'lazy';
    ui.overlay.remove(); ui.pbar.remove();
    ui.preview.appendChild(img);
    ui.card.classList.remove('is-processing');
    ui.actions.style.display = 'flex';
    ui.btnDl.addEventListener('click', () => multiDownloadOne(id));
    ui.btnDel.addEventListener('click', () => removeCard(id));
    toast(`✓ ${shortName(file.name, 28)}`);
  }
}

/* ══════════════════════════════════════════════════════
   CORE AI:  RMBG-1.4  +  LAAR
══════════════════════════════════════════════════════ */
async function runAI(id, file) {
  const ui = getCardUI(id);
  const setP = (pct, label) => {
    ui.prog.textContent = Math.min(Math.round(pct), 100) + '%';
    ui.fill.style.width = Math.min(pct, 100) + '%';
    if (label) ui.step.textContent = label;
  };

  try {
    setP(3, 'Mempersiapkan…');
    if (file.size > 5 * 1024 * 1024) {
      S.model = 'small'; // auto downgrade biar cepat
    }

    /* ── RMBG-1.4 inference ─────────────────────────────
     * model "small" = isnet_quint8 (INT8 quantized, ~3-5s)
     * model "medium"= isnet        (FP32 balanced,  ~6-9s)
     * proxyToWorker: inference in Web Worker → UI stays live
     ────────────────────────────────────────────────────── */
    const rawPng = await removeBackground(file, {
      model         : S.model,
      proxyToWorker : true,
      output        : { format: 'image/png', quality: 1 },
      debug         : false,
      progress(key, cur, tot) {
        const r = tot > 0 ? cur / tot : 0;
        if (key.startsWith('fetch'))
          setP(5 + r * 68, r < 0.99 ? `Mengunduh model AI… ${Math.round(r*100)}%` : 'Model siap…');
        else if (key.startsWith('compute'))
          setP(73 + r * 14, r < 0.5 ? 'AI segmentasi…' : 'Membangun mask…');
      },
    });

    /* ── LAAR post-processing ── */
    setP(88, 'Memperbaiki detail rambut & tepi…');
    await sleep(20);   // yield so progress bar renders
    const laarPng = await luminanceAwareAlphaRecovery(rawPng);

    setP(100, 'Selesai!');
    return laarPng;

  } catch (err) {
    console.error('[BgRemover]', err);
    ui.overlay.innerHTML = `
      <div style="text-align:center;padding:14px 10px">
        <i class="fa-solid fa-circle-exclamation"
           style="color:#f87171;font-size:1.5rem;display:block;margin-bottom:8px"></i>
        <strong style="color:#f87171;font-size:11px">Gagal memproses</strong><br>
        <small style="font-size:10px;color:#9ca3af;display:block;margin-top:4px;line-height:1.5">
          ${esc(trimMsg(err))}
        </small>
      </div>`;
    ui.pbar.remove();
    toast(`Gagal: ${shortName(file.name, 26)}`, 'error');
    return null;
  }
}

/* ══════════════════════════════════════════════════════
   LAAR — Luminance-Aware Alpha Recovery
   GPU-accelerated via canvas CSS blur filter.
   Recovers bright hair/fur pixels that RMBG under-masks.
══════════════════════════════════════════════════════ */
function luminanceAwareAlphaRecovery(pngBlob) {
  return new Promise((resolve, reject) => {
    const src = URL.createObjectURL(pngBlob);
    const img = new Image();

    img.onload = () => {
      URL.revokeObjectURL(src);
      const W = img.naturalWidth, H = img.naturalHeight, n = W * H;

      /* Read raw RGBA */
      const mc   = mkCanvas(W, H);
      const mctx = mc.getContext('2d', { willReadFrequently: true });
      mctx.drawImage(img, 0, 0);
      const id = mctx.getImageData(0, 0, W, H);
      const d  = id.data;

      /* Build binary core mask (alpha ≥ coreAlpha → 255) */
      const core    = new Uint8Array(n);
      const coreImg = new ImageData(W, H);
      for (let i = 0; i < n; i++) {
        const v = d[i*4+3] >= LAAR.coreAlpha ? 255 : 0;
        core[i] = v;
        coreImg.data[i*4] = coreImg.data[i*4+1] = coreImg.data[i*4+2] = v;
        coreImg.data[i*4+3] = 255;
      }

      /* GPU blur: draw core → blur → read zone gradient */
      const mc2 = mkCanvas(W, H);
      mc2.getContext('2d').putImageData(coreImg, 0, 0);

      const mc3  = mkCanvas(W, H);
      const c3   = mc3.getContext('2d', { willReadFrequently: true });
      c3.filter  = `blur(${LAAR.blurRadius}px)`;
      c3.drawImage(mc2, 0, 0);
      c3.filter  = 'none';
      const blurD = c3.getImageData(0, 0, W, H).data;

      /* Build refined alpha */
      const newA = new Uint8Array(n);
      for (let i = 0; i < n; i++) {
        const rawA  = d[i*4+3];
        const blurV = blurD[i*4];
        if (rawA >= LAAR.coreAlpha)       { newA[i] = 255; continue; }
        if (blurV < LAAR.zoneThreshold)   { newA[i] = 0;   continue; }
        const r = d[i*4], g = d[i*4+1], b = d[i*4+2];
        const lum = (r*299 + g*587 + b*114) / 1000;
        if (lum >= LAAR.lumThreshold) {
          newA[i] = Math.max(LAAR.alphaMin,
            Math.min(255, Math.round((blurV/255) * (lum/255) * 255 * LAAR.alphaBoost)));
        } else {
          newA[i] = rawA;  // keep dark pixels (shadow/line-art between strands)
        }
      }

      /* GPU feather: blur the alpha mask gently for smooth edges */
      const fImg = new ImageData(W, H);
      for (let i = 0; i < n; i++) {
        fImg.data[i*4] = fImg.data[i*4+1] = fImg.data[i*4+2] = newA[i];
        fImg.data[i*4+3] = 255;
      }
      const mc4 = mkCanvas(W, H);
      mc4.getContext('2d').putImageData(fImg, 0, 0);
      const mc5  = mkCanvas(W, H);
      const c5   = mc5.getContext('2d', { willReadFrequently: true });
      c5.filter  = 'blur(1.5px)';
      c5.drawImage(mc4, 0, 0);
      c5.filter  = 'none';
      const fD = c5.getImageData(0, 0, W, H).data;

      /* Blend feathered alpha into transition zone only */
      for (let i = 0; i < n; i++) {
        if (newA[i] === 0 || newA[i] >= 240) continue;
        newA[i] = Math.round(newA[i] * 0.55 + fD[i*4] * 0.45);
      }

      /* Write back → export PNG */
      for (let i = 0; i < n; i++) d[i*4+3] = newA[i];
      mctx.putImageData(id, 0, 0);
      mc.toBlob(b => b ? resolve(b) : reject(new Error('LAAR toBlob failed')), 'image/png');
    };

    img.onerror = () => { URL.revokeObjectURL(src); reject(new Error('LAAR img load failed')); };
    img.src = src;
  });
}

/* ══════════════════════════════════════════════════════
   EDITOR — open
══════════════════════════════════════════════════════ */
function openEditor(origFile, laarPngBlob) {
  return new Promise((resolve, reject) => {
    const src = URL.createObjectURL(laarPngBlob);
    const img = new Image();

    img.onload = () => {
      URL.revokeObjectURL(src);
      let W = img.naturalWidth, H = img.naturalHeight;

      /* Scale down very large images for editor performance */
      if (W > MAX_EDITOR_DIM || H > MAX_EDITOR_DIM) {
        const s = Math.min(MAX_EDITOR_DIM / W, MAX_EDITOR_DIM / H);
        W = Math.round(W * s);
        H = Math.round(H * s);
      }

      /* Read RGBA at editor dimensions */
      const ec   = mkCanvas(W, H);
      const ectx = ec.getContext('2d', { willReadFrequently: true });
      ectx.drawImage(img, 0, 0, W, H);
      const id = ectx.getImageData(0, 0, W, H);
      const d  = id.data;

      /* Freeze original state */
      ED.W            = W;
      ED.H            = H;
      ED.origRGBA     = new Uint8ClampedArray(d);  // never modified
      ED.initAlpha    = new Uint8Array(W * H);
      ED.curAlpha     = new Uint8Array(W * H);
      ED.undoStack    = [];
      ED.redoStack    = [];
      ED.tool         = 'remove';
      ED.brushSize    = 30;
      ED.isDrawing    = false;
      ED.origFileName = origFile.name;
      ED.active       = true;
      if (EL.brushSizeSlider) EL.brushSizeSlider.value = '30';
      if (EL.brushSizeVal) EL.brushSizeVal.textContent = '30px';

      for (let i = 0; i < W * H; i++) {
        ED.initAlpha[i] = ED.curAlpha[i] = d[i*4+3];
      }

      /* Precompute checkerboard */
      ED.checker = buildChecker(W, H);

      /* Setup canvases */
      EL.displayCanvas.width  = W;  EL.displayCanvas.height = H;
      EL.brushOverlay.width   = W;  EL.brushOverlay.height  = H;
      EL.origCanvas.width     = W;  EL.origCanvas.height    = H;

      ED.displayCanvas = EL.displayCanvas;
      ED.brushOverlay  = EL.brushOverlay;

      /* Draw original on left panel */
      EL.origCanvas.getContext('2d').drawImage(img, 0, 0, W, H);

      /* Initial full render */
      renderEditor();

      /* Attach brush events */
      attachBrushEvents();

      /* Reset tool UI */
      EL.editorToolbar?.querySelectorAll('.bgr-tool-btn[data-tool]').forEach(b => b.classList.remove('active'));
      EL.btnRemoveArea?.classList.add('active');
      updateUndoRedoBtns();

      /* Switch view */
      uiShowEditor();
      toast('Gunakan Remove untuk hapus background, Restore untuk kembalikan detail');
      resolve();
    };

    img.onerror = () => { URL.revokeObjectURL(src); reject(new Error('Editor img load failed')); };
    img.src = src;
  });
}

/* ── Precompute checkerboard Uint8 array ── */
function buildChecker(W, H) {
  const CELL = 10;
  const cb = new Uint8Array(W * H);
  for (let y = 0; y < H; y++) {
    for (let x = 0; x < W; x++) {
      cb[y * W + x] = (((x/CELL)|0) ^ ((y/CELL)|0)) & 1 ? 138 : 193;
    }
  }
  return cb;
}

/* ══════════════════════════════════════════════════════
   EDITOR RENDER
   Composites origRGBA × curAlpha over checkerboard.
   Uses partial rect update for brush performance.
══════════════════════════════════════════════════════ */
function renderEditor(rect) {
  const { W, H, origRGBA, curAlpha, checker, displayCanvas } = ED;
  const ctx = displayCanvas.getContext('2d');

  const x0 = rect?.x0 ?? 0,  y0 = rect?.y0 ?? 0;
  const x1 = rect?.x1 ?? W-1, y1 = rect?.y1 ?? H-1;
  const rw = x1 - x0 + 1,   rh = y1 - y0 + 1;

  const buf = new Uint8ClampedArray(rw * rh * 4);

  for (let ry = 0; ry < rh; ry++) {
    for (let rx = 0; rx < rw; rx++) {
      const px = x0 + rx, py = y0 + ry;
      const si = (py * W + px) * 4;
      const di = (ry * rw + rx) * 4;
      const a  = curAlpha[py * W + px] / 255;
      const ia = 1 - a;
      const c  = checker[py * W + px];
      buf[di]   = (origRGBA[si]   * a + c * ia + 0.5) | 0;
      buf[di+1] = (origRGBA[si+1] * a + c * ia + 0.5) | 0;
      buf[di+2] = (origRGBA[si+2] * a + c * ia + 0.5) | 0;
      buf[di+3] = 255;
    }
  }

  ctx.putImageData(new ImageData(buf, rw, rh), x0, y0);
}

/* ── Schedule partial render via rAF ── */
function scheduleRender(rect) {
  if (ED.rafId) {
    if (rect && ED.pendingRect) {
      ED.pendingRect.x0 = Math.min(ED.pendingRect.x0, rect.x0);
      ED.pendingRect.y0 = Math.min(ED.pendingRect.y0, rect.y0);
      ED.pendingRect.x1 = Math.max(ED.pendingRect.x1, rect.x1);
      ED.pendingRect.y1 = Math.max(ED.pendingRect.y1, rect.y1);
    } else if (!rect) {
      ED.pendingRect = null;
    }
    return;
  }
  ED.pendingRect = rect ? { ...rect } : null;
  ED.rafId = requestAnimationFrame(() => {
    ED.rafId = null;
    renderEditor(ED.pendingRect);
    ED.pendingRect = null;
  });
}

/* ══════════════════════════════════════════════════════
   BRUSH EVENTS
══════════════════════════════════════════════════════ */
function attachBrushEvents() {
  const c = ED.brushOverlay;

  /* Remove stale listeners by cloning */
  const fresh = c.cloneNode(false);
  fresh.id = 'brushOverlay';
  fresh.width  = ED.W;
  fresh.height = ED.H;
  c.parentNode.replaceChild(fresh, c);
  ED.brushOverlay = EL.brushOverlay = fresh;

  /* Coord translation: CSS display px → logical canvas px */
  const pos = e => {
    const r  = fresh.getBoundingClientRect();
    const sx = ED.W / r.width;
    const sy = ED.H / r.height;
    const src = e.touches ? e.touches[0] : e;
    return {
      x: Math.round((src.clientX - r.left) * sx),
      y: Math.round((src.clientY - r.top)  * sy),
    };
  };

  const onDown = e => {
    e.preventDefault();
    ED.isDrawing = true;
    pushUndo();
    const { x, y } = pos(e);
    ED.lastX = x; ED.lastY = y;
    paintStroke(x, y, x, y);
    drawCursor(x, y);
  };

  const onMove = e => {
    e.preventDefault();
    const { x, y } = pos(e);
    drawCursor(x, y);
    if (!ED.isDrawing) return;
    paintStroke(ED.lastX, ED.lastY, x, y);
    ED.lastX = x; ED.lastY = y;
  };

  const onUp = () => { ED.isDrawing = false; };

  fresh.addEventListener('mousedown',  onDown);
  fresh.addEventListener('mousemove',  onMove);
  fresh.addEventListener('mouseup',    onUp);
  fresh.addEventListener('mouseleave', () => { onUp(); clearCursor(); });
  fresh.addEventListener('touchstart', onDown, { passive: false });
  fresh.addEventListener('touchmove',  onMove, { passive: false });
  fresh.addEventListener('touchend',   onUp);
}

/* ── Paint stroke with linear interpolation ── */
function paintStroke(x0, y0, x1, y1) {
  const { W, H, curAlpha, initAlpha, tool, brushSize, origRGBA } = ED;

  const dist  = Math.hypot(x1-x0, y1-y0);
  const steps = Math.max(1, Math.ceil(dist / Math.max(1, brushSize * 0.15)));

  let dx0 = Infinity, dy0 = Infinity, dx1 = -1, dy1 = -1;

  for (let s = 0; s <= steps; s++) {
    const t  = steps === 0 ? 0 : s / steps;
    const cx = Math.round(x0 + (x1-x0) * t);
    const cy = Math.round(y0 + (y1-y0) * t);

    const r  = brushSize;
    const r2 = r * r;

    const bx0 = Math.max(0, cx-r);
    const by0 = Math.max(0, cy-r);
    const bx1 = Math.min(W-1, cx+r);
    const by1 = Math.min(H-1, cy+r);

    for (let py = by0; py <= by1; py++) {
      for (let px = bx0; px <= bx1; px++) {

        const ddx = px-cx, ddy = py-cy;
        const d2  = ddx*ddx + ddy*ddy;
        if (d2 > r2) continue;

        const i = py * W + px;

        /* 🔥 Gaussian falloff (lebih smooth) */
        const str = Math.exp(-d2 / (r*r));

        /* 🔥 EDGE DETECTION (biar nempel objek) */
        const si = i * 4;
        const lum = (origRGBA[si]*299 + origRGBA[si+1]*587 + origRGBA[si+2]*114) / 1000;

        let edgeBoost = 1;
        if (lum > 200 || lum < 40) edgeBoost = 1.2;

        const finalStr = str * edgeBoost;

        if (tool === 'remove') {
          curAlpha[i] = Math.max(0, Math.round(curAlpha[i] * (1 - finalStr)));
        } else {
          curAlpha[i] = Math.min(255,
            Math.round(curAlpha[i] + (initAlpha[i] - curAlpha[i]) * finalStr));
        }
      }
    }

    dx0 = Math.min(dx0, bx0);
    dy0 = Math.min(dy0, by0);
    dx1 = Math.max(dx1, bx1);
    dy1 = Math.max(dy1, by1);
  }

  if (dx0 <= dx1) scheduleRender({ x0:dx0, y0:dy0, x1:dx1, y1:dy1 });
}

/* ── Brush cursor (drawn on brushOverlay canvas) ── */
function drawCursor(x, y) {
  const ctx = ED.brushOverlay?.getContext('2d');
  if (!ctx) return;
  ctx.clearRect(0, 0, ED.W, ED.H);

  const isRemove = ED.tool === 'remove';
  const color    = isRemove ? 'rgba(248,113,113,0.9)' : 'rgba(163,230,53,0.9)';
  const fill     = isRemove ? '#f87171' : '#a3e635';

  /* Outer ring */
  ctx.beginPath();
  ctx.arc(x, y, ED.brushSize, 0, Math.PI * 2);
  ctx.strokeStyle = color;
  ctx.lineWidth   = Math.max(1, ED.brushSize * 0.04);
  ctx.shadowColor = color;
  ctx.shadowBlur = 8;
  ctx.stroke();

  /* Inner fill dot */
  ctx.beginPath();
  ctx.arc(x, y, 3, 0, Math.PI * 2);
  ctx.fillStyle = fill;
  ctx.fill();
}

function clearCursor() {
  const ctx = ED.brushOverlay?.getContext('2d');
  if (ctx) ctx.clearRect(0, 0, ED.W, ED.H);
}

/* ══════════════════════════════════════════════════════
   UNDO / REDO
══════════════════════════════════════════════════════ */
function pushUndo() {
  ED.undoStack.push(ED.curAlpha.slice());
  if (ED.undoStack.length > MAX_UNDO) ED.undoStack.shift();
  ED.redoStack = [];
  updateUndoRedoBtns();
}

function editorUndo() {
  if (!ED.undoStack.length) return;
  ED.redoStack.push(ED.curAlpha.slice());
  ED.curAlpha = ED.undoStack.pop();
  updateUndoRedoBtns();
  scheduleRender();
}

function editorRedo() {
  if (!ED.redoStack.length) return;
  ED.undoStack.push(ED.curAlpha.slice());
  ED.curAlpha = ED.redoStack.pop();
  updateUndoRedoBtns();
  scheduleRender();
}

function editorReset() {
  pushUndo();
  ED.curAlpha = ED.initAlpha.slice();
  scheduleRender();
}

function updateUndoRedoBtns() {
  if (EL.btnUndo) EL.btnUndo.disabled = ED.undoStack.length === 0;
  if (EL.btnRedo) EL.btnRedo.disabled = ED.redoStack.length === 0;
}

/* ══════════════════════════════════════════════════════
   EDITOR DOWNLOAD
══════════════════════════════════════════════════════ */
async function editorDownload(fmt) {
  const btn  = fmt === 'png' ? EL.btnDownloadPNG : EL.btnDownloadJPG;
  const orig = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

  try {
    const { W, H, origRGBA, curAlpha } = ED;
    const buf = new Uint8ClampedArray(W * H * 4);
    for (let i = 0; i < W * H; i++) {
      buf[i*4]   = origRGBA[i*4];
      buf[i*4+1] = origRGBA[i*4+1];
      buf[i*4+2] = origRGBA[i*4+2];
      buf[i*4+3] = curAlpha[i];
    }

    const c   = mkCanvas(W, H);
    const ctx = c.getContext('2d');

    if (S.bg !== 'transparent' || fmt === 'jpg') {
      ctx.fillStyle = S.bg !== 'transparent' ? S.bg : '#ffffff';
      ctx.fillRect(0, 0, W, H);
    }

    /* Draw subject with alpha */
    const tmpC = mkCanvas(W, H);
    tmpC.getContext('2d').putImageData(new ImageData(buf, W, H), 0, 0);
    ctx.drawImage(tmpC, 0, 0);

    const mime = fmt === 'jpg' ? 'image/jpeg' : 'image/png';
    c.toBlob(blob => {
      if (!blob) return;
      const url  = URL.createObjectURL(blob);
      const name = stripExt(ED.origFileName) + '_nobg.' + fmt;
      triggerDl(url, name);
      setTimeout(() => URL.revokeObjectURL(url), 5000);
      toast(`↓ ${name} diunduh`);
    }, mime, fmt === 'jpg' ? 0.93 : undefined);

  } finally {
    setTimeout(() => { btn.disabled = false; btn.innerHTML = orig; }, 1500);
  }
}

/* ══════════════════════════════════════════════════════
   EXIT EDITOR
══════════════════════════════════════════════════════ */
function exitEditor() {
  ED.active = false;
  if (ED.rafId) { cancelAnimationFrame(ED.rafId); ED.rafId = null; }
  ED.origRGBA = ED.initAlpha = ED.curAlpha = ED.checker = null;
  ED.undoStack = []; ED.redoStack = [];
  clearCursor();

  S.results.clear();
  EL.bgrResults.innerHTML = '';

  uiShowMain();
  uiEmpty(true);
  uiBulk(false);
}

/* ══════════════════════════════════════════════════════
   MULTI: CARD BUILDER
══════════════════════════════════════════════════════ */
function buildCard(id, name) {
  const div     = document.createElement('div');
  div.className = 'bgr-result-card is-processing';
  div.id        = `card-${id}`;
  div.innerHTML = `
    <div class="bgr-rc-preview" id="preview-${id}">
      <div class="bgr-rc-overlay" id="overlay-${id}">
        <div class="bgr-rc-spinner"></div>
        <span class="bgr-rc-progress-text" id="prog-${id}">0%</span>
        <span class="bgr-rc-step-text" id="step-${id}">Memulai…</span>
      </div>
      <div class="bgr-rc-progress-bar" id="pbar-${id}">
        <div class="bgr-rc-progress-fill" id="fill-${id}" style="width:0%"></div>
      </div>
    </div>
    <div class="bgr-rc-info">
      <p class="bgr-rc-name" title="${esc(name)}">${esc(shortName(name))}</p>
      <div class="bgr-rc-actions" id="actions-${id}" style="display:none">
        <button class="bgr-rc-btn-dl"  id="dl-${id}"  type="button">
          <i class="fa-solid fa-download"></i> Download
        </button>
        <button class="bgr-rc-btn-del" id="del-${id}" type="button" aria-label="Hapus">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>`;
  return div;
}

function getCardUI(id) {
  const g = s => document.getElementById(`${s}-${id}`);
  return {
    card:g('card'), preview:g('preview'), overlay:g('overlay'),
    prog:g('prog'), step:g('step'), fill:g('fill'), pbar:g('pbar'),
    actions:g('actions'), btnDl:g('dl'), btnDel:g('del'),
  };
}

function removeCard(id) {
  const r = S.results.get(id);
  if (r) URL.revokeObjectURL(r.objectUrl);
  S.results.delete(id);
  document.getElementById(`card-${id}`)?.remove();
  if (!document.querySelectorAll('.bgr-result-card').length) { uiEmpty(true); uiBulk(false); }
}

function multiDownloadOne(id) {
  const r = S.results.get(id);
  if (!r) return;
  const ext  = S.format === 'jpg' ? 'jpg' : 'png';
  const name = stripExt(r.originalName) + '_nobg.' + ext;
  triggerDl(r.objectUrl, name);
  toast(`↓ ${name}`);
}

async function multiDownloadZip() {
  if (!S.results.size) return;
  const btn  = EL.btnDownloadZip;
  const orig = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Membuat ZIP…';
  try {
    const zip = new window.JSZip();
    const ext = S.format === 'jpg' ? 'jpg' : 'png';
    for (const [, r] of S.results) {
      zip.file(stripExt(r.originalName) + '_nobg.' + ext, await r.blob.arrayBuffer());
    }
    const blob = await zip.generateAsync({ type:'blob', compression:'DEFLATE', compressionOptions:{level:6} });
    const url  = URL.createObjectURL(blob);
    triggerDl(url, `mediatools_bgremover_${Date.now()}.zip`);
    setTimeout(() => URL.revokeObjectURL(url), 10_000);
    toast(`↓ ZIP (${S.results.size} file) siap`);
  } catch (e) {
    console.error('[BgRemover ZIP]', e);
    toast('Gagal membuat ZIP', 'error');
  } finally {
    btn.disabled = false; btn.innerHTML = orig;
  }
}

/* ══════════════════════════════════════════════════════
   UI STATE MACHINE
══════════════════════════════════════════════════════ */
function uiShowMain() {
  EL.bgrMain.style.display    = '';
  EL.editorView.style.display = 'none';
  document.querySelector('.bgr-howto').style.display = '';
}

function uiShowEditor() {
  EL.bgrMain.style.display    = 'none';
  EL.editorView.style.display = '';
  document.querySelector('.bgr-howto').style.display = 'none';
}

function uiEmpty(show)  { EL.bgrEmpty.style.display = show ? 'flex' : 'none'; }
function uiBulk(show)   { EL.bgrBulkActions.classList.toggle('bgr-hidden', !show); }

let _tt;
function toast(msg, type = 'success') {
  EL.bgrToastMsg.textContent = msg;
  EL.bgrToast.className      = `bgr-toast${type === 'error' ? ' error' : ''}`;
  EL.bgrToast.classList.add('show');
  clearTimeout(_tt);
  _tt = setTimeout(() => EL.bgrToast.classList.remove('show'), 3500);
}

/* ══════════════════════════════════════════════════════
   UTILITIES
══════════════════════════════════════════════════════ */
const mkCanvas = (w, h) => Object.assign(document.createElement('canvas'), { width:w, height:h });
const uid      = () => `${Date.now()}-${Math.random().toString(36).slice(2,8)}`;
const sleep    = ms => new Promise(r => setTimeout(r, ms));
const stripExt = n  => n.replace(/\.[^/.]+$/, '');
const extOf    = n  => n.split('.').pop() || '?';
const esc      = s  => String(s).replace(/[&<>"']/g,
  c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]);
const trimMsg  = e  => (e?.message || 'Terjadi kesalahan').slice(0, 90);
function shortName(name, max = 22) {
  if (name.length <= max) return name;
  const ext = name.includes('.') ? '.' + name.split('.').pop() : '';
  return name.slice(0, max - ext.length - 3) + '…' + ext;
}
function triggerDl(url, name) {
  const a = Object.assign(document.createElement('a'), { href:url, download:name, style:'display:none' });
  document.body.appendChild(a); a.click(); a.remove();
}