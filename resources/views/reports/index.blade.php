<x-app-layout>
    <div class="w-full py-5 sm:py-7 px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in print:p-0 print:space-y-4">

        {{-- Top Header & Quick Action Bar --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 print:hidden">
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        System Reports & Analytics
                    </h1>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/30 dark:border-[#199CA4]/40 shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#199CA4] animate-pulse"></span>
                        {{ $dateRangeLabel }}
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-3xl">
                    Official shelter performance metrics, intake registry logs, adoption statistics, and adopter compliance tracking.
                </p>
            </div>

            {{-- Export & Print Buttons --}}
            <div class="flex items-center flex-wrap gap-2.5">
                {{-- Export PDF --}}
                <a href="{{ route('reports.export.pdf', array_merge(request()->query(), ['type' => $reportType])) }}" target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-white text-xs font-extrabold shadow-sm shadow-rose-600/20 hover:shadow-rose-600/30 transition-all duration-200 cursor-pointer active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Export PDF</span>
                </a>

                {{-- Export CSV --}}
                <a href="{{ route('reports.export.csv', array_merge(request()->query(), ['type' => $reportType])) }}" download
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white text-xs font-extrabold shadow-sm shadow-emerald-600/20 hover:shadow-emerald-600/30 transition-all duration-200 cursor-pointer active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Export CSV</span>
                </a>

                {{-- Print View --}}
                <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-[#12141C] hover:bg-slate-50 dark:hover:bg-white/[0.06] text-slate-700 dark:text-slate-200 text-xs font-extrabold border border-slate-200/90 dark:border-white/[0.1] shadow-xs hover:border-slate-300 dark:hover:border-white/[0.2] transition-all duration-200 cursor-pointer active:scale-95">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Print</span>
                </button>
            </div>
        </div>

        {{-- Print-Only Formal Letterhead Header --}}
        <div class="hidden print:block border-b-2 border-slate-800 pb-4 mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-black text-slate-900 uppercase tracking-tight">CDO Animal Welfare Society Inc.</h1>
                    <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wide mt-0.5">
                        {{ ucfirst($reportType) }} Report
                    </h2>
                    <p class="text-xs text-slate-600 mt-1">Reporting Period: {{ $dateRangeLabel }} | Generated: {{ now()->format('F d, Y h:i A') }}</p>
                </div>
            </div>
        </div>

        {{-- Segmented Category Navigation Tabs --}}
        <div class="bg-slate-100/80 dark:bg-[#0C0D13]/80 p-1.5 rounded-2xl border border-slate-200/80 dark:border-white/[0.06] overflow-x-auto no-scrollbar print:hidden shadow-2xs">
            @php
                $tabs = [
                    'overview'   => ['label' => 'Executive Overview', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                    'adoptions'  => ['label' => 'Adoption Reports', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    'intakes'    => ['label' => 'Pet Intake Registry', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                    'medical'    => ['label' => 'Medical Procedures', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    'compliance' => ['label' => 'Adopter Compliance', 'icon' => 'M16 11V7a4 4 0 10-8 0v4M5 11h14v10H5V11z'],
                ];
            @endphp

            <div class="flex items-center gap-1.5 min-w-max">
                @foreach($tabs as $key => $tab)
                    @php
                        $isActive = ($reportType === $key);
                        $queryParam = array_merge(request()->query(), ['type' => $key]);
                        unset($queryParam['page']);
                    @endphp
                    <a href="{{ route('reports.index', $queryParam) }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-extrabold transition-all duration-200 whitespace-nowrap {{ $isActive ? 'bg-[#199CA4] text-white shadow-md shadow-[#199CA4]/25' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-white/[0.05]' }}">
                        <svg class="w-4 h-4 shrink-0 {{ $isActive ? 'text-white' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $tab['icon'] }}" />
                        </svg>
                        <span>{{ $tab['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Filter Toolbar Card --}}
        <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] print:hidden">
            <form method="GET" action="{{ route('reports.index') }}" id="reportFilterForm" class="space-y-4">
                <input type="hidden" name="type" value="{{ $reportType }}">

                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-white/[0.06]">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                        </div>
                        <h2 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">
                            Report Parameters & Filters
                        </h2>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('reports.index', ['type' => $reportType]) }}"
                            class="text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition">
                            Clear Filters
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
                    
                    {{-- Date Preset --}}
                    <div class="lg:col-span-3">
                        <label for="preset" class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1.5">
                            Reporting Timeframe
                        </label>
                        <select name="preset" id="preset"
                            onchange="if (this.value !== 'custom') { document.getElementById('reportFilterForm').submit(); }"
                            class="w-full bg-slate-50 dark:bg-[#0C0D13] border border-slate-200/90 dark:border-white/[0.08] text-xs font-bold text-slate-800 dark:text-slate-200 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition cursor-pointer">
                            <option value="this_month" {{ $preset === 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="last_month" {{ $preset === 'last_month' ? 'selected' : '' }}>Last Month</option>
                            <option value="last_3_months" {{ $preset === 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
                            <option value="this_year" {{ $preset === 'this_year' ? 'selected' : '' }}>This Year (2026)</option>
                            <option value="last_year" {{ $preset === 'last_year' ? 'selected' : '' }}>Last Year</option>
                            <option value="all_time" {{ $preset === 'all_time' ? 'selected' : '' }}>All Time History</option>
                            <option value="custom" {{ $preset === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                        </select>
                    </div>

                    {{-- Custom Start Date --}}
                    <div class="lg:col-span-2">
                        <label for="start_date" class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1.5">
                            From Date
                        </label>
                        <input type="date" name="start_date" id="start_date" value="{{ $startDate }}"
                            class="w-full bg-slate-50 dark:bg-[#0C0D13] border border-slate-200/90 dark:border-white/[0.08] text-xs font-bold text-slate-800 dark:text-slate-200 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition cursor-pointer">
                    </div>

                    {{-- Custom End Date --}}
                    <div class="lg:col-span-2">
                        <label for="end_date" class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1.5">
                            To Date
                        </label>
                        <input type="date" name="end_date" id="end_date" value="{{ $endDate }}"
                            class="w-full bg-slate-50 dark:bg-[#0C0D13] border border-slate-200/90 dark:border-white/[0.08] text-xs font-bold text-slate-800 dark:text-slate-200 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition cursor-pointer">
                    </div>

                    {{-- Species Filter (Applicable for overview, adoptions, intakes, medical) --}}
                    @if(in_array($reportType, ['overview', 'adoptions', 'intakes', 'medical']))
                        <div class="lg:col-span-2">
                            <label for="species" class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1.5">
                                Animal Species
                            </label>
                            <select name="species" id="species"
                                class="w-full bg-slate-50 dark:bg-[#0C0D13] border border-slate-200/90 dark:border-white/[0.08] text-xs font-bold text-slate-800 dark:text-slate-200 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition cursor-pointer">
                                <option value="all" {{ $species === 'all' ? 'selected' : '' }}>All Species</option>
                                <option value="dog" {{ $species === 'dog' ? 'selected' : '' }}>Canine (Dogs)</option>
                                <option value="cat" {{ $species === 'cat' ? 'selected' : '' }}>Feline (Cats)</option>
                            </select>
                        </div>
                    @endif

                    {{-- Contextual Status Filter --}}
                    @if($reportType === 'adoptions')
                        <div class="lg:col-span-3">
                            <label for="status" class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1.5">
                                Application Status
                            </label>
                            <select name="status" id="status"
                                class="w-full bg-slate-50 dark:bg-[#0C0D13] border border-slate-200/90 dark:border-white/[0.08] text-xs font-bold text-slate-800 dark:text-slate-200 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition cursor-pointer">
                                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
                                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="under_review" {{ $status === 'under_review' ? 'selected' : '' }}>Under Review</option>
                                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                    @elseif($reportType === 'intakes')
                        <div class="lg:col-span-3">
                            <label for="status" class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1.5">
                                Shelter Inventory Status
                            </label>
                            <select name="status" id="status"
                                class="w-full bg-slate-50 dark:bg-[#0C0D13] border border-slate-200/90 dark:border-white/[0.08] text-xs font-bold text-slate-800 dark:text-slate-200 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition cursor-pointer">
                                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Inventory Statuses</option>
                                <option value="available" {{ $status === 'available' ? 'selected' : '' }}>Available for Adoption</option>
                                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending Adoption</option>
                                <option value="adopted" {{ $status === 'adopted' ? 'selected' : '' }}>Successfully Adopted</option>
                            </select>
                        </div>
                    @elseif($reportType === 'compliance')
                        <div class="lg:col-span-3">
                            <label for="status" class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1.5">
                                Check-in Status
                            </label>
                            <select name="status" id="status"
                                class="w-full bg-slate-50 dark:bg-[#0C0D13] border border-slate-200/90 dark:border-white/[0.08] text-xs font-bold text-slate-800 dark:text-slate-200 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition cursor-pointer">
                                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Compliance States</option>
                                <option value="submitted" {{ $status === 'submitted' ? 'selected' : '' }}>Up to Date</option>
                                <option value="due_soon" {{ $status === 'due_soon' ? 'selected' : '' }}>Due Soon</option>
                                <option value="overdue" {{ $status === 'overdue' ? 'selected' : '' }}>Overdue</option>
                                <option value="pending_first" {{ $status === 'pending_first' ? 'selected' : '' }}>Pending 1st Check-In</option>
                            </select>
                        </div>
                    @endif

                    {{-- Search Input & Action Trigger --}}
                    <div class="lg:col-span-12 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-3 border-t border-slate-100 dark:border-white/[0.04]">
                        <div class="relative flex-1 max-w-lg">
                            <input type="text" name="q" value="{{ $search }}" placeholder="Search by name, reference code, email, or keyword..."
                                class="w-full bg-slate-50 dark:bg-[#0C0D13] border border-slate-200/90 dark:border-white/[0.08] text-xs font-medium text-slate-800 dark:text-slate-200 rounded-xl pl-9 pr-3 py-2.5 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        <div class="flex items-center gap-2 justify-end">
                            <a href="{{ route('reports.index', ['type' => $reportType]) }}"
                                class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.06] transition">
                                Reset
                            </a>
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white text-xs font-extrabold shadow-sm shadow-[#199CA4]/25 transition-all cursor-pointer active:scale-95">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Apply Filters</span>
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>

        {{-- Dynamic KPI Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @if($reportType === 'overview')
                {{-- Rescues & Intakes --}}
                <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-black text-slate-400 dark:text-slate-500">Rescues & Intakes</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white my-1 tracking-tight">{{ $stats['totalIntakes'] ?? 0 }}</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#199CA4]"></span>
                            In reporting period
                        </p>
                    </div>
                    <div class="p-3 bg-teal-500/10 dark:bg-teal-500/15 rounded-2xl text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>

                {{-- Finalized Adoptions --}}
                <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-black text-slate-400 dark:text-slate-500">Finalized Adoptions</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white my-1 tracking-tight">{{ $stats['approvedAdoptions'] ?? 0 }}</h3>
                        <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            {{ $stats['conversionRate'] ?? 0 }}% conversion rate
                        </p>
                    </div>
                    <div class="p-3 bg-emerald-500/10 dark:bg-emerald-500/15 rounded-2xl text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                {{-- Medical Procedures --}}
                <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-black text-slate-400 dark:text-slate-500">Medical Procedures</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white my-1 tracking-tight">{{ $stats['totalMedicals'] ?? 0 }}</h3>
                        <p class="text-[11px] text-indigo-600 dark:text-indigo-400 font-medium flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            Vaccines, surgery & logs
                        </p>
                    </div>
                    <div class="p-3 bg-indigo-500/10 dark:bg-indigo-500/15 rounded-2xl text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>

                {{-- Current Shelter Residents --}}
                <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-black text-slate-400 dark:text-slate-500">Current Shelter Residents</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white my-1 tracking-tight">{{ $stats['activeShelter'] ?? 0 }}</h3>
                        <p class="text-[11px] text-amber-600 dark:text-amber-400 font-medium flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            {{ $stats['totalDogs'] ?? 0 }} Dogs • {{ $stats['totalCats'] ?? 0 }} Cats
                        </p>
                    </div>
                    <div class="p-3 bg-amber-500/10 dark:bg-amber-500/15 rounded-2xl text-amber-600 dark:text-amber-400 border border-amber-500/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                </div>

            @elseif($reportType === 'adoptions')
                {{-- Total Applications --}}
                <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-black text-slate-400 dark:text-slate-500">Applications Submitted</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white my-1 tracking-tight">{{ $stats['totalApplications'] ?? 0 }}</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#199CA4]"></span>
                            In selected date range
                        </p>
                    </div>
                    <div class="p-3 bg-teal-500/10 dark:bg-teal-500/15 rounded-2xl text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>

                {{-- Approved --}}
                <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-black text-slate-400 dark:text-slate-500">Approved Adoptions</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white my-1 tracking-tight">{{ $stats['approvedCount'] ?? 0 }}</h3>
                        <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            {{ $stats['approvalRate'] ?? 0 }}% approval rate
                        </p>
                    </div>
                    <div class="p-3 bg-emerald-500/10 dark:bg-emerald-500/15 rounded-2xl text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                {{-- Under Review / Pending --}}
                <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-black text-slate-400 dark:text-slate-500">Under Review / Pending</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white my-1 tracking-tight">{{ ($stats['underReviewCount'] ?? 0) + ($stats['pendingCount'] ?? 0) }}</h3>
                        <p class="text-[11px] text-amber-600 dark:text-amber-400 font-medium flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            {{ $stats['underReviewCount'] ?? 0 }} in active evaluation
                        </p>
                    </div>
                    <div class="p-3 bg-amber-500/10 dark:bg-amber-500/15 rounded-2xl text-amber-600 dark:text-amber-400 border border-amber-500/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                {{-- Rejected / Ineligible --}}
                <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-black text-slate-400 dark:text-slate-500">Ineligible / Rejected</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white my-1 tracking-tight">{{ $stats['rejectedCount'] ?? 0 }}</h3>
                        <p class="text-[11px] text-rose-600 dark:text-rose-400 font-medium flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                            Failed compliance/screening
                        </p>
                    </div>
                    <div class="p-3 bg-rose-500/10 dark:bg-rose-500/15 rounded-2xl text-rose-600 dark:text-rose-400 border border-rose-500/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>

            @elseif($reportType === 'intakes')
                {{-- Total Rescued --}}
                <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-black text-slate-400 dark:text-slate-500">Total Rescued</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white my-1 tracking-tight">{{ $stats['totalIntakes'] ?? 0 }}</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#199CA4]"></span>
                            Intake admissions
                        </p>
                    </div>
                    <div class="p-3 bg-teal-500/10 dark:bg-teal-500/15 rounded-2xl text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>

                {{-- Species Breakdown --}}
                <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-black text-slate-400 dark:text-slate-500">Species Breakdown</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white my-1 tracking-tight">{{ $stats['dogCount'] ?? 0 }} Dogs</h3>
                        <p class="text-[11px] text-indigo-600 dark:text-indigo-400 font-medium flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            {{ $stats['catCount'] ?? 0 }} Cats admitted
                        </p>
                    </div>
                    <div class="p-3 bg-indigo-500/10 dark:bg-indigo-500/15 rounded-2xl text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </div>
                </div>

                {{-- Available for Adoption --}}
                <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-black text-slate-400 dark:text-slate-500">Currently Available</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white my-1 tracking-tight">{{ $stats['availableCount'] ?? 0 }}</h3>
                        <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Ready for new homes
                        </p>
                    </div>
                    <div class="p-3 bg-emerald-500/10 dark:bg-emerald-500/15 rounded-2xl text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                {{-- Rehomed / Adopted --}}
                <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-black text-slate-400 dark:text-slate-500">Successfully Rehomed</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white my-1 tracking-tight">{{ $stats['adoptedCount'] ?? 0 }}</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#199CA4]"></span>
                            {{ $stats['pendingCount'] ?? 0 }} Pending finalization
                        </p>
                    </div>
                    <div class="p-3 bg-teal-500/10 dark:bg-teal-500/15 rounded-2xl text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                </div>

            @elseif($reportType === 'medical')
                {{-- Total Medical Procedures --}}
                <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-black text-slate-400 dark:text-slate-500">Total Procedures</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white my-1 tracking-tight">{{ $stats['totalMedicals'] ?? 0 }}</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            Recorded clinical logs
                        </p>
                    </div>
                    <div class="p-3 bg-indigo-500/10 dark:bg-indigo-500/15 rounded-2xl text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>

                {{-- Vaccinations --}}
                <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-black text-slate-400 dark:text-slate-500">Vaccinations Given</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white my-1 tracking-tight">{{ $stats['vaccinationCount'] ?? 0 }}</h3>
                        <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Rabies, 5-in-1, core vaccines
                        </p>
                    </div>
                    <div class="p-3 bg-emerald-500/10 dark:bg-emerald-500/15 rounded-2xl text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                </div>

                {{-- Surgeries & Spay/Neuter --}}
                <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-black text-slate-400 dark:text-slate-500">Surgeries & Spay/Neuter</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white my-1 tracking-tight">{{ $stats['surgeryCount'] ?? 0 }}</h3>
                        <p class="text-[11px] text-indigo-600 dark:text-indigo-400 font-medium flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            Sterilizations & medical ops
                        </p>
                    </div>
                    <div class="p-3 bg-indigo-500/10 dark:bg-indigo-500/15 rounded-2xl text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L12 12z" />
                        </svg>
                    </div>
                </div>

                {{-- Deworming & Checkups --}}
                <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-black text-slate-400 dark:text-slate-500">Deworming & Checkups</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white my-1 tracking-tight">{{ ($stats['dewormingCount'] ?? 0) + ($stats['checkupCount'] ?? 0) }}</h3>
                        <p class="text-[11px] text-amber-600 dark:text-amber-400 font-medium flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Routine wellness visits
                        </p>
                    </div>
                    <div class="p-3 bg-amber-500/10 dark:bg-amber-500/15 rounded-2xl text-amber-600 dark:text-amber-400 border border-amber-500/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>

            @elseif($reportType === 'compliance')
                {{-- Total Registered Adopters --}}
                <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-black text-slate-400 dark:text-slate-500">Total Adopters</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white my-1 tracking-tight">{{ $stats['totalAdopters'] ?? 0 }}</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#199CA4]"></span>
                            Registered profiles
                        </p>
                    </div>
                    <div class="p-3 bg-teal-500/10 dark:bg-teal-500/15 rounded-2xl text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>

                {{-- Up to Date --}}
                <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-black text-slate-400 dark:text-slate-500">Up to Date</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white my-1 tracking-tight">{{ $stats['submittedCount'] ?? 0 }}</h3>
                        <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            {{ $stats['goodStandingRate'] ?? 0 }}% compliance rate
                        </p>
                    </div>
                    <div class="p-3 bg-emerald-500/10 dark:bg-emerald-500/15 rounded-2xl text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                {{-- Due Soon --}}
                <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-black text-slate-400 dark:text-slate-500">Due Soon (≤7 Days)</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white my-1 tracking-tight">{{ $stats['dueSoonCount'] ?? 0 }}</h3>
                        <p class="text-[11px] text-amber-600 dark:text-amber-400 font-medium flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Monthly check-in reminder
                        </p>
                    </div>
                    <div class="p-3 bg-amber-500/10 dark:bg-amber-500/15 rounded-2xl text-amber-600 dark:text-amber-400 border border-amber-500/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                {{-- Overdue Check-ins --}}
                <div class="bg-white dark:bg-[#12141C] p-4 sm:p-5 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-black text-slate-400 dark:text-slate-500">Overdue Check-Ins</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white my-1 tracking-tight">{{ $stats['overdueCount'] ?? 0 }}</h3>
                        <p class="text-[11px] text-rose-600 dark:text-rose-400 font-medium flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                            Requires staff follow-up
                        </p>
                    </div>
                    <div class="p-3 bg-rose-500/10 dark:bg-rose-500/15 rounded-2xl text-rose-600 dark:text-rose-400 border border-rose-500/20">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            @endif
        </div>

        {{-- Visual Analytics Chart Section --}}
        @if(!empty($chartData['labels']))
            <div class="bg-white dark:bg-[#12141C] rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] p-5 sm:p-6 print:hidden">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-white/[0.06]">
                    <div>
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                </svg>
                            </div>
                            <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">
                                {{ ucfirst($reportType) }} Trends & Visual Analytics
                            </h2>
                        </div>
                        <p class="text-xs text-slate-400 dark:text-slate-400 mt-0.5">
                            Interactive visual distributions for {{ $dateRangeLabel }}.
                        </p>
                    </div>

                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08] self-start sm:self-center">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Live Aggregation
                    </span>
                </div>

                <div class="h-64 sm:h-80 relative w-full">
                    <canvas id="reportAnalyticsChart"></canvas>
                </div>
            </div>
        @endif

        {{-- Detailed Data Registry Table --}}
        <div class="bg-white dark:bg-[#12141C] rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] overflow-hidden">
            
            {{-- Table Header --}}
            <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between bg-slate-50/50 dark:bg-[#171923]/60">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-900 dark:text-white tracking-tight">
                            {{ ucfirst($reportType) }} Detailed Registry Records
                        </h2>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500">
                            @if(is_object($records) && method_exists($records, 'total'))
                                Showing {{ $records->firstItem() ?? 0 }} to {{ $records->lastItem() ?? 0 }} of {{ $records->total() }} records
                            @else
                                {{ count($records) }} records displayed
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- Table View --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    
                    @if($reportType === 'adoptions')
                        <thead class="bg-slate-50/90 dark:bg-[#171923] text-[10px] uppercase font-black tracking-wider text-slate-400 dark:text-slate-400 border-b border-slate-100 dark:border-white/[0.06]">
                            <tr>
                                <th class="py-3.5 px-4 sm:px-5">App Reference</th>
                                <th class="py-3.5 px-4 sm:px-5">Applicant</th>
                                <th class="py-3.5 px-4 sm:px-5">Pet Details</th>
                                <th class="py-3.5 px-4 sm:px-5">Status</th>
                                <th class="py-3.5 px-4 sm:px-5">Submitted Date</th>
                                <th class="py-3.5 px-4 sm:px-5">Evaluator</th>
                                <th class="py-3.5 px-4 sm:px-5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                            @forelse($records as $app)
                                <tr class="hover:bg-slate-50/90 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="py-3.5 px-4 sm:px-5">
                                        <span class="inline-flex items-center font-mono font-black text-[11px] text-slate-900 dark:text-white px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-white/[0.06] border border-slate-200/80 dark:border-white/[0.08]">
                                            #APP-{{ str_pad($app->id, 4, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $app->applicant_name ?? 'N/A' }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $app->applicant_email ?? '' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5">
                                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ $app->pet ? $app->pet->name : 'Pet no. ' . $app->pet_id }}</div>
                                        <div class="text-[11px] text-slate-400 capitalize">{{ $app->pet ? ($app->pet->type ?? 'N/A') : 'N/A' }} • {{ $app->pet ? ($app->pet->breed ?? 'Mixed') : '' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5">
                                        @if($app->status === 'approved')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black bg-emerald-100/80 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/80">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Approved
                                            </span>
                                        @elseif($app->status === 'under_review')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black bg-amber-100/80 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/80">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Under Review
                                            </span>
                                        @elseif($app->status === 'rejected')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black bg-rose-100/80 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800/80">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Rejected
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08]">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5 text-slate-500 whitespace-nowrap font-medium">
                                        {{ $app->created_at ? $app->created_at->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5 text-slate-600 dark:text-slate-400 font-medium">
                                        {{ $app->evaluator_name ?? 'Pending Staff' }}
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5 text-right print:hidden">
                                        <a href="{{ route('adoption-applications.show', $app->id) }}"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold text-[#199CA4] hover:text-white dark:text-[#41C1CB] hover:bg-[#199CA4] dark:hover:bg-[#199CA4] transition-colors">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="font-bold text-xs">No adoption applications recorded in this reporting period.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    @elseif($reportType === 'intakes')
                        <thead class="bg-slate-50/90 dark:bg-[#171923] text-[10px] uppercase font-black tracking-wider text-slate-400 dark:text-slate-400 border-b border-slate-100 dark:border-white/[0.06]">
                            <tr>
                                <th class="py-3.5 px-4 sm:px-5">Pet Code</th>
                                <th class="py-3.5 px-4 sm:px-5">Pet Name</th>
                                <th class="py-3.5 px-4 sm:px-5">Species & Breed</th>
                                <th class="py-3.5 px-4 sm:px-5">Attributes</th>
                                <th class="py-3.5 px-4 sm:px-5">Status</th>
                                <th class="py-3.5 px-4 sm:px-5">Intake Date</th>
                                <th class="py-3.5 px-4 sm:px-5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                            @forelse($records as $pet)
                                <tr class="hover:bg-slate-50/90 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="py-3.5 px-4 sm:px-5">
                                        <span class="inline-flex items-center font-mono font-black text-[11px] text-slate-900 dark:text-white px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-white/[0.06] border border-slate-200/80 dark:border-white/[0.08]">
                                            #PET-{{ str_pad($pet->id, 4, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5 font-bold text-slate-900 dark:text-white">
                                        {{ $pet->name ?? 'Pet no. ' . $pet->id }}
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5">
                                        <div class="font-bold text-slate-800 dark:text-slate-200 capitalize">{{ $pet->type ?? 'N/A' }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $pet->breed ?? 'Mixed Breed' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5 text-slate-600 dark:text-slate-400 font-medium capitalize">
                                        {{ $pet->gender ?? 'N/A' }} • {{ $pet->age ?? 'N/A' }} • {{ $pet->color ?? 'N/A' }}
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5">
                                        @if($pet->status === 'available')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black bg-emerald-100/80 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/80">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Available
                                            </span>
                                        @elseif($pet->status === 'adopted')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/30 dark:border-[#199CA4]/40">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#199CA4]"></span>
                                                Adopted
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black bg-amber-100/80 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/80">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5 text-slate-500 whitespace-nowrap font-medium">
                                        {{ $pet->created_at ? $pet->created_at->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5 text-right print:hidden">
                                        <a href="{{ route('pets.show', $pet->id) }}"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold text-[#199CA4] hover:text-white dark:text-[#41C1CB] hover:bg-[#199CA4] dark:hover:bg-[#199CA4] transition-colors">
                                            Pet Profile
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                            </svg>
                                            <p class="font-bold text-xs">No pet intake records found matching selected criteria.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    @elseif($reportType === 'medical')
                        <thead class="bg-slate-50/90 dark:bg-[#171923] text-[10px] uppercase font-black tracking-wider text-slate-400 dark:text-slate-400 border-b border-slate-100 dark:border-white/[0.06]">
                            <tr>
                                <th class="py-3.5 px-4 sm:px-5">Date</th>
                                <th class="py-3.5 px-4 sm:px-5">Pet Information</th>
                                <th class="py-3.5 px-4 sm:px-5">Procedure</th>
                                <th class="py-3.5 px-4 sm:px-5">Administered By</th>
                                <th class="py-3.5 px-4 sm:px-5">Next Due Date</th>
                                <th class="py-3.5 px-4 sm:px-5">Recorded By</th>
                                <th class="py-3.5 px-4 sm:px-5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                            @forelse($records as $log)
                                <tr class="hover:bg-slate-50/90 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="py-3.5 px-4 sm:px-5 font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                        {{ $log->date ? $log->date->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $log->pet ? $log->pet->name : 'Pet no. ' . $log->pet_id }}</div>
                                        <div class="text-[11px] text-slate-400 capitalize">{{ $log->pet ? ($log->pet->type ?? '') : '' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-indigo-100/80 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/80">
                                            {{ $log->category ?? 'General Checkup' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5 font-medium text-slate-700 dark:text-slate-300">
                                        {{ $log->administered_by ?? 'CAWS Clinic Staff' }}
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5 text-slate-500 whitespace-nowrap font-medium">
                                        {{ $log->next_due_date ? $log->next_due_date->format('M d, Y') : 'None Scheduled' }}
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5 text-slate-500 font-medium">
                                        {{ $log->creator ? $log->creator->name : 'System' }}
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5 text-right print:hidden">
                                        <a href="{{ route('medical-logs.index') }}"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold text-[#199CA4] hover:text-white dark:text-[#41C1CB] hover:bg-[#199CA4] dark:hover:bg-[#199CA4] transition-colors">
                                            View Logs
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="font-bold text-xs">No clinical procedures recorded in this reporting period.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    @elseif($reportType === 'compliance')
                        <thead class="bg-slate-50/90 dark:bg-[#171923] text-[10px] uppercase font-black tracking-wider text-slate-400 dark:text-slate-400 border-b border-slate-100 dark:border-white/[0.06]">
                            <tr>
                                <th class="py-3.5 px-4 sm:px-5">Adopter ID</th>
                                <th class="py-3.5 px-4 sm:px-5">Adopter Name</th>
                                <th class="py-3.5 px-4 sm:px-5">Location</th>
                                <th class="py-3.5 px-4 sm:px-5">Adopted Pets</th>
                                <th class="py-3.5 px-4 sm:px-5">Compliance Status</th>
                                <th class="py-3.5 px-4 sm:px-5">Last Check-In</th>
                                <th class="py-3.5 px-4 sm:px-5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                            @forelse($records as $item)
                                <tr class="hover:bg-slate-50/90 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="py-3.5 px-4 sm:px-5">
                                        <span class="inline-flex items-center font-mono font-black text-[11px] text-slate-900 dark:text-white px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-white/[0.06] border border-slate-200/80 dark:border-white/[0.08]">
                                            {{ $item['adopter_code'] }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $item['full_name'] }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $item['email'] }} • {{ $item['phone'] }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5 text-slate-600 dark:text-slate-400 font-medium">
                                        {{ $item['location'] }}
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5 font-bold text-slate-900 dark:text-white">
                                        {{ $item['adopted_count'] }} Pet(s)
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5">
                                        @if($item['status_type'] === 'submitted')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black bg-emerald-100/80 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/80">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                {{ $item['status_label'] }}
                                            </span>
                                        @elseif($item['status_type'] === 'due_soon')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black bg-amber-100/80 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/80">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                {{ $item['status_label'] }}
                                            </span>
                                        @elseif($item['status_type'] === 'overdue')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black bg-rose-100/80 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800/80">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                {{ $item['status_label'] }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08]">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                {{ $item['status_label'] }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5 text-slate-500 whitespace-nowrap font-medium">
                                        {{ $item['last_check_in_date'] }}
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5 text-right print:hidden">
                                        <a href="{{ route('adopters.index') }}"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold text-[#199CA4] hover:text-white dark:text-[#41C1CB] hover:bg-[#199CA4] dark:hover:bg-[#199CA4] transition-colors">
                                            Adopter Profile
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 10-8 0v4M5 11h14v10H5V11z" />
                                            </svg>
                                            <p class="font-bold text-xs">No adopter records matching the compliance criteria.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    @else
                        {{-- Executive Overview Key Milestones --}}
                        <thead class="bg-slate-50/90 dark:bg-[#171923] text-[10px] uppercase font-black tracking-wider text-slate-400 dark:text-slate-400 border-b border-slate-100 dark:border-white/[0.06]">
                            <tr>
                                <th class="py-3.5 px-4 sm:px-5">Milestone</th>
                                <th class="py-3.5 px-4 sm:px-5">Adopter</th>
                                <th class="py-3.5 px-4 sm:px-5">Pet Adopted</th>
                                <th class="py-3.5 px-4 sm:px-5">Finalized Date</th>
                                <th class="py-3.5 px-4 sm:px-5">Authorized Staff</th>
                                <th class="py-3.5 px-4 sm:px-5 text-right">Contract</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                            @forelse($records as $app)
                                <tr class="hover:bg-slate-50/90 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="py-3.5 px-4 sm:px-5">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black bg-emerald-100/80 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/80">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Finalized Adoption
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $app->applicant_name ?? 'N/A' }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $app->applicant_email ?? '' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $app->pet ? $app->pet->name : 'Pet no. ' . $app->pet_id }}</div>
                                        <div class="text-[11px] text-slate-400 capitalize">{{ $app->pet ? ($app->pet->type ?? '') : '' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5 text-slate-500 whitespace-nowrap font-medium">
                                        {{ $app->approved_at ? \Carbon\Carbon::parse($app->approved_at)->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5 text-slate-600 dark:text-slate-400 font-medium">
                                        {{ $app->staff_name ?? ($app->staff ? $app->staff->name : 'CAWS Representative') }}
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-5 text-right print:hidden">
                                        <a href="{{ route('adoption-applications.contract', $app->id) }}" target="_blank"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold text-[#199CA4] hover:text-white dark:text-[#41C1CB] hover:bg-[#199CA4] dark:hover:bg-[#199CA4] transition-colors">
                                            Contract PDF
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p class="font-bold text-xs">No finalized adoptions in this reporting period.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    @endif

                </table>
            </div>

            {{-- Pagination footer if paginator object --}}
            @if(is_object($records) && method_exists($records, 'links') && $records->hasPages())
                <div class="p-4 sm:p-5 border-t border-slate-100 dark:border-white/[0.06] bg-slate-50/50 dark:bg-[#171923]/60 print:hidden">
                    {{ $records->links() }}
                </div>
            @endif

        </div>

    </div>

    {{-- Chart.js Scripts --}}
    @if(!empty($chartData['labels']))
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('reportAnalyticsChart');
                if (!ctx) return;

                function getChartColors() {
                    const isDark = document.documentElement.classList.contains('dark');
                    return {
                        tickColor: isDark ? '#94A3B8' : '#64748B',
                        gridColor: isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.05)',
                    };
                }

                const colors = getChartColors();
                const chartType = '{{ $reportType === "compliance" ? "doughnut" : "bar" }}';

                let chart = new Chart(ctx, {
                    type: chartType,
                    data: @json($chartData),
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                display: {{ $reportType === 'compliance' || $reportType === 'overview' || $reportType === 'adoptions' ? 'true' : 'false' }},
                                position: 'top',
                                labels: {
                                    boxWidth: 10,
                                    boxHeight: 10,
                                    usePointStyle: true,
                                    font: { size: 11, weight: 'bold' },
                                    color: colors.tickColor,
                                    padding: 16
                                }
                            },
                            tooltip: {
                                backgroundColor: '#12141C',
                                borderColor: 'rgba(255,255,255,0.12)',
                                borderWidth: 1,
                                titleColor: '#ffffff',
                                bodyColor: '#e2e8f0',
                                titleFont: { size: 12, weight: 'bold' },
                                bodyFont: { size: 11, weight: 'normal' },
                                padding: 10,
                                cornerRadius: 10,
                            }
                        },
                        scales: chartType === 'doughnut' ? {} : {
                            x: {
                                grid: { display: false },
                                ticks: {
                                    color: colors.tickColor,
                                    font: { size: 11, weight: '600' }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    precision: 0,
                                    color: colors.tickColor,
                                    font: { size: 11, weight: '600' }
                                },
                                grid: {
                                    color: colors.gridColor,
                                    strokeDash: [3, 3],
                                },
                                border: { display: false }
                            }
                        }
                    }
                });

                window.addEventListener('theme-changed', function () {
                    const c = getChartColors();
                    if (chart && chartType !== 'doughnut') {
                        chart.options.scales.x.ticks.color = c.tickColor;
                        chart.options.scales.y.ticks.color = c.tickColor;
                        chart.options.scales.y.grid.color = c.gridColor;
                    }
                    if (chart && chart.options.plugins.legend) {
                        chart.options.plugins.legend.labels.color = c.tickColor;
                    }
                    chart.update();
                });
            });
        </script>
    @endif

    {{-- Formal Print Stylesheet --}}
    <style>
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
            }
            .fixed, aside, nav, header {
                display: none !important;
            }
            .lg\:pl-64 {
                padding-left: 0 !important;
            }
            .print\:hidden {
                display: none !important;
            }
            .print\:block {
                display: block !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            th, td {
                border: 1px solid #cbd5e1 !important;
                padding: 6px 8px !important;
                font-size: 10px !important;
                color: #000000 !important;
            }
            thead th {
                background-color: #f1f5f9 !important;
            }
        }
    </style>
</x-app-layout>
