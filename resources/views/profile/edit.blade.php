<x-app-layout>
    <div class="w-full py-6 px-4 sm:px-6 lg:px-8 space-y-6">
        
        {{-- Page Title --}}
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#333634] dark:text-white tracking-tight">Account Settings</h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-slate-400 mt-1">Manage your administrative profile, credentials, and account preferences.</p>
        </div>

        {{-- Account Header Overview Banner --}}
        <div class="bg-gradient-to-r from-white via-white to-teal-50/50 dark:from-[#0e1d20] dark:via-[#0e1d20] dark:to-[#12272b] rounded-3xl p-6 border border-gray-100 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div class="flex items-center gap-4">
                @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-white dark:border-slate-700 shadow-md flex-shrink-0">
                @else
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#EAF5F6] to-[#d3eef1] dark:from-[#133036] dark:to-[#17454d] text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-extrabold text-xl border border-[#199CA4]/20 dark:border-[#41C1CB]/30 shadow-md flex-shrink-0">
                        {{ $user->initials }}
                    </div>
                @endif
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-lg sm:text-xl font-extrabold text-gray-900 dark:text-white leading-tight">{{ $user->name }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $user->role === 'admin' ? 'bg-purple-100 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-800' : 'bg-teal-100 dark:bg-teal-950/60 text-teal-800 dark:text-teal-300 border border-teal-200 dark:border-teal-800' }}">
                            {{ ucfirst($user->role) }} Account
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">{{ $user->email }}</p>
                    <p class="text-[11px] text-gray-400 dark:text-slate-500 mt-1">Member active since {{ $user->created_at->format('M Y') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>System Active</span>
                </span>
            </div>
        </div>

        {{-- Form Cards --}}
        <div class="space-y-6">
            {{-- 1. Profile Information & Photo --}}
            @include('profile.partials.update-profile-information-form')

            {{-- 2. Password Security --}}
            @include('profile.partials.update-password-form')

            {{-- 3. Danger Zone --}}
            @include('profile.partials.delete-user-form')
        </div>

    </div>
</x-app-layout>