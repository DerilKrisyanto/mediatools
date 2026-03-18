@extends('layouts.app')

@section('title', 'MediaTools - Login')

@section('content')
<div class="min-h-screen bg-[#020d0d] text-white flex items-center justify-center px-4 pt-20">
    <!-- Background Decoration -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[20%] left-[10%] w-72 h-72 bg-[#a3e635]/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[20%] right-[10%] w-72 h-72 bg-blue-500/10 rounded-full blur-[120px]"></div>
    </div>

    <div class="w-full max-w-md relative">
        <div class="bg-[#0a2525]/40 border border-white/10 backdrop-blur-xl rounded-[2.5rem] p-8 md:p-10 shadow-2xl">
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-[#a3e635]/10 rounded-2xl mb-4 border border-[#a3e635]/20">
                    <i class="fa-solid fa-shield-halved text-[#a3e635] text-2xl"></i>
                </div>
                <h1 class="text-3xl font-bold tracking-tight">Welcome Back</h1>
                <p class="text-gray-400 mt-2 text-sm">Please enter your details to sign in</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                
                <!-- Email Address -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Email Address</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-500 group-focus-within:text-[#a3e635] transition-colors">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full bg-white/5 border border-white/10 rounded-2xl py-3.5 pl-11 pr-4 outline-none focus:border-[#a3e635] focus:ring-1 focus:ring-[#a3e635]/20 transition-all text-sm"
                            placeholder="name@company.com">
                    </div>
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Password -->
                <div class="space-y-2">
                    <div class="flex justify-between items-center ml-1">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest">Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-[10px] text-[#a3e635] hover:underline uppercase font-bold">Forgot?</a>
                        @endif
                    </div>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-500 group-focus-within:text-[#a3e635] transition-colors">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input type="password" name="password" required
                            class="w-full bg-white/5 border border-white/10 rounded-2xl py-3.5 pl-11 pr-4 outline-none focus:border-[#a3e635] focus:ring-1 focus:ring-[#a3e635]/20 transition-all text-sm"
                            placeholder="••••••••">
                    </div>
                    @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Remember Me -->
                <label class="flex items-center gap-3 cursor-pointer group w-fit">
                    <div class="relative">
                        <input type="checkbox" name="remember" class="peer hidden">
                        <div class="w-5 h-5 border border-white/20 rounded-md bg-white/5 peer-checked:bg-[#a3e635] peer-checked:border-[#a3e635] transition-all"></div>
                        <i class="fa-solid fa-check absolute inset-0 text-[#020d0d] text-[10px] flex items-center justify-center opacity-0 peer-checked:opacity-100"></i>
                    </div>
                    <span class="text-sm text-gray-400 group-hover:text-gray-200 transition-colors">Remember device</span>
                </label>

                <button type="submit" class="w-full bg-[#a3e635] text-[#020d0d] font-bold py-4 rounded-2xl hover:scale-[1.02] active:scale-95 transition-all shadow-lg shadow-[#a3e635]/10">
                    Sign In
                </button>
            </form>

            <div class="mt-8 pt-8 border-t border-white/5 text-center">
                <p class="text-gray-500 text-sm">Don't have an account? 
                    <a href="{{ route('register') }}" class="text-[#a3e635] font-bold hover:underline">Create Account</a>
                </p>
            </div>
        </div>
        
        <!-- Footer -->
        <p class="text-center text-gray-600 text-[10px] mt-8 uppercase tracking-[0.3em]">
            &copy; {{ date('Y') }} MediaTools Secure Access
        </p>
    </div>
</div>
@endsection