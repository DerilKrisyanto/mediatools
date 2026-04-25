<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OtpMail extends Mailable
{
    // TIDAK pakai Queueable agar dikirim synchronous (langsung)
    // Ini krusial: OTP harus sampai sebelum user nunggu

    public function __construct(
        public string $otp,
        public string $recipientName = '',
        public string $purpose = 'register',
        public int    $expiryMinutes = 5,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->purpose === 'login'
            ? "🔐 Kode Login MediaTools: {$this->otp}"
            : "✅ Kode Verifikasi MediaTools: {$this->otp}";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.otp');
    }

    public function attachments(): array
    {
        return [];
    }
}