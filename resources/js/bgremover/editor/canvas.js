/**
 * resources/js/bgremover/editor/canvas.js
 *
 * Three-canvas editor:
 *   origCanvas    — original image (read-only display, left panel)
 *   displayCanvas — composited result over checkerboard (right panel, editable)
 *   overlayCanvas — brush cursor only (stacked above displayCanvas)
 *
 * KEY DESIGN:
 *   origRGBA stores colors from origFile (the raw upload).
 *   curAlpha stores alpha from aiBlob (the backend result).
 *   Restore tool uses origRGBA colors × new alpha=255, so it correctly
 *   recovers pixels the AI wrongly removed (e.g. white hair).
 */

import { S, MAX_EDITOR_DIM } from '../state';
import { paintLine }         from './brush';
import { pushUndo }          from './history';

const CHECKER_CELL = 12;

/* ══════════════════════════════════════════════════════
   initEditor(origFile, aiBlob)
   origFile — original File uploaded by the user
   aiBlob   — transparent PNG returned by the server
══════════════════════════════════════════════════════ */
export function initEditor(origFile, aiBlob) {
  return new Promise((resolve, reject) => {
    const loadImg = blob => new Promise((res, rej) => {
      const url = URL.createObjectURL(blob);
      const img = new Image();
      img.onload  = () => { URL.revokeObjectURL(url); res(img); };
      img.onerror = () => { URL.revokeObjectURL(url); rej(new Error('img load failed')); };
      img.src = url;
    });

    Promise.all([loadImg(origFile), loadImg(aiBlob)])
      .then(([origImg, aiImg]) => {
        let W = aiImg.naturalWidth, H = aiImg.naturalHeight;
        if (W > MAX_EDITOR_DIM || H > MAX_EDITOR_DIM) {
          const s = Math.min(MAX_EDITOR_DIM / W, MAX_EDITOR_DIM / H);
          W = Math.round(W * s); H = Math.round(H * s);
        }
        const n = W * H;

        /* RGB from original file */
        const origC   = mk(W, H);
        const origCtx = origC.getContext('2d', { willReadFrequently: true });
        origCtx.drawImage(origImg, 0, 0, W, H);
        const origData = origCtx.getImageData(0, 0, W, H).data;

        /* Alpha from AI result */
        const aiC   = mk(W, H);
        const aiCtx = aiC.getContext('2d', { willReadFrequently: true });
        aiCtx.drawImage(aiImg, 0, 0, W, H);
        const aiData = aiCtx.getImageData(0, 0, W, H).data;

        /* Build editor buffers */
        const ed        = S.ed;
        ed.W            = W; ed.H = H;
        ed.origRGBA     = new Uint8ClampedArray(n * 4);
        ed.initAlpha    = new Uint8Array(n);
        ed.curAlpha     = new Uint8Array(n);

        for (let i = 0; i < n; i++) {
          ed.origRGBA[i*4]   = origData[i*4];
          ed.origRGBA[i*4+1] = origData[i*4+1];
          ed.origRGBA[i*4+2] = origData[i*4+2];
          ed.origRGBA[i*4+3] = origData[i*4+3];
          const a = aiData[i*4+3];
          ed.initAlpha[i] = a;
          ed.curAlpha[i]  = a;
        }

        ed.checker      = buildChecker(W, H);
        ed.tool         = 'remove';
        ed.brushSize    = 30;
        ed.brushOpacity = 100;
        ed.isDrawing    = false;
        ed.active       = true;
        S.undoStack     = [];
        S.redoStack     = [];

        /* Setup canvases */
        const DC = document.getElementById('displayCanvas');
        const OV = document.getElementById('overlayCanvas');
        const OC = document.getElementById('origCanvas');
        if (!DC || !OV || !OC) { reject(new Error('Canvas elements missing')); return; }

        DC.width = OV.width = OC.width  = W;
        DC.height = OV.height = OC.height = H;
        ed.displayCanvas = DC;
        ed.overlayCanvas = OV;

        OC.getContext('2d').drawImage(origImg, 0, 0, W, H);
        renderEditor(null);
        attachBrushEvents(OV);

        /* Sync toolbar UI */
        document.querySelectorAll('#editorToolbar [data-tool]')
          .forEach(b => b.classList.remove('active'));
        document.getElementById('btnRemoveArea')?.classList.add('active');
        const sl = document.getElementById('brushSizeSlider');
        const lb = document.getElementById('brushSizeVal');
        if (sl) sl.value = '30';
        if (lb) lb.textContent = '30px';

        import('./history').then(m => m.updateButtons());
        resolve();
      })
      .catch(reject);
  });
}

