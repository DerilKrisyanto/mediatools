@extends('layouts.app')

@section('title', 'MediaTools — Verifikasi Email Anda')

@section('content')
<div class="min-h-screen bg-[#020d0d] text-white flex items-center justify-center px-4 pt-20">

    {{-- Background blobs --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[20%] left-[15%] w-72 h-72 bg-[#a3e635]/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[15%] right-[10%] w-72 h-72 bg-emerald-500/8 rounded-full blur-[120px]"></div>
    </div>

    <div class="w-full max-w-md relative">
        <div class="bg-[#0a2525]/40 border border-white/10 backdrop-blur-xl rounded-[2.5rem] p-8 md:p-10 shadow-2xl">

            {{-- Header --}}
            <div class="text-center mb-8">
                {{-- Animated envelope icon --}}
                <div class="inline-flex items-center justify-center w-20 h-20 bg-[#a3e635]/10 rounded-2xl mb-4 border border-[#a3e635]/20 relative overflow-hidden">
                    <img src="{{ asset('images/icons.png') }}"
                         alt="MediaTools"
                         class="w-14 h-14 object-contain"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <i class="fa-solid fa-envelope-open-text text-[#a3e635] text-3xl hidden" style="display:none;"></i>
                </div>
                <h1 class="text-2xl font-bold tracking-tight">Cek Email Anda</h1>
                <p class="text-gray-400 mt-2 text-sm leading-relaxed">
                    Kode OTP 6 digit telah dikirim ke<br>
                    <strong class="text-white">{{ $email }}</strong>
                </p>
            </div>

            {{-- Status --}}
            @if (session('status'))
                <div class="mb-5 p-3 bg-[#a3e635]/10 border border-[#a3e635]/20 rounded-xl text-[#a3e635] text-sm text-center">
                    <i class="fa-solid fa-circle-check mr-1"></i>
                    {{ session('status') }}
                </div>
            @endif

            {{-- Error --}}
            @if ($errors->any())
                <div class="mb-5 p-3 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm text-center">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('otp.verify') }}" class="space-y-6">
                @csrf

                {{-- OTP Input --}}
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest text-center">Masukkan Kode OTP</label>

                    {{-- 6 kotak OTP --}}
                    <div class="flex gap-2 justify-center" id="otpBoxes">
                        @for($i = 0; $i < 6; $i++)
                        <input type="text"
                               maxlength="1"
                               inputmode="numeric"
                               pattern="[0-9]"
                               class="otp-box w-12 h-14 text-center text-xl font-bold bg-white/5 border border-white/10 rounded-2xl outline-none focus:border-[#a3e635] focus:ring-2 focus:ring-[#a3e635]/20 transition-all caret-transparent"
                               autocomplete="off">
                        @endfor
                    </div>

                    {{-- Hidden input yang dikirim ke server --}}
                    <input type="hidden" name="otp" id="otpHidden">
                </div>

                {{-- Timer --}}
                <div class="text-center">
                    <p class="text-gray-500 text-xs" id="timerText">
                        Kode berlaku selama <span id="countdown" class="text-white font-bold">10:00</span>
                    </p>
                </div>

                <button type="submit" id="verifyBtn"
                        class="w-full bg-[#a3e635] text-[#020d0d] font-bold py-4 rounded-2xl hover:scale-[1.02] hover:shadow-lg hover:shadow-[#a3e635]/20 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:scale-100">
                    <i class="fa-solid fa-check-double mr-2"></i>
                    Verifikasi Sekarang
                </button>
            </form>

            {{-- Resend OTP --}}
            <div class="mt-6 text-center">
                <p class="text-gray-500 text-sm">Tidak menerima kode?</p>
                <form method="POST" action="{{ route('otp.resend') }}" class="mt-2">
                    @csrf
                    <button type="submit"
                            class="text-[#a3e635] text-sm font-bold hover:underline transition-colors"
                            id="resendBtn">
                        <i class="fa-solid fa-rotate-right text-xs mr-1"></i>
                        Kirim Ulang Kode
                    </button>
                </form>
            </div>

            {{-- Ganti email --}}
            <div class="mt-4 text-center">
                <a href="{{ route('register') }}" class="text-gray-600 text-xs hover:text-gray-400 transition-colors">
                    <i class="fa-solid fa-arrow-left text-[10px] mr-1"></i>
                    Gunakan email lain
                </a>
            </div>
        </div>

        <p class="text-center text-gray-600 text-[10px] mt-8 uppercase tracking-[0.3em]">
            &copy; {{ date('Y') }} MediaTools Secure Access
        </p>
    </div>
</div>

@push('scripts')
<script>
// ── OTP Box Navigation ────────────────────────────────────────────────
(function () {
    const boxes   = document.querySelectorAll('.otp-box');
    const hidden  = document.getElementById('otpHidden');
    const verifyBtn = document.getElementById('verifyBtn');

    function syncHidden() {
        hidden.value = Array.from(boxes).map(b => b.value).join('');
    }

    boxes.forEach((box, idx) => {
        box.addEventListener('input', e => {
            const val = e.target.value.replace(/\D/g, '');
            box.value = val ? val[val.length - 1] : '';
            syncHidden();
            if (val && idx < boxes.length - 1) boxes[idx + 1].focus();
        });

        box.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !box.value && idx > 0) {
                boxes[idx - 1].focus();
                boxes[idx - 1].value = '';
                syncHidden();
            }
            if (e.key === 'ArrowLeft'  && idx > 0)               boxes[idx - 1].focus();
            if (e.key === 'ArrowRight' && idx < boxes.length - 1) boxes[idx + 1].focus();
        });

        // Handle paste
        box.addEventListener('paste', e => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            pasted.split('').slice(0, 6).forEach((ch, i) => {
                if (boxes[i]) boxes[i].value = ch;
            });
            syncHidden();
            const nextEmpty = Array.from(boxes).findIndex(b => !b.value);
            if (nextEmpty !== -1) boxes[nextEmpty].focus();
            else boxes[5].focus();
        });
    });

    // Focus first box on load
    if (boxes[0]) boxes[0].focus();

    // Auto-submit when all 6 digits filled
    document.getElementById('otpHidden').addEventListener('change', () => {});
    document.querySelectorAll('.otp-box').forEach(b => {
        b.addEventListener('input', () => {
            if (hidden.value.length === 6) verifyBtn.click();
        });
    });
})();

// ── Countdown Timer ───────────────────────────────────────────────────
(function () {
    let seconds = 10 * 60; // 10 menit
    const el = document.getElementById('countdown');
    const timerText = document.getElementById('timerText');

    const timer = setInterval(() => {
        seconds--;
        if (seconds <= 0) {
            clearInterval(timer);
            timerText.innerHTML = '<span class="text-red-400">Kode sudah kedaluwarsa. Kirim ulang.</span>';
            return;
        }
        const m = String(Math.floor(seconds / 60)).padStart(2, '0');
        const s = String(seconds % 60).padStart(2, '0');
        el.textContent = m + ':' + s;
        if (seconds <= 60) el.className = 'text-red-400 font-bold';
    }, 1000);
})();
</script>
@endpush
@endsection
