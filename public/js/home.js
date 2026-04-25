/* ── Category filter ── */
function filterTools(cat) {
    document.querySelectorAll('.cat-tab').forEach(function(tab) {
        tab.classList.toggle('active', tab.dataset.cat === cat);
        tab.setAttribute('aria-selected', tab.dataset.cat === cat ? 'true' : 'false');
    });
    document.querySelectorAll('#toolsGrid .tool-card').forEach(function(card) {
        var match = cat === 'all' || card.dataset.cat === cat;
        card.style.display = match ? '' : 'none';
    });
}

/* ── Contact form (Auth only — rendered by Blade @auth) ── */
(function () {
    var form    = document.getElementById('contactForm');
    if (!form) return; /* guest — form not rendered */

    var btn     = document.getElementById('contactSubmitBtn');
    var btnText = document.getElementById('contactBtnText');
    var btnIcon = document.getElementById('contactBtnIcon');
    var alert   = document.getElementById('contactAlert');

    function showAlert(type, msg) {
        var isOk = type === 'success';
        alert.style.display      = 'block';
        alert.style.background   = isOk ? 'rgba(34,197,94,0.08)'  : 'rgba(239,68,68,0.08)';
        alert.style.border       = '1px solid ' + (isOk ? 'rgba(34,197,94,0.2)' : 'rgba(239,68,68,0.2)');
        alert.style.color        = isOk ? '#86efac' : '#fca5a5';
        alert.innerHTML          = (isOk ? '✓ ' : '⚠ ') + msg;
        /* auto-hide success after 6s */
        if (isOk) setTimeout(function () { alert.style.display = 'none'; }, 6000);
    }

    function setLoading(loading) {
        btn.disabled          = loading;
        btn.style.opacity     = loading ? '0.7' : '1';
        btn.style.cursor      = loading ? 'wait' : 'pointer';
        btnIcon.className     = loading
            ? 'fa-solid fa-circle-notch fa-spin'
            : 'fa-solid fa-paper-plane';
        btnText.textContent   = loading ? 'Mengirim...' : 'Kirim Pesan';
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        /* Basic client-side check */
        var subject = form.querySelector('[name="subject"]').value;
        if (!subject) {
            showAlert('error', 'Pilih topik pesan terlebih dahulu.');
            return;
        }
        var message = form.querySelector('[name="message"]').value.trim();
        if (message.length < 10) {
            showAlert('error', 'Pesan minimal 10 karakter.');
            return;
        }

        setLoading(true);
        alert.style.display = 'none';

        var payload = {
            name:    form.querySelector('[name="name"]').value.trim(),
            email:   form.querySelector('[name="email"]').value.trim(),
            subject: subject,
            message: message,
        };

        fetch('{{ route("contact.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(payload),
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            setLoading(false);
            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            }
            if (data.success) {
                showAlert('success', data.message);
                form.querySelector('[name="subject"]').value  = '';
                form.querySelector('[name="message"]').value = '';
            } else {
                showAlert('error', data.message || 'Terjadi kesalahan. Coba lagi.');
            }
        })
        .catch(function () {
            setLoading(false);
            showAlert('error', 'Koneksi bermasalah. Silakan coba lagi.');
        });
    });
})();