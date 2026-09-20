<x-app-layout>
    <div class="w-full py-6 sm:py-8 px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Staff Management</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Manage shelter personnel, official staff codes, job titles, and active staff statuses.</p>
            </div>
            <div class="w-full sm:w-auto">
                <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#199CA4] hover:bg-[#13787F] px-4 py-2.5 text-xs sm:text-sm font-bold text-white transition-all shadow-xs">
                    <span>+</span> Add Staff Member
                </a>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div role="alert" class="px-4 py-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl flex items-center justify-between text-xs text-emerald-800 dark:text-emerald-300 shadow-xs animate-fade-in">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/60 flex items-center justify-center text-emerald-600 dark:text-emerald-300 shrink-0 font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="font-semibold">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.closest('[role=alert]').remove()" class="text-emerald-500 hover:text-emerald-700 text-lg leading-none cursor-pointer p-1" aria-label="Close notification">&times;</button>
            </div>
        @endif

        @if(session('info'))
            <div role="alert" class="px-4 py-3.5 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800/60 rounded-2xl flex items-center justify-between text-xs text-sky-800 dark:text-sky-300 shadow-xs animate-fade-in">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-lg bg-sky-100 dark:bg-sky-900/60 flex items-center justify-center text-sky-600 dark:text-sky-300 shrink-0 font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="font-semibold">{{ session('info') }}</span>
                </div>
                <button type="button" onclick="this.closest('[role=alert]').remove()" class="text-sky-500 hover:text-sky-700 text-lg leading-none cursor-pointer p-1" aria-label="Close notification">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div role="alert" class="px-4 py-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-2xl flex items-center justify-between text-xs text-rose-800 dark:text-rose-300 shadow-xs animate-fade-in">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-lg bg-rose-100 dark:bg-rose-900/60 flex items-center justify-center text-rose-600 dark:text-rose-300 shrink-0 font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="font-semibold">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.closest('[role=alert]').remove()" class="text-rose-500 hover:text-rose-700 text-lg leading-none cursor-pointer p-1" aria-label="Close notification">&times;</button>
            </div>
        @endif

        <div class="bg-white dark:bg-[#12141C] rounded-2xl sm:rounded-3xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] overflow-hidden">
            <div class="p-4 sm:p-6 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between bg-slate-50/50 dark:bg-[#171923]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20 dark:border-[#41C1CB]/30 flex items-center justify-center font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white">Active Personnel Directory</h2>
                </div>
                <span class="text-xs font-bold text-slate-400 dark:text-slate-500">
                    {{ $users->count() }} Members
                </span>
            </div>

            {{-- Mobile Card Feed (<md) --}}
            <div class="block md:hidden divide-y divide-slate-100 dark:divide-white/[0.06]">
                @foreach($users as $u)
                    @php
                        $profile = $u->staffProfile;
                        $staffCode = $profile?->staff_code ?? (($u->role === 'admin' ? 'ADM-' : 'STF-') . str_pad((string) $u->id, 4, '0', STR_PAD_LEFT));
                        $positionTitle = $profile?->position_title ?? ($u->role === 'admin' ? 'Shelter Director / Head Administrator' : 'CAWS Staff Member');
                        $phone = $profile?->phone ?: 'No phone set';
                        $status = $profile?->status ?? 'active';
                        $isEmailVerified = $u->hasVerifiedEmail();
                    @endphp
                    <div class="p-4 space-y-3.5">
                        {{-- Top row: Avatar + Name + Staff Code + Duty Status --}}
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                @if($u->avatar_url)
                                    <img src="{{ $u->avatar_url }}" alt="{{ $u->name }}" class="w-12 h-12 rounded-2xl object-cover border border-slate-200 dark:border-white/[0.08] shadow-2xs shrink-0">
                                @else
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-200 flex items-center justify-center font-extrabold text-xs border border-slate-200 dark:border-white/[0.08] shadow-2xs shrink-0">
                                        {{ $u->initials }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-extrabold text-slate-900 dark:text-white text-sm truncate">{{ $u->name }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08]">
                                            {{ $staffCode }}
                                        </span>
                                    </div>
                                    <span class="block text-xs text-slate-400 dark:text-slate-500 font-normal truncate mt-0.5">{{ $u->email }}</span>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border shrink-0 {{ $status === 'active' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $status === 'active' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                {{ $status === 'active' ? 'Active' : 'Deactivated' }}
                            </span>
                        </div>

                        {{-- Middle details grid --}}
                        <div class="bg-slate-50/80 dark:bg-[#171923] p-3 rounded-xl border border-slate-100 dark:border-white/[0.04] space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400 dark:text-slate-500 font-medium">Designation</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200 text-right truncate ml-2">{{ $positionTitle }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400 dark:text-slate-500 font-medium">System Access</span>
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-extrabold border {{ $u->role === 'admin' ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60' : 'bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800/60' }}">
                                    <span class="w-1 h-1 rounded-full {{ $u->role === 'admin' ? 'bg-indigo-500' : 'bg-sky-500' }}"></span>
                                    {{ ucfirst($u->role) }} Access
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400 dark:text-slate-500 font-medium">Email Verification</span>
                                @if($isEmailVerified)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Verified
                                    </span>
                                @else
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Pending
                                        </span>
                                        <form method="POST" action="{{ route('users.resend-verification', $u) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2 py-0.5 rounded-md border border-[#199CA4]/30 bg-[#199CA4]/10 hover:bg-[#199CA4]/20 text-[10px] font-bold text-[#199CA4] hover:text-[#13787F] transition cursor-pointer" title="Resend verification email to {{ $u->email }}">
                                                Resend
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400 dark:text-slate-500 font-medium">Phone</span>
                                @if($phone && $phone !== 'No phone set')
                                    <a href="tel:{{ $phone }}" class="font-mono text-xs text-[#199CA4] hover:underline font-semibold">{{ $phone }}</a>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 text-xs italic">{{ $phone }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Action button --}}
                        <div class="pt-1">
                            <button type="button" onclick="openStaffModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($staffCode) }}', '{{ addslashes($positionTitle) }}', '{{ addslashes($profile?->phone ?? '') }}', '{{ addslashes($profile?->specialization ?? '') }}', '{{ $status }}', '{{ $u->role }}', {{ $u->id === auth()->id() ? 'true' : 'false' }})" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#12272b] hover:bg-slate-50 dark:hover:bg-[#18353b] text-xs font-bold text-slate-700 dark:text-slate-200 transition shadow-2xs cursor-pointer">
                                Edit Details
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Desktop Data Table (>=md) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-[#171923] border-b border-slate-200/80 dark:border-white/[0.06] text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 lg:px-5 py-3.5">Staff Member</th>
                            <th class="px-4 lg:px-5 py-3.5">Designation & Role</th>
                            <th class="px-4 lg:px-5 py-3.5">Email Status</th>
                            <th class="px-4 lg:px-5 py-3.5">Contact Phone</th>
                            <th class="px-4 lg:px-5 py-3.5">Staff Status</th>
                            <th class="px-4 lg:px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06] text-xs sm:text-sm">
                        @foreach($users as $u)
                            @php
                                $profile = $u->staffProfile;
                                $staffCode = $profile?->staff_code ?? (($u->role === 'admin' ? 'ADM-' : 'STF-') . str_pad((string) $u->id, 4, '0', STR_PAD_LEFT));
                                $positionTitle = $profile?->position_title ?? ($u->role === 'admin' ? 'Shelter Director / Head Administrator' : 'CAWS Staff Member');
                                $phone = $profile?->phone ?: 'No phone set';
                                $status = $profile?->status ?? 'active';
                                $isEmailVerified = $u->hasVerifiedEmail();
                            @endphp
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-[#181A24] transition-colors">
                                <td class="px-4 lg:px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        @if($u->avatar_url)
                                            <img src="{{ $u->avatar_url }}" alt="{{ $u->name }}" class="w-10 h-10 rounded-2xl object-cover border border-slate-200 dark:border-white/[0.08] shadow-2xs shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-200 flex items-center justify-center font-extrabold text-xs border border-slate-200 dark:border-white/[0.08] shadow-2xs shrink-0">
                                                {{ $u->initials }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="text-slate-900 dark:text-white font-extrabold text-sm leading-tight truncate">{{ $u->name }}</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08]">
                                                    {{ $staffCode }}
                                                </span>
                                            </div>
                                            <span class="block text-[11px] text-slate-400 dark:text-slate-500 font-normal mt-0.5 truncate">{{ $u->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 lg:px-5 py-3.5">
                                    <div class="flex flex-col gap-1 max-w-[220px]">
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 leading-snug">{{ $positionTitle }}</span>
                                        <span class="inline-flex items-center gap-1.5 w-fit px-2 py-0.5 rounded-full text-[10px] font-extrabold border {{ $u->role === 'admin' ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60' : 'bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800/60' }}">
                                            <span class="w-1 h-1 rounded-full {{ $u->role === 'admin' ? 'bg-indigo-500' : 'bg-sky-500' }}"></span>
                                            {{ ucfirst($u->role) }} Access
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 lg:px-5 py-3.5 whitespace-nowrap">
                                    @if($isEmailVerified)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold border bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Verified
                                        </span>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold border bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                Pending
                                            </span>
                                            <form method="POST" action="{{ route('users.resend-verification', $u) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2 py-0.5 rounded-lg border border-[#199CA4]/30 bg-[#199CA4]/10 hover:bg-[#199CA4]/20 text-[10px] font-bold text-[#199CA4] hover:text-[#13787F] transition cursor-pointer" title="Resend verification email to {{ $u->email }}">
                                                    Resend Link
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 lg:px-5 py-3.5">
                                    <div class="max-w-[150px] truncate text-xs text-slate-500 dark:text-slate-400">
                                        @if($phone && $phone !== 'No phone set')
                                            <a href="tel:{{ $phone }}" class="font-mono text-[#199CA4] hover:underline font-semibold" title="{{ $phone }}">{{ $phone }}</a>
                                        @else
                                            <span class="italic text-slate-400 dark:text-slate-500">{{ $phone }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 lg:px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $status === 'active' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $status === 'active' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        {{ $status === 'active' ? 'Active' : 'Deactivated' }}
                                    </span>
                                </td>
                                <td class="px-4 lg:px-5 py-3.5 whitespace-nowrap text-right">
                                    <button type="button" onclick="openStaffModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($staffCode) }}', '{{ addslashes($positionTitle) }}', '{{ addslashes($profile?->phone ?? '') }}', '{{ addslashes($profile?->specialization ?? '') }}', '{{ $status }}', '{{ $u->role }}', {{ $u->id === auth()->id() ? 'true' : 'false' }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#12272b] hover:bg-slate-50 dark:hover:bg-[#18353b] text-xs font-bold text-slate-700 dark:text-slate-200 transition shadow-2xs cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit Details
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Edit Staff Details Modal --}}
    <div id="staffModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#12141C] w-full max-w-md max-h-[92vh] flex flex-col rounded-3xl border border-slate-200 dark:border-white/[0.08] shadow-2xl p-5 sm:p-6">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 shrink-0">
                <div>
                    <h3 id="modalStaffName" class="text-base font-bold text-slate-900 dark:text-white">Edit Staff Details</h3>
                    <p id="modalStaffCode" class="text-xs text-slate-400 font-mono mt-0.5">Code: STF-0000</p>
                </div>
                <button type="button" onclick="closeStaffModal()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-white flex items-center justify-center transition cursor-pointer" aria-label="Close modal">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal Form Body --}}
            <form id="staffModalForm" method="POST" class="space-y-3.5 mt-3.5 overflow-y-auto pr-1">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Position / Designation Title</label>
                    <input type="text" id="modalPosition" name="position_title" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3.5 py-2 text-xs font-semibold bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Contact Phone</label>
                        <input type="text" id="modalPhone" name="phone" placeholder="09123456789" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3.5 py-2 text-xs font-semibold bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Staff Status</label>
                        <div class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3.5 py-2 bg-slate-50/80 dark:bg-slate-900/60 flex items-center justify-between">
                            <span id="modalStatusBadge" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold border">
                                <span id="modalStatusDot" class="w-1.5 h-1.5 rounded-full"></span>
                                <span id="modalStatusText">Active</span>
                            </span>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">Changed via button below</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Specialization / Department (Optional)</label>
                    <input type="text" id="modalSpecialization" name="specialization" placeholder="e.g. Veterinary Care, Adoption Screening" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3.5 py-2 text-xs font-semibold bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4]">
                </div>

                {{-- Account Access & Deactivation Section inside Modal --}}
                <div id="modalDeactivateSection" class="p-3.5 rounded-2xl border transition-colors hidden">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <h4 id="modalDeactivateHeading" class="text-xs font-bold text-slate-800 dark:text-slate-200">Account Access</h4>
                            <p id="modalDeactivateDesc" class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-snug">
                                Deactivate this staff account if they are no longer part of the organization.
                            </p>
                        </div>
                        <button type="button" id="modalDeactivateBtn" onclick="triggerStaffStatusConfirm()" class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                            <!-- Populated dynamically via JS -->
                        </button>
                    </div>
                </div>

                {{-- Modal Action Buttons: Always in view without sliding --}}
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800 shrink-0">
                    <button type="button" onclick="closeStaffModal()" class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-xs font-bold text-white shadow-xs transition cursor-pointer">
                        Save Staff Details
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Dedicated Staff Status Confirmation Modal (Always z-[100] above all modals) --}}
    <div id="staffStatusConfirmDialog" onclick="if(event.target === this) closeStaffStatusConfirm()" class="fixed inset-0 z-[100] hidden bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#12141C] w-full max-w-sm rounded-3xl border border-slate-200 dark:border-white/[0.08] shadow-2xl p-6 text-center space-y-4 relative animate-fade-in">
            {{-- Status Icon --}}
            <div id="statusConfirmIconBox" class="w-14 h-14 rounded-2xl mx-auto flex items-center justify-center shadow-xs">
                <!-- SVG Icon populated dynamically -->
            </div>

            <div class="space-y-1.5">
                <h3 id="statusConfirmTitle" class="text-base font-extrabold text-slate-900 dark:text-white"></h3>
                <p id="statusConfirmDesc" class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed"></p>
            </div>

            <div class="flex items-center gap-2.5 pt-2">
                <button type="button" onclick="closeStaffStatusConfirm()" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#181A24] text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition cursor-pointer">
                    Cancel
                </button>
                <form id="staffStatusToggleForm" method="POST" action="" class="flex-1">
                    @csrf
                    <button type="submit" id="statusConfirmSubmitBtn" class="w-full px-4 py-2.5 rounded-xl text-xs font-bold text-white transition shadow-xs cursor-pointer">
                        Confirm
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        var activeStaffData = null;

        function openStaffModal(userId, name, code, position, phone, specialization, status, role, isSelf) {
            var normStatus = (status === 'inactive' || status === 'deactivated') ? 'deactivated' : status;

            activeStaffData = {
                id: userId,
                name: name,
                code: code,
                position: position,
                phone: phone,
                specialization: specialization,
                status: normStatus,
                role: role,
                isSelf: isSelf
            };

            document.getElementById('modalStaffName').textContent = name;
            document.getElementById('modalStaffCode').textContent = 'Staff Code: ' + code;
            document.getElementById('modalPosition').value = position;
            document.getElementById('modalPhone').value = phone;
            document.getElementById('modalSpecialization').value = specialization;
            document.getElementById('staffModalForm').action = '/users/' + userId + '/staff-profile';

            var statusBadge = document.getElementById('modalStatusBadge');
            var statusDot = document.getElementById('modalStatusDot');
            var statusText = document.getElementById('modalStatusText');

            if (normStatus === 'deactivated') {
                statusBadge.className = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold border bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60';
                statusDot.className = 'w-1.5 h-1.5 rounded-full bg-rose-500';
                statusText.textContent = 'Deactivated';
            } else {
                statusBadge.className = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold border bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60';
                statusDot.className = 'w-1.5 h-1.5 rounded-full bg-emerald-500';
                statusText.textContent = 'Active';
            }

            var deactivateSection = document.getElementById('modalDeactivateSection');
            var deactivateHeading = document.getElementById('modalDeactivateHeading');
            var deactivateDesc = document.getElementById('modalDeactivateDesc');
            var deactivateBtn = document.getElementById('modalDeactivateBtn');

            if (role === 'staff' && !isSelf) {
                deactivateSection.classList.remove('hidden');
                deactivateSection.style.display = 'block';

                if (normStatus === 'deactivated') {
                    deactivateSection.className = 'p-3.5 rounded-2xl border transition-colors bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-200/80 dark:border-emerald-800/40';
                    deactivateHeading.textContent = 'Account Deactivated';
                    deactivateDesc.textContent = 'This staff account is currently deactivated. Reactivate to restore portal login access.';
                    deactivateBtn.className = 'shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs border border-emerald-300 dark:border-emerald-700/60 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 cursor-pointer';
                    deactivateBtn.textContent = 'Reactivate Account';
                } else {
                    deactivateSection.className = 'p-3.5 rounded-2xl border transition-colors bg-rose-50/50 dark:bg-rose-950/20 border-rose-200/80 dark:border-rose-800/40';
                    deactivateHeading.textContent = 'Deactivate Account';
                    deactivateDesc.textContent = 'Deactivate this staff account if they are no longer part of the organization.';
                    deactivateBtn.className = 'shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs border border-rose-200 dark:border-rose-900/60 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-900/60 cursor-pointer';
                    deactivateBtn.textContent = 'Deactivate Account';
                }
            } else {
                deactivateSection.classList.add('hidden');
                deactivateSection.style.display = 'none';
            }

            var modal = document.getElementById('staffModal');
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }

        function closeStaffModal() {
            var modal = document.getElementById('staffModal');
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }

        function triggerStaffStatusConfirm() {
            if (!activeStaffData) return;

            var dialog = document.getElementById('staffStatusConfirmDialog');
            var iconBox = document.getElementById('statusConfirmIconBox');
            var title = document.getElementById('statusConfirmTitle');
            var desc = document.getElementById('statusConfirmDesc');
            var form = document.getElementById('staffStatusToggleForm');
            var submitBtn = document.getElementById('statusConfirmSubmitBtn');

            form.action = '/users/' + activeStaffData.id + '/toggle-status';

            if (activeStaffData.status === 'deactivated') {
                iconBox.className = 'w-14 h-14 rounded-2xl mx-auto flex items-center justify-center bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60';
                iconBox.innerHTML = '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                title.textContent = 'Reactivate Staff Account?';
                desc.innerHTML = 'Are you sure you want to reactivate the account for <strong>' + activeStaffData.name + '</strong>? They will regain portal access immediately.';
                submitBtn.className = 'w-full px-4 py-2.5 rounded-xl text-xs font-bold text-white transition shadow-xs bg-emerald-600 hover:bg-emerald-700 cursor-pointer';
                submitBtn.textContent = 'Confirm Reactivation';
            } else {
                iconBox.className = 'w-14 h-14 rounded-2xl mx-auto flex items-center justify-center bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60';
                iconBox.innerHTML = '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
                title.textContent = 'Deactivate Staff Account?';
                desc.innerHTML = 'Are you sure you want to deactivate the account for <strong>' + activeStaffData.name + '</strong>? They will be signed out immediately and won\'t be able to log in to the portal.';
                submitBtn.className = 'w-full px-4 py-2.5 rounded-xl text-xs font-bold text-white transition shadow-xs bg-rose-600 hover:bg-rose-700 cursor-pointer';
                submitBtn.textContent = 'Confirm Deactivation';
            }

            dialog.style.display = 'flex';
            dialog.classList.remove('hidden');
        }

        function closeStaffStatusConfirm() {
            var dialog = document.getElementById('staffStatusConfirmDialog');
            dialog.style.display = 'none';
            dialog.classList.add('hidden');
        }
    </script>
</x-app-layout>