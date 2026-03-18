document.addEventListener("DOMContentLoaded", function () {

    /* ======================================================
       ELEMENT REFERENCES
    ====================================================== */

    const previewContainer = document.getElementById("qr-preview-container");
    const inputContent = document.getElementById("qr-content");
    const inputColorDark = document.getElementById("qr-color-dark");
    const inputColorLight = document.getElementById("qr-color-light");
    const inputLogo = document.getElementById("qr-logo-file");
    const styleOptions = document.querySelectorAll(".style-opt");

    const downloadBtn = document.getElementById("btn-download");
    const syncBtn = document.getElementById("btn-sync");

    let debounceTimer;


    /* ======================================================
       QR DEFAULT SETTINGS
    ====================================================== */

    let qrSettings = {
        width: 300,
        height: 300,
        type: "svg",

        data: inputContent.value || "https://mediatools.id",

        image: "",

        dotsOptions: {
            color: "#a3e635",
            type: "square"
        },

        backgroundOptions: {
            color: "#ffffff"
        },

        imageOptions: {
            crossOrigin: "anonymous",
            margin: 10
        }
    };


    /* ======================================================
       INITIALIZE QR
    ====================================================== */

    const qrCode = new QRCodeStyling(qrSettings);
    qrCode.append(previewContainer);


    /* ======================================================
       QR LIVE RENDER UPDATE
    ====================================================== */

    function updateQR() {

        clearTimeout(debounceTimer);

        debounceTimer = setTimeout(() => {

            qrSettings.data = inputContent.value || "https://mediatools.id";
            qrSettings.dotsOptions.color = inputColorDark.value;
            qrSettings.backgroundOptions.color = inputColorLight.value;

            qrCode.update(qrSettings);

        }, 120);

    }


    /* ======================================================
       INPUT LISTENERS (REAL-TIME UPDATE)
    ====================================================== */

    [inputContent, inputColorDark, inputColorLight].forEach((el) => {

        el.addEventListener("input", updateQR);
        el.addEventListener("change", updateQR);

    });


    /* ======================================================
       STYLE SELECTOR (DOTS / SQUARE / ROUNDED)
    ====================================================== */

    styleOptions.forEach((option) => {

        option.addEventListener("click", function () {

            styleOptions.forEach((opt) => opt.classList.remove("active"));
            this.classList.add("active");

            qrSettings.dotsOptions.type = this.dataset.val;

            qrCode.update({
                dotsOptions: {
                    type: this.dataset.val,
                    color: inputColorDark.value
                }
            });

        });

    });


    /* ======================================================
       LOGO UPLOAD HANDLER
    ====================================================== */

    inputLogo?.addEventListener("change", function (event) {

        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();

        reader.onload = function (e) {

            qrSettings.image = e.target.result;

            qrCode.update({
                image: e.target.result
            });

        };

        reader.readAsDataURL(file);

    });


    /* ======================================================
       DOWNLOAD QR
    ====================================================== */

    downloadBtn?.addEventListener("click", function () {

        if (!inputContent.value.trim()) {

            showToast("Please enter a valid URL or content first.", "error");
            return;

        }

        qrCode.download({
            name: "MT-QR-Architect",
            extension: "png"
        });

        showToast("QR code successfully downloaded.");

    });


    /* ======================================================
       CLOUD SYNC
    ====================================================== */

    syncBtn?.addEventListener("click", async function () {

        if (!inputContent.value.trim()) {

            showToast("Please enter content before syncing.", "error");
            return;

        }

        const button = this;
        const originalHTML = button.innerHTML;

        button.disabled = true;
        button.innerHTML = '<i class="fa-solid fa-spinner animate-spin mr-2"></i> Syncing...';

        try {

            const response = await fetch("/qr/store", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    content: inputContent.value,
                    settings: JSON.stringify(qrSettings)
                })
            });

            if (response.ok) {

                showToast("QR configuration successfully synced to cloud.");

            } else {

                window.location.href = "/login";

            }

        } catch (error) {

            console.error(error);
            showToast("Failed to sync QR configuration.", "error");

        } finally {

            button.disabled = false;
            button.innerHTML = originalHTML;

        }

    });

});


/* ======================================================
   TOAST NOTIFICATION
====================================================== */

function showToast(message, type = "success") {

    const toast = document.getElementById("toast");
    const toastMsg = document.getElementById("toast-msg");

    if (!toast || !toastMsg) return;

    toastMsg.innerText = message;

    toast.classList.add("show");

    setTimeout(() => {
        toast.classList.remove("show");
    }, 3000);

}