/**
 * resources/js/bgremover/ui.js
 * All DOM interaction and UI state management.
 *
 * State machine:
 *   upload | processing | result | editor | multi
 */

import { S, ACCEPTED, MAX_BYTES }           from './state';
import { undo, redo, resetToAI, updateButtons } from './editor/history';
import { clearCursor }                       from './editor/canvas';

/* ══════════════════════════════════════════════════════
   BOOT
══════════════════════════════════════════════════════ */
export function initUI() {
  bindDropzone();
  bindControls();
  bindResultView();
  bindEditorToolbar();
  bindBulkActions();
  bindKeyboard();
  initCompareSlider();
  setUIState('upload');
}

/* ══════════════════════════════════════════════════════
   UI STATE MACHINE
══════════════════════════════════════════════════════ */
export function setUIState(state) {
  S.mode = state;

  const views = ['viewUpload','viewProcessing','viewResult','viewEditor','viewMulti'];
  views.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
  });

  const map = {
    upload: 'viewUpload', processing: 'viewProcessing',
    result: 'viewResult', editor: 'viewEditor', multi: 'viewMulti',
  };
  const activeId = map[state];
  const active = activeId ? document.getElementById(activeId) : null;
  if (active) active.style.display = '';

  // Show howto only on upload / multi
  const howto = document.querySelector('.bgr-howto');
  if (howto) howto.style.display = ['upload','multi'].includes(state) ? '' : 'none';
}

/* ══════════════════════════════════════════════════════
   PROGRESS (processing view)
══════════════════════════════════════════════════════ */
export function setProgress(pct, label) {
  const p = Math.min(Math.round(pct), 100);
  const fill  = document.getElementById('progressFill');
  const pctEl = document.getElementById('progressPct');
  const lblEl = document.getElementById('progressLabel');
  if (fill)  fill.style.width  = p + '%';
  if (pctEl) pctEl.textContent = p + '%';
  if (lblEl && label) lblEl.textContent = label;
}

export function setProcessingThumb(file) {
  const el = document.getElementById('processingThumb');
  if (!el) return;
  if (el._blobUrl) URL.revokeObjectURL(el._blobUrl);
  const u = URL.createObjectURL(file);
  el._blobUrl = u;
  el.src = u;
}

/* ══════════════════════════════════════════════════════
   RESULT VIEW — before/after comparison
══════════════════════════════════════════════════════ */
export function setResultView(origUrl, resultUrl, filename) {
  const origImg   = document.getElementById('compareOrigImg');
  const resultImg = document.getElementById('compareResultImg');
  const namEl     = document.getElementById('resultFilename');

  if (origImg)   origImg.src   = origUrl;
  if (resultImg) resultImg.src = resultUrl;
  if (namEl)     namEl.textContent = filename ?? '';

  // Reset compare slider to 50%
  updateCompareSlider(50);
}

function bindResultView() {
  document.getElementById('btnResultEdit')?.addEventListener('click', () => {
    if (!S.origFile || !S.aiBlob) return;
    window._bgrOpenEditor?.(S.origFile, S.aiBlob, 'result');
  });

  document.getElementById('btnResultDownloadPNG')?.addEventListener('click', () => {
    if (!S.aiBlobUrl) return;
    const name = stripExt(S.origFile?.name ?? 'image') + '_nobg.png';
    downloadResult(S.aiBlob, name, 'png');
  });

  document.getElementById('btnResultDownloadJPG')?.addEventListener('click', () => {
    if (!S.aiBlob) return;
    const name = stripExt(S.origFile?.name ?? 'image') + '_nobg.jpg';
    blobWithBg(S.aiBlob, S.bg, name, 'jpg');
  });

  document.getElementById('btnResultNew')?.addEventListener('click', () => {
    if (S.aiBlobUrl) { URL.revokeObjectURL(S.aiBlobUrl); S.aiBlobUrl = null; }
    if (S.origObjectUrl) { URL.revokeObjectURL(S.origObjectUrl); S.origObjectUrl = null; }
    S.aiBlob = S.origFile = null;
    setUIState('upload');
  });
}

/* ── Compare slider ── */
let _cmpDragging = false;
let _cmpPct = 50;

