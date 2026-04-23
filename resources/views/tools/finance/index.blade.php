@extends('layouts.app')

@section('title', 'Pencatatan Keuangan Gratis | MediaTools')
@section('meta_description', 'Aplikasi pencatatan keuangan gratis untuk mencatat pemasukan dan pengeluaran harian secara mudah dan praktis. Kelola keuangan pribadi, bisnis kecil, atau usaha Anda dengan laporan sederhana langsung di browser tanpa ribet.')
@section('meta_keywords', 'pencatatan keuangan, aplikasi keuangan gratis, catatan pemasukan pengeluaran, keuangan pribadi, aplikasi kas sederhana, laporan keuangan harian, manajemen keuangan usaha, pembukuan sederhana, catatan uang harian, aplikasi finansial online')
@section('og_image', 'finance')

@include('seo.finance')

@section('content')
<link rel="stylesheet" href="{{ asset('css/finance.css') }}">
<link rel="stylesheet" href="{{ asset('css/ads.css') }}">

<div class="fi-page" id="financePage">
<script>document.body.classList.add('fi-active');</script>

{{-- ═══════════════════════════════════════
     PAGE HEADER
     ═══════════════════════════════════════ --}}
<div style="background: linear-gradient(135deg,#0f172a 0%,#0b2323 100%); padding: 32px 0 40px; margin-top: -50px; padding-top: 82px;">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(163,230,53,0.15);display:flex;align-items:center;justify-content:center;">
                        <i class="fa-solid fa-chart-pie" style="color:#a3e635;font-size:0.9rem;"></i>
                    </div>
                    <span style="font-size:0.7rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#a3e635;">MediaTools</span>
                </div>
                <nav aria-label="Breadcrumb" class="flex justify-center mb-5">
                    <ol class="flex items-center gap-2 text-xs" style="color:var(--text-3)">
                        <li><a href="{{ url('/') }}" style="color:var(--text-3);text-decoration:none;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text-3)'">Home</a></li>
                        <li style="margin:0 4px;font-size:9px;">›</li>
                        <li style="color:var(--accent);font-weight:600;">Pencatatan Keuangan Gratis</li>
                    </ol>
                </nav>
                <h1 style="font-size:clamp(1.4rem,3vw,1.8rem);font-weight:800;color:#fff;letter-spacing:-0.02em;margin-bottom:4px;">
                    Pencatatan Keuangan UMKM
                </h1>
                <p style="font-size:0.85rem;color:#6b7280;">Catat, pantau, dan analisis arus kas bisnis Anda secara real-time.</p>
            </div>

            {{-- Export Buttons --}}
            <div class="flex flex-wrap gap-2">
                <button onclick="openPrintModal('income')" class="btn-print">
                    <i class="fa-solid fa-arrow-trend-up" style="color:#22c55e;"></i>
                    Cetak Pemasukan
                </button>
                <button onclick="openPrintModal('expense')" class="btn-print">
                    <i class="fa-solid fa-arrow-trend-down" style="color:#ef4444;"></i>
                    Cetak Pengeluaran
                </button>
                <button onclick="openPrintModal('all')" class="btn-print" style="background:#a3e635;color:#0f172a;border-color:#a3e635;font-weight:700;">
                    <i class="fa-solid fa-print"></i>
                    Cetak Semua
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Slight overlap curve --}}
<div style="height:32px;background:linear-gradient(135deg,#0f172a 0%,#0b2323 100%);border-radius:0 0 32px 32px;margin-bottom:-16px;"></div>

{{-- ═══════════════════════════════════════
     MAIN CONTENT
     ═══════════════════════════════════════ --}}
<div class="max-w-7xl mx-auto px-6 pb-16" style="padding-top:24px;">

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="fi-alert success mb-5" id="flashAlert">
        <i class="fa-solid fa-circle-check"></i>
        {{ session('success') }}
        <button onclick="this.closest('#flashAlert').remove()" style="margin-left:auto;background:none;border:none;cursor:pointer;color:inherit;font-size:0.9rem;">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    @endif
    @if($errors->any())
    <div class="fi-alert error mb-5">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Filter + Actions Bar ── --}}
    <div class="filter-bar mb-6">
        <i class="fa-solid fa-filter" style="color:var(--fi-text-muted);font-size:0.8rem;"></i>
        <span style="font-size:0.8rem;font-weight:700;color:var(--fi-text-muted);white-space:nowrap;">Periode:</span>

        <form method="GET" action="{{ route('tools.finance') }}" class="flex items-center gap-2 flex-wrap">
            <select name="month" class="filter-select">
                @php
                $months = [
                    1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
                    5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
                    9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
                ];
                @endphp
                @foreach($months as $num => $label)
                <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select name="year" class="filter-select">
                @foreach($years as $y)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
                @if(!$years->contains(now()->year))
                <option value="{{ now()->year }}" {{ $year == now()->year ? 'selected' : '' }}>{{ now()->year }}</option>
                @endif
            </select>

            <button type="submit" class="filter-btn">
                <i class="fa-solid fa-magnifying-glass mr-1"></i> Tampilkan
            </button>
        </form>

        {{-- Current period badge --}}
        <div style="margin-left:auto;">
            <span style="font-size:0.78rem;font-weight:700;background:var(--fi-green-dim);color:var(--fi-green);padding:5px 12px;border-radius:99px;">
                <i class="fa-regular fa-calendar mr-1"></i>
                {{ \Carbon\Carbon::createFromDate($year, $month, 1)->locale('id')->isoFormat('MMMM YYYY') }}
            </span>
        </div>
    </div>

    {{-- ── Stat Cards Row ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <div class="stat-card income">
            <div class="stat-icon income"><i class="fa-solid fa-arrow-trend-up"></i></div>
            <div class="stat-label">Total Pemasukan</div>
            <div class="stat-value income" id="displayIncome">
                Rp {{ number_format($totalIncome, 0, ',', '.') }}
            </div>
            @php $incTxCount = $transactions->where('type','income')->count(); @endphp
            <div style="font-size:0.72rem;color:var(--fi-text-muted);margin-top:6px;">
                {{ $incTxCount }} transaksi
            </div>
            @if($totalIncome > 0)
            <div class="progress-track">
                <div class="progress-bar" style="width:100%;background:var(--fi-green);"></div>
            </div>
            @endif
        </div>

        <div class="stat-card expense">
            <div class="stat-icon expense"><i class="fa-solid fa-arrow-trend-down"></i></div>
            <div class="stat-label">Total Pengeluaran</div>
            <div class="stat-value expense" id="displayExpense">
                Rp {{ number_format($totalExpense, 0, ',', '.') }}
            </div>
            @php $expTxCount = $transactions->where('type','expense')->count(); @endphp
            <div style="font-size:0.72rem;color:var(--fi-text-muted);margin-top:6px;">
                {{ $expTxCount }} transaksi
            </div>
            @if($totalExpense > 0 && $totalIncome > 0)
            <div class="progress-track">
                <div class="progress-bar" style="width:{{ min(100, round($totalExpense/$totalIncome*100)) }}%;background:var(--fi-red);"></div>
            </div>
            @endif
        </div>

        <div class="stat-card balance">
            <div class="stat-icon balance"><i class="fa-solid fa-scale-balanced"></i></div>
            <div class="stat-label">Saldo Bersih</div>
            <div class="stat-value {{ $balance >= 0 ? 'positive' : 'negative' }}">
                {{ $balance >= 0 ? '' : '-' }}Rp {{ number_format(abs($balance), 0, ',', '.') }}
            </div>
            <div style="font-size:0.72rem;margin-top:6px;font-weight:600;
                color:{{ $balance >= 0 ? 'var(--fi-green)' : 'var(--fi-red)' }};">
                {{ $balance >= 0 ? '✓ Positif' : '⚠ Defisit' }}
            </div>
        </div>

        <div class="stat-card count">
            <div class="stat-icon count"><i class="fa-solid fa-receipt"></i></div>
            <div class="stat-label">Total Transaksi</div>
            <div class="stat-value" style="color:var(--fi-amber);">{{ $txCount }}</div>
            <div style="font-size:0.72rem;color:var(--fi-text-muted);margin-top:6px;">
                Bulan ini
            </div>
        </div>
    </div>

    {{-- ── Quick Action Buttons ── --}}
    <div class="action-container">
        <div class="flex gap-3">
            <button onclick="openModal('incomeModal')" class="btn-add-income">
                <i class="fa-solid fa-plus"></i>
                <span>Pemasukan</span> </button>
            <button onclick="openModal('expenseModal')" class="btn-add-expense">
                <i class="fa-solid fa-plus"></i>
                <span>Pengeluaran</span>
            </button>
        </div>
        <div class="info-text">
            <i class="fa-solid fa-circle-info mr-1"></i>
            Data tersimpan otomatis ke akun Anda
        </div>
    </div>

    {{-- ── Charts Row ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-6">

        {{-- Doughnut Chart (2 cols) --}}
        <div class="chart-wrapper lg:col-span-2">
            <div class="chart-title">Distribusi Keuangan</div>
            <div class="chart-subtitle">Komposisi Bulan Ini</div>

            @if($totalIncome == 0 && $totalExpense == 0)
            <div style="text-align:center;padding:40px 0;color:var(--fi-text-muted);font-size:0.85rem;">
                <i class="fa-solid fa-chart-pie" style="font-size:2rem;margin-bottom:12px;display:block;opacity:0.3;"></i>
                Belum ada data transaksi
            </div>
            @else
            <div class="chart-3d-wrap">
                <canvas id="doughnutChart" style="max-height:220px;"></canvas>
            </div>
            {{-- Legend --}}
            <div style="display:flex;flex-direction:column;gap:10px;margin-top:20px;">
                <div style="display:flex;align-items:center;justify-content:space-between;font-size:0.82rem;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div class="legend-dot" style="background:var(--fi-green);"></div>
                        <span style="font-weight:600;color:var(--fi-text-muted);">Pemasukan</span>
                    </div>
                    <span style="font-weight:800;color:var(--fi-green);">
                        Rp {{ number_format($totalIncome,0,',','.') }}
                    </span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;font-size:0.82rem;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div class="legend-dot" style="background:var(--fi-red);"></div>
                        <span style="font-weight:600;color:var(--fi-text-muted);">Pengeluaran</span>
                    </div>
                    <span style="font-weight:800;color:var(--fi-red);">
                        Rp {{ number_format($totalExpense,0,',','.') }}
                    </span>
                </div>
                @if($totalIncome > 0)
                <div style="display:flex;align-items:center;justify-content:space-between;font-size:0.82rem;padding-top:8px;border-top:1px solid var(--fi-border2);">
                    <span style="font-weight:600;color:var(--fi-text-muted);">Rasio Pengeluaran</span>
                    <span style="font-weight:800;color:{{ $totalExpense/$totalIncome > 0.8 ? 'var(--fi-red)' : 'var(--fi-green)' }};">
                        {{ round($totalExpense / max($totalIncome,1) * 100, 1) }}%
                    </span>
                </div>
                @endif
            </div>
            @endif
        </div>

        {{-- Line Chart (3 cols) --}}
        <div class="chart-wrapper lg:col-span-3">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:4px;">
                <div>
                    <div class="chart-title">Tren Keuangan</div>
                    <div class="chart-subtitle">6 Bulan Terakhir</div>
                </div>
                <div style="display:flex;gap:12px;align-items:center;">
                    <div style="display:flex;align-items:center;gap:5px;font-size:0.72rem;font-weight:700;color:var(--fi-text-muted);">
                        <div style="width:20px;height:3px;border-radius:2px;background:var(--fi-green);"></div>
                        Masuk
                    </div>
                    <div style="display:flex;align-items:center;gap:5px;font-size:0.72rem;font-weight:700;color:var(--fi-text-muted);">
                        <div style="width:20px;height:3px;border-radius:2px;background:var(--fi-red);border-style:dashed;"></div>
                        Keluar
                    </div>
                </div>
            </div>
            <div class="chart-3d-wrap">
                <canvas id="lineChart" style="max-height:240px;"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Transaction Table ── --}}
    <div class="fi-table-wrap">
        {{-- Table Header --}}
        <div style="padding:18px 20px;border-bottom:1px solid var(--fi-border2);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <div style="font-size:0.95rem;font-weight:800;color:var(--fi-text);margin-bottom:2px;">
                    Riwayat Transaksi
                </div>
                <div style="font-size:0.78rem;color:var(--fi-text-muted);">
                    {{ \Carbon\Carbon::createFromDate($year,$month,1)->locale('id')->isoFormat('MMMM YYYY') }}
                    — {{ $txCount }} transaksi
                </div>
            </div>
            {{-- Tab Filter (menggunakan Link untuk akurasi Pagination) --}}
            <div style="display:flex;gap:6px;" id="tabBar">
                <a href="{{ request()->fullUrlWithQuery(['type' => 'all', 'page' => 1]) }}" 
                class="tab-btn {{ $type === 'all' ? 'active-all' : '' }}">
                    Semua ({{ $txCount }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['type' => 'income', 'page' => 1]) }}" 
                class="tab-btn {{ $type === 'income' ? 'active-income' : '' }}">
                    <i class="fa-solid fa-arrow-trend-up" style="color:var(--fi-green);font-size:0.7rem;"></i>
                    Masuk ({{ $incTxCount }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['type' => 'expense', 'page' => 1]) }}" 
                class="tab-btn {{ $type === 'expense' ? 'active-expense' : '' }}">
                    <i class="fa-solid fa-arrow-trend-down" style="color:var(--fi-red);font-size:0.7rem;"></i>
                    Keluar ({{ $expTxCount }})
                </a>
            </div>
        </div>

        @if($transactions->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fa-regular fa-folder-open"></i></div>
            <h4>Belum Ada Transaksi</h4>
            <p>Mulai catat pemasukan atau pengeluaran Anda untuk bulan ini.</p>
        </div>
        @else
        <div class="table-responsive-container">
            <table class="fi-table" id="txTable">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Jenis</th>
                        <th style="text-align:right;">Qty</th>
                        <th style="text-align:right;">Harga/Item</th>
                        <th style="text-align:right;">Total</th>
                        <th>Catatan</th>
                        <th style="text-align:center;">Hapus</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $tx)
                    <tr data-type="{{ $tx->type }}" class="tx-row">
                        <td style="white-space:nowrap;font-weight:600;color:var(--fi-text-muted);">
                            {{ $tx->transaction_date->locale('id')->isoFormat('D MMM YYYY') }}
                        </td>
                        <td style="font-weight:700;max-width:160px;">
                            {{ $tx->name }}
                        </td>
                        <td>
                            @if($tx->type === 'income')
                            <span class="badge-income">
                                <i class="fa-solid fa-arrow-up" style="font-size:0.6rem;"></i>
                                Masuk
                            </span>
                            @else
                            <span class="badge-expense">
                                <i class="fa-solid fa-arrow-down" style="font-size:0.6rem;"></i>
                                Keluar
                            </span>
                            @endif
                        </td>
                        <td style="text-align:right;color:var(--fi-text-muted);">
                            {{ number_format($tx->quantity, $tx->quantity == intval($tx->quantity) ? 0 : 2, ',', '.') }}
                        </td>
                        <td style="text-align:right;color:var(--fi-text-muted);">
                            Rp {{ number_format($tx->price_per_item, 0, ',', '.') }}
                        </td>
                        <td style="text-align:right;font-weight:800;white-space:nowrap;
                            color:{{ $tx->type === 'income' ? 'var(--fi-green)' : 'var(--fi-red)' }};">
                            {{ $tx->type === 'income' ? '+' : '-' }}Rp {{ number_format($tx->total_amount, 0, ',', '.') }}
                        </td>
                        <td style="font-size:0.8rem;color:var(--fi-text-muted);max-width:140px;">
                            {{ $tx->notes ?? '—' }}
                        </td>
                        <td style="text-align:center;">
                            <form method="POST"
                                  action="{{ route('tools.finance.transactions.destroy', $tx->id) }}"
                                  onsubmit="return confirm('Hapus transaksi ini?')">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="month" value="{{ $month }}">
                                <input type="hidden" name="year" value="{{ $year }}">
                                <button type="submit" class="btn-delete" title="Hapus">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:var(--fi-surface2);">
                        <td colspan="5" style="padding:13px 16px;font-size:0.8rem;font-weight:700;color:var(--fi-text-muted);">
                            RINGKASAN BULAN INI
                        </td>
                        <td style="text-align:right;padding:13px 16px;font-weight:800;font-size:0.9rem;
                            color:{{ $balance >= 0 ? 'var(--fi-green)' : 'var(--fi-red)' }};">
                            {{ $balance >= 0 ? '+' : '-' }}Rp {{ number_format(abs($balance), 0, ',', '.') }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
            <div class="fi-pagination-info">
                <div style="font-size: 0.8rem; color: var(--fi-text-muted);">
                    Menampilkan {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }} 
                    dari {{ $transactions->total() }} transaksi
                </div>
                <div class="custom-pagination">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>

</div><!-- /max-w-7xl -->
    {{-- ═══ ADS SLOT: HEADER ═══ --}}
    <div class="ads-slot-header no-print" style="margin-bottom:5px;">@include('components.ads.banner-header')</div>
    <div class="ads-slot-header no-print" style="margin-bottom:5px;">@include('components.ads.banner-header')</div>
    {{-- ═══ ADS SLOT: NATIVE BANNER ═══ --}}
    <div class="ads-slot-native no-print">@include('components.ads.banner-content')</div>
</div><!-- /fi-page -->


{{-- ══════════════════════════════════════════════════════
     MODAL: TAMBAH PEMASUKAN
     ══════════════════════════════════════════════════════ --}}
<div class="fi-modal-overlay" id="incomeModal">
    <div class="fi-modal">
        <div class="modal-header">
            <div>
                <div class="modal-icon income"><i class="fa-solid fa-arrow-trend-up"></i></div>
                <h3>Tambah Pemasukan</h3>
                <p class="modal-subtitle">Catat sumber pemasukan bisnis Anda</p>
            </div>
            <button class="btn-modal-close" onclick="closeModal('incomeModal')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('tools.finance.transactions.store') }}" id="incomeForm">
            @csrf
            <input type="hidden" name="type" value="income">

            <div style="display:flex;flex-direction:column;gap:14px;">
                <div>
                    <label class="fi-form-label">Tanggal Transaksi</label>
                    <input type="date" name="transaction_date" class="fi-form-input"
                           value="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label class="fi-form-label">Nama / Sumber Pemasukan</label>
                    <input type="text" name="name" class="fi-form-input"
                           placeholder="cth: Penjualan Produk, Jasa Konsultasi..." required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label class="fi-form-label">Jumlah Item</label>
                        <input type="number" name="quantity" id="inc_qty" class="fi-form-input"
                               placeholder="1" min="0.01" step="0.01" value="1"
                               oninput="calcTotal('inc')" required>
                    </div>
                    <div>
                        <label class="fi-form-label">Harga per Item (Rp)</label>
                        <input type="number" name="price_per_item" id="inc_price" class="fi-form-input"
                               placeholder="0" min="0" step="1"
                               oninput="calcTotal('inc')" required>
                    </div>
                </div>
                <div class="fi-total-preview">
                    <div style="font-size:0.72rem;font-weight:700;color:var(--fi-text-muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">
                        Total Pemasukan
                    </div>
                    <div class="total-amount" id="inc_total" style="color:var(--fi-green);">Rp 0</div>
                </div>
                <div>
                    <label class="fi-form-label">Catatan (opsional)</label>
                    <textarea name="notes" class="fi-form-input" rows="2"
                              placeholder="Catatan tambahan..."></textarea>
                </div>
            </div>

            <button type="submit" class="btn-modal-submit income">
                <i class="fa-solid fa-floppy-disk mr-2"></i>
                Simpan Pemasukan
            </button>
        </form>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════
     MODAL: TAMBAH PENGELUARAN
     ══════════════════════════════════════════════════════ --}}
