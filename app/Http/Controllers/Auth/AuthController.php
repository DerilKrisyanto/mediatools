<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function __construct(protected OtpService $otp) {}

    /* ═══════════════════════════════════════════════════════════════════
       REGISTER  —  Email + Password → OTP Verifikasi → Akun Dibuat
    ═══════════════════════════════════════════════════════════════════ */

    public function showRegister()
    {
        if (Auth::check()) return redirect()->route('home');
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'min:2', 'max:100'],
            'email'    => ['required', 'email:rfc,dns', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required'      => 'Nama wajib diisi.',
            'name.min'           => 'Nama minimal 2 karakter.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // Email sudah terdaftar → arahkan ke login
        if (User::where('email', $data['email'])->exists()) {
            return back()
                ->withInput($request->only('name', 'email'))
                ->withErrors(['email' => 'Email ini sudah terdaftar. Silakan login.']);
        }

        // Kirim OTP verifikasi email
        $result = $this->otp->sendOtp($data['email'], 'register', $data['name']);

        if (!$result['success']) {
            return back()
                ->withInput($request->only('name', 'email'))
                ->withErrors(['email' => $result['message']]);
        }

        // Simpan data di session — akun BELUM dibuat sampai OTP verified
        $request->session()->put('pending_register', [
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        return redirect()->route('auth.verify-otp')
            ->with('otp_purpose', 'register')
            ->with('otp_email', $data['email'])
            ->with('success', "Kode OTP dikirim ke {$data['email']}. Cek inbox atau folder spam.");
    }

    /* ═══════════════════════════════════════════════════════════════════
       LOGIN  —  Email + Password → Langsung Login (Tanpa OTP)
       SaaS standard: Notion, Linear, Vercel, GitHub semuanya begini.
       OTP hanya untuk register (verifikasi email baru).
    ═══════════════════════════════════════════════════════════════════ */

    public function showLogin()
    {
        if (Auth::check()) return redirect()->route('home');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // ── Rate Limiting: maks 5 percobaan/menit per IP+email ──────────
        $throttleKey = 'login:' . sha1($request->ip() . '|' . strtolower($request->email));

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => "Terlalu banyak percobaan gagal. Coba lagi dalam {$seconds} detik.",
                ]);
        }

        // ── Validasi input ───────────────────────────────────────────────
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // ── Cek kredensial ───────────────────────────────────────────────
        $remember = $request->boolean('remember');

        if (!Auth::attempt($request->only('email', 'password'), $remember)) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau password salah.']);
        }

        // ── Login berhasil ───────────────────────────────────────────────
        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))
            ->with('success', 'Selamat datang kembali, ' . Auth::user()->name . '!');
    }

    /* ═══════════════════════════════════════════════════════════════════
       OTP VERIFICATION  —  Hanya untuk Register
    ═══════════════════════════════════════════════════════════════════ */

    public function showVerifyOtp(Request $request)
    {
        if (!$request->session()->has('pending_register')) {
            return redirect()->route('register')
                ->withErrors(['error' => 'Sesi kedaluwarsa. Silakan daftar ulang.']);
        }

        $email   = $request->session()->get('otp_email')
                ?? $request->session()->get('pending_register.email')
                ?? '';
        $purpose = 'register';

        return view('auth.verify-otp', compact('email', 'purpose'));
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.digits'   => 'Kode OTP harus 6 digit angka.',
        ]);

        $pending = $request->session()->get('pending_register');

        if (!$pending) {
            return redirect()->route('register')
                ->withErrors(['error' => 'Sesi tidak ditemukan. Silakan daftar ulang.']);
        }

        // Sesi kedaluwarsa
        if (now()->timestamp > $pending['expires_at']) {
            $request->session()->forget('pending_register');
            return redirect()->route('register')
                ->withErrors(['error' => 'Sesi pendaftaran kedaluwarsa (10 menit). Silakan daftar ulang.']);
        }

        // Verifikasi kode OTP
        if (!$this->otp->verifyOtp($pending['email'], $request->otp, 'register')) {
            return back()->withErrors(['otp' => 'Kode OTP salah atau sudah kedaluwarsa.']);
        }

        // Race condition: pastikan email belum ada user lain yang daftar
        if (User::where('email', $pending['email'])->exists()) {
            $request->session()->forget('pending_register');
            return redirect()->route('login')
                ->withErrors(['email' => 'Email ini sudah terdaftar. Silakan login.']);
        }

        // ✅ Buat akun
        $user = User::create([
            'name'              => $pending['name'],
            'email'             => $pending['email'],
            'password'          => $pending['password'], // sudah di-hash sejak register
            'email_verified_at' => now(),
        ]);

        // Bersihkan session
        $request->session()->forget(['pending_register', 'otp_email', 'otp_purpose']);

        // Login otomatis setelah register berhasil
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')
            ->with('success', "Akun berhasil dibuat! Selamat datang, {$user->name}! 🎉");
    }

    public function resendOtp(Request $request)
    {
        $pending = $request->session()->get('pending_register');

        if (!$pending) {
            return redirect()->route('register')
                ->withErrors(['error' => 'Sesi kedaluwarsa. Silakan daftar ulang.']);
        }

        $result = $this->otp->sendOtp($pending['email'], 'register', $pending['name']);

        if (!$result['success']) {
            return back()->withErrors(['otp' => $result['message']]);
        }

        // Perpanjang sesi 10 menit lagi
        $pending['expires_at'] = now()->addMinutes(10)->timestamp;
        $request->session()->put('pending_register', $pending);

        return back()->with('success', "OTP baru dikirim ke {$pending['email']}.");
    }

    /* ═══════════════════════════════════════════════════════════════════
       LOGOUT
    ═══════════════════════════════════════════════════════════════════ */

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Anda berhasil keluar.');
    }
}
