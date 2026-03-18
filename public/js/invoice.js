/* ============================================================
   MediaTools — Invoice Generator
   invoice.js
   ============================================================ */

'use strict';

/* ── State ──────────────────────────────────────────────── */
let items = [
    { id: 1, desc: 'Jasa Desain Website', subDesc: 'Revisi maksimal 3x · Termasuk hosting domain', qty: 1, price: 5000000 },
    { id: 2, desc: 'Pembuatan Konten Sosial Media',  subDesc: '10 postingan feed + caption', qty: 5, price: 350000 },
];

let notes = [
    'Pembayaran penuh dilakukan sebelum project dimulai.',
    'Revisi di luar ketentuan dikenakan biaya tambahan.',
];

let payments = [
    { bank: 'Bank Central Asia (BCA)', account: '1234-5678-90', owner: 'PT Media Tools Indonesia' },
];

let currentTemplate = 1;

/* ── Formatter ──────────────────────────────────────────── */
function fmt(num) {
    return new Intl.NumberFormat('id-ID').format(Math.round(num));
}

/* ── Template Switching ─────────────────────────────────── */
function setTemplate(num) {
    currentTemplate = num;
    const paper = document.getElementById('invoice-content');
    if (paper) paper.dataset.template = num;

    document.querySelectorAll('.tpl-btn').forEach(btn => {
        btn.classList.toggle('active', parseInt(btn.dataset.tpl) === num);
    });
}

/* ── Items ──────────────────────────────────────────────── */
function renderItems() {
    const container = document.getElementById('item-list');
    if (!container) return;

    container.innerHTML = '';

    items.forEach((item, idx) => {
        const tr = document.createElement('tr');
        tr.className = 'inv-item-row';

        tr.innerHTML = `
            <td style="text-align:center;font-size:10px;font-weight:700;color:#94a3b8;padding:8px 4px;vertical-align:top;">
                ${idx + 1}
            </td>
            <td style="padding:8px;vertical-align:top;">
                <div contenteditable="true"
                     class="inv-item-desc"
                     data-id="${item.id}" data-key="desc"
                     onblur="handleItemTextBlur(this)">${item.desc}</div>
                <div contenteditable="true"
                     class="inv-item-subdesc"
                     data-id="${item.id}" data-key="subDesc"
                     onblur="handleItemTextBlur(this)">${item.subDesc}</div>
            </td>
            <td style="padding:8px 4px;vertical-align:top;text-align:center;width:52px;">
                <input type="number"
                       class="inv-item-qty"
                       value="${item.qty}"
                       min="0.01" step="0.01"
                       style="width:44px;"
                       data-id="${item.id}" data-key="qty"
                       oninput="handleItemNumInput(this)">
            </td>
            <td style="padding:8px 4px;vertical-align:top;text-align:right;width:120px;">
                <input type="number"
                       class="inv-item-price"
                       value="${item.price}"
                       min="0"
                       style="width:110px;text-align:right;"
                       data-id="${item.id}" data-key="price"
                       oninput="handleItemNumInput(this)">
            </td>
            <td class="inv-item-total" id="row-total-${item.id}" style="width:110px;text-align:right;padding:8px 4px;vertical-align:top;">
                ${fmt(item.qty * item.price)}
            </td>
            <td style="width:28px;padding:4px;vertical-align:top;" class="no-print">
                <button class="inv-del-btn" onclick="removeItem(${item.id})" title="Hapus baris">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        `;

        container.appendChild(tr);
    });

    calculate();
    checkPageFit();
}

function handleItemTextBlur(el) {
    const id  = parseInt(el.dataset.id);
    const key = el.dataset.key;
    const item = items.find(i => i.id === id);
    if (item) item[key] = el.innerText.trim();
}

