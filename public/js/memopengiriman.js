/* ================= Preview logo sebelum upload ================= */
    var logoInput = document.getElementById('logoInput');
    if (logoInput) {
        logoInput.addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                var img = document.getElementById('logoPreviewImg');
                var placeholder = document.getElementById('logoPreviewPlaceholder');
                img.src = e.target.result;
                img.style.display = 'block';
                if (placeholder) placeholder.style.display = 'none';
            };
            reader.readAsDataURL(file);
        });
    }

    /* ================= Repeater No Struk & No Struk Instalasi ================= */
    function bindRepeater(containerId, addBtnId, inputName, initialValues) {
        var container = document.getElementById(containerId);
        var addBtn = document.getElementById(addBtnId);

        function addRow(value) {
            var row = document.createElement('div');
            row.className = 'memo-repeater-row';
            row.style.display = 'flex';
            row.style.gap = '6px';
            row.style.marginBottom = '6px';

            var input = document.createElement('input');
            input.type = 'text';
            input.name = inputName + '[]';
            input.className = 'memo-input';
            input.value = value || '';
            input.style.flex = '1';

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'memo-btn memo-btn-warning memo-btn-sm';
            removeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
            removeBtn.addEventListener('click', function () {
                var rows = container.querySelectorAll('.memo-repeater-row');
                if (rows.length > 1) {
                    row.remove();
                } else {
                    input.value = '';
                }
            });

            row.appendChild(input);
            row.appendChild(removeBtn);
            container.appendChild(row);
        }

        var values = (initialValues && initialValues.length) ? initialValues : [''];
        values.forEach(function (v) { addRow(v); });

        addBtn.addEventListener('click', function () { addRow(''); });
    }

    /* ================= Repeater Barang (Nama + Qty) ================= */
    function bindBarangRepeater(containerId, addBtnId, initialItems) {
        var container = document.getElementById(containerId);
        var addBtn = document.getElementById(addBtnId);

        function addRow(nama, qty) {
            var row = document.createElement('div');
            row.className = 'memo-repeater-row';
            row.style.display = 'flex';
            row.style.gap = '6px';
            row.style.marginBottom = '6px';

            var namaInput = document.createElement('input');
            namaInput.type = 'text';
            namaInput.name = 'barang_nama[]';
            namaInput.className = 'memo-input';
            namaInput.placeholder = 'Nama barang, contoh: AC Split 1PK';
            namaInput.value = nama || '';
            namaInput.style.flex = '2';

            var qtyInput = document.createElement('input');
            qtyInput.type = 'number';
            qtyInput.name = 'barang_qty[]';
            qtyInput.className = 'memo-input';
            qtyInput.placeholder = 'Qty';
            qtyInput.min = '1';
            qtyInput.value = qty || '1';
            qtyInput.style.flex = '0 0 90px';

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'memo-btn memo-btn-warning memo-btn-sm';
            removeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
            removeBtn.addEventListener('click', function () {
                var rows = container.querySelectorAll('.memo-repeater-row');
                if (rows.length > 1) {
                    row.remove();
                } else {
                    namaInput.value = '';
                    qtyInput.value = '1';
                }
            });

            row.appendChild(namaInput);
            row.appendChild(qtyInput);
            row.appendChild(removeBtn);
            container.appendChild(row);
        }

        var items = (initialItems && initialItems.length) ? initialItems : [{ nama: '', qty: 1 }];
        items.forEach(function (item) { addRow(item.nama, item.qty); });

        addBtn.addEventListener('click', function () { addRow('', 1); });
    }

    /* ================= Batasi input No Telepon hanya angka/+/-/spasi ================= */
    document.querySelectorAll('.only-phone').forEach(function (el) {
        el.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9+\-\s]/g, '');
        });
    });

    /* ================= Format Rupiah live (Biaya Kirim & Biaya Instalasi) ================= */
    function bindRupiah(displayId, hiddenId, initialValue) {
        var display = document.getElementById(displayId);
        var hidden  = document.getElementById(hiddenId);

        function setDisplay(raw) {
            display.value = raw ? 'Rp ' + Number(raw).toLocaleString('id-ID') : '';
        }

        setDisplay(initialValue || '');
        hidden.value = initialValue || '';

        display.addEventListener('input', function () {
            var angka = this.value.replace(/\D/g, '');
            hidden.value = angka;
            setDisplay(angka);
        });
    }

    /* ================= Datetime picker -> format "Minggu, 05-Juli-2026 16:42" ================= */
    var HARI  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    var BULAN = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

    function pad(n) { return String(n).padStart(2, '0'); }

    function formatIndo(d) {
        return HARI[d.getDay()] + ', ' + pad(d.getDate()) + '-' + BULAN[d.getMonth()] + '-' + d.getFullYear() + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function nowLocalValue() {
        var d = new Date();
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function bindTanggalIndo(pickerId, hiddenId, initialFormatted) {
        var picker = document.getElementById(pickerId);
        var hidden = document.getElementById(hiddenId);

        if (!picker.value) picker.value = nowLocalValue();

        hidden.value = initialFormatted ? initialFormatted : formatIndo(new Date(picker.value));

        picker.addEventListener('input', function () {
            if (this.value) hidden.value = formatIndo(new Date(this.value));
        });
    }

    /* ================= Toggle field instalasi (tampil HANYA jika "Ya") ================= */
    var instalasiSelect = document.getElementById('instalasi_select');
    var instalasiFields = document.getElementById('instalasi_extra_fields');

    function toggleInstalasi() {
        var ya = instalasiSelect.value === '1';
        instalasiFields.style.display = ya ? 'block' : 'none';
    }
    instalasiSelect.addEventListener('change', toggleInstalasi);
    toggleInstalasi();

    /* ================= Checkbox bulk select (cetak/hapus/export/status/batal) ================= */
    var checkAll   = document.getElementById('checkAll');
    var countEl    = document.getElementById('selectedCount');
    var btnCetak   = document.getElementById('btnCetakTerpilih');
    var btnHapus   = document.getElementById('btnHapusTerpilih');
    var btnTerkirim = document.getElementById('btnTandaiTerkirim');
    var btnPending  = document.getElementById('btnTandaiPending');
    var btnBatal   = document.getElementById('btnBatalPilih');
    var btnExport  = document.getElementById('btnExportExcel');
    var bulkBar    = document.getElementById('bulkBar');

    function rowChecks() { return document.querySelectorAll('.row-check'); }

    function refresh() {
        var checked = document.querySelectorAll('.row-check:checked');
        var total = rowChecks().length;

        countEl.textContent = checked.length;
        var has = checked.length > 0;
        btnCetak.disabled = !has;
        btnHapus.disabled = !has;
        btnTerkirim.disabled = !has;
        btnPending.disabled = !has;
        bulkBar.style.display = has ? 'flex' : 'none';

        if (checkAll) checkAll.checked = total > 0 && checked.length === total;
    }

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            rowChecks().forEach(function (cb) { cb.checked = checkAll.checked; });
            refresh();
        });
    }

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('row-check')) refresh();
    });

    function buildHiddenIds(form) {
        form.querySelectorAll('input[name="ids[]"]').forEach(function (el) { el.remove(); });
        document.querySelectorAll('.row-check:checked').forEach(function (cb) {
            var inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'ids[]';
            inp.value = cb.value;
            form.appendChild(inp);
        });
    }

    btnCetak.addEventListener('click', function () {
        var form = document.getElementById('bulkPrintForm');
        buildHiddenIds(form);
        form.submit();
    });

    btnHapus.addEventListener('click', function () {
        var checked = document.querySelectorAll('.row-check:checked');
        if (checked.length === 0) return;
        if (!confirm('Hapus ' + checked.length + ' memo terpilih? Tindakan ini tidak bisa dibatalkan.')) return;

        var form = document.getElementById('bulkDeleteForm');
        buildHiddenIds(form);
        form.submit();
    });

    function submitBulkStatus(statusValue, label) {
        var checked = document.querySelectorAll('.row-check:checked');
        if (checked.length === 0) return;
        if (!confirm('Tandai ' + checked.length + ' memo terpilih sebagai "' + label + '"?')) return;

        var form = document.getElementById('bulkStatusForm');
        document.getElementById('bulkStatusValue').value = statusValue;
        buildHiddenIds(form);
        form.submit();
    }

    btnTerkirim.addEventListener('click', function () {
        submitBulkStatus('1', 'Terkirim');
    });

    btnPending.addEventListener('click', function () {
        submitBulkStatus('0', 'Pending');
    });

    btnBatal.addEventListener('click', function () {
        rowChecks().forEach(function (cb) { cb.checked = false; });
        refresh();
    });

    /* Export selalu bisa diklik (baik ada seleksi maupun tidak) */
    btnExport.addEventListener('click', function () {
        var form = document.getElementById('exportExcelForm');
        buildHiddenIds(form); // kalau ada yang dicentang, ids[] ikut terkirim; kalau tidak, kosong -> fallback ke filter tanggal
        form.submit();
    });

    /* ================= Aksi Kirim (PDF via Email) — validasi ringan sebelum submit ================= */
    document.querySelectorAll('.kirim-email-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var email = form.getAttribute('data-email');
            var nomor = form.getAttribute('data-nomor');

            if (!email) {
                e.preventDefault();
                alert('Email tujuan untuk memo ' + nomor + ' belum diisi.\n\nSilakan klik "Edit" pada memo ini dan isi kolom "Email Tujuan" terlebih dahulu sebelum mengirim.');
                return;
            }

            if (!confirm('Kirim cetakan PDF memo ' + nomor + ' ke ' + email + '?')) {
                e.preventDefault();
            }
        });
    });

    refresh();