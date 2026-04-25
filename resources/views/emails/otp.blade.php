<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi MediaTools</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #040f0f; color: #f0fdf4; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 40px 20px; }
        .card {
            background: #0b2323;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.06);
        }
        .header {
            background: linear-gradient(135deg, #0e2a2a 0%, #0b2323 100%);
            padding: 36px 40px 28px;
            text-align: center;
            border-bottom: 1px solid rgba(163,230,53,0.12);
        }
        .logo {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }
        .logo-icon {
            width: 40px; height: 40px;
            background: rgba(163,230,53,0.15);
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .logo-text { font-size: 18px; font-weight: 800; color: #f0fdf4; letter-spacing: -0.02em; }
        .logo-text span { color: #a3e635; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 16px; color: #9ca3af; margin-bottom: 20px; }
        .greeting strong { color: #f0fdf4; }
        .otp-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #6b7280;
            text-align: center;
            margin-bottom: 12px;
        }
        .otp-box {
            background: rgba(163,230,53,0.06);
            border: 2px solid rgba(163,230,53,0.25);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            margin-bottom: 24px;
        }
        .otp-code {
            font-size: 44px;
            font-weight: 900;
            letter-spacing: 14px;
            color: #a3e635;
            font-variant-numeric: tabular-nums;
            line-height: 1;
        }
        .otp-expiry {
            font-size: 12px;
            color: #6b7280;
            margin-top: 10px;
        }
        .info-text { font-size: 14px; color: #9ca3af; line-height: 1.7; }
        .warning-box {
            background: rgba(239,68,68,0.07);
            border: 1px solid rgba(239,68,68,0.15);
            border-radius: 12px;
            padding: 14px 18px;
            margin-top: 20px;
        }
        .warning-box p { font-size: 12px; color: #f87171; line-height: 1.6; }
        .footer {
            padding: 20px 40px 28px;
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.04);
        }
        .footer p { font-size: 11px; color: #4b5563; line-height: 1.7; }
        .footer a { color: #a3e635; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">

        {{-- Header --}}
        <div class="header">
            <div class="logo">
                <span class="logo-icon">⚡</span>
                <span class="logo-text">MEDIA<span>TOOLS.</span></span>
            </div>
        </div>

        {{-- Body --}}
        <div class="body">
            <p class="greeting">Halo, <strong>{{ $name }}</strong> 👋</p>
            <p class="info-text" style="margin-bottom:24px;">
                Terima kasih telah mendaftar di <strong style="color:#f0fdf4;">MediaTools</strong>. Gunakan kode verifikasi di bawah ini untuk menyelesaikan proses pendaftaran Anda:
            </p>

            <p class="otp-label">Kode Verifikasi (OTP)</p>
            <div class="otp-box">
                <div class="otp-code">{{ $otp }}</div>
                <p class="otp-expiry">⏱ Berlaku selama <strong style="color:#f0fdf4;">10 menit</strong></p>
            </div>

            <p class="info-text">
                Masukkan kode di atas pada halaman verifikasi MediaTools. Jika Anda tidak melakukan pendaftaran, abaikan email ini dengan aman.
            </p>

            <div class="warning-box">
                <p>🔒 <strong>Jangan bagikan kode ini kepada siapapun</strong> — termasuk tim MediaTools. Kami tidak pernah meminta kode OTP Anda.</p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p>Email ini dikirim secara otomatis. Jangan membalas email ini.<br>
            &copy; {{ date('Y') }} MediaTools · <a href="https://mediatools.cloud">mediatools.cloud</a></p>
        </div>

    </div>
</div>
</body>
</html>
