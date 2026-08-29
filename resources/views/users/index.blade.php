<x-app-layout>
    <div class="w-full py-6 sm:py-8 px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">User Management</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Manage administrator and staff accounts with system access.</p>
            </div>
            <div>
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#199CA4] hover:bg-[#13787F] px-4 py-2.5 text-xs sm:text-sm font-bold text-white transition-all shadow-xs">
                    <span>+</span> Create User
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-[#12141C] rounded-2xl sm:rounded-3xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-[#171923] border-b border-slate-200/80 dark:border-white/[0.06] text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3.5">User</th>
                            <th class="px-6 py-3.5">Email</th>
                            <th class="px-6 py-3.5">Current Role</th>
                            <th class="px-6 py-3.5">Created Date</th>
                            <th class="px-6 py-3.5 text-right">Assign Role</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06] text-xs sm:text-sm">
                        @foreach($users as $u)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-[#181A24] transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        @if($u->avatar_url)
                                            <img src="{{ $u->avatar_url }}" alt="{{ $u->name }}" class="w-10 h-10 rounded-2xl object-cover border border-slate-200 dark:border-white/[0.08] shadow-2xs flex-shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-200 flex items-center justify-center font-extrabold text-xs border border-slate-200 dark:border-white/[0.08] shadow-2xs flex-shrink-0">
                                                {{ $u->initials }}
                                            </div>
                                        @endif
                                        <div>
                                            <span class="block text-slate-900 dark:text-white font-extrabold text-sm leading-tight">{{ $u->name }}</span>
                                            <span class="block text-[11px] text-slate-400 dark:text-slate-500 font-normal mt-0.5">ID: #{{ $u->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300 font-semibold">{{ $u->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $u->role === 'admin' ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60' : 'bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800/60' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $u->role === 'admin' ? 'bg-indigo-500' : 'bg-sky-500' }}"></span>
                                        {{ ucfirst($u->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-500 dark:text-slate-400 font-medium">{{ $u->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    @if($u->id === Auth::id())
                                        <span class="text-xs text-slate-400 font-medium italic">Current Account</span>
                                    @else
                                        <form action="{{ route('users.update-role', $u) }}" method="POST" class="inline-flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role" onchange="this.form.submit()" class="rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-1.5 bg-white dark:bg-[#12272b] text-xs font-bold text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] cursor-pointer">
                                                <option value="staff" {{ $u->role === 'staff' ? 'selected' : '' }}>Staff</option>
                                                <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                            </select>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>