<x-app-layout>
    <div class="w-full max-w-5xl mx-auto py-5 sm:py-8 px-4 sm:px-6 lg:px-8 animate-fade-in space-y-6">

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

            $latestVaccine = $pet->medicalLogs->where('category', 'vaccination')->first();
            $latestDeworming = $pet->medicalLogs->where('category', 'deworming')->first();
            $today = now()->startOfDay();
            $vaccineDueDate = $latestVaccine?->next_due_date ? $latestVaccine->next_due_date->copy()->startOfDay() : null;
            $isVaccineOverdue = $vaccineDueDate && $vaccineDueDate->lt($today);
            $isVaccineDueSoon = $vaccineDueDate && !$isVaccineOverdue && $vaccineDueDate->diffInDays($today) <= 30;
        @endphp

        {{-- Minimal Top Navigation Bar --}}
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('pets.index') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Directory</span>
            </a>

            <div class="flex items-center gap-2">
                @if(in_array(Auth::user()?->role, ['admin', 'staff']))
                    <a href="{{ route('pets.edit', $pet) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-[#171923] text-xs font-bold transition shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span>Edit</span>
                    </a>
                @endif
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-extrabold border {{ $style }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $dot }} animate-pulse"></span>
                    {{ ucfirst($pet->status ?? 'Unknown') }}
                </span>
            </div>
        </div>

        {{-- Hero Pet Profile Card --}}
        <div class="bg-white dark:bg-[#12141C] rounded-2xl sm:rounded-3xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-12">
                
                {{-- Pet Photo Column --}}
                <div class="md:col-span-5 relative bg-slate-100 dark:bg-[#0C0D13]">
                    <div class="aspect-4/3 sm:aspect-square md:h-full w-full relative overflow-hidden flex items-center justify-center">
                        @if($pet->photo_path)
                            <img src="{{ asset('storage/'.$pet->photo_path) }}" alt="{{ $pet->name ?: ('Pet no. ' . $pet->id) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full min-h-[220px] flex flex-col items-center justify-center text-slate-300 dark:text-slate-600 p-8">
                                <svg class="w-16 h-16 mb-2" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                <span class="text-xs font-semibold text-slate-400">No photo uploaded</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Pet Overview & Key Details Column --}}
                <div class="md:col-span-7 p-5 sm:p-7 flex flex-col justify-between space-y-5">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap mb-1.5">
                            <span class="text-xs font-mono font-bold text-[#199CA4] dark:text-[#41C1CB] bg-[#199CA4]/10 dark:bg-[#199CA4]/20 px-2 py-0.5 rounded-md">
                                Pet no. {{ $pet->id }}
                            </span>
                            <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">
                                Added {{ $pet->created_at ? $pet->created_at->diffForHumans() : 'recently' }}
                            </span>
                        </div>

                        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            {{ $pet->name ?: ('Pet no. ' . $pet->id) }}
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium mt-1">
                            {{ $pet->breed ?? 'Mixed Breed' }} &bull; {{ ucfirst($pet->color ?? 'Unknown color') }}
                        </p>

                        {{-- Minimal 4-Metric Grid --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-4">
                            <div class="bg-slate-50/80 dark:bg-[#171923] p-2.5 rounded-xl border border-slate-100 dark:border-white/[0.04]">
                                <span class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 block">Species</span>
                                <span class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 mt-0.5 block">{{ ucfirst($pet->type ?? '—') }}</span>
                            </div>
                            <div class="bg-slate-50/80 dark:bg-[#171923] p-2.5 rounded-xl border border-slate-100 dark:border-white/[0.04]">
                                <span class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 block">Gender</span>
                                <span class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 mt-0.5 block">{{ ucfirst($pet->gender ?? '—') }}</span>
                            </div>
                            <div class="bg-slate-50/80 dark:bg-[#171923] p-2.5 rounded-xl border border-slate-100 dark:border-white/[0.04]">
                                <span class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 block">Age</span>
                                <span class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 mt-0.5 block">{{ $pet->age ?? '—' }}</span>
                            </div>
                            <div class="bg-slate-50/80 dark:bg-[#171923] p-2.5 rounded-xl border border-slate-100 dark:border-white/[0.04]">
                                <span class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 block">Color</span>
                                <span class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 mt-0.5 block truncate">{{ $pet->color ?? '—' }}</span>
                            </div>
                        </div>

                        {{-- Temperament Tags --}}
                        @if($pet->temperamentTags->isNotEmpty())
                            <div class="mt-4 flex items-center gap-1.5 flex-wrap">
                                @foreach($pet->temperamentTags as $tag)
                                    <span class="px-2.5 py-0.5 bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200/60 dark:border-white/[0.06]">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Registered By & Action Row --}}
                    <div class="pt-3 border-t border-slate-100 dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                            Registered by <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $pet->added_by_name ?: ($pet->addedBy?->name ?? 'CAWS Administration') }}</span>
                            @if($pet->addedBy?->staffProfile)
                                <span class="text-[#199CA4] dark:text-[#41C1CB]">({{ $pet->addedBy->staffProfile->position_title }})</span>
                            @endif
                            @if($pet->created_at) &bull; {{ $pet->created_at->format('M d, Y') }} @endif
                        </p>

                        @unless(in_array(Auth::user()?->role, ['admin', 'staff']))
                            @if($pet->status === 'available')
                                <a href="{{ route('adoption-applications.create', $pet) }}" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-[#199CA4] hover:bg-[#13787F] text-white rounded-xl text-xs sm:text-sm font-extrabold shadow-xs transition-all">
                                    Apply to Adopt
                                </a>
                            @endif
                        @endunless
                    </div>
                </div>
            </div>
        </div>

        {{-- About Section (Only if description exists) --}}
        @if($pet->description)
            <div class="bg-white dark:bg-[#12141C] p-5 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] space-y-2">
                <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    About {{ $pet->name ?: ('Pet no. ' . $pet->id) }}
                </h2>
                <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 font-medium leading-relaxed">
                    {{ $pet->description }}
                </p>
            </div>
        @endif

        {{-- Clinical & Vaccination History Card --}}
        <div class="bg-white dark:bg-[#12141C] p-5 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-white/[0.06]">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-xs">
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

            {{-- Minimal 2-Column Health Summary --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="p-3.5 rounded-xl border {{ $latestVaccine ? ($isVaccineOverdue ? 'bg-rose-50/50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/40' : ($isVaccineDueSoon ? 'bg-amber-50/50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-900/40' : 'bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/40')) : 'bg-slate-50/80 dark:bg-[#171923] border-slate-100 dark:border-white/[0.04]' }}">
                    <span class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 block">Vaccination</span>
                    <div class="flex items-center justify-between gap-2 mt-1">
                        <span class="text-xs font-extrabold {{ $latestVaccine ? ($isVaccineOverdue ? 'text-rose-600 dark:text-rose-400' : ($isVaccineDueSoon ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400')) : 'text-slate-500' }}">
                            {{ $latestVaccine ? ($isVaccineOverdue ? 'Booster Overdue' : ($isVaccineDueSoon ? 'Booster Due Soon' : 'Up to Date')) : 'No vaccination logged' }}
                        </span>
                        @if($latestVaccine && $latestVaccine->next_due_date)
                            <span class="text-[11px] font-mono font-bold text-slate-500 dark:text-slate-400">Due: {{ $latestVaccine->next_due_date->format('M d, Y') }}</span>
                        @endif
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50/80 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.04]">
                    <span class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 block">Deworming</span>
                    <div class="flex items-center justify-between gap-2 mt-1">
                        <span class="text-xs font-extrabold {{ $latestDeworming ? 'text-purple-600 dark:text-purple-400' : 'text-slate-500' }}">
                            {{ $latestDeworming ? 'Administered' : 'No deworming logged' }}
                        </span>
                        @if($latestDeworming)
                            <span class="text-[11px] font-mono font-bold text-slate-500 dark:text-slate-400">Last: {{ $latestDeworming->date->format('M d, Y') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Medical Notes --}}
            @if($pet->medical_history)
                <div class="p-3 rounded-xl bg-slate-50/60 dark:bg-white/[0.02] border border-slate-100 dark:border-white/[0.04] text-xs text-slate-700 dark:text-slate-300">
                    <span class="font-bold text-slate-900 dark:text-white">Medical Notes:</span> {{ $pet->medical_history }}
                </div>
            @endif

            {{-- Recent Clinical Entries --}}
            @if($pet->medicalLogs->isNotEmpty())
                <div class="pt-2 space-y-2">
                    <span class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 block">Recent Clinical Entries</span>
                    <div class="divide-y divide-slate-100 dark:divide-white/[0.06] border border-slate-100 dark:border-white/[0.04] rounded-xl overflow-hidden">
                        @foreach($pet->medicalLogs->take(4) as $log)
                            <div class="p-2.5 flex items-center justify-between text-xs hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $log->date->format('M d, Y') }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border
                                        {{ $log->category === 'vaccination' ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60' : '' }}
                                        {{ $log->category === 'deworming' ? 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800/60' : '' }}
                                        {{ !in_array($log->category, ['vaccination', 'deworming']) ? 'bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 border-slate-200 dark:border-white/[0.08]' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $log->category)) }}
                                    </span>
                                </div>
                                <span class="text-[11px] text-slate-400 truncate max-w-[140px] sm:max-w-none text-right">
                                    {{ $log->administered_by ?: ($log->creator?->name ?? 'Staff') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                    @if($pet->medicalLogs->count() > 4)
                        <div class="text-center pt-1">
                            <a href="{{ route('medical-logs.index', ['q' => $pet->id]) }}" class="text-[11px] font-bold text-[#199CA4] hover:underline">
                                View all {{ $pet->medicalLogs->count() }} records in Medical Logs
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>

    </div>
</x-app-layout>