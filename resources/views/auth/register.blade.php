<x-guest-layout>
    <div class="relative min-h-screen bg-teal-100 flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">

        <div class="relative z-10 max-w-2xl w-full bg-white/40 backdrop-blur-xl border border-white/60 rounded-[30px] p-10 shadow-[0_20px_50px_rgba(0,0,0,0.1)]">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-900 tracking-wider">
                    {{ $pageTitle ?? 'CREATE ACCOUNT' }}
                </h2>
                <p class="mt-2 text-sm text-gray-700">
                    {{ $pageSubtitle ?? 'Add a new staff user for the dashboard.' }}
                </p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <input type="hidden" name="role" value="{{ $registerRole ?? 'staff' }}" />

                <div class="bg-white/50 border border-white/60 rounded-2xl p-4 flex items-center justify-between">
                    <div>
                        <span class="block text-[11px] font-bold tracking-wider text-gray-500 uppercase">Account Type</span>
                        <span class="text-lg font-bold text-gray-900">{{ ucfirst($registerRole ?? 'staff') }}</span>
                    </div>
                    <div class="bg-teal-700/10 text-teal-800 text-xs font-semibold px-3 py-1.5 rounded-xl">
                        {{ $registerRole === 'admin' ? 'Admin account' : 'Staff account' }}
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-900 mb-1">Full Name</label>
                        <div class="relative flex items-center">
                            <input id="name" name="name" type="text" required value="{{ old('name') }}" autofocus autocomplete="name"
                                class="w-full bg-gray-50/60 border border-gray-300 text-gray-900 rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-teal-700 focus:ring-1 focus:ring-teal-700 transition duration-150" />
                            <span class="absolute right-3 text-gray-500 pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                        </div>
                        @error('name') <span class="text-red-600 text-sm mt-2 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-900 mb-1">Email</label>
                        <div class="relative flex items-center">
                            <input id="email" name="email" type="email" required value="{{ old('email') }}" autocomplete="username"
                                class="w-full bg-gray-50/60 border border-gray-300 text-gray-900 rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-teal-700 focus:ring-1 focus:ring-teal-700 transition duration-150" />
                            <span class="absolute right-3 text-gray-500 pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                        </div>
                        @error('email') <span class="text-red-600 text-sm mt-2 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-900 mb-1">Password</label>
                        <div class="relative flex items-center">
                            <input id="password" name="password" type="password" required autocomplete="new-password"
                                class="w-full bg-gray-50/60 border border-gray-300 text-gray-900 rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-teal-700 focus:ring-1 focus:ring-teal-700 transition duration-150 no-native-toggle" />
                            <button type="button" onclick="togglePassword('password', 'eye-icon-1')" class="absolute right-3 text-gray-500 hover:text-gray-700 focus:outline-none">
                                <svg id="eye-icon-1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        @error('password') <span class="text-red-600 text-sm mt-2 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-900 mb-1">Confirm Password</label>
                        <div class="relative flex items-center">
                            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                                class="w-full bg-gray-50/60 border border-gray-300 text-gray-900 rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-teal-700 focus:ring-1 focus:ring-teal-700 transition duration-150 no-native-toggle" />
                            <button type="button" onclick="togglePassword('password_confirmation', 'eye-icon-2')" class="absolute right-3 text-gray-500 hover:text-gray-700 focus:outline-none">
                                <svg id="eye-icon-2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-teal-600 to-teal-800 hover:from-teal-700 hover:to-teal-900 text-white font-semibold rounded-xl shadow-lg active:scale-95 transition duration-150 tracking-wide">
                        Create Account
                    </button>
                </div>

                @if (Route::has('login'))
                    <div class="text-center text-sm pt-2 text-gray-700">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-semibold text-teal-800 hover:text-teal-900 underline transition duration-150">
                            Login
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