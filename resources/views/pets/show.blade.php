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
                                <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                            </div>
                        </div>
                    @else
                        <div class="w-14 h-14 rounded-2xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-extrabold text-2xl border border-[#199CA4]/20 dark:border-[#41C1CB]/30 shrink-0">
                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
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

                    <a href="{{ route('pets.index') }}" class="px-4 py-2.5 border border-slate-200 dark:border-white/[0.08] text-slate-600 dark:text-slate-300 rounded-xl text-xs sm:text-sm font-bold hover:bg-slate-50 dark:hover:bg-white/[0.06] transition">Back to Catalog</a>
                </div>
            </div>

            {{-- Pet Media & Details Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-8">
                <div class="md:col-span-1">
                    <div class="w-full h-72 bg-slate-50 dark:bg-[#0C0D13] rounded-2xl overflow-hidden flex items-center justify-center border border-slate-200 dark:border-white/[0.08] shadow-2xs group relative">
                        @if($pet->photo_path)
                            <img src="{{ asset('storage/'.$pet->photo_path) }}" alt="Pet no. {{ $pet->id }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="hidden w-full h-full bg-slate-100 dark:bg-[#0C0D13] flex-col items-center justify-center text-center p-6">
                                <svg class="w-12 h-12 text-slate-400 dark:text-slate-600 mb-2" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                <span class="text-xs text-slate-500 font-bold">Pet no. {{ $pet->id }}</span>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center text-center p-6">
                                <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-2" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
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
                        {{-- Clinical & Vaccination History Card --}}
                        <div class="p-5 bg-white dark:bg-[#171923] rounded-2xl border border-slate-200 dark:border-white/[0.06] shadow-2xs space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-white/[0.06]">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-lg bg-[#199CA4]/10 text-[#199CA4] flex items-center justify-center font-bold text-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                    </div>
                                    <h2 class="text-xs font-extrabold text-slate-800 dark:text-white uppercase tracking-wider">
                                        Immunization & Clinical Records
                                    </h2>
                                </div>
                                @if(in_array(Auth::user()?->role, ['admin', 'staff']))
                                    <a href="{{ route('medical-logs.create-for-pet', $pet->id) }}" class="inline-flex items-center gap-1 text-xs font-bold text-[#199CA4] hover:text-[#13787F] dark:text-[#41C1CB] hover:underline">
                                        <span>+ Add Record</span>
                                    </a>
                                @endif
                            </div>

                            @if($pet->medical_history)
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/60 dark:border-white/[0.04] text-xs text-slate-700 dark:text-slate-300">
                                    <span class="font-bold text-slate-900 dark:text-white">Medical Notes:</span> {{ $pet->medical_history }}
                                </div>
                            @endif

                            @php
                                $latestVaccine = $pet->medicalLogs->where('category', 'vaccination')->first();
                                $latestDeworming = $pet->medicalLogs->where('category', 'deworming')->first();
                                $today = now()->startOfDay();
                                $vaccineDueDate = $latestVaccine?->next_due_date ? $latestVaccine->next_due_date->copy()->startOfDay() : null;
                                $isVaccineOverdue = $vaccineDueDate && $vaccineDueDate->lt($today);
                                $isVaccineDueSoon = $vaccineDueDate && !$isVaccineOverdue && $vaccineDueDate->diffInDays($today) <= 30;
                            @endphp

                            {{-- Vaccine Status Highlights --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="p-3 rounded-xl border {{ $latestVaccine ? ($isVaccineOverdue ? 'bg-rose-50/50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/40' : ($isVaccineDueSoon ? 'bg-amber-50/50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-900/40' : 'bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/40')) : 'bg-slate-50 dark:bg-white/[0.03] border-slate-200 dark:border-white/[0.06]' }}">
                                    <p class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400">Vaccination Status</p>
                                    @if($latestVaccine)
                                        <div class="flex items-center gap-1.5 mt-1">
                                            @if($isVaccineOverdue)
                                                <span class="text-xs font-black text-rose-600 dark:text-rose-400">Booster Overdue</span>
                                            @elseif($isVaccineDueSoon)
                                                <span class="text-xs font-black text-amber-600 dark:text-amber-400">Booster Due Soon</span>
                                            @else
                                                <span class="text-xs font-black text-emerald-600 dark:text-emerald-400">Up to Date</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            Next booster: <span class="font-bold">{{ $latestVaccine->next_due_date ? $latestVaccine->next_due_date->format('M d, Y') : 'None scheduled' }}</span>
                                        </p>
                                    @else
                                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mt-1">No vaccination logged yet</p>
                                    @endif
                                </div>

                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.06]">
                                    <p class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400">Deworming Status</p>
                                    @if($latestDeworming)
                                        <p class="text-xs font-black text-purple-600 dark:text-purple-400 mt-1">Administered</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            Last dose: <span class="font-bold">{{ $latestDeworming->date->format('M d, Y') }}</span>
                                        </p>
                                    @else
                                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mt-1">No deworming logged</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Detailed Log History Timeline --}}
                            @if($pet->medicalLogs->isNotEmpty())
                                <div class="space-y-2 pt-2">
                                    <p class="text-[11px] uppercase tracking-wider font-bold text-slate-400">Past Clinical Entries</p>
                                    <div class="divide-y divide-slate-100 dark:divide-white/[0.06] border border-slate-100 dark:border-white/[0.06] rounded-xl overflow-hidden">
                                        @foreach($pet->medicalLogs->take(5) as $log)
                                            <div class="p-2.5 hover:bg-slate-50/60 dark:hover:bg-white/[0.02] flex items-center justify-between text-xs transition">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-slate-800 dark:text-white">{{ $log->date->format('M d, Y') }}</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border
                                                        {{ $log->category === 'vaccination' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border-indigo-200' : '' }}
                                                        {{ $log->category === 'deworming' ? 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border-purple-200' : '' }}
                                                        {{ !in_array($log->category, ['vaccination', 'deworming']) ? 'bg-slate-100 text-slate-700 dark:bg-white/[0.06] dark:text-slate-300 border-slate-200' : '' }}">
                                                        {{ ucfirst(str_replace('_', ' ', $log->category)) }}
                                                    </span>
                                                </div>
                                                <div class="text-right text-[11px] text-slate-500 dark:text-slate-400">
                                                    By: <span class="font-semibold">{{ $log->administered_by ?: ($log->creator?->name ?? 'Staff') }}</span>
                                                    @if($log->next_due_date)
                                                        • Due: <span class="font-semibold">{{ $log->next_due_date->format('M d, Y') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($pet->medicalLogs->count() > 5)
                                        <div class="text-center pt-1">
                                            <a href="{{ route('medical-logs.index', ['q' => $pet->id]) }}" class="text-[11px] font-bold text-[#199CA4] hover:underline">
                                                View all {{ $pet->medicalLogs->count() }} clinical records
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endif
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
        </div>
    </div>
</x-app-layout>