<div class="fi-modal-overlay" id="expenseModal">
    <div class="fi-modal">
        <div class="modal-header">
            <div>
                <div class="modal-icon expense"><i class="fa-solid fa-arrow-trend-down"></i></div>
                <h3>Tambah Pengeluaran</h3>
                <p class="modal-subtitle">Catat pengeluaran operasional bisnis Anda</p>
            </div>
            <button class="btn-modal-close" onclick="closeModal('expenseModal')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('tools.finance.transactions.store') }}" id="expenseForm">
            @csrf
            <input type="hidden" name="type" value="expense">

            <div style="display:flex;flex-direction:column;gap:14px;">
                <div>
                    <label class="fi-form-label">Tanggal Transaksi</label>
                    <input type="date" name="transaction_date" class="fi-form-input"
                           value="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label class="fi-form-label">Nama / Jenis Pengeluaran</label>
                    <input type="text" name="name" class="fi-form-input"
                           placeholder="cth: Bahan Baku, Listrik, Gaji Karyawan..." required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label class="fi-form-label">Jumlah Item</label>
                        <input type="number" name="quantity" id="exp_qty" class="fi-form-input"
                               placeholder="1" min="0.01" step="0.01" value="1"
                               oninput="calcTotal('exp')" required>
                    </div>
                    <div>
                        <label class="fi-form-label">Harga per Item (Rp)</label>
                        <input type="number" name="price_per_item" id="exp_price" class="fi-form-input"
                               placeholder="0" min="0" step="1"
                               oninput="calcTotal('exp')" required>
                    </div>
                </div>
                <div class="fi-total-preview">
                    <div style="font-size:0.72rem;font-weight:700;color:var(--fi-text-muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">
                        Total Pengeluaran
                    </div>
                    <div class="total-amount" id="exp_total" style="color:var(--fi-red);">Rp 0</div>
                </div>
                <div>
                    <label class="fi-form-label">Catatan (opsional)</label>
                    <textarea name="notes" class="fi-form-input" rows="2"
                              placeholder="Catatan tambahan..."></textarea>
                </div>
            </div>

            <button type="submit" class="btn-modal-submit expense">
                <i class="fa-solid fa-floppy-disk mr-2"></i>
                Simpan Pengeluaran
            </button>
        </form>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════
     MODAL: EXPORT / CETAK PDF
     ══════════════════════════════════════════════════════ --}}
