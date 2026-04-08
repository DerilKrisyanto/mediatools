/**
 * MediaTools File Converter — fileconverter.js  v3 PRO
 * =====================================================
 * - Fixes double-upload bug (single click-handler on drop zone)
 * - Reliable state machine: idle → uploading → processing → done / error
 * - Multi-file support with remove-per-file
 * - JSZip-based Download All
 * - Clean category & type switching
 */

(function () {
    "use strict";

    /* ──────────────────────────────────────────────
       DOM REFS
    ────────────────────────────────────────────── */
    const dropZone       = document.getElementById("drop-zone");
    const fileInput      = document.getElementById("file-input");
    const fileList       = document.getElementById("file-list");
    const btnConvert     = document.getElementById("btn-convert");
    const btnConvertLbl  = document.getElementById("btn-convert-label");
    const btnAddMore     = document.getElementById("btn-add-more");
    const addCount       = document.getElementById("add-count");
    const acceptedHint   = document.getElementById("accepted-hint");
    const mainCard       = document.getElementById("fc-main-card");

    const stepUpload     = document.getElementById("step-upload");
    const stateProc      = document.getElementById("state-processing");
    const stateResult    = document.getElementById("state-result");
    const stateError     = document.getElementById("state-error");

    const procTitle      = document.getElementById("proc-title");
    const procSub        = document.getElementById("proc-sub");
    const progressBar    = document.getElementById("progress-bar");
    const resultTitle    = document.getElementById("result-title");
    const resultSub      = document.getElementById("result-sub");
    const resultFiles    = document.getElementById("result-files");
    const btnDownloadAll = document.getElementById("btn-download-all");
    const btnReset       = document.getElementById("btn-reset");
    const btnRetry       = document.getElementById("btn-retry");
    const errorMsg       = document.getElementById("error-msg");
    const toast          = document.getElementById("fc-toast");
    const toastMsg       = document.getElementById("fc-toast-msg");

    /* ──────────────────────────────────────────────
       STATE
    ────────────────────────────────────────────── */
    let selectedType  = null;   // e.g. "pdf_to_word"
    let selectedFmt   = "";     // e.g. "PDF"
    let selectedFiles = [];     // File[]
    let toastTimer    = null;
    let fakeTimer     = null;

    /* ──────────────────────────────────────────────
       ROUTES  (read from meta tags or fallback)
    ────────────────────────────────────────────── */
    const processUrl  = document.querySelector('meta[name="fc-process-url"]')?.content
                     || "/file-converter/process";
    const downloadUrl = document.querySelector('meta[name="fc-download-url"]')?.content
                     || "/file-converter/download";

    /* ──────────────────────────────────────────────
       CATEGORY TABS
    ────────────────────────────────────────────── */
    document.querySelectorAll(".fc-cat-btn").forEach((btn) => {
        btn.addEventListener("click", () => {
            const cat = btn.dataset.cat;

            // Update tab active state
            document.querySelectorAll(".fc-cat-btn").forEach((b) => {
                b.classList.toggle("active", b === btn);
                b.setAttribute("aria-selected", String(b === btn));
            });

            // Show/hide type groups
            document.querySelectorAll(".fc-type-group").forEach((g) => {
                g.classList.toggle("fc-hidden", g.dataset.cat !== cat);
            });

            // Reset type selection when switching category
            clearTypeSelection();
        });
    });

    /* ──────────────────────────────────────────────
       TYPE BUTTONS
    ────────────────────────────────────────────── */
    document.querySelectorAll(".fc-type-btn").forEach((btn) => {
        btn.addEventListener("click", () => {
            // Deselect all
            document.querySelectorAll(".fc-type-btn").forEach((b) => b.classList.remove("selected"));

            // Select clicked
            btn.classList.add("selected");
            selectedType = btn.dataset.type;
            selectedFmt  = btn.dataset.fmt || "";

            // Update accept attribute and hint
            const exts = selectedFmt.split(",").map((e) => "." + e.trim().toLowerCase());
            fileInput.accept = exts.join(",");
            if (acceptedHint) acceptedHint.textContent = "Format: " + selectedFmt;

            // Show main card
            mainCard.classList.remove("fc-hidden");

            // Update convert button
            updateConvertBtn();
        });
    });

    function clearTypeSelection() {
        document.querySelectorAll(".fc-type-btn").forEach((b) => b.classList.remove("selected"));
        selectedType = null;
        selectedFmt  = "";
        if (acceptedHint) acceptedHint.textContent = "Format: —";
        updateConvertBtn();
        // Hide card only if no files
        if (selectedFiles.length === 0) {
            mainCard.classList.add("fc-hidden");
        }
    }

    /* ──────────────────────────────────────────────
       FILE INPUT  (fix double-upload: ONE handler)
    ────────────────────────────────────────────── */

    // The <input type="file"> is positioned absolute over the drop zone.
    // We listen ONLY on the input's 'change' event — never trigger .click() manually.
    fileInput.addEventListener("change", (e) => {
        addFiles(Array.from(e.target.files || []));
        // Reset the input value so the same file can be re-selected
        fileInput.value = "";
    });

    // "Add More" button: open the same file input
    if (btnAddMore) {
        btnAddMore.addEventListener("click", (e) => {
            e.stopPropagation();
            fileInput.click();
        });
    }

    /* ──────────────────────────────────────────────
       DRAG & DROP
    ────────────────────────────────────────────── */
    dropZone.addEventListener("dragover", (e) => {
        e.preventDefault();
        dropZone.classList.add("drag-over");
    });

    ["dragleave", "dragend"].forEach((evt) => {
        dropZone.addEventListener(evt, () => dropZone.classList.remove("drag-over"));
    });

    dropZone.addEventListener("drop", (e) => {
        e.preventDefault();
        dropZone.classList.remove("drag-over");
        const files = Array.from(e.dataTransfer?.files || []);
        if (files.length) addFiles(files);
    });

    /* ──────────────────────────────────────────────
       ADD FILES
    ────────────────────────────────────────────── */
    function addFiles(incoming) {
        const MAX = 5;
        const MAX_BYTES = 50 * 1024 * 1024; // 50 MB

        if (!selectedType) {
            showToast("Pilih jenis konversi terlebih dahulu.", "warn");
            return;
        }

        const allowedExts = selectedFmt
            .split(",")
            .map((e) => e.trim().toLowerCase());

        let added = 0;

        for (const f of incoming) {
            if (selectedFiles.length >= MAX) {
                showToast(`Maksimal ${MAX} file per konversi.`, "warn");
                break;
            }

            // Extension check
            const ext = f.name.split(".").pop().toLowerCase();
            if (allowedExts.length && !allowedExts.includes(ext)) {
                showToast(`Format .${ext} tidak didukung untuk konversi ini.`, "warn");
                continue;
            }

            // Size check
            if (f.size > MAX_BYTES) {
                showToast(`${f.name} melebihi batas 50 MB.`, "warn");
                continue;
            }

            // Duplicate check
            if (selectedFiles.some((sf) => sf.name === f.name && sf.size === f.size)) {
                showToast(`${f.name} sudah ditambahkan.`, "warn");
                continue;
            }

            selectedFiles.push(f);
            added++;
        }

        if (added > 0) renderFileList();
        updateConvertBtn();
        updateAddMore();
    }

    /* ──────────────────────────────────────────────
       RENDER FILE LIST
    ────────────────────────────────────────────── */
    function renderFileList() {
        fileList.innerHTML = "";

        selectedFiles.forEach((f, idx) => {
            const item = document.createElement("div");
            item.className = "fc-file-item";
            item.setAttribute("role", "listitem");

            const iconCls = getFileIconClass(f.name);
            const iconSvg = getFileIconSymbol(f.name);

            item.innerHTML = `
                <div class="fc-file-icon ${iconCls}">
                    <i class="fa-solid ${iconSvg}"></i>
                </div>
                <div class="fc-file-meta">
                    <span class="fc-file-name" title="${escHtml(f.name)}">${escHtml(f.name)}</span>
                    <span class="fc-file-size">${formatBytes(f.size)}</span>
                </div>
                <button class="fc-file-remove" aria-label="Hapus ${escHtml(f.name)}" data-idx="${idx}">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
            fileList.appendChild(item);
        });

        // Remove handlers
        fileList.querySelectorAll(".fc-file-remove").forEach((btn) => {
            btn.addEventListener("click", (e) => {
                const i = parseInt(btn.dataset.idx, 10);
                selectedFiles.splice(i, 1);
                renderFileList();
                updateConvertBtn();
                updateAddMore();

                if (selectedFiles.length === 0) {
                    // Show drop zone placeholder again
                    document.getElementById("drop-placeholder")?.classList.remove("fc-hidden");
                }
            });
        });

        // Hide placeholder when files present
        const placeholder = document.getElementById("drop-placeholder");
        if (placeholder) {
            placeholder.classList.toggle("fc-hidden", selectedFiles.length > 0);
        }
    }

    /* ──────────────────────────────────────────────
       UI HELPERS
    ────────────────────────────────────────────── */
    function updateConvertBtn() {
        const ready = selectedType && selectedFiles.length > 0;
        btnConvert.disabled = !ready;

        if (!selectedType) {
            btnConvertLbl.textContent = "Pilih jenis konversi dahulu";
        } else if (selectedFiles.length === 0) {
            btnConvertLbl.textContent = "Upload file terlebih dahulu";
        } else {
            const n = selectedFiles.length;
            btnConvertLbl.textContent = `Konversi ${n} File${n > 1 ? "s" : ""}`;
        }
    }

    function updateAddMore() {
        if (!btnAddMore || !addCount) return;
        const n = selectedFiles.length;
        const visible = n > 0 && n < 5;
        btnAddMore.classList.toggle("fc-hidden", !visible);
        addCount.textContent = `${n}/5`;
    }

    /* ──────────────────────────────────────────────
       CONVERT
    ────────────────────────────────────────────── */
    btnConvert.addEventListener("click", doConvert);

    async function doConvert() {
        if (!selectedType || selectedFiles.length === 0) return;

        showState("processing");
        startFakeProgress();

        const fd = new FormData();
        fd.append("conv_type", selectedType);
        fd.append("_token", getCsrf());
        selectedFiles.forEach((f) => fd.append("files[]", f));

        try {
            const resp = await fetch(processUrl, {
                method: "POST",
                body: fd,
                headers: { "X-CSRF-TOKEN": getCsrf() },
            });

            stopFakeProgress(100);

            const data = await resp.json().catch(() => ({
                success: false,
                message: `Server returned ${resp.status} — bukan JSON.`,
            }));

            if (!resp.ok || !data.success) {
                const msg = data.message || data.errors?.[0]?.error || "Konversi gagal.";
                showErrorState(msg);
                return;
            }

            showResultState(data.files || [], data.errors || []);
        } catch (err) {
            stopFakeProgress(0);
            showErrorState("Tidak dapat terhubung ke server. Periksa koneksi internet Anda.");
            console.error("FC Error:", err);
        }
    }

    /* ──────────────────────────────────────────────
       PROGRESS (fake visual feedback)
    ────────────────────────────────────────────── */
    let fakeVal = 0;

    function startFakeProgress() {
        fakeVal = 0;
        progressBar.style.width = "0%";
        procTitle.textContent = "Mengkonversi file...";
        procSub.textContent   = "Memproses...";

        const steps = [
            { pct: 15, delay: 400,  msg: "Membaca file..." },
            { pct: 35, delay: 1200, msg: "Mengkonversi..." },
            { pct: 60, delay: 3000, msg: "Membangun dokumen output..." },
            { pct: 80, delay: 6000, msg: "Hampir selesai..." },
            { pct: 90, delay: 12000, msg: "Menyelesaikan..." },
        ];

        let cumDelay = 0;
        steps.forEach(({ pct, delay, msg }) => {
            cumDelay += delay;
            setTimeout(() => {
                if (fakeVal < pct) {
                    fakeVal = pct;
                    progressBar.style.width = pct + "%";
                    procSub.textContent = msg;
                }
            }, cumDelay);
        });
    }

    function stopFakeProgress(finalPct) {
        if (fakeTimer) clearTimeout(fakeTimer);
        fakeVal = finalPct;
        progressBar.style.width = finalPct + "%";
    }

    /* ──────────────────────────────────────────────
       STATE MACHINE
    ────────────────────────────────────────────── */
    function showState(name) {
        [stepUpload, stateProc, stateResult, stateError].forEach((el) => {
            if (el) el.classList.add("fc-hidden");
        });

        switch (name) {
            case "upload":
                if (stepUpload)  stepUpload.classList.remove("fc-hidden");
                if (btnConvert)  btnConvert.classList.remove("fc-hidden");
                break;
            case "processing":
                if (stateProc)   stateProc.classList.remove("fc-hidden");
                if (btnConvert)  btnConvert.classList.add("fc-hidden");
                break;
            case "result":
                if (stateResult) stateResult.classList.remove("fc-hidden");
                if (btnConvert)  btnConvert.classList.add("fc-hidden");
                break;
            case "error":
                if (stateError)  stateError.classList.remove("fc-hidden");
                if (btnConvert)  btnConvert.classList.add("fc-hidden");
                break;
        }
    }

    function showResultState(files, errors) {
        showState("result");

        const count = files.length;
        resultTitle.textContent = count > 1
            ? `${count} File Berhasil Dikonversi!`
            : "Konversi Selesai!";
        resultSub.textContent = "File siap diunduh.";

        resultFiles.innerHTML = "";

        files.forEach((f) => {
            const row = document.createElement("div");
            row.className = "fc-result-file";
            row.setAttribute("role", "listitem");

            const dlHref = `${downloadUrl}/${f.token}`;

            row.innerHTML = `
                <div class="fc-result-file-icon">
                    <i class="fa-solid ${getFileIconSymbol(f.filename)}"></i>
                </div>
                <div class="fc-result-file-meta">
                    <span class="fc-result-file-name" title="${escHtml(f.filename)}">${escHtml(f.filename)}</span>
                    <span class="fc-result-file-size">${formatBytes(f.size)}</span>
                    <span class="fc-result-file-engine">engine: ${escHtml(f.engine || "?")}</span>
                </div>
                <a href="${dlHref}" class="fc-btn-dl-single" download="${escHtml(f.filename)}">
                    <i class="fa-solid fa-download"></i> Download
                </a>
            `;
            resultFiles.appendChild(row);
        });

        // Partial errors notice
        if (errors.length > 0) {
            const notice = document.createElement("div");
            notice.className = "fc-partial-notice";
            notice.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i>
                <span>${errors.length} file gagal dikonversi: ${errors.map((e) => escHtml(e.file)).join(", ")}</span>`;
            resultFiles.appendChild(notice);
        }

        // "Download All" ZIP — if multiple files
        if (files.length > 1) {
            btnDownloadAll.classList.remove("fc-hidden");
            btnDownloadAll.onclick = () => downloadAllAsZip(files);
        } else {
            btnDownloadAll.classList.add("fc-hidden");
        }
    }

    function showErrorState(msg) {
        showState("error");
        if (errorMsg) errorMsg.textContent = msg;
    }

    /* ──────────────────────────────────────────────
       DOWNLOAD ALL (ZIP via JSZip)
    ────────────────────────────────────────────── */
    async function downloadAllAsZip(files) {
        if (typeof JSZip === "undefined") {
            showToast("JSZip tidak tersedia — download satu per satu.", "warn");
            return;
        }

        const zip = new JSZip();
        btnDownloadAll.disabled = true;
        btnDownloadAll.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Menyiapkan ZIP...</span>';

        let ok = 0;
        for (const f of files) {
            try {
                const dlHref = `${downloadUrl}/${f.token}`;
                const resp   = await fetch(dlHref);
                if (resp.ok) {
                    const blob = await resp.blob();
                    zip.file(f.filename, blob);
                    ok++;
                }
            } catch (_) {
                // skip failed file
            }
        }

        if (ok === 0) {
            showToast("Tidak ada file yang bisa di-ZIP.", "warn");
            btnDownloadAll.disabled = false;
            btnDownloadAll.innerHTML = '<i class="fa-solid fa-file-zipper"></i> <span>Download Semua (ZIP)</span>';
            return;
        }

        const content = await zip.generateAsync({ type: "blob" });
        triggerDownload(content, "mediatools_converted.zip");
        btnDownloadAll.disabled = false;
        btnDownloadAll.innerHTML = '<i class="fa-solid fa-file-zipper"></i> <span>Download Semua (ZIP)</span>';
        showToast(`${ok} file berhasil dizip!`);
    }

    function triggerDownload(blob, filename) {
        const url = URL.createObjectURL(blob);
        const a   = document.createElement("a");
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        setTimeout(() => {
            URL.revokeObjectURL(url);
            a.remove();
        }, 2000);
    }

    /* ──────────────────────────────────────────────
       RESET
    ────────────────────────────────────────────── */
    if (btnReset) {
        btnReset.addEventListener("click", resetAll);
    }
    if (btnRetry) {
        btnRetry.addEventListener("click", resetAll);
    }

    function resetAll() {
        selectedFiles = [];
        fileList.innerHTML = "";
        fileInput.value   = "";

        // Show placeholder
        const placeholder = document.getElementById("drop-placeholder");
        if (placeholder) placeholder.classList.remove("fc-hidden");

        updateConvertBtn();
        updateAddMore();
        showState("upload");
        btnConvert.classList.remove("fc-hidden");
        progressBar.style.width = "0%";
    }

    /* ──────────────────────────────────────────────
       TOAST
    ────────────────────────────────────────────── */
    function showToast(msg, type = "ok") {
        if (!toast || !toastMsg) return;
        if (toastTimer) clearTimeout(toastTimer);

        toastMsg.textContent = msg;

        const ico = toast.querySelector(".fc-toast-ico");
        if (ico) {
            ico.className = type === "warn"
                ? "fa-solid fa-triangle-exclamation fc-toast-ico"
                : "fa-solid fa-check fc-toast-ico";
            ico.style.color = type === "warn" ? "#fbbf24" : "#a3e635";
        }

        toast.classList.add("show");
        toastTimer = setTimeout(() => toast.classList.remove("show"), 3200);
    }

    /* ──────────────────────────────────────────────
       UTILS
    ────────────────────────────────────────────── */
    function getCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content || "";
    }

    function formatBytes(bytes) {
        if (bytes === 0) return "0 B";
        const k    = 1024;
        const unit = ["B", "KB", "MB", "GB"];
        const i    = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + " " + unit[i];
    }

    function escHtml(str) {
        const map = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" };
        return String(str).replace(/[&<>"']/g, (c) => map[c]);
    }

    function getFileIconClass(name) {
        const ext = name.split(".").pop().toLowerCase();
        if (ext === "pdf") return "fc-file-icon--pdf";
        if (["doc", "docx"].includes(ext)) return "fc-file-icon--word";
        if (["xls", "xlsx"].includes(ext)) return "fc-file-icon--excel";
        if (["ppt", "pptx"].includes(ext)) return "fc-file-icon--ppt";
        if (["jpg", "jpeg", "png", "gif", "webp", "bmp"].includes(ext)) return "fc-file-icon--img";
        return "fc-file-icon--def";
    }

    function getFileIconSymbol(name) {
        const ext = name.split(".").pop().toLowerCase();
        if (ext === "pdf") return "fa-file-pdf";
        if (["doc", "docx"].includes(ext)) return "fa-file-word";
        if (["xls", "xlsx"].includes(ext)) return "fa-file-excel";
        if (["ppt", "pptx"].includes(ext)) return "fa-file-powerpoint";
        if (["jpg", "jpeg", "png", "gif", "webp", "bmp"].includes(ext)) return "fa-image";
        if (ext === "zip") return "fa-file-zipper";
        return "fa-file";
    }

    /* init */
    showState("upload");

})();
