<x-app-layout>
    <div class="w-full py-6 sm:py-8 px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Staff Management</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Manage shelter personnel, official staff codes, job titles, and active duty statuses.</p>
            </div>
            <div>
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#199CA4] hover:bg-[#13787F] px-4 py-2.5 text-xs sm:text-sm font-bold text-white transition-all shadow-xs">
                    <span>+</span> Add Staff Member
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-[#12141C] rounded-2xl sm:rounded-3xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-[#171923] border-b border-slate-200/80 dark:border-white/[0.06] text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3.5">Staff Member</th>
                            <th class="px-6 py-3.5">Staff Code</th>
                            <th class="px-6 py-3.5">Designation / Role</th>
                            <th class="px-6 py-3.5">Contact</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06] text-xs sm:text-sm">
                        @foreach($users as $u)
                            @php
                                $profile = $u->staffProfile;
                                $staffCode = $profile?->staff_code ?? (($u->role === 'admin' ? 'ADM-' : 'STF-') . str_pad($u->id, 4, '0', STR_PAD_LEFT));
                                $positionTitle = $profile?->position_title ?? ($u->role === 'admin' ? 'Shelter Director / Head Administrator' : 'CAWS Staff Member');
                                $phone = $profile?->phone ?: 'No phone set';
                                $status = $profile?->status ?? 'active';
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
        <div class="bg-white dark:bg-[#12141C] w-full max-w-lg rounded-3xl border border-slate-200 dark:border-white/[0.08] shadow-2xl p-6 sm:p-7 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <h3 id="modalStaffName" class="text-lg font-bold text-slate-900 dark:text-white">Edit Staff Details</h3>
                    <p id="modalStaffCode" class="text-xs text-slate-400 font-mono mt-0.5">Code: STF-0000</p>
                </div>
                <button type="button" onclick="closeStaffModal()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-white flex items-center justify-center text-xs font-bold transition">
                    
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