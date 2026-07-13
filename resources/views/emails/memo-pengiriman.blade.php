<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memo Pengiriman — MediaTools</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Plus Jakarta Sans',Helvetica,Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 16px;">
    <tr>
        <td align="center">
            <table width="100%" style="max-width:520px;background:#071a1a;border-radius:24px;overflow:hidden;border:1px solid rgba(163,230,53,0.15);">

                {{-- Header --}}
                <tr>
                    <td style="padding:36px 40px 24px;border-bottom:1px solid rgba(255,255,255,0.06);">
                        <span style="font-size:22px;font-weight:800;color:#ffffff;letter-spacing:-0.5px;">
                            MEDIA<span style="color:#a3e635;">TOOLS.</span>
                        </span>
                    </td>
                </tr>

                {{-- Body --}}
                <tr>
                    <td style="padding:36px 40px;">
                        <p style="margin:0 0 16px;font-size:22px;font-weight:800;color:#f0fdf4;line-height:1.3;">
                            Memo Pengiriman
                        </p>

                        <p style="margin:0 0 24px;font-size:14px;color:#9ca3af;line-height:1.6;">
                            Halo <strong style="color:#f0fdf4;">{{ $memo->tujuan_contact_person }}</strong>,<br>
                            Anda menerima memo pengiriman berikut. Dokumen lengkap terlampir dalam format PDF pada email ini.
                        </p>

                        {{-- Ringkasan Memo --}}
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                            <tr>
                                <td style="background:rgba(163,230,53,0.06);border:1px solid rgba(163,230,53,0.2);border-radius:14px;padding:20px 22px;">
                                    <p style="margin:0 0 6px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.1em;">No. Struk</p>
                                    <p style="margin:0 0 14px;font-size:15px;font-weight:700;color:#f0fdf4;">{{ $memo->no_struk ?: '' }}</p>

                                    <p style="margin:0 0 6px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.1em;">Diterima Dari</p>
                                    <p style="margin:0 0 14px;font-size:14px;color:#f0fdf4;">{{ $memo->diterima_dari }}</p>

                                    <p style="margin:0 0 6px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.1em;">Tanggal Pengiriman</p>
                                    <p style="margin:0;font-size:14px;color:#f0fdf4;">{{ $memo->instalasi_hari_tanggal ?: ''}}</p>

                                    <p style="margin:0 0 6px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.1em;">Dikirim Ke</p>
                                    <p style="margin:0;font-size:14px;color:#f0fdf4;">{{ $memo->tujuan_contact_person }}</p>

                                    <p style="margin:0 0 6px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.1em;">Nama PIC</p>
                                    <p style="margin:0;font-size:14px;color:#f0fdf4;">{{ $memo->nama_sales }}</p>
                                </td>
                            </tr>
                        </table>

                        {{-- Info Lampiran --}}
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                            <tr>
                                <td style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:12px;padding:14px 18px;">
                                    <p style="margin:0;font-size:12px;color:#9ca3af;line-height:1.5;">
                                        📎 <strong style="color:#f0fdf4;">Lampiran:</strong> Cetakan PDF memo pengiriman ini tersedia pada email ini, silakan buka atau unduh untuk melihat detail lengkap.
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0;font-size:13px;color:#6b7280;line-height:1.6;">
                            Jika Anda tidak mengenali pengiriman ini, abaikan email ini atau hubungi pengirim langsung di nomor 0817-582-292.
                        </p>
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="padding:20px 40px;border-top:1px solid rgba(255,255,255,0.06);">
                        <p style="margin:0;font-size:11px;color:#374151;text-align:center;line-height:1.6;">
                            © {{ date('Y') }} MediaTools Indonesia ·
                            <a href="https://mediatools.cloud" style="color:#6b7280;text-decoration:none;">mediatools.cloud</a>
                            <br>Email ini dikirim otomatis, mohon tidak membalas.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>