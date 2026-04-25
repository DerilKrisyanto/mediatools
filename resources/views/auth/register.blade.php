@extends('layouts.app')

@section('title', 'MediaTools — Daftar Akun Gratis')

@section('content')
<div class="min-h-screen bg-[#020d0d] text-white flex items-center justify-center px-4 pt-28 pb-12">

    {{-- Background blobs --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[10%] right-[10%] w-72 h-72 bg-blue-500/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[10%] left-[10%] w-72 h-72 bg-[#a3e635]/10 rounded-full blur-[120px]"></div>
    </div>

    <div class="w-full max-w-md relative">
        <div class="bg-[#0a2525]/40 border border-white/10 backdrop-blur-xl rounded-[2.5rem] p-8 md:p-10 shadow-2xl">

            {{-- Header dengan logo --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-[#a3e635]/10 rounded-2xl mb-4 border border-[#a3e635]/20 overflow-hidden">
                    <img src="{{ asset('images/icons.png') }}"
                         alt="MediaTools"
                         class="w-14 h-14 object-contain"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <i class="fa-solid fa-bolt text-[#a3e635] text-3xl hidden" style="display:none;"></i>
                </div>
                <h1 class="text-3xl font-bold tracking-tight">Get Started</h1>
                <p class="text-gray-400 mt-2 text-sm">Bergabung dengan ekosistem digital MediaTools</p>
            </div>

            {{-- Error / info global --}}
            @if ($errors->has('email') && str_contains($errors->first('email'), 'sudah terdaftar'))
                <div class="mb-5 p-3 bg-blue-500/10 border border-blue-500/20 rounded-xl text-blue-300 text-sm text-center">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    {{ $errors->first('email') }}
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                {{-- Nama --}}
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Nama Lengkap</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-500 group-focus-within:text-[#a3e635] transition-colors">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <input type="text" name="name" id="name"
                               value="{{ old('name') }}"
                               required autofocus autocomplete="name"
                               class="w-full bg-white/5 border {{ $errors->has('name') ? 'border-red-500' : 'border-white/10' }} rounded-2xl py-3.5 pl-11 pr-4 outline-none focus:border-[#a3e635] focus:ring-1 focus:ring-[#a3e635]/20 transition-all text-sm"
                               placeholder="John Doe">
                    </div>
                    @error('name')
                        <p class="text-red-400 text-xs mt-1 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Email Address</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-500 group-focus-within:text-[#a3e635] transition-colors">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <input type="email" name="email" id="email"
                               value="{{ old('email') }}"
                               required autocomplete="email"
                               class="w-full bg-white/5 border {{ $errors->has('email') ? 'border-red-500' : 'border-white/10' }} rounded-2xl py-3.5 pl-11 pr-4 outline-none focus:border-[#a3e635] focus:ring-1 focus:ring-[#a3e635]/20 transition-all text-sm"
                               placeholder="name@email.com">
                    </div>
                    @error('email')
                        @if (!str_contains($message, 'sudah terdaftar'))
                            <p class="text-red-400 text-xs mt-1 ml-1">{{ $message }}</p>
                        @endif
                    @enderror
                </div>

                {{-- Password --}}
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Password</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-500 group-focus-within:text-[#a3e635] transition-colors">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input type="password" name="password" id="regPassword"
                               required autocomplete="new-password"
                               class="w-full bg-white/5 border {{ $errors->has('password') ? 'border-red-500' : 'border-white/10' }} rounded-2xl py-3.5 pl-11 pr-12 outline-none focus:border-[#a3e635] focus:ring-1 focus:ring-[#a3e635]/20 transition-all text-sm"
                               placeholder="Min. 8 karakter">
                        <button type="button"
                                onclick="togglePassword('regPassword', this)"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-500 hover:text-[#a3e635] transition-colors"
                                tabindex="-1" aria-label="Tampilkan password">
                            <i class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-400 text-xs mt-1 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Konfirmasi Password</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-500 group-focus-within:text-[#a3e635] transition-colors">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <input type="password" name="password_confirmation" id="regPasswordConfirm"
                               required autocomplete="new-password"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-3.5 pl-11 pr-12 outline-none focus:border-[#a3e635] focus:ring-1 focus:ring-[#a3e635]/20 transition-all text-sm"
                               placeholder="Ulangi password">
                        <button type="button"
                                onclick="togglePassword('regPasswordConfirm', this)"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-500 hover:text-[#a3e635] transition-colors"
                                tabindex="-1" aria-label="Tampilkan konfirmasi password">
                            <i class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                {{-- Info OTP --}}
                <div class="flex items-start gap-3 p-3 bg-[#a3e635]/5 border border-[#a3e635]/15 rounded-xl">
                    <i class="fa-solid fa-envelope-circle-check text-[#a3e635] text-sm mt-0.5 flex-shrink-0"></i>
                    <p class="text-gray-400 text-xs leading-relaxed">
                        Kami akan mengirimkan <strong class="text-white">kode verifikasi (OTP)</strong> ke email Anda untuk memastikan keamanan akun.
                    </p>
                </div>

                <div class="pt-1">
                    <button type="submit"
                            class="w-full bg-[#a3e635] text-[#020d0d] font-bold py-4 rounded-2xl hover:scale-[1.02] hover:shadow-lg hover:shadow-[#a3e635]/20 active:scale-95 transition-all">
                        Buat Akun & Verifikasi Email
                    </button>
                </div>
            </form>

            <div class="mt-8 pt-8 border-t border-white/5 text-center">
                <p class="text-gray-500 text-sm">Sudah punya akun?
                    <a href="{{ route('login') }}" class="text-[#a3e635] font-bold hover:underline">Masuk</a>
                </p>
            </div>
        </div>
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
