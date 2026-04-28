@extends('layouts.app')
@section('title', 'Masuk — MediaTools')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 pt-24 pb-16">

    {{-- Background subtle blobs --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 rounded-full opacity-20 blur-[100px]"
             style="background:radial-gradient(circle, rgba(163,230,53,0.3), transparent 70%);"></div>
        <div class="absolute bottom-1/4 right-1/4 w-80 h-80 rounded-full opacity-10 blur-[100px]"
             style="background:radial-gradient(circle, rgba(59,130,246,0.4), transparent 70%);"></div>
    </div>

    <div class="w-full max-w-md relative">

        {{-- Card --}}
        <div class="relative overflow-hidden rounded-3xl border"
             style="background:var(--card-bg); border-color:var(--border-accent);">

            {{-- Subtle top glow --}}
            <div class="absolute top-0 inset-x-0 h-px"
                 style="background:linear-gradient(90deg,transparent,rgba(163,230,53,0.4),transparent);"></div>

            <div class="relative z-10 p-8 sm:p-10">

                {{-- ── Header ── --}}
                <div class="text-center mb-8">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 mb-6 group">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center overflow-hidden"
                             style="background:rgba(163,230,53,0.1); border:1px solid rgba(163,230,53,0.2);">
                            <img src="{{ asset('images/icons.png') }}"
                                 alt="MediaTools"
                                 class="w-7 h-7 object-contain"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
                            <i class="fa-solid fa-bolt text-[#a3e635] hidden"></i>
                        </div>
                        <span class="text-lg font-bold text-white tracking-tight">
                            MEDIA<span style="color:#a3e635;">TOOLS.</span>
                        </span>
                    </a>
                    <h1 class="text-2xl font-extrabold text-white mb-1">Masuk ke Akun</h1>
                    <p class="text-sm" style="color:var(--text-muted);">
                        Selamat datang kembali di MediaTools
                    </p>
                </div>

                {{-- ── Flash Messages ── --}}
                @if(session('success'))
                    <div class="mb-5 flex items-start gap-3 p-4 rounded-xl text-sm font-medium"
                         style="background:rgba(163,230,53,0.08);border:1px solid rgba(163,230,53,0.2);color:#a3e635;">
                        <i class="fa-solid fa-circle-check mt-0.5 flex-shrink-0"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 flex items-start gap-3 p-4 rounded-xl text-sm font-medium"
                         style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);color:#fca5a5;">
                        <i class="fa-solid fa-triangle-exclamation mt-0.5 flex-shrink-0"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                {{-- ── Form ── --}}
                <form method="POST" action="{{ route('auth.login') }}" class="space-y-5" novalidate>
                    @csrf

                    {{-- Email --}}
                    <div class="form-group">
                        <label class="block text-xs font-bold mb-2 uppercase tracking-wider"
                               style="color:var(--text-dim);" for="email">
                            Alamat Email
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none
                                        text-gray-500 group-focus-within:text-[#a3e635] transition-colors">
                                <i class="fa-solid fa-envelope text-sm"></i>
                            </div>
                            <input type="email"
                                   name="email"
                                   id="email"
                                   value="{{ old('email') }}"
                                   required
                                   autofocus
                                   autocomplete="email"
                                   placeholder="email@gmail.com"
                                   class="form-input pl-11 @error('email') border-red-500/60 @enderror">
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="form-group">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold uppercase tracking-wider"
                                   style="color:var(--text-dim);" for="loginPwd">
                                Password
                            </label>
                            {{-- Aktifkan setelah forgot password tersedia --}}
                            {{-- <a href="{{ route('password.request') }}" class="text-xs font-semibold hover:underline" style="color:#a3e635;">Lupa password?</a> --}}
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none
                                        text-gray-500 group-focus-within:text-[#a3e635] transition-colors">
                                <i class="fa-solid fa-lock text-sm"></i>
                            </div>
                            <input type="password"
                                   name="password"
                                   id="loginPwd"
                                   required
                                   autocomplete="current-password"
                                   placeholder="Password Anda"
                                   class="form-input pl-11 pr-12 @error('password') border-red-500/60 @enderror">
                            <button type="button"
                                    onclick="togglePwd('loginPwd', 'eyeLogin')"
                                    tabindex="-1"
                                    aria-label="Tampilkan/sembunyikan password"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center
                                           text-gray-500 hover:text-gray-300 transition-colors">
                                <i class="fa-solid fa-eye text-sm" id="eyeLogin"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Remember Me --}}
                    <label class="flex items-center gap-3 cursor-pointer group w-fit select-none">
                        <div class="relative flex-shrink-0">
                            <input type="checkbox"
                                   name="remember"
                                   id="remember"
                                   class="peer sr-only">
                            <div class="w-5 h-5 rounded-md border transition-all
                                        peer-checked:bg-[#a3e635] peer-checked:border-[#a3e635]"
                                 style="border-color:var(--border); background:rgba(255,255,255,0.04);">
                            </div>
                            <i class="fa-solid fa-check absolute inset-0 flex items-center justify-center
                                      text-[10px] text-[#040f0f] opacity-0 peer-checked:opacity-100
                                      pointer-events-none" style="margin-top:1px;"></i>
                        </div>
                        <span class="text-sm" style="color:var(--text-dim);">
                            Ingat saya selama 30 hari
                        </span>
                    </label>

                    {{-- Submit --}}
                    <button type="submit"
                            class="btn-primary w-full py-4 text-sm mt-2 gap-2">
                        <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                        <span>Masuk</span>
                    </button>
                </form>

                {{-- Divider --}}
                <div class="mt-8 pt-8 border-t text-center" style="border-color:var(--border);">
                    <p class="text-sm" style="color:var(--text-muted);">
                        Belum punya akun?
                        <a href="{{ route('register') }}"
                           class="font-bold ml-1 hover:underline"
                           style="color:#a3e635;">
                            Daftar Gratis
                        </a>
                    </p>
                </div>

            </div>
        </div>

        {{-- Footer note --}}
        <p class="text-center mt-6 text-[11px] uppercase tracking-widest"
           style="color:var(--text-muted); opacity:0.5;">
            &copy; {{ date('Y') }} MediaTools &middot; Secure Access
        </p>

    </div>
</div>

@push('scripts')
<script>
function togglePwd(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (!input || !icon) return;
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    icon.className = 'fa-solid text-sm ' + (isHidden ? 'fa-eye-slash' : 'fa-eye');
}
</script>
@endpush
@endsection
