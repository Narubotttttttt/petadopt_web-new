<x-app-layout>
    <div class="w-full py-6 sm:py-8 px-4 sm:px-6 lg:px-8 animate-fade-in space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Adoption Requests</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Review incoming applications from mobile adopters and schedule meet-and-greets.</p>
            </div>
        </div>

        <div class="bg-white dark:bg-[#12141C] rounded-2xl sm:rounded-3xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between bg-slate-50/50 dark:bg-[#171923]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20 dark:border-[#41C1CB]/30 flex items-center justify-center font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white">Submitted Applications</h2>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-[#171923] border-b border-slate-200/80 dark:border-white/[0.06]">
                        <tr>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Pet</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Applicant</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Submitted Date</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                        @forelse($applications as $application)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-[#181A24] text-xs sm:text-sm transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-slate-900 dark:text-white font-extrabold">
                                    <div class="flex items-center gap-3">
                                        @php
                                            $pet = $application->pet;
                                            $petImg = $pet && $pet->photo_path ? (str_starts_with($pet->photo_path, 'http') ? $pet->photo_path : asset('storage/' . ltrim($pet->photo_path, '/'))) : null;
                                        @endphp
                                        @if($petImg)
                                            <div class="w-10 h-10 rounded-xl overflow-hidden shrink-0 border border-slate-200 dark:border-white/[0.08] shadow-2xs relative bg-slate-100 dark:bg-[#171923]">
                                                <img src="{{ $petImg }}" alt="{{ $pet->name ?? 'Pet' }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="hidden w-full h-full bg-slate-100 dark:bg-white/[0.06] text-slate-500 dark:text-slate-300 items-center justify-center font-bold text-sm">
                                                    <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c-1.66 0-3 1.34-3 3 0 2 2 3.5 3 3.5s3-1.5 3-3.5c0-1.66-1.34-3-3-3zm-4.5-2c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm9 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/></svg>
                                                </div>
                                            </div>
                                        @else
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/[0.06] text-slate-400 border border-slate-200 dark:border-white/[0.08] flex items-center justify-center shrink-0">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c-1.66 0-3 1.34-3 3 0 2 2 3.5 3 3.5s3-1.5 3-3.5c0-1.66-1.34-3-3-3zm-4.5-2c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm9 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/></svg>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-extrabold text-slate-900 dark:text-white">
                                                {{ $pet && !empty($pet->name) ? $pet->name : 'Pet no. ' . $application->pet_id }}
                                            </div>
                                            <div class="text-[11px] font-semibold text-slate-400 dark:text-slate-500">
                                                Pet no. {{ $application->pet_id }} · {{ $pet->breed ?? 'Mixed Breed' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-700 dark:text-slate-300">
                                    <div class="font-extrabold text-slate-900 dark:text-white">{{ $application->applicant_name }}</div>
                                    <div class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 font-medium">{{ $application->applicant_phone }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold border w-fit
                                            {{ $application->status === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60' : '' }}
                                            {{ $application->status === 'rejected' ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60' : '' }}
                                            {{ $application->status === 'under_review' ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60' : '' }}
                                            {{ $application->status === 'pending' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60' : '' }}">
                                            <span class="w-1.5 h-1.5 rounded-full
                                                {{ $application->status === 'approved' ? 'bg-emerald-500' : '' }}
                                                {{ $application->status === 'rejected' ? 'bg-rose-500' : '' }}
                                                {{ $application->status === 'under_review' ? 'bg-indigo-500' : '' }}
                                                {{ $application->status === 'pending' ? 'bg-amber-500' : '' }}"></span>
                                            {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                        </span>

                                        @if($application->evaluated_at)
                                            <span class="inline-flex items-center gap-1 text-[10px] text-[#199CA4] dark:text-[#41C1CB] font-bold">
                                                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                <span>Screened ({{ ucfirst(str_replace('_', ' ', $application->evaluation_recommendation ?? 'Evaluated')) }})</span>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-500 dark:text-slate-400 font-medium">
                                    {{ $application->created_at ? $application->created_at->format('M d, Y · h:i A') : '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right space-x-1.5">
                                    @if(in_array($application->status, ['approved', 'adopted']))
                                        <a href="{{ route('adoption-applications.contract', $application) }}" target="_blank" title="Print Contract" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-[#1D1F2C] font-bold transition shadow-2xs text-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                            <span>Print</span>
                                        </a>
                                    @endif
                                    <a href="{{ route('adoption-applications.show', $application) }}" class="inline-flex items-center px-3.5 py-1.5 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white font-extrabold transition-all duration-200 shadow-2xs text-xs">View Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-400 dark:text-slate-500">
                                    <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    No adoption applications submitted yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 dark:border-white/[0.06] bg-slate-50/50 dark:bg-[#171923]">
                {{ $applications->links() }}
            </div>
        </div>
    </div>
</x-app-layout>