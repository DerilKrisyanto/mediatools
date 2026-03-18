/* ============================================================
   MediaTools — Email Signature Studio
   signature.js
   ============================================================ */

'use strict';

/* ── State ──────────────────────────────────────────────── */
let currentTemplate  = 1;
let currentAccent    = '#a3e635';
let currentAvatarB64 = '';

/* ── Field Map ──────────────────────────────────────────── */
const FIELD_MAP = {
    name:      'prev-name',
    job_title: 'prev-job',
    company:   'prev-company',
    email:     'prev-email',
    phone:     'prev-phone',
    website:   'prev-web',
    address:   'prev-address',
    linkedin:  'prev-linkedin',
};

/* ── Accent Colors ──────────────────────────────────────── */
const ACCENT_COLORS = [
    { hex: '#a3e635', name: 'MediaTools Hijau' },
    { hex: '#3b82f6', name: 'Biru Profesional'  },
    { hex: '#8b5cf6', name: 'Ungu Elegan'       },
    { hex: '#f59e0b', name: 'Amber Hangat'      },
    { hex: '#ef4444', name: 'Merah Berani'      },
    { hex: '#0ea5e9', name: 'Biru Langit'       },
    { hex: '#14b8a6', name: 'Teal Modern'       },
    { hex: '#1e293b', name: 'Slate Gelap'       },
];

/* ─────────────────────────────────────────────────────────
   TEMPLATE DEFINITIONS
   All styles are inline so signatures work in email clients.
───────────────────────────────────────────────────────── */

