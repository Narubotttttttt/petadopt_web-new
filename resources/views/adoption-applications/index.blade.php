<x-app-layout>
    <div class="w-full py-8 px-4 sm:px-6 lg:px-8 animate-fade-in space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">Adoption Requests</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Review incoming applications from mobile adopters and schedule meet-and-greets.</p>
            </div>
        </div>

        <div class="bg-white dark:bg-[#0e1d20] rounded-3xl shadow-card border border-[#199CA4]/20 dark:border-slate-800 overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-gradient-to-r from-white via-[#F0FBFB]/30 to-white dark:from-[#0a171a] dark:via-[#0e1d20] dark:to-[#0a171a]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-sm">📋</div>
                    <h2 class="text-base font-extrabold text-slate-800 dark:text-white">Submitted Applications</h2>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-[#F8FAFA] dark:bg-[#091518] border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Pet</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Applicant</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Submitted Date</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @forelse($applications as $application)
                            <tr class="table-row-hover text-xs sm:text-sm">
                                <td class="px-6 py-4 whitespace-nowrap text-slate-800 dark:text-white font-extrabold">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-7 h-7 rounded-lg bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center text-xs">🐾</span>
                                        <span>Pet #{{ $application->pet_id }} ({{ $application->pet->breed ?? 'Mixed Breed' }})</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-700 dark:text-slate-300">
                                    <div class="font-extrabold text-slate-800 dark:text-white">{{ $application->applicant_name }}</div>
                                    <div class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 font-medium">{{ $application->applicant_phone }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold border
                                        {{ $application->status === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : '' }}
                                        {{ $application->status === 'rejected' ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800' : '' }}
                                        {{ $application->status === 'under_review' ? 'bg-[#199CA4]/10 dark:bg-[#199CA4]/25 text-[#199CA4] dark:text-[#41C1CB] border-[#199CA4]/20 dark:border-[#41C1CB]/30' : '' }}
                                        {{ $application->status === 'pending' ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800' : '' }}">
                                        <span class="w-1.5 h-1.5 rounded-full
                                            {{ $application->status === 'approved' ? 'bg-emerald-500' : '' }}
                                            {{ $application->status === 'rejected' ? 'bg-rose-500' : '' }}
                                            {{ $application->status === 'under_review' ? 'bg-[#199CA4]' : '' }}
                                            {{ $application->status === 'pending' ? 'bg-amber-500' : '' }}"></span>
                                        {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-500 dark:text-slate-400 font-medium">
                                    {{ $application->created_at ? $application->created_at->format('M d, Y · h:i A') : '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right space-x-1.5">
                                    @if(in_array($application->status, ['approved', 'adopted']))
                                        <a href="{{ route('adoption-applications.contract', $application) }}" target="_blank" title="Print Contract" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] font-bold border border-[#199CA4]/20 dark:border-[#41C1CB]/30 hover:bg-[#199CA4] hover:text-white transition shadow-2xs text-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                            <span>Print</span>
                                        </a>
                                    @endif
                                    <a href="{{ route('adoption-applications.show', $application) }}" class="inline-flex items-center px-3.5 py-1.5 rounded-xl bg-[#199CA4] text-white font-extrabold hover:bg-[#146970] transition-all duration-200 shadow-2xs text-xs">View Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-400 dark:text-slate-500">
                                    <div class="text-2xl mb-1">📋</div>
                                    No adoption applications submitted yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-[#F8FAFA] dark:bg-[#091518]">
                {{ $applications->links() }}
            </div>
        </div>
    </div>
</x-app-layout>