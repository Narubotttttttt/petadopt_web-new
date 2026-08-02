<section class="mx-auto max-w-3xl">
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm ring-1 ring-black/5">
        <div class="border-b border-slate-200 px-6 py-5 sm:px-8">
            <h2 class="text-xl font-semibold text-slate-900">
                {{ __('Update Password') }}
            </h2>

            <p class="mt-1 text-sm text-slate-600">
                {{ __('Ensure your account is using a long, random password to stay secure.') }}
            </p>
        </div>

        <div class="px-6 py-6 sm:px-8">
            <form method="post" action="{{ route('password.update') }}" class="space-y-6">
                @csrf
                @method('put')

                <div>
                    <label for="update_password_current_password" class="block text-sm font-medium text-slate-700">{{ __('Current Password') }}</label>
                    <input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full rounded-2xl border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-teal-500 focus:ring-teal-500" autocomplete="current-password" />
                    @error('current_password', 'updatePassword')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="update_password_password" class="block text-sm font-medium text-slate-700">{{ __('New Password') }}</label>
                    <input id="update_password_password" name="password" type="password" class="mt-1 block w-full rounded-2xl border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-teal-500 focus:ring-teal-500" autocomplete="new-password" />
                    @error('password', 'updatePassword')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="update_password_password_confirmation" class="block text-sm font-medium text-slate-700">{{ __('Confirm Password') }}</label>
                    <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full rounded-2xl border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-teal-500 focus:ring-teal-500" autocomplete="new-password" />
                    @error('password_confirmation', 'updatePassword')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-4 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>

                    @if (session('status') === 'password-updated')
                        <p
                            x-data="{ show: true }"
                            x-show="show"
                            x-transition
                            x-init="setTimeout(() => show = false, 2000)"
                            class="text-sm font-medium text-emerald-600"
                        >{{ __('Saved.') }}</p>
                    @endif
                </div>
            </form>
        </div>
    </div>
</section>