document.addEventListener("DOMContentLoaded", function () {

let currentMode = 'single';
let lastResults = [];
let lastSession = null;

const states = document.querySelectorAll('.state');
const fileInput = document.getElementById('fileInput');
const fileName  = document.getElementById('fileName');
const previewList = document.getElementById('previewList');
const resultList  = document.getElementById('resultList');
const downloadBtn = document.getElementById('downloadBtn');
const modeButtons = document.querySelectorAll('.mode-btn');
const dropzone = document.getElementById('dropzone');

function showState(name) {
    states.forEach(s => s.classList.remove('active'));
    document.querySelector('.state-' + name).classList.add('active');
}

// ========== MODE SWITCH ========== //
modeButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        modeButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        currentMode = btn.dataset.mode;
        fileInput.multiple = currentMode === 'multi';

        resetAll();
    });
});

// ========== DROPZONE ========== //
dropzone.addEventListener('click', () => fileInput.click());

dropzone.addEventListener('dragover', e => {
    e.preventDefault();
    dropzone.classList.add('dragging');
});

dropzone.addEventListener('dragleave', () => {
    dropzone.classList.remove('dragging');
});

dropzone.addEventListener('drop', e => {
    e.preventDefault();
    dropzone.classList.remove('dragging');

    const allowedTypes = ["image/jpeg", "image/png", "image/jpg"];

    for (let file of e.dataTransfer.files) {
        if (!allowedTypes.includes(file.type)) {
            alert("Only JPG, JPEG and PNG images are allowed.");
            return;
        }
    }

    fileInput.files = e.dataTransfer.files;
    handleFiles();
});

// ========== FILE SELECT ========== //
fileInput.addEventListener('change', handleFiles);

function handleFiles() {

    if (!fileInput.files.length) return;

    const allowedTypes = ["image/jpeg", "image/png", "image/jpg"];

    for (let file of fileInput.files) {
        if (!allowedTypes.includes(file.type)) {
            alert("Only JPG, JPEG and PNG images are allowed.");
            fileInput.value = '';
            return;
        }
    }

    previewList.innerHTML = '';

    Array.from(fileInput.files).forEach(file => {
        const div = document.createElement('div');
        div.className = 'preview-item';

        div.innerHTML = `
        <img src="${URL.createObjectURL(file)}">
        `;

        previewList.appendChild(div);
    });

    fileName.textContent =
        currentMode === 'single'
            ? fileInput.files[0].name
            : `${fileInput.files.length} images selected`;

    showState('preview');
}

// ========== PROCESS ========== //
document.getElementById('removeBg').onclick = async () => {

    if (!fileInput.files.length) return;

    showState('processing');

    removeBg.disabled = true;
    removeBg.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

    const formData = new FormData();
    const files = Array.from(fileInput.files);

    files.forEach(f => {
        formData.append('files[]', f);
    });

    const token = document.querySelector('meta[name="csrf-token"]').content;

    try {
        const res = await fetch('/bg/process', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token
            },
            body: formData
        });

        if (!res.ok) {
            const text = await res.text();
            console.error("Server error:", text);
            alert("Server error. Check console.");
            showState('preview');
            return;
        }

        let data;

        try {
            data = await res.json();
        } catch {
            alert("Invalid server response");
            showState('preview');
            return;
        }

        lastResults = data.results;
        lastSession = data.session_id;

        resultList.innerHTML = '';

        data.results.forEach((item, index) => {

            const row = document.createElement('div');
            row.className = 'result-row';

            row.innerHTML = `
                <div class="result-box">
                    <div class="result-label">Before</div>
                    <img src="${URL.createObjectURL(files[index])}">
                </div>

                <div class="arrow">→</div>

                <div class="result-box transparent-bg">
                    <div class="result-label">After</div>
                    <img src="${item.result_url}">
                </div>
            `;

            resultList.appendChild(row);
        });

        showState('result');
        removeBg.disabled = false;
        removeBg.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Remove Background';

    } catch (err) {
        console.error(err);
        alert("Processing failed");
        showState('preview');
    }
};

// ========== DOWNLOAD ========== //
downloadBtn.onclick = () => {

    if (!lastSession || !lastResults.length) return;

    lastResults.forEach((result,i) => {

        setTimeout(()=>{

            // Ambil nama file dari URL
            const urlParts = result.result_url.split('/');
            const filename = urlParts[urlParts.length - 1];

            const link = document.createElement('a');
            link.href = `/bg/download/${lastSession}/${filename}`;
            link.target = '_blank';

            document.body.appendChild(link);
            link.click();
            link.remove();
        });

    });
};

// ========== CHANGE IMAGE ========== //
document.getElementById('changeImage').onclick = async () => {
    const token = document.querySelector('meta[name="csrf-token"]').content;
    if (lastSession) {
        await fetch('/bg/cleanup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ session_id: lastSession })
        });
    }

    resetAll();
};

// ========== PROCESS ANOTHER ========== //
document.getElementById('processAnother').onclick = async () => {
    const token = document.querySelector('meta[name="csrf-token"]').content;
    if (lastSession) {
        await fetch('/bg/cleanup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ session_id: lastSession })
        });
    }

    resetAll();
};

function resetAll() {
    fileInput.value = '';
    previewList.innerHTML = '';
    resultList.innerHTML = '';
    lastResults = [];
    lastSession = null;
    showState('idle');
}

});