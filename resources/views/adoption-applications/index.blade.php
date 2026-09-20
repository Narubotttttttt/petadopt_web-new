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

            {{-- Mobile Card Feed (<md) --}}
            <div class="block md:hidden divide-y divide-slate-100 dark:divide-white/[0.06]">
                @forelse($applications as $application)
                    @php
                        $pet = $application->pet;
                        $petImg = $pet && $pet->photo_path ? (str_starts_with($pet->photo_path, 'http') ? $pet->photo_path : asset('storage/' . ltrim($pet->photo_path, '/'))) : null;
                    @endphp
                    <div class="p-4 space-y-3.5">
                        {{-- Top row: Pet info + status --}}
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                @if($petImg)
                                    <div class="w-12 h-12 rounded-2xl overflow-hidden shrink-0 border border-slate-200 dark:border-white/[0.08] shadow-2xs relative bg-slate-100 dark:bg-[#171923]">
                                        <img src="{{ $petImg }}" alt="{{ $pet->name ?? 'Pet' }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="hidden w-full h-full bg-slate-100 dark:bg-white/[0.06] text-slate-500 dark:text-slate-300 items-center justify-center font-bold text-sm">
                                            <svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                        </div>
                                    </div>
                                @else
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-white/[0.06] text-slate-400 border border-slate-200 dark:border-white/[0.08] flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="font-extrabold text-slate-900 dark:text-white text-sm truncate">
                                        {{ $pet && !empty($pet->name) ? $pet->name : 'Pet no. ' . $application->pet_id }}
                                    </div>
                                    <div class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 truncate">
                                        Pet no. {{ $application->pet_id }} · {{ $pet->breed ?? 'Mixed Breed' }}
                                    </div>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold border shrink-0
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
                        </div>

                        {{-- Middle applicant details card --}}
                        <div class="bg-slate-50/80 dark:bg-[#171923] p-3 rounded-xl border border-slate-100 dark:border-white/[0.04] space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400 dark:text-slate-500 font-medium">Applicant</span>
                                <span class="font-extrabold text-slate-900 dark:text-white">{{ $application->applicant_name }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400 dark:text-slate-500 font-medium">Origin</span>
                                @if($application->application_source === 'recommendation')
                                    <span class="font-extrabold text-emerald-600 dark:text-emerald-400 inline-flex items-center gap-1">
                                        AI Recommendation {{ $application->compatibility_score !== null ? '(' . number_format($application->compatibility_score, 0) . '%)' : '' }}
                                    </span>
                                @else
                                    <span class="font-semibold text-slate-600 dark:text-slate-400">Manual Catalog</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400 dark:text-slate-500 font-medium">Contact Phone</span>
                                <a href="tel:{{ $application->applicant_phone }}" class="font-mono font-semibold text-[#199CA4] hover:underline">{{ $application->applicant_phone }}</a>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400 dark:text-slate-500 font-medium">Submitted</span>
                                <span class="text-slate-600 dark:text-slate-400 font-medium">{{ $application->created_at ? $application->created_at->format('M d, Y · h:i A') : '—' }}</span>
                            </div>
                        </div>

                        {{-- Action buttons --}}
                        <div class="flex items-center gap-2 pt-1">
                            @if(in_array($application->status, ['approved', 'adopted']))
                                @if($application->signature_path)
                                    <a href="{{ route('adoption-applications.contract', $application) }}" target="_blank" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-[#1D1F2C] font-bold transition shadow-2xs text-xs shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                        <span>Print</span>
                                    </a>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1.5 rounded-xl text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 shrink-0">
                                        Unsigned
                                    </span>
                                @endif
                            @endif
                            <a href="{{ route('adoption-applications.show', $application) }}" class="flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white font-extrabold transition shadow-2xs text-xs">
                                View Details
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-slate-400 dark:text-slate-500">
                        <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        No adoption applications submitted yet.
                    </div>
                @endforelse
            </div>

            {{-- Desktop Data Table (>=md) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-[#171923] border-b border-slate-200/80 dark:border-white/[0.06]">
                        <tr>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Pet</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Applicant</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Origin / Match</th>
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
                                                    <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                                </div>
                                            </div>
                                        @else
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/[0.06] text-slate-400 border border-slate-200 dark:border-white/[0.08] flex items-center justify-center shrink-0">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
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
                                    @if($application->application_source === 'recommendation')
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                AI Match
                                            </span>
                                            @if($application->compatibility_score !== null)
                                                <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 font-mono">
                                                    {{ number_format($application->compatibility_score, 0) }}%
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-white/[0.08]">
                                            Manual Catalog
                                        </span>
                                    @endif
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
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-500 dark:text-slate-400 font-medium">
                                    {{ $application->created_at ? $application->created_at->format('M d, Y · h:i A') : '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right space-x-1.5">
                                    @if(in_array($application->status, ['approved', 'adopted']))
                                        @if($application->signature_path)
                                            <a href="{{ route('adoption-applications.contract', $application) }}" target="_blank" title="Print Contract" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-[#1D1F2C] font-bold transition shadow-2xs text-xs">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                </svg>
                                                <span>Print</span>
                                            </a>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60" title="Awaiting adopter signature">
                                                Unsigned
                                            </span>
                                        @endif
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