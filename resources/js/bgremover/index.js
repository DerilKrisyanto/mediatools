/**
 * resources/js/bgremover/index.js
 * Entry point — orchestrates BgRemover AND PasFoto flows.
 */

import { S, PHOTO_SIZES, PF_BG_COLORS }    from './state.js';
import { removeRMBG }                        from './ai/rmbg.js';
import { initEditor }                        from './editor/canvas.js';
import {
  initUI, setUIState, setProgress, setProcessingThumb,
  buildMultiCard, setCardProgress, setCardDone, setCardError,
  setResultView, renderPfLivePreview, toast,
  setResultViewPf, loadPfFile, updateEditorButtons,
} from './ui.js';
import {
  runPasFotoFlow, recompositePreview, generatePasFotoPDF, dataURLSizeKB,
} from './pasfoto/flow.js';

/* ── Boot ── */
function bootBgRemover() {
  initUI();
  window._bgrHandleFiles = handleFiles;
  window._pfStartProcess = pfStartProcess;
  window._pfFlow = { recompositePreview, generatePasFotoPDF };
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', bootBgRemover, { once: true });
} else {
  bootBgRemover();
}

/* ══════════════════════════════════════════════════════
   FILE DISPATCH
══════════════════════════════════════════════════════ */
async function handleFiles(files) {
  if (['editor','processing','pf-processing'].includes(S.mode)) return;
  if (files.length === 1) await singleFlow(files[0]);
  else await multiFlow(files);
}

/* ══════════════════════════════════════════════════════
   BGREMOVER — SINGLE
══════════════════════════════════════════════════════ */
async function singleFlow(file) {
  S.origFile  = file;
  S.isMulti   = false;

  if (S.origObjectUrl) URL.revokeObjectURL(S.origObjectUrl);
  if (S.aiBlobUrl)     URL.revokeObjectURL(S.aiBlobUrl);
  S.origObjectUrl = URL.createObjectURL(file);

  setUIState('processing');
  setProgress(0, 'Mempersiapkan…');
  setProcessingThumb(file);

  try {
    const aiBlob = await removeRMBG(file, S.model, (pct, label) => setProgress(pct, label));
    S.aiBlob    = aiBlob;
    S.aiBlobUrl = URL.createObjectURL(aiBlob);

    setProgress(100, 'Selesai!');
    await sleep(300);

    // Show before/after result view
    S.editorReturnTo = 'result';
    setResultView(S.origObjectUrl, S.aiBlobUrl, file.name);
    setUIState('result');

  } catch (err) {
    console.error('[BgRemover single]', err);
    toast(err?.message?.slice(0, 80) ?? 'Error tidak diketahui', 'error');
    setUIState('upload');
  }
}

/* ══════════════════════════════════════════════════════
   BGREMOVER — OPEN EDITOR
══════════════════════════════════════════════════════ */
window._bgrOpenEditor = async function (origFile, aiBlob, returnTo = 'result') {
  S.editorReturnTo = returnTo;
  setUIState('processing');
  setProgress(0, 'Membuka editor…');
  if (origFile instanceof File) setProcessingThumb(origFile);

  try {
    setProgress(40, 'Memuat gambar ke canvas editor…');
    await initEditor(origFile, aiBlob);
    setProgress(100, 'Editor siap!');
    await sleep(150);
    setUIState('editor');
    toast('Gunakan brush Hapus/Pulihkan untuk menyempurnakan hasil AI');
  } catch (err) {
    console.error('[BgRemover editor]', err);
    toast('Gagal membuka editor: ' + (err?.message ?? ''), 'error');
    setUIState(returnTo === 'multi' ? 'multi' : 'result');
  }
};

/* ══════════════════════════════════════════════════════
   BGREMOVER — MULTI (batch)
══════════════════════════════════════════════════════ */
async function multiFlow(files) {
  S.isMulti = true;
  setUIState('multi');
  S.results.clear();
  const grid = document.getElementById('multiGrid');
  if (grid) grid.innerHTML = '';

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    const id   = `img-${i}-${Date.now()}`;
    buildMultiCard(id, file);

    try {
      const aiBlob = await removeRMBG(
        file,
        S.model,
        (pct, label) => setCardProgress(id, pct, label)
      );
      setCardDone(id, file, aiBlob);
    } catch (err) {
      setCardError(id, err?.message ?? 'Gagal');
    }
  }

  toast(`Selesai! ${files.length} foto berhasil diproses.`);
}

/* ══════════════════════════════════════════════════════
   PAS FOTO — MAIN FLOW
══════════════════════════════════════════════════════ */
async function pfStartProcess() {
  if (!S.pf.cropper || !S.pf.origFile) return;

  setProcessingThumb(S.pf.origFile);
  setUIState('pf-processing');
  setProgress(0, 'Mempersiapkan proses…');

  try {
    const bgHex   = PF_BG_COLORS[S.pf.selectedBg]?.hex ?? '#cc0000';
    const quality = S.pf.quality ?? 'medium';

    const result = await runPasFotoFlow(
      S.pf.cropper,
      {
        size      : S.pf.selectedSize,
        bgHex,
        quality,
        compressKB: S.pf.compressTarget,
      },
      (pct, label) => setProgress(pct, label)
    );

    S.pf.resultCanvas  = result.resultCanvas;
    S.pf.resultDataURL = result.resultDataURL;
    S.pf.resultSizeKB  = result.resultSizeKB;
    S.pf.aiImg         = result.aiImg;
    S.pf.photoSize     = result.photoSize;

    setProgress(100, 'Selesai!');
    await sleep(300);

    setResultViewPf(result);
    setUIState('pf-result');
    toast('Pas foto berhasil dibuat!');

  } catch (err) {
    console.error('[PasFoto]', err);
    toast(err?.message?.slice(0, 80) ?? 'Gagal memproses pas foto', 'error');
    setUIState('pf-crop');
  }
}

/* ── Helpers ── */
function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }