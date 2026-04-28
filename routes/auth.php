<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes — MediaTools
|--------------------------------------------------------------------------
| Login : email + password → langsung masuk (tanpa OTP)
| Register : email + password → OTP verifikasi → akun dibuat → login
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    // ── Register ────────────────────────────────────────────────────────
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');

    // ── OTP Verification (hanya untuk register) ─────────────────────────
    Route::get('/verify-otp',    [AuthController::class, 'showVerifyOtp'])->name('auth.verify-otp');
    Route::post('/verify-otp',   [AuthController::class, 'verifyOtp'])
         ->name('auth.verify-otp.submit')
         ->middleware('throttle:10,1');
    Route::post('/resend-otp',   [AuthController::class, 'resendOtp'])
         ->name('auth.resend-otp')
         ->middleware('throttle:3,1');

    // ── Login ────────────────────────────────────────────────────────────
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
         ->name('auth.login')
         ->middleware('throttle:10,1');

});

// ── Logout ───────────────────────────────────────────────────────────────
Route::post('/logout', [AuthController::class, 'logout'])
     ->middleware('auth')
     ->name('logout');

// Fallback GET logout → antisipasi 419 Page Expired
Route::get('/logout', function () {
    if (auth()->check()) {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
    return redirect('/');
})->name('logout.get');
