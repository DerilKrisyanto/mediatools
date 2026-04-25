<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpVerificationMail;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = $request->email;

        // ── Email sudah terdaftar → coba auto-login ──────────────────────
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            if (Auth::attempt(['email' => $email, 'password' => $request->password])) {
                $request->session()->regenerate();
                return redirect()->route('home');
            }

            return back()
                ->withInput($request->only('name', 'email'))
                ->withErrors([
                    'email' => 'Email ini sudah terdaftar. Password yang Anda masukkan tidak sesuai.',
                ]);
        }

        // ── Email baru → buat user + kirim OTP dalam satu transaksi ──────
        try {
            DB::beginTransaction();

            $user = User::create([
                'name'     => $request->name,
                'email'    => $email,
                'password' => Hash::make($request->password),
            ]);

            // Generate & simpan OTP
            $code = self::generateOtp($email);

            // Kirim email — jika gagal, exception akan ditangkap di bawah
            Mail::to($email)->send(new OtpVerificationMail($code, $user->name));

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();

            // Catat error di log untuk debugging
            Log::error('Register OTP mail failed: ' . $e->getMessage(), [
                'email' => $email,
            ]);

            return back()
                ->withInput($request->only('name', 'email'))
                ->withErrors([
                    'email' => 'Gagal mengirim kode OTP ke email Anda. Pastikan alamat email valid dan coba lagi. Jika masalah berlanjut, hubungi support.',
                ]);
        }

        // Simpan email di session untuk halaman OTP
        $request->session()->put('otp_email', $email);
        $request->session()->put('otp_purpose', 'register');

        return redirect()->route('otp.show')
            ->with('status', 'Kode OTP telah dikirim ke ' . $email . '. Silakan cek inbox atau folder spam.');
    }

    /**
     * Generate OTP dan simpan ke DB.
     */
    public static function generateOtp(string $email): string
    {
        EmailOtp::where('email', $email)->delete();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailOtp::create([
            'email'      => $email,
            'code'       => $code,
            'expires_at' => now()->addMinutes(10),
            'used'       => false,
        ]);

        return $code;
    }

    /**
     * Generate + kirim OTP (dipanggil dari OtpVerificationController::resend).
     */
    public static function sendOtp(string $email, string $name): void
    {
        $code = self::generateOtp($email);
        Mail::to($email)->send(new OtpVerificationMail($code, $name));
    }
}