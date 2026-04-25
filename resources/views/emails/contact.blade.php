<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Pesan Kontak — MediaTools</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0a0a0b;color:#fafafa;-webkit-font-smoothing:antialiased}
  .wrapper{max-width:580px;margin:0 auto;padding:32px 16px}
  .card{background:#111113;border:1px solid rgba(255,255,255,0.08);border-radius:16px;overflow:hidden}
  .header{padding:28px 32px 24px;border-bottom:1px solid rgba(255,255,255,0.07);display:flex;align-items:center;gap:10px}
  .logo-mark{width:36px;height:36px;background:rgba(163,230,53,0.1);border:1px solid rgba(163,230,53,0.2);border-radius:9px;display:flex;align-items:center;justify-content:center}
  .logo-text{font-size:17px;font-weight:800;letter-spacing:-0.03em;color:#fafafa}
  .logo-text span{color:#a3e635}
  .body{padding:28px 32px}
  .tag{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;background:rgba(163,230,53,0.1);border:1px solid rgba(163,230,53,0.2);border-radius:99px;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#a3e635;margin-bottom:16px}
  h1{font-size:20px;font-weight:800;letter-spacing:-0.02em;margin-bottom:6px;color:#fafafa}
  .subtitle{font-size:13px;color:#a1a1aa;margin-bottom:24px}
  .meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px}
  .meta-item{background:#18181b;border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:12px 14px}
  .meta-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#71717a;margin-bottom:4px}
  .meta-value{font-size:13px;font-weight:600;color:#fafafa;word-break:break-word}
  .message-box{background:#18181b;border:1px solid rgba(255,255,255,0.07);border-left:3px solid #a3e635;border-radius:0 10px 10px 0;padding:16px 18px;margin-bottom:24px}
  .message-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#71717a;margin-bottom:8px}
  .message-text{font-size:14px;color:#d4d4d8;line-height:1.7;white-space:pre-wrap}
  .reply-btn{display:block;text-align:center;padding:13px 24px;background:#a3e635;color:#0a0a0b;font-size:14px;font-weight:700;border-radius:10px;text-decoration:none;margin-bottom:20px}
  .divider{height:1px;background:rgba(255,255,255,0.07);margin:20px 0}
  .footer-note{font-size:12px;color:#52525b;text-align:center;line-height:1.6}
  .footer-note a{color:#a3e635;text-decoration:none}
  @media(max-width:480px){
    .body{padding:20px 18px}
    .header{padding:20px 18px}
    .meta-grid{grid-template-columns:1fr}
  }
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">

    <!-- Header -->
    <div class="header">
      <img src="{{ config('app.url') }}/images/icons.png"
           alt="MediaTools"
           style="width:25px;height:15px;border-radius:5px;display:block;">
      <span class="logo-text">MEDIA<span>TOOLS.</span></span>
    </div>

    <!-- Body -->
    <div class="body">
      <div class="tag">&#9993; Pesan Masuk</div>
      <h1>Pesan baru dari form kontak</h1>
      <p class="subtitle">Dikirim oleh pengguna terdaftar · {{ now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>

      <!-- Meta grid -->
      <div class="meta-grid">
        <div class="meta-item">
          <div class="meta-label">Nama Pengirim</div>
          <div class="meta-value">{{ $data['name'] }}</div>
        </div>
        <div class="meta-item">
          <div class="meta-label">Akun MediaTools</div>
          <div class="meta-value">{{ $data['sender_email'] }}</div>
        </div>
        <div class="meta-item">
          <div class="meta-label">Email Kontak</div>
          <div class="meta-value">{{ $data['email'] }}</div>
        </div>
        <div class="meta-item">
          <div class="meta-label">Topik</div>
          <div class="meta-value">{{ $data['subject'] }}</div>
        </div>
      </div>

      <!-- Message -->
      <div class="message-box">
        <div class="message-label">Isi Pesan</div>
        <div class="message-text">{{ $data['message'] }}</div>
      </div>

      <!-- Reply CTA -->
      <a href="mailto:{{ $data['email'] }}?subject=Re: {{ rawurlencode('[MediaTools] ' . $data['subject']) }}" class="reply-btn">
        &#8617; Balas Pesan Ini
      </a>

      <div class="divider"></div>
      <p class="footer-note">
        Email ini dikirim otomatis dari form kontak <a href="https://mediatools.cloud">mediatools.cloud</a><br>
        Balas langsung ke email ini untuk merespons pengirim.
      </p>
    </div>

  </div>

  <p style="text-align:center;font-size:11px;color:#3f3f46;margin-top:16px;">
    © {{ date('Y') }} MediaTools Indonesia · <a href="https://mediatools.cloud" style="color:#52525b;text-decoration:none;">mediatools.cloud</a>
  </p>
</div>
</body>
</html>