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
            <div class="text-center mb-6">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">Forgot Password</h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1.5 font-medium leading-relaxed">
                    Enter your account email and we will send a password reset link to regain access.
                </p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            {{-- UXHub: Accessible Error Handling - Error Summary Alert --}}
            @if ($errors->any())
                <div id="error-summary" role="alert" aria-live="assertive" class="mb-5 p-4 bg-rose-50/90 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-2xl text-xs text-rose-800 dark:text-rose-300 flex items-start gap-3 shadow-xs">
                    <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="font-bold text-[13px]">Please check the highlighted field:</p>
                        <ul class="list-disc list-inside mt-1 space-y-0.5 text-xs text-rose-700 dark:text-rose-300">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" novalidate class="space-y-5">
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
                    <p id="email-help" class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Enter your registered staff email address to receive recovery instructions.</p>
                    @error('email')
                        <p id="email-error" role="alert" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400 font-semibold flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                {{-- UXHub: Writing Button Labels - Active Verb --}}
                <div class="flex items-center gap-3 pt-2">
                    <a href="{{ route('login') }}"
                        class="w-1/3 py-3.5 px-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-[#199CA4] hover:bg-[#F0FBFB] dark:hover:bg-[#12272b] text-slate-600 dark:text-slate-300 hover:text-[#199CA4] dark:hover:text-[#41C1CB] font-extrabold text-xs sm:text-sm text-center transition flex items-center justify-center">
                        Back
                    </a>
                    <button type="submit" class="flex-1 py-3.5 px-4 bg-gradient-to-r from-[#199CA4] to-[#14838B] hover:from-[#146970] hover:to-[#12585e] text-white font-extrabold rounded-xl shadow-lg shadow-[#199CA4]/30 active:scale-[0.98] transition duration-150 tracking-wide text-xs sm:text-sm cursor-pointer">
                        Send Reset Link
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // UXHub Field Guide: Accessible Error Handling - Automatically move focus to first error field
        document.addEventListener('DOMContentLoaded', function() {
            const firstInvalid = document.querySelector('[aria-invalid="true"]');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        });
    </script>
</x-guest-layout>
