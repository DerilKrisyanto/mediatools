@extends('layouts.app')

@section('og_image', 'invoice')
@section('title', 'Invoice Generator Gratis — Buat Invoice PDF Profesional Online | MediaTools')
@section('meta_description', 'Buat invoice atau tagihan profesional dalam 2 menit. 3 template siap pakai, kalkulasi PPN & diskon otomatis, download PDF gratis tanpa daftar. Terbaik untuk freelancer & UMKM Indonesia.')
@section('meta_keywords', 'invoice generator gratis, buat invoice, invoice maker free, invoice pdf online, invoice creator, buat tagihan online, template invoice pdf, invoice freelancer, invoice template gratis, invoice generator indonesia, invoice profesional, buat tagihan pdf, invoice online gratis, nota tagihan digital, invoice bisnis')
@include('seo.invoice')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/invoice.css') }}">
<link rel="stylesheet" href="{{ asset('css/ads.css') }}">
@endpush

@section('content')

<div class="invoice-shell">

    {{-- ═══ TOP BAR (Editor Controls) ═══ --}}
    <div class="invoice-topbar no-print">

        {{-- Left: Title + Template Selector --}}
        <div style="display:flex;flex-direction:column;gap:10px;">
            <h2 class="invoice-topbar-title">
                <i class="fa-solid fa-file-invoice" style="font-size:.9em;"></i>
                Invoice <span>Generator</span>
            </h2>

            {{-- Template Selector --}}
            <div class="tpl-selector">
                <button class="tpl-btn active" data-tpl="1" onclick="setTemplate(1)">
                    <span class="tpl-dot tpl-dot-1"></span>
                    Klasik
                </button>
                <button class="tpl-btn" data-tpl="2" onclick="setTemplate(2)">
                    <span class="tpl-dot tpl-dot-2"></span>
                    Modern
                </button>
                <button class="tpl-btn" data-tpl="3" onclick="setTemplate(3)">
                    <span class="tpl-dot tpl-dot-3"></span>
                    Elegan
                </button>
                <button class="btn-download" data-tpl="4" onclick="downloadPDF(4)">
                    <span class="fa-solid fa-file-pdf"></span>
                    Unduh PDF
                </button>
            </div>
        </div>
    </div>

    {{-- ═══ SLOT 1: HEADER BANNER 728×90 ═══ --}}
    <div class="ads-slot-header no-print">
        @include('components.ads.banner-header')
    </div>

    {{-- ═══ PAGE FIT WARNING ═══ --}}
    <div class="page-warning no-print" id="page-warning">
        <div class="page-warning-inner">
            <i class="fa-solid fa-triangle-exclamation"></i>
            Konten melebihi 1 halaman A4. Kurangi jumlah item atau perkecil catatan agar hasil PDF rapi.
        </div>
    </div>

    {{-- ═══ MAIN LAYOUT: Canvas + Sidebar ═══ --}}
    <div class="invoice-main-layout">
        {{-- Kolom kiri: A4 Canvas --}}
        <div class="invoice-canvas-col">

            {{-- ════ A4 PAPER ════ --}}
            <div class="invoice-canvas">
                <div id="invoice-content" data-template="1">

                    {{-- ── HEADER ── --}}
                    <div class="inv-header-wrap">
                        <div class="inv-header-row">

                            {{-- Brand (Logo + Company Name) --}}
                            <div class="inv-brand-area">

                                {{-- Logo --}}
                                <div class="inv-logo-area" id="inv-logo-wrap">
                                    <input type="file" id="logoUpload" accept="image/*" class="hidden no-print" style="display:none;">

                                    <div id="inv-logo-placeholder" class="inv-logo-placeholder no-print" title="Klik untuk upload logo">
                                        <i class="fa-solid fa-image"></i>
                                        <span>Upload Logo</span>
                                    </div>

                                    <div style="position:relative;display:inline-block;">
                                        <img id="inv-logo-preview" class="inv-logo-preview" alt="Logo Perusahaan">
                                        <button id="inv-logo-remove" class="inv-logo-remove no-print" title="Hapus logo">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Company Identity --}}
                                <div>
                                    <div contenteditable="true"
                                        class="inv-biz-name"
                                        id="inv-biz-name"
                                        spellcheck="false">[NAMA PERUSAHAAN]</div>
                                    <div contenteditable="true"
                                        class="inv-biz-tagline"
                                        spellcheck="false">Tagline atau motto perusahaan Anda di sini</div>
                                </div>
                            </div>

                            {{-- Invoice Title & Number --}}
                            <div class="inv-title-area">
                                <div class="inv-label">Invoice</div>
                                <div contenteditable="true"
                                    class="inv-number-field"
                                    id="inv-number"
                                    spellcheck="false">#INV/{{ date('Ymd') }}/001</div>
                            </div>

                        </div>
                    </div>
                    {{-- /HEADER --}}

                    {{-- ── BODY ── --}}
                    <div class="inv-body">

                        {{-- Billing Info --}}
                        <div class="inv-billing-row">

                            {{-- Bill To --}}
                            <div>
                                <div class="inv-section-label">Tagihan Untuk:</div>
                                <div contenteditable="true"
                                    class="inv-client-name"
                                    spellcheck="false">Nama Klien / Perusahaan</div>
                                <div contenteditable="true"
                                    class="inv-client-address"
                                    spellcheck="false">Alamat lengkap klien,
                                                        Kota, Kode Pos
                                                        Email: klien@email.com
                                </div>
                            </div>

                            {{-- Meta: Date, Due, Customer ID --}}
                            <div>
                                <div class="inv-section-label">Detail Invoice:</div>
                                <div class="inv-meta-row">
                                    <span class="inv-meta-label">Tanggal</span>
                                    <input type="text"
                                        class="inv-meta-input"
                                        value="{{ date('d/m/Y') }}">
                                </div>
                                <div class="inv-meta-row">
                                    <span class="inv-meta-label">Jatuh Tempo</span>
                                    <input type="text"
                                        class="inv-meta-input"
                                        value="{{ date('d/m/Y', strtotime('+14 days')) }}">
                                </div>
                                <div class="inv-meta-row">
                                    <span class="inv-meta-label">ID Pelanggan</span>
                                    <div contenteditable="true"
                                        class="inv-meta-val"
                                        style="font-size:11px;">CUST-{{ rand(1000,9999) }}</div>
                                </div>
                                <div class="inv-meta-row">
                                    <span class="inv-meta-label">Status</span>
                                    <span style="font-size:9px;font-weight:800;padding:2px 8px;background:rgba(34,197,94,0.1);color:#16a34a;border-radius:99px;border:1px solid rgba(34,197,94,0.2);">
                                        BELUM DIBAYAR
                                    </span>
                                </div>
                            </div>

                        </div>
                        {{-- /Billing Info --}}

                        {{-- Items Table --}}
                        <table class="inv-table">
                            <thead>
                                <tr>
                                    <th class="center" style="width:28px;">#</th>
                                    <th>Deskripsi Layanan / Produk</th>
                                    <th class="center" style="width:52px;">Qty</th>
                                    <th class="right"  style="width:120px;">Harga (Rp)</th>
                                    <th class="right"  style="width:110px;">Total (Rp)</th>
                                    <th class="no-print" style="width:28px;"></th>
                                </tr>
                            </thead>
                            <tbody id="item-list">
                                {{-- Rendered by invoice.js --}}
                            </tbody>
                        </table>

                        <button type="button"
                                onclick="addItem()"
                                class="inv-add-row-btn no-print">
                            <i class="fa-solid fa-plus-circle"></i>
                            Tambah Item
                        </button>

                        {{-- Summary Row --}}
                        <div class="inv-summary-row">

                            {{-- Left: Notes + Payment --}}
                            <div>
                                {{-- Notes --}}
                                <div class="inv-section-label">Catatan:</div>
                                <div id="note-list">{{-- Rendered by invoice.js --}}</div>
                                <button type="button" onclick="addNote()" class="inv-add-small-btn no-print">
                                    <i class="fa-solid fa-plus"></i> Tambah Catatan
                                </button>

                                {{-- Payment Info --}}
                                <div class="inv-payment-box" style="margin-top:12px;">
                                    <div class="inv-section-label" style="margin-bottom:10px;">Info Pembayaran:</div>
                                    <div id="payment-list">{{-- Rendered by invoice.js --}}</div>
                                    <button type="button" onclick="addPayment()" class="inv-add-small-btn no-print">
                                        <i class="fa-solid fa-plus"></i> Tambah Rekening
                                    </button>
                                </div>
                            </div>

                            {{-- Right: Totals --}}
                            <div>
                                <div class="inv-total-row">
                                    <span class="inv-total-label">Subtotal</span>
                                    <span class="inv-total-val">Rp <span id="inv-subtotal">0</span></span>
                                </div>

                                <div class="inv-tax-disc-row">
                                    <div class="inv-tax-disc-label">
                                        Diskon
                                        <input type="number"
                                            id="discountPercent"
                                            class="inv-num-input no-print"
                                            value="0" min="0" max="100">
                                        <span class="no-print" style="font-size:10px;color:#6b7280;">%</span>
                                    </div>
                                    <span style="font-weight:800;color:#ef4444;font-size:11px;">
                                        − Rp <span id="inv-discount-val">0</span>
                                    </span>
                                </div>

                                <div class="inv-tax-disc-row" style="border-bottom:1px solid #f1f5f9;padding-bottom:8px;">
                                    <div class="inv-tax-disc-label">
                                        PPN/Pajak
                                        <input type="number"
                                            id="taxPercent"
                                            class="inv-num-input no-print"
                                            value="11" min="0" max="100">
                                        <span class="no-print" style="font-size:10px;color:#6b7280;">%</span>
                                    </div>
                                    <span style="font-weight:800;color:#374151;font-size:11px;">
                                        Rp <span id="inv-tax-val">0</span>
                                    </span>
                                </div>

                                <div class="inv-grand-total-row">
                                    <span class="inv-grand-label">Total Akhir</span>
                                    <span class="inv-grand-val">
                                        <small style="font-size:13px;font-weight:700;opacity:.7;margin-right:2px;">Rp</small><span id="inv-grand-total">0</span>
                                    </span>
                                </div>

                                {{-- Terbilang --}}
                                <div style="margin-top:6px;padding:6px 8px;background:#f8fafc;border-radius:6px;border:1px solid #e2e8f0;">
                                    <span style="font-size:9px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.08em;">Terbilang: </span>
                                    <span style="font-size:9px;color:#64748b;font-style:italic;" id="inv-terbilang">—</span>
                                </div>
                            </div>

                        </div>
                        {{-- /Summary --}}

                    </div>
                    {{-- /BODY --}}

                    {{-- ── FOOTER ── --}}
                    <div class="inv-footer">
                        <p class="inv-footer-thanks">Terima kasih atas kepercayaan dan kerja sama Anda!</p>
                        <div contenteditable="true"
                            class="inv-footer-contact"
                            spellcheck="false">
                            Jl. Nama Jalan No. 123, Kota, Provinsi · Telp: 0812-3456-7890 · Email: admin@perusahaan.com
                        </div>
                    </div>
                    {{-- /FOOTER --}}

                </div>
                {{-- /A4 PAPER --}}
            </div>
            {{-- /CANVAS --}}

        </div>
        {{-- /Kolom kiri --}}

        {{-- ═══ SLOT 3: RESULT BANNER 300×250 ═══ --}}
        <div class="ads-slot-result no-print">
            @include('components.ads.banner-result')
        </div>

        {{-- ═══ SLOT 4: NATIVE BANNER ═══ --}}
        <div class="ads-slot-native no-print">
            @include('components.ads.banner-content')
        </div>
 
    </div>
    {{-- /MAIN LAYOUT --}}

