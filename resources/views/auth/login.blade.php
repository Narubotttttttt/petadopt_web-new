<x-guest-layout>
    <div class="relative min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-[#146970] flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8 overflow-hidden">
        
        {{-- Ambient decorative background circles --}}
        <div class="absolute top-1/4 -left-20 w-96 h-96 bg-[#199CA4]/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-md w-full bg-white/95 dark:bg-[#0e1d20]/95 backdrop-blur-xl border border-white/40 dark:border-slate-800 rounded-3xl p-8 sm:p-10 shadow-2xl animate-fade-in">
            
            {{-- Logo --}}
            <div class="flex justify-center mb-6">
                <div class="relative">
                    <img src="{{ asset('images/caws-logo.png') }}" alt="CDO Animal Welfare Society Inc." class="h-20 w-20 rounded-full object-contain bg-white dark:bg-[#0e1d20] p-1.5 shadow-xl border-2 border-white dark:border-slate-700 ring-4 ring-[#199CA4]/25">
                </div>
            </div>

            {{-- Title --}}
            <div class="text-center mb-8">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">Staff Portal Login</h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 font-medium">CDO Animal Welfare Society Inc.</p>
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            {{-- UXHub: Accessible Error Handling - Error Summary Alert --}}
            @if ($errors->any())
                <div id="error-summary" role="alert" aria-live="assertive" class="mb-5 p-4 bg-rose-50/90 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-2xl text-xs text-rose-800 dark:text-rose-300 flex items-start gap-3 shadow-xs">
                    <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="font-bold text-[13px]">Please review the highlighted fields:</p>
                        <ul class="list-disc list-inside mt-1 space-y-0.5 text-xs text-rose-700 dark:text-rose-300">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" novalidate class="space-y-5">
                @csrf

                {{-- UXHub: Input Field Anatomy - Email Address --}}
                <div class="field">
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Email Address <span class="text-rose-500" title="Required">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <input id="email" name="email" type="email" required value="{{ old('email') }}" autofocus autocomplete="username"
                            placeholder="e.g. staff@caws.org"
                            aria-required="true"
                            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                            aria-describedby="email-help @if($errors->has('email')) email-error @endif"
                            class="w-full bg-slate-50/80 dark:bg-[#12272b] border @if($errors->has('email')) border-rose-500 dark:border-rose-500 ring-2 ring-rose-500/20 @else border-slate-200 dark:border-slate-700 @endif text-slate-800 dark:text-white rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/15 transition duration-150 placeholder-slate-400 dark:placeholder-slate-500 text-xs sm:text-sm font-medium">
                        <span class="absolute right-3.5 text-slate-400 dark:text-slate-500 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </span>
                    </div>
                    <p id="email-help" class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Enter your registered staff or administrator email address.</p>
                    @error('email')
                        <p id="email-error" role="alert" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400 font-semibold flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                {{-- UXHub: Password Field Best Practices & Anatomy --}}
                <div class="field">
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Password <span class="text-rose-500" title="Required">*</span>
                        </label>
                    </div>
                    <div class="relative flex items-center">
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                            placeholder="Enter your account password"
                            aria-required="true"
                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                            aria-describedby="password-help @if($errors->has('password')) password-error @endif"
                            class="w-full bg-slate-50/80 dark:bg-[#12272b] border @if($errors->has('password')) border-rose-500 dark:border-rose-500 ring-2 ring-rose-500/20 @else border-slate-200 dark:border-slate-700 @endif text-slate-800 dark:text-white rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/15 transition duration-150 placeholder-slate-400 dark:placeholder-slate-500 text-xs sm:text-sm font-medium no-native-toggle">
                        
                        <button type="button" onclick="togglePassword()" id="password-toggle-btn" aria-label="Show password" aria-pressed="false" class="absolute right-3 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none p-1 cursor-pointer">
                            <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    <p id="password-help" class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Minimum 8 characters.</p>
                    @error('password')
                        <p id="password-error" role="alert" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400 font-semibold flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label for="remember_me" class="inline-flex items-center text-slate-600 dark:text-slate-300 cursor-pointer">
                        <input id="remember_me" type="checkbox" class="rounded border-slate-300 dark:border-slate-700 text-[#199CA4] focus:ring-[#199CA4] w-4 h-4 bg-white dark:bg-[#12272b]" name="remember">
                        <span class="ml-2 font-semibold">Remember Me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="font-bold text-[#199CA4] dark:text-[#41C1CB] hover:underline" href="{{ route('password.request') }}">
                            Forgot Password?
                        </a>
                    @endif
                </div>

                {{-- UXHub: Writing Button Labels - Active Verb --}}
                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-[#199CA4] to-[#14838B] hover:from-[#146970] hover:to-[#12585e] text-white font-extrabold rounded-xl shadow-lg shadow-[#199CA4]/30 active:scale-[0.98] transition duration-150 tracking-wide text-sm cursor-pointer">
                        Sign In to Dashboard
                    </button>
                </div>

                @if (Route::has('register'))
                    <div class="text-center text-xs pt-2 text-slate-500 dark:text-slate-400 font-medium">
                        Need an account?
                        <a href="{{ route('register') }}" class="font-bold text-[#199CA4] dark:text-[#41C1CB] hover:underline">
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
            const toggleBtn = document.getElementById('password-toggle-btn');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.setAttribute('aria-pressed', 'true');
                toggleBtn.setAttribute('aria-label', 'Hide password');
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
            } else {
                passwordInput.type = 'password';
                toggleBtn.setAttribute('aria-pressed', 'false');
                toggleBtn.setAttribute('aria-label', 'Show password');
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
            }
        }

        // UXHub Field Guide: Accessible Error Handling - Automatically move focus to first error field
        document.addEventListener('DOMContentLoaded', function() {
            const firstInvalid = document.querySelector('[aria-invalid="true"]');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        });
    </script>
</x-guest-layout>