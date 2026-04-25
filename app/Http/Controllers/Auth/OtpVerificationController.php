<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OtpVerificationController extends Controller
{
    /**
     * Tampilkan halaman input OTP.
     */
    public function show(Request $request): View|RedirectResponse
    {
        if (!$request->session()->has('otp_email')) {
            return redirect()->route('register');
        }

        return view('auth.verify-otp', [
            'email' => $request->session()->get('otp_email'),
        ]);
    }

    /**
     * Verifikasi kode OTP yang dimasukkan user.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ], [
            'otp.required' => 'Masukkan kode OTP terlebih dahulu.',
            'otp.size'     => 'Kode OTP harus tepat 6 digit.',
            'otp.regex'    => 'Kode OTP hanya boleh berisi angka.',
        ]);

        $email = $request->session()->get('otp_email');

        if (!$email) {
            return redirect()->route('register')
                ->withErrors(['otp' => 'Sesi berakhir. Silakan daftar ulang.']);
        }

        // Cari OTP valid terbaru untuk email ini
        $otpRecord = EmailOtp::where('email', $email)
            ->where('code', $request->otp)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otpRecord) {
            return back()->withErrors([
                'otp' => 'Kode OTP tidak valid atau sudah kedaluwarsa.',
            ]);
        }

        // Tandai OTP sudah dipakai
        $otpRecord->update(['used' => true]);

        // Temukan user dan login
        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('register')
                ->withErrors(['otp' => 'Akun tidak ditemukan. Silakan daftar ulang.']);
        }

        // Tandai email verified
        if (!$user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }

        // Login user
        Auth::login($user, remember: true);
        $request->session()->regenerate();

        // Hapus data OTP dari session
        $request->session()->forget(['otp_email', 'otp_purpose']);

        return redirect()->route('home')
            ->with('success', 'Selamat datang di MediaTools, ' . $user->name . '!');
    }

    /**
     * Kirim ulang OTP ke email yang sama.
     */
    public function resend(Request $request): RedirectResponse
    {
        $email = $request->session()->get('otp_email');

        if (!$email) {
            return redirect()->route('register')
                ->withErrors(['error' => 'Sesi berakhir. Silakan daftar ulang.']);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('register');
        }

        // Throttle: cegah spam kirim OTP (maks 1x per 60 detik)
        $recent = \App\Models\EmailOtp::where('email', $email)
            ->where('created_at', '>', now()->subSeconds(60))
            ->exists();

        if ($recent) {
            return back()->withErrors([
                'otp' => 'Tunggu 60 detik sebelum meminta kode OTP baru.',
            ]);
        }

        RegisteredUserController::sendOtp($email, $user->name);

        return back()->with('status', 'Kode OTP baru telah dikirim ke ' . $email . '.');
    }
}
