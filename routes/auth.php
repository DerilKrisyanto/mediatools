<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
 
    // Register
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
 
    // Login
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
 
    // OTP Verification (shared untuk register dan login)
    Route::get('/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('auth.verify-otp');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('auth.verify-otp.submit');
    Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('auth.resend-otp');
 
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');