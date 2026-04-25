@extends('layouts.app')
@section('title', 'Masuk — MediaTools')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 pt-24 pb-16">
    <div class="w-full max-w-md">

        <div class="relative overflow-hidden rounded-3xl border" style="background:var(--card-bg);border-color:var(--border-accent);">
            <div class="absolute inset-0 bg-gradient-to-br from-[#a3e635]/4 to-transparent pointer-events-none"></div>
            <div class="relative z-10 p-8 sm:p-10">

                {{-- Header --}}
                <div class="text-center mb-8">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 mb-6">
                        <img src="{{ asset('images/icons.png') }}" alt="Logo" class="w-8 h-8 object-contain">
                        <span class="text-lg font-bold text-white">MEDIA<span class="text-[#a3e635]">TOOLS.</span></span>
                    </a>
                    <h1 class="text-2xl font-extrabold text-white mb-1">Masuk ke Akun</h1>
                    <p class="text-sm" style="color:var(--text-muted);">Kami akan mengirimkan kode OTP. Pastikan email yang terdaftar masih aktif</p>
                </div>

                @if(session('success'))
                    <div class="mb-4 p-4 rounded-xl text-sm font-semibold" style="background:rgba(163,230,53,0.1);border:1px solid rgba(163,230,53,0.2);color:#a3e635;">
                        <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-4 rounded-xl text-sm font-semibold" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);color:#fca5a5;">
                        <i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('auth.login') }}" class="space-y-5">
                    @csrf

                    <div class="form-group">
                        <label class="block text-xs font-bold mb-2 uppercase tracking-wider" style="color:var(--text-dim);">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="form-input @error('email') border-red-500 @enderror"
                               placeholder="email@gmail.com" autofocus>
                    </div>

                    <div class="form-group">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold uppercase tracking-wider" style="color:var(--text-dim);">Password</label>
                            {{-- Tambahkan link forgot password jika ada --}}
                            {{-- <a href="#" class="text-xs" style="color:#a3e635;">Lupa password?</a> --}}
                        </div>
                        <div class="relative">
                            <input type="password" name="password" id="password" required
                                   class="form-input pr-12 @error('password') border-red-500 @enderror"
                                   placeholder="Password Anda">
                            <button type="button" onclick="togglePwd()"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
                                <i class="fa-solid fa-eye text-sm" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-600 bg-transparent accent-[#a3e635]">
                        <span class="text-sm font-medium" style="color:var(--text-dim);">Ingat saya di browser ini</span>
                    </label>

                    <button type="submit" class="btn-primary w-full py-4 text-sm mt-2">
                        <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                        <span>Masuk</span>
                    </button>
                </form>

                <p class="text-center mt-6 text-sm" style="color:var(--text-muted);">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-bold" style="color:#a3e635;">Daftar disini</a>
                </p>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePwd() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eyeIcon');
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'text' ? 'fa-solid fa-eye-slash text-sm' : 'fa-solid fa-eye text-sm';
}
</script>
@endpush
@endsection