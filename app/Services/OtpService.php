<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\EmailOtp;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /**
     * Generate OTP dan kirim ke email.
     * Return ['success' => bool, 'message' => string]
     */
    public function sendOtp(string $email, string $purpose = 'register', string $name = ''): array
    {
        // Rate limiting
        if (EmailOtp::isRateLimited($email)) {
            return [
                'success' => false,
                'message' => 'Terlalu banyak permintaan OTP. Coba lagi dalam 10 menit.',
            ];
        }

        $record = EmailOtp::generate($email, $purpose);

        try {
            // Kirim SYNCHRONOUS (tidak di-queue) agar pasti terkirim sebelum redirect
            Mail::to($email)->send(new OtpMail(
                otp:            $record->otp,
                recipientName:  $name,
                purpose:        $purpose,
                expiryMinutes:  5,
            ));

            Log::info('OTP sent', ['email' => $email, 'purpose' => $purpose]);

            return ['success' => true, 'message' => 'OTP berhasil dikirim.'];

        } catch (\Throwable $e) {
            Log::error('OTP send failed', [
                'email'   => $email,
                'purpose' => $purpose,
                'error'   => $e->getMessage(),
            ]);

            // Hapus record OTP yang gagal kirim
            $record->delete();

            return [
                'success' => false,
                'message' => 'Gagal mengirim email OTP. Silakan coba beberapa saat lagi.',
                'debug'   => config('app.debug') ? $e->getMessage() : null,
            ];
        }
    }

    /**
     * Verifikasi kode OTP
     */
    public function verifyOtp(string $email, string $code, string $purpose = 'register'): bool
    {
        return EmailOtp::verify($email, $code, $purpose);
    }
}