/**
 * resources/js/bgremover/index.js
 * Entry point — orchestrates backend AI, result view, and brush editor.
 *
 * State machine:
 *   upload → processing → result  (single image)
 *                       → editor  (from result, optional brush edit)
 *   upload → multi                (batch ≥2 images)
 */

import { S }          from './state';
import { removeRMBG } from './ai/rmbg';
import { initEditor } from './editor/canvas';
import {
  initUI, setUIState, setProgress, setProcessingThumb,
  buildMultiCard, setCardProgress, setCardDone, setCardError,
  setResultView, toast,
} from './ui';

/* ── Boot ── */
document.addEventListener('DOMContentLoaded', () => {
  initUI();
  window._bgrHandleFiles = handleFiles;
});

/* ══════════════════════════════════════════════════════
   FILE DISPATCH
══════════════════════════════════════════════════════ */
async function handleFiles(files) {
  if (S.mode === 'editor' || S.mode === 'processing') return;

  if (files.length === 1) {
    await singleFlow(files[0]);
  } else {
    await multiFlow(files);
  }
}

/* ══════════════════════════════════════════════════════
   SINGLE IMAGE: upload → processing → result (→ editor)
══════════════════════════════════════════════════════ */
async function singleFlow(file) {
  S.origFile = file;
  S.isMulti  = false;

  // Revoke any old URLs
  if (S.origObjectUrl) { URL.revokeObjectURL(S.origObjectUrl); }
  if (S.aiBlobUrl)     { URL.revokeObjectURL(S.aiBlobUrl); }

  S.origObjectUrl = URL.createObjectURL(file);
  setUIState('processing');
  setProgress(0, 'Mempersiapkan…');
  setProcessingThumb(file);

  try {
    const aiBlob = await removeRMBG(file, S.model, (pct, label) => {
      setProgress(pct, label);
    });

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
   OPEN EDITOR — called from result view "Edit" button
   or from multi-image card "Edit" button
══════════════════════════════════════════════════════ */
window._bgrOpenEditor = async function (origFile, aiBlob, returnTo = 'result') {
  S.editorReturnTo = returnTo;
  setUIState('processing');
  setProgress(0, 'Membuka editor…');
  if (origFile instanceof File) setProcessingThumb(origFile);

  try {
    setProgress(40, 'Memuat gambar ke editor…');
    await initEditor(origFile, aiBlob);
    setProgress(100, 'Editor siap!');
    await sleep(150);
    setUIState('editor');
    toast('Gunakan brush Remove/Restore untuk menyempurnakan hasil AI');
  } catch (err) {
    console.error('[BgRemover editor]', err);
    toast('Gagal membuka editor: ' + (err?.message ?? ''), 'error');
    setUIState(returnTo === 'multi' ? 'multi' : 'result');
  }
};

/* ══════════════════════════════════════════════════════
   MULTI IMAGE (batch): upload → multi (grid)
══════════════════════════════════════════════════════ */
async function multiFlow(files) {
  S.isMulti = true;
  setUIState('multi');

  const grid = document.getElementById('multiGrid');
  if (!grid) return;

  for (const file of files) {
    const id   = uid();
    const card = buildMultiCard(id, file.name);
    grid.appendChild(card);

    try {
      const origObjectUrl = URL.createObjectURL(file);
      const blob = await removeRMBG(file, S.model, (pct, label) => {
        setCardProgress(id, pct, label);
      });

      const objectUrl = URL.createObjectURL(blob);
      S.results.set(id, { blob, objectUrl, name: file.name, origFile: file, origObjectUrl });
      setCardDone(id, origObjectUrl, objectUrl);
      toast(`✓ ${shortName(file.name, 28)}`);

    } catch (err) {
      console.error('[BgRemover multi]', err);
      setCardError(id, err?.message ?? 'Error');
      toast(`Gagal: ${shortName(file.name, 26)}`, 'error');
    }
  }
}

/* ── Utilities ── */
const uid   = () => `${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
const sleep = ms => new Promise(r => setTimeout(r, ms));
function shortName(name, max = 22) {
  if (name.length <= max) return name;
  const ext = name.includes('.') ? '.' + name.split('.').pop() : '';
  return name.slice(0, max - ext.length - 3) + '…' + ext;
}