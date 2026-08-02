<x-guest-layout>
    
    <div class="relative min-h-screen bg-teal-100 flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        
    
        <div class="relative z-10 max-w-md w-full bg-white/40 backdrop-blur-xl border border-white/60 rounded-[30px] p-10 shadow-[0_20px_50px_rgba(0,0,0,0.1)]">
            
            
            <div class="flex justify-center mb-6">
                <img src="{{ asset('images/caws-logo.jpg') }}" alt="CAWS Logo" class="h-20 w-20 rounded-full object-cover shadow-md border-2 border-white/80">
            </div>

        
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-900 tracking-wider">LOGIN</h2>
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-900 mb-1">Email</label>
                    <div class="relative flex items-center">
                        <input id="email" name="email" type="email" required value="{{ old('email') }}" autofocus autocomplete="username"
                            class="w-full bg-gray-50/60 border border-gray-300 text-gray-900 rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-teal-700 focus:ring-1 focus:ring-teal-700 transition duration-150 placeholder-gray-400">
                        <span class="absolute right-3 text-gray-500 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </span>
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-600" />
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-900 mb-1">Password</label>
                    <div class="relative flex items-center">
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                            class="w-full bg-gray-50/60 border border-gray-300 text-gray-900 rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-teal-700 focus:ring-1 focus:ring-teal-700 transition duration-150 placeholder-gray-400 no-native-toggle">
                        
                        <button type="button" onclick="togglePassword()" class="absolute right-3 text-gray-500 hover:text-gray-700 focus:outline-none">
                            <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-600" />
                </div>

                
                <div class="flex items-center justify-between text-sm pt-2">
                    <label for="remember_me" class="inline-flex items-center text-gray-900 cursor-pointer">
                        <input id="remember_me" type="checkbox" class="rounded border-gray-400 text-teal-700 focus:ring-teal-700 w-4 h-4 bg-transparent" name="remember">
                        <span class="ml-2 font-medium">Remember Me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="font-medium text-gray-900 hover:text-teal-800 transition duration-150" href="{{ route('password.request') }}">
                            Forgot <span class="font-bold underline">Password?</span>
                        </a>
                    @endif
                </div>

                
                <div class="pt-4">
                    <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-teal-600 to-teal-800 hover:from-teal-700 hover:to-teal-900 text-white font-semibold rounded-xl shadow-lg active:scale-95 transition duration-150 tracking-wide">
                        Login
                    </button>
                </div>

                
                @if (Route::has('register'))
                    <div class="text-center text-sm pt-2 text-gray-700">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="font-semibold text-teal-800 hover:text-teal-900 underline transition duration-150">
                            Create Account
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    
    <style>
        
        .no-native-toggle::-ms-reveal,
        .no-native-toggle::-ms-clear {
            display: none;
        }
        
        .no-native-toggle::-webkit-credentials-auto-fill-button,
        .no-native-toggle::-webkit-strong-password-auto-fill-button {
            visibility: hidden;
            display: none !important;
            pointer-events: none;
            position: absolute;
            right: 0;
        }
    </style>

    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
            } else {
                passwordInput.type = 'password';
                
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
            }
        }
    </script>
</x-guest-layout>