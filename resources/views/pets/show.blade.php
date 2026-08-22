<x-app-layout>
    <div class="w-full py-8 px-4 sm:px-6 lg:px-8 animate-fade-in space-y-8">
        
        {{-- Pet Header Card --}}
        <div class="bg-white dark:bg-[#0e1d20] p-8 sm:p-10 rounded-3xl shadow-card border border-[#199CA4]/20 dark:border-slate-800 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#199CA4] via-[#41C1CB] to-[#14838B]"></div>

            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between pb-8 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#F0FBFB] to-[#D6F4F6] dark:from-[#133036] dark:to-[#17454d] text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-extrabold text-2xl shadow-2xs ring-1 ring-[#199CA4]/20 dark:ring-[#41C1CB]/30">
                        🐾
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">{{ ucfirst($pet->type ?? 'Pet') }} #{{ $pet->id }}</h1>
                            @php
                                $statusStyles = [
                                    'available' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                    'pending' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                    'adopted' => 'bg-[#199CA4]/10 dark:bg-[#199CA4]/25 text-[#199CA4] dark:text-[#41C1CB] border-[#199CA4]/20 dark:border-[#41C1CB]/30',
                                ];
                                $dotColors = [
                                    'available' => 'bg-emerald-500',
                                    'pending' => 'bg-amber-500',
                                    'adopted' => 'bg-[#199CA4]',
                                ];
                                $style = $statusStyles[$pet->status] ?? 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700';
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
                    @if(in_array(Auth::user()->role, ['admin', 'staff']))
                        <a href="{{ route('pets.edit', $pet) }}" class="px-5 py-2.5 bg-gradient-to-r from-[#199CA4] to-[#14838B] hover:from-[#146970] hover:to-[#12585e] text-white rounded-xl text-xs sm:text-sm font-extrabold shadow-md shadow-[#199CA4]/25 hover:shadow-lg transition-all">Edit Profile</a>
                    @endif

                    @unless(in_array(Auth::user()->role, ['admin', 'staff']))
                        @if($pet->status === 'available')
                            <a href="{{ route('adoption-applications.create', $pet) }}" class="px-5 py-2.5 bg-gradient-to-r from-[#199CA4] to-[#14838B] hover:from-[#146970] hover:to-[#12585e] text-white rounded-xl text-xs sm:text-sm font-extrabold shadow-md shadow-[#199CA4]/25 hover:shadow-lg transition-all">Apply to adopt</a>
                        @endif
                    @endunless

                    <a href="{{ route('pets.index') }}" class="px-4 py-2.5 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs sm:text-sm font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition">← Back to Catalog</a>
                </div>
            </div>

            {{-- Pet Media & Details Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-8">
                <div class="md:col-span-1">
                    <div class="w-full h-72 bg-slate-50 dark:bg-[#12272b] rounded-2xl overflow-hidden flex items-center justify-center border border-[#199CA4]/20 dark:border-slate-700 shadow-2xs group relative">
                        @if($pet->photo_path)
                            <img src="{{ asset('storage/'.$pet->photo_path) }}" alt="Pet #{{ $pet->id }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="hidden w-full h-full bg-[#F0FBFB] dark:bg-[#12272b] items-center justify-center text-center p-6">
                                <span class="text-6xl block mb-2">🐾</span>
                                <span class="text-xs text-[#199CA4] dark:text-[#41C1CB] font-bold">Pet #{{ $pet->id }}</span>
                            </div>
                        @else
                            <div class="text-center p-6">
                                <span class="text-6xl block mb-2">🐾</span>
                                <span class="text-xs text-slate-400 font-semibold">No photo uploaded</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="md:col-span-2 space-y-6">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3.5">
                        <div class="bg-white dark:bg-[#12272b] p-4 rounded-2xl border border-[#199CA4]/20 dark:border-slate-800 shadow-2xs">
                            <p class="text-[10px] uppercase tracking-wider font-extrabold text-[#199CA4] dark:text-[#41C1CB]">Species</p>
                            <p class="text-sm font-extrabold text-slate-800 dark:text-white mt-0.5">{{ ucfirst($pet->type ?? '—') }}</p>
                        </div>
                        <div class="bg-white dark:bg-[#12272b] p-4 rounded-2xl border border-[#199CA4]/20 dark:border-slate-800 shadow-2xs">
                            <p class="text-[10px] uppercase tracking-wider font-extrabold text-[#199CA4] dark:text-[#41C1CB]">Breed</p>
                            <p class="text-sm font-extrabold text-slate-800 dark:text-white mt-0.5 truncate">{{ $pet->breed ?? '—' }}</p>
                        </div>
                        <div class="bg-white dark:bg-[#12272b] p-4 rounded-2xl border border-[#199CA4]/20 dark:border-slate-800 shadow-2xs">
                            <p class="text-[10px] uppercase tracking-wider font-extrabold text-[#199CA4] dark:text-[#41C1CB]">Color</p>
                            <p class="text-sm font-extrabold text-slate-800 dark:text-white mt-0.5">{{ $pet->color ?? '—' }}</p>
                        </div>
                        <div class="bg-white dark:bg-[#12272b] p-4 rounded-2xl border border-[#199CA4]/20 dark:border-slate-800 shadow-2xs">
                            <p class="text-[10px] uppercase tracking-wider font-extrabold text-[#199CA4] dark:text-[#41C1CB]">Gender</p>
                            <p class="text-sm font-extrabold text-slate-800 dark:text-white mt-0.5">{{ ucfirst($pet->gender ?? '—') }}</p>
                        </div>
                        <div class="bg-white dark:bg-[#12272b] p-4 rounded-2xl border border-[#199CA4]/20 dark:border-slate-800 shadow-2xs">
                            <p class="text-[10px] uppercase tracking-wider font-extrabold text-[#199CA4] dark:text-[#41C1CB]">Age Stage</p>
                            <p class="text-sm font-extrabold text-slate-800 dark:text-white mt-0.5">{{ $pet->age ?? '—' }}</p>
                        </div>
                        <div class="bg-white dark:bg-[#12272b] p-4 rounded-2xl border border-[#199CA4]/20 dark:border-slate-800 shadow-2xs">
                            <p class="text-[10px] uppercase tracking-wider font-extrabold text-[#199CA4] dark:text-[#41C1CB]">System ID</p>
                            <p class="text-sm font-extrabold text-[#14838B] dark:text-[#41C1CB] mt-0.5">Pet #{{ $pet->id }}</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="p-4 bg-white dark:bg-[#12272b] rounded-2xl border border-[#199CA4]/20 dark:border-slate-800 shadow-2xs">
                            <p class="text-xs font-extrabold text-[#199CA4] dark:text-[#41C1CB] uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                                <span>🩺</span> Medical Background
                            </p>
                            <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-200 font-bold leading-relaxed">{{ $pet->medical_history ?? 'No specific medical background recorded.' }}</p>
                        </div>

                        <div class="p-4 bg-white dark:bg-[#12272b] rounded-2xl border border-[#199CA4]/20 dark:border-slate-800 shadow-2xs">
                            <p class="text-xs font-extrabold text-[#199CA4] dark:text-[#41C1CB] uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <span>🏷️</span> Temperament Traits
                            </p>
                            @if($pet->temperamentTags->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($pet->temperamentTags as $tag)
                                        <span class="px-3 py-1 bg-[#199CA4]/10 dark:bg-[#199CA4]/25 text-[#199CA4] dark:text-[#41C1CB] text-xs font-extrabold rounded-xl border border-[#199CA4]/20 dark:border-[#41C1CB]/30 shadow-2xs">{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-slate-400 font-medium">No temperament tags specified for this pet.</span>
                            @endif
                        </div>

                        @if($pet->description)
                            <div class="p-4 bg-white dark:bg-[#12272b] rounded-2xl border border-[#199CA4]/20 dark:border-slate-800 shadow-2xs">
                                <p class="text-xs font-extrabold text-[#199CA4] dark:text-[#41C1CB] uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                                    <span>📝</span> About This Pet
                                </p>
                                <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 font-medium leading-relaxed">{{ $pet->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Vaccination & Medical Logs --}}
            @if(in_array(Auth::user()->role, ['admin', 'staff']))
                <div class="mt-10 pt-8 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-sm">🩺</span>
                            <div>
                                <h2 class="text-base font-extrabold text-slate-800 dark:text-white">Vaccination & Clinical Log History</h2>
                                <p class="text-xs text-slate-400">Chronological healthcare and vaccination treatments</p>
                            </div>
                        </div>
                        <a href="{{ route('medical-logs.create-for-pet', $pet) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-[#199CA4] to-[#14838B] text-white rounded-xl text-xs font-extrabold shadow-md shadow-[#199CA4]/20 hover:shadow-lg transition cursor-pointer">+ Add Medical Entry</a>
                    </div>

                    @if($pet->medicalLogs->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($pet->medicalLogs as $log)
                                <div class="border border-[#199CA4]/20 dark:border-slate-800 rounded-2xl p-4 bg-white dark:bg-[#12272b] card-hover-effect flex flex-col justify-between shadow-2xs">
                                    <div>
                                        <div class="flex items-center justify-between gap-2 mb-2">
                                            <span class="text-xs font-extrabold text-slate-800 dark:text-white">{{ $log->date->format('F d, Y') }}</span>
                                            @php
                                                $categoryStyles = [
                                                    'vaccination' => 'bg-[#199CA4]/10 dark:bg-[#199CA4]/25 text-[#199CA4] dark:text-[#41C1CB] border-[#199CA4]/20 dark:border-[#41C1CB]/30',
                                                    'deworming' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                                    'treatment' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                                    'checkup' => 'bg-[#199CA4]/10 dark:bg-[#199CA4]/25 text-[#14838B] dark:text-[#41C1CB] border-[#199CA4]/20 dark:border-[#41C1CB]/30',
                                                    'surgery' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                                                    'injury_illness' => 'bg-orange-50 dark:bg-orange-950/60 text-orange-700 dark:text-orange-300 border-orange-200 dark:border-orange-800',
                                                ];
                                                $catStyle = $categoryStyles[$log->category] ?? 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700';
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
                                    <div class="flex items-center justify-between pt-3 mt-3 border-t border-slate-100 dark:border-slate-800">
                                        @if($log->next_due_date)
                                            <span class="text-[11px] font-extrabold text-[#14838B] dark:text-[#41C1CB] bg-[#F0FBFB] dark:bg-[#142e33] px-2.5 py-0.5 rounded-md border border-[#199CA4]/20 dark:border-[#41C1CB]/30">Next Due: {{ $log->next_due_date->format('M d, Y') }}</span>
                                        @else
                                            <span class="text-[11px] text-slate-400">No follow-up set</span>
                                        @endif
                                        <a href="{{ route('medical-logs.edit', $log) }}" class="text-xs font-extrabold text-[#199CA4] dark:text-[#41C1CB] hover:underline">Edit Log →</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center bg-slate-50/50 dark:bg-[#12272b]/50 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                            <span class="text-3xl block mb-1">🩺</span>
                            <p class="text-xs text-slate-400 font-medium">No medical log entries recorded for this pet yet.</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>