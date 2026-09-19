<x-app-layout>
    <div class="w-full py-6 sm:py-8 px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Staff Management</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Manage shelter personnel, official staff codes, job titles, and active duty statuses.</p>
            </div>
            <div class="w-full sm:w-auto">
                <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#199CA4] hover:bg-[#13787F] px-4 py-2.5 text-xs sm:text-sm font-bold text-white transition-all shadow-xs">
                    <span>+</span> Add Staff Member
                </a>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div role="alert" class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl text-xs text-emerald-800 dark:text-emerald-300 flex items-center gap-3 shadow-xs animate-fade-in">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div role="alert" class="p-4 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800/60 rounded-2xl text-xs text-sky-800 dark:text-sky-300 flex items-center gap-3 shadow-xs animate-fade-in">
                <svg class="w-5 h-5 text-sky-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-semibold">{{ session('info') }}</span>
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
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border shrink-0 {{ $status === 'active' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $status === 'active' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
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
                            <button type="button" onclick="openStaffModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($staffCode) }}', '{{ addslashes($positionTitle) }}', '{{ addslashes($profile?->phone ?? '') }}', '{{ addslashes($profile?->specialization ?? '') }}', '{{ $status }}')" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#12272b] hover:bg-slate-50 dark:hover:bg-[#18353b] text-xs font-bold text-slate-700 dark:text-slate-200 transition shadow-2xs cursor-pointer">
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
                            <th class="px-6 py-3.5">Staff Member</th>
                            <th class="px-6 py-3.5">Staff Code</th>
                            <th class="px-6 py-3.5">Designation / Role</th>
                            <th class="px-6 py-3.5">Email Status</th>
                            <th class="px-6 py-3.5">Contact</th>
                            <th class="px-6 py-3.5">Duty Status</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
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
                                            <span class="block text-[11px] text-slate-400 dark:text-slate-500 font-normal mt-0.5">{{ $u->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-slate-100 dark:bg-white/[0.06] text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-white/[0.08]">
                                        {{ $staffCode }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $positionTitle }}</span>
                                        <span class="inline-flex items-center gap-1.5 w-fit px-2 py-0.5 rounded-full text-[10px] font-extrabold border {{ $u->role === 'admin' ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60' : 'bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800/60' }}">
                                            <span class="w-1 h-1 rounded-full {{ $u->role === 'admin' ? 'bg-indigo-500' : 'bg-sky-500' }}"></span>
                                            {{ ucfirst($u->role) }} Access
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                <td class="px-6 py-4 whitespace-nowrap text-slate-500 dark:text-slate-400 text-xs">
                                    {{ $phone }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $status === 'active' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $status === 'active' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <button type="button" onclick="openStaffModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($staffCode) }}', '{{ addslashes($positionTitle) }}', '{{ addslashes($profile?->phone ?? '') }}', '{{ addslashes($profile?->specialization ?? '') }}', '{{ $status }}')" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#12272b] hover:bg-slate-50 dark:hover:bg-[#18353b] text-xs font-bold text-slate-700 dark:text-slate-200 transition cursor-pointer">
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
        <div class="bg-white dark:bg-[#12141C] w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-3xl border border-slate-200 dark:border-white/[0.08] shadow-2xl p-6 sm:p-7 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <h3 id="modalStaffName" class="text-lg font-bold text-slate-900 dark:text-white">Edit Staff Details</h3>
                    <p id="modalStaffCode" class="text-xs text-slate-400 font-mono mt-0.5">Code: STF-0000</p>
                </div>
                <button type="button" onclick="closeStaffModal()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-white flex items-center justify-center transition" aria-label="Close modal">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="staffModalForm" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Position / Designation Title</label>
                    <input type="text" id="modalPosition" name="position_title" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3.5 py-2 text-xs font-semibold bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Contact Phone</label>
                        <input type="text" id="modalPhone" name="phone" placeholder="09123456789" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3.5 py-2 text-xs font-semibold bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Employment Status</label>
                        <select id="modalStatus" name="status" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3.5 py-2 text-xs font-semibold bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4]">
                            <option value="active">Active</option>
                            <option value="on_leave">On Leave</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Specialization / Department (Optional)</label>
                    <input type="text" id="modalSpecialization" name="specialization" placeholder="e.g. Veterinary Care, Adoption Screening" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3.5 py-2 text-xs font-semibold bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4]">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="closeStaffModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-xs font-bold text-white shadow-xs transition">
                        Save Staff Details
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openStaffModal(userId, name, code, position, phone, specialization, status) {
            document.getElementById('modalStaffName').textContent = name;
            document.getElementById('modalStaffCode').textContent = 'Staff Code: ' + code;
            document.getElementById('modalPosition').value = position;
            document.getElementById('modalPhone').value = phone;
            document.getElementById('modalSpecialization').value = specialization;
            document.getElementById('modalStatus').value = status;
            document.getElementById('staffModalForm').action = '/users/' + userId + '/staff-profile';
            document.getElementById('staffModal').classList.remove('hidden');
        }

        function closeStaffModal() {
            document.getElementById('staffModal').classList.add('hidden');
        }
    </script>
</x-app-layout>