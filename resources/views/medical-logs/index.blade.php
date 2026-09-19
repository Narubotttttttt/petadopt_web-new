<x-app-layout>
    <div class="w-full py-6 sm:py-8 px-4 sm:px-6 lg:px-8 animate-fade-in space-y-5 sm:space-y-6">
        {{-- Header Bar --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Medical Logs</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Record and manage shelter vaccinations and routine dewormings.</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3 w-full sm:w-auto">
                <form method="GET" action="{{ route('medical-logs.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
                    @if(request('filter') && request('filter') !== 'all')
                        <input type="hidden" name="filter" value="{{ request('filter') }}" />
                    @endif
                    <div class="relative flex-1 sm:w-64">
                        <input 
                            name="q" 
                            value="{{ old('q', request('q')) }}" 
                            placeholder="Search pet, breed, or vet..." 
                            class="w-full pl-9 {{ request('q') ? 'pr-8' : 'pr-4' }} py-2.5 bg-white dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] shadow-2xs transition font-medium" 
                        />
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        @if(request('q'))
                            <a href="{{ route('medical-logs.index', ['filter' => request('filter', 'all')]) }}" title="Clear search" class="absolute right-2.5 top-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </a>
                        @endif
                    </div>
                    <button type="submit" class="px-4 sm:px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.06] dark:hover:bg-white/[0.1] text-slate-700 dark:text-slate-200 font-bold rounded-xl text-xs sm:text-sm border border-slate-200 dark:border-white/[0.08] shadow-2xs transition-colors cursor-pointer shrink-0">Search</button>
                </form>
                <a href="{{ route('medical-logs.create') }}" class="inline-flex items-center justify-center gap-1.5 px-4 sm:px-5 py-2.5 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white text-xs sm:text-sm font-bold shadow-xs transition-all duration-200 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Record Medical Log</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="px-4 py-3 bg-[#199CA4]/10 dark:bg-[#199CA4]/15 border border-[#199CA4]/25 text-[#146970] dark:text-[#41C1CB] rounded-2xl flex items-center gap-2 text-sm font-semibold shadow-2xs">
                <svg class="w-5 h-5 text-[#199CA4] dark:text-[#41C1CB] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Clinical KPI Metric Summary Cards (3 cards) --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            {{-- Total Clinical Records --}}
            <a href="{{ route('medical-logs.index', ['filter' => 'all']) }}" class="group block p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#12141C] border {{ $filter === 'all' ? 'border-[#199CA4] ring-2 ring-[#199CA4]/20' : 'border-slate-200/80 dark:border-white/[0.07]' }} hover:border-[#199CA4]/50 shadow-2xs transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-xs font-extrabold uppercase tracking-wider text-[#199CA4] dark:text-[#41C1CB] truncate">Total Logs</span>
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center border border-[#199CA4]/20 shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    </div>
                </div>
                <div class="mt-2 sm:mt-3 flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ $totalLogsCount }}</span>
                    <span class="text-[10px] sm:text-xs font-semibold text-slate-400 truncate">entries</span>
                </div>
                <p class="mt-0.5 sm:mt-1 text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 font-medium truncate">All clinical entries</p>
            </a>

            {{-- Core Vaccinations Card --}}
            <a href="{{ route('medical-logs.index', ['filter' => 'vaccination']) }}" class="group block p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#12141C] border {{ $filter === 'vaccination' ? 'border-[#199CA4] ring-2 ring-[#199CA4]/20' : 'border-slate-200/80 dark:border-white/[0.07]' }} hover:border-[#199CA4]/50 shadow-2xs transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate">Vaccinations</span>
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/15 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center border border-[#199CA4]/20 shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                </div>
                <div class="mt-2 sm:mt-3 flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ $vaccineCount }}</span>
                    <span class="text-[10px] sm:text-xs font-semibold text-slate-400 truncate">{{ Str::plural('dose', $vaccineCount) }}</span>
                </div>
                <p class="mt-0.5 sm:mt-1 text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 font-medium truncate">Immunization records</p>
            </a>

            {{-- Total Routine Dewormings --}}
            <a href="{{ route('medical-logs.index', ['filter' => 'deworming']) }}" class="group block p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#12141C] border {{ $filter === 'deworming' ? 'border-[#4F46E5] ring-2 ring-[#4F46E5]/20' : 'border-slate-200/80 dark:border-white/[0.07]' }} hover:border-[#4F46E5]/50 shadow-2xs transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate">Dewormings</span>
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-[#4F46E5]/10 dark:bg-[#6366F1]/15 text-[#4F46E5] dark:text-[#818CF8] flex items-center justify-center border border-[#4F46E5]/20 dark:border-[#6366F1]/25 shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    </div>
                </div>
                <div class="mt-2 sm:mt-3 flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ $dewormingCount }}</span>
                    <span class="text-[10px] sm:text-xs font-semibold text-slate-400 truncate">{{ Str::plural('dose', $dewormingCount) }}</span>
                </div>
                <p class="mt-0.5 sm:mt-1 text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 font-medium truncate">Parasite prevention</p>
            </a>
        </div>

        {{-- Interactive Filter Navigation Chips --}}
        <div class="flex items-center gap-2 overflow-x-auto scrollbar-none pb-1">
            <a href="{{ route('medical-logs.index', ['filter' => 'all', 'q' => request('q')]) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all border whitespace-nowrap shrink-0 {{ $filter === 'all' ? 'bg-[#199CA4] text-white border-[#199CA4] shadow-xs' : 'bg-white dark:bg-[#12141C] text-slate-600 dark:text-slate-300 border-slate-200/80 dark:border-white/[0.07] hover:bg-slate-50 dark:hover:bg-white/[0.04]' }}">
                All Records ({{ $totalLogsCount }})
            </a>
            <a href="{{ route('medical-logs.index', ['filter' => 'vaccination', 'q' => request('q')]) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all border whitespace-nowrap shrink-0 {{ $filter === 'vaccination' ? 'bg-[#199CA4] text-white border-[#199CA4] shadow-xs' : 'bg-white dark:bg-[#12141C] text-slate-600 dark:text-slate-300 border-slate-200/80 dark:border-white/[0.07] hover:bg-slate-50 dark:hover:bg-white/[0.04]' }}">
                Vaccinations ({{ $vaccineCount }})
            </a>
            <a href="{{ route('medical-logs.index', ['filter' => 'deworming', 'q' => request('q')]) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all border whitespace-nowrap shrink-0 {{ $filter === 'deworming' ? 'bg-[#4F46E5] text-white border-[#4F46E5] shadow-xs' : 'bg-white dark:bg-[#12141C] text-slate-600 dark:text-slate-300 border-slate-200/80 dark:border-white/[0.07] hover:bg-slate-50 dark:hover:bg-white/[0.04]' }}">
                Deworming ({{ $dewormingCount }})
            </a>
        </div>

        {{-- Main Container Card --}}
        <div class="bg-white dark:bg-[#12141C] rounded-2xl sm:rounded-3xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] overflow-hidden">
            
            {{-- Card Header Bar --}}
            <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/50 dark:bg-[#171923]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-sm shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white">Clinical & Immunization Registry</h2>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Registry of verified vaccinations and routine parasite prevention</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-xs font-semibold">
                    <span class="text-[11px] text-slate-600 dark:text-slate-300 font-extrabold bg-slate-100 dark:bg-white/[0.06] px-3 py-1 rounded-full border border-slate-200 dark:border-white/[0.08]">
                        {{ $logs->total() }} Total Records
                    </span>
                </div>
            </div>

            {{-- 1. Mobile Feed Presentation (< md screens) --}}
            <div class="block md:hidden divide-y divide-slate-100 dark:divide-white/[0.06]">
                @forelse($logs as $log)
                    @php
                        if ($log->category === 'vaccination') {
                            $badgeStyle = 'bg-[#199CA4]/10 dark:bg-[#199CA4]/15 text-[#15838B] dark:text-[#41C1CB] border-[#199CA4]/25';
                            $dotStyle = 'bg-[#199CA4] dark:bg-[#41C1CB]';
                        } elseif ($log->category === 'deworming') {
                            $badgeStyle = 'bg-[#4F46E5]/10 dark:bg-[#6366F1]/15 text-[#4338CA] dark:text-[#A5B4FC] border-[#4F46E5]/25 dark:border-[#6366F1]/30';
                            $dotStyle = 'bg-[#4F46E5] dark:bg-[#818CF8]';
                        } else {
                            $badgeStyle = 'bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 border-slate-200/80 dark:border-white/[0.08]';
                            $dotStyle = null;
                        }
                    @endphp
                    <div class="p-4 space-y-3 hover:bg-slate-50/50 dark:hover:bg-[#161822] transition-colors">
                        {{-- Top Row: Pet Photo, Pet Name & Category Badge --}}
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                @if($log->pet?->photo_path)
                                    <img src="{{ asset('storage/'.ltrim($log->pet->photo_path, '/')) }}" alt="{{ $log->pet->name }}" class="w-9 h-9 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shrink-0" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="hidden w-9 h-9 rounded-xl bg-[#199CA4]/10 text-[#199CA4] items-center justify-center font-bold text-xs shrink-0">#{{ $log->pet->id }}</div>
                                @else
                                    <div class="w-9 h-9 rounded-xl bg-[#199CA4]/10 text-[#199CA4] flex items-center justify-center font-bold text-xs shrink-0">#{{ $log->pet_id }}</div>
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('pets.show', $log->pet_id) }}" class="font-extrabold text-slate-900 dark:text-white hover:text-[#199CA4] truncate block text-xs sm:text-sm">
                                        {{ $log->pet->name ?? ('Pet no. ' . $log->pet_id) }}
                                    </a>
                                    <p class="text-[10px] text-slate-400 truncate">{{ ucfirst($log->pet->type ?? 'Pet') }} &bull; {{ $log->pet->breed ?? 'Mixed' }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border shrink-0 {{ $badgeStyle }}">
                                @if($dotStyle)
                                    <span class="w-1.5 h-1.5 rounded-full {{ $dotStyle }}"></span>
                                @endif
                                <span>{{ ucfirst(str_replace('_', ' ', $log->category)) }}</span>
                            </span>
                        </div>

                        {{-- Details Grid (3 columns, no due date indicators) --}}
                        <div class="grid grid-cols-3 gap-2 text-[11px] bg-slate-50 dark:bg-white/[0.02] p-2.5 rounded-xl border border-slate-100 dark:border-white/[0.04]">
                            <div>
                                <span class="text-slate-400 block text-[10px] font-medium">Date Given:</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $log->date->format('M d, Y') }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] font-medium">Medication:</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200 truncate block">{{ $log->vaccine_name ?: ($log->category === 'vaccination' ? 'Core Vaccine' : 'Routine Dose') }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] font-medium">Staff:</span>
                                <span class="font-medium text-slate-600 dark:text-slate-400 truncate block">{{ $log->administered_by ?: ($log->creator?->name ?? 'Staff') }}</span>
                            </div>
                        </div>

                        {{-- Touch-Friendly Actions Row --}}
                        <div class="flex items-center gap-2 pt-1">
                            <a href="{{ route('medical-logs.edit', $log) }}"
                                class="flex-1 py-2 text-center rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#1D1F2C] font-bold text-xs transition shadow-2xs">
                                Edit
                            </a>
                            @if(Auth::user()?->role === 'admin')
                                <button 
                                    type="button" 
                                    data-title='Delete "<strong>Medical Log for {{ $log->pet->name ?? ('Pet no. ' . $log->pet_id) }}</strong>"?'
                                    data-message="This action cannot be undone."
                                    data-action="{{ route('medical-logs.destroy', $log) }}"
                                    data-confirm-text="Delete"
                                    data-method="DELETE"
                                    onclick="openConfirmModal(this.dataset)"
                                    class="px-3.5 py-2 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 font-bold hover:bg-rose-100 dark:hover:bg-rose-900/60 transition text-xs cursor-pointer"
                                >
                                    Delete
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 sm:p-12 text-center text-slate-400 dark:text-slate-500">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-white/[0.04] text-slate-400 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <p class="font-extrabold text-sm text-slate-700 dark:text-slate-200 mb-1">No medical log records found</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500">Try switching filter tabs or click "Record Medical Log" to record a new clinical entry.</p>
                    </div>
                @endforelse
            </div>

            {{-- 2. Desktop High-Density Data Table (>= md screens) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-[#171923] border-b border-slate-200/80 dark:border-white/[0.06]">
                        <tr>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Pet Information</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Medication / Vaccine</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Date Administered</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Administered By</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                        @forelse($logs as $log)
                            @php
                                if ($log->category === 'vaccination') {
                                    $badgeStyle = 'bg-[#199CA4]/10 dark:bg-[#199CA4]/15 text-[#15838B] dark:text-[#41C1CB] border-[#199CA4]/25';
                                    $dotStyle = 'bg-[#199CA4] dark:bg-[#41C1CB]';
                                    $fallbackMed = 'Core Vaccination';
                                } elseif ($log->category === 'deworming') {
                                    $badgeStyle = 'bg-[#4F46E5]/10 dark:bg-[#6366F1]/15 text-[#4338CA] dark:text-[#A5B4FC] border-[#4F46E5]/25 dark:border-[#6366F1]/30';
                                    $dotStyle = 'bg-[#4F46E5] dark:bg-[#818CF8]';
                                    $fallbackMed = 'Routine Deworming';
                                } else {
                                    $badgeStyle = 'bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 border-slate-200/80 dark:border-white/[0.08]';
                                    $dotStyle = null;
                                    $fallbackMed = 'General Treatment';
                                }
                            @endphp
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-[#181A24] transition-colors text-xs sm:text-sm">
                                {{-- 1. Pet Information --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        @if($log->pet?->photo_path)
                                            <img src="{{ asset('storage/'.ltrim($log->pet->photo_path, '/')) }}" alt="{{ $log->pet->name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shrink-0" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="hidden w-10 h-10 rounded-xl bg-[#199CA4]/10 text-[#199CA4] items-center justify-center font-bold text-xs shrink-0">#{{ $log->pet->id }}</div>
                                        @else
                                            <div class="w-10 h-10 rounded-xl bg-[#199CA4]/10 text-[#199CA4] flex items-center justify-center font-bold text-xs shrink-0">#{{ $log->pet_id }}</div>
                                        @endif
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <a href="{{ route('pets.show', $log->pet_id) }}" class="font-extrabold text-slate-900 dark:text-white hover:text-[#199CA4] dark:hover:text-[#41C1CB] hover:underline">
                                                    {{ $log->pet->name ?? ('Pet no. ' . $log->pet_id) }}
                                                </a>
                                                <span class="text-[10px] font-mono text-slate-400">#PET-{{ str_pad((string)$log->pet_id, 4, '0', STR_PAD_LEFT) }}</span>
                                            </div>
                                            <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium mt-0.5">
                                                {{ ucfirst($log->pet->type ?? 'Pet') }} &bull; {{ $log->pet->breed ?? 'Mixed Breed' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- 2. Medication / Vaccine --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold border w-fit {{ $badgeStyle }}">
                                            @if($dotStyle)
                                                <span class="w-1.5 h-1.5 rounded-full {{ $dotStyle }}"></span>
                                            @endif
                                            <span>{{ ucfirst(str_replace('_', ' ', $log->category)) }}</span>
                                        </span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200 text-xs">
                                            {{ $log->vaccine_name ?: $fallbackMed }}
                                        </span>
                                    </div>
                                </td>

                                {{-- 3. Date Administered --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 block">{{ $log->date->format('M d, Y') }}</span>
                                    <span class="text-[11px] text-slate-400 font-medium">{{ $log->date->diffForHumans() }}</span>
                                </td>

                                {{-- 4. Administered By --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-slate-100 dark:bg-white/[0.08] text-slate-600 dark:text-slate-300 flex items-center justify-center font-bold text-[10px] shrink-0">
                                            {{ strtoupper(substr($log->administered_by ?: ($log->creator?->name ?? 'S'), 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="font-medium text-slate-700 dark:text-slate-300 block text-xs">
                                                {{ $log->administered_by ?: ($log->creator?->name ?? 'Shelter Staff') }}
                                            </span>
                                            <span class="text-[10px] text-slate-400 font-medium">Authorized Staff</span>
                                        </div>
                                    </div>
                                </td>

                                {{-- 5. Actions --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('medical-logs.edit', $log) }}" 
                                            class="px-3.5 py-1.5 text-center rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#1D1F2C] font-bold transition text-xs shadow-2xs inline-block">
                                            Edit
                                        </a>
                                        @if(Auth::user()?->role === 'admin')
                                            <button 
                                                type="button" 
                                                data-title='Delete "<strong>Medical Log for {{ $log->pet->name ?? ('Pet no. ' . $log->pet_id) }}</strong>"?'
                                                data-message="This action cannot be undone."
                                                data-action="{{ route('medical-logs.destroy', $log) }}"
                                                data-confirm-text="Delete"
                                                data-method="DELETE"
                                                onclick="openConfirmModal(this.dataset)"
                                                class="px-3.5 py-1.5 text-center rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-[#1D1F2C] font-bold transition text-xs cursor-pointer"
                                            >
                                                Delete
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <p class="font-bold text-sm text-slate-700 dark:text-slate-200">No medical log records found</p>
                                    <p class="text-xs text-slate-400 mt-1">Try switching filter tabs or click "Record Medical Log" to record a new clinical entry.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer Pagination --}}
            <div class="p-4 border-t border-slate-100 dark:border-white/[0.06] bg-slate-50/50 dark:bg-[#171923]">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                        Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} entries
                    </p>
                    <div class="w-full sm:w-auto flex justify-center">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>