function handleItemNumInput(el) {
    const id  = parseInt(el.dataset.id);
    const key = el.dataset.key;
    const item = items.find(i => i.id === id);
    if (!item) return;

    item[key] = parseFloat(el.value) || 0;

    // Update only the total cell for this row (no full re-render)
    const totalCell = document.getElementById(`row-total-${id}`);
    if (totalCell) totalCell.textContent = fmt(item.qty * item.price);

    calculate();
    checkPageFit();
}

function addItem() {
    items.push({
        id: Date.now(),
        desc: 'Nama Layanan',
        subDesc: 'Deskripsi singkat layanan',
        qty: 1,
        price: 0,
    });
    renderItems();
}

function removeItem(id) {
    if (items.length <= 1) return; // keep minimum 1
    items = items.filter(i => i.id !== id);
    renderItems();
}

/* ── Notes ──────────────────────────────────────────────── */
function renderNotes() {
    const container = document.getElementById('note-list');
    if (!container) return;

    container.innerHTML = '';
    notes.forEach((note, idx) => {
        const div = document.createElement('div');
        div.className = 'inv-note-item';
        div.innerHTML = `
            <span class="inv-note-dot">•</span>
            <div contenteditable="true"
                 class="inv-note-text"
                 onblur="notes[${idx}] = this.innerText.trim()">${note}</div>
            <button class="inv-del-btn no-print"
                    onclick="notes.splice(${idx}, 1); renderNotes();"
                    title="Hapus catatan">
                <i class="fa-solid fa-times"></i>
            </button>
        `;
        container.appendChild(div);
    });
}

function addNote() {
    notes.push('Tulis catatan di sini...');
    renderNotes();
}

/* ── Payments ───────────────────────────────────────────── */
function renderPayments() {
    const container = document.getElementById('payment-list');
    if (!container) return;

    container.innerHTML = '';
    payments.forEach((pay, idx) => {
        const div = document.createElement('div');
        div.className = 'inv-payment-item';
        div.innerHTML = `
            <div contenteditable="true" class="inv-payment-bank"
                 onblur="payments[${idx}].bank = this.innerText.trim()">${pay.bank}</div>
            <div contenteditable="true" class="inv-payment-acc"
                 onblur="payments[${idx}].account = this.innerText.trim()">${pay.account}</div>
            <div contenteditable="true" class="inv-payment-own"
                 onblur="payments[${idx}].owner = this.innerText.trim()">A/N: ${pay.owner}</div>
            <button class="inv-payment-del no-print"
                    onclick="payments.splice(${idx}, 1); renderPayments();"
                    title="Hapus rekening">
                <i class="fa-solid fa-trash"></i>
            </button>
        `;
        container.appendChild(div);
    });
}

function addPayment() {
    payments.push({ bank: 'Nama Bank', account: '000-000-0000', owner: 'Nama Pemilik' });
    renderPayments();
}

/* ── Calculations ───────────────────────────────────────── */
function calculate() {
    const subtotal  = items.reduce((sum, i) => sum + (i.qty * i.price), 0);
    const discP     = parseFloat(document.getElementById('discountPercent')?.value) || 0;
    const discVal   = subtotal * (discP / 100);
    const taxP      = parseFloat(document.getElementById('taxPercent')?.value) || 0;
    const taxVal    = (subtotal - discVal) * (taxP / 100);
    const grand     = (subtotal - discVal) + taxVal;

    setText('inv-subtotal',    fmt(subtotal));
    setText('inv-discount-val', fmt(discVal));
    setText('inv-tax-val',     fmt(taxVal));
    setText('inv-grand-total', fmt(grand));
}

function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
}

/* ── Page Fit Check ─────────────────────────────────────── */
function checkPageFit() {
    const paper   = document.getElementById('invoice-content');
    const warning = document.getElementById('page-warning');
    if (!paper || !warning) return;

    // A4 height in px at 96dpi ≈ 1123px
    const A4_HEIGHT = 1123;
    const actualH   = paper.scrollHeight;

    warning.classList.toggle('show', actualH > A4_HEIGHT * 1.02);
}

