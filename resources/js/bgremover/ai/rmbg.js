/**
 * resources/js/bgremover/ai/rmbg.js
 *
 * Sends the image to the Laravel backend (Python → rembg → BiRefNet / isnet).
 * Returns a PNG Blob — same contract as the old client-side removeRMBG().
 *
 * Progress simulation:
 *   0-25%   real upload progress (XHR upload events)
 *   25-88%  simulated steps that mirror actual server-side stages
 *   88-100% set in index.js once the blob is received
 */

const PROCESS_URL = '/tools/bgremover/process';
const TIMEOUT_MS  = 180_000;  // 3 min hard limit

/**
 * removeRMBG(file, quality, onProgress) → Promise<Blob (image/png)>
 *
 * @param {File}   file       - image file selected by user
 * @param {string} quality    - 'small'|'medium' (maps to fast/high internally)
 * @param {Function} onProgress - (pct: number, label: string) => void
 */
export function removeRMBG(file, quality = 'medium', onProgress = () => {}) {

  // Map internal quality tokens to backend param
  const q = quality === 'small' ? 'fast' : 'high';

  return new Promise((resolve, reject) => {

    /* ── Build form data ── */
    const fd   = new FormData();
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    fd.append('image',   file);
    fd.append('quality', q);

    /* ── Simulated AI-progress ticker (runs while server processes) ── */
    const SIM_STEPS = [
      [26, 'AI menganalisis komposisi gambar…'],
      [38, 'Mendeteksi subjek utama…'],
      [50, 'Membangun segmentation mask…'],
      [62, 'Memproses area rambut & detail halus…'],
      [72, 'Memperbaiki tepi transparan…'],
      [80, 'Alpha matting pada area edge…'],
      [88, 'Mengoptimalkan kualitas output…'],
    ];

    let simPct = 25;
    let stepIdx = 0;

    const simTick = setInterval(() => {
      if (stepIdx >= SIM_STEPS.length) return;
      const [target, label] = SIM_STEPS[stepIdx];
      if (simPct < target) {
        simPct += 0.6;
        onProgress(Math.min(simPct, target), label);
      } else {
        stepIdx++;
      }
    }, 120);

    const stopSim = () => clearInterval(simTick);

    /* ── XHR ── */
    const xhr = new XMLHttpRequest();
    xhr.open('POST', PROCESS_URL, true);
    xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
    xhr.setRequestHeader('Accept', 'image/png, application/json');
    xhr.responseType = 'blob';
    xhr.timeout      = TIMEOUT_MS;

    /* Upload progress → 0-25% */
    xhr.upload.onprogress = (e) => {
      if (!e.lengthComputable) return;
      const pct = 2 + (e.loaded / e.total) * 23;
      onProgress(pct, `Mengunggah gambar… ${Math.round(e.loaded / e.total * 100)}%`);
    };

    xhr.onload = async () => {
      stopSim();

      if (xhr.status === 200) {
        // Verify we got an image, not an error JSON
        const ct = xhr.getResponseHeader('Content-Type') ?? '';
        if (ct.startsWith('image/')) {
          onProgress(92, 'Memuat hasil…');
          resolve(xhr.response);
        } else {
          // Server returned JSON error inside a blob — decode it
          try {
            const text = await xhr.response.text();
            const json = JSON.parse(text);
            reject(new Error(json.error ?? 'Server error'));
          } catch {
            reject(new Error(`Server error ${xhr.status}`));
          }
        }
      } else {
        // Try to extract message from JSON blob
        try {
          const text = await xhr.response.text();
          const json = JSON.parse(text);
          reject(new Error(json.error ?? `HTTP ${xhr.status}`));
        } catch {
          reject(new Error(`HTTP ${xhr.status} — server error`));
        }
      }
    };

    xhr.onerror   = () => { stopSim(); reject(new Error('Network error — tidak dapat menghubungi server')); };
    xhr.ontimeout = () => { stopSim(); reject(new Error('Timeout — server membutuhkan waktu terlalu lama')); };
    xhr.onabort   = () => { stopSim(); reject(new Error('Dibatalkan')); };

    xhr.send(fd);
  });
}