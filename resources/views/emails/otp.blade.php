<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP MediaTools</title>
    <!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Plus Jakarta Sans',Helvetica,Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 16px;">
    <tr>
        <td align="center">
            <table width="100%" style="max-width:520px;background:#071a1a;border-radius:24px;overflow:hidden;border:1px solid rgba(163,230,53,0.15);">

                {{-- Header --}}
                <tr>
                    <td style="padding:36px 40px 24px;border-bottom:1px solid rgba(255,255,255,0.06);">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>
                                    <span style="font-size:22px;font-weight:800;color:#ffffff;letter-spacing:-0.5px;">
                                        MEDIA<span style="color:#a3e635;">TOOLS.</span>
                                    </span>
                                </td>
                                <td align="right">
                                    <span style="display:inline-block;padding:4px 12px;background:rgba(163,230,53,0.1);border:1px solid rgba(163,230,53,0.2);border-radius:99px;font-size:10px;font-weight:700;color:#a3e635;letter-spacing:0.1em;text-transform:uppercase;">
                                        @if($purpose === 'login') Login OTP @else Verifikasi @endif
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Body --}}
                <tr>
                    <td style="padding:36px 40px;">

                        {{-- Greeting --}}
                        <p style="margin:0 0 16px;font-size:24px;font-weight:800;color:#f0fdf4;line-height:1.3;">
                            @if($purpose === 'login')
                                Konfirmasi Login Anda
                            @else
                                Selamat Datang di MediaTools! 🎉
                            @endif
                        </p>

                        <p style="margin:0 0 28px;font-size:14px;color:#9ca3af;line-height:1.6;">
                            @if($recipientName) Halo, <strong style="color:#f0fdf4;">{{ $recipientName }}</strong>! <br> @endif
                            Gunakan kode OTP berikut untuk 
                            @if($purpose === 'login') masuk ke akun @else verifikasi akun @endif
                            Anda. Kode berlaku selama <strong style="color:#f0fdf4;">{{ $expiryMinutes }} menit</strong>.
                        </p>

                        {{-- OTP Code --}}
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
                            <tr>
                                <td align="center" style="background:rgba(163,230,53,0.06);border:1px solid rgba(163,230,53,0.2);border-radius:16px;padding:28px;">
                                    <p style="margin:0 0 8px;font-size:11px;font-weight:700;color:#6b7280;letter-spacing:0.15em;text-transform:uppercase;">Kode OTP Anda</p>
                                    {{-- Split each digit --}}
                                    <div style="display:inline-block;">
                                        @foreach(str_split($otp) as $i => $digit)
                                        <span style="display:inline-block;width:44px;height:54px;line-height:54px;text-align:center;font-size:32px;font-weight:800;color:#a3e635;background:rgba(255,255,255,0.04);border:1px solid rgba(163,230,53,0.2);border-radius:10px;margin:0 3px;font-family:monospace;">{{ $digit }}</span>
                                        @endforeach
                                    </div>
                                    <p style="margin:12px 0 0;font-size:11px;color:#6b7280;">
                                        <span style="color:#ef4444;">⏱</span> Kedaluwarsa dalam {{ $expiryMinutes }} menit
                                    </p>
                                </td>
                            </tr>
                        </table>

                        {{-- Warning --}}
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                            <tr>
                                <td style="background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.15);border-radius:12px;padding:14px 18px;">
                                    <p style="margin:0;font-size:12px;color:#fca5a5;line-height:1.5;">
                                        🔒 <strong>Jangan bagikan kode ini kepada siapapun.</strong>
                                        Tim MediaTools tidak pernah meminta kode OTP Anda.
                                        Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini.
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0;font-size:13px;color:#6b7280;line-height:1.6;">
                            Butuh bantuan? Hubungi kami di
                            <a href="mailto:halo@mediatools.cloud" style="color:#a3e635;text-decoration:none;">halo@mediatools.cloud</a>
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