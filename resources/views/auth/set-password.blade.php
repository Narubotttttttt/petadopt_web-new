<x-guest-layout>
    <div class="relative min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-[#146970] flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8 overflow-hidden">
        {{-- Ambient decorative background circles --}}
        <div class="absolute top-1/4 -left-20 w-96 h-96 bg-[#199CA4]/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Main Registration Card --}}
        <div class="relative z-10 max-w-xl w-full bg-white/95 dark:bg-[#0e1d20]/95 backdrop-blur-xl border border-white/40 dark:border-slate-800 rounded-3xl p-8 sm:p-10 shadow-2xl animate-fade-in relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#199CA4] via-[#41C1CB] to-[#14838B]"></div>

            {{-- Brand & Header --}}
            <div class="text-center mb-6 pt-2">
                <div class="flex justify-center mb-5">
                    <div class="relative">
                        <img src="{{ asset('images/caws-logo.png') }}" alt="CDO Animal Welfare Society Inc." class="h-16 w-16 rounded-full object-contain bg-white dark:bg-[#0e1d20] p-1 shadow-xl border-2 border-white dark:border-slate-700 ring-4 ring-[#199CA4]/25">
                    </div>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">
                    {{ $pageTitle ?? 'Set Account Password' }}
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium">
                    {{ $pageSubtitle ?? 'Step 2 of 2: Create a secure password to complete your account.' }}
                </p>
            </div>

            {{-- Step Indicator Bar --}}
            <div class="mb-6" aria-label="Registration Steps">
                <div class="grid grid-cols-2 gap-3 p-1.5 bg-slate-100 dark:bg-slate-900/60 rounded-2xl border border-slate-200/80 dark:border-slate-800">
                    <div class="flex items-center justify-center gap-2.5 py-2.5 px-3 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/30 text-emerald-800 dark:text-emerald-300 border border-emerald-300/40">
                        <span class="w-6 h-6 rounded-full bg-emerald-500 text-white text-xs font-extrabold flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <div class="text-left leading-tight">
                            <span class="block text-[10px] uppercase font-bold tracking-wider text-emerald-700 dark:text-emerald-300">Step 1 (Verified)</span>
                            <span class="text-xs font-extrabold">Identity and Email</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-center gap-2.5 py-2.5 px-3 rounded-xl bg-white dark:bg-[#12272b] text-slate-800 dark:text-white shadow-xs border border-[#199CA4]/30">
                        <span class="w-6 h-6 rounded-full bg-[#199CA4] text-white text-xs font-extrabold flex items-center justify-center">2</span>
                        <div class="text-left leading-tight">
                            <span class="block text-[10px] uppercase font-bold tracking-wider text-[#199CA4]">Step 2 (Active)</span>
                            <span class="text-xs font-extrabold">Set Password</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Verified Account Summary Banner --}}
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-extrabold text-slate-800 dark:text-white">{{ $registrationData['name'] ?? 'Authorized User' }}</span>
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/60 px-2 py-0.5 rounded-full">
                                Verified
                            </span>
                        </div>
                        <span class="block text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5 break-all">
                            {{ $registrationData['email'] ?? '' }}
                        </span>
                    </div>
                </div>
                <a href="{{ route('register', [], false) }}" class="text-xs font-bold text-[#199CA4] hover:underline whitespace-nowrap">
                    Edit Identity
                </a>
            </div>

            {{-- Server Errors Alert --}}
            @if ($errors->any())
                <div id="error-summary" role="alert" class="mb-6 p-4 bg-rose-50/90 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-2xl text-xs text-rose-800 dark:text-rose-300 flex items-start gap-3 shadow-xs">
                    <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="font-bold text-[13px]">Please correct the following errors:</p>
                        <ul class="list-disc list-inside mt-1 space-y-0.5 text-xs text-rose-700 dark:text-rose-300">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Page 2 Form: Password Setup --}}
            <form method="POST" action="{{ route('register.set-password.store', [], false) }}" class="space-y-5">
                @csrf

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Password <span class="text-rose-500" title="Required">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <input id="password" name="password" type="password" required autofocus autocomplete="new-password"
                            placeholder="At least 8 characters"
                            class="w-full bg-slate-50/80 dark:bg-[#12272b] border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/15 transition duration-150 text-xs sm:text-sm font-semibold" />
                        <button type="button" onclick="togglePassword('password', 'eye-icon-1')" aria-label="Toggle password visibility" class="absolute right-3 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none p-1 cursor-pointer">
                            <svg id="eye-icon-1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Minimum 8 characters. We recommend combining letters, numbers, and symbols.</p>
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Confirm Password <span class="text-rose-500" title="Required">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                            placeholder="Re-type your password"
                            class="w-full bg-slate-50/80 dark:bg-[#12272b] border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/15 transition duration-150 text-xs sm:text-sm font-semibold" />
                        <button type="button" onclick="togglePassword('password_confirmation', 'eye-icon-2')" aria-label="Toggle confirm password visibility" class="absolute right-3 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none p-1 cursor-pointer">
                            <svg id="eye-icon-2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Must match the password entered above.</p>
                </div>

                {{-- Action Navigation --}}
                <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800/80">
                    <a href="{{ route('register', [], false) }}"
                        class="inline-flex items-center gap-2 py-3 px-5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-[#199CA4] hover:bg-[#F0FBFB] dark:hover:bg-[#12272b] text-slate-600 dark:text-slate-300 font-extrabold text-xs transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Back to Step 1</span>
                    </a>
                    <button type="submit"
                        class="py-3.5 px-8 bg-gradient-to-r from-[#199CA4] to-[#14838B] hover:from-[#146970] hover:to-[#12585e] text-white font-extrabold rounded-xl shadow-lg shadow-[#199CA4]/30 active:scale-[0.98] transition duration-150 tracking-wide text-xs sm:text-sm cursor-pointer">
                        {{ ($registrationData['role'] ?? '') === 'admin' ? 'Complete Admin Registration' : 'Create Staff Account' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);

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