<div class="fi-modal-overlay" id="printModal">
    <div class="fi-modal">
        <div class="modal-header">
            <div>
                <div class="modal-icon print"><i class="fa-solid fa-print"></i></div>
                <h3 id="printModalTitle">Cetak Laporan</h3>
                <p class="modal-subtitle">Pilih rentang waktu yang ingin dicetak</p>
            </div>
            <button class="btn-modal-close" onclick="closeModal('printModal')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div style="display:flex;flex-direction:column;gap:14px;">
            <input type="hidden" id="printType" value="all">

            <div>
                <label class="fi-form-label">Filter Berdasarkan</label>
                <select id="printFilterBy" class="fi-form-input" onchange="togglePrintFilter()">
                    <option value="month">Bulan & Tahun Tertentu</option>
                    <option value="year">Satu Tahun Penuh</option>
                    <option value="range">Rentang Tanggal Custom</option>
                </select>
            </div>

            {{-- Month filter --}}
            <div id="printMonthWrap" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label class="fi-form-label">Bulan</label>
                    <select id="printMonth" class="fi-form-input">
                        @foreach($months as $num => $label)
                        <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="fi-form-label">Tahun</label>
                    <select id="printYear" class="fi-form-input">
                        @foreach($years as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                        @if(!$years->contains(now()->year))
                        <option value="{{ now()->year }}" selected>{{ now()->year }}</option>
                        @endif
                    </select>
                </div>
            </div>

            {{-- Year only filter --}}
            <div id="printYearWrap" style="display:none;">
                <label class="fi-form-label">Tahun</label>
                <select id="printYearOnly" class="fi-form-input">
                    @foreach($years as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                    @if(!$years->contains(now()->year))
                    <option value="{{ now()->year }}" selected>{{ now()->year }}</option>
                    @endif
                </select>
            </div>

            {{-- Range filter --}}
            <div id="printRangeWrap" style="display:none;display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label class="fi-form-label">Dari Tanggal</label>
                    <input type="date" id="printDateFrom" class="fi-form-input"
                           value="{{ date('Y-m-01') }}">
                </div>
                <div>
                    <label class="fi-form-label">Sampai Tanggal</label>
                    <input type="date" id="printDateTo" class="fi-form-input"
                           value="{{ date('Y-m-d') }}">
                </div>
            </div>
        </div>

        <button onclick="doPrint()" class="btn-modal-submit print">
            <i class="fa-solid fa-print mr-2"></i>
            Buka Halaman Cetak
        </button>
    </div>
</div>

@endsection

@push('scripts')
{{-- Chart.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<script>
/* ══════════════════════════════════════════
   MODAL FUNCTIONS
   ══════════════════════════════════════════ */
function openModal(id){
    document.getElementById(id).classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeModal(id){
    document.getElementById(id).classList.remove('show');
    document.body.style.overflow = '';
}
// Close on backdrop click
document.querySelectorAll('.fi-modal-overlay').forEach(overlay=>{
    overlay.addEventListener('click', e=>{
        if(e.target === overlay) closeModal(overlay.id);
    });
});
// Close on ESC
document.addEventListener('keydown', e=>{
    if(e.key === 'Escape'){
        document.querySelectorAll('.fi-modal-overlay.show').forEach(m=>closeModal(m.id));
    }
});

/* ══════════════════════════════════════════
   CALC TOTAL PREVIEW IN MODAL
   ══════════════════════════════════════════ */
function calcTotal(prefix){
    const qty   = parseFloat(document.getElementById(prefix+'_qty').value)   || 0;
    const price = parseFloat(document.getElementById(prefix+'_price').value) || 0;
    const total = qty * price;
    document.getElementById(prefix+'_total').textContent =
        'Rp ' + total.toLocaleString('id-ID', {maximumFractionDigits: 0});
}

/* ══════════════════════════════════════════
   TABLE FILTER TABS
   ══════════════════════════════════════════ */
function filterTable(type, btn){
    // Update tab styles
    document.querySelectorAll('.tab-btn').forEach(b=>{
        b.classList.remove('active-all','active-income','active-expense');
    });
    if(type === 'all')     btn.classList.add('active-all');
    if(type === 'income')  btn.classList.add('active-income');
    if(type === 'expense') btn.classList.add('active-expense');

    // Filter rows
    document.querySelectorAll('.tx-row').forEach(row=>{
        row.style.display = (type === 'all' || row.dataset.type === type) ? '' : 'none';
    });
}

/* ══════════════════════════════════════════
   PRINT MODAL
   ══════════════════════════════════════════ */
const printTitles = {
    all:     'Cetak Laporan Lengkap',
    income:  'Cetak Laporan Pemasukan',
    expense: 'Cetak Laporan Pengeluaran',
};

function openPrintModal(type){
    document.getElementById('printType').value = type;
    document.getElementById('printModalTitle').textContent = printTitles[type] || 'Cetak Laporan';
    openModal('printModal');
}

function togglePrintFilter(){
    const v = document.getElementById('printFilterBy').value;
    document.getElementById('printMonthWrap').style.display  = v === 'month' ? 'grid' : 'none';
    document.getElementById('printYearWrap').style.display   = v === 'year'  ? 'block': 'none';
    document.getElementById('printRangeWrap').style.display  = v === 'range' ? 'grid' : 'none';
}

function doPrint(){
    const type     = document.getElementById('printType').value;
    const filterBy = document.getElementById('printFilterBy').value;
    const base     = '{{ route("tools.finance.print") }}';
    let params     = `?type=${type}&filter_by=${filterBy}`;

    if(filterBy === 'month'){
        params += `&month=${document.getElementById('printMonth').value}`;
        params += `&year=${document.getElementById('printYear').value}`;
    } else if(filterBy === 'year'){
        params += `&year=${document.getElementById('printYearOnly').value}`;
    } else {
        params += `&date_from=${document.getElementById('printDateFrom').value}`;
        params += `&date_to=${document.getElementById('printDateTo').value}`;
    }

    window.open(base + params, '_blank');
    closeModal('printModal');
}

/* ══════════════════════════════════════════
   CHARTS
   ══════════════════════════════════════════ */
// Chart data from PHP
const chartLabels  = @json($chartLabels);
const chartIncome  = @json($chartIncome);
const chartExpense = @json($chartExpense);
const totalIncome  = {{ $totalIncome }};
const totalExpense = {{ $totalExpense }};

// ── 3D Shadow Plugin ──────────────────────
const shadow3DPlugin = {
    id: 'shadow3d',
    beforeDraw(chart){
        const ctx = chart.ctx;
        ctx.save();
        ctx.shadowColor  = 'rgba(0,0,0,0.18)';
        ctx.shadowBlur   = 18;
        ctx.shadowOffsetX = 4;
        ctx.shadowOffsetY = 6;
    },
    afterDraw(chart){ chart.ctx.restore(); },
};

// ── Doughnut Chart ────────────────────────
const doughnutEl = document.getElementById('doughnutChart');
if(doughnutEl && (totalIncome > 0 || totalExpense > 0)){
    new Chart(doughnutEl, {
        type: 'doughnut',
        plugins: [shadow3DPlugin],
        data: {
            labels: ['Pemasukan', 'Pengeluaran'],
            datasets: [{
                data: [totalIncome, totalExpense],
                backgroundColor: [
                    'rgba(34,197,94,0.85)',
                    'rgba(239,68,68,0.85)',
                ],
                borderColor: ['#22c55e','#ef4444'],
                borderWidth: 2,
                hoverBackgroundColor: ['#22c55e','#ef4444'],
                hoverOffset: 10,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label(ctx){
                            const val = ctx.parsed;
                            return ` Rp ${val.toLocaleString('id-ID')}`;
                        }
                    },
                    bodyFont: { family: 'Plus Jakarta Sans', weight: '700' },
                    padding: 10,
                    cornerRadius: 8,
                },
            },
            animation: {
                animateScale: true,
                duration: 1200,
                easing: 'easeOutBounce',
            },
        },
    });
}

// ── Line Chart ────────────────────────────
const lineEl = document.getElementById('lineChart');
if(lineEl){
    // Create gradient fills
    const ctx      = lineEl.getContext('2d');
    const gradInc  = ctx.createLinearGradient(0, 0, 0, 240);
    gradInc.addColorStop(0, 'rgba(34,197,94,0.28)');
    gradInc.addColorStop(1, 'rgba(34,197,94,0.02)');

    const gradExp  = ctx.createLinearGradient(0, 0, 0, 240);
    gradExp.addColorStop(0, 'rgba(239,68,68,0.2)');
    gradExp.addColorStop(1, 'rgba(239,68,68,0.01)');

    new Chart(lineEl, {
        type: 'line',
        plugins: [shadow3DPlugin],
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: 'Pemasukan',
                    data: chartIncome,
                    borderColor: '#22c55e',
                    backgroundColor: gradInc,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#22c55e',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.45,
                },
                {
                    label: 'Pengeluaran',
                    data: chartExpense,
                    borderColor: '#ef4444',
                    backgroundColor: gradExp,
                    borderWidth: 2.5,
                    borderDash: [6, 3],
                    pointBackgroundColor: '#ef4444',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.45,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label(ctx){
                            return ` ${ctx.dataset.label}: Rp ${ctx.parsed.y.toLocaleString('id-ID')}`;
                        },
                    },
                    bodyFont:  { family: 'Plus Jakarta Sans', weight: '700' },
                    titleFont: { family: 'Plus Jakarta Sans', weight: '700' },
                    padding: 12,
                    cornerRadius: 10,
                    displayColors: true,
                    boxWidth: 10,
                    boxHeight: 10,
                },
            },
            scales: {
                x: {
                    grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                    ticks: {
                        font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' },
                        color: '#94a3b8',
                    },
                    border: { display: false },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                    ticks: {
                        font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' },
                        color: '#94a3b8',
                        callback(value){
                            if(value >= 1000000) return 'Rp '+(value/1000000).toFixed(1)+'jt';
                            if(value >= 1000)    return 'Rp '+(value/1000).toFixed(0)+'rb';
                            return 'Rp '+value;
                        },
                    },
                    border: { display: false },
                },
            },
            animation: {
                duration: 1400,
                easing: 'easeOutQuart',
            },
        },
    });
}

/* ══════════════════════════════════════════
   AUTO-DISMISS FLASH ALERT
   ══════════════════════════════════════════ */
const flashAlert = document.getElementById('flashAlert');
if(flashAlert){
    setTimeout(()=>{
        flashAlert.style.transition = 'opacity 0.5s';
        flashAlert.style.opacity    = '0';
        setTimeout(()=>flashAlert.remove(), 500);
    }, 4000);
}

/* ══════════════════════════════════════════
   RESTORE MONTH/YEAR IF VALIDATION FAILS
   ══════════════════════════════════════════ */
@if($errors->any())
    // Reopen the modal that had errors
    @if(old('type') === 'income')
        openModal('incomeModal');
        document.querySelector('#incomeForm [name="name"]').value = '{{ old("name") }}';
    @elseif(old('type') === 'expense')
        openModal('expenseModal');
        document.querySelector('#expenseForm [name="name"]').value = '{{ old("name") }}';
    @endif
@endif
</script>
@endpush