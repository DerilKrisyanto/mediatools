<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Memo Pengiriman</title>
<style>
    @page { margin: 8mm 8mm; }
    * { box-sizing: border-box; }
    body {
        font-family: "DejaVu Sans", Helvetica, Arial, sans-serif;
        font-size: 8pt;
        color: #111;
        margin: 0;
        padding: 0;
    }

    .memo-block {
        width: 100%;
        border: 1.2pt solid #111;
        padding: 7px 12px;
    }

    .memo-title-row td { vertical-align: middle; padding-bottom: 3px; }
    .memo-title {
        font-weight: bold;
        font-size: 11pt;
        letter-spacing: 1px;
    }
    .memo-title-divider {
        border-bottom: 1pt solid #111;
        margin-bottom: 5px;
    }

    table.info { width: 100%; border-collapse: collapse; font-size: 8pt; }
    table.info td { padding: 1px 3px; vertical-align: top; line-height: 1; }
    td.label { width: 25%; }
    td.colon { width: 3%; }

    .section-label {
        font-weight: bold;
        text-decoration: underline;
        font-size: 8pt;
        margin: 5px 0 2px;
    }

    .checkbox-box {
        font-family: "DejaVu Sans", sans-serif;
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 1pt solid #111;
        text-align: center;
        line-height: 10px;
        font-size: 13pt;
        font-weight: bold;
        margin-right: 4px;
        vertical-align: middle;
    }

    .syarat-title { font-weight: bold; font-size: 8pt; margin-top: 10px; }
    .syarat-list { margin: 2px 0 3px 14px; padding: 0; font-size: 7.6pt; line-height: 1.3; }
    .syarat-list li { margin-bottom: 1px; }

    .ttd-table { width: 100%; margin-top: 3px; font-size: 8pt; }
    .ttd-table td { width: 50%; text-align: center; }

    .cut-line {
        text-align: center;
        font-size: 5pt;
        color: #777;
        margin: 1mm 0;
    }

    .page-break { page-break-after: always; }
</style>
</head>
<body>

@foreach($memos as $i => $memo)
    <div class="memo-block">
        <table class="memo-title-row" style="width:100%;">
            <tr>
                <td style="text-align:center;">
                    <span class="memo-title">MEMO PENGIRIMAN</span>
                </td>
                <td style="width:100px; text-align:right;">
                    <img src="{{ $logoPath }}" style="width:120px;">
                </td>
            </tr>
        </table>
        <div class="memo-title-divider"></div>

        <table class="info">
            <tr><td class="label">Telah Terima Dari</td><td class="colon">:</td><td>{{ $memo->diterima_dari }}</td></tr>
            <tr><td class="label">No Telepon</td><td class="colon">:</td><td>{{ $memo->telepon_dari ?: '' }}</td></tr>
            <tr><td class="label">No. Struk</td><td class="colon">:</td><td>{{ $memo->no_struk ?: '' }}</td></tr>
            <tr><td class="label">Berupa</td><td class="colon">:</td><td>{{ $memo->berupa_text ?: '' }}</td></tr>
        </table>

        <div class="section-label">Untuk Dikirimkan Ke</div>
        <table class="info">
            <tr><td class="label">Contact Person</td><td class="colon">:</td><td>{{ $memo->tujuan_contact_person }}</td></tr>
            <tr><td class="label">Alamat Tujuan</td><td class="colon">:</td><td>{{ $memo->tujuan_alamat }}</td></tr>
            <tr><td class="label">No Telepon</td><td class="colon">:</td><td>{{ $memo->tujuan_telepon ?: '' }}</td></tr>
            <tr><td class="label">Keterangan Lainnya</td><td class="colon">:</td><td>{{ $memo->keterangan_lainnya ?: '' }}</td></tr>
            <tr><td class="label">Hari / Jam / Tgl</td><td class="colon">:</td><td>{{ $memo->pengiriman_hari_tanggal ?: '' }}</td></tr>
            <tr>
                <td class="label">Biaya Kirim</td>
                <td class="colon">:</td>
                <td>{{ $memo->biaya_kirim ? 'Rp ' . number_format($memo->biaya_kirim, 0, ',', '.') : '' }}</td>
            </tr>
        </table>

        <div class="section-label">Instalasi</div>
        <table class="info">
            <tr>
                <td colspan="3">
                    <span class="checkbox-box">{!! $memo->instalasi ? '&#10004;' : '' !!}</span> YA
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <span class="checkbox-box">{!! !$memo->instalasi ? '&#10004;' : '' !!}</span> TIDAK
                </td>
            </tr>
            @if($memo->instalasi)
            <tr><td class="label">No. Struk Instalasi</td><td class="colon">:</td><td>{{ $memo->no_struk_instalasi ?: '' }}</td></tr>
            <tr><td class="label">Hari / Jam / Tgl</td><td class="colon">:</td><td>{{ $memo->instalasi_hari_tanggal ?: '' }}</td></tr>
            <tr>
                <td class="label">Biaya Instalasi</td>
                <td class="colon">:</td>
                <td>{{ $memo->biaya_instalasi ? 'Rp ' . number_format($memo->biaya_instalasi, 0, ',', '.') : '' }}</td>
            </tr>
            @endif
        </table>

        <div class="syarat-title">Syarat pengiriman:</div>
        <ol class="syarat-list">
            <li>Pengantaran mulai dari jam 10.00–16.00 WITA.</li>
            <li>Pengangkatan barang hanya sampai di Lantai Dasar.</li>
            <li>Produk dikirim dalam bentuk original packing.</li>
            <li>Jika ada kendala dapat menghubungi pihak store dengan nomor 0817-582-292.</li>
        </ol>

        <div style="font-size:7pt; margin-top:1px;">
            Tanggal : {{ \Carbon\Carbon::parse($memo->tanggal_memo)->format('d-m-Y') }}
        </div>

        <table class="ttd-table">
            <tr>
                <td>
                    Yang Menyerahkan,<br><br>
                    ( {{ $memo->tujuan_contact_person }} )<br>
                    Customer
                </td>
                <td>
                    Penerima,<br><br>
                    ( {{ $memo->customer_service ?: '..........................' }} )<br>
                    Customer Service
                </td>
            </tr>
        </table>
    </div>

    @if(!$loop->last)
        @if($i % 2 === 0)
            <div class="cut-line">&#9986; &nbsp; potong di sini &nbsp; &#9986;</div>
        @else
            <div class="page-break"></div>
        @endif
    @endif
@endforeach

</body>
</html>