/* ══════════════════════════════════════════════════════
   RENDERER
   composites origRGBA[RGB] × curAlpha over checkerboard
══════════════════════════════════════════════════════ */
export function renderEditor(rect) {
  const ed = S.ed;
  if (!ed.active || !ed.displayCanvas) return;

  const { W, H, origRGBA, curAlpha, checker, displayCanvas } = ed;
  const ctx = displayCanvas.getContext('2d');

  const x0 = rect?.x0 ?? 0,  y0 = rect?.y0 ?? 0;
  const x1 = rect?.x1 ?? W - 1, y1 = rect?.y1 ?? H - 1;
  const rw = x1 - x0 + 1, rh = y1 - y0 + 1;

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

export function scheduleRender(rect) {
  const ed = S.ed;
  if (ed.rafId) {
    if (rect && ed.pendingRect) {
      ed.pendingRect.x0 = Math.min(ed.pendingRect.x0, rect.x0);
      ed.pendingRect.y0 = Math.min(ed.pendingRect.y0, rect.y0);
      ed.pendingRect.x1 = Math.max(ed.pendingRect.x1, rect.x1);
      ed.pendingRect.y1 = Math.max(ed.pendingRect.y1, rect.y1);
    } else if (!rect) {
      ed.pendingRect = null;
    }
    return;
  }
  ed.pendingRect = rect ? { ...rect } : null;
  ed.rafId = requestAnimationFrame(() => {
    ed.rafId = null;
    renderEditor(ed.pendingRect);
    ed.pendingRect = null;
  });
}

/* ══════════════════════════════════════════════════════
   BRUSH EVENTS
══════════════════════════════════════════════════════ */
function attachBrushEvents(overlayEl) {
  const fresh  = overlayEl.cloneNode(false);
  fresh.id     = 'overlayCanvas';
  fresh.width  = S.ed.W;
  fresh.height = S.ed.H;
  overlayEl.parentNode.replaceChild(fresh, overlayEl);
  S.ed.overlayCanvas = fresh;

  const toLogical = e => {
    const r   = fresh.getBoundingClientRect();
    const src = e.touches ? e.touches[0] : e;
    return {
      x: Math.round((src.clientX - r.left) * (S.ed.W / r.width)),
      y: Math.round((src.clientY - r.top)  * (S.ed.H / r.height)),
    };
  };

  const onDown = e => {
    e.preventDefault();
    S.ed.isDrawing = true;
    pushUndo();
    const { x, y } = toLogical(e);
    S.ed.lastX = x; S.ed.lastY = y;
    paintLine(x, y, x, y);
    drawCursor(x, y);
  };

  const onMove = e => {
    e.preventDefault();
    const { x, y } = toLogical(e);
    drawCursor(x, y);
    if (!S.ed.isDrawing) return;
    paintLine(S.ed.lastX, S.ed.lastY, x, y);
    S.ed.lastX = x; S.ed.lastY = y;
  };

  const onUp = () => { S.ed.isDrawing = false; };

  fresh.addEventListener('mousedown',  onDown);
  fresh.addEventListener('mousemove',  onMove);
  fresh.addEventListener('mouseup',    onUp);
  fresh.addEventListener('mouseleave', () => { onUp(); clearCursor(); });
  fresh.addEventListener('touchstart', onDown, { passive: false });
  fresh.addEventListener('touchmove',  onMove, { passive: false });
  fresh.addEventListener('touchend',   onUp);
}

function drawCursor(x, y) {
  const { overlayCanvas, W, H, brushSize, tool } = S.ed;
  if (!overlayCanvas) return;
  const ctx  = overlayCanvas.getContext('2d');
  ctx.clearRect(0, 0, W, H);

  const isRemove = tool === 'remove';
  const color    = isRemove ? 'rgba(248,113,113,0.85)' : 'rgba(163,230,53,0.85)';
  const dot      = isRemove ? '#f87171' : '#a3e635';

  ctx.beginPath();
  ctx.arc(x, y, brushSize, 0, Math.PI * 2);
  ctx.strokeStyle = color;
  ctx.lineWidth   = Math.max(1.5, brushSize * 0.035);
  ctx.shadowColor = color;
  ctx.shadowBlur  = 6;
  ctx.stroke();
  ctx.shadowBlur  = 0;

  ctx.beginPath();
  ctx.arc(x, y, 3, 0, Math.PI * 2);
  ctx.fillStyle = dot;
  ctx.fill();
}

export function clearCursor() {
  const { overlayCanvas, W, H } = S.ed;
  if (overlayCanvas) overlayCanvas.getContext('2d').clearRect(0, 0, W, H);
}

function buildChecker(W, H) {
  const cb = new Uint8Array(W * H);
  for (let y = 0; y < H; y++)
    for (let x = 0; x < W; x++)
      cb[y * W + x] = (((x / CHECKER_CELL) | 0) ^ ((y / CHECKER_CELL) | 0)) & 1 ? 130 : 180;
  return cb;
}

const mk = (w, h) => Object.assign(document.createElement('canvas'), { width: w, height: h });