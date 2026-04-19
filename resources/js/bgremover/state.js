/**
 * resources/js/bgremover/state.js
 * Global state singleton — single source of truth for BgRemover + PasFoto.
 *
 * FIX (v2): S.model default changed from 'medium' → 'fast' to match the
 *   initial active quality button (data-q="fast") in the Upload view.
 *   Previously the button showed "Cepat" as selected, but the actual value
 *   sent to the backend was 'medium' (which rmbg.js then wrongly mapped to
 *   'high'). Now state and UI are in sync on first use.
 */

export const S = {

  /* ── Tool selection ──
   *  null        = mode-select screen
   *  'bgremover' = BG Remover flow
   *  'pasfoto'   = PasFoto flow
   */
  tool : null,

  /* ── BG Remover file refs ── */
  origFile      : null,
  origObjectUrl : null,
  aiBlob        : null,
  aiBlobUrl     : null,

  /* ── App state machine ──
   *  'mode-select'
   *  BG Remover: 'upload' | 'processing' | 'result' | 'editor' | 'multi'
   *  PasFoto:    'pf-upload' | 'pf-crop' | 'pf-processing' | 'pf-result'
   */
  mode    : 'mode-select',
  isMulti : false,

  /* ── BG Remover output prefs ──
   *  model: must match one of the data-q values in the quality buttons AND
   *         one of the keys in BgRemoverController::MODEL_MAP on the backend:
   *         'fast' | 'medium' | 'high' | 'portrait'
   *  The initial active button in the HTML is data-q="fast", so we use 'fast'.
   */
  bg     : 'transparent',
  model  : 'fast',
  format : 'png',

  /* ── PasFoto sub-state ── */
  pf : {
    origFile      : null,
    cropper       : null,       // Cropper.js instance
    selectedSize  : '3x4',      // '2x3' | '3x4' | '4x6'
    selectedBg    : 'merah',    // key from PF_BG_COLORS
    quality       : 'medium',   // 'medium' | 'portrait' — matches active PF quality btn
    compressTarget: null,        // null | 200 | 300

    // Results
    aiImg         : null,        // HTMLImageElement — transparent PNG
    resultCanvas  : null,
    resultDataURL : null,
    resultSizeKB  : null,
    photoSize     : null,        // PHOTO_SIZES[selectedSize] snapshot
  },

  /* ── Editor state ── */
  ed : {
    active        : false,
    W             : 0,
    H             : 0,
    origRGBA      : null,
    initAlpha     : null,
    curAlpha      : null,
    tool          : 'remove',
    brushSize     : 30,
    brushOpacity  : 100,
    isDrawing     : false,
    lastX         : -1,
    lastY         : -1,
    rafId         : null,
    pendingRect   : null,
    displayCanvas : null,
    overlayCanvas : null,
  },

  undoStack      : [],
  redoStack      : [],
  results        : new Map(),
  editorReturnTo : 'upload',
};

export const MAX_UNDO       = 30;
export const MAX_BYTES      = 20 * 1024 * 1024;
export const MAX_EDITOR_DIM = 1800;
export const ACCEPTED       = new Set(['image/jpeg','image/jpg','image/png','image/webp']);

/* ── PasFoto photo sizes (px @ ~200 DPI) ── */
export const PHOTO_SIZES = {
  '2x3': { key:'2x3', label:'2×3 cm', desc:'KTP, SIM, Paspor', width:157,  height:236,  ratio:2/3,  printW:20, printH:30 },
  '3x4': { key:'3x4', label:'3×4 cm', desc:'CPNS, Ijazah',     width:236,  height:315,  ratio:3/4,  printW:30, printH:40 },
  '4x6': { key:'4x6', label:'4×6 cm', desc:'Lamaran Kerja',    width:315,  height:472,  ratio:4/6,  printW:40, printH:60 },
};

/* ── PasFoto background color map ── */
export const PF_BG_COLORS = {
  merah : { hex:'#cc0000', label:'Merah'  },
  biru  : { hex:'#0047ab', label:'Biru'   },
  hijau : { hex:'#006400', label:'Hijau'  },
  putih : { hex:'#ffffff', label:'Putih'  },
};

/* ── PDF layout: photos per A4 page ── */
export const PDF_LAYOUT = {
  '2x3': { cols:4, rows:4 },
  '3x4': { cols:3, rows:3 },
  '4x6': { cols:2, rows:2 },
};