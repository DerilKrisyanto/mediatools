/**
 * imageProcessor.js
 * Core image processing utilities for PasFotoOnline.
 * All operations are done on HTML Canvas — no server required.
 *
 * Place at: public/js/tools/imageProcessor.js
 */

/**
 * Standard Indonesian pas foto sizes.
 * Dimensions in pixels at ~200 DPI equivalent (good quality for printing).
 * width × height | ratio = width/height
 */
export const PHOTO_SIZES = {
    '2x3': {
        key:    '2x3',
        label:  '2×3 cm',
        desc:   'KTP, SIM, Paspor',
        width:  157,   // 2 cm @ ~200 DPI
        height: 236,   // 3 cm @ ~200 DPI
        ratio:  2 / 3,
        // Print dimensions in mm (for PDF)
        printW: 20,
        printH: 30,
    },
    '3x4': {
        key:    '3x4',
        label:  '3×4 cm',
        desc:   'Ijazah, CPNS, Skripsi',
        width:  236,
        height: 315,
        ratio:  3 / 4,
        printW: 30,
        printH: 40,
    },
    '4x6': {
        key:    '4x6',
        label:  '4×6 cm',
        desc:   'Lamaran kerja, beasiswa',
        width:  315,
        height: 472,
        ratio:  4 / 6,
        printW: 40,
        printH: 60,
    },
};

/**
 * Validate an image file before processing.
 * @param {File} file
 * @throws {Error} with Indonesian message on failure
 */
export function validateImageFile(file) {
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    const maxSizeMB    = 15;

    if (!file || !file.type) {
        throw new Error('File tidak valid. Silakan coba lagi.');
    }
    if (!allowedTypes.includes(file.type.toLowerCase())) {
        throw new Error('Format file tidak didukung. Gunakan JPG, PNG, atau WebP.');
    }
    if (file.size > maxSizeMB * 1024 * 1024) {
        throw new Error(`Ukuran file terlalu besar. Maksimum ${maxSizeMB}MB.`);
    }
    return true;
}

/**
 * Load an image File into an HTMLImageElement.
 * @param {File} file
 * @returns {Promise<HTMLImageElement>}
 */
export function loadImageFromFile(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img  = new Image();
            img.onload  = () => resolve(img);
            img.onerror = () => reject(new Error('Gagal memuat gambar. File mungkin rusak.'));
            img.src     = e.target.result;
        };
        reader.onerror = () => reject(new Error('Gagal membaca file.'));
        reader.readAsDataURL(file);
    });
}

/**
 * Draw an HTMLImageElement onto a new Canvas.
 * @param {HTMLImageElement} img
 * @returns {HTMLCanvasElement}
 */
export function imageToCanvas(img) {
    const canvas = createCanvas(img.naturalWidth || img.width, img.naturalHeight || img.height);
    canvas.getContext('2d').drawImage(img, 0, 0);
    return canvas;
}

/**
 * Create a canvas with given dimensions.
 * @param {number} width
 * @param {number} height
 * @returns {HTMLCanvasElement}
 */
export function createCanvas(width, height) {
    const c   = document.createElement('canvas');
    c.width   = width;
    c.height  = height;
    return c;
}

/**
 * Resize a source canvas to exact target dimensions using high-quality resampling.
 * Uses a multi-step downscale for large → small to improve quality.
 *
 * @param {HTMLCanvasElement} srcCanvas
 * @param {number} targetW
 * @param {number} targetH
 * @returns {HTMLCanvasElement}
 */
export function resizeCanvas(srcCanvas, targetW, targetH) {
    // For significant downscaling, do it in steps for better quality
    let current = srcCanvas;
    let cw      = srcCanvas.width;
    let ch      = srcCanvas.height;

    while (cw > targetW * 2 || ch > targetH * 2) {
        cw = Math.max(Math.round(cw / 2), targetW);
        ch = Math.max(Math.round(ch / 2), targetH);
        const step = createCanvas(cw, ch);
        const ctx  = step.getContext('2d');
        ctx.imageSmoothingEnabled  = true;
        ctx.imageSmoothingQuality  = 'high';
        ctx.drawImage(current, 0, 0, cw, ch);
        current = step;
    }

    const out = createCanvas(targetW, targetH);
    const ctx = out.getContext('2d');
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(current, 0, 0, targetW, targetH);
    return out;
}

/**
 * Get a PHOTO_SIZES entry by key (defaults to '3x4').
 * @param {string} key
 * @returns {object}
 */
export function getPhotoSize(key) {
    return PHOTO_SIZES[key] || PHOTO_SIZES['3x4'];
}

/**
 * Convert a data URL to an approximate file size in KB.
 * @param {string} dataURL
 * @returns {number} size in KB
 */
export function dataURLtoSizeKB(dataURL) {
    const base64 = dataURL.split(',')[1] || '';
    return Math.round((base64.length * 3) / 4 / 1024);
}
