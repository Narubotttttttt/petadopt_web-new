<x-app-layout>
    <div class="w-full py-8 px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">User Management</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Manage administrator and staff accounts with system access.</p>
            </div>
            <div>
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-[#199CA4] to-[#14838B] px-4 py-2.5 text-xs sm:text-sm font-bold text-white hover:from-[#146970] hover:to-[#12585e] transition-all shadow-md shadow-[#199CA4]/25">
                    <span>+</span> Create User
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-[#0e1d20] rounded-2xl shadow-card border border-slate-200/80 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-[#091518] border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3.5">User</th>
                            <th class="px-6 py-3.5">Email</th>
                            <th class="px-6 py-3.5">Role</th>
                            <th class="px-6 py-3.5">Created Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 text-xs sm:text-sm">
                        @foreach($users as $user)
                            <tr class="table-row-hover">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        @if($user->avatar_url)
                                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-2xl object-cover border border-slate-200 dark:border-slate-700 shadow-2xs flex-shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#F0FBFB] to-[#D6F4F6] dark:from-[#133036] dark:to-[#17454d] text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-extrabold text-xs border border-[#199CA4]/20 dark:border-[#41C1CB]/30 shadow-2xs flex-shrink-0">
                                                {{ $user->initials }}
                                            </div>
                                        @endif
                                        <div>
                                            <span class="block text-slate-800 dark:text-white font-extrabold text-sm leading-tight">{{ $user->name }}</span>
                                            <span class="block text-[11px] text-slate-400 dark:text-slate-500 font-normal mt-0.5">ID: #{{ $user->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300 font-semibold">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $user->role === 'admin' ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800' : 'bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $user->role === 'admin' ? 'bg-indigo-500' : 'bg-sky-500' }}"></span>
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-500 dark:text-slate-400 font-medium">{{ $user->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>