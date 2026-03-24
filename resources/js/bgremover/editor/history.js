/**
 * resources/js/bgremover/editor/history.js
 * Undo / redo stack — stores Uint8Array snapshots of curAlpha.
 * One snapshot per complete stroke (mousedown → mouseup).
 */

import { S, MAX_UNDO } from '../state';

/** Push current curAlpha before a brush stroke. */
export function pushUndo() {
  S.undoStack.push(S.ed.curAlpha.slice());
  if (S.undoStack.length > MAX_UNDO) S.undoStack.shift();
  S.redoStack = [];
  updateButtons();
}

/** Ctrl+Z */
export function undo() {
  if (!S.undoStack.length) return;
  S.redoStack.push(S.ed.curAlpha.slice());
  S.ed.curAlpha = S.undoStack.pop();
  updateButtons();
  scheduleFullRender();
}

/** Ctrl+Y / Ctrl+Shift+Z */
export function redo() {
  if (!S.redoStack.length) return;
  S.undoStack.push(S.ed.curAlpha.slice());
  S.ed.curAlpha = S.redoStack.pop();
  updateButtons();
  scheduleFullRender();
}

/** Reset curAlpha to original AI result */
export function resetToAI() {
  pushUndo();
  S.ed.curAlpha = S.ed.initAlpha.slice();
  scheduleFullRender();
}

export function updateButtons() {
  const u = document.getElementById('btnUndo');
  const r = document.getElementById('btnRedo');
  if (u) u.disabled = S.undoStack.length === 0;
  if (r) r.disabled = S.redoStack.length === 0;
}

function scheduleFullRender() {
  import('./canvas').then(m => m.scheduleRender(null));
}