</div>
{{-- /SHELL --}}

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="{{ asset('js/invoice.js') }}"></script>
<script>
/* Terbilang (Indonesian number to words) — lightweight version */
(function () {
    const satuan = ['','satu','dua','tiga','empat','lima','enam','tujuh','delapan','sembilan',
                    'sepuluh','sebelas','dua belas','tiga belas','empat belas','lima belas',
                    'enam belas','tujuh belas','delapan belas','sembilan belas'];
    const puluhan = ['','','dua puluh','tiga puluh','empat puluh','lima puluh',
                     'enam puluh','tujuh puluh','delapan puluh','sembilan puluh'];

    function spell(n) {
        if (n < 0)       return 'minus ' + spell(-n);
        if (n === 0)     return 'nol';
        if (n < 20)      return satuan[n];
        if (n < 100)     return puluhan[Math.floor(n/10)] + (n % 10 ? ' ' + satuan[n % 10] : '');
        if (n < 200)     return 'seratus' + (n % 100 ? ' ' + spell(n % 100) : '');
        if (n < 1000)    return satuan[Math.floor(n/100)] + ' ratus' + (n % 100 ? ' ' + spell(n % 100) : '');
        if (n < 2000)    return 'seribu' + (n % 1000 ? ' ' + spell(n % 1000) : '');
        if (n < 1000000) return spell(Math.floor(n/1000)) + ' ribu' + (n % 1000 ? ' ' + spell(n % 1000) : '');
        if (n < 1e9)     return spell(Math.floor(n/1e6)) + ' juta' + (n % 1e6 ? ' ' + spell(n % 1e6) : '');
        if (n < 1e12)    return spell(Math.floor(n/1e9)) + ' miliar' + (n % 1e9 ? ' ' + spell(n % 1e9) : '');
        return n.toLocaleString('id-ID');
    }

    const observer = new MutationObserver(() => {
        const raw = document.getElementById('inv-grand-total')?.textContent?.replace(/\./g, '') || '0';
        const num = parseInt(raw.replace(/[^0-9]/g, '')) || 0;
        const el  = document.getElementById('inv-terbilang');
        if (el) el.textContent = num > 0
            ? spell(num).replace(/\b\w/g, c => c.toUpperCase()) + ' Rupiah'
            : '—';
    });

    document.addEventListener('DOMContentLoaded', () => {
        const target = document.getElementById('inv-grand-total');
        if (target) observer.observe(target, { childList: true, characterData: true, subtree: true });
    });
})();
</script>
@endpush