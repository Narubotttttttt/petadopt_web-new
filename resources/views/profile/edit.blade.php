<x-app-layout>
    <div class="max-w-5xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">
        
        {{-- Page Title --}}
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#333634] tracking-tight">Account Settings</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Manage your administrative profile, credentials, and account preferences.</p>
        </div>

        {{-- Account Header Overview Banner --}}
        <div class="bg-gradient-to-r from-white via-white to-teal-50/50 rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div class="flex items-center gap-4">
                @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-white shadow-md flex-shrink-0">
                @else
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#EAF5F6] to-[#d3eef1] text-[#199CA4] flex items-center justify-center font-extrabold text-xl border border-[#199CA4]/20 shadow-md flex-shrink-0">
                        {{ $user->initials }}
                    </div>
                @endif
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-lg sm:text-xl font-extrabold text-gray-900 leading-tight">{{ $user->name }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-teal-100 text-teal-800 border border-teal-200' }}">
                            {{ ucfirst($user->role) }} Account
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $user->email }}</p>
                    <p class="text-[11px] text-gray-400 mt-1">Member active since {{ $user->created_at->format('M Y') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
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