function initCompareSlider() {
  const wrap = document.getElementById('compareWrap');
  if (!wrap) return;

  const getPct = (clientX) => {
    const rect = wrap.getBoundingClientRect();
    return Math.max(0, Math.min(100, ((clientX - rect.left) / rect.width) * 100));
  };

  // Mouse
  wrap.addEventListener('mousedown',  (e) => { _cmpDragging = true; updateCompareSlider(getPct(e.clientX)); });
  document.addEventListener('mouseup',   () => { _cmpDragging = false; });
  document.addEventListener('mousemove', (e) => { if (_cmpDragging) updateCompareSlider(getPct(e.clientX)); });

  // Touch
  wrap.addEventListener('touchstart', (e) => {
    _cmpDragging = true;
    updateCompareSlider(getPct(e.touches[0].clientX));
  }, { passive: true });
  document.addEventListener('touchend',  () => { _cmpDragging = false; });
  document.addEventListener('touchmove', (e) => {
    if (_cmpDragging) updateCompareSlider(getPct(e.touches[0].clientX));
  }, { passive: true });
}

function updateCompareSlider(pct) {
  _cmpPct = pct;
  const after  = document.getElementById('compareAfter');
  const handle = document.getElementById('compareHandle');
  if (after)  after.style.clipPath  = `inset(0 ${100 - pct}% 0 0)`;
  if (handle) handle.style.left     = pct + '%';
}

/* ══════════════════════════════════════════════════════
   DROPZONE
══════════════════════════════════════════════════════ */
function bindDropzone() {
  const dz    = document.getElementById('dropzone');
  const input = document.getElementById('fileInput');
  const btn   = document.getElementById('btnBrowse');

  if (!dz || !input) return;

  dz.addEventListener('click', () => input.click());
  btn?.addEventListener('click', e => { e.stopPropagation(); input.click(); });

  input.addEventListener('change', e => {
    handleFiles([...e.target.files]);
    input.value = '';
  });

  ['dragenter','dragover'].forEach(ev =>
    dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.add('drag-over'); })
  );
  ['dragleave','dragend'].forEach(ev =>
    dz.addEventListener(ev, () => dz.classList.remove('drag-over'))
  );
  dz.addEventListener('drop', e => {
    e.preventDefault();
    dz.classList.remove('drag-over');
    handleFiles([...(e.dataTransfer?.files ?? [])].filter(f => ACCEPTED.has(f.type)));
  });

  document.addEventListener('paste', e => {
    const imgs = [...(e.clipboardData?.files ?? [])].filter(f => ACCEPTED.has(f.type));
    if (imgs.length) handleFiles(imgs);
  });
}

function handleFiles(files) {
  const valid = files.filter(f => {
    if (!ACCEPTED.has(f.type)) { toast(`.${f.name.split('.').pop()} tidak didukung`, 'error'); return false; }
    if (f.size > MAX_BYTES)    { toast(`${shortName(f.name)} melebihi 20 MB`, 'error');         return false; }
    return true;
  });
  if (!valid.length) return;
  window._bgrHandleFiles?.(valid);
}

