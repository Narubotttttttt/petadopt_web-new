<x-guest-layout>
    <div class="relative min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-[#146970] flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8 overflow-hidden">
        {{-- Ambient decorative background circles --}}
        <div class="absolute top-1/4 -left-20 w-96 h-96 bg-[#199CA4]/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Main Card --}}
        <div class="relative z-10 max-w-lg w-full bg-white/95 dark:bg-[#0e1d20]/95 backdrop-blur-xl border border-white/40 dark:border-slate-800 rounded-3xl p-8 sm:p-10 shadow-2xl animate-fade-in overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#199CA4] via-[#41C1CB] to-[#14838B]"></div>

            {{-- Brand & Header --}}
            <div class="text-center mb-6 pt-2">
                <div class="flex justify-center mb-5">
                    <div class="relative">
                        <img src="{{ asset('images/caws-logo.png') }}" alt="CDO Animal Welfare Society Inc." class="h-16 w-16 rounded-full object-contain bg-white dark:bg-[#0e1d20] p-1 shadow-xl border-2 border-white dark:border-slate-700 ring-4 ring-[#199CA4]/25">
                        <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-[#199CA4] text-white flex items-center justify-center shadow-md">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">
                    Verify Your Email
                </h1>
                <p class="mt-1.5 text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium">
                    CDO Animal Welfare Society Inc. Management Portal
                </p>
            </div>

            {{-- Dynamic Status Message when verification link resent --}}
            @if (session('status') == 'verification-link-sent')
                <div role="alert" class="mb-5 p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl text-xs text-emerald-800 dark:text-emerald-300 flex items-start gap-3 shadow-xs animate-fade-in">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div>
                        <p class="font-bold text-[13px]">Verification Link Dispatched</p>
                        <p class="mt-0.5 text-xs text-emerald-700 dark:text-emerald-300 leading-relaxed">
                            A fresh verification email and code have been delivered to your inbox.
                        </p>
                    </div>
                </div>
            @endif

            {{-- Account Information Box --}}
            <div class="bg-[#F0FBFB] dark:bg-[#12272b] border border-[#199CA4]/25 rounded-2xl p-4 mb-6">
                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-1">
                    <span class="font-bold uppercase tracking-wider text-[10px] text-[#199CA4]">Account Inactive</span>
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-600 dark:text-amber-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        Pending Verification
                    </span>
                </div>
                <div class="text-sm font-extrabold text-slate-800 dark:text-white break-all">
                    {{ auth()->user()?->email ?? 'Your registered email' }}
                </div>
            </div>

            {{-- Fast Verification via 6-Digit Code (Especially useful if reading email on phone) --}}
            <div class="mb-6 p-5 bg-slate-50/80 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 rounded-2xl">
                <label for="otp" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Enter 6-Digit Verification Code
                </label>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3 leading-relaxed">
                    Viewing the email on your phone? Simply type the 6-digit code sent to your inbox:
                </p>

                <form method="POST" action="{{ route('verification.otp') }}" class="space-y-3">
                    @csrf
                    <div class="flex items-center gap-2">
                        <input type="text"
                               name="otp"
                               id="otp"
                               maxlength="6"
                               placeholder="123456"
                               inputmode="numeric"
                               pattern="[0-9]{6}"
                               required
                               autocomplete="one-time-code"
                               class="flex-1 text-center font-mono text-lg font-bold tracking-widest px-4 py-2.5 rounded-xl border @error('otp') border-rose-400 @else border-slate-200 dark:border-slate-700 @enderror bg-white dark:bg-[#12272b] text-slate-900 dark:text-white focus:ring-2 focus:ring-[#199CA4] focus:border-[#199CA4]">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white text-xs font-extrabold shadow-sm hover:shadow transition cursor-pointer">
                            Verify Code
                        </button>
                    </div>
                    @error('otp')
                        <p class="text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>
                    @enderror
                </form>
            </div>

            <div class="relative flex items-center justify-center mb-6">
                <div class="border-t border-slate-200 dark:border-slate-800 w-full"></div>
                <span class="bg-white dark:bg-[#0e1d20] px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider shrink-0">
                    Or Use Email Link
                </span>
                <div class="border-t border-slate-200 dark:border-slate-800 w-full"></div>
            </div>

            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed mb-6">
                You can also click the "Verify Email Address" button inside the email. If you have not received it yet, request a new link below.
            </p>

            {{-- Action Buttons --}}
            <div class="space-y-3">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-gradient-to-r from-[#199CA4] to-[#13787F] hover:from-[#13787F] hover:to-[#0D5B62] text-white text-xs sm:text-sm font-extrabold shadow-md hover:shadow-lg transition duration-150 focus:outline-none focus:ring-2 focus:ring-[#199CA4] focus:ring-offset-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>Resend Verification Email</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#12272b] hover:bg-slate-50 dark:hover:bg-[#18353b] text-slate-600 dark:text-slate-300 text-xs sm:text-sm font-bold transition duration-150 focus:outline-none focus:ring-2 focus:ring-slate-400 cursor-pointer">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Log Out</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
