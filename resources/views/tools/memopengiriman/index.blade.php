@extends('layouts.app')

@section('og_image', 'memopengiriman')
@section('title', 'Memo Pengiriman Online — Buat & Kirim PDF Berlogo | MediaTools')
@section('meta_description', 'Buat memo pengiriman & surat jalan PDF berlogo gratis, kirim via email ke pelanggan, dan export Excel. Cepat, rapi, & privat — alternatif terbaik template Word & Excel manual.')
@section('meta_keywords', 'memo pengiriman online, buat memo pengiriman, surat jalan online, memo pengiriman pdf, format memo pengiriman barang, template memo pengiriman excel, delivery note generator, bukti pengiriman barang, contoh memo pengiriman, aplikasi memo pengiriman gratis, generator surat jalan, cetak memo pengiriman, buat surat jalan pdf, aplikasi surat jalan umkm')

@include('seo.memopengiriman')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/memopengiriman.css') }}">
@endpush

@section('content')
<div class="memo-page">

    {{-- ====================== HEADER BANNER ====================== --}}
    <div class="memo-header">
        <div class="memo-header-decor" aria-hidden="true"></div>

        <div class="memo-wrap">
            <div class="memo-header-inner">

                <div class="memo-header-content">
                    <p class="breadcrumb">
                        <a href="{{ route('home') }}" class="breadcrumb-home"><i class="fa-solid fa-house"></i> Home</a>
                        <i class="fa-solid fa-chevron-right breadcrumb-sep"></i>
                        <span class="breadcrumb-current">Memo Pengiriman</span>
                    </p>

                    <h1>Memo <span class="memo-hero-accent-text">Pengiriman</span>.</h1>

                    <p class="memo-hero-desc">
                        Buat, kelola, cetak PDF berlogo, dan kirim memo pengiriman via email
                        dengan cepat dan mudah. Data Anda privat, hanya bisa diakses oleh akun Anda sendiri.
                    </p>

                    <div class="memo-hero-tags">
                        <div class="memo-hero-tag">
                            <i class="fa-solid fa-shield-halved"></i>
                            <div>
                                <strong>Privat &amp; Aman</strong>
                                <span>Data hanya milik Anda</span>
                            </div>
                        </div>
                        <div class="memo-hero-tag">
                            <i class="fa-solid fa-file-pdf"></i>
                            <div>
                                <strong>PDF Berlogo</strong>
                                <span>Siap cetak profesional</span>
                            </div>
                        </div>
                        <div class="memo-hero-tag">
                            <i class="fa-solid fa-envelope"></i>
                            <div>
                                <strong>Kirim via Email</strong>
                                <span>Langsung ke pelanggan</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Decorative illustration — hidden on small/medium screens --}}
                <div class="memo-hero-illustration" aria-hidden="true">
                    <div class="memo-hero-glow"></div>

                    <div class="memo-hero-card">
                        <div class="memo-hero-card-badge"><i class="fa-solid fa-check"></i></div>
                        <div class="memo-hero-card-header">
                            <i class="fa-solid fa-file-lines"></i>
                            <span>MEMO PENGIRIMAN</span>
                        </div>
                        <div class="memo-hero-card-lines">
                            <span></span><span></span><span class="short"></span>
                        </div>
                        <div class="memo-hero-card-table">
                            <span></span><span></span><span></span><span></span>
                        </div>
                    </div>

                    <div class="memo-hero-box memo-hero-box-1"><i class="fa-solid fa-box"></i></div>
                    <div class="memo-hero-box memo-hero-box-2"><i class="fa-solid fa-box"></i></div>
                    <div class="memo-hero-truck"><i class="fa-solid fa-truck-fast"></i></div>
                </div>

            </div>
        </div>
    </div>

    <div class="memo-wrap">

        {{-- ====================== STATS BAR (overlaps header) ====================== --}}
        <div class="memo-stats-bar">
            <div class="memo-stat">
                <span class="memo-stat-icon"><i class="fa-regular fa-clock"></i></span>
                <div>
                    <strong>Hemat Waktu</strong>
                    <span>template memo dalam hitungan detik</span>
                </div>
            </div>
            <div class="memo-stat">
                <span class="memo-stat-icon"><i class="fa-solid fa-print"></i></span>
                <div>
                    <strong>Cetak Mudah</strong>
                    <span>PDF siap cetak dengan logo perusahaan Anda</span>
                </div>
            </div>
            <div class="memo-stat">
                <span class="memo-stat-icon"><i class="fa-solid fa-paper-plane"></i></span>
                <div>
                    <strong>Kirim Cepat</strong>
                    <span>Kirim memo PDF langsung ke email pelanggan</span>
                </div>
            </div>
            <div class="memo-stat">
                <span class="memo-stat-icon"><i class="fa-solid fa-lock"></i></span>
                <div>
                    <strong>100% Privat</strong>
                    <span>Hanya Anda yang dapat mengakses data Anda</span>
                </div>
            </div>
        </div>

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

        {{-- ======================================================
            LAYOUT 2 KOLOM (desktop & tablet, >=768px):
            Kiri  = Form Input Memo
            Kanan = Logo Perusahaan (atas) + Laporan Memo (bawah)
            Di mobile (<768px), otomatis kembali stack 1 kolom
            mengikuti urutan DOM asli: Logo -> Form -> Laporan.
            ====================================================== --}}
        <div class="memo-layout">

        {{-- ====================== LOGO PERUSAHAAN (PER USER) ====================== --}}
        <div class="memo-card memo-logo-card memo-card--logo">
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
                        <label for="logoInput" class="memo-btn memo-btn-success" style="cursor:pointer;">
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
        <div class="memo-card memo-card--form">
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
                        <label>No Telepon</label>
                        <input type="tel" inputmode="numeric" name="telepon_dari" class="memo-input only-phone"
                            placeholder="08xx-xxxx-xxxx"
                            value="{{ old('telepon_dari', $editMemo->telepon_dari ?? '') }}">
                        @error('telepon_dari') <div class="memo-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="memo-form-group">
                        <label>No Struk</label>
                        <div id="noStrukRepeater" class="memo-repeater"></div>
                        <button type="button" class="memo-btn memo-btn-success memo-btn-sm" id="btnAddNoStruk" style="margin-top:6px;">
                            <i class="fa-solid fa-plus"></i> Tambah Struk
                        </button>
                        @error('no_struk_items') <div class="memo-error">{{ $message }}</div> @enderror
                        @error('no_struk_items.*') <div class="memo-error">{{ $message }}</div> @enderror
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
                    <label>Berupa (Deskripsi)</label>
                    <div id="barangRepeater" class="memo-repeater"></div>
                    <button type="button" class="memo-btn memo-btn-success memo-btn-sm" id="btnAddBarang" style="margin-top:6px;">
                        <i class="fa-solid fa-plus"></i> Tambah Data
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

                <div class="memo-grid-2">
                    <div class="memo-form-group">
                        <label>Email Tujuan (untuk kirim PDF)</label>
                        <input type="email" name="email_tujuan_person" class="memo-input"
                               placeholder="email@penerima.com"
                               value="{{ old('email_tujuan_person', $editMemo->email_tujuan_person ?? '') }}">
                        <p style="font-size:12px; color:#6b7280; margin-top:4px;">
                            Opsional. Isi kolom ini agar tombol <strong>"Kirim"</strong> pada tabel rekap bisa mengirim hasil cetak PDF memo ini langsung ke email penerima.
                        </p>
                        @error('email_tujuan_person') <div class="memo-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="memo-grid-2">
                    <div class="memo-form-group">
                        <label>Alamat</label>
                        <textarea name="tujuan_alamat" class="memo-input" rows="2"
                                placeholder="Alamat lengkap tujuan pengiriman" required>{{ old('tujuan_alamat', $editMemo->tujuan_alamat ?? '') }}</textarea>
                        @error('tujuan_alamat') <div class="memo-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="memo-form-group">
                        <label>Keterangan Lainnya</label>
                        <textarea name="keterangan_lainnya" class="memo-input" rows="2"
                                placeholder="Tulis Keterangan lainnya (jika ada)">{{ old('keterangan_lainnya', $editMemo->keterangan_lainnya ?? '') }}</textarea>
                        @error('keterangan_lainnya') <div class="memo-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="memo-grid-2">
                    <div class="memo-form-group">
                        <label>Nama Customer Service</label>
                        <input type="text" name="customer_service" class="memo-input"
                            placeholder="Nama yang menangani pengiriman"
                            value="{{ old('customer_service', $editMemo->customer_service ?? '') }}" required>
                        @error('customer_service') <div class="memo-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="memo-form-group">
                        <label>Nama Sales</label>
                        <input type="text" name="nama_sales" class="memo-input"
                            placeholder="Nama sales dari pengiriman ini"
                            value="{{ old('nama_sales', $editMemo->nama_sales ?? '') }}" required>
                        @error('nama_sales') <div class="memo-error">{{ $message }}</div> @enderror
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
                <div class="memo-grid-3">
                    <div class="memo-form-group">
                        <label>Instalasi</label>
                        <select name="instalasi" id="instalasi_select" class="memo-input">
                            <option value="0" {{ !old('instalasi', $editMemo->instalasi ?? false) ? 'selected' : '' }}>Tidak</option>
                            <option value="1" {{ old('instalasi', $editMemo->instalasi ?? false) ? 'selected' : '' }}>Ya</option>
                        </select>
                    </div>
                </div>

                <div id="instalasi_extra_fields">
                    <div class="memo-grid-3">
                        <div class="memo-form-group">
                            <label>Tanggal Instalasi</label>
                            <input type="datetime-local" id="instalasi_picker" class="memo-input">
                            <input type="hidden" name="instalasi_hari_tanggal" id="instalasi_hari_tanggal">
                            @error('instalasi_hari_tanggal') <div class="memo-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="memo-form-group">
                            <label>Biaya Instalasi (Rp)</label>
                            <input type="text" inputmode="numeric" id="biaya_instalasi_display" class="memo-input" placeholder="Rp 0">
                            <input type="hidden" name="biaya_instalasi" id="biaya_instalasi">
                            @error('biaya_instalasi') <div class="memo-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="memo-form-group">
                            <label>No Struk Instalasi</label>
                            <div id="noStrukInstalasiRepeater" class="memo-repeater"></div>
                            <button type="button" class="memo-btn memo-btn-success memo-btn-sm" id="btnAddNoStrukInstalasi" style="margin-top:6px;">
                                <i class="fa-solid fa-plus"></i> Tambah Struk
                            </button>
                            @error('no_struk_instalasi_items') <div class="memo-error">{{ $message }}</div> @enderror
                            @error('no_struk_instalasi_items.*') <div class="memo-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="memo-grid-3">
                    <div class="memo-form-group">
                        <label>Tanggal Memo</label>
                        <input type="date" name="tanggal_memo" class="memo-input"
                            value="{{ old('tanggal_memo', isset($editMemo) ? \Carbon\Carbon::parse($editMemo->tanggal_memo)->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                        @error('tanggal_memo') <div class="memo-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="memo-grid-2">
                        <div class="memo-form-group">
                            <label>Status</label>
                            <select name="status_pengiriman" id="statusPengirimanSelect" class="memo-input">
                                <option value="1" {{ old('status_pengiriman', $editMemo->status_pengiriman ?? true) ? 'selected' : '' }}>Terkirim</option>
                                <option value="0" {{ !old('status_pengiriman', $editMemo->status_pengiriman ?? true) ? 'selected' : '' }}>Pending</option>
                            </select>
                            @error('status_pengiriman') <div class="memo-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div style="text-align:right; margin-top:25px;">
                        <button type="submit" class="memo-btn memo-btn-success">
                            <i class="fa-solid fa-floppy-disk"></i>
                            {{ isset($editMemo) ? 'Update Memo' : 'Simpan Memo' }}
                        </button>
                        @if(isset($editMemo))
                            <a href="{{ route('tools.memopengiriman') }}" class="memo-btn memo-btn-outline">Batal Edit</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- ====================== REKAP / TABEL ====================== --}}
        <div class="memo-card memo-card--report">
            <h2>Laporan Memo Pengiriman</h2>

            {{-- ---------- Filter Periode Tanggal & Status ---------- --}}
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
                    <div class="memo-filter-field">
                        <label>Status</label>
                        <select name="status" class="memo-input">
                            <option value="" {{ is_null($statusFilter) ? 'selected' : '' }}>Semua Status</option>
                            <option value="1" {{ $statusFilter === true ? 'selected' : '' }}>Terkirim</option>
                            <option value="0" {{ $statusFilter === false ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>
                    <button type="submit" class="memo-btn memo-btn-success">
                        <i class="fa-solid fa-search"></i> Cari
                    </button>
                </form>

                <button type="button" id="btnExportExcel" class="memo-btn memo-btn-primary">
                    <i class="fa-solid fa-file-excel"></i> Export ke Excel
                </button>
            </div>
            <p class="memo-filter-hint">
                Export akan mengambil data <strong>yang dicentang</strong> pada tabel di bawah.
                Kalau tidak ada yang dicentang, export akan mengambil <strong>semua data sesuai periode & status filter</strong> di atas.
            </p>

            <form id="bulkPrintForm" method="POST" action="{{ route('tools.memopengiriman.bulk-pdf') }}" target="_blank">
                @csrf
            </form>
            <form id="bulkDeleteForm" method="POST" action="{{ route('tools.memopengiriman.bulk-destroy') }}">
                @csrf
                @method('DELETE')
            </form>
            <form id="bulkStatusForm" method="POST" action="{{ route('tools.memopengiriman.bulk-status') }}">
                @csrf
                <input type="hidden" name="status" id="bulkStatusValue" value="1">
            </form>
            <form id="exportExcelForm" method="POST" action="{{ route('tools.memopengiriman.export-excel') }}">
                @csrf
                <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                <input type="hidden" name="date_to" value="{{ $dateTo }}">
                <input type="hidden" name="status" value="{{ is_null($statusFilter) ? '' : ($statusFilter ? '1' : '0') }}">
            </form>

            <div class="memo-bulk-bar" id="bulkBar" style="display:none;">
                <span><span id="selectedCount">0</span> memo dipilih</span>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <button type="button" id="btnCetakTerpilih" class="memo-btn memo-btn-primary" disabled>
                        <i class="fa-solid fa-print"></i> Cetak
                    </button>
                    <button type="button" id="btnTandaiTerkirim" class="memo-btn memo-btn-success" disabled>
                        <i class="fa-solid fa-truck-fast"></i> Kirim
                    </button>
                    <button type="button" id="btnTandaiPending" class="memo-btn memo-btn-warning" disabled>
                        <i class="fa-solid fa-clock"></i> Pending
                    </button>
                    <button type="button" id="btnHapusTerpilih" class="memo-btn memo-btn-danger" disabled>
                        <i class="fa-solid fa-trash"></i> Hapus
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
                            <th>Status</th>
                            <th>No. Struk</th>
                            <th>Tgl Pengiriman</th>
                            <th>Diterima Dari</th>
                            <th>Dikirim Ke</th>
                            <th>Nama CS</th>
                            <th>Nama Sales</th>
                            <th>Instalasi</th>
                            <th style="text-align:right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($memos as $m)
                        <tr>
                            <td><input type="checkbox" class="memo-checkbox row-check" value="{{ $m->id }}"></td>
                            <td>
                                <span class="memo-badge {{ $m->status_pengiriman ? 'memo-badge-yes' : 'memo-badge-pending' }}">
                                    {{ $m->status_pengiriman ? 'Terkirim' : 'Pending' }}
                                </span>
                            </td>
                            <td>{{ $m->no_struk ?: '-' }}</td>
                            <td>{{ $m->pengiriman_hari_tanggal ?: '-' }}</td>
                            <td>{{ $m->diterima_dari }}</td>
                            <td>{{ $m->tujuan_contact_person }}</td>
                            <td>{{ $m->customer_service ?: '-' }}</td>
                            <td>{{ $m->nama_sales ?: '-' }}</td>
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
                                <form action="{{ route('tools.memopengiriman.kirim-email', $m) }}" method="POST"
                                      style="display:inline;" class="kirim-email-form"
                                      data-email="{{ $m->email_tujuan_person }}"
                                      data-nomor="{{ $m->nomor_memo }}">
                                    @csrf
                                    <button type="submit" class="memo-action-icon" title="Kirim PDF via Email">
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </button>
                                </form>
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
                            <td colspan="10">
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

        {{-- ═══ SLOT 3: RESULT BANNER 300×250 ═══ --}}
        <div class="ads-slot-result no-print">
            @include('components.ads.banner-result')
        </div>

        {{-- ═══ SLOT 4: NATIVE BANNER ═══ --}}
        <div class="ads-slot-native no-print">
            @include('components.ads.banner-content')
        </div>

        </div>{{-- /.memo-layout --}}

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="{{ asset('js/memopengiriman.js') }}"></script>
<script>
(function () {
    /* ================= Repeater No Struk & No Struk Instalasi ================= */
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
    bindBarangRepeater('barangRepeater', 'btnAddBarang', @json($barangInitial));

    /* ================= Format Rupiah live (Biaya Kirim & Biaya Instalasi) ================= */
    bindRupiah('biaya_kirim_display', 'biaya_kirim', '{{ old('biaya_kirim', $editMemo->biaya_kirim ?? '') }}');
    bindRupiah('biaya_instalasi_display', 'biaya_instalasi', '{{ old('biaya_instalasi', $editMemo->biaya_instalasi ?? '') }}');

    /* ================= Datetime picker -> format "Minggu, 05-Juli-2026 16:42" ================= */
    bindTanggalIndo('pengiriman_picker', 'pengiriman_hari_tanggal', @json(old('pengiriman_hari_tanggal', $editMemo->pengiriman_hari_tanggal ?? null)));
    bindTanggalIndo('instalasi_picker', 'instalasi_hari_tanggal', @json(old('instalasi_hari_tanggal', $editMemo->instalasi_hari_tanggal ?? null)));
})();
</script>
@endpush