<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public string $recipientName;
    public string $purpose;
    public int $expiryMinutes;

    public function __construct(
        string $otp,
        string $recipientName = '',
        string $purpose = 'register',
        int $expiryMinutes = 10
    ) {
        $this->otp = $otp;
        $this->recipientName = $recipientName;
        $this->purpose = $purpose;
        $this->expiryMinutes = $expiryMinutes;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->purpose === 'login'
                ? 'Kode OTP Login — MediaTools'
                : 'Verifikasi Email — MediaTools',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
            with: [
                'otp'            => $this->otp,
                'recipientName'  => $this->recipientName,
                'purpose'        => $this->purpose,
                'expiryMinutes'  => $this->expiryMinutes,
            ],
        );
    }
}