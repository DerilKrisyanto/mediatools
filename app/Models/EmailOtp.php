<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmailOtp extends Model
{
    protected $fillable = [
        'email',
        'otp',
        'purpose',
        'used',
        'attempts',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used'       => 'boolean',
    ];

    /**
     * Generate a secure 6-digit OTP
     */
    public static function generate(string $email, string $purpose = 'register'): self
    {
        // Hapus OTP lama untuk email & purpose ini
        static::where('email', $email)
              ->where('purpose', $purpose)
              ->delete();

        return static::create([
            'email'      => $email,
            'otp'        => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'purpose'    => $purpose,
            'used'       => false,
            'attempts'   => 0,
            'expires_at' => now()->addMinutes(5),
        ]);
    }

    /**
     * Verifikasi OTP yang dikirimkan user
     */
    public static function verify(string $email, string $code, string $purpose = 'register'): bool
    {
        $record = static::where('email', $email)
                        ->where('purpose', $purpose)
                        ->where('used', false)
                        ->where('expires_at', '>', now())
                        ->latest()
                        ->first();

        if (!$record) return false;

        // Tambah percobaan
        $record->increment('attempts');

        // Max 5 kali percobaan
        if ($record->attempts > 5) {
            $record->delete();
            return false;
        }

        if (hash_equals($record->otp, $code)) {
            $record->update(['used' => true]);
            return true;
        }

        return false;
    }

    /**
     * Cek apakah sudah request OTP terlalu sering (rate limit: 3x per 10 menit)
     */
    public static function isRateLimited(string $email): bool
    {
        return static::where('email', $email)
                     ->where('created_at', '>', now()->subMinutes(10))
                     ->count() >= 3;
    }
}