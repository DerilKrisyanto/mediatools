@extends('layouts.app')

@section('title', 'Laporan Keuangan | MediaTools')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/finance_cetak.css') }}">
<link rel="stylesheet" href="{{ asset('css/ads.css') }}">
@endpush

@section('content')
<div class="page-wrap">
    {{-- ═══ ADS SLOT: HEADER ═══ --}}
    <div class="ads-slot-header no-print" style="margin-bottom:10px;">@include('components.ads.banner-header')</div>

    {{-- ── Print Buttons (screen only) ── --}}
    <div class="print-btn-wrap">
        <button onclick="window.close()" class="print-btn secondary">
            ← Kembali ke Dashboard
        </button>
        <button onclick="window.print()" class="print-btn primary" style="background:#a3e635;color:#0f172a;border-color:#a3e635;font-weight:700;">
            <i class="fa-solid fa-print"></i>
            Cetak / Simpan PDF
        </button>
    </div>

    {{-- ── Report Header ── --}}
    <div class="report-header">
        <div>
            <div class="report-brand">
                <div class="logo-box">M</div>
                <span class="brand-name">MediaTools · Pencatatan Keuangan</span>
            </div>
            <div class="report-title">{{ $typeLabel }}</div>
            <div class="report-period">{{ $periodLabel }}</div>
        </div>
        <div class="report-meta">
            <div class="printed-on">Dicetak pada:</div>
            {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}<br>
            {{ now()->format('H:i') }} WIB<br><br>
            <strong>{{ auth()->user()->name }}</strong>
        </div>
    </div>

    {{-- ── Summary Boxes ── --}}
    <div class="summary-grid">
        <div class="summary-box income">
            <div class="sum-label">↑ Total Pemasukan</div>
            <div class="sum-amount">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
            @php $incCount = $transactions->where('type','income')->count(); @endphp
            <div class="sum-count">{{ $incCount }} transaksi pemasukan</div>
        </div>
        <div class="summary-box expense">
            <div class="sum-label">↓ Total Pengeluaran</div>
            <div class="sum-amount">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
            @php $expCount = $transactions->where('type','expense')->count(); @endphp
            <div class="sum-count">{{ $expCount }} transaksi pengeluaran</div>
        </div>
        <div class="summary-box balance">
            <div class="sum-label">⚖ Saldo Bersih</div>
            <div class="sum-amount" style="{{ $balance < 0 ? 'color:var(--red)' : '' }}">
                {{ $balance >= 0 ? '' : '-' }}Rp {{ number_format(abs($balance), 0, ',', '.') }}
            </div>
            <div class="sum-count" style="{{ $balance < 0 ? 'color:#b91c1c' : '' }}">
                {{ $balance >= 0 ? '✓ Surplus' : '⚠ Defisit' }}
            </div>
        </div>
    </div>

    {{-- ══════════════════════
         TABLE: ALL / INCOME / EXPENSE
         ══════════════════════ --}}

    @if($transactions->isEmpty())
    <div style="text-align:center;padding:48px;color:var(--light);border:1px dashed var(--border);border-radius:14px;">
        <div style="font-size:2rem;margin-bottom:12px;opacity:0.4;">📂</div>
        <strong style="color:var(--muted);">Tidak ada data transaksi</strong><br>
        <span style="font-size:0.8rem;">untuk periode yang dipilih.</span>
    </div>
    @else

    {{-- If type = 'all', show two sections; otherwise show one --}}
    @php
    $incomeRows  = $transactions->where('type','income');
    $expenseRows = $transactions->where('type','expense');
    @endphp

    {{-- ── INCOME TABLE ── --}}
    @if($type !== 'expense' && $incomeRows->isNotEmpty())
    <div class="report-table-wrap" style="margin-bottom:{{ $type === 'all' ? '24px' : '0' }};">
        @if($type === 'all')
        <div class="section-title">↑ PEMASUKAN</div>
        @endif
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width:36px;">#</th>
                    <th>Tanggal</th>
                    <th>Nama / Sumber</th>
                    @if($type === 'all')<th>Jenis</th>@endif
                    <th>Qty</th>
                    <th>Harga/Item</th>
                    <th>Total</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($incomeRows as $i => $tx)
                <tr>
                    <td style="color:var(--light);font-weight:700;">{{ $i+1 }}</td>
                    <td style="white-space:nowrap;font-weight:600;color:var(--muted);">
                        {{ $tx->transaction_date->locale('id')->isoFormat('D MMM YYYY') }}
                    </td>
                    <td style="font-weight:700;">{{ $tx->name }}</td>
                    @if($type === 'all')
                    <td><span class="type-badge income">↑ Masuk</span></td>
                    @endif
                    <td style="text-align:right;color:var(--muted);">
                        {{ number_format($tx->quantity, $tx->quantity == intval($tx->quantity) ? 0 : 2, ',', '.') }}
                    </td>
                    <td style="text-align:right;color:var(--muted);">
                        Rp {{ number_format($tx->price_per_item, 0, ',', '.') }}
                    </td>
                    <td class="amount-cell income">+Rp {{ number_format($tx->total_amount, 0, ',', '.') }}</td>
                    <td style="color:var(--light);font-size:0.78rem;">{{ $tx->notes ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="{{ $type === 'all' ? 6 : 5 }}" style="font-weight:700;color:var(--muted);">
                        SUBTOTAL PEMASUKAN ({{ $incomeRows->count() }} transaksi)
                    </td>
                    <td style="text-align:right;font-weight:800;color:var(--green);white-space:nowrap;">
                        +Rp {{ number_format($totalIncome, 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- ── EXPENSE TABLE ── --}}
    @if($type !== 'income' && $expenseRows->isNotEmpty())
    <div class="report-table-wrap">
        @if($type === 'all')
        <div class="section-title">↓ PENGELUARAN</div>
        @endif
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width:36px;">#</th>
                    <th>Tanggal</th>
                    <th>Nama / Jenis</th>
                    @if($type === 'all')<th>Jenis</th>@endif
                    <th>Qty</th>
                    <th>Harga/Item</th>
                    <th>Total</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenseRows as $i => $tx)
                <tr>
                    <td style="color:var(--light);font-weight:700;">{{ $i+1 }}</td>
                    <td style="white-space:nowrap;font-weight:600;color:var(--muted);">
                        {{ $tx->transaction_date->locale('id')->isoFormat('D MMM YYYY') }}
                    </td>
                    <td style="font-weight:700;">{{ $tx->name }}</td>
                    @if($type === 'all')
                    <td><span class="type-badge expense">↓ Keluar</span></td>
                    @endif
                    <td style="text-align:right;color:var(--muted);">
                        {{ number_format($tx->quantity, $tx->quantity == intval($tx->quantity) ? 0 : 2, ',', '.') }}
                    </td>
                    <td style="text-align:right;color:var(--muted);">
                        Rp {{ number_format($tx->price_per_item, 0, ',', '.') }}
                    </td>
                    <td class="amount-cell expense">-Rp {{ number_format($tx->total_amount, 0, ',', '.') }}</td>
                    <td style="color:var(--light);font-size:0.78rem;">{{ $tx->notes ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="{{ $type === 'all' ? 6 : 5 }}" style="font-weight:700;color:var(--muted);">
                        SUBTOTAL PENGELUARAN ({{ $expenseRows->count() }} transaksi)
                    </td>
                    <td style="text-align:right;font-weight:800;color:var(--red);white-space:nowrap;">
                        -Rp {{ number_format($totalExpense, 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- ── Grand Total (only for 'all') ── --}}
    @if($type === 'all')
    <div style="background:{{ $balance >= 0 ? '#f0fdf4' : '#fef2f2' }};
                border:1.5px solid {{ $balance >= 0 ? '#bbf7d0' : '#fecaca' }};
                border-radius:12px;padding:16px 20px;
                display:flex;justify-content:space-between;align-items:center;
                margin-bottom:20px;">
        <div>
            <div style="font-size:0.68rem;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;
                        color:{{ $balance >= 0 ? '#15803d' : '#b91c1c' }};margin-bottom:4px;">
                SALDO BERSIH PERIODE INI
            </div>
            <div style="font-size:0.8rem;color:var(--muted);">
                {{ $transactions->count() }} total transaksi · {{ $periodLabel }}
            </div>
        </div>
        <div style="font-size:1.5rem;font-weight:800;letter-spacing:-0.02em;
                    color:{{ $balance >= 0 ? 'var(--green)' : 'var(--red)' }};">
            {{ $balance >= 0 ? '+' : '-' }}Rp {{ number_format(abs($balance), 0, ',', '.') }}
        </div>
    </div>
    @endif

    @endif {{-- end $transactions->isEmpty() --}}

    {{-- ── Report Footer ── --}}
    <div class="report-footer">
        Laporan digenerate otomatis oleh <strong>MediaTools — Pencatatan Keuangan UMKM</strong><br>
        Dokumen ini bersifat konfidensial dan hanya untuk penggunaan internal.
    </div>

    {{-- ═══ ADS SLOT: HEADER ═══ --}}
    <div class="ads-slot-header no-print" style="margin-bottom:5px;">@include('components.ads.banner-header')</div>
    <div class="ads-slot-header no-print" style="margin-bottom:5px;">@include('components.ads.banner-header')</div>
    {{-- ═══ ADS SLOT: NATIVE BANNER ═══ --}}
    <div class="ads-slot-native no-print">@include('components.ads.banner-content')</div>

</div><!-- /page-wrap -->

@endsection

@push('scripts')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<script>
    // Auto-trigger print dialog (uncomment if desired)
    // window.addEventListener('load', () => setTimeout(() => window.print(), 500));
</script>
@endpush