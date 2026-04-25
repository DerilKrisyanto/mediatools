@extends('layouts.app')

@section('title', 'MediaTools — Masuk ke Akun Anda')

@section('content')
<div class="min-h-screen bg-[#020d0d] text-white flex items-center justify-center px-4 pt-20">

    {{-- Background blobs --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[20%] left-[10%] w-72 h-72 bg-[#a3e635]/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[20%] right-[10%] w-72 h-72 bg-blue-500/10 rounded-full blur-[120px]"></div>
    </div>

    <div class="w-full max-w-md relative">
        <div class="bg-[#0a2525]/40 border border-white/10 backdrop-blur-xl rounded-[2.5rem] p-8 md:p-10 shadow-2xl">

            {{-- Header dengan logo --}}
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-[#a3e635]/10 rounded-2xl mb-4 border border-[#a3e635]/20 overflow-hidden">
                    <img src="{{ asset('images/icons.png') }}"
                         alt="MediaTools"
                         class="w-14 h-14 object-contain"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <i class="fa-solid fa-shield-halved text-[#a3e635] text-3xl hidden" style="display:none;"></i>
                </div>
                <h1 class="text-3xl font-bold tracking-tight">Welcome!</h1>
                <p class="text-gray-400 mt-2 text-sm">Masuk ke akun MediaTools Anda</p>
            </div>

            {{-- Session status --}}
            @if (session('status'))
                <div class="mb-5 p-3 bg-[#a3e635]/10 border border-[#a3e635]/20 rounded-xl text-[#a3e635] text-sm text-center">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Error global --}}
            @if ($errors->any() && !$errors->has('email') && !$errors->has('password'))
                <div class="mb-5 p-3 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm text-center">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                {{-- Email --}}
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Email Address</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-500 group-focus-within:text-[#a3e635] transition-colors">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <input type="email" name="email" id="email"
                               value="{{ old('email') }}"
                               required autofocus autocomplete="email"
                               class="w-full bg-white/5 border {{ $errors->has('email') ? 'border-red-500' : 'border-white/10' }} rounded-2xl py-3.5 pl-11 pr-4 outline-none focus:border-[#a3e635] focus:ring-1 focus:ring-[#a3e635]/20 transition-all text-sm"
                               placeholder="name@company.com">
                    </div>
                    @error('email')
                        <p class="text-red-400 text-xs mt-1 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="space-y-2">
                    <div class="flex justify-between items-center ml-1">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest">Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-[10px] text-[#a3e635] hover:underline uppercase font-bold">Lupa?</a>
                        @endif
                    </div>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-500 group-focus-within:text-[#a3e635] transition-colors">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input type="password" name="password" id="loginPassword"
                               required autocomplete="current-password"
                               class="w-full bg-white/5 border {{ $errors->has('password') ? 'border-red-500' : 'border-white/10' }} rounded-2xl py-3.5 pl-11 pr-12 outline-none focus:border-[#a3e635] focus:ring-1 focus:ring-[#a3e635]/20 transition-all text-sm"
                               placeholder="••••••••">
                        {{-- Toggle show/hide password --}}
                        <button type="button"
                                onclick="togglePassword('loginPassword', this)"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-500 hover:text-[#a3e635] transition-colors"
                                tabindex="-1" aria-label="Tampilkan password">
                            <i class="fa-solid fa-eye text-sm" id="loginPasswordIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-400 text-xs mt-1 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <label class="flex items-center gap-3 cursor-pointer group w-fit">
                    <div class="relative">
                        <input type="checkbox" name="remember" class="peer hidden">
                        <div class="w-5 h-5 border border-white/20 rounded-md bg-white/5 peer-checked:bg-[#a3e635] peer-checked:border-[#a3e635] transition-all"></div>
                        <i class="fa-solid fa-check absolute inset-0 text-[#020d0d] text-[10px] flex items-center justify-center opacity-0 peer-checked:opacity-100 pointer-events-none"></i>
                    </div>
                    <span class="text-sm text-gray-400 group-hover:text-gray-200 transition-colors">Ingat perangkat ini</span>
                </label>

                <button type="submit"
                        class="w-full bg-[#a3e635] text-[#020d0d] font-bold py-4 rounded-2xl hover:scale-[1.02] hover:shadow-lg hover:shadow-[#a3e635]/20 active:scale-95 transition-all">
                    Masuk
                </button>
            </form>

            <div class="mt-8 pt-8 border-t border-white/5 text-center">
                <p class="text-gray-500 text-sm">Belum punya akun?
                    <a href="{{ route('register') }}" class="text-[#a3e635] font-bold hover:underline">Daftar Gratis</a>
                </p>
            </div>
        </div>

        <p class="text-center text-gray-600 text-[10px] mt-8 uppercase tracking-[0.3em]">
            &copy; {{ date('Y') }} MediaTools Secure Access
        </p>
    </div>
</div>

@push('scripts')
<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon  = btn.querySelector('i');
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    icon.className = isHidden ? 'fa-solid fa-eye-slash text-sm' : 'fa-solid fa-eye text-sm';
    btn.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
}
</script>
@endpush
@endsection