/* ══════════════════════════════════════════════════════
   OUTPUT CONTROLS (upload panel)
══════════════════════════════════════════════════════ */
function bindControls() {
  document.getElementById('bgSwatches')?.addEventListener('click', e => {
    const sw = e.target.closest('[data-bg]');
    if (!sw) return;
    document.querySelectorAll('#bgSwatches .bgr-swatch').forEach(s => s.classList.remove('active'));
    sw.classList.add('active');
    S.bg = sw.dataset.bg;
  });

  document.getElementById('customColor')?.addEventListener('input', e => {
    S.bg = e.target.value;
    document.querySelectorAll('#bgSwatches .bgr-swatch').forEach(s => s.classList.remove('active'));
    e.target.closest('.bgr-swatch')?.classList.add('active');
  });

  document.getElementById('qualityBtns')?.addEventListener('click', e => {
    const btn = e.target.closest('[data-q]');
    if (!btn) return;
    document.querySelectorAll('#qualityBtns .bgr-q-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    S.model = btn.dataset.q === 'high' ? 'medium' : 'small';
  });

  document.getElementById('formatBtns')?.addEventListener('click', e => {
    const btn = e.target.closest('[data-fmt]');
    if (!btn) return;
    document.querySelectorAll('#formatBtns .bgr-f-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    S.format = btn.dataset.fmt;
  });
}

/* ══════════════════════════════════════════════════════
   EDITOR TOOLBAR
══════════════════════════════════════════════════════ */
function bindEditorToolbar() {
  document.getElementById('editorToolbar')?.addEventListener('click', e => {
    const btn = e.target.closest('[data-tool]');
    if (!btn) return;
    document.querySelectorAll('#editorToolbar [data-tool]').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    S.ed.tool = btn.dataset.tool;
  });

  document.getElementById('brushSizeSlider')?.addEventListener('input', e => {
    S.ed.brushSize = +e.target.value;
    const lbl = document.getElementById('brushSizeVal');
    if (lbl) lbl.textContent = S.ed.brushSize + 'px';
  });

  document.getElementById('btnUndo')?.addEventListener('click', undo);
  document.getElementById('btnRedo')?.addEventListener('click', redo);

  document.getElementById('btnEditReset')?.addEventListener('click', () => {
    if (confirm('Reset semua perubahan ke hasil AI?')) resetToAI();
  });

  document.getElementById('btnDownloadPNG')?.addEventListener('click', () => editorDownload('png'));
  document.getElementById('btnDownloadJPG')?.addEventListener('click', () => editorDownload('jpg'));

  // "Back" button → return to result or multi
  document.getElementById('btnEditorBack')?.addEventListener('click', () => exitEditorUI());

  updateButtons();
}

/* ── Editor download ── */
async function editorDownload(fmt) {
  const btnId = fmt === 'png' ? 'btnDownloadPNG' : 'btnDownloadJPG';
  const btn   = document.getElementById(btnId);
  const orig  = btn?.innerHTML;
  if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>'; }

  try {
    const { W, H, origRGBA, curAlpha } = S.ed;
    const n   = W * H;
    const buf = new Uint8ClampedArray(n * 4);

    // For JPG, composite over chosen bg color
    let bgR = 255, bgG = 255, bgB = 255;
    if (fmt === 'png') {
      // Keep transparent
      for (let i = 0; i < n; i++) {
        buf[i*4]   = origRGBA[i*4];
        buf[i*4+1] = origRGBA[i*4+1];
        buf[i*4+2] = origRGBA[i*4+2];
        buf[i*4+3] = curAlpha[i];
      }
    } else {
      if (S.bg !== 'transparent') {
        const m = S.bg.match(/^#([0-9a-f]{6})$/i);
        if (m) {
          const hex = m[1];
          bgR = parseInt(hex.slice(0,2),16);
          bgG = parseInt(hex.slice(2,4),16);
          bgB = parseInt(hex.slice(4,6),16);
        }
      }
      for (let i = 0; i < n; i++) {
        const a = curAlpha[i] / 255;
        const ia = 1 - a;
        buf[i*4]   = Math.round(origRGBA[i*4]   * a + bgR * ia);
        buf[i*4+1] = Math.round(origRGBA[i*4+1] * a + bgG * ia);
        buf[i*4+2] = Math.round(origRGBA[i*4+2] * a + bgB * ia);
        buf[i*4+3] = 255;
      }
    }

    const c   = mkCanvas(W, H);
    const ctx = c.getContext('2d');
    const tmp = mkCanvas(W, H);
    tmp.getContext('2d').putImageData(new ImageData(buf, W, H), 0, 0);
    ctx.drawImage(tmp, 0, 0);

    const name = stripExt(S.origFile?.name ?? 'image') + '_nobg.' + fmt;
    const mime = fmt === 'jpg' ? 'image/jpeg' : 'image/png';

    c.toBlob(blob => {
      if (!blob) return;
      const url = URL.createObjectURL(blob);
      triggerDl(url, name);
      setTimeout(() => URL.revokeObjectURL(url), 5000);
      toast(`↓ ${name} diunduh`);
    }, mime, fmt === 'jpg' ? 0.93 : undefined);

  } finally {
    setTimeout(() => {
      if (btn) { btn.disabled = false; btn.innerHTML = orig; }
    }, 1500);
  }
}

/* ── Exit editor ── */
export function exitEditorUI() {
  if (S.ed.rafId)  { cancelAnimationFrame(S.ed.rafId); S.ed.rafId = null; }
  clearCursor();
  S.ed.active   = false;
  S.ed.origRGBA = S.ed.initAlpha = S.ed.curAlpha = S.ed.checker = null;
  S.undoStack   = [];
  S.redoStack   = [];
  updateButtons();

  const dest = S.editorReturnTo;

  if (dest === 'result' && S.origObjectUrl && S.aiBlobUrl) {
    setResultView(S.origObjectUrl, S.aiBlobUrl, S.origFile?.name ?? '');
    setUIState('result');
  } else if (dest === 'multi') {
    setUIState('multi');
  } else {
    setUIState('upload');
  }
}

/* ══════════════════════════════════════════════════════
   MULTI-IMAGE CARD BUILDER
══════════════════════════════════════════════════════ */
export function buildMultiCard(id, name) {
  const div     = document.createElement('div');
  div.className = 'bgr-result-card is-processing';
  div.id        = `card-${id}`;
  div.innerHTML = `
    <div class="bgr-rc-preview" id="preview-${id}">
      <div class="bgr-rc-overlay" id="overlay-${id}">
        <div class="bgr-rc-spinner"></div>
        <span class="bgr-rc-progress-text" id="prog-${id}">0%</span>
        <span class="bgr-rc-step-text"     id="step-${id}">Memulai…</span>
      </div>
      <div class="bgr-rc-progress-bar" id="pbar-${id}">
        <div class="bgr-rc-progress-fill" id="fill-${id}" style="width:0%"></div>
      </div>
    </div>
    <div class="bgr-rc-info">
      <p class="bgr-rc-name" title="${esc(name)}">${esc(shortName(name))}</p>
      <div class="bgr-rc-actions" id="actions-${id}" style="display:none">
        <button class="bgr-rc-btn-edit" id="edit-${id}" type="button" title="Edit manual">
          <i class="fa-solid fa-paintbrush"></i>
        </button>
        <a class="bgr-rc-btn-dl" id="dl-${id}" download>
          <i class="fa-solid fa-download"></i> PNG
        </a>
        <button class="bgr-rc-btn-del" id="del-${id}" type="button">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>`;
  return div;
}

export function setCardProgress(id, pct, label) {
  const p = document.getElementById(`prog-${id}`);
  const f = document.getElementById(`fill-${id}`);
  const s = document.getElementById(`step-${id}`);
  if (p) p.textContent = Math.min(Math.round(pct), 100) + '%';
  if (f) f.style.width = Math.min(pct, 100) + '%';
  if (s && label) s.textContent = label;
}

export function setCardDone(id, origObjectUrl, objectUrl) {
  const overlay = document.getElementById(`overlay-${id}`);
  const pbar    = document.getElementById(`pbar-${id}`);
  const preview = document.getElementById(`preview-${id}`);
  const card    = document.getElementById(`card-${id}`);
  const actions = document.getElementById(`actions-${id}`);

  overlay?.remove();
  pbar?.remove();

  // Show before/after mini comparison in card
  if (preview) {
    preview.innerHTML = `
      <div class="bgr-rc-mini-compare">
        <div class="bgr-rc-mini-before"><img src="${esc(origObjectUrl)}" loading="lazy"><span>Sebelum</span></div>
        <div class="bgr-rc-mini-after"><img src="${esc(objectUrl)}" loading="lazy"><span>Sesudah</span></div>
      </div>`;
  }

  card?.classList.remove('is-processing');
  if (actions) actions.style.display = 'flex';

  // Download button
  const dlBtn = document.getElementById(`dl-${id}`);
  if (dlBtn) dlBtn.href = objectUrl;
  dlBtn?.addEventListener('click', () => {
    const r = S.results.get(id);
    if (!r) return;
    dlBtn.download = stripExt(r.name) + '_nobg.png';
    toast(`↓ ${shortName(r.name)}`);
  });

  // Edit button → open brush editor
  document.getElementById(`edit-${id}`)?.addEventListener('click', () => {
    const r = S.results.get(id);
    if (!r) return;
    S.origFile   = r.origFile;
    S.origObjectUrl = r.origObjectUrl;
    S.aiBlob     = r.blob;
    S.aiBlobUrl  = r.objectUrl;
    window._bgrOpenEditor?.(r.origFile, r.blob, 'multi');
  });

  // Delete button
  document.getElementById(`del-${id}`)?.addEventListener('click', () => {
    const r = S.results.get(id);
    if (r) {
      URL.revokeObjectURL(r.objectUrl);
      URL.revokeObjectURL(r.origObjectUrl);
    }
    S.results.delete(id);
    document.getElementById(`card-${id}`)?.remove();
    if (!S.results.size) setUIState('upload');
  });
}

export function setCardError(id, msg) {
  const overlay = document.getElementById(`overlay-${id}`);
  document.getElementById(`pbar-${id}`)?.remove();
  if (overlay) overlay.innerHTML = `
    <div style="text-align:center;padding:14px 10px">
      <i class="fa-solid fa-circle-exclamation"
         style="color:#f87171;font-size:1.5rem;display:block;margin-bottom:8px"></i>
      <strong style="color:#f87171;font-size:11px">Gagal</strong><br>
      <small style="font-size:10px;color:#9ca3af;display:block;margin-top:4px">
        ${esc((msg || '').slice(0, 80))}
      </small>
    </div>`;
}

/* ══════════════════════════════════════════════════════
   BULK ACTIONS (multi view)
══════════════════════════════════════════════════════ */
function bindBulkActions() {
  document.getElementById('btnClearAll')?.addEventListener('click', () => {
    S.results.forEach(r => {
      URL.revokeObjectURL(r.objectUrl);
      URL.revokeObjectURL(r.origObjectUrl);
    });
    S.results.clear();
    const grid = document.getElementById('multiGrid');
    if (grid) grid.innerHTML = '';
    setUIState('upload');
    toast('Semua gambar dihapus');
  });

  document.getElementById('btnAddMore')?.addEventListener('click', () =>
    document.getElementById('fileInput')?.click()
  );

  document.getElementById('btnDownloadZip')?.addEventListener('click', multiDownloadZip);
}

async function multiDownloadZip() {
  if (!S.results.size) return;
  const btn  = document.getElementById('btnDownloadZip');
  const orig = btn?.innerHTML;
  if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> ZIP…'; }
  try {
    const zip = new window.JSZip();
    const ext = 'png';
    for (const [, r] of S.results)
      zip.file(stripExt(r.name) + '_nobg.' + ext, await r.blob.arrayBuffer());
    const blob = await zip.generateAsync({ type: 'blob', compression: 'DEFLATE', compressionOptions: { level: 6 } });
    const url  = URL.createObjectURL(blob);
    triggerDl(url, `mediatools_bgremover_${Date.now()}.zip`);
    setTimeout(() => URL.revokeObjectURL(url), 10_000);
    toast(`↓ ZIP (${S.results.size} file) siap`);
  } catch {
    toast('Gagal membuat ZIP', 'error');
  } finally {
    if (btn) { btn.disabled = false; btn.innerHTML = orig; }
  }
}

/* ══════════════════════════════════════════════════════
   KEYBOARD SHORTCUTS
══════════════════════════════════════════════════════ */
function bindKeyboard() {
  document.addEventListener('keydown', e => {
    if (S.mode !== 'editor') return;
    const ctrl = e.ctrlKey || e.metaKey;
    if (ctrl && e.key === 'z' && !e.shiftKey) { e.preventDefault(); undo(); }
    if (ctrl && (e.key === 'y' || (e.key === 'z' && e.shiftKey))) { e.preventDefault(); redo(); }
  });
}

/* ══════════════════════════════════════════════════════
   TOAST
══════════════════════════════════════════════════════ */
let _tt;
export function toast(msg, type = 'success') {
  const el  = document.getElementById('bgrToast');
  const txt = document.getElementById('bgrToastMsg');
  if (!el) return;
  if (txt) txt.textContent = msg;
  el.className = `bgr-toast${type === 'error' ? ' error' : ''}`;
  el.classList.add('show');
  clearTimeout(_tt);
  _tt = setTimeout(() => el.classList.remove('show'), 4200);
}

/* ══════════════════════════════════════════════════════
   HELPERS
══════════════════════════════════════════════════════ */
function downloadResult(blob, name, fmt) {
  const url = URL.createObjectURL(blob);
  triggerDl(url, name);
  setTimeout(() => URL.revokeObjectURL(url), 5000);
  toast(`↓ ${name} diunduh`);
}

async function blobWithBg(blob, bg, name, fmt) {
  const img = await blobToImg(blob);
  const c   = mkCanvas(img.naturalWidth, img.naturalHeight);
  const ctx = c.getContext('2d');

  if (bg && bg !== 'transparent') {
    ctx.fillStyle = bg;
    ctx.fillRect(0, 0, c.width, c.height);
  }
  ctx.drawImage(img, 0, 0);

  c.toBlob(b => {
    if (!b) return;
    const url = URL.createObjectURL(b);
    triggerDl(url, name);
    setTimeout(() => URL.revokeObjectURL(url), 5000);
    toast(`↓ ${name} diunduh`);
  }, 'image/jpeg', 0.93);
}

function blobToImg(blob) {
  return new Promise((res, rej) => {
    const u = URL.createObjectURL(blob);
    const i = new Image();
    i.onload  = () => { URL.revokeObjectURL(u); res(i); };
    i.onerror = () => { URL.revokeObjectURL(u); rej(new Error('img load')); };
    i.src = u;
  });
}

const mkCanvas  = (w, h) => Object.assign(document.createElement('canvas'), { width:w, height:h });
const stripExt  = n => n.replace(/\.[^/.]+$/, '');
const triggerDl = (url, name) => {
  const a = Object.assign(document.createElement('a'), { href:url, download:name, style:'display:none' });
  document.body.appendChild(a); a.click(); a.remove();
};
const esc = s => String(s).replace(/[&<>"']/g,
  c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]
);
function shortName(name, max = 22) {
  if (name.length <= max) return name;
  const ext = name.includes('.') ? '.' + name.split('.').pop() : '';
  return name.slice(0, max - ext.length - 3) + '…' + ext;
}