function getTemplateHTML(data) {
    const { name, job, company, email, phone, website, address, linkedin, avatar, accent } = data;

    // Darken accent 20% for text on white
    const accentDark = shadeHex(accent, -25);

    switch (currentTemplate) {

        /* ══════════════════════════════════════════════════
           TEMPLATE 1: KLASIK
           Horizontal | Avatar left | Bold vertical divider
        ════════════════════════════════════════════════════ */
        case 1: return `
<table cellpadding="0" cellspacing="0" border="0"
       style="font-family:'Plus Jakarta Sans',Helvetica,Arial,sans-serif;width:100%;min-width:460px;max-width:560px;">
  <tr>
    <!-- Avatar column -->
    <td style="vertical-align:top;padding:28px 24px 28px 28px;">
      <img id="prev-avatar" src="${avatar}" width="80" height="80"
           style="border-radius:16px;object-fit:cover;display:block;border:2px solid ${accent}20;">
    </td>

    <!-- Divider -->
    <td style="vertical-align:stretch;padding:24px 0;">
      <div style="width:3px;height:100%;background:${accent};border-radius:99px;min-height:80px;"></div>
    </td>

    <!-- Info column -->
    <td style="vertical-align:middle;padding:28px 28px 28px 24px;">
      <div id="prev-name"
           style="margin:0;font-size:20px;font-weight:900;color:#0f172a;line-height:1.1;letter-spacing:-0.03em;">${name}</div>
      <div id="prev-job"
           style="margin:5px 0 2px;font-size:13px;color:#64748b;font-weight:600;">${job}</div>
      <div id="prev-company"
           style="margin:0 0 16px;font-size:9px;font-weight:900;color:${accent};text-transform:uppercase;letter-spacing:0.14em;">${company}</div>

      <!-- Contact rows -->
      <table cellpadding="0" cellspacing="0" border="0">
        ${email ? `<tr><td style="padding:2px 0;font-size:11px;color:#475569;white-space:nowrap;">
          <span style="color:${accent};font-weight:800;margin-right:10px;font-size:9px;text-transform:uppercase;">✉</span>
          <a id="prev-email" href="mailto:${email}" style="color:#0f172a;text-decoration:none;font-weight:600;">${email}</a>
        </td></tr>` : ''}
        ${phone ? `<tr><td style="padding:2px 0;font-size:11px;color:#475569;white-space:nowrap;">
          <span style="color:${accent};font-weight:800;margin-right:10px;font-size:9px;text-transform:uppercase;">☎</span>
          <span id="prev-phone" style="color:#0f172a;font-weight:600;">${phone}</span>
        </td></tr>` : ''}
        ${website ? `<tr><td style="padding:2px 0;font-size:11px;color:#475569;white-space:nowrap;">
          <span style="color:${accent};font-weight:800;margin-right:10px;font-size:9px;text-transform:uppercase;">⌘</span>
          <a id="prev-web" href="https://${website.replace(/^https?:\/\//,'')}" style="color:#0f172a;text-decoration:none;font-weight:600;">${website}</a>
        </td></tr>` : ''}
        ${linkedin ? `<tr><td style="padding:2px 0;font-size:11px;color:#475569;white-space:nowrap;">
          <span style="color:${accent};font-weight:800;margin-right:10px;font-size:9px;text-transform:uppercase;">in</span>
          <a id="prev-linkedin" href="https://linkedin.com/in/${linkedin.replace(/.*linkedin\.com\/in\//,'')}" style="color:#0f172a;text-decoration:none;font-weight:600;">${linkedin}</a>
        </td></tr>` : ''}
        ${address ? `<tr><td style="padding:4px 0 0;font-size:10px;color:#94a3b8;font-style:italic;">
          <span id="prev-address">${address}</span>
        </td></tr>` : ''}
      </table>
    </td>
  </tr>
</table>`;


        /* ══════════════════════════════════════════════════
           TEMPLATE 2: MODERN
           Full-width accent header bar | Avatar circle | Icon badges
        ════════════════════════════════════════════════════ */
        case 2: return `
<table cellpadding="0" cellspacing="0" border="0"
       style="font-family:'Plus Jakarta Sans',Helvetica,Arial,sans-serif;width:100%;min-width:460px;max-width:560px;border-radius:12px;overflow:hidden;">
  <!-- Header accent bar -->
  <tr>
    <td colspan="2" style="background:${accent};padding:10px 24px;line-height:1;">
      <span style="font-size:9px;font-weight:900;text-transform:uppercase;letter-spacing:0.2em;color:${accentDark};">
        ${company || 'YOUR COMPANY'}
      </span>
    </td>
  </tr>

  <!-- Main content row -->
  <tr>
    <!-- Avatar -->
    <td style="background:#ffffff;vertical-align:top;padding:20px 16px 20px 24px;width:88px;">
      <img id="prev-avatar" src="${avatar}" width="72" height="72"
           style="border-radius:50%;object-fit:cover;display:block;border:3px solid ${accent};">
    </td>

    <!-- Info -->
    <td style="background:#ffffff;vertical-align:middle;padding:20px 24px 20px 8px;">
      <div id="prev-name"
           style="font-size:18px;font-weight:900;color:#0f172a;letter-spacing:-0.02em;line-height:1.1;">${name}</div>
      <div style="display:flex;align-items:center;gap:8px;margin:4px 0 14px;">
        <span id="prev-job" style="font-size:12px;color:#64748b;font-weight:600;">${job}</span>
      </div>

      <!-- Icon badge row -->
      <table cellpadding="0" cellspacing="0" border="0">
        <tr>
          ${email ? `<td style="padding-right:8px;">
            <a href="mailto:${email}" title="${email}"
               style="display:inline-flex;align-items:center;gap:5px;padding:5px 10px;background:${accent}15;border-radius:8px;text-decoration:none;">
              <span style="font-size:10px;color:${accent};">✉</span>
              <span id="prev-email" style="font-size:10px;font-weight:700;color:#334155;">${email}</span>
            </a>
          </td>` : ''}
        </tr>
        <tr><td style="height:6px;"></td></tr>
        <tr>
          ${phone ? `<td style="padding-right:8px;">
            <span style="display:inline-flex;align-items:center;gap:5px;padding:5px 10px;background:#f1f5f9;border-radius:8px;">
              <span style="font-size:10px;color:${accent};">☎</span>
              <span id="prev-phone" style="font-size:10px;font-weight:700;color:#334155;">${phone}</span>
            </span>
          </td>` : ''}
          ${website ? `<td>
            <a href="https://${website.replace(/^https?:\/\//,'')}"
               style="display:inline-flex;align-items:center;gap:5px;padding:5px 10px;background:#f1f5f9;border-radius:8px;text-decoration:none;">
              <span style="font-size:10px;color:${accent};">⌘</span>
              <span id="prev-web" style="font-size:10px;font-weight:700;color:#334155;">${website}</span>
            </a>
          </td>` : ''}
        </tr>
        ${(linkedin || address) ? `<tr><td style="height:6px;"></td></tr>
        <tr>
          ${linkedin ? `<td colspan="2">
            <a href="https://linkedin.com/in/${linkedin.replace(/.*linkedin\.com\/in\//,'')}"
               style="display:inline-flex;align-items:center;gap:5px;padding:5px 10px;background:#eff6ff;border-radius:8px;text-decoration:none;">
              <span style="font-size:10px;font-weight:900;color:#3b82f6;">in</span>
              <span id="prev-linkedin" style="font-size:10px;font-weight:700;color:#334155;">${linkedin}</span>
            </a>
          </td>` : ''}
        </tr>` : ''}
        ${address ? `<tr><td colspan="2" style="padding-top:8px;">
          <span id="prev-address" style="font-size:10px;color:#94a3b8;font-style:italic;">${address}</span>
        </td></tr>` : ''}
      </table>
    </td>
  </tr>

  <!-- Footer accent strip -->
  <tr>
    <td colspan="2" style="background:${accent}18;padding:8px 24px;border-top:1px solid ${accent}25;">
      <span id="prev-company"
            style="font-size:9px;color:${accentDark};font-weight:800;text-transform:uppercase;letter-spacing:0.12em;"></span>
    </td>
  </tr>
</table>`;


        /* ══════════════════════════════════════════════════
           TEMPLATE 3: ELEGAN
           Centered vertical | Circular avatar | Minimal icons
        ════════════════════════════════════════════════════ */
        case 3: return `
<table cellpadding="0" cellspacing="0" border="0"
       style="font-family:'Plus Jakarta Sans',Helvetica,Arial,sans-serif;width:100%;min-width:420px;max-width:520px;">
  <tr>
    <td style="text-align:center;padding:28px 32px 24px;background:#ffffff;">

      <!-- Avatar -->
      <div style="margin-bottom:12px;">
        <img id="prev-avatar" src="${avatar}" width="76" height="76"
             style="border-radius:50%;object-fit:cover;border:3px solid ${accent};box-shadow:0 0 0 5px ${accent}20;display:inline-block;">
      </div>

      <!-- Name & Title -->
      <div id="prev-name"
           style="font-size:22px;font-weight:900;color:#0f172a;letter-spacing:-0.03em;line-height:1.1;">${name}</div>
      <div id="prev-job"
           style="font-size:12px;color:#64748b;font-weight:600;margin:4px 0 2px;">${job}</div>
      <div id="prev-company"
           style="font-size:9px;font-weight:900;text-transform:uppercase;letter-spacing:0.16em;color:${accent};margin-bottom:18px;">${company}</div>

      <!-- Divider with accent dots -->
      <div style="display:flex;align-items:center;justify-content:center;gap:6px;margin-bottom:18px;">
        <div style="width:28px;height:1px;background:${accent}40;"></div>
        <div style="width:5px;height:5px;border-radius:50%;background:${accent};"></div>
        <div style="width:28px;height:1px;background:${accent}40;"></div>
      </div>

      <!-- Contact grid -->
      <table cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
        ${email ? `<tr>
          <td style="padding:3px 8px;text-align:right;">
            <span style="font-size:9px;font-weight:900;color:${accent};text-transform:uppercase;">Email</span>
          </td>
          <td style="padding:3px 0 3px 8px;border-left:1px solid ${accent}30;text-align:left;">
            <a id="prev-email" href="mailto:${email}" style="font-size:11px;color:#0f172a;text-decoration:none;font-weight:600;">${email}</a>
          </td>
        </tr>` : ''}
        ${phone ? `<tr>
          <td style="padding:3px 8px;text-align:right;">
            <span style="font-size:9px;font-weight:900;color:${accent};text-transform:uppercase;">Telp</span>
          </td>
          <td style="padding:3px 0 3px 8px;border-left:1px solid ${accent}30;text-align:left;">
            <span id="prev-phone" style="font-size:11px;color:#0f172a;font-weight:600;">${phone}</span>
          </td>
        </tr>` : ''}
        ${website ? `<tr>
          <td style="padding:3px 8px;text-align:right;">
            <span style="font-size:9px;font-weight:900;color:${accent};text-transform:uppercase;">Web</span>
          </td>
          <td style="padding:3px 0 3px 8px;border-left:1px solid ${accent}30;text-align:left;">
            <a id="prev-web" href="https://${website.replace(/^https?:\/\//,'')}" style="font-size:11px;color:#0f172a;text-decoration:none;font-weight:600;">${website}</a>
          </td>
        </tr>` : ''}
        ${linkedin ? `<tr>
          <td style="padding:3px 8px;text-align:right;">
            <span style="font-size:9px;font-weight:900;color:${accent};text-transform:uppercase;">LinkedIn</span>
          </td>
          <td style="padding:3px 0 3px 8px;border-left:1px solid ${accent}30;text-align:left;">
            <a id="prev-linkedin" href="https://linkedin.com/in/${linkedin.replace(/.*linkedin\.com\/in\//,'')}" style="font-size:11px;color:#0f172a;text-decoration:none;font-weight:600;">${linkedin}</a>
          </td>
        </tr>` : ''}
        ${address ? `<tr>
          <td colspan="2" style="padding:8px 8px 0;text-align:center;">
            <span id="prev-address" style="font-size:10px;color:#94a3b8;font-style:italic;">${address}</span>
          </td>
        </tr>` : ''}
      </table>

      <!-- Bottom accent line -->
      <div style="margin-top:18px;height:3px;background:linear-gradient(90deg,transparent,${accent},transparent);border-radius:99px;"></div>
    </td>
  </tr>
</table>`;

        default: return '';
    }
}

