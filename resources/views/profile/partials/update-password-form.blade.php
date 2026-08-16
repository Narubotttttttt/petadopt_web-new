<section class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden" x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
    <div class="border-b border-gray-100 px-6 py-5 sm:px-8 bg-gray-50/40">
        <h2 class="text-base sm:text-lg font-extrabold text-gray-900">
            Update Password
        </h2>
        <p class="mt-0.5 text-xs text-gray-500">
            Ensure your administrator account uses a strong, secure password.
        </p>
    </div>

    <div class="px-6 py-6 sm:px-8">
        <form method="post" action="{{ route('password.update') }}" class="space-y-5">
            @csrf
            @method('put')

            {{-- Current Password --}}
            <div>
                <label for="update_password_current_password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Current Password</label>
                <div class="relative">
                    <input id="update_password_current_password" 
                           name="current_password" 
                           :type="showCurrent ? 'text' : 'password'" 
                           class="w-full pl-4 pr-10 py-2.5 text-xs sm:text-sm bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition" 
                           autocomplete="current-password" />
                    <button type="button" @click="showCurrent = !showCurrent" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 text-xs font-bold">
                        <span x-text="showCurrent ? 'Hide' : 'Show'"></span>
                    </button>
                </div>
                @error('current_password', 'updatePassword')
                    <p class="mt-2 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            {{-- New Password & Confirmation --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="update_password_password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">New Password</label>
                    <div class="relative">
                        <input id="update_password_password" 
                               name="password" 
                               :type="showNew ? 'text' : 'password'" 
                               class="w-full pl-4 pr-10 py-2.5 text-xs sm:text-sm bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition" 
                               autocomplete="new-password" />
                        <button type="button" @click="showNew = !showNew" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 text-xs font-bold">
                            <span x-text="showNew ? 'Hide' : 'Show'"></span>
                        </button>
                    </div>
                    @error('password', 'updatePassword')
                        <p class="mt-2 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="update_password_password_confirmation" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Confirm Password</label>
                    <div class="relative">
                        <input id="update_password_password_confirmation" 
                               name="password_confirmation" 
                               :type="showConfirm ? 'text' : 'password'" 
                               class="w-full pl-4 pr-10 py-2.5 text-xs sm:text-sm bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition" 
                               autocomplete="new-password" />
                        <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 text-xs font-bold">
                            <span x-text="showConfirm ? 'Hide' : 'Show'"></span>
                        </button>
                    </div>
                    @error('password_confirmation', 'updatePassword')
                        <p class="mt-2 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Submit Row --}}
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <button type="submit" class="px-5 py-2.5 bg-[#199CA4] hover:bg-[#13787F] text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-xs">
                    Update Password
                </button>

                @if (session('status') === 'password-updated')
                    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="flex items-center gap-1.5 text-xs font-bold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-200">
                        <span>✓</span>
                        <span>Password changed successfully!</span>
                    </div>
                @endif
            </div>
        </form>
    </div>
</section>