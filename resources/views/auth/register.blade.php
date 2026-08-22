<x-guest-layout>
    <div class="relative min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-[#146970] flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8 overflow-hidden">
        
        {{-- Ambient decorative background circles --}}
        <div class="absolute top-1/4 -left-20 w-96 h-96 bg-[#199CA4]/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>


        {{-- Main Registration Card --}}
        <div class="relative z-10 max-w-2xl w-full bg-white/95 dark:bg-[#0e1d20]/95 backdrop-blur-xl border border-white/40 dark:border-slate-800 rounded-3xl p-8 sm:p-10 shadow-2xl animate-fade-in relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#199CA4] via-[#41C1CB] to-[#14838B]"></div>

            <div class="text-center mb-8 pt-2">
                <div class="flex justify-center mb-4">
                    <img src="{{ asset('images/caws-logo.jpg') }}" alt="CAWS Logo" class="h-16 w-16 rounded-2xl object-cover shadow-lg border-2 border-white dark:border-slate-700 ring-4 ring-[#199CA4]/20">
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">
                    {{ $pageTitle ?? 'Create Staff Account' }}
                </h2>
                <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium">
                    {{ $pageSubtitle ?? 'Add a new authorized account for the CAWS dashboard.' }}
                </p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <input type="hidden" name="role" value="{{ $registerRole ?? 'staff' }}" />

                <div class="bg-[#F0FBFB] dark:bg-[#12272b] border border-[#199CA4]/20 dark:border-[#199CA4]/30 rounded-2xl p-4 flex items-center justify-between shadow-2xs">
                    <div>
                        <span class="block text-[10px] font-bold tracking-wider text-[#199CA4] dark:text-[#41C1CB] uppercase">Account Permission</span>
                        <span class="text-base font-extrabold text-slate-800 dark:text-white">{{ ucfirst($registerRole ?? 'staff') }}</span>
                    </div>
                    <div class="bg-[#199CA4] text-white text-xs font-extrabold px-3.5 py-1 rounded-xl shadow-xs">
                        {{ $registerRole === 'admin' ? 'Admin Access' : 'Staff Access' }}
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Full Name <span class="text-rose-500">*</span></label>
                        <div class="relative flex items-center">
                            <input id="name" name="name" type="text" required value="{{ old('name') }}" autofocus autocomplete="name"
                                placeholder="Juan Dela Cruz"
                                class="w-full bg-slate-50/80 dark:bg-[#12272b] border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/15 transition duration-150 text-xs sm:text-sm font-semibold" />
                            <span class="absolute right-3 text-slate-400 dark:text-slate-500 pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                        </div>
                        @error('name') <span class="text-rose-600 dark:text-rose-400 text-xs mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Email Address <span class="text-rose-500">*</span></label>
                        <div class="relative flex items-center">
                            <input id="email" name="email" type="email" required value="{{ old('email') }}" autocomplete="username"
                                placeholder="staff@caws.org"
                                class="w-full bg-slate-50/80 dark:bg-[#12272b] border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/15 transition duration-150 text-xs sm:text-sm font-semibold" />
                            <span class="absolute right-3 text-slate-400 dark:text-slate-500 pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                        </div>
                        @error('email') <span class="text-rose-600 dark:text-rose-400 text-xs mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Password <span class="text-rose-500">*</span></label>
                        <div class="relative flex items-center">
                            <input id="password" name="password" type="password" required autocomplete="new-password"
                                placeholder="••••••••"
                                class="w-full bg-slate-50/80 dark:bg-[#12272b] border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/15 transition duration-150 text-xs sm:text-sm font-semibold no-native-toggle" />
                            <button type="button" onclick="togglePassword('password', 'eye-icon-1')" class="absolute right-3 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none p-1 cursor-pointer">
                                <svg id="eye-icon-1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        @error('password') <span class="text-rose-600 dark:text-rose-400 text-xs mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Confirm Password <span class="text-rose-500">*</span></label>
                        <div class="relative flex items-center">
                            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                                placeholder="••••••••"
                                class="w-full bg-slate-50/80 dark:bg-[#12272b] border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/15 transition duration-150 text-xs sm:text-sm font-semibold no-native-toggle" />
                            <button type="button" onclick="togglePassword('password_confirmation', 'eye-icon-2')" class="absolute right-3 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none p-1 cursor-pointer">
                                <svg id="eye-icon-2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <a href="{{ Auth::check() ? route('users.index') : route('login') }}"
                        class="w-1/3 py-3.5 px-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-[#199CA4] hover:bg-[#F0FBFB] dark:hover:bg-[#12272b] text-slate-600 dark:text-slate-300 hover:text-[#199CA4] dark:hover:text-[#41C1CB] font-extrabold text-xs sm:text-sm text-center transition flex items-center justify-center gap-1.5 group">
                        <span>Cancel</span>
                    </a>
                    <button type="submit" class="flex-1 py-3.5 px-4 bg-gradient-to-r from-[#199CA4] to-[#14838B] hover:from-[#146970] hover:to-[#12585e] text-white font-extrabold rounded-xl shadow-lg shadow-[#199CA4]/30 active:scale-[0.98] transition duration-150 tracking-wide text-xs sm:text-sm cursor-pointer">
                        Create Account
                    </button>
                </div>

                @if (Route::has('login'))
                    <div class="text-center text-xs pt-2 text-slate-500 dark:text-slate-400 font-medium">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-bold text-[#199CA4] dark:text-[#41C1CB] hover:underline">
                            Sign In
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