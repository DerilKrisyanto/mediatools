/**
 * resources/js/bgremover/state.js
 * Global state singleton — single source of truth for all modules.
 */
export const S = {

  /* ── File references ── */
  origFile      : null,   // File   — raw uploaded file (never modified)
  origObjectUrl : null,   // string — blob URL of original for display

  /* ── AI result ── */
  aiBlob        : null,   // Blob   — transparent PNG from backend
  aiBlobUrl     : null,   // string — object URL of aiBlob (result view)

  /* ── App mode ──
   *  'upload' | 'processing' | 'result' | 'editor' | 'multi'
   */
  mode    : 'upload',
  isMulti : false,

  /* ── Output preferences ── */
  bg     : 'transparent', // hex color | 'transparent'
  model  : 'medium',      // 'small' (fast) | 'medium' (HD)
  format : 'png',         // 'png' | 'jpg'

  /* ── Editor state ── */
  ed : {
    active        : false,
    W             : 0,
    H             : 0,
    origRGBA      : null,  // Uint8ClampedArray(W*H*4) — original colors
    initAlpha     : null,  // Uint8Array(W*H) — AI alpha (source for Reset)
    curAlpha      : null,  // Uint8Array(W*H) — live editable alpha
    checker       : null,  // Uint8Array(W*H) — checkerboard pattern

    tool          : 'remove', // 'remove' | 'restore'
    brushSize     : 30,
    brushOpacity  : 100,

    isDrawing     : false,
    lastX         : -1,
    lastY         : -1,

    rafId         : null,
    pendingRect   : null,  // { x0,y0,x1,y1 } dirty region for partial render

    displayCanvas : null,
    overlayCanvas : null,
  },

  /* ── Undo / Redo ── */
  undoStack : [],
  redoStack : [],

  /* ── Multi-image results ──
   *  id → { blob, objectUrl, name, origFile, origObjectUrl }
   */
  results   : new Map(),

  /* ── Return destination after editor ──
   *  'result' (single-image flow) | 'multi' (batch flow)
   */
  editorReturnTo : 'upload',
};

export const MAX_UNDO       = 30;
export const MAX_BYTES      = 20 * 1024 * 1024;   // 20 MB
export const MAX_EDITOR_DIM = 1800;                // px
export const ACCEPTED       = new Set(['image/jpeg', 'image/jpg', 'image/png', 'image/webp']);