/* ── Logo Upload ────────────────────────────────────────── */
function initLogoUpload() {
    const uploadInput = document.getElementById('logoUpload');
    const preview     = document.getElementById('inv-logo-preview');
    const placeholder = document.getElementById('inv-logo-placeholder');
    const removeBtn   = document.getElementById('inv-logo-remove');
    const logoWrap    = document.getElementById('inv-logo-wrap');

    if (!uploadInput) return;

    // Click on placeholder to trigger file input
    placeholder.addEventListener('click', () => uploadInput.click());
    // Also allow clicking preview to change logo
    preview.addEventListener('click', () => uploadInput.click());

    uploadInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file || !file.type.startsWith('image/')) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
            removeBtn.style.display = 'flex';
        };
        reader.readAsDataURL(file);
    });

    removeBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        preview.src = '';
        preview.style.display = 'none';
        placeholder.style.display = 'flex';
        removeBtn.style.display = 'none';
        uploadInput.value = '';
    });
}

/* ── PDF Download ───────────────────────────────────────── */
function downloadPDF(btn) {
    const paper   = document.getElementById('invoice-content');
    const bizName = document.getElementById('inv-biz-name')?.innerText?.trim() || 'Invoice';
    const invNum  = document.getElementById('inv-number')?.innerText?.trim()   || '';

    const filename = `Invoice_${bizName}_${invNum}`.replace(/[^a-z0-9_\-]/gi, '_') + '.pdf';

    // Save original button content
    const origHTML = btn.innerHTML;
    btn.innerHTML  = '<i class="fa-solid fa-spinner fa-spin"></i><span>Generating…</span>';
    btn.disabled   = true;

    const options = {
        margin:     [0, 0, 0, 0],
        filename,
        image:      { type: 'jpeg', quality: 0.97 },
        html2canvas: {
            scale:         2.5,
            useCORS:       true,
            letterRendering: true,
            logging:       false,
            onclone(clonedDoc) {
                // Hide all editor-only elements in the cloned document
                clonedDoc.querySelectorAll('.no-print').forEach(el => {
                    el.style.display = 'none';
                });

                // Remove contenteditable outlines
                clonedDoc.querySelectorAll('[contenteditable]').forEach(el => {
                    el.style.outline    = 'none';
                    el.style.background = 'transparent';
                    el.style.boxShadow  = 'none';
                });

                // Remove input borders/outlines
                clonedDoc.querySelectorAll('input').forEach(el => {
                    el.style.border     = 'none';
                    el.style.outline    = 'none';
                    el.style.background = 'transparent';
                    el.style.boxShadow  = 'none';
                });
            },
        },
        jsPDF: {
            unit:        'mm',
            format:      'a4',
            orientation: 'portrait',
            compress:    true,
        },
        pagebreak: {
            mode:   'avoid-all',
            before: '.page-break-before',
            after:  '.page-break-after',
        },
    };

    html2pdf()
        .set(options)
        .from(paper)
        .save()
        .then(() => {
            btn.innerHTML = '<i class="fa-solid fa-check"></i><span>Berhasil!</span>';
            setTimeout(() => {
                btn.innerHTML = origHTML;
                btn.disabled  = false;
            }, 2200);
        })
        .catch(() => {
            btn.innerHTML = '<i class="fa-solid fa-exclamation-triangle"></i><span>Gagal</span>';
            btn.disabled  = false;
        });
}

/* ── Initialisation ─────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    setTemplate(1);
    renderItems();
    renderNotes();
    renderPayments();
    initLogoUpload();

    // Wire up tax/discount inputs
    document.getElementById('discountPercent')?.addEventListener('input', calculate);
    document.getElementById('taxPercent')?.addEventListener('input', calculate);

    // Initial page fit check after a tick (let DOM settle)
    setTimeout(checkPageFit, 150);
});