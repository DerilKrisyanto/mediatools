/**
 * exportPdf.js
 * Generate a printable A4 PDF containing a grid of passport photos.
 * Uses jsPDF loaded from CDN (no npm install required).
 *
 * CDN:  https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js
 * Include that script in the blade view BEFORE this module is loaded.
 *
 * Place at: public/js/tools/exportPdf.js
 */

/**
 * Number of copies per A4 page for each photo size.
 * Layout: cols × rows = total copies per page.
 */
const PDF_LAYOUT = {
    '2x3': { cols: 4, rows: 4, total: 16 },
    '3x4': { cols: 3, rows: 3, total:  9 },
    '4x6': { cols: 2, rows: 2, total:  4 },
};

/** A4 dimensions in mm */
const A4 = { w: 210, h: 297, margin: 8 };

/**
 * Generate and download a PDF with the photo repeated in a grid.
 *
 * @param {string} dataURL      - JPEG data URL of the processed photo
 * @param {string} sizeKey      - '2x3' | '3x4' | '4x6'
 * @param {number} [copies=1]   - Number of PDF pages (default 1)
 * @param {string} [filename]   - Optional override for filename
 */
export function generatePDF(dataURL, sizeKey, copies = 1, filename) {
    if (typeof window.jspdf === 'undefined') {
        throw new Error('jsPDF belum dimuat. Pastikan CDN sudah diinclude.');
    }

    const { jsPDF } = window.jspdf;

    // Photo print size in mm
    const photoMM = {
        '2x3': { w: 20, h: 30 },
        '3x4': { w: 30, h: 40 },
        '4x6': { w: 40, h: 60 },
    }[sizeKey] || { w: 30, h: 40 };

    const layout = PDF_LAYOUT[sizeKey] || PDF_LAYOUT['3x4'];

    const doc = new jsPDF({
        orientation: 'portrait',
        unit:        'mm',
        format:      'a4',
    });

    for (let page = 0; page < copies; page++) {
        if (page > 0) doc.addPage('a4', 'portrait');
        _drawPhotoPage(doc, dataURL, photoMM, layout);
    }

    const name = filename || `pasfoto_${sizeKey}_${Date.now()}.pdf`;
    doc.save(name);
}

/**
 * Draw a single page of photos in a grid.
 * Centers the grid on the A4 page, adds cut guide lines.
 *
 * @private
 */
function _drawPhotoPage(doc, dataURL, photoMM, layout) {
    const { w: pW, h: pH } = photoMM;
    const { cols, rows }   = layout;
    const GAP              = 3; // mm between photos

    // Calculate total grid size
    const gridW = cols * pW + (cols - 1) * GAP;
    const gridH = rows * pH + (rows - 1) * GAP;

    // Center on A4
    const startX = (A4.w - gridW) / 2;
    const startY = (A4.h - gridH) / 2;

    for (let row = 0; row < rows; row++) {
        for (let col = 0; col < cols; col++) {
            const x = startX + col * (pW + GAP);
            const y = startY + row * (pH + GAP);

            // Place photo
            doc.addImage(dataURL, 'JPEG', x, y, pW, pH, undefined, 'FAST');

            // Cut guide — thin dashed grey border
            doc.setDrawColor(200, 200, 200);
            doc.setLineWidth(0.15);

            // Use dashes by drawing individual segments
            _drawDashedRect(doc, x, y, pW, pH, 1.5, 1.0);
        }
    }

    // Page footer
    doc.setFontSize(7);
    doc.setTextColor(180, 180, 180);
    const label = `PasFotoOnline.id  ·  Ukuran ${pW}×${pH}mm  ·  ${cols * rows} foto per halaman  ·  Cetak 1:1 di A4`;
    doc.text(label, A4.w / 2, A4.h - 3, { align: 'center' });
}

/**
 * Draw a dashed rectangle.
 * jsPDF's setLineDashPattern isn't reliable on all renderers,
 * so we draw segment-by-segment.
 * @private
 */
function _drawDashedRect(doc, x, y, w, h, dashLen, gapLen) {
    doc.setLineWidth(0.2);
    doc.setDrawColor(190, 190, 190);

    // Top
    _dashedLine(doc, x, y, x + w, y, dashLen, gapLen);
    // Bottom
    _dashedLine(doc, x, y + h, x + w, y + h, dashLen, gapLen);
    // Left
    _dashedLine(doc, x, y, x, y + h, dashLen, gapLen);
    // Right
    _dashedLine(doc, x + w, y, x + w, y + h, dashLen, gapLen);
}

function _dashedLine(doc, x1, y1, x2, y2, dashLen, gapLen) {
    const dx    = x2 - x1;
    const dy    = y2 - y1;
    const len   = Math.sqrt(dx * dx + dy * dy);
    const ux    = dx / len;
    const uy    = dy / len;
    let dist    = 0;
    let drawing = true;

    while (dist < len) {
        const segLen  = Math.min(drawing ? dashLen : gapLen, len - dist);
        const sx      = x1 + ux * dist;
        const sy      = y1 + uy * dist;
        const ex      = x1 + ux * (dist + segLen);
        const ey      = y1 + uy * (dist + segLen);

        if (drawing) doc.line(sx, sy, ex, ey);

        dist    += segLen;
        drawing  = !drawing;
    }
}

/**
 * Get how many photos fit on one A4 page for a given size.
 * @param {string} sizeKey
 * @returns {number}
 */
export function photosPerPage(sizeKey) {
    return (PDF_LAYOUT[sizeKey] || PDF_LAYOUT['3x4']).total;
}
