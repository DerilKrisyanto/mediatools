/**
 * resources/js/bgremover/pasfoto/flow.js
 * PasFoto processing pipeline — pure data, no DOM.
 */

import { removeRMBG }  from '../ai/rmbg.js';
import { PHOTO_SIZES } from '../state.js';

/* ══════════════════════════════════════════════════════
   MAIN PIPELINE
══════════════════════════════════════════════════════ */
export async function runPasFotoFlow(cropperInstance, options, onProgress = () => {}) {
  const { size, bgHex, quality, compressKB } = options;
  const photoSize = PHOTO_SIZES[size] || PHOTO_SIZES['3x4'];

  /* Step 1 — Crop */
  onProgress(2, 'Memotong foto ke ukuran yang dipilih…');
  const croppedCanvas = cropperInstance.getCroppedCanvas({
    width : photoSize.width,
    height: photoSize.height,
    imageSmoothingEnabled: true,
    imageSmoothingQuality: 'high',
    fillColor: '#ffffff',
  });

  /* Step 2 — Prepare for AI */
  onProgress(5, 'Menyiapkan gambar untuk AI BiRefNet…');
  const croppedBlob = await canvasToBlob(croppedCanvas, 'image/jpeg', 0.95);
  const croppedFile = new File([croppedBlob], 'pasfoto_crop.jpg', { type: 'image/jpeg' });

  /* Step 3 — AI removes background */
  const aiBlob = await removeRMBG(croppedFile, quality, (pct, label) => {
    onProgress(5 + (pct / 100) * 87, label);
  });

  /* Step 4 — Load transparent PNG */
  onProgress(92, 'Memuat hasil AI…');
  const aiImg = await blobToImg(aiBlob);

  /* Step 5 — Composite on background */
  onProgress(95, 'Menerapkan warna latar belakang…');
  const resultCanvas = compositeOnBg(aiImg, bgHex, photoSize.width, photoSize.height);

  /* Step 6 — Encode */
  onProgress(97, 'Mengoptimalkan ukuran file…');
  let resultDataURL, resultSizeKB;

  if (compressKB) {
    const r      = await compressToTarget(resultCanvas, compressKB);
    resultDataURL = r.dataURL;
    resultSizeKB  = r.sizeKB;
  } else {
    resultDataURL = resultCanvas.toDataURL('image/jpeg', 0.95);
    resultSizeKB  = dataURLSizeKB(resultDataURL);
  }

  onProgress(100, 'Selesai!');
  return { resultCanvas, resultDataURL, resultSizeKB, aiImg, photoSize };
}

/* ══════════════════════════════════════════════════════
   RE-COMPOSITE (no server call — just change BG color)
══════════════════════════════════════════════════════ */
export function recompositePreview(aiImg, bgHex, width, height) {
  return compositeOnBg(aiImg, bgHex, width, height);
}

/* ══════════════════════════════════════════════════════
   PDF EXPORT — custom photo count, A4 layout
══════════════════════════════════════════════════════ */
export function generatePasFotoPDF(dataURL, sizeKey, copies = 4, bgLabel = '') {
  if (!window.jspdf) throw new Error('jsPDF tidak tersedia. Pastikan CDN sudah dimuat.');
  const { jsPDF } = window.jspdf;

  const photoMM = {
    '2x3': { w: 20, h: 30 },
    '3x4': { w: 30, h: 40 },
    '4x6': { w: 40, h: 60 },
  }[sizeKey] ?? { w: 30, h: 40 };

  const A4   = { w: 210, h: 297 };
  const GAP  = 3; // mm between photos
  const PAD  = 8; // page margin mm

  // Calculate max columns and rows
  const maxCols = Math.floor((A4.w - PAD * 2 + GAP) / (photoMM.w + GAP));
  const maxRows = Math.floor((A4.h - PAD * 2 + GAP) / (photoMM.h + GAP));
  const maxPerPage = maxCols * maxRows;

  // Clamp copies to max
  const count = Math.min(copies, maxPerPage);

  const cols = Math.min(count, maxCols);
  const rows = Math.ceil(count / cols);

  // Center the grid on A4
  const gridW = cols * photoMM.w + (cols - 1) * GAP;
  const gridH = rows * photoMM.h + (rows - 1) * GAP;
  const startX = (A4.w - gridW) / 2;
  const startY = (A4.h - gridH) / 2;

  const doc = new jsPDF({
    orientation: 'portrait',
    unit: 'mm',
    format: 'a4',
  });

  let placed = 0;
  for (let row = 0; row < rows && placed < count; row++) {
    for (let col = 0; col < cols && placed < count; col++) {
      const x = startX + col * (photoMM.w + GAP);
      const y = startY + row * (photoMM.h + GAP);
      doc.addImage(dataURL, 'JPEG', x, y, photoMM.w, photoMM.h);

      // Draw thin cut-line border
      doc.setDrawColor(200, 200, 200);
      doc.setLineWidth(0.1);
      doc.rect(x, y, photoMM.w, photoMM.h);

      placed++;
    }
  }

  // Footer text
  doc.setFontSize(6);
  doc.setTextColor(160, 160, 160);
  doc.text(
    `MediaTools — Pas Foto ${sizeKey.replace('x','×')} cm ${bgLabel ? '• BG ' + bgLabel : ''} — ${count} foto`,
    A4.w / 2, A4.h - 4,
    { align: 'center' }
  );

  doc.save(`pasfoto-${sizeKey}-${count}foto-mediatools.pdf`);
}

/* ══════════════════════════════════════════════════════
   HELPERS
══════════════════════════════════════════════════════ */
function compositeOnBg(aiImg, bgHex, width, height) {
  const canvas = document.createElement('canvas');
  canvas.width  = width;
  canvas.height = height;
  const ctx = canvas.getContext('2d');
  if (bgHex && bgHex !== 'transparent') {
    ctx.fillStyle = bgHex;
    ctx.fillRect(0, 0, width, height);
  }
  ctx.drawImage(aiImg, 0, 0, width, height);
  return canvas;
}

function canvasToBlob(canvas, type = 'image/jpeg', quality = 0.92) {
  return new Promise((res, rej) => {
    canvas.toBlob(blob => {
      if (blob) res(blob);
      else rej(new Error('Canvas toBlob gagal'));
    }, type, quality);
  });
}

function blobToImg(blob) {
  return new Promise((res, rej) => {
    const url = URL.createObjectURL(blob);
    const img = new Image();
    img.onload  = () => { res(img); };
    img.onerror = () => rej(new Error('Gagal memuat gambar dari blob'));
    img.src = url;
  });
}

async function compressToTarget(canvas, targetKB, minQuality = 0.4) {
  let quality = 0.92;
  let dataURL = canvas.toDataURL('image/jpeg', quality);
  let sizeKB  = dataURLSizeKB(dataURL);

  while (sizeKB > targetKB && quality > minQuality) {
    quality -= 0.05;
    dataURL = canvas.toDataURL('image/jpeg', Math.max(quality, minQuality));
    sizeKB  = dataURLSizeKB(dataURL);
  }

  return { dataURL, sizeKB };
}

export function dataURLSizeKB(dataURL) {
  const base64 = dataURL.split(',')[1] ?? '';
  return Math.round((base64.length * 3) / 4 / 1024);
}