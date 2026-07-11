<table border="1" cellspacing="0" cellpadding="4">
    <thead>
        <tr style="background:#e2e8f0; font-weight:bold;">
            <th>No. Memo</th>
            <th>Diterima Dari</th>
            <th>No. Struk</th>
            <th>No Telepon (Dari)</th>
            <th>Berupa</th>
            <th>Contact Person Tujuan</th>
            <th>Alamat Tujuan</th>
            <th>No Telepon (Tujuan)</th>
            <th>Nama CS</th>
            <th>Nama Sales</th>
            <th>Tgl Pengiriman</th>
            <th>Biaya Kirim (Rp)</th>
            <th>Instalasi</th>
            <th>No. Struk Instalasi</th>
            <th>Tgl Instalasi</th>
            <th>Biaya Instalasi (Rp)</th>
            <th>Nama Customer Service</th>
            <th>Tgl Memo</th>
        </tr>
    </thead>
    <tbody>
        @foreach($memos as $m)
        <tr>
            <td>{{ $m->nomor_memo }}</td>
            <td>{{ $m->diterima_dari }}</td>
            <td>{{ $m->no_struk ?: '-' }}</td>
            <td>{{ $m->telepon_dari ?: '-' }}</td>
            <td>{{ $m->berupa }}</td>
            <td>{{ $m->tujuan_contact_person }}</td>
            <td>{{ $m->tujuan_alamat }}</td>
            <td>{{ $m->tujuan_telepon ?: '-' }}</td>
            <td>{{ $m->customer_service ?: '-' }}</td>
            <td>{{ $m->nama_sales ?: '-' }}</td>
            <td>{{ $m->pengiriman_hari_tanggal ?: '-' }}</td>
            <td>{{ (int) ($m->biaya_kirim ?? 0) }}</td>
            <td>{{ $m->instalasi ? 'Ya' : 'Tidak' }}</td>
            <td>{{ $m->no_struk_instalasi ?: '-' }}</td>
            <td>{{ $m->instalasi_hari_tanggal ?: '-' }}</td>
            <td>{{ (int) ($m->biaya_instalasi ?? 0) }}</td>
            <td>{{ $m->instalasi_hari_tanggal ?: '-' }}</td>
            <td>{{ \Carbon\Carbon::parse($m->tanggal_memo)->format('d-m-Y') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>