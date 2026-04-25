<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpVerificationMail;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Tampilkan halaman register.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Proses pendaftaran atau auto-login jika email sudah terdaftar.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = $request->email;

        // ── Jika email sudah terdaftar → auto-login langsung ──────────────
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            // Coba login dengan password yang dimasukkan
            if (Auth::attempt(['email' => $email, 'password' => $request->password])) {
                $request->session()->regenerate();
                return redirect()->route('home');
            }

            // Password salah, tapi email ada → beri pesan yang tepat
            return back()
                ->withInput($request->only('name', 'email'))
                ->withErrors([
                    'email' => 'Email ini sudah terdaftar. Password yang Anda masukkan tidak sesuai.',
                ]);
        }

        // ── Email belum terdaftar → buat user baru & kirim OTP ─────────────
        $user = User::create([
            'name'     => $request->name,
            'email'    => $email,
            'password' => Hash::make($request->password),
        ]);

        // Kirim OTP ke email
        $this->sendOtp($email, $user->name);

        // Simpan email di session untuk halaman OTP
        $request->session()->put('otp_email', $email);
        $request->session()->put('otp_purpose', 'register');

        return redirect()->route('otp.show')
            ->with('status', 'Kode OTP telah dikirim ke ' . $email . '. Silakan cek inbox atau folder spam.');
    }

    // ── Helper: generate & kirim OTP ──────────────────────────────────────
    public static function sendOtp(string $email, string $name): void
    {
        // Hapus OTP lama untuk email ini
        EmailOtp::where('email', $email)->delete();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailOtp::create([
            'email'      => $email,
            'code'       => $code,
            'expires_at' => now()->addMinutes(10),
            'used'       => false,
        ]);

        Mail::to($email)->send(new OtpVerificationMail($code, $name));
    }
}
