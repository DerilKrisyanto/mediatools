/* ============================================================
   MediaTools — LinkTree Builder
   linktree.js
   ============================================================ */

'use strict';

/* ── State ──────────────────────────────────────────────── */
let selectedPlan     = null;
let selectedTemplate = 'dark';
let isEditMode       = false;

/* ── Modal helpers ──────────────────────────────────────── */
function openModal(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.add('open'); document.body.style.overflow = 'hidden'; }
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.remove('open'); }
}

window.closeAllModals = function () {
    ['planModal','createModal'].forEach(closeModal);
    document.body.style.overflow = '';
};

/* Close on backdrop click */
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('lt-modal-backdrop')) closeAllModals();
});

/* ── Entry point when user clicks "Create / Edit" ──────── */
window.handleCreateStep = async function () {
    const res    = await fetch(document.getElementById('checkPlanUrl').value, { method:'POST', headers:{ 'X-CSRF-TOKEN': csrf() } });
    const data   = await res.json();

    if (data.has_plan) {
        // User already has an active plan — go straight to edit form
        isEditMode = true;
        populateEditForm(data.linktree);
        openModal('createModal');
    } else {
        isEditMode = false;
        openModal('planModal');
    }
};

window.handleEditClick = function (json) {
    isEditMode = true;
    const lt = JSON.parse(decodeURIComponent(json));
    populateEditForm(lt);
    openModal('createModal');
};

/* ── Populate form for edit mode ───────────────────────── */
function populateEditForm(lt) {
    if (!lt) return;
    setVal('name',      lt.name?.replace(/^@/, ''));
    setVal('username',  lt.username?.replace(/^@/, ''));
    setVal('bio',       lt.bio);
    setVal('web_url',   lt.links_data?.[0]?.url || '');

    // Socials
    (lt.socials_data || []).forEach(s => {
        if (s.icon?.includes('instagram')) setVal('ig_user', s.url?.replace('https://instagram.com/', ''));
        if (s.icon?.includes('tiktok'))    setVal('tt_user', s.url?.replace('https://tiktok.com/@', ''));
        if (s.icon?.includes('whatsapp'))  setVal('wa_number', s.url?.replace('https://wa.me/', ''));
    });

    if (lt.avatar) {
        const prev = document.getElementById('avatarPreview');
        if (prev) prev.src = lt.avatar;
    }

    // Set template
    selectTemplate(lt.page_template || 'dark');

    // Hidden flag
    setVal('plan_type', 'existing');
    document.getElementById('selected_plan_input').value = 'existing';

    // Update submit label
    const btn = document.getElementById('submitBtn');
    if (btn) btn.querySelector('span').textContent = 'Simpan Perubahan';
}

/* ── Plan selection ─────────────────────────────────────── */
window.selectPlan = function (plan) {
    selectedPlan = plan;
    document.getElementById('selected_plan_input').value = plan;

    document.querySelectorAll('.lt-plan-card').forEach(c => {
        c.classList.toggle('selected', c.dataset.plan === plan);
    });

    // Small delay so user sees selection flash
    setTimeout(() => {
        closeModal('planModal');
        openModal('createModal');
        isEditMode = false;
    }, 260);
};

/* ── Template selection ─────────────────────────────────── */
window.selectTemplate = function (tpl) {
    selectedTemplate = tpl;
    document.getElementById('page_template_input').value = tpl;

    document.querySelectorAll('.lt-tpl-option').forEach(o => {
        o.classList.toggle('active', o.dataset.tpl === tpl);
    });
};

/* ── Avatar upload ──────────────────────────────────────── */
(function initAvatar() {
    document.addEventListener('DOMContentLoaded', function () {
        const input   = document.getElementById('avatarInput');
        const preview = document.getElementById('avatarPreview');
        const b64     = document.getElementById('avatar_base64');
        if (!input) return;

        input.addEventListener('change', function () {
            const file = this.files[0];
            if (!file || !file.type.startsWith('image/')) return;
            if (file.size > 2 * 1024 * 1024) { ltToast('Gambar maksimal 2MB.', 'error'); return; }

            const reader = new FileReader();
            reader.onload = e => {
                if (preview) preview.src = e.target.result;
                if (b64)     b64.value   = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    });
})();

/* ── Form submit ────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('linktreeForm');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = document.getElementById('submitBtn');
        const origHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>Memproses…</span>';

        const fd   = new FormData(form);
        const data = Object.fromEntries(fd.entries());

        try {
            const res    = await fetch(form.dataset.storeUrl, {
                method:  'POST',
                headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': csrf() },
                body:    JSON.stringify(data),
            });

            const result = await res.json();

            if (!result.success) throw new Error(result.message || 'Gagal menyimpan.');

            if (!result.payment_needed) {
                ltToast('Perubahan berhasil disimpan! ⚡');
                setTimeout(() => location.reload(), 1500);
                return;
            }

            if (result.method === 'midtrans') {
                closeAllModals();
                // Open Midtrans Snap
                window.snap.pay(result.snap_token, {
                    onSuccess: () => { ltToast('Pembayaran berhasil! Linktree aktif.'); setTimeout(() => location.reload(), 2000); },
                    onPending: () => { ltToast('Menunggu konfirmasi pembayaran…'); },
                    onError:   () => { ltToast('Pembayaran gagal. Coba lagi.', 'error'); },
                    onClose:   () => { ltToast('Pembayaran dibatalkan.', 'error'); },
                });
            } else if (result.method === 'whatsapp') {
                closeAllModals();
                ltToast('Mengarahkan ke WhatsApp…');
                setTimeout(() => window.open(result.payment_url, '_blank'), 800);
            }

        } catch (err) {
            console.error(err);
            ltToast(err.message || 'Terjadi kesalahan.', 'error');
        } finally {
            btn.disabled  = false;
            btn.innerHTML = origHTML;
        }
    });

    // Default template selection on load
    selectTemplate('dark');
});

/* ── Toast ──────────────────────────────────────────────── */
window.ltToast = function (msg, type = 'success') {
    const toast = document.getElementById('lt-toast');
    const msgEl = document.getElementById('lt-toast-msg');
    const icon  = document.getElementById('lt-toast-icon');
    if (!toast) return;

    msgEl.textContent = msg;

    if (type === 'error') {
        toast.style.background = '#ef4444';
        toast.style.color      = '#ffffff';
        icon.innerHTML         = '<i class="fa-solid fa-triangle-exclamation"></i>';
    } else {
        toast.style.background = '#a3e635';
        toast.style.color      = '#040f0f';
        icon.innerHTML         = '<i class="fa-solid fa-check"></i>';
    }

    toast.classList.add('show');
    clearTimeout(toast._t);
    toast._t = setTimeout(() => toast.classList.remove('show'), 4000);
};

/* ── Helpers ────────────────────────────────────────────── */
function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function setVal(id, val) {
    const el = document.getElementById(id);
    if (el && val !== undefined && val !== null) el.value = val;
}