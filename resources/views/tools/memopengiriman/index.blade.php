@extends('layouts.app')
@section('title', 'Memo Pengiriman Barang — MediaTools')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/memopengiriman.css') }}">
@endpush

@section('content')
<div class="memo-page">

    {{-- ====================== HEADER BANNER ====================== --}}
    <div class="memo-header">
        <div class="memo-wrap">
            <p class="breadcrumb">
                <a href="{{ route('home') }}">Home</a> &rsaquo; <span>Memo Pengiriman</span>
            </p>
            <h1>Memo Pengiriman Barang</h1>
            <p>Input, kelola, dan cetak memo pengiriman barang Anda. Setiap memo yang Anda simpan hanya dapat dilihat dan dikelola oleh akun Anda sendiri.</p>
        </div>
    </div>

    <div class="memo-wrap">

        {{-- ====================== FLASH MESSAGES ====================== --}}
        @if(session('success'))
            <div class="memo-alert memo-alert-success">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="memo-alert memo-alert-error">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}
            </div>
        @endif

        {{-- ====================== LOGO PERUSAHAAN (PER USER) ====================== --}}
        <div class="memo-card memo-logo-card">
            <h2>Logo Perusahaan / Toko</h2>
            <p class="memo-logo-desc">
                Logo ini akan otomatis tampil di setiap cetakan memo pengiriman Anda.
                Hanya Anda yang bisa mengganti atau menghapus logo milik akun Anda sendiri.
            </p>

            <div class="memo-logo-row">
                <div class="memo-logo-preview">
                    @if(auth()->user()->logo_path)
                        <img src="{{ auth()->user()->logo_url }}" alt="Logo saat ini" id="logoPreviewImg">
                    @else
                        <div class="memo-logo-placeholder" id="logoPreviewPlaceholder">
                            <i class="fa-solid fa-image"></i>
                            <span>Belum ada logo</span>
                        </div>
                        <img src="" alt="Preview logo" id="logoPreviewImg" style="display:none;">
                    @endif
                </div>

                <div class="memo-logo-actions">
                    <form action="{{ route('tools.memopengiriman.profile.logo.store') }}" method="POST" enctype="multipart/form-data" id="logoUploadForm">
                        @csrf
                        <label for="logoInput" class="memo-btn memo-btn-outline" style="cursor:pointer;">
                            <i class="fa-solid fa-upload"></i>
                            {{ auth()->user()->logo_path ? 'Ganti Logo' : 'Upload Logo' }}
                        </label>
                        <input type="file" name="logo" id="logoInput" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="document.getElementById('logoUploadForm').submit();">
                        @error('logo') <div class="memo-error">{{ $message }}</div> @enderror
                    </form>

                    @if(auth()->user()->logo_path)
                        <form action="{{ route('tools.memopengiriman.profile.logo.destroy') }}" method="POST" onsubmit="return confirm('Hapus logo Anda? Cetakan memo akan kembali memakai logo default.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="memo-btn memo-btn-danger">
                                <i class="fa-solid fa-trash"></i> Hapus Logo
                            </button>
                        </form>
                    @endif

                    <p class="memo-logo-hint">Format JPG/PNG/WEBP, maksimal 2MB.</p>
                </div>
            </div>
        </div>

        {{-- ====================== FORM INPUT / EDIT ====================== --}}
        <div class="memo-card">
            <h2>{{ isset($editMemo) ? 'Edit Memo Pengiriman' : 'Input Memo Pengiriman Baru' }}</h2>

            <form method="POST"
                  action="{{ isset($editMemo) ? route('tools.memopengiriman.update', $editMemo) : route('tools.memopengiriman.store') }}"
                  id="memoForm">
                @csrf
                @if(isset($editMemo)) @method('PUT') @endif

                <div class="memo-form-group">
                    <label>Telah diterima dari</label>
                    <input type="text" name="diterima_dari" class="memo-input"
                            placeholder="Nama customer / toko"
                            value="{{ old('diterima_dari', $editMemo->diterima_dari ?? '') }}" required>
                    @error('diterima_dari') <div class="memo-error">{{ $message }}</div> @enderror
                </div>

                <div class="memo-grid-2">
                    <div class="memo-form-group">
                        <label>No Struk</label>
                        <div id="noStrukRepeater" class="memo-repeater"></div>
                        <button type="button" class="memo-btn memo-btn-outline memo-btn-sm" id="btnAddNoStruk" style="margin-top:6px;">
                            <i class="fa-solid fa-plus"></i> Tambah No. Struk
                        </button>
                        @error('no_struk_items') <div class="memo-error">{{ $message }}</div> @enderror
                        @error('no_struk_items.*') <div class="memo-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="memo-form-group">
                        <label>No Telepon</label>
                        <input type="tel" inputmode="numeric" name="telepon_dari" class="memo-input only-phone"
                            placeholder="08xx-xxxx-xxxx"
                            value="{{ old('telepon_dari', $editMemo->telepon_dari ?? '') }}">
                        @error('telepon_dari') <div class="memo-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                @php
                    $barangInitial = old('barang_nama')
                        ? collect(old('barang_nama'))->map(function ($nama, $i) {
                            return ['nama' => $nama, 'qty' => old('barang_qty')[$i] ?? 1];
                        })->values()->all()
                        : (isset($editMemo) ? array_values($editMemo->berupa ?? []) : []);
                @endphp

                <div class="memo-form-group">
                    <label>Berupa (Deskripsi Barang)</label>
                    <div id="barangRepeater" class="memo-repeater"></div>
                    <button type="button" class="memo-btn memo-btn-outline memo-btn-sm" id="btnAddBarang" style="margin-top:6px;">
                        <i class="fa-solid fa-plus"></i> Tambah Barang
                    </button>
                    @error('barang_nama') <div class="memo-error">{{ $message }}</div> @enderror
                    @error('barang_nama.*') <div class="memo-error">{{ $message }}</div> @enderror
                </div>

                <div class="memo-section-label">Untuk Dikirimkan Ke</div>
                <div class="memo-grid-2">
                    <div class="memo-form-group">
                        <label>Contact Person</label>
                        <input type="text" name="tujuan_contact_person" class="memo-input"
                               placeholder="Nama penerima"
                               value="{{ old('tujuan_contact_person', $editMemo->tujuan_contact_person ?? '') }}" required>
                        @error('tujuan_contact_person') <div class="memo-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="memo-form-group">
                        <label>No Telepon</label>
                        <input type="tel" inputmode="numeric" name="tujuan_telepon" class="memo-input only-phone"
                               placeholder="08xx-xxxx-xxxx"
                               value="{{ old('tujuan_telepon', $editMemo->tujuan_telepon ?? '') }}">
                        @error('tujuan_telepon') <div class="memo-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="memo-form-group">
                    <label>Alamat</label>
                    <textarea name="tujuan_alamat" class="memo-input" rows="2"
                              placeholder="Alamat lengkap tujuan pengiriman" required>{{ old('tujuan_alamat', $editMemo->tujuan_alamat ?? '') }}</textarea>
                    @error('tujuan_alamat') <div class="memo-error">{{ $message }}</div> @enderror
                </div>

                <div class="memo-grid-2">
                    <div class="memo-form-group">
                        <label>Nama Customer Service</label>
                        <input type="text" name="customer_service" class="memo-input"
                            placeholder="Nama CS yang menangani pengiriman ini"
                            value="{{ old('customer_service', $editMemo->customer_service ?? '') }}" required>
                        @error('customer_service') <div class="memo-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="memo-form-group">
                        <label>Tanggal Memo</label>
                        <input type="date" name="tanggal_memo" class="memo-input"
                            value="{{ old('tanggal_memo', isset($editMemo) ? \Carbon\Carbon::parse($editMemo->tanggal_memo)->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                        @error('tanggal_memo') <div class="memo-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="memo-grid-2">
                    <div class="memo-form-group">
                        <label>Tanggal Pengiriman</label>
                        <input type="datetime-local" id="pengiriman_picker" class="memo-input">
                        <input type="hidden" name="pengiriman_hari_tanggal" id="pengiriman_hari_tanggal">
                        @error('pengiriman_hari_tanggal') <div class="memo-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="memo-form-group">
                        <label>Biaya Kirim (Rp)</label>
                        <input type="text" inputmode="numeric" id="biaya_kirim_display" class="memo-input" placeholder="Rp 0">
                        <input type="hidden" name="biaya_kirim" id="biaya_kirim">
                        @error('biaya_kirim') <div class="memo-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="memo-section-label">Instalasi</div>
                <div class="memo-form-group" style="max-width:280px;">
                    <label>Instalasi</label>
                    <select name="instalasi" id="instalasi_select" class="memo-input">
                        <option value="0" {{ !old('instalasi', $editMemo->instalasi ?? false) ? 'selected' : '' }}>Tidak</option>
                        <option value="1" {{ old('instalasi', $editMemo->instalasi ?? false) ? 'selected' : '' }}>Ya</option>
                    </select>
                </div>

                <div id="instalasi_extra_fields">
                    <div class="memo-grid-2">
                        <div class="memo-form-group">
                            <label>Tanggal Instalasi</label>
                            <input type="datetime-local" id="instalasi_picker" class="memo-input">
                            <input type="hidden" name="instalasi_hari_tanggal" id="instalasi_hari_tanggal">
                            @error('instalasi_hari_tanggal') <div class="memo-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="memo-form-group">
                            <label>No Struk Instalasi</label>
                            <div id="noStrukInstalasiRepeater" class="memo-repeater"></div>
                            <button type="button" class="memo-btn memo-btn-outline memo-btn-sm" id="btnAddNoStrukInstalasi" style="margin-top:6px;">
                                <i class="fa-solid fa-plus"></i> Tambah No. Struk Instalasi
                            </button>
                            @error('no_struk_instalasi_items') <div class="memo-error">{{ $message }}</div> @enderror
                            @error('no_struk_instalasi_items.*') <div class="memo-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="memo-form-group" style="max-width:280px;">
                        <label>Biaya Instalasi (Rp)</label>
                        <input type="text" inputmode="numeric" id="biaya_instalasi_display" class="memo-input" placeholder="Rp 0">
                        <input type="hidden" name="biaya_instalasi" id="biaya_instalasi">
                        @error('biaya_instalasi') <div class="memo-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div style="display:flex; gap:10px; margin-top:8px;">
                    <button type="submit" class="memo-btn memo-btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i>
                        {{ isset($editMemo) ? 'Update Memo' : 'Simpan Memo' }}
                    </button>
                    @if(isset($editMemo))
                        <a href="{{ route('tools.memopengiriman') }}" class="memo-btn memo-btn-outline">Batal Edit</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- ====================== REKAP / TABEL ====================== --}}
        <div class="memo-card">
            <h2>Laporan Memo Pengiriman</h2>

            {{-- ---------- Filter Periode Tanggal ---------- --}}
            <div class="memo-filter-row">
                <form method="GET" action="{{ route('tools.memopengiriman') }}" class="memo-filter-form">
                    <div class="memo-filter-field">
                        <label>Dari Tanggal</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="memo-input">
                    </div>
                    <div class="memo-filter-field">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="memo-input">
                    </div>
                    <button type="submit" class="memo-btn memo-btn-primary">
                        <i class="fa-solid fa-search"></i> Cari
                    </button>
                </form>

                <button type="button" id="btnExportExcel" class="memo-btn memo-btn-outline">
                    <i class="fa-solid fa-file-excel"></i> Export ke Excel
                </button>
            </div>
            <p class="memo-filter-hint">
                Export akan mengambil data <strong>yang dicentang</strong> pada tabel di bawah.
                Kalau tidak ada yang dicentang, export akan mengambil <strong>semua data sesuai periode filter</strong> di atas.
            </p>

            <form id="bulkPrintForm" method="POST" action="{{ route('tools.memopengiriman.bulk-pdf') }}" target="_blank">
                @csrf
            </form>
            <form id="bulkDeleteForm" method="POST" action="{{ route('tools.memopengiriman.bulk-destroy') }}">
                @csrf
                @method('DELETE')
            </form>
            <form id="exportExcelForm" method="POST" action="{{ route('tools.memopengiriman.export-excel') }}">
                @csrf
                <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                <input type="hidden" name="date_to" value="{{ $dateTo }}">
            </form>

            <div class="memo-bulk-bar" id="bulkBar" style="display:none;">
                <span><span id="selectedCount">0</span> memo dipilih</span>
                <div style="display:flex; gap:8px;">
                    <button type="button" id="btnCetakTerpilih" class="memo-btn memo-btn-outline" disabled>
                        <i class="fa-solid fa-print"></i> Cetak Memo
                    </button>
                    <button type="button" id="btnHapusTerpilih" class="memo-btn memo-btn-danger" disabled>
                        <i class="fa-solid fa-trash"></i> Hapus Hapus
                    </button>
                    <button type="button" id="btnBatalPilih" class="memo-btn memo-btn-outline">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                </div>
            </div>

            <div class="memo-table-wrap">
                <table class="memo-table">
                    <thead>
                        <tr>
                            <th style="width:36px;"><input type="checkbox" class="memo-checkbox" id="checkAll"></th>
                            <th>No. Memo</th>
                            <th>No. Struk</th>
                            <th>Tgl Memo</th>
                            <th>Diterima Dari</th>
                            <th>Dikirim Ke</th>
                            <th>Instalasi</th>
                            <th style="text-align:right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($memos as $m)
                        <tr>
                            <td><input type="checkbox" class="memo-checkbox row-check" value="{{ $m->id }}"></td>
                            <td>{{ $m->nomor_memo }}</td>
                            <td>{{ $m->no_struk ?: '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($m->tanggal_memo)->format('d-m-Y') }}</td>
                            <td>{{ $m->diterima_dari }}</td>
                            <td>{{ $m->tujuan_contact_person }}</td>
                            <td>
                                <span class="memo-badge {{ $m->instalasi ? 'memo-badge-yes' : 'memo-badge-no' }}">
                                    {{ $m->instalasi ? 'Ya' : 'Tidak' }}
                                </span>
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                <a href="{{ route('tools.memopengiriman.pdf', $m) }}" target="_blank"
                                   class="memo-action-icon" title="Cetak">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                                <a href="{{ route('tools.memopengiriman.edit', $m) }}"
                                   class="memo-action-icon" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form action="{{ route('tools.memopengiriman.destroy', $m) }}" method="POST"
                                      style="display:inline;"
                                      onsubmit="return confirm('Hapus memo ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="memo-action-icon danger" title="Hapus">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="memo-empty">Tidak ada memo pengiriman pada periode yang dipilih.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:16px;">
                {{ $memos->links() }}
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
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
            removeBtn.className = 'memo-btn memo-btn-outline memo-btn-sm';
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

    bindRepeater(
        'noStrukRepeater',
        'btnAddNoStruk',
        'no_struk_items',
        @json(old('no_struk_items', isset($editMemo) ? $editMemo->no_struk_array : []))
    );

    bindRepeater(
        'noStrukInstalasiRepeater',
        'btnAddNoStrukInstalasi',
        'no_struk_instalasi_items',
        @json(old('no_struk_instalasi_items', isset($editMemo) ? $editMemo->no_struk_instalasi_array : []))
    );

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
            removeBtn.className = 'memo-btn memo-btn-outline memo-btn-sm';
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

    bindBarangRepeater('barangRepeater', 'btnAddBarang', @json($barangInitial));

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

    bindRupiah('biaya_kirim_display', 'biaya_kirim', '{{ old('biaya_kirim', $editMemo->biaya_kirim ?? '') }}');
    bindRupiah('biaya_instalasi_display', 'biaya_instalasi', '{{ old('biaya_instalasi', $editMemo->biaya_instalasi ?? '') }}');

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

    bindTanggalIndo('pengiriman_picker', 'pengiriman_hari_tanggal', @json(old('pengiriman_hari_tanggal', $editMemo->pengiriman_hari_tanggal ?? null)));
    bindTanggalIndo('instalasi_picker', 'instalasi_hari_tanggal', @json(old('instalasi_hari_tanggal', $editMemo->instalasi_hari_tanggal ?? null)));

    /* ================= Toggle field instalasi (tampil HANYA jika "Ya") ================= */
    var instalasiSelect = document.getElementById('instalasi_select');
    var instalasiFields = document.getElementById('instalasi_extra_fields');

    function toggleInstalasi() {
        var ya = instalasiSelect.value === '1';
        instalasiFields.style.display = ya ? 'block' : 'none';
    }
    instalasiSelect.addEventListener('change', toggleInstalasi);
    toggleInstalasi();

    /* ================= Checkbox bulk select (cetak/hapus/export/batal) ================= */
    var checkAll   = document.getElementById('checkAll');
    var countEl    = document.getElementById('selectedCount');
    var btnCetak   = document.getElementById('btnCetakTerpilih');
    var btnHapus   = document.getElementById('btnHapusTerpilih');
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

    refresh();
})();
</script>
@endpush