/* ── Helper: Darken hex color ───────────────────────────── */
function shadeHex(hex, percent) {
    let r = parseInt(hex.slice(1,3),16);
    let g = parseInt(hex.slice(3,5),16);
    let b = parseInt(hex.slice(5,7),16);
    r = Math.max(0, Math.min(255, r + percent));
    g = Math.max(0, Math.min(255, g + percent));
    b = Math.max(0, Math.min(255, b + percent));
    return '#' + [r,g,b].map(v => v.toString(16).padStart(2,'0')).join('');
}

/* ── Collect current form data ──────────────────────────── */
function getFormData() {
    const v = id => document.getElementById(id)?.value?.trim() || '';
    const defaultAvatar = () => {
        const name = v('name') || 'User';
        return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=${currentAccent.slice(1)}&color=0f172a&bold=true&size=128`;
    };

    return {
        name:    v('name')      || 'Nama Lengkap',
        job:     v('job_title') || 'Jabatan Profesional',
        company: v('company')   || 'Nama Perusahaan',
        email:   v('email'),
        phone:   v('phone'),
        website: v('website'),
        address: v('address'),
        linkedin:v('linkedin'),
        avatar:  currentAvatarB64 || document.getElementById('avatar-preview')?.src || defaultAvatar(),
        accent:  currentAccent,
    };
}

/* ── Re-render preview ──────────────────────────────────── */
function renderPreview() {
    const box = document.getElementById('signature-content');
    if (!box) return;

    box.innerHTML = getTemplateHTML(getFormData());
}

/* ── Template switch ────────────────────────────────────── */
window.setTemplate = function(n) {
    currentTemplate = n;
    document.querySelectorAll('.sig-tpl-btn').forEach(btn => {
        btn.classList.toggle('active', parseInt(btn.dataset.tpl) === n);
    });
    renderPreview();
};

/* ── Accent color switch ─────────────────────────────────── */
window.setAccent = function(hex) {
    currentAccent = hex;
    document.querySelectorAll('.sig-color-swatch').forEach(s => {
        s.classList.toggle('selected', s.dataset.color === hex);
    });
    renderPreview();
};

/* ── Build color swatches ───────────────────────────────── */
function buildColorSwatches() {
    const wrap = document.getElementById('sig-colors');
    if (!wrap) return;

    ACCENT_COLORS.forEach(c => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'sig-color-swatch' + (c.hex === currentAccent ? ' selected' : '');
        btn.dataset.color = c.hex;
        btn.style.background = c.hex;
        btn.title = c.name;
        btn.onclick = () => setAccent(c.hex);
        wrap.appendChild(btn);
    });
}

/* ── Avatar upload ──────────────────────────────────────── */
function initAvatarUpload() {
    const input   = document.getElementById('avatarInput');
    const preview = document.getElementById('avatar-preview');
    if (!input || !preview) return;

    input.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            showToast('File harus berupa gambar.', 'error'); return;
        }
        if (file.size > 2 * 1024 * 1024) {
            showToast('Ukuran gambar maksimal 2MB.', 'error'); return;
        }

        const reader = new FileReader();
        reader.onload = e => {
            currentAvatarB64 = e.target.result;
            preview.src      = e.target.result;

            const avatarEl = document.getElementById('prev-avatar');
            if (avatarEl) avatarEl.src = e.target.result;

            renderPreview();
            showToast('Foto berhasil dimuat!');
        };
        reader.readAsDataURL(file);
    });
}

/* ── Live input → preview ───────────────────────────────── */
function initLiveInputs() {
    Object.keys(FIELD_MAP).forEach(fieldId => {
        const input = document.getElementById(fieldId);
        if (!input) return;
        input.addEventListener('input', debounce(renderPreview, 120));
    });
}

/* ── Debounce helper ────────────────────────────────────── */
function debounce(fn, ms) {
    let t;
    return function(...args) { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), ms); };
}

/* ── Copy HTML to clipboard ─────────────────────────────── */
window.copyHTML = async function() {
    const box = document.getElementById('signature-content');
    if (!box) return;

    const html = box.innerHTML.trim();
    try {
        await navigator.clipboard.writeText(html);
        showToast('HTML tersalin! Tempel di Gmail/Outlook. ⚡');
    } catch {
        // Fallback
        const ta = document.createElement('textarea');
        ta.value = html;
        ta.style.position = 'fixed'; ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        showToast('HTML tersalin!');
    }
};

/* ── Download PNG ───────────────────────────────────────── */
window.downloadSignature = async function() {
    const box = document.getElementById('signature-content');
    if (!box) { showToast('Preview tidak ditemukan.', 'error'); return; }

    showToast('Sedang merender gambar…');

    try {
        const canvas = await html2canvas(box, {
            scale:           3,
            backgroundColor: '#ffffff',
            useCORS:         true,
            logging:         false,
        });

        const name  = (document.getElementById('name')?.value || 'signature')
                        .replace(/\s+/g, '-').toLowerCase();
        const tpl   = ['klasik','modern','elegan'][currentTemplate - 1];
        const link  = document.createElement('a');
        link.href     = canvas.toDataURL('image/png');
        link.download = `${name}-signature-${tpl}.png`;
        link.click();

        showToast('PNG berhasil diunduh!');
    } catch (err) {
        console.error(err);
        showToast('Gagal mengekspor PNG.', 'error');
    }
};

/* ── Save signature (AJAX) ──────────────────────────────── */
window.saveSignature = async function() {
    const form = document.getElementById('signature-form');
    const btn  = document.getElementById('btn-save');
    if (!form || !btn) return;

    const origHTML = btn.innerHTML;
    btn.disabled   = true;
    btn.innerHTML  = '<i class="fa-solid fa-spinner fa-spin"></i><span>Menyimpan…</span>';

    const fd   = new FormData(form);
    const data = Object.fromEntries(fd.entries());
    data.avatar_base64 = currentAvatarB64;
    data.template      = currentTemplate;
    data.accent_color  = currentAccent;

    try {
        const res    = await fetch(form.action, {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            body: JSON.stringify(data),
        });

        const result = await res.json();

        if (res.ok) {
            showToast(result.message || 'Signature berhasil disimpan! ⚡');
        } else {
            showToast(result.message || 'Gagal menyimpan. Coba lagi.', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Kesalahan jaringan. Coba lagi.', 'error');
    } finally {
        btn.disabled  = false;
        btn.innerHTML = origHTML;
    }
};

/* ── Toast ──────────────────────────────────────────────── */
window.showToast = function(msg, type = 'success') {
    const toast  = document.getElementById('toast');
    const msgEl  = document.getElementById('toast-message');
    const iconEl = document.getElementById('toast-icon-box');
    if (!toast) return;

    msgEl.textContent = msg;

    if (type === 'error') {
        toast.style.background = '#ef4444';
        toast.style.color      = '#ffffff';
        iconEl.innerHTML       = '<i class="fa-solid fa-triangle-exclamation"></i>';
    } else {
        toast.style.background = '#a3e635';
        toast.style.color      = '#040f0f';
        iconEl.innerHTML       = '<i class="fa-solid fa-check"></i>';
    }

    toast.classList.add('show');
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => toast.classList.remove('show'), 4000);
};

/* ── Init ───────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    buildColorSwatches();
    initAvatarUpload();
    initLiveInputs();
    renderPreview();  // Initial render
});