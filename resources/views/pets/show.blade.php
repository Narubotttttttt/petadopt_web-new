<x-app-layout>
    <div class="w-full py-6 sm:py-8 px-4 sm:px-6 lg:px-8 animate-fade-in space-y-8">
        
        {{-- Pet Header Card --}}
        <div class="bg-white dark:bg-[#12141C] p-6 sm:p-8 rounded-3xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-[#199CA4]"></div>

            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between pb-8 border-b border-slate-100 dark:border-white/[0.06]">
                <div class="flex items-center gap-4">
                    @if($pet->photo_path)
                        <div class="w-14 h-14 rounded-2xl overflow-hidden shrink-0 border border-slate-200 dark:border-white/[0.08] shadow-2xs relative bg-slate-100 dark:bg-[#171923]">
                            <img src="{{ asset('storage/'.$pet->photo_path) }}" alt="Pet no. {{ $pet->id }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="hidden w-full h-full bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] items-center justify-center">
                                <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c-1.66 0-3 1.34-3 3 0 2 2 3.5 3 3.5s3-1.5 3-3.5c0-1.66-1.34-3-3-3zm-4.5-2c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm9 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/></svg>
                            </div>
                        </div>
                    @else
                        <div class="w-14 h-14 rounded-2xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-extrabold text-2xl border border-[#199CA4]/20 dark:border-[#41C1CB]/30 shrink-0">
                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c-1.66 0-3 1.34-3 3 0 2 2 3.5 3 3.5s3-1.5 3-3.5c0-1.66-1.34-3-3-3zm-4.5-2c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm9 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/></svg>
                        </div>
                    @endif
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Pet no. {{ $pet->id }}</h1>
                            @php
                                $statusStyles = [
                                    'available' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60',
                                    'pending' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60',
                                    'adopted' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60',
                                ];
                                $dotColors = [
                                    'available' => 'bg-emerald-500',
                                    'pending' => 'bg-amber-500',
                                    'adopted' => 'bg-indigo-500',
                                ];
                                $style = $statusStyles[$pet->status] ?? 'bg-slate-50 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 border-slate-200 dark:border-white/[0.08]';
                                $dot = $dotColors[$pet->status] ?? 'bg-slate-400';
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-extrabold border {{ $style }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $dot }} animate-pulse"></span>
                                {{ ucfirst($pet->status ?? 'Unknown') }}
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium mt-1">{{ $pet->breed ?? 'Mixed Breed' }} • {{ ucfirst($pet->color ?? '—') }} • Added {{ $pet->created_at->diffForHumans() }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    @if(in_array(Auth::user()?->role, ['admin', 'staff']))
                        <a href="{{ route('pets.edit', $pet) }}" class="px-5 py-2.5 bg-[#199CA4] hover:bg-[#13787F] text-white rounded-xl text-xs sm:text-sm font-extrabold shadow-xs transition-all">Edit Profile</a>
                    @endif

                    @unless(in_array(Auth::user()?->role, ['admin', 'staff']))
                        @if($pet->status === 'available')
                            <a href="{{ route('adoption-applications.create', $pet) }}" class="px-5 py-2.5 bg-[#199CA4] hover:bg-[#13787F] text-white rounded-xl text-xs sm:text-sm font-extrabold shadow-xs transition-all">Apply to adopt</a>
                        @endif
                    @endunless

                    <a href="{{ route('pets.index') }}" class="px-4 py-2.5 border border-slate-200 dark:border-white/[0.08] text-slate-600 dark:text-slate-300 rounded-xl text-xs sm:text-sm font-bold hover:bg-slate-50 dark:hover:bg-white/[0.06] transition">← Back to Catalog</a>
                </div>
            </div>

            {{-- Pet Media & Details Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-8">
                <div class="md:col-span-1">
                    <div class="w-full h-72 bg-slate-50 dark:bg-[#0C0D13] rounded-2xl overflow-hidden flex items-center justify-center border border-slate-200 dark:border-white/[0.08] shadow-2xs group relative">
                        @if($pet->photo_path)
                            <img src="{{ asset('storage/'.$pet->photo_path) }}" alt="Pet no. {{ $pet->id }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="hidden w-full h-full bg-slate-100 dark:bg-[#0C0D13] flex-col items-center justify-center text-center p-6">
                                <svg class="w-12 h-12 text-slate-400 dark:text-slate-600 mb-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c-1.66 0-3 1.34-3 3 0 2 2 3.5 3 3.5s3-1.5 3-3.5c0-1.66-1.34-3-3-3zm-4.5-2c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm9 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/></svg>
                                <span class="text-xs text-slate-500 font-bold">Pet no. {{ $pet->id }}</span>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center text-center p-6">
                                <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c-1.66 0-3 1.34-3 3 0 2 2 3.5 3 3.5s3-1.5 3-3.5c0-1.66-1.34-3-3-3zm-4.5-2c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm9 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/></svg>
                                <span class="text-xs text-slate-400 font-semibold">No photo uploaded</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="md:col-span-2 space-y-6">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3.5">
                        <div class="bg-white dark:bg-[#171923] p-4 rounded-2xl border border-slate-200 dark:border-white/[0.06] shadow-2xs">
                            <p class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-400">Species</p>
                            <p class="text-sm font-extrabold text-slate-900 dark:text-white mt-0.5">{{ ucfirst($pet->type ?? '—') }}</p>
                        </div>
                        <div class="bg-white dark:bg-[#171923] p-4 rounded-2xl border border-slate-200 dark:border-white/[0.06] shadow-2xs">
                            <p class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-400">Breed</p>
                            <p class="text-sm font-extrabold text-slate-900 dark:text-white mt-0.5 truncate">{{ $pet->breed ?? '—' }}</p>
                        </div>
                        <div class="bg-white dark:bg-[#171923] p-4 rounded-2xl border border-slate-200 dark:border-white/[0.06] shadow-2xs">
                            <p class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-400">Color</p>
                            <p class="text-sm font-extrabold text-slate-900 dark:text-white mt-0.5">{{ $pet->color ?? '—' }}</p>
                        </div>
                        <div class="bg-white dark:bg-[#171923] p-4 rounded-2xl border border-slate-200 dark:border-white/[0.06] shadow-2xs">
                            <p class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-400">Gender</p>
                            <p class="text-sm font-extrabold text-slate-900 dark:text-white mt-0.5">{{ ucfirst($pet->gender ?? '—') }}</p>
                        </div>
                        <div class="bg-white dark:bg-[#171923] p-4 rounded-2xl border border-slate-200 dark:border-white/[0.06] shadow-2xs">
                            <p class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-400">Age Stage</p>
                            <p class="text-sm font-extrabold text-slate-900 dark:text-white mt-0.5">{{ $pet->age ?? '—' }}</p>
                        </div>
                        <div class="bg-white dark:bg-[#171923] p-4 rounded-2xl border border-slate-200 dark:border-white/[0.06] shadow-2xs">
                            <p class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-400">System ID</p>
                            <p class="text-sm font-extrabold text-slate-900 dark:text-white mt-0.5">Pet no. {{ $pet->id }}</p>
                        </div>
                        <div class="bg-white dark:bg-[#171923] p-4 rounded-2xl border border-slate-200 dark:border-white/[0.06] shadow-2xs col-span-2 sm:col-span-3">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                <div>
                                    <p class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-400">Registered In System By</p>
                                    <p class="text-sm font-extrabold text-slate-900 dark:text-white mt-0.5">
                                        {{ $pet->added_by_name ?: ($pet->addedBy?->name ?? 'CAWS Administration') }}
                                        @if($pet->addedBy?->staffProfile)
                                            <span class="text-xs font-semibold text-[#199CA4] dark:text-teal-400">({{ $pet->addedBy->staffProfile->position_title }})</span>
                                        @endif
                                    </p>
                                </div>
                                <span class="text-xs text-slate-400 font-medium">Logged on {{ $pet->created_at ? $pet->created_at->format('M d, Y') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="p-4 bg-white dark:bg-[#171923] rounded-2xl border border-slate-200 dark:border-white/[0.06] shadow-2xs">
                            <p class="text-xs font-extrabold text-slate-800 dark:text-white uppercase tracking-wider mb-1.5">
                                Medical Background
                            </p>
                            <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-200 font-bold leading-relaxed">{{ $pet->medical_history ?? 'No specific medical background recorded.' }}</p>
                        </div>

                        <div class="p-4 bg-white dark:bg-[#171923] rounded-2xl border border-slate-200 dark:border-white/[0.06] shadow-2xs">
                            <p class="text-xs font-extrabold text-slate-800 dark:text-white uppercase tracking-wider mb-2">
                                Temperament Traits
                            </p>
                            @if($pet->temperamentTags->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($pet->temperamentTags as $tag)
                                        <span class="px-3 py-1 bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-200 text-xs font-extrabold rounded-xl border border-slate-200 dark:border-white/[0.08] shadow-2xs">{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-slate-400 font-medium">No temperament tags specified for this pet.</span>
                            @endif
                        </div>

                        @if($pet->description)
                            <div class="p-4 bg-white dark:bg-[#171923] rounded-2xl border border-slate-200 dark:border-white/[0.06] shadow-2xs">
                                <p class="text-xs font-extrabold text-slate-800 dark:text-white uppercase tracking-wider mb-1.5">
                                    About This Pet
                                </p>
                                <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 font-medium leading-relaxed">{{ $pet->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Vaccination & Medical Logs --}}
            @if(in_array(Auth::user()?->role, ['admin', 'staff']))
                <div class="mt-10 pt-8 border-t border-slate-100 dark:border-white/[0.06]">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-sm border border-[#199CA4]/20 dark:border-[#41C1CB]/30">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-extrabold text-slate-900 dark:text-white">Vaccination & Clinical Log History</h2>
                                <p class="text-xs text-slate-400">Chronological healthcare and vaccination treatments</p>
                            </div>
                        </div>
                        <a href="{{ route('medical-logs.create-for-pet', $pet) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#199CA4] hover:bg-[#13787F] text-white rounded-xl text-xs font-extrabold shadow-xs transition cursor-pointer">+ Add Medical Entry</a>
                    </div>

                    @if($pet->medicalLogs->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($pet->medicalLogs as $log)
                                <div class="border border-slate-200/80 dark:border-white/[0.06] rounded-2xl p-4 bg-white dark:bg-[#171923] card-hover-effect flex flex-col justify-between shadow-2xs">
                                    <div>
                                        <div class="flex items-center justify-between gap-2 mb-2">
                                            <span class="text-xs font-extrabold text-slate-900 dark:text-white">{{ $log->date->format('F d, Y') }}</span>
                                            @php
                                                $categoryStyles = [
                                                    'vaccination' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60',
                                                    'deworming' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60',
                                                    'treatment' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60',
                                                    'checkup' => 'bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800/60',
                                                    'surgery' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60',
                                                    'injury_illness' => 'bg-orange-50 dark:bg-orange-950/40 text-orange-700 dark:text-orange-300 border-orange-200 dark:border-orange-800/60',
                                                ];
                                                $catStyle = $categoryStyles[$log->category] ?? 'bg-slate-50 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 border-slate-200 dark:border-white/[0.08]';
                                            @endphp
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border {{ $catStyle }}">{{ ucfirst(str_replace('_', ' ', $log->category)) }}</span>
                                        </div>
                                        @if($log->administered_by)
                                            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">By: <span class="font-bold text-slate-700 dark:text-slate-200">{{ $log->administered_by }}</span></p>
                                        @endif
                                        @if($log->notes)
                                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 italic">"{{ $log->notes }}"</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center justify-between pt-3 mt-3 border-t border-slate-100 dark:border-white/[0.06]">
                                        @if($log->next_due_date)
                                            <span class="text-[11px] font-extrabold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 px-2.5 py-0.5 rounded-md border border-amber-200 dark:border-amber-800/60">Next Due: {{ $log->next_due_date->format('M d, Y') }}</span>
                                        @else
                                            <span class="text-[11px] text-slate-400">No follow-up set</span>
                                        @endif
                                        <a href="{{ route('medical-logs.edit', $log) }}" class="text-xs font-extrabold text-slate-700 dark:text-slate-300 hover:text-[#199CA4] dark:hover:text-teal-400 hover:underline">Edit Log →</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center bg-slate-50/50 dark:bg-[#171923]/50 rounded-2xl border border-dashed border-slate-200 dark:border-white/[0.08]">
                            <svg class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-xs text-slate-400 font-medium">No medical log entries recorded for this pet yet.</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>