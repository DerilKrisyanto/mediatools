/**
 * resources/js/bgremover/editor/brush.js
 *
 * Paints brush strokes by modifying S.ed.curAlpha.
 *
 * Remove  → drives curAlpha[i] → 0   (transparent)
 * Restore → drives curAlpha[i] → 255 (full original pixel)
 *
 * Restore always targets 255, NOT initAlpha — this lets users
 * fix areas the AI wrongly removed (e.g. white hair blending into bg).
 *
 * Gaussian falloff for smooth centre-to-edge brush feel.
 * paintLine interpolates between mouse events for smooth strokes.
 */

import { S }              from '../state';
import { scheduleRender } from './canvas';

/**
 * paintLine(x0, y0, x1, y1)
 * Interpolated stroke between two logical canvas points.
 */
export function paintLine(x0, y0, x1, y1) {
  const { brushSize } = S.ed;
  const dist  = Math.hypot(x1 - x0, y1 - y0);
  const steps = Math.max(1, Math.ceil(dist / Math.max(1, brushSize * 0.15)));

  let rx0 = Infinity, ry0 = Infinity, rx1 = -1, ry1 = -1;

  for (let s = 0; s <= steps; s++) {
    const t  = steps === 0 ? 0 : s / steps;
    const cx = Math.round(x0 + (x1 - x0) * t);
    const cy = Math.round(y0 + (y1 - y0) * t);
    const b  = paintCircle(cx, cy);
    rx0 = Math.min(rx0, b.x0); ry0 = Math.min(ry0, b.y0);
    rx1 = Math.max(rx1, b.x1); ry1 = Math.max(ry1, b.y1);
  }

  if (rx0 <= rx1) scheduleRender({ x0: rx0, y0: ry0, x1: rx1, y1: ry1 });
}

function paintCircle(cx, cy) {
  const { W, H, curAlpha, tool, brushSize, brushOpacity } = S.ed;
  const r       = brushSize;
  const r2      = r * r;
  const opacity = (brushOpacity ?? 100) / 100;

  const x0 = Math.max(0,     cx - r);
  const y0 = Math.max(0,     cy - r);
  const x1 = Math.min(W - 1, cx + r);
  const y1 = Math.min(H - 1, cy + r);

  for (let py = y0; py <= y1; py++) {
    for (let px = x0; px <= x1; px++) {
      const d2  = (px - cx) ** 2 + (py - cy) ** 2;
      if (d2 > r2) continue;

      const i   = py * W + px;
      const str = Math.exp(-d2 / (r2 * 0.5)) * opacity;  // Gaussian falloff

      if (tool === 'remove') {
        curAlpha[i] = Math.max(0,   Math.round(curAlpha[i] * (1 - str)));
      } else {
        // Target = 255 always (not initAlpha) — allows fixing AI mistakes
        curAlpha[i] = Math.min(255, Math.round(curAlpha[i] + (255 - curAlpha[i]) * str));
      }
    }
  }

  return { x0, y0, x1, y1 };
}