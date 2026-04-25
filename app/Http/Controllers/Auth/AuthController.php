<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(protected OtpService $otp) {}

    /* ═══════════════════════════════════════
       REGISTER
    ═══════════════════════════════════════ */

    /** Tampilkan form register */
    public function showRegister()
    {
        if (Auth::check()) return redirect()->route('home');
        return view('auth.register');
    }

    /**
     * Step 1 Register: Validasi data, simpan sementara di session, kirim OTP
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'min:2', 'max:100'],
            'email'                 => ['required', 'email:rfc,dns', 'max:255'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required'         => 'Nama wajib diisi.',
            'name.min'              => 'Nama minimal 2 karakter.',
            'email.required'        => 'Email wajib diisi.',
            'email.email'           => 'Format email tidak valid.',
            'password.required'     => 'Password wajib diisi.',
            'password.min'          => 'Password minimal 8 karakter.',
            'password.confirmed'    => 'Konfirmasi password tidak cocok.',
        ]);

        // Cek apakah email sudah terdaftar
        if (User::where('email', $data['email'])->exists()) {
            return back()
                ->withInput($request->only('name', 'email'))
                ->withErrors(['email' => 'Email ini sudah terdaftar. Silakan login.']);
        }

        // Kirim OTP
        $result = $this->otp->sendOtp($data['email'], 'register', $data['name']);

        if (!$result['success']) {
            return back()
                ->withInput($request->only('name', 'email'))
                ->withErrors(['email' => $result['message']]);
        }

        // Simpan data pendaftaran di session (BELUM buat akun)
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

    /* ═══════════════════════════════════════
       LOGIN
    ═══════════════════════════════════════ */

    /** Tampilkan form login */
    public function showLogin()
    {
        if (Auth::check()) return redirect()->route('home');
        return view('auth.login');
    }

    /**
     * Step 1 Login: Validasi kredensial, kirim OTP
     */
    public function login(Request $request)
    {
        // Rate limit: max 5 percobaan login per 1 menit per IP
        $throttleKey = 'login:'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = User::where('email', $data['email'])->first();

        // Validasi kredensial
        if (!$user || !Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau password salah.']);
        }

        RateLimiter::clear($throttleKey);

        // Kirim OTP
        $result = $this->otp->sendOtp($data['email'], 'login', $user->name);

        if (!$result['success']) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => $result['message']]);
        }

        // Simpan info login di session
        $request->session()->put('pending_login', [
            'user_id'    => $user->id,
            'email'      => $user->email,
            'remember'   => $request->boolean('remember'),
            'expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        return redirect()->route('auth.verify-otp')
            ->with('otp_purpose', 'login')
            ->with('otp_email', $data['email'])
            ->with('success', "Kode OTP dikirim ke {$data['email']}. Cek inbox atau folder spam.");
    }

    /* ═══════════════════════════════════════
       OTP VERIFICATION
    ═══════════════════════════════════════ */

    /** Tampilkan form input OTP */
    public function showVerifyOtp(Request $request)
    {
        // Pastikan ada pending session
        $hasPending = $request->session()->has('pending_register')
                   || $request->session()->has('pending_login');

        if (!$hasPending && !$request->session()->has('otp_email')) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Sesi kedaluwarsa. Silakan mulai ulang.']);
        }

        // Ambil email dari session (untuk ditampilkan di view)
        $email = $request->session()->get('otp_email')
              ?? $request->session()->get('pending_register.email')
              ?? $request->session()->get('pending_login.email')
              ?? '';

        $purpose = $request->session()->get('otp_purpose', 'register');

        return view('auth.verify-otp', compact('email', 'purpose'));
    }

    /**
     * Step 2: Verifikasi OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.digits'   => 'Kode OTP harus 6 digit angka.',
        ]);

        // Cek pending login dulu
        if ($pending = $request->session()->get('pending_login')) {

            // Validasi sesi belum kedaluwarsa
            if (now()->timestamp > $pending['expires_at']) {
                $request->session()->forget('pending_login');
                return redirect()->route('login')
                    ->withErrors(['error' => 'Sesi login kedaluwarsa. Silakan login ulang.']);
            }

            if (!$this->otp->verifyOtp($pending['email'], $request->otp, 'login')) {
                return back()->withErrors(['otp' => 'Kode OTP salah atau sudah kedaluwarsa.']);
            }

            // OTP valid → login user
            $user = User::find($pending['user_id']);
            if (!$user) {
                return redirect()->route('login')->withErrors(['error' => 'Akun tidak ditemukan.']);
            }

            $request->session()->forget('pending_login');
            $request->session()->forget('otp_email');
            $request->session()->forget('otp_purpose');

            Auth::login($user, $pending['remember'] ?? false);
            $request->session()->regenerate();

            return redirect()->intended(route('home'))
                ->with('success', "Selamat datang kembali, {$user->name}!");
        }

        // Cek pending register
        if ($pending = $request->session()->get('pending_register')) {

            // Validasi sesi belum kedaluwarsa
            if (now()->timestamp > $pending['expires_at']) {
                $request->session()->forget('pending_register');
                return redirect()->route('register')
                    ->withErrors(['error' => 'Sesi pendaftaran kedaluwarsa. Silakan daftar ulang.']);
            }

            if (!$this->otp->verifyOtp($pending['email'], $request->otp, 'register')) {
                return back()->withErrors(['otp' => 'Kode OTP salah atau sudah kedaluwarsa.']);
            }

            // Double-check email belum ada (race condition)
            if (User::where('email', $pending['email'])->exists()) {
                $request->session()->forget('pending_register');
                return redirect()->route('login')
                    ->withErrors(['email' => 'Email ini sudah terdaftar. Silakan login.']);
            }

            // OTP valid → buat akun
            $user = User::create([
                'name'              => $pending['name'],
                'email'             => $pending['email'],
                'password'          => $pending['password'], // sudah di-hash
                'email_verified_at' => now(),
            ]);

            $request->session()->forget('pending_register');
            $request->session()->forget('otp_email');
            $request->session()->forget('otp_purpose');

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('home')
                ->with('success', "Akun berhasil dibuat! Selamat datang, {$user->name}!");
        }

        // Tidak ada pending session
        return redirect()->route('login')
            ->withErrors(['error' => 'Sesi tidak ditemukan. Silakan mulai ulang.']);
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        $email   = '';
        $purpose = 'register';
        $name    = '';

        if ($pending = $request->session()->get('pending_login')) {
            $email   = $pending['email'];
            $purpose = 'login';
            $user    = User::find($pending['user_id']);
            $name    = $user?->name ?? '';
        } elseif ($pending = $request->session()->get('pending_register')) {
            $email   = $pending['email'];
            $purpose = 'register';
            $name    = $pending['name'];
        }

        if (!$email) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Sesi kedaluwarsa. Silakan mulai ulang.']);
        }

        $result = $this->otp->sendOtp($email, $purpose, $name);

        if (!$result['success']) {
            return back()->withErrors(['otp' => $result['message']]);
        }

        return back()->with('success', "OTP baru dikirim ke {$email}.");
    }

    /* ═══════════════════════════════════════
       LOGOUT
    ═══════════════════════════════════════ */

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Anda berhasil keluar.');
    }
}