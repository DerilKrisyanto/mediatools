/* ── Category filter ── */
function filterTools(cat) {
    document.querySelectorAll('.cat-tab').forEach(function(tab) {
        tab.classList.toggle('active', tab.dataset.cat === cat);
        tab.setAttribute('aria-selected', tab.dataset.cat === cat ? 'true' : 'false');
    });
    document.querySelectorAll('#toolsGrid .tool-card').forEach(function(card) {
        card.style.display = (cat === 'all' || card.dataset.cat === cat) ? '' : 'none';
    });
}

/* ================================================================
   CONTACT FORM — AJAX submit
   FIX: form sekarang punya action+method sebagai fallback,
   JS mencegat submit event sebelum browser kirim request.
================================================================ */
document.addEventListener('DOMContentLoaded', function () {

    var form    = document.getElementById('contactForm');
    if (!form) return; /* guest — form tidak dirender */

    var btn     = document.getElementById('contactSubmitBtn');
    var btnText = document.getElementById('contactBtnText');
    var btnIcon = document.getElementById('contactBtnIcon');
    var alertEl = document.getElementById('contactAlert');
    var csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    function showAlert(type, msg) {
        var ok = (type === 'success');
        alertEl.style.display    = 'block';
        alertEl.style.background = ok ? 'rgba(34,197,94,0.08)'  : 'rgba(239,68,68,0.08)';
        alertEl.style.border     = '1px solid ' + (ok ? 'rgba(34,197,94,0.2)' : 'rgba(239,68,68,0.2)');
        alertEl.style.color      = ok ? '#86efac' : '#fca5a5';
        alertEl.textContent      = (ok ? '✓  ' : '⚠  ') + msg;
        alertEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        if (ok) setTimeout(function () { alertEl.style.display = 'none'; }, 7000);
    }

    function setLoading(on) {
        btn.disabled        = on;
        btn.style.opacity   = on ? '0.7' : '1';
        btn.style.cursor    = on ? 'wait' : 'pointer';
        btnIcon.className   = on ? 'fa-solid fa-circle-notch fa-spin' : 'fa-solid fa-paper-plane';
        btnIcon.style.fontSize = '12px';
        btnText.textContent = on ? 'Mengirim...' : 'Kirim Pesan';
    }

    form.addEventListener('submit', function (e) {
        /* Cegah browser kirim form secara normal */
        e.preventDefault();
        e.stopPropagation();

        alertEl.style.display = 'none';

        var name    = form.querySelector('[name="name"]').value.trim();
        var email   = form.querySelector('[name="email"]').value.trim();
        var subject = form.querySelector('[name="subject"]').value;
        var message = form.querySelector('[name="message"]').value.trim();

        /* Validasi sisi klien */
        if (!name)              { showAlert('error', 'Nama lengkap wajib diisi.');       return; }
        if (!email)             { showAlert('error', 'Email wajib diisi.');               return; }
        if (!subject)           { showAlert('error', 'Pilih topik pesan terlebih dahulu.'); return; }
        if (message.length < 10){ showAlert('error', 'Pesan minimal 10 karakter.');      return; }

        setLoading(true);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ name: name, email: email, subject: subject, message: message }),
        })
        .then(function (res) {
            /* Tangkap 422 (validation) dan 500 juga sebagai JSON */
            return res.json().then(function (data) {
                return { status: res.status, data: data };
            });
        })
        .then(function (res) {
            setLoading(false);
            if (res.data.redirect) {
                window.location.href = res.data.redirect;
                return;
            }
            if (res.data.success) {
                showAlert('success', res.data.message);
                form.querySelector('[name="subject"]').value  = '';
                form.querySelector('[name="message"]').value = '';
            } else {
                /* Tampilkan pesan error dari server */
                var msg = res.data.message || 'Terjadi kesalahan. Silakan coba lagi.';
                if (res.data.debug) msg += ' (' + res.data.debug + ')';
                showAlert('error', msg);
            }
        })
        .catch(function (err) {
            setLoading(false);
            showAlert('error', 'Koneksi bermasalah. Silakan coba lagi.');
            console.error('[Contact]', err);
        });
    });
});