<section class="bg-white rounded-3xl border border-rose-100 shadow-sm overflow-hidden">
    <div class="border-b border-rose-100 px-6 py-5 sm:px-8 bg-rose-50/40">
        <h2 class="text-base sm:text-lg font-extrabold text-rose-900">
            Account Management & Danger Zone
        </h2>
        <p class="mt-0.5 text-xs text-rose-700/80">
            Permanently remove this staff account and revoke all administrative credentials.
        </p>
    </div>

    <div class="px-6 py-6 sm:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-xs text-gray-600 max-w-xl">
                    Once your account is deleted, all resources and active sessions will be terminated. Please ensure this action is intentional.
                </p>
                @if($errors->userDeletion->isNotEmpty())
                    <p class="mt-2 text-xs font-bold text-rose-600">{{ $errors->userDeletion->first() }}</p>
                @endif
            </div>

            <button type="button"
                x-data=""
                x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                class="px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs sm:text-sm font-bold rounded-xl transition shadow-2xs self-start sm:self-auto shrink-0">
                Delete Account
            </button>
        </div>

        <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
            <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                @csrf
                @method('delete')

                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-lg">
                        ⚠️
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold text-gray-900">
                            Confirm Account Deletion
                        </h2>
                        <p class="text-xs text-gray-500">
                            This action is permanent and cannot be undone.
                        </p>
                    </div>
                </div>

                <p class="text-xs text-gray-600 mb-4">
                    Please enter your password to confirm you would like to permanently delete your account.
                </p>

                <div class="mb-4">
                    <label for="delete_password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Password Confirmation</label>
                    <input id="delete_password"
                        name="password"
                        type="password"
                        placeholder="Enter your current password"
                        class="w-full px-4 py-2.5 text-xs sm:text-sm bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition"
                    />
                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                </div>

                <div class="flex justify-end gap-2.5 pt-3 border-t border-gray-100">
                    <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-xl transition">
                        Cancel
                    </button>

                    <button type="submit" class="px-5 py-2 text-xs sm:text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-xs transition">
                        Delete Account
                    </button>
                </div>
            </form>
        </x-modal>
    </div>
</section>