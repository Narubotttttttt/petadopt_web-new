<x-guest-layout>
    <div class="relative min-h-screen bg-slate-100 dark:bg-[#0A0B10] flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8 overflow-hidden">
        
        {{-- Ambient decorative background blur --}}
        <div class="absolute top-1/4 -left-20 w-96 h-96 bg-[#199CA4]/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-[#146970]/20 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Split Card Container --}}
        <div class="relative z-10 w-full max-w-4xl bg-white dark:bg-[#12141C] rounded-3xl shadow-2xl overflow-hidden border border-slate-200/80 dark:border-white/[0.08] flex flex-col md:flex-row animate-fade-in">
            
            {{-- Left Panel: Sign In Form --}}
            <div class="w-full md:w-1/2 p-8 sm:p-12 flex flex-col justify-center">
                {{-- Form Header with Title and Social / Contact Icons --}}
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Sign In</h2>
                    </div>

                    {{-- Social / Brand Circular Icons --}}
                    <div class="flex items-center gap-2">
                        <a href="https://www.facebook.com" target="_blank" rel="noopener" title="Facebook" 
                           class="w-9 h-9 rounded-full bg-slate-100 dark:bg-white/[0.06] hover:bg-[#199CA4]/10 hover:text-[#199CA4] text-slate-500 dark:text-slate-400 flex items-center justify-center text-xs transition duration-150 border border-slate-200/60 dark:border-white/[0.06]">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="mailto:info@caws.org" title="Contact Us" 
                           class="w-9 h-9 rounded-full bg-slate-100 dark:bg-white/[0.06] hover:bg-[#199CA4]/10 hover:text-[#199CA4] text-slate-500 dark:text-slate-400 flex items-center justify-center text-xs transition duration-150 border border-slate-200/60 dark:border-white/[0.06]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </a>
                    </div>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                {{-- Informational Session Notice --}}
                @if (session('info'))
                    <div class="mb-5 p-3.5 bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800/60 rounded-2xl text-xs text-teal-800 dark:text-teal-300 flex items-start gap-2.5 shadow-xs">
                        <svg class="w-4 h-4 text-[#199CA4] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                {{-- Accessible Error Summary Alert --}}
                @if ($errors->any())
                    <div id="error-summary" role="alert" aria-live="assertive" class="mb-5 p-3.5 bg-rose-50/90 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-2xl text-xs text-rose-800 dark:text-rose-300 flex items-start gap-2.5 shadow-xs">
                        <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <p class="font-bold text-xs">Please review your credentials:</p>
                            <ul class="list-disc list-inside mt-0.5 space-y-0.5 text-[11px] text-rose-700 dark:text-rose-300">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" novalidate class="space-y-4">
                    @csrf

                    {{-- USERNAME / EMAIL --}}
                    <div class="field">
                        <label for="email" class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                            Email
                        </label>
                        <div class="relative flex items-center">
                            <input id="email" name="email" type="email" required value="{{ old('email') }}" autofocus autocomplete="username"
                                placeholder="Email"
                                aria-required="true"
                                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                                class="w-full bg-slate-100/90 dark:bg-white/[0.05] border-0 text-slate-800 dark:text-white rounded-full px-5 py-3.5 focus:outline-none focus:ring-2 focus:ring-[#199CA4] transition duration-150 placeholder-slate-400 dark:placeholder-slate-500 text-xs sm:text-sm font-medium">
                        </div>
                        @error('email')
                            <p role="alert" class="mt-1 px-3 text-[11px] text-rose-600 dark:text-rose-400 font-semibold">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- PASSWORD --}}
                    <div class="field">
                        <label for="password" class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                            Password
                        </label>
                        <div class="relative flex items-center">
                            <input id="password" name="password" type="password" required autocomplete="current-password"
                                placeholder="Password"
                                aria-required="true"
                                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                class="w-full bg-slate-100/90 dark:bg-white/[0.05] border-0 text-slate-800 dark:text-white rounded-full px-5 py-3.5 pr-12 focus:outline-none focus:ring-2 focus:ring-[#199CA4] transition duration-150 placeholder-slate-400 dark:placeholder-slate-500 text-xs sm:text-sm font-medium no-native-toggle">
                            
                            <button type="button" onclick="togglePassword()" id="password-toggle-btn" aria-label="Show password" aria-pressed="false" class="absolute right-4 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none cursor-pointer">
                                <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p role="alert" class="mt-1 px-3 text-[11px] text-rose-600 dark:text-rose-400 font-semibold">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Sign In Button --}}
                    <div class="pt-2">
                        <button type="submit" class="w-full py-3.5 px-6 bg-gradient-to-r from-[#199CA4] to-[#13787F] hover:from-[#13787F] hover:to-[#0D5B62] text-white font-bold rounded-full shadow-lg shadow-[#199CA4]/25 active:scale-[0.98] transition duration-150 text-xs sm:text-sm tracking-wide cursor-pointer">
                            Sign In
                        </button>
                    </div>

                    {{-- Bottom Options Row: Remember Me & Forgot Password --}}
                    <div class="flex items-center justify-between text-xs pt-1 px-1">
                        <label for="remember_me" class="inline-flex items-center text-[#199CA4] dark:text-[#41C1CB] font-bold cursor-pointer">
                            <input id="remember_me" type="checkbox" class="rounded border-slate-300 dark:border-slate-700 text-[#199CA4] focus:ring-[#199CA4] w-4 h-4 bg-white dark:bg-[#12272b] accent-[#199CA4]" name="remember">
                            <span class="ml-2 font-semibold">Remember Me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="font-medium text-slate-400 dark:text-slate-500 hover:text-[#199CA4] dark:hover:text-white transition" href="{{ route('password.request') }}">
                                Forgot Password
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Right Panel: CAWS Mission & Mobile Adopter Guidance --}}
            <div class="w-full md:w-1/2 bg-gradient-to-br from-[#199CA4] via-[#147980] to-[#0D5B62] p-8 sm:p-12 flex flex-col items-center justify-center text-center text-white relative overflow-hidden">
                {{-- Decorative Ambient Glows --}}
                <div class="absolute -top-16 -right-16 w-60 h-60 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute -bottom-16 -left-16 w-60 h-60 bg-black/10 rounded-full blur-2xl pointer-events-none"></div>

                @php
                    $adminExists = $adminExists ?? \App\Models\User::where('role', 'admin')->exists();
                @endphp

                <div class="relative z-10 max-w-xs space-y-4">
                    {{-- Logo Badge --}}
                    <div class="inline-flex p-2 rounded-full bg-white/15 backdrop-blur-xs border border-white/20 shadow-lg mb-1">
                        <img src="{{ asset('images/caws-logo.png') }}" alt="CDO Animal Welfare Society" class="h-16 w-16 rounded-full object-contain bg-white p-1">
                    </div>

                    @if(! $adminExists)
                        <h3 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-snug">
                            Initial Setup
                        </h3>
                        <p class="text-teal-100/90 text-xs sm:text-sm font-medium leading-relaxed">
                            No administrator account detected. Set up the primary administrator account to get started.
                        </p>

                        @if (Route::has('register'))
                            <div class="pt-2">
                                <a href="{{ route('register') }}" 
                                   class="inline-flex items-center justify-center px-8 py-2.5 rounded-full border-2 border-white text-white font-extrabold text-xs sm:text-sm hover:bg-white hover:text-[#0D5B62] active:scale-95 transition-all duration-150 shadow-sm">
                                    Setup Administrator
                                </a>
                            </div>
                        @endif
                    @else
                        <h3 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-snug">
                            CDO Animal Welfare Society
                        </h3>
                        <p class="text-teal-100/95 text-xs sm:text-sm font-medium leading-relaxed">
                            Rescuing, rehabilitating, and rehoming companion animals across Cagayan de Oro.
                        </p>

                        {{-- Mobile Adopter Guidance Box --}}
                        <div class="my-3 p-3.5 rounded-2xl bg-white/10 border border-white/15 text-left text-xs space-y-1 backdrop-blur-xs">
                            <div class="flex items-center gap-2 font-bold text-white text-[11px] uppercase tracking-wider">
                                <svg class="w-3.5 h-3.5 text-teal-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span>Looking to Adopt?</span>
                            </div>
                            <p class="text-teal-100/80 text-[11px] leading-relaxed">
                                Pet adoption applications and check-ins are managed through the CAWS Mobile App. This web portal is reserved for authorized shelter operations.
                            </p>
                        </div>

                        <div class="pt-1 flex flex-col gap-2.5 w-full">
                            {{-- Firebase App Distribution Download Link --}}
                            <a href="{{ config('services.firebase.app_distribution_url', 'https://appdistribution.firebase.dev') }}" 
                               target="_blank" 
                               rel="noopener" 
                               title="Download Mobile App via Firebase App Distribution"
                               class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full bg-white text-[#0D5B62] font-extrabold text-xs sm:text-sm hover:bg-teal-50 active:scale-95 transition-all duration-150 shadow-md">
                                <svg class="w-4 h-4 text-[#199CA4]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Get Mobile App (Firebase)</span>
                            </a>

                            <a href="https://www.facebook.com" target="_blank" rel="noopener" 
                               class="inline-flex items-center justify-center gap-2 px-6 py-2 rounded-full border border-white/40 text-white font-semibold text-xs hover:bg-white/10 active:scale-95 transition-all duration-150">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                <span>Visit CAWS Community</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

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

        document.addEventListener('DOMContentLoaded', function() {
            const firstInvalid = document.querySelector('[aria-invalid="true"]');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        });
    </script>
</x-guest-layout>