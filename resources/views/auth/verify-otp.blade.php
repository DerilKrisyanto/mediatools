{{-- FILE: resources/views/auth/verify-otp.blade.php --}}
@extends('layouts.app')
@section('title', 'Verifikasi OTP — MediaTools')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 pt-24 pb-16">
    <div class="w-full max-w-md">

        <div class="relative overflow-hidden rounded-3xl border" style="background:var(--card-bg);border-color:var(--border-accent);">
            <div class="absolute inset-0 bg-gradient-to-br from-[#a3e635]/4 to-transparent pointer-events-none"></div>
            <div class="relative z-10 p-8 sm:p-10">

                {{-- Header --}}
                <div class="text-center mb-8">
                    {{-- Icon --}}
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center"
                         style="background:rgba(163,230,53,0.1);border:1px solid rgba(163,230,53,0.2);">
                        <i class="fa-solid fa-envelope-open-text text-2xl" style="color:#a3e635;"></i>
                    </div>
                    <h1 class="text-2xl font-extrabold text-white mb-2">Cek Email Anda</h1>
                    <p class="text-sm leading-relaxed" style="color:var(--text-muted);">
                        Kode OTP 6 digit telah dikirim ke<br>
                        <strong class="font-bold" style="color:var(--text-primary);">{{ $email }}</strong>
                    </p>
                </div>

                {{-- Flash --}}
                @if(session('success'))
                    <div class="mb-4 p-4 rounded-xl text-sm font-semibold" style="background:rgba(163,230,53,0.1);border:1px solid rgba(163,230,53,0.2);color:#a3e635;">
                        <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
                    </div>
                @endif

                @if($errors->has('otp'))
                    <div class="mb-4 p-4 rounded-xl text-sm font-semibold" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);color:#fca5a5;">
                        <i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ $errors->first('otp') }}
                    </div>
                @endif

                @if($errors->has('error'))
                    <div class="mb-4 p-4 rounded-xl text-sm font-semibold" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);color:#fca5a5;">
                        <i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ $errors->first('error') }}
                    </div>
                @endif

                {{-- OTP Form --}}
                <form method="POST" action="{{ route('auth.verify-otp.submit') }}" id="otpForm">
                    @csrf

                    <div class="mb-6">
                        <label class="block text-xs font-bold mb-3 text-center uppercase tracking-wider" style="color:var(--text-dim);">
                            Masukkan Kode OTP
                        </label>

                        {{-- 6 kotak digit OTP --}}
                        <div class="flex justify-center gap-2 sm:gap-3 mb-3" id="otp-boxes">
                            @for($i = 0; $i < 6; $i++)
                            <input type="text"
                                   inputmode="numeric"
                                   maxlength="1"
                                   class="otp-digit w-12 h-14 sm:w-14 sm:h-16 text-center text-xl sm:text-2xl font-bold rounded-xl border outline-none transition-all"
                                   style="background:rgba(255,255,255,0.04);border-color:var(--border);color:var(--text-primary);"
                                   data-index="{{ $i }}"
                                   autocomplete="off"
                                   {{ $i === 0 ? 'autofocus' : '' }}>
                            @endfor
                        </div>

                        {{-- Hidden input yang berisi OTP lengkap --}}
                        <input type="hidden" name="otp" id="otp-value">

                        {{-- Timer countdown --}}
                        <div class="text-center">
                            <span class="text-xs font-semibold" style="color:var(--text-muted);">
                                Kode kedaluwarsa dalam: <span id="countdown" class="font-bold" style="color:#a3e635;">5:00</span>
                            </span>
                        </div>
                    </div>

                    <button type="submit" id="submitBtn" class="btn-primary w-full py-4 text-sm" disabled>
                        <i class="fa-solid fa-shield-check text-xs"></i>
                        <span>Verifikasi OTP</span>
                    </button>
                </form>

                {{-- Resend --}}
                <div class="mt-6 text-center">
                    <p class="text-sm mb-2" style="color:var(--text-muted);">Tidak menerima email?</p>
                    <p class="text-xs mb-3" style="color:var(--text-muted);">Periksa folder <strong>Spam</strong> atau <strong>Junk Mail</strong> Anda.</p>

                    <form method="POST" action="{{ route('auth.resend-otp') }}" id="resendForm">
                        @csrf
                        <button type="submit" id="resendBtn"
                                class="text-sm font-bold transition-colors"
                                style="color:#a3e635;background:none;border:none;cursor:pointer;">
                            <i class="fa-solid fa-rotate-right text-xs mr-1"></i>
                            Kirim Ulang OTP
                        </button>
                    </form>

                    <a href="{{ $purpose === 'login' ? route('login') : route('register') }}"
                       class="block mt-3 text-xs" style="color:var(--text-muted);">
                        ← Kembali ke {{ $purpose === 'login' ? 'Login' : 'Daftar' }}
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const digits   = document.querySelectorAll('.otp-digit');
    const hidden   = document.getElementById('otp-value');
    const submitBtn = document.getElementById('submitBtn');
    const focusCss = 'border-[#a3e635] bg-[rgba(163,230,53,0.06)] shadow-[0_0_0_3px_rgba(163,230,53,0.15)]';
    const filledCss = 'border-[#a3e635]';

    /* ---- Auto-focus next, update hidden input ---- */
    digits.forEach((input, idx) => {
        // Atur style focus
        input.addEventListener('focus', () => {
            input.style.borderColor = '#a3e635';
            input.style.background  = 'rgba(163,230,53,0.06)';
            input.style.boxShadow   = '0 0 0 3px rgba(163,230,53,0.12)';
            input.select();
        });
        input.addEventListener('blur', () => {
            input.style.boxShadow = '';
            if (!input.value) {
                input.style.borderColor = 'var(--border)';
                input.style.background  = 'rgba(255,255,255,0.04)';
            }
        });

        input.addEventListener('input', (e) => {
            const val = e.target.value.replace(/\D/g, '').slice(-1);
            input.value = val;
            if (val && idx < 5) digits[idx + 1].focus();
            syncOtp();
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !input.value && idx > 0) {
                digits[idx - 1].focus();
                digits[idx - 1].value = '';
                syncOtp();
            }
            if (e.key === 'ArrowLeft' && idx > 0)  digits[idx - 1].focus();
            if (e.key === 'ArrowRight' && idx < 5) digits[idx + 1].focus();
        });

        // Paste support
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
            text.split('').forEach((ch, i) => {
                if (digits[i]) digits[i].value = ch;
            });
            if (digits[Math.min(text.length, 5)]) digits[Math.min(text.length, 5)].focus();
            syncOtp();
        });
    });

    function syncOtp() {
        const otp = Array.from(digits).map(d => d.value).join('');
        hidden.value = otp;
        submitBtn.disabled = otp.length < 6;
        if (otp.length === 6) submitBtn.style.opacity = '1';
    }

    /* ---- Countdown timer ---- */
    let seconds = 5 * 60;
    const el = document.getElementById('countdown');
    const resendBtn = document.getElementById('resendBtn');

    function tick() {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        el.textContent = `${m}:${s.toString().padStart(2, '0')}`;
        if (seconds <= 60) el.style.color = '#ef4444';
        if (seconds <= 0) {
            el.textContent = 'Kedaluwarsa';
            el.style.color = '#ef4444';
            submitBtn.disabled = true;
            return;
        }
        seconds--;
        setTimeout(tick, 1000);
    }
    tick();

    /* ---- Resend cooldown ---- */
    let resendCooldown = 60;
    resendBtn.addEventListener('click', function () {
        this.disabled = true;
        let cd = resendCooldown;
        const iv = setInterval(() => {
            this.textContent = `Kirim ulang (${cd}s)`;
            if (--cd < 0) {
                clearInterval(iv);
                this.disabled = false;
                this.innerHTML = '<i class="fa-solid fa-rotate-right text-xs mr-1"></i> Kirim Ulang OTP';
            }
        }, 1000);
    });
})();
</script>
@endpush
@endsection