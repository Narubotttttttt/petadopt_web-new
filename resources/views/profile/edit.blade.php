<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-900">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm ring-1 ring-black/5">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm ring-1 ring-black/5">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>
</x-app-layout>