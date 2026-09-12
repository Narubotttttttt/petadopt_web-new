<x-app-layout>
    <div class="w-full py-4 sm:py-6 px-4 sm:px-6 lg:px-8 space-y-5 animate-fade-in"
         x-data="{
             events: @js($events->getCollection()->keyBy('id')),
             showDetailModal: false,
             selectedEvent: null,
             modalTab: 'record',
             showImageModal: false,
             previewImageUrl: '',
             previewImageTitle: '',
             openDetail(target, mode = null) {
                 const ev = (typeof target === 'string') ? (this.events[target] || null) : target;
                 if (!ev) return;
                 this.selectedEvent = Object.assign({}, ev);
                 if (mode) {
                     this.modalTab = mode;
                 } else {
                     this.modalTab = (ev.event_type === 'intake') ? 'pet' : 'record';
                 }
                 this.showDetailModal = true;
             },
             closeDetail() {
                 this.showDetailModal = false;
                 setTimeout(() => {
                     if (!this.showDetailModal) {
                         this.selectedEvent = null;
                     }
                 }, 200);
             },
             openImagePreview(url, title) {
                 if (!url) return;
                 this.previewImageUrl = url;
                 this.previewImageTitle = title || 'Document Preview';
                 this.showImageModal = true;
             },
             closeImagePreview() {
                 this.showImageModal = false;
                 setTimeout(() => {
                     if (!this.showImageModal) {
                         this.previewImageUrl = '';
                     }
                 }, 200);
             },
             updateScrollLock() {
                 if (this.showDetailModal || this.showImageModal) {
                     document.documentElement.classList.add('overflow-hidden');
                 } else {
                     document.documentElement.classList.remove('overflow-hidden');
                 }
             }
         }"
         x-init="
             $watch('showDetailModal', () => updateScrollLock());
             $watch('showImageModal', () => updateScrollLock());
         "
         @keydown.escape.window="showImageModal ? closeImagePreview() : closeDetail()">

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Pet History & Records
                    </h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#199CA4]/10 dark:bg-white/[0.06] text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20 dark:border-white/[0.08]">
                        {{ $events->total() }} Events Found
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Unified chronological records of pet intakes, clinical medical procedures, and adoption milestones.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('pets.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white text-xs font-bold shadow-xs transition-colors">
                    <span>+</span> Rescued Pet Intake
                </a>
                <a href="{{ route('medical-logs.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.1] text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200/80 dark:border-white/[0.08] shadow-xs transition-colors">
                    <span>+</span> Medical Log
                </a>
            </div>
        </div>

        {{-- Top KPI Metric Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            {{-- Total Rescued / Intakes --}}
            <div class="bg-white dark:bg-[#12141C] p-4 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex justify-between items-center">
                <div>
                    <p class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-500">Historical Rescues</p>
                    <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white my-0.5 tracking-tight">{{ $totalHistoricalPets }}</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#199CA4]"></span>
                        {{ $totalDogsCount }} Dogs • {{ $totalCatsCount }} Cats
                    </p>
                </div>
                <div class="p-2.5 bg-slate-100 dark:bg-white/[0.06] rounded-xl text-[#199CA4] dark:text-[#41C1CB] border border-slate-200 dark:border-white/[0.08]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
            </div>

            {{-- Finalized Adoptions --}}
            <div class="bg-white dark:bg-[#12141C] p-4 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex justify-between items-center">
                <div>
                    <p class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-500">Completed Adoptions</p>
                    <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white my-0.5 tracking-tight">{{ $totalHistoricalAdoptions }}</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Rehomed successfully
                    </p>
                </div>
                <div class="p-2.5 bg-slate-100 dark:bg-white/[0.06] rounded-xl text-emerald-600 dark:text-emerald-400 border border-slate-200 dark:border-white/[0.08]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            {{-- Medical Log History --}}
            <div class="bg-white dark:bg-[#12141C] p-4 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex justify-between items-center">
                <div>
                    <p class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-500">Clinical Procedures</p>
                    <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white my-0.5 tracking-tight">{{ $totalHistoricalMedicalLogs }}</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                        Vaccines, checkups & surgery
                    </p>
                </div>
                <div class="p-2.5 bg-slate-100 dark:bg-white/[0.06] rounded-xl text-indigo-600 dark:text-indigo-400 border border-slate-200 dark:border-white/[0.08]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>

            {{-- Currently Active In Shelter --}}
            <div class="bg-white dark:bg-[#12141C] p-4 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex justify-between items-center">
                <div>
                    <p class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-500">Active Shelter Residents</p>
                    <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white my-0.5 tracking-tight">{{ $totalActiveInShelter }}</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Available & In Progress
                    </p>
                </div>
                <div class="p-2.5 bg-slate-100 dark:bg-white/[0.06] rounded-xl text-amber-600 dark:text-amber-400 border border-slate-200 dark:border-white/[0.08]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Analytics & Charts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            {{-- Pet History Flow: Intakes vs Adoptions vs Medical Events (8 cols) --}}
            <div class="lg:col-span-8 bg-white dark:bg-[#12141C] rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] p-4 sm:p-5 flex flex-col justify-between">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white">Pet History & Intake Flow</h2>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#199CA4]/10 dark:bg-white/[0.06] text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20 dark:border-white/[0.08]">
                                Year {{ $selectedYear }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 dark:text-slate-400 mt-0.5">
                            Monthly comparison of Pet Intake Registrations, Adoptions, and Clinical Procedures.
                        </p>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <label for="history-year-select" class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Year:</label>
                        <select id="history-year-select" onchange="window.location.href='{{ route('pet-history.index') }}?year=' + this.value + '&event_type={{ $eventType }}&species={{ $species }}&q={{ urlencode($search) }}'"
                            class="bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-xs font-bold text-slate-700 dark:text-slate-200 rounded-lg px-2.5 py-1 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition cursor-pointer">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="h-60 sm:h-64 relative w-full">
                    <canvas id="petHistoryCombinedChart"></canvas>
                </div>

                {{-- Chart Custom Legend --}}
                <div class="flex flex-wrap items-center justify-center gap-4 pt-3 border-t border-slate-100 dark:border-white/[0.06] text-xs font-semibold">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-[#199CA4]"></span>
                        <span class="text-slate-600 dark:text-slate-300">Intakes ({{ array_sum($chartIntakes) }})</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <span class="text-slate-600 dark:text-slate-300">Adoptions ({{ array_sum($chartAdoptions) }})</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                        <span class="text-slate-600 dark:text-slate-300">Medical Logs ({{ array_sum($chartMedicals) }})</span>
                    </div>
                </div>
            </div>

            {{-- Breakdown Card: Procedure Breakdown & Species Stats (4 cols) --}}
            <div class="lg:col-span-4 bg-white dark:bg-[#12141C] rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] p-4 sm:p-5 flex flex-col justify-between">
                <div>
                    <h2 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white">Historical Breakdown</h2>
                    <p class="text-[11px] text-slate-400 dark:text-slate-400 mt-0.5">Clinical procedures and species summary.</p>
                </div>

                <div class="space-y-3 my-3">
                    @php
                        $categories = [
                            'vaccination' => ['name' => 'Vaccinations', 'color' => 'bg-indigo-500', 'bar' => 'bg-indigo-500'],
                            'deworming'   => ['name' => 'Deworming', 'color' => 'bg-purple-500', 'bar' => 'bg-purple-500'],
                            'checkup'     => ['name' => 'Checkups & Exams', 'color' => 'bg-emerald-500', 'bar' => 'bg-emerald-500'],
                            'treatment'   => ['name' => 'Treatments', 'color' => 'bg-amber-500', 'bar' => 'bg-amber-500'],
                            'surgery'     => ['name' => 'Surgeries & Spay', 'color' => 'bg-rose-500', 'bar' => 'bg-rose-500'],
                        ];
                        $maxProc = max(array_values($medicalCategoryBreakdown) ?: [1]);
                    @endphp

                    @foreach($categories as $key => $meta)
                        @php
                            $cnt = $medicalCategoryBreakdown[$key] ?? 0;
                            $pct = $maxProc > 0 ? round(($cnt / $maxProc) * 100) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs font-semibold mb-1">
                                <span class="text-slate-700 dark:text-slate-300">{{ $meta['name'] }}</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $cnt }}</span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 dark:bg-white/[0.06] rounded-full overflow-hidden">
                                <div class="h-full {{ $meta['bar'] }} rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06] flex items-center justify-between">
                    <div>
                        <div class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400">Species Ratio</div>
                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-0.5">
                            {{ $totalDogsCount }} Dogs : {{ $totalCatsCount }} Cats
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs font-bold text-[#199CA4] dark:text-[#41C1CB]">
                        <span>{{ $totalHistoricalPets > 0 ? round(($totalDogsCount / $totalHistoricalPets) * 100) : 0 }}% Canine</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Interactive Filter Bar --}}
        <div id="history-records" class="scroll-mt-20 bg-white dark:bg-[#12141C] rounded-2xl p-4 shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] space-y-3">
            <form id="historyFilterForm" method="GET" action="{{ route('pet-history.index') }}#history-records" class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                <input type="hidden" name="year" value="{{ $selectedYear }}">

                {{-- Event Type Filter Tabs --}}
                <div class="flex flex-wrap items-center gap-1.5">
                    @php
                        $eventTabs = [
                            'all'           => 'All History',
                            'intake'        => 'Intakes / Rescues',
                            'medical'       => 'Medical Logs',
                            'adoption'      => 'Adoptions',
                            'health_update' => 'Health Updates',
                        ];
                    @endphp
                    @foreach($eventTabs as $key => $label)
                        <button type="submit" name="event_type" value="{{ $key }}"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors cursor-pointer {{ $eventType === $key ? 'bg-[#199CA4] text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-white/[0.1]' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- Search & Dropdowns --}}
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Species Filter --}}
                    <select name="species" onchange="this.form.submit()"
                        class="bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-xs font-bold text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition cursor-pointer">
                        <option value="all" {{ $species === 'all' ? 'selected' : '' }}>All Species</option>
                        <option value="dog" {{ $species === 'dog' ? 'selected' : '' }}>Dogs Only</option>
                        <option value="cat" {{ $species === 'cat' ? 'selected' : '' }}>Cats Only</option>
                    </select>

                    {{-- Search Input --}}
                    <div class="relative">
                        <input type="text" name="q" value="{{ $search }}" placeholder="Search pet, breed, adopter..."
                            class="pl-8 pr-3 py-2 bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-xs w-48 sm:w-60 focus:outline-none focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] shadow-2xs transition" />
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <button type="submit" class="px-3.5 py-2 bg-[#199CA4] hover:bg-[#13787F] text-white font-bold rounded-xl text-xs shadow-xs transition-colors cursor-pointer">
                        Filter
                    </button>

                    @if($search || $eventType !== 'all' || $species !== 'all' || $petIdFilter)
                        <a href="{{ route('pet-history.index') }}#history-records" class="px-3 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Unified Historical Records Table & Timeline --}}
        <div class="bg-white dark:bg-[#12141C] rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] overflow-hidden">
            {{-- Desktop Table View (lg and up) --}}
            <div class="hidden lg:block overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-[#171923] border-b border-slate-200/80 dark:border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Date & Time</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Pet Information</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Event Category</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Activity Summary</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Recorded By / Meta</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                        @forelse($events as $event)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-[#181A24] transition-colors text-xs sm:text-sm">
                                {{-- Date --}}
                                <td class="px-5 py-3.5 whitespace-nowrap text-slate-700 dark:text-slate-300 font-semibold">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $event['date'] }}</div>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500">{{ $event['timestamp']->diffForHumans() }}</div>
                                </td>

                                {{-- Pet Info --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <button type="button" 
                                            @click="openDetail('{{ $event['id'] }}', 'pet')" 
                                            class="shrink-0 cursor-pointer focus:outline-none group"
                                            title="View Pet Details">
                                            @if(!empty($event['pet_photo']))
                                                <img src="{{ $event['pet_photo'] }}" alt="{{ $event['pet_name'] }}" class="w-9 h-9 rounded-xl object-cover shrink-0 border border-slate-200 dark:border-white/[0.08] group-hover:scale-105 transition" />
                                            @else
                                                <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-white/[0.06] text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-xs shrink-0 border border-slate-200 dark:border-white/[0.08] group-hover:scale-105 transition">
                                                    {{ strtoupper(substr($event['pet_name'], 0, 2)) }}
                                                </div>
                                            @endif
                                        </button>
                                        <div class="min-w-0">
                                            <button type="button" 
                                                @click="openDetail('{{ $event['id'] }}', 'pet')" 
                                                class="font-extrabold text-slate-900 dark:text-white hover:text-[#199CA4] dark:hover:text-[#41C1CB] hover:underline truncate block max-w-[160px] text-left cursor-pointer">
                                                {{ $event['pet_name'] }}
                                            </button>
                                            <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium truncate max-w-[160px]">
                                                {{ ucfirst($event['pet_type'] ?? '') }}{{ $event['pet_breed'] ? ' • ' . $event['pet_breed'] : '' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Event Category Badge --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $event['badge_class'] }}">
                                        {{ $event['badge_label'] }}
                                    </span>
                                </td>

                                {{-- Activity Summary --}}
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-slate-900 dark:text-white text-xs">{{ $event['title'] }}</div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-1 max-w-sm">{{ $event['description'] }}</p>
                                </td>

                                {{-- Meta / Logger --}}
                                <td class="px-5 py-3.5 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400 font-medium">
                                    {{ $event['meta'] }}
                                </td>

                                {{-- Action --}}
                                <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                    <button type="button"
                                        @click="openDetail('{{ $event['id'] }}')"
                                        class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.1] text-slate-700 dark:text-slate-200 font-bold transition text-xs border border-slate-200/80 dark:border-white/[0.08] cursor-pointer shadow-2xs">
                                        {{ $event['action_label'] }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500">
                                    <p class="font-bold text-sm text-slate-600 dark:text-slate-300">No history events found</p>
                                    <p class="text-xs text-slate-400 mt-1">Try adjusting your filters or date range.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile & Tablet Card View (below lg) --}}
            <div class="block lg:hidden divide-y divide-slate-100 dark:divide-white/[0.06]">
                @forelse($events as $event)
                    <div class="p-4 sm:p-5 space-y-3 hover:bg-slate-50/50 dark:hover:bg-[#181A24] transition-colors">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $event['badge_class'] }}">
                                {{ $event['badge_label'] }}
                            </span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold">
                                {{ $event['date'] }} <span class="text-slate-400 dark:text-slate-500 font-normal">({{ $event['timestamp']->diffForHumans() }})</span>
                            </span>
                        </div>

                        <div class="flex items-start gap-3">
                            <button type="button"
                                    @click="openDetail('{{ $event['id'] }}', 'pet')"
                                    class="shrink-0 cursor-pointer focus:outline-none group"
                                    title="View Pet Details">
                                @if(!empty($event['pet_photo']))
                                    <img src="{{ $event['pet_photo'] }}" alt="{{ $event['pet_name'] }}" class="w-12 h-12 rounded-xl object-cover shrink-0 border border-slate-200 dark:border-white/[0.08] group-hover:scale-105 transition" />
                                @else
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-white/[0.06] text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-xs shrink-0 border border-slate-200 dark:border-white/[0.08] group-hover:scale-105 transition">
                                        {{ strtoupper(substr($event['pet_name'], 0, 2)) }}
                                    </div>
                                @endif
                            </button>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <button type="button"
                                            @click="openDetail('{{ $event['id'] }}', 'pet')"
                                            class="font-extrabold text-slate-900 dark:text-white hover:text-[#199CA4] dark:hover:text-[#41C1CB] hover:underline truncate text-sm text-left cursor-pointer">
                                        {{ $event['pet_name'] }}
                                    </button>
                                    <span class="text-xs text-slate-400 dark:text-slate-500 font-medium truncate">
                                        {{ ucfirst($event['pet_type'] ?? '') }}{{ $event['pet_breed'] ? ' • ' . $event['pet_breed'] : '' }}
                                    </span>
                                </div>
                                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-1">{{ $event['title'] }}</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mt-0.5">{{ $event['description'] }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-white/[0.04] text-xs">
                            <span class="text-slate-400 dark:text-slate-500 truncate max-w-[50%]">{{ $event['meta'] }}</span>
                            <button type="button"
                                    @click="openDetail('{{ $event['id'] }}')"
                                    class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.1] text-slate-700 dark:text-slate-200 font-bold transition text-xs border border-slate-200/80 dark:border-white/[0.08] cursor-pointer shadow-2xs">
                                {{ $event['action_label'] }}
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 dark:text-slate-500">
                        <p class="font-bold text-sm text-slate-600 dark:text-slate-300">No history events found</p>
                        <p class="text-xs text-slate-400 mt-1">Try adjusting your filters or date range.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination footer --}}
            <div class="p-4 border-t border-slate-100 dark:border-white/[0.06] bg-slate-50/50 dark:bg-[#171923]">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                        Showing {{ $events->firstItem() ?? 0 }} to {{ $events->lastItem() ?? 0 }} of {{ $events->total() }} events
                    </p>
                    <div>
                        {{ $events->links() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Comprehensive Event & Record Details Modal (Stays on Pet History Page) --}}
        <div x-show="showDetailModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-2.5 sm:p-4 md:p-6"
             aria-labelledby="history-detail-modal-title" role="dialog" aria-modal="true">
            
            {{-- Backdrop --}}
            <div x-show="showDetailModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeDetail()"
                 class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>

            {{-- Modal Dialog (Responsive, Center-locked) --}}
            <div x-show="showDetailModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.stop
                 class="relative z-10 w-full max-w-full sm:max-w-2xl lg:max-w-3xl max-h-[92vh] sm:max-h-[90vh] flex flex-col rounded-2xl sm:rounded-3xl bg-white dark:bg-[#12141C] text-left shadow-2xl border border-slate-200/80 dark:border-white/[0.08] overflow-hidden">
                
                <template x-if="selectedEvent">
                    <div class="flex flex-col max-h-[92vh] sm:max-h-[90vh] overflow-hidden">
                        {{-- Modal Header --}}
                        <div class="shrink-0 px-4 sm:px-6 py-3.5 sm:py-4 bg-slate-50/90 dark:bg-[#171923] border-b border-slate-200/80 dark:border-white/[0.06] flex flex-col gap-3">
                            <div class="flex items-center justify-between gap-2.5">
                                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                                    {{-- Header Category Icon --}}
                                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center shrink-0 border"
                                         :class="{
                                             'bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] border-[#199CA4]/30': modalTab === 'pet' || selectedEvent.event_type === 'intake',
                                             'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/60': selectedEvent.event_type === 'adoption' && modalTab !== 'pet',
                                             'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800/60': selectedEvent.event_type === 'medical' && modalTab !== 'pet',
                                             'bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 border-teal-200 dark:border-teal-800/60': selectedEvent.event_type === 'health_update' && modalTab !== 'pet'
                                         }">
                                        {{-- Pet / Intake Icon --}}
                                        <template x-if="modalTab === 'pet' || selectedEvent.event_type === 'intake'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                            </svg>
                                        </template>
                                        {{-- Adoption Icon --}}
                                        <template x-if="selectedEvent.event_type === 'adoption' && modalTab !== 'pet'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </template>
                                        {{-- Medical Icon --}}
                                        <template x-if="selectedEvent.event_type === 'medical' && modalTab !== 'pet'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </template>
                                        {{-- Health Update Icon --}}
                                        <template x-if="selectedEvent.event_type === 'health_update' && modalTab !== 'pet'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                            </svg>
                                        </template>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                                            <template x-if="modalTab === 'pet' && selectedEvent.event_type !== 'intake'">
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold border bg-[#199CA4]/10 text-[#199CA4] dark:bg-teal-950/40 dark:text-[#41C1CB] border-[#199CA4]/25">
                                                    Pet Profile Details
                                                </span>
                                            </template>
                                            <template x-if="modalTab !== 'pet' || selectedEvent.event_type === 'intake'">
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold border" 
                                                      :class="selectedEvent.badge_class"
                                                      x-text="selectedEvent.badge_label"></span>
                                            </template>
                                            <span class="text-xs text-slate-400 dark:text-slate-500 font-medium" x-text="'• ' + selectedEvent.date"></span>
                                        </div>
                                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white mt-0.5 truncate" x-text="modalTab === 'pet' && selectedEvent.event_type !== 'intake' ? (selectedEvent.pet_name + ' Profile & Shelter Record') : selectedEvent.title"></h3>
                                    </div>
                                </div>

                                <button type="button" 
                                        @click="closeDetail()"
                                        aria-label="Close modal"
                                        class="w-8 h-8 rounded-xl shrink-0 bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.1] text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white flex items-center justify-center transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            {{-- In-Modal Interactive View Tab Switcher (When event is related to a pet) --}}
                            <template x-if="selectedEvent && selectedEvent.event_type !== 'intake'">
                                <div class="flex items-center gap-1.5 p-1 bg-slate-200/70 dark:bg-[#0C0D13] rounded-xl border border-slate-200/80 dark:border-white/[0.06] self-start">
                                    <button type="button"
                                            @click="modalTab = 'record'"
                                            :class="modalTab === 'record' ? 'bg-white dark:bg-[#1E2130] text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                                            class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                                        <span x-text="selectedEvent.event_type === 'adoption' ? 'Adoption Application' : (selectedEvent.event_type === 'medical' ? 'Medical Log' : 'Check-in Details')"></span>
                                    </button>
                                    <button type="button"
                                            @click="modalTab = 'pet'"
                                            :class="modalTab === 'pet' ? 'bg-white dark:bg-[#1E2130] text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                                            class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                                        <span x-text="selectedEvent.pet_name ? (selectedEvent.pet_name + '\'s Profile') : 'Pet Profile'"></span>
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#199CA4]"></span>
                                    </button>
                                </div>
                            </template>
                        </div>

                        {{-- Modal Body --}}
                        <div class="flex-1 p-4 sm:p-6 space-y-4 overflow-y-auto overscroll-contain">
                                
                                {{-- 1. PET DETAILS VIEW (When clicking Pet Info, Intake, or switching to Pet Profile tab) --}}
                                <template x-if="modalTab === 'pet' || selectedEvent.event_type === 'intake'">
                                    <div class="space-y-4">
                                        {{-- Optional Return to Record Banner --}}
                                        <template x-if="selectedEvent.event_type !== 'intake'">
                                            <div class="flex items-center justify-between p-2.5 px-3.5 rounded-xl bg-slate-100/80 dark:bg-[#171923] border border-slate-200/70 dark:border-white/[0.06]">
                                                <div class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-300">
                                                    <span>Viewing pet details for:</span>
                                                    <span class="font-bold text-slate-900 dark:text-white" x-text="selectedEvent.badge_label"></span>
                                                </div>
                                                <button type="button"
                                                        @click="modalTab = 'record'"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#199CA4]/10 hover:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] text-xs font-bold transition cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                                    <span>Back to Record</span>
                                                </button>
                                            </div>
                                        </template>

                                        {{-- Pet Hero Header Card --}}
                                        <div class="p-4 rounded-2xl bg-gradient-to-r from-slate-50 to-white dark:from-[#171923] dark:to-[#12141C] border border-slate-200/80 dark:border-white/[0.08] flex flex-col sm:flex-row items-center sm:items-start gap-4">
                                            <template x-if="selectedEvent.pet_photo">
                                                <div class="relative group cursor-pointer shrink-0" @click="openImagePreview(selectedEvent.pet_photo, selectedEvent.pet_name + ' Photo')">
                                                    <img :src="selectedEvent.pet_photo" :alt="selectedEvent.pet_name" class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border-2 border-white dark:border-[#12141C] shadow-sm hover:opacity-95 transition" />
                                                    <div class="absolute inset-0 bg-black/40 rounded-2xl opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-[10px] font-bold transition">
                                                        Enlarge
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="!selectedEvent.pet_photo">
                                                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-br from-[#199CA4]/20 to-[#199CA4]/10 dark:from-[#199CA4]/30 dark:to-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-black text-2xl shrink-0 border border-[#199CA4]/20"
                                                     x-text="selectedEvent.pet_name ? selectedEvent.pet_name.substring(0, 2).toUpperCase() : 'PT'">
                                                </div>
                                            </template>
                                            <div class="min-w-0 flex-1 text-center sm:text-left">
                                                <div class="flex items-center justify-center sm:justify-start gap-2 flex-wrap">
                                                    <h3 class="text-xl font-black text-slate-900 dark:text-white" x-text="selectedEvent.pet_name"></h3>
                                                    <template x-if="selectedEvent.pet_status">
                                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold uppercase tracking-wider bg-slate-100 dark:bg-white/[0.08] text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08]" x-text="selectedEvent.pet_status"></span>
                                                    </template>
                                                </div>
                                                <div class="flex items-center justify-center sm:justify-start gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300 mt-1.5 flex-wrap">
                                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-white/[0.05] text-slate-700 dark:text-slate-300" x-text="selectedEvent.pet_type ? (selectedEvent.pet_type.charAt(0).toUpperCase() + selectedEvent.pet_type.slice(1)) : 'Pet'"></span>
                                                    <template x-if="selectedEvent.pet_breed">
                                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-white/[0.05] text-slate-700 dark:text-slate-300" x-text="selectedEvent.pet_breed"></span>
                                                    </template>
                                                    <template x-if="selectedEvent.pet_gender">
                                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-white/[0.05] text-slate-700 dark:text-slate-300" x-text="selectedEvent.pet_gender"></span>
                                                    </template>
                                                </div>
                                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1.5 font-medium" x-text="'Shelter Catalog ID #' + (selectedEvent.pet_id || selectedEvent.raw_id)"></p>
                                            </div>
                                        </div>

                                        {{-- Pet Specification Matrix (6-box grid) --}}
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-2.5">
                                            {{-- Age --}}
                                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                                <div class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                    <span>Age</span>
                                                </div>
                                                <span class="text-xs font-extrabold text-slate-900 dark:text-white mt-1 block" x-text="selectedEvent.pet_age || 'Unknown'"></span>
                                            </div>

                                            {{-- Size / Weight --}}
                                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                                <div class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                                                    <span>Size / Weight</span>
                                                </div>
                                                <span class="text-xs font-extrabold text-slate-900 dark:text-white mt-1 block" x-text="(selectedEvent.pet_size || 'Medium') + (selectedEvent.pet_weight ? ' • ' + selectedEvent.pet_weight + ' kg' : '')"></span>
                                            </div>

                                            {{-- Coat Color --}}
                                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                                <div class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4 4 4 0 014-4h2.5M17 21a4 4 0 004-4 4 4 0 00-4-4H14.5M12 3v12"></path></svg>
                                                    <span>Coat Color</span>
                                                </div>
                                                <span class="text-xs font-extrabold text-slate-900 dark:text-white mt-1 block" x-text="selectedEvent.pet_color || 'N/A'"></span>
                                            </div>

                                            {{-- Health Standing --}}
                                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                                <div class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                                    <span>Health Status</span>
                                                </div>
                                                <span class="text-xs font-extrabold text-slate-900 dark:text-white mt-1 block" x-text="selectedEvent.pet_health_status || 'Good Condition'"></span>
                                            </div>

                                            {{-- Spayed / Neutered --}}
                                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                                <div class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                                    <span>Spayed / Neutered</span>
                                                </div>
                                                <span class="text-xs font-extrabold text-slate-900 dark:text-white mt-1 block" x-text="selectedEvent.pet_spayed_neutered || 'N/A'"></span>
                                            </div>

                                            {{-- Vaccinated --}}
                                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                                <div class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                                                    <span>Vaccinated</span>
                                                </div>
                                                <span class="text-xs font-extrabold text-slate-900 dark:text-white mt-1 block" x-text="selectedEvent.pet_vaccinated || 'N/A'"></span>
                                            </div>
                                        </div>

                                        {{-- Biography / Shelter Intake Notes --}}
                                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                            <div class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                <span>Background & Shelter Notes</span>
                                            </div>
                                            <p class="text-xs text-slate-700 dark:text-slate-200 leading-relaxed font-medium whitespace-pre-line break-words" x-text="selectedEvent.pet_description || selectedEvent.notes || 'No detailed background notes provided.'"></p>
                                        </div>

                                        {{-- Registration & Logger info --}}
                                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-500 dark:text-slate-400 font-medium">
                                            <div>
                                                <span class="font-bold text-slate-700 dark:text-slate-300">Shelter Admission:</span>
                                                <span x-text="selectedEvent.intake_date || selectedEvent.date"></span>
                                            </div>
                                            <div>
                                                <span class="font-bold text-slate-700 dark:text-slate-300">Registered By:</span>
                                                <span x-text="selectedEvent.recorded_by || 'Staff'"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- 2. ADOPTION APPLICATION VIEW --}}
                                <template x-if="selectedEvent.event_type === 'adoption' && modalTab !== 'pet'">
                                    <div class="space-y-4">
                                        {{-- Pet Overview Banner --}}
                                        <div class="p-3.5 rounded-2xl bg-gradient-to-r from-slate-50 to-white dark:from-[#171923] dark:to-[#12141C] border border-slate-200/70 dark:border-white/[0.06] flex items-center justify-between gap-3.5">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <template x-if="selectedEvent.pet_photo">
                                                    <img :src="selectedEvent.pet_photo" :alt="selectedEvent.pet_name" class="w-12 h-12 rounded-xl object-cover shrink-0 border border-slate-200 dark:border-white/[0.08]" />
                                                </template>
                                                <template x-if="!selectedEvent.pet_photo">
                                                    <div class="w-12 h-12 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-black text-xs shrink-0 border border-slate-200 dark:border-white/[0.08]"
                                                         x-text="selectedEvent.pet_name ? selectedEvent.pet_name.substring(0, 2).toUpperCase() : 'PT'">
                                                    </div>
                                                </template>
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-1.5">
                                                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white truncate" x-text="selectedEvent.pet_name"></h3>
                                                        <span class="text-[10px] font-bold text-slate-400" x-text="'(Pet #' + selectedEvent.pet_id + ')'"></span>
                                                    </div>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5 truncate">
                                                        <span x-text="selectedEvent.pet_type ? (selectedEvent.pet_type.charAt(0).toUpperCase() + selectedEvent.pet_type.slice(1)) : ''"></span>
                                                        <template x-if="selectedEvent.pet_breed">
                                                            <span x-text="' • ' + selectedEvent.pet_breed"></span>
                                                        </template>
                                                    </p>
                                                </div>
                                            </div>

                                            <button type="button" 
                                                    @click="modalTab = 'pet'" 
                                                    class="shrink-0 px-2.5 py-1 text-[11px] font-bold text-[#199CA4] dark:text-[#41C1CB] bg-[#199CA4]/10 hover:bg-[#199CA4]/20 rounded-lg transition cursor-pointer">
                                                View Pet Profile
                                            </button>
                                        </div>

                                        {{-- Applicant Information Matrix --}}
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-400">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                <span>Applicant Information</span>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-2.5">
                                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Full Name</span>
                                                    <span class="text-xs font-extrabold text-slate-900 dark:text-white mt-0.5 block" x-text="selectedEvent.applicant_name || 'N/A'"></span>
                                                </div>
                                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Email Address</span>
                                                    <span class="text-xs font-extrabold text-slate-900 dark:text-white mt-0.5 block truncate" x-text="selectedEvent.applicant_email || '—'"></span>
                                                </div>
                                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Contact Phone</span>
                                                    <span class="text-xs font-extrabold text-slate-900 dark:text-white mt-0.5 block" x-text="selectedEvent.applicant_phone || '—'"></span>
                                                </div>
                                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">ID Document Type</span>
                                                    <span class="text-xs font-extrabold text-slate-900 dark:text-white mt-0.5 block" x-text="selectedEvent.id_type || 'Government Issued ID'"></span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Screening Documents (Valid ID & Barangay Certificate) --}}
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-400">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                <span>Attached Verification Documents</span>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                                                {{-- Valid ID Box --}}
                                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06] flex flex-col justify-between space-y-2">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">1. Government Valid ID</span>
                                                        <template x-if="selectedEvent.valid_id_url">
                                                            <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">Uploaded</span>
                                                        </template>
                                                    </div>
                                                    <template x-if="selectedEvent.valid_id_url">
                                                        <div class="group relative rounded-xl overflow-hidden border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C]">
                                                            <img :src="selectedEvent.valid_id_url" alt="Valid ID" class="w-full h-32 sm:h-36 object-cover cursor-pointer hover:scale-105 transition duration-200" @click="openImagePreview(selectedEvent.valid_id_url, 'Valid Government ID')" />
                                                            <button type="button" @click="openImagePreview(selectedEvent.valid_id_url, 'Valid Government ID')" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-bold transition cursor-pointer">
                                                                Click to Enlarge
                                                            </button>
                                                        </div>
                                                    </template>
                                                    <template x-if="!selectedEvent.valid_id_url">
                                                        <div class="p-6 rounded-xl border border-dashed border-slate-200 dark:border-white/[0.08] text-center text-xs text-slate-400">
                                                            No Valid ID attached
                                                        </div>
                                                    </template>
                                                </div>

                                                {{-- Barangay Certificate Box --}}
                                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06] flex flex-col justify-between space-y-2">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">2. Barangay Certificate</span>
                                                        <template x-if="selectedEvent.barangay_certificate_url">
                                                            <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">Uploaded</span>
                                                        </template>
                                                    </div>
                                                    <template x-if="selectedEvent.barangay_certificate_url">
                                                        <div class="group relative rounded-xl overflow-hidden border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C]">
                                                            <img :src="selectedEvent.barangay_certificate_url" alt="Barangay Certificate" class="w-full h-32 sm:h-36 object-cover cursor-pointer hover:scale-105 transition duration-200" @click="openImagePreview(selectedEvent.barangay_certificate_url, 'Barangay Certificate')" />
                                                            <button type="button" @click="openImagePreview(selectedEvent.barangay_certificate_url, 'Barangay Certificate')" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-bold transition cursor-pointer">
                                                                Click to Enlarge
                                                            </button>
                                                        </div>
                                                    </template>
                                                    <template x-if="!selectedEvent.barangay_certificate_url">
                                                        <div class="p-6 rounded-xl border border-dashed border-slate-200 dark:border-white/[0.08] text-center text-xs text-slate-400">
                                                            No Barangay Certificate attached
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Application Questionnaire & Screening Responses --}}
                                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                            <div class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                                <span>Adoption Motivation & Responses</span>
                                            </div>
                                            <p class="text-xs text-slate-700 dark:text-slate-200 leading-relaxed font-medium whitespace-pre-line break-words" x-text="selectedEvent.message || selectedEvent.evaluation_notes || 'No questionnaire details provided.'"></p>
                                        </div>

                                        {{-- Official Digital Signatures & Evaluation Status --}}
                                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06] space-y-3">
                                            <div class="flex items-center justify-between text-xs font-medium text-slate-500 dark:text-slate-400 flex-wrap gap-2">
                                                <div>
                                                    <span class="font-bold text-slate-700 dark:text-slate-300">Evaluator / Staff:</span>
                                                    <span x-text="selectedEvent.evaluator_name || selectedEvent.staff_name || 'Shelter Staff'"></span>
                                                </div>
                                                <template x-if="selectedEvent.scheduled_at">
                                                    <div>
                                                        <span class="font-bold text-slate-700 dark:text-slate-300">Scheduled:</span>
                                                        <span x-text="selectedEvent.scheduled_at"></span>
                                                    </div>
                                                </template>
                                            </div>

                                            {{-- Signatures side-by-side --}}
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2 border-t border-slate-200/60 dark:border-white/[0.06]">
                                                {{-- Adopter Signature --}}
                                                <div class="p-3 rounded-lg bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.08]">
                                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Adopter Signature</span>
                                                    <template x-if="selectedEvent.signature_url">
                                                        <div>
                                                            <img :src="selectedEvent.signature_url" alt="Adopter Signature" class="h-10 object-contain mx-auto" />
                                                            <span class="text-[10px] text-slate-400 text-center block mt-1" x-text="'Signed ' + (selectedEvent.signed_at || '')"></span>
                                                        </div>
                                                    </template>
                                                    <template x-if="!selectedEvent.signature_url">
                                                        <span class="text-xs font-semibold text-slate-400 block py-2 text-center" x-text="selectedEvent.signed_at ? ('Signed ' + selectedEvent.signed_at) : 'Awaiting signature'"></span>
                                                    </template>
                                                </div>

                                                {{-- Staff Signature --}}
                                                <div class="p-3 rounded-lg bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.08]">
                                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Staff Signature</span>
                                                    <template x-if="selectedEvent.staff_signature_url">
                                                        <div>
                                                            <img :src="selectedEvent.staff_signature_url" alt="Staff Signature" class="h-10 object-contain mx-auto" />
                                                            <span class="text-[10px] text-slate-400 text-center block mt-1" x-text="(selectedEvent.staff_name || 'Staff') + ' • ' + (selectedEvent.staff_signed_at || '')"></span>
                                                        </div>
                                                    </template>
                                                    <template x-if="!selectedEvent.staff_signature_url">
                                                        <span class="text-xs font-semibold text-slate-400 block py-2 text-center" x-text="selectedEvent.staff_signed_at ? ('Signed ' + selectedEvent.staff_signed_at) : 'Shelter approved'"></span>
                                                    </template>
                                                </div>
                                            </div>

                                            <template x-if="selectedEvent.contract_url">
                                                <div class="pt-2 border-t border-slate-200/60 dark:border-white/[0.06] flex justify-end">
                                                    <a :href="selectedEvent.contract_url" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs font-bold hover:bg-emerald-100 transition">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                        <span>Preview Adoption Agreement PDF</span>
                                                    </a>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- 3. MEDICAL LOG DETAILS VIEW --}}
                                <template x-if="selectedEvent.event_type === 'medical' && modalTab !== 'pet'">
                                    <div class="space-y-4">
                                        {{-- Pet Quick Banner --}}
                                        <div class="p-3.5 rounded-2xl bg-gradient-to-r from-slate-50 to-white dark:from-[#171923] dark:to-[#12141C] border border-slate-200/70 dark:border-white/[0.06] flex items-center justify-between gap-3.5">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <template x-if="selectedEvent.pet_photo">
                                                    <img :src="selectedEvent.pet_photo" :alt="selectedEvent.pet_name" class="w-12 h-12 rounded-xl object-cover shrink-0 border border-slate-200 dark:border-white/[0.08]" />
                                                </template>
                                                <template x-if="!selectedEvent.pet_photo">
                                                    <div class="w-12 h-12 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-black text-xs shrink-0 border border-slate-200 dark:border-white/[0.08]"
                                                         x-text="selectedEvent.pet_name ? selectedEvent.pet_name.substring(0, 2).toUpperCase() : 'PT'">
                                                    </div>
                                                </template>
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-1.5">
                                                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white truncate" x-text="selectedEvent.pet_name"></h3>
                                                        <span class="text-[10px] font-bold text-slate-400" x-text="'(Pet #' + selectedEvent.pet_id + ')'"></span>
                                                    </div>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5 truncate">
                                                        <span x-text="selectedEvent.pet_type ? (selectedEvent.pet_type.charAt(0).toUpperCase() + selectedEvent.pet_type.slice(1)) : ''"></span>
                                                        <template x-if="selectedEvent.pet_breed">
                                                            <span x-text="' • ' + selectedEvent.pet_breed"></span>
                                                        </template>
                                                    </p>
                                                </div>
                                            </div>

                                            <button type="button" 
                                                    @click="modalTab = 'pet'" 
                                                    class="shrink-0 px-2.5 py-1 text-[11px] font-bold text-[#199CA4] dark:text-[#41C1CB] bg-[#199CA4]/10 hover:bg-[#199CA4]/20 rounded-lg transition cursor-pointer">
                                                View Pet Profile
                                            </button>
                                        </div>

                                        {{-- Clinical Procedure Specs Grid --}}
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Procedure</span>
                                                <span class="text-xs font-extrabold text-slate-900 dark:text-white mt-0.5 block" x-text="selectedEvent.category_name || selectedEvent.badge_label"></span>
                                            </div>
                                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Administered By</span>
                                                <span class="text-xs font-extrabold text-slate-900 dark:text-white mt-0.5 block" x-text="selectedEvent.administered_by || 'Veterinary Staff'"></span>
                                            </div>
                                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Date Performed</span>
                                                <span class="text-xs font-extrabold text-slate-900 dark:text-white mt-0.5 block" x-text="selectedEvent.date"></span>
                                            </div>
                                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Next Due Date</span>
                                                <template x-if="selectedEvent.next_due_date">
                                                    <span class="text-xs font-extrabold mt-0.5 block"
                                                          :class="selectedEvent.is_overdue ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'"
                                                          x-text="selectedEvent.next_due_date + (selectedEvent.is_overdue ? ' (Overdue)' : '')"></span>
                                                </template>
                                                <template x-if="!selectedEvent.next_due_date">
                                                    <span class="text-xs font-semibold text-slate-400 mt-0.5 block">None / Not required</span>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                            <div class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                <span>Clinical Remarks & Notes</span>
                                            </div>
                                            <p class="text-xs text-slate-700 dark:text-slate-200 leading-relaxed font-medium whitespace-pre-line break-words" x-text="selectedEvent.notes"></p>
                                        </div>

                                        <div class="flex items-center justify-between text-[11px] text-slate-400 px-1 font-medium">
                                            <span x-text="'Recorded by: ' + (selectedEvent.recorded_by || 'Staff')"></span>
                                            <span x-text="'Record ID: #' + (selectedEvent.raw_id || selectedEvent.id)"></span>
                                        </div>
                                    </div>
                                </template>

                                {{-- 4. HEALTH UPDATE CHECK-IN VIEW --}}
                                <template x-if="selectedEvent.event_type === 'health_update' && modalTab !== 'pet'">
                                    <div class="space-y-4">
                                        {{-- Pet Quick Banner --}}
                                        <div class="p-3.5 rounded-2xl bg-gradient-to-r from-slate-50 to-white dark:from-[#171923] dark:to-[#12141C] border border-slate-200/70 dark:border-white/[0.06] flex items-center justify-between gap-3.5">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <template x-if="selectedEvent.pet_photo">
                                                    <img :src="selectedEvent.pet_photo" :alt="selectedEvent.pet_name" class="w-12 h-12 rounded-xl object-cover shrink-0 border border-slate-200 dark:border-white/[0.08]" />
                                                </template>
                                                <template x-if="!selectedEvent.pet_photo">
                                                    <div class="w-12 h-12 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-black text-xs shrink-0 border border-slate-200 dark:border-white/[0.08]"
                                                         x-text="selectedEvent.pet_name ? selectedEvent.pet_name.substring(0, 2).toUpperCase() : 'PT'">
                                                    </div>
                                                </template>
                                                <div class="min-w-0">
                                                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white truncate" x-text="selectedEvent.pet_name"></h3>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5 truncate">
                                                        <span x-text="selectedEvent.pet_type ? (selectedEvent.pet_type.charAt(0).toUpperCase() + selectedEvent.pet_type.slice(1)) : ''"></span>
                                                        <template x-if="selectedEvent.pet_breed">
                                                            <span x-text="' • ' + selectedEvent.pet_breed"></span>
                                                        </template>
                                                    </p>
                                                </div>
                                            </div>

                                            <button type="button" 
                                                    @click="modalTab = 'pet'" 
                                                    class="shrink-0 px-2.5 py-1 text-[11px] font-bold text-[#199CA4] dark:text-[#41C1CB] bg-[#199CA4]/10 hover:bg-[#199CA4]/20 rounded-lg transition cursor-pointer">
                                                View Pet Profile
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Health Standing</span>
                                                <span class="text-xs font-extrabold text-slate-900 dark:text-white mt-0.5 block" x-text="selectedEvent.health_status"></span>
                                            </div>
                                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Current Weight</span>
                                                <span class="text-xs font-extrabold text-slate-900 dark:text-white mt-0.5 block" x-text="selectedEvent.weight || 'Not specified'"></span>
                                            </div>
                                        </div>

                                        <template x-if="selectedEvent.photo_url">
                                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06] space-y-2">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Check-in Photo</span>
                                                <div class="group relative rounded-xl overflow-hidden border border-slate-200 dark:border-white/[0.08] max-h-56 bg-white dark:bg-[#12141C]">
                                                    <img :src="selectedEvent.photo_url" alt="Health Check-in Photo" class="w-full h-full object-cover cursor-pointer hover:scale-105 transition" @click="openImagePreview(selectedEvent.photo_url, 'Health Check-in Photo')" />
                                                    <button type="button" @click="openImagePreview(selectedEvent.photo_url, 'Health Check-in Photo')" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-bold transition cursor-pointer">
                                                        Click to Enlarge
                                                    </button>
                                                </div>
                                            </div>
                                        </template>

                                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                            <div class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                                                <span>Adopter Observations</span>
                                            </div>
                                            <p class="text-xs text-slate-700 dark:text-slate-200 leading-relaxed font-medium whitespace-pre-line break-words" x-text="selectedEvent.notes"></p>
                                        </div>

                                        <div class="flex items-center justify-between text-[11px] text-slate-400 px-1 font-medium">
                                            <span x-text="'Submitted by: ' + (selectedEvent.submitted_by || 'Adopter')"></span>
                                            <span x-text="'Check-in Date: ' + selectedEvent.date"></span>
                                        </div>
                                    </div>
                                </template>

                            </div>

                            {{-- Modal Footer Actions --}}
                            <div class="shrink-0 px-4 sm:px-6 py-3.5 bg-slate-50/90 dark:bg-[#171923] border-t border-slate-200/80 dark:border-white/[0.06] flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-2.5 sm:gap-3">
                                <div>
                                    <template x-if="selectedEvent.action_url">
                                        <a :href="selectedEvent.action_url" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-[#199CA4] dark:text-slate-400 dark:hover:text-[#41C1CB] transition">
                                            <span>Open Full Page Record</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                        </a>
                                    </template>
                                </div>

                                <button type="button" 
                                        @click="closeDetail()"
                                        class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-200 dark:bg-white/[0.08] hover:bg-slate-300 dark:hover:bg-white/[0.12] text-slate-700 dark:text-slate-200 text-xs font-bold transition cursor-pointer text-center">
                                    Close
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
        </div>

        {{-- Document & Image Preview Lightbox Modal --}}
        <div x-show="showImageModal"
             x-cloak
             class="fixed inset-0 z-60 flex items-center justify-center p-3 sm:p-4 md:p-6"
             aria-labelledby="image-preview-modal-title" role="dialog" aria-modal="true">
            
            <div x-show="showImageModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeImagePreview()"
                 class="fixed inset-0 bg-black/85 backdrop-blur-xs transition-opacity"></div>

            <div x-show="showImageModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.stop
                 class="relative z-10 w-full max-w-3xl max-h-[92vh] flex flex-col overflow-hidden rounded-2xl bg-[#12141C] text-left shadow-2xl border border-white/[0.1]">
                
                <div class="shrink-0 px-4 py-3 bg-[#171923] border-b border-white/[0.06] flex items-center justify-between">
                    <span class="text-xs font-bold text-white truncate max-w-[80%]" x-text="previewImageTitle"></span>
                    <button type="button" @click="closeImagePreview()" aria-label="Close preview" class="text-slate-400 hover:text-white text-lg leading-none cursor-pointer p-1">&times;</button>
                </div>

                <div class="flex-1 p-3 sm:p-4 flex items-center justify-center bg-black/60 overflow-auto overscroll-contain">
                    <img :src="previewImageUrl" :alt="previewImageTitle" class="max-w-full max-h-[75vh] object-contain rounded-xl shadow-lg select-none" />
                </div>
            </div>
        </div>
    </div>

    {{-- Chart.js Script for Pet History Flow --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('petHistoryCombinedChart');
            if (!ctx) return;

            const months = @json($chartMonths);
            const intakes = @json($chartIntakes);
            const adoptions = @json($chartAdoptions);
            const medicals = @json($chartMedicals);

            function getChartColors() {
                const isDark = document.documentElement.classList.contains('dark');
                return {
                    gridColor: isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(226, 232, 240, 0.6)',
                    tickColor: isDark ? '#64748b' : '#94a3b8',
                    pointBorderColor: isDark ? '#12141C' : '#ffffff',
                };
            }

            const colors = getChartColors();

            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: 'Intakes',
                            data: intakes,
                            backgroundColor: 'rgba(25, 156, 164, 0.85)',
                            borderColor: '#199CA4',
                            borderWidth: 1.5,
                            borderRadius: 6,
                            barPercentage: 0.6,
                            categoryPercentage: 0.8,
                        },
                        {
                            label: 'Adoptions',
                            data: adoptions,
                            backgroundColor: 'rgba(16, 185, 129, 0.85)',
                            borderColor: '#10B981',
                            borderWidth: 1.5,
                            borderRadius: 6,
                            barPercentage: 0.6,
                            categoryPercentage: 0.8,
                        },
                        {
                            label: 'Medical Logs',
                            data: medicals,
                            backgroundColor: 'rgba(99, 102, 241, 0.85)',
                            borderColor: '#6366F1',
                            borderWidth: 1.5,
                            borderRadius: 6,
                            barPercentage: 0.6,
                            categoryPercentage: 0.8,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: '#12141C',
                            borderColor: 'rgba(255,255,255,0.1)',
                            borderWidth: 1,
                            titleColor: '#ffffff',
                            bodyColor: '#e2e8f0',
                            titleFont: { size: 11, weight: 'bold' },
                            bodyFont: { size: 11, weight: 'bold' },
                            padding: 8,
                            cornerRadius: 10,
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                            ticks: {
                                color: colors.tickColor,
                                font: { size: 10, weight: '600' }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0,
                                color: colors.tickColor,
                                font: { size: 10, weight: '600' }
                            },
                            grid: {
                                color: colors.gridColor,
                                strokeDash: [3, 3],
                            },
                            border: {
                                display: false
                            }
                        }
                    }
                }
            });

            window.addEventListener('theme-changed', function () {
                const c = getChartColors();
                chart.options.scales.x.ticks.color = c.tickColor;
                chart.options.scales.y.ticks.color = c.tickColor;
                chart.options.scales.y.grid.color = c.gridColor;
                chart.update();
            });

            // Preserve scroll position on filter tab clicks & search
            const filterForm = document.getElementById('historyFilterForm');
            if (filterForm) {
                filterForm.addEventListener('submit', function () {
                    sessionStorage.setItem('pet_history_scroll_pos', window.scrollY.toString());
                });
            }

            const savedScroll = sessionStorage.getItem('pet_history_scroll_pos');
            if (savedScroll !== null) {
                sessionStorage.removeItem('pet_history_scroll_pos');
                window.scrollTo({
                    top: parseInt(savedScroll, 10),
                    behavior: 'instant'
                });
            }
        });
    </script>
</x-app-layout>
