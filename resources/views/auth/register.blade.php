@extends('layouts.app')

@section('title', 'MediaTools - Create Account')

@section('content')
<div class="min-h-screen bg-[#020d0d] text-white flex items-center justify-center px-4 pt-28 pb-12">
    <!-- Background Decoration -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[10%] right-[10%] w-72 h-72 bg-blue-500/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[10%] left-[10%] w-72 h-72 bg-[#a3e635]/10 rounded-full blur-[120px]"></div>
    </div>

    <div class="w-full max-w-md relative">
        <div class="bg-[#0a2525]/40 border border-white/10 backdrop-blur-xl rounded-[2.5rem] p-8 md:p-10 shadow-2xl">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold tracking-tight">Get Started</h1>
                <p class="text-gray-400 mt-2 text-sm">Join MediaTools digital ecosystem</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf
                
                <!-- Name -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Full Name</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-500 group-focus-within:text-[#a3e635] transition-colors">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus
                            class="w-full bg-white/5 border border-white/10 rounded-2xl py-3.5 pl-11 pr-4 outline-none focus:border-[#a3e635] focus:ring-1 focus:ring-[#a3e635]/20 transition-all text-sm"
                            placeholder="John Doe">
                    </div>
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Email Address -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Email Address</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-500 group-focus-within:text-[#a3e635] transition-colors">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full bg-white/5 border border-white/10 rounded-2xl py-3.5 pl-11 pr-4 outline-none focus:border-[#a3e635] focus:ring-1 focus:ring-[#a3e635]/20 transition-all text-sm"
                            placeholder="name@email.com">
                    </div>
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Password -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Password</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-500 group-focus-within:text-[#a3e635] transition-colors">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input type="password" name="password" required
                            class="w-full bg-white/5 border border-white/10 rounded-2xl py-3.5 pl-11 pr-4 outline-none focus:border-[#a3e635] focus:ring-1 focus:ring-[#a3e635]/20 transition-all text-sm"
                            placeholder="Min. 8 characters">
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Confirm Password</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-500 group-focus-within:text-[#a3e635] transition-colors">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <input type="password" name="password_confirmation" required
                            class="w-full bg-white/5 border border-white/10 rounded-2xl py-3.5 pl-11 pr-4 outline-none focus:border-[#a3e635] focus:ring-1 focus:ring-[#a3e635]/20 transition-all text-sm"
                            placeholder="Repeat password">
                    </div>
                    @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-[#a3e635] text-[#020d0d] font-bold py-4 rounded-2xl hover:scale-[1.02] active:scale-95 transition-all shadow-lg shadow-[#a3e635]/10">
                        Create Account
                    </button>
                </div>
            </form>

            <div class="mt-8 pt-8 border-t border-white/5 text-center">
                <p class="text-gray-500 text-sm">Already a member? 
                    <a href="{{ route('login') }}" class="text-[#a3e635] font-bold hover:underline">Sign In</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection