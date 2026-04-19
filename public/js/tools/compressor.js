/**
 * compressor.js
 * Client-side image compression using Canvas binary-search on JPEG quality.
 * No external libraries needed — pure canvas API.
 *
 * Place at: public/js/tools/compressor.js
 */

/**
 * Compute the approximate byte-size of a data URL.
 * Base64 encoding: every 4 chars encodes 3 bytes, minus padding.
 *
 * @param {string} dataURL
 * @returns {number} size in bytes
 */
export function dataURLSizeBytes(dataURL) {
    const base64 = dataURL.split(',')[1] || '';
    // Remove padding
    const padding = (base64.endsWith('==') ? 2 : base64.endsWith('=') ? 1 : 0);
    return Math.floor((base64.length / 4) * 3) - padding;
}

/**
 * Size in KB.
 * @param {string} dataURL
 * @returns {number}
 */
export function dataURLSizeKB(dataURL) {
    return Math.round(dataURLSizeBytes(dataURL) / 1024);
}

/**
 * Compress a canvas to a target file size using binary search on JPEG quality.
 * Falls back to lowest acceptable quality if target cannot be reached.
 *
 * @param {HTMLCanvasElement} canvas        - Source canvas
 * @param {number}            targetKB      - Target max size in KB
 * @param {string}            [mime='image/jpeg']
 * @param {number}            [iterations=10] - Binary search iterations
 * @returns {Promise<{dataURL:string, quality:number, sizeKB:number}>}
 */
export async function compressToTargetKB(canvas, targetKB, mime = 'image/jpeg', iterations = 10) {
    const targetBytes = targetKB * 1024;

    // Check if max quality already fits
    const maxQ     = canvas.toDataURL(mime, 1.0);
    const maxBytes = dataURLSizeBytes(maxQ);

    if (maxBytes <= targetBytes) {
        return { dataURL: maxQ, quality: 1.0, sizeKB: Math.round(maxBytes / 1024) };
    }

    // Binary search
    let lo = 0.05;
    let hi = 0.95;
    let bestDataURL  = null;
    let bestQuality  = lo;

    for (let i = 0; i < iterations; i++) {
        const mid     = (lo + hi) / 2;
        const attempt = canvas.toDataURL(mime, mid);
        const bytes   = dataURLSizeBytes(attempt);

        if (bytes <= targetBytes) {
            // This quality fits — try higher (better quality)
            bestDataURL = attempt;
            bestQuality = mid;
            lo          = mid;
        } else {
            // Too large — lower quality
            hi = mid;
        }
    }

    // If we never found a fitting quality, use the absolute minimum
    if (!bestDataURL) {
        bestDataURL = canvas.toDataURL(mime, 0.05);
        bestQuality = 0.05;
    }

    return {
        dataURL: bestDataURL,
        quality: bestQuality,
        sizeKB:  dataURLSizeKB(bestDataURL),
    };
}

/**
 * Downscale canvas if needed to help reach a target size,
 * then compress. Useful when binary-search alone can't hit a very small target.
 *
 * @param {HTMLCanvasElement} canvas
 * @param {number}            targetKB
 * @param {number}            [minScale=0.5] - Don't scale below this factor
 * @returns {Promise<{dataURL, quality, sizeKB, canvas}>}
 */
export async function compressWithDownscale(canvas, targetKB, minScale = 0.5) {
    // First try without downscale
    const result = await compressToTargetKB(canvas, targetKB);
    if (result.sizeKB <= targetKB) return { ...result, canvas };

    // Try with progressive downscale
    let scale       = 0.9;
    let currentCanvas = canvas;

    while (scale >= minScale) {
        const newW  = Math.round(canvas.width  * scale);
        const newH  = Math.round(canvas.height * scale);
        const tmp   = document.createElement('canvas');
        tmp.width   = newW;
        tmp.height  = newH;
        const tctx  = tmp.getContext('2d');
        tctx.imageSmoothingEnabled = true;
        tctx.imageSmoothingQuality = 'high';
        tctx.drawImage(canvas, 0, 0, newW, newH);

        const r = await compressToTargetKB(tmp, targetKB);
        if (r.sizeKB <= targetKB) {
            return { ...r, canvas: tmp };
        }

        currentCanvas = tmp;
        scale        -= 0.1;
    }

    // Use whatever we have at minimum scale
    const final = await compressToTargetKB(currentCanvas, targetKB);
    return { ...final, canvas: currentCanvas };
}

/**
 * Export canvas as highest-quality JPEG without compression.
 * @param {HTMLCanvasElement} canvas
 * @returns {string} data URL
 */
export function toMaxQualityJPEG(canvas) {
    return canvas.toDataURL('image/jpeg', 1.0);
}
