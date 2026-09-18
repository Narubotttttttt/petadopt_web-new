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
                    {{ $pageTitle ?? 'Create Staff Account' }}
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium">
                    Step 1 of 2: Verify your identity and email address.
                </p>
            </div>

            {{-- Step Indicator Bar --}}
            <div class="mb-6" aria-label="Registration Steps">
                <div class="grid grid-cols-2 gap-3 p-1.5 bg-slate-100 dark:bg-slate-900/60 rounded-2xl border border-slate-200/80 dark:border-slate-800">
                    <div class="flex items-center justify-center gap-2.5 py-2.5 px-3 rounded-xl bg-white dark:bg-[#12272b] text-slate-800 dark:text-white shadow-xs border border-[#199CA4]/30">
                        <span class="w-6 h-6 rounded-full bg-[#199CA4] text-white text-xs font-extrabold flex items-center justify-center">1</span>
                        <div class="text-left leading-tight">
                            <span class="block text-[10px] uppercase font-bold tracking-wider text-[#199CA4]">Step 1 (Active)</span>
                            <span class="text-xs font-extrabold">Identity and Email</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-center gap-2.5 py-2.5 px-3 rounded-xl bg-transparent text-slate-400 dark:text-slate-500 opacity-70">
                        <span class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs font-extrabold flex items-center justify-center">2</span>
                        <div class="text-left leading-tight">
                            <span class="block text-[10px] uppercase font-bold tracking-wider text-slate-400 dark:text-slate-500">Next Page</span>
                            <span class="text-xs font-extrabold">Set Password</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Role Banner --}}
            <div class="bg-[#F0FBFB] dark:bg-[#12272b] border border-[#199CA4]/20 dark:border-[#199CA4]/30 rounded-2xl p-4 flex items-center justify-between shadow-2xs mb-6">
                <div>
                    <span class="block text-[10px] font-bold tracking-wider text-[#199CA4] dark:text-[#41C1CB] uppercase">Account Access Level</span>
                    <span class="text-base font-extrabold text-slate-800 dark:text-white">{{ ucfirst($registerRole ?? 'staff') }}</span>
                </div>
                <div class="bg-[#199CA4] text-white text-xs font-extrabold px-3.5 py-1 rounded-xl shadow-xs">
                    {{ $registerRole === 'admin' ? 'Administrator' : 'Staff Member' }}
                </div>
            </div>

            {{-- Server Errors Alert --}}
            @if ($errors->any())
                <div id="error-summary" role="alert" class="mb-6 p-4 bg-rose-50/90 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-2xl text-xs text-rose-800 dark:text-rose-300 flex items-start gap-3 shadow-xs">
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

            {{-- Dynamic AJAX Status Alert --}}
            <div id="ajax-alert" class="hidden mb-6 p-4 rounded-2xl text-xs flex items-start gap-3 shadow-xs animate-fade-in" role="alert">
                <div id="ajax-alert-icon" class="shrink-0 mt-0.5"></div>
                <div>
                    <p id="ajax-alert-title" class="font-bold text-[13px]"></p>
                    <p id="ajax-alert-msg" class="mt-0.5 text-xs leading-relaxed"></p>
                </div>
            </div>

            {{-- Step 1 Form --}}
            <form id="step-1-form" method="POST" action="{{ route('register.step-1', [], false) }}" class="space-y-5">
                @csrf

                {{-- Full Name --}}
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Full Name <span class="text-rose-500" title="Required">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <input id="name" name="name" type="text" required value="{{ old('name', $prefill['name'] ?? '') }}" autofocus autocomplete="name"
                            placeholder="e.g. Juan Dela Cruz"
                            class="w-full bg-slate-50/80 dark:bg-[#12272b] border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/15 transition duration-150 text-xs sm:text-sm font-semibold" />
                        <span class="absolute right-3.5 text-slate-400 dark:text-slate-500 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Official full name for authorization and portal records.</p>
                </div>

                {{-- Email Address with Send Code button --}}
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Email Address <span class="text-rose-500" title="Required">*</span>
                    </label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="relative flex items-center flex-1">
                            <input id="email" name="email" type="email" required value="{{ old('email', $prefill['email'] ?? '') }}" autocomplete="username"
                                placeholder="e.g. staff@caws.org"
                                class="w-full bg-slate-50/80 dark:bg-[#12272b] border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/15 transition duration-150 text-xs sm:text-sm font-semibold" />
                            <span class="absolute right-3.5 text-slate-400 dark:text-slate-500 pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                        </div>
                        <button type="button" id="btn-send-code" onclick="sendVerificationCode()"
                            class="inline-flex items-center justify-center gap-2 py-3 px-5 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white text-xs font-extrabold shadow-sm transition active:scale-95 disabled:opacity-60 disabled:pointer-events-none cursor-pointer whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            <span id="btn-send-code-text">Send Code</span>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">We will send a 6-digit confirmation code to this address.</p>
                </div>

                {{-- 6-Digit Code Input & Verify Button --}}
                <div class="p-5 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-2xl space-y-3">
                    <div class="flex items-center justify-between">
                        <label for="code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            6-Digit Verification Code <span class="text-rose-500" title="Required">*</span>
                        </label>
                        <span id="verification-status-badge" class="inline-flex items-center gap-1.5 text-[11px] font-bold text-slate-400 dark:text-slate-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                            Awaiting Code
                        </span>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <input id="code" name="code" type="text" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" placeholder="000000" required
                            class="flex-1 text-center font-mono text-lg font-bold tracking-widest bg-white dark:bg-[#12272b] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/15 transition text-sm" />
                        <button type="button" id="btn-verify-code" onclick="verifyRegistrationCode()"
                            class="py-2.5 px-6 rounded-xl bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 text-white text-xs font-extrabold shadow-sm transition active:scale-95 disabled:opacity-50 disabled:pointer-events-none cursor-pointer whitespace-nowrap">
                            <span>Verify Code</span>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">Enter the 6-digit code received in your inbox on your phone or PC.</p>
                </div>

                {{-- Action Navigation --}}
                <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800/80">
                    <a href="{{ Auth::check() ? route('users.index') : route('login') }}"
                        class="py-3 px-5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-[#199CA4] hover:bg-[#F0FBFB] dark:hover:bg-[#12272b] text-slate-600 dark:text-slate-300 font-extrabold text-xs transition">
                        Cancel
                    </a>
                    <button type="submit" id="btn-continue"
                        class="inline-flex items-center gap-2 py-3 px-7 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white font-extrabold text-xs sm:text-sm shadow-md transition active:scale-95 cursor-pointer">
                        <span>Continue to Set Password</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>

                @if (Route::has('login'))
                    <div class="text-center text-xs pt-4 text-slate-500 dark:text-slate-400 font-medium">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-bold text-[#199CA4] dark:text-[#41C1CB] hover:underline">
                            Sign In
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <script>
        let verifiedEmail = null;
        let verifiedCode = null;
        let cooldownSeconds = 0;
        let cooldownInterval = null;

        function resetVerificationState() {
            verifiedEmail = null;
            verifiedCode = null;
            const badge = document.getElementById('verification-status-badge');
            if (badge) {
                badge.className = 'inline-flex items-center gap-1.5 text-[11px] font-bold text-slate-400 dark:text-slate-500';
                badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Awaiting Verification';
            }
            const btn = document.getElementById('btn-verify-code');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<span>Verify Code</span>';
            }
        }

        document.getElementById('email').addEventListener('input', function() {
            if (verifiedEmail && this.value.trim().toLowerCase() !== verifiedEmail) {
                resetVerificationState();
            }
        });

        document.getElementById('code').addEventListener('input', function() {
            if (verifiedCode && this.value.trim() !== verifiedCode) {
                resetVerificationState();
            }
        });

        function showAlert(type, title, msg) {
            const alertEl = document.getElementById('ajax-alert');
            const iconEl = document.getElementById('ajax-alert-icon');
            const titleEl = document.getElementById('ajax-alert-title');
            const msgEl = document.getElementById('ajax-alert-msg');

            alertEl.classList.remove('hidden', 'bg-rose-50/90', 'dark:bg-rose-950/40', 'border-rose-200', 'text-rose-800', 'dark:text-rose-300',
                'bg-emerald-50', 'dark:bg-emerald-950/40', 'border-emerald-200', 'text-emerald-800', 'dark:text-emerald-300');

            if (type === 'success') {
                alertEl.classList.add('bg-emerald-50', 'dark:bg-emerald-950/40', 'border', 'border-emerald-200', 'dark:border-emerald-800/60', 'text-emerald-800', 'dark:text-emerald-300');
                iconEl.innerHTML = '<svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
            } else {
                alertEl.classList.add('bg-rose-50/90', 'dark:bg-rose-950/40', 'border', 'border-rose-200', 'dark:border-rose-800/60', 'text-rose-800', 'dark:text-rose-300');
                iconEl.innerHTML = '<svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
            }

            titleEl.textContent = title;
            msgEl.textContent = msg;
            alertEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        async function sendVerificationCode() {
            const emailInput = document.getElementById('email');
            const nameInput = document.getElementById('name');
            const email = emailInput.value.trim();
            const name = nameInput.value.trim();

            if (!email) {
                showAlert('error', 'Email Required', 'Please enter your email address before requesting a code.');
                emailInput.focus();
                return;
            }

            resetVerificationState();

            const btn = document.getElementById('btn-send-code');
            const btnText = document.getElementById('btn-send-code-text');
            btn.disabled = true;
            btnText.textContent = 'Sending...';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || document.querySelector('input[name="_token"]')?.value
                    || '{{ csrf_token() }}';

                const response = await fetch("{{ route('register.send-code', [], false) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email: email, name: name })
                });

                let data = null;
                try {
                    data = await response.json();
                } catch (jsonErr) {
                    // Response body was not JSON
                }

                if (response.ok && data?.success) {
                    showAlert('success', 'Verification Code Dispatched', data.message || 'A 6-digit code has been sent to your email.');
                    document.getElementById('code').focus();
                    startCooldown(60);
                } else {
                    btn.disabled = false;
                    btnText.textContent = 'Send Code';

                    if (response.status === 419) {
                        showAlert('error', 'Session Expired', 'Your session expired. Please refresh the page and try again.');
                    } else if (response.status === 429) {
                        showAlert('error', 'Please Wait', 'Too many requests. Please wait a minute before requesting another code.');
                    } else {
                        const errMsg = data?.errors?.email?.[0] || data?.message || ('Unable to send verification code (Status ' + response.status + '). Please check your email.');
                        showAlert('error', 'Delivery Failed', errMsg);
                    }
                }
            } catch (err) {
                console.error('Send verification code error:', err);
                btn.disabled = false;
                btnText.textContent = 'Send Code';
                showAlert('error', 'Connection Error', 'Could not communicate with the server. Please check your network connection or reload the page.');
            }
        }

        function startCooldown(seconds) {
            cooldownSeconds = seconds;
            const btn = document.getElementById('btn-send-code');
            const btnText = document.getElementById('btn-send-code-text');
            btn.disabled = true;

            clearInterval(cooldownInterval);
            cooldownInterval = setInterval(() => {
                cooldownSeconds--;
                if (cooldownSeconds <= 0) {
                    clearInterval(cooldownInterval);
                    btn.disabled = false;
                    btnText.textContent = 'Resend Code';
                } else {
                    btnText.textContent = `Resend in ${cooldownSeconds}s`;
                }
            }, 1000);
        }

        async function verifyRegistrationCode() {
            const emailInput = document.getElementById('email');
            const codeInput = document.getElementById('code');
            const email = emailInput.value.trim().toLowerCase();
            const code = codeInput.value.trim();

            if (!email) {
                showAlert('error', 'Email Required', 'Please enter your email address first.');
                emailInput.focus();
                return false;
            }

            if (!code || code.length !== 6) {
                showAlert('error', 'Invalid Code Length', 'Please enter the complete 6-digit code sent to your email.');
                codeInput.focus();
                return false;
            }

            const btn = document.getElementById('btn-verify-code');
            btn.disabled = true;
            btn.innerHTML = '<span>Verifying...</span>';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || document.querySelector('input[name="_token"]')?.value
                    || '{{ csrf_token() }}';

                const response = await fetch("{{ route('register.verify-code', [], false) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email: email, code: code })
                });

                let data = null;
                try {
                    data = await response.json();
                } catch (jsonErr) {}

                if (response.ok && data?.success) {
                    verifiedEmail = email;
                    verifiedCode = code;

                    // Update Badge
                    const badge = document.getElementById('verification-status-badge');
                    if (badge) {
                        badge.className = 'inline-flex items-center gap-1.5 text-[11px] font-extrabold text-emerald-600 dark:text-emerald-400';
                        badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Email Verified';
                    }

                    btn.disabled = true;
                    btn.innerHTML = '<span>Verified</span>';

                    showAlert('success', 'Email Verified', 'Your email address has been verified. Click "Continue to Set Password" below to proceed.');
                    return true;
                } else {
                    resetVerificationState();
                    const errMsg = data?.errors?.code?.[0] || data?.message || ('Verification failed (Status ' + response.status + '). Please check your code.');
                    showAlert('error', 'Verification Failed', errMsg);
                    codeInput.focus();
                    return false;
                }
            } catch (err) {
                console.error('Verify code error:', err);
                btn.disabled = false;
                btn.innerHTML = '<span>Verify Code</span>';
                showAlert('error', 'Connection Error', 'Could not verify code. Please check your network connection.');
                return false;
            }
        }

        document.getElementById('step-1-form').addEventListener('submit', async function(e) {
            const email = document.getElementById('email').value.trim().toLowerCase();
            const code = document.getElementById('code').value.trim();

            const isVerified = (verifiedEmail === email && verifiedCode === code && code.length === 6);

            if (isVerified) {
                return true;
            }

            e.preventDefault();

            if (!email) {
                showAlert('error', 'Email Required', 'Please enter your email address.');
                document.getElementById('email').focus();
                return;
            }

            if (!code || code.length !== 6) {
                showAlert('error', 'Verification Code Required', 'Please enter the 6-digit verification code sent to your email.');
                document.getElementById('code').focus();
                return;
            }

            const continueBtn = document.getElementById('btn-continue');
            const originalContent = continueBtn.innerHTML;
            continueBtn.disabled = true;
            continueBtn.innerHTML = '<span>Verifying Code...</span>';

            const success = await verifyRegistrationCode();

            if (success) {
                this.submit();
            } else {
                continueBtn.disabled = false;
                continueBtn.innerHTML = originalContent;
            }
        });
    </script>
</x-guest-layout>