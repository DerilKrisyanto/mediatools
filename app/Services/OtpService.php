<?php

namespace App\Services;

use App\Mail\OtpVerificationMail;
use App\Models\EmailOtp;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    // Berapa menit OTP berlaku
    const EXPIRES_MINUTES = 10;

    // Throttle: minimal jarak antar pengiriman OTP (detik)
    const RESEND_COOLDOWN = 60;

    // Maks percobaan verifikasi OTP yang salah sebelum OTP di-invalidate
    const MAX_ATTEMPTS = 5;

    /**
     * Generate, simpan, dan kirim OTP ke email.
     *
     * @return array{success: bool, message: string}
     */
    public function sendOtp(string $email, string $purpose, string $name = ''): array
    {
        // ── Throttle: cegah spam kirim OTP ──────────────────────────────
        $cooldownKey = "otp_cooldown:{$email}:{$purpose}";
        if (Cache::has($cooldownKey)) {
            $remaining = Cache::get($cooldownKey . '_ttl', self::RESEND_COOLDOWN);
            return [
                'success' => false,
                'message' => "Tunggu {$remaining} detik sebelum meminta OTP baru.",
            ];
        }

        try {
            // Hapus OTP lama untuk email + purpose yang sama
            EmailOtp::where('email', $email)
                    ->where('purpose', $purpose)
                    ->delete();

            // Generate kode 6 digit
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Simpan ke database
            EmailOtp::create([
                'email'      => $email,
                'purpose'    => $purpose,
                'code'       => $code,
                'expires_at' => now()->addMinutes(self::EXPIRES_MINUTES),
                'attempts'   => 0,
                'used'       => false,
            ]);

            // Kirim email
            Mail::to($email)->send(new OtpVerificationMail($code, $name ?: $email));

            // Set cooldown agar tidak bisa kirim ulang terlalu cepat
            Cache::put($cooldownKey, true, self::RESEND_COOLDOWN);
            Cache::put($cooldownKey . '_ttl', self::RESEND_COOLDOWN, self::RESEND_COOLDOWN);

            return ['success' => true, 'message' => 'OTP berhasil dikirim.'];

        } catch (\Exception $e) {
            Log::error('OTP send failed', [
                'email'   => $email,
                'purpose' => $purpose,
                'error'   => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengirim OTP. Coba beberapa saat lagi atau hubungi support.',
            ];
        }
    }

    /**
     * Verifikasi kode OTP yang dimasukkan user.
     */
    public function verifyOtp(string $email, string $code, string $purpose): bool
    {
        $record = EmailOtp::where('email', $email)
                          ->where('purpose', $purpose)
                          ->where('used', false)
                          ->latest()
                          ->first();

        if (!$record) {
            return false;
        }

        // Cek kedaluwarsa
        if ($record->expires_at->isPast()) {
            $record->delete();
            return false;
        }

        // Cek maks percobaan salah
        if ($record->attempts >= self::MAX_ATTEMPTS) {
            $record->delete();
            return false;
        }

        // Kode tidak cocok → tambah counter attempts
        if ($record->code !== $code) {
            $record->increment('attempts');
            return false;
        }

        // ✅ Valid → tandai sebagai used
        $record->update(['used' => true]);

        return true;
    }
}
