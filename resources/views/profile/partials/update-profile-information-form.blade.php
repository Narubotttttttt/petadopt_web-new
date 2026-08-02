<section class="mx-auto max-w-3xl">
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm ring-1 ring-black/5">
        <div class="border-b border-slate-200 px-6 py-5 sm:px-8">
            <h2 class="text-xl font-semibold text-slate-900">
                {{ __('Profile Information') }}
            </h2>

            <p class="mt-1 text-sm text-slate-600">
                {{ __("Update your account's profile information and email address.") }}
            </p>
        </div>

        <div class="px-6 py-6 sm:px-8">
            <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                @csrf
            </form>

            <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
                @csrf
                @method('patch')

                <div class="space-y-6">
                    <div>
                        <x-input-label for="name" :value="__('Name')" class="text-sm font-medium text-slate-700" />
                        <x-text-input
                            id="name"
                            name="name"
                            type="text"
                            class="mt-1 block w-full rounded-2xl border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            :value="old('name', $user->name)"
                            required
                            autofocus
                            autocomplete="name"
                        />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email')" class="text-sm font-medium text-slate-700" />
                        <x-text-input
                            id="email"
                            name="email"
                            type="email"
                            class="mt-1 block w-full rounded-2xl border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            :value="old('email', $user->email)"
                            required
                            autocomplete="username"
                        />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="mt-4 rounded-2xl bg-amber-50 p-4 text-sm text-slate-800">
                                <p>
                                    {{ __('Your email address is unverified.') }}

                                    <button
                                        form="send-verification"
                                        class="ml-1 font-medium text-teal-700 underline decoration-teal-500/60 underline-offset-2 hover:text-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
                                    >
                                        {{ __('Click here to re-send the verification email.') }}
                                    </button>
                                </p>

                                @if (session('status') === 'verification-link-sent')
                                    <p class="mt-2 text-sm font-medium text-emerald-600">
                                        {{ __('A new verification link has been sent to your email address.') }}
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col gap-4 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>

                    @if (session('status') === 'profile-updated')
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
