<x-app-layout>
    <div class="w-full py-6 sm:py-8 px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in print:p-0 print:space-y-4">

        {{-- Header Bar --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between print:hidden">
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        System Reports
                    </h1>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20">
                        {{ $dateRangeLabel }}
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Summary metrics and administrative registry records.
                </p>
            </div>

            {{-- Quick Timeframe Selector & Export Actions --}}
            <div class="flex flex-wrap items-center gap-2.5">
                {{-- Quick Timeframe Dropdown --}}
                <form method="GET" action="{{ route('reports.index') }}" class="inline-block">
                    <input type="hidden" name="type" value="{{ $reportType }}">
                    @if($search)
                        <input type="hidden" name="q" value="{{ $search }}">
                    @endif
                    <select name="preset" onchange="this.form.submit()"
                        class="bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.08] text-xs font-bold text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition cursor-pointer shadow-2xs">
                        <option value="this_month" {{ $preset === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ $preset === 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="last_3_months" {{ $preset === 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
                        <option value="this_year" {{ $preset === 'this_year' ? 'selected' : '' }}>This Year</option>
                        <option value="last_year" {{ $preset === 'last_year' ? 'selected' : '' }}>Last Year</option>
                        <option value="all_time" {{ $preset === 'all_time' ? 'selected' : '' }}>All Time</option>
                    </select>
                </form>

                {{-- Export PDF --}}
                <a href="{{ route('reports.export.pdf', array_merge(request()->query(), ['type' => $reportType])) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Export PDF</span>
                </a>

                {{-- Export CSV --}}
                <a href="{{ route('reports.export.csv', array_merge(request()->query(), ['type' => $reportType])) }}" download
                    class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Export CSV</span>
                </a>

                {{-- Print --}}
                <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-white dark:bg-[#12141C] hover:bg-slate-50 dark:hover:bg-white/[0.06] text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200/80 dark:border-white/[0.08] shadow-2xs transition-colors cursor-pointer">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Print</span>
                </button>
            </div>
        </div>

        {{-- Print-Only Header --}}
        <div class="hidden print:block border-b-2 border-slate-800 pb-3 mb-4">
            <h1 class="text-xl font-bold uppercase text-slate-900">CDO Animal Welfare Society Inc.</h1>
            <h2 class="text-sm font-semibold uppercase text-slate-700 mt-0.5">{{ ucfirst($reportType) }} Report</h2>
            <p class="text-xs text-slate-500 mt-1">Period: {{ $dateRangeLabel }} | Generated: {{ now()->format('F d, Y h:i A') }}</p>
        </div>

        {{-- Simple Category Tabs & Search Row --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 print:hidden">
            {{-- Category Tabs --}}
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1">
                @php
                    $simpleTabs = [
                        'overview'   => 'Overview',
                        'adoptions'  => 'Adoptions',
                        'intakes'    => 'Pet Intakes',
                        'medical'    => 'Medical Logs',
                        'compliance' => 'Compliance',
                    ];
                @endphp
                @foreach($simpleTabs as $key => $label)
                    @php
                        $isActive = ($reportType === $key);
                        $tabParams = array_merge(request()->query(), ['type' => $key]);
                        unset($tabParams['page']);
                    @endphp
                    <a href="{{ route('reports.index', $tabParams) }}"
                       class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $isActive ? 'bg-[#199CA4] text-white shadow-xs' : 'bg-white dark:bg-[#12141C] text-slate-600 dark:text-slate-400 border border-slate-200/80 dark:border-white/[0.08] hover:bg-slate-50 dark:hover:bg-white/[0.04]' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Simple Inline Search Form --}}
            <form method="GET" action="{{ route('reports.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="type" value="{{ $reportType }}">
                <input type="hidden" name="preset" value="{{ $preset }}">
                <div class="relative">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Search records..."
                        class="pl-8 pr-3 py-2 bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.08] text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-xs w-48 sm:w-64 focus:outline-none focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] shadow-2xs transition font-medium">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <button type="submit" class="px-3.5 py-2 bg-[#199CA4] hover:bg-[#13787F] text-white font-bold rounded-xl text-xs shadow-xs transition-colors cursor-pointer">
                    Search
                </button>
                @if($search)
                    <a href="{{ route('reports.index', ['type' => $reportType, 'preset' => $preset]) }}" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 font-semibold underline">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- Simple 4 Summary Metric Cards (Clean & Straightforward) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @if($reportType === 'overview')
                {{-- Rescues & Intakes --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Rescues & Intakes</span>
                    <div class="mt-2 text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ $stats['totalIntakes'] ?? 0 }}</div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                        {{ $stats['dogIntakes'] ?? 0 }} Dogs, {{ $stats['catIntakes'] ?? 0 }} Cats
                    </p>
                </div>

                {{-- Total Adoption --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Adoption</span>
                    <div class="mt-2 text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['approvedAdoptions'] ?? 0 }}</div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                        Finalized adoptions
                    </p>
                </div>

                {{-- Medical Logs --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Medical Procedures</span>
                    <div class="mt-2 text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ $stats['totalMedicals'] ?? 0 }}</div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                        {{ $stats['vaccinationCount'] ?? 0 }} Vaccines, {{ $stats['surgeryCount'] ?? 0 }} Surgeries
                    </p>
                </div>

                {{-- Shelter Residents --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">In Shelter</span>
                    <div class="mt-2 text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ $stats['activeShelter'] ?? 0 }}</div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                        {{ $stats['totalDogs'] ?? 0 }} Dogs, {{ $stats['totalCats'] ?? 0 }} Cats
                    </p>
                </div>

            @elseif($reportType === 'adoptions')
                {{-- Total Applications --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Applications</span>
                    <div class="mt-2 text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ $stats['totalApplications'] ?? 0 }}</div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">Filed applications</p>
                </div>

                {{-- Total Adoption --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Adoption</span>
                    <div class="mt-2 text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['approvedCount'] ?? 0 }}</div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                        Approved & finalized
                    </p>
                </div>

                {{-- Under Review / Pending --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Under Review</span>
                    <div class="mt-2 text-2xl sm:text-3xl font-black text-amber-600 dark:text-amber-400">{{ $stats['underReviewCount'] ?? 0 }}</div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                        {{ $stats['pendingCount'] ?? 0 }} Pending evaluation
                    </p>
                </div>

                {{-- Rejected --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Rejected</span>
                    <div class="mt-2 text-2xl sm:text-3xl font-black text-rose-600 dark:text-rose-400">{{ $stats['rejectedCount'] ?? 0 }}</div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">Ineligible applicants</p>
                </div>

            @elseif($reportType === 'intakes')
                {{-- Total Intakes --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Admissions</span>
                    <div class="mt-2 text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ $stats['totalIntakes'] ?? 0 }}</div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">Shelter intakes</p>
                </div>

                {{-- Available --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Available</span>
                    <div class="mt-2 text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['availableCount'] ?? 0 }}</div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">Ready for adoption</p>
                </div>

                {{-- Adopted --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Adopted</span>
                    <div class="mt-2 text-2xl sm:text-3xl font-black text-[#199CA4] dark:text-[#41C1CB]">{{ $stats['adoptedCount'] ?? 0 }}</div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">Successfully rehomed</p>
                </div>

                {{-- Species Ratio --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Species Ratio</span>
                    <div class="mt-2 text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ $stats['dogCount'] ?? 0 }} / {{ $stats['catCount'] ?? 0 }}</div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">Dogs vs Cats</p>
                </div>

            @elseif($reportType === 'medical')
                {{-- Total Procedures --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Procedures</span>
                    <div class="mt-2 text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ $stats['totalMedicals'] ?? 0 }}</div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">Clinical logs</p>
                </div>

                {{-- Vaccinations --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Vaccinations</span>
                    <div class="mt-2 text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['vaccinationCount'] ?? 0 }}</div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">Core vaccines administered</p>
                </div>

                {{-- Surgeries --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Surgeries</span>
                    <div class="mt-2 text-2xl sm:text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ $stats['surgeryCount'] ?? 0 }}</div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">Spay, neuter & medical ops</p>
                </div>

                {{-- Deworming & Checkups --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Routine Care</span>
                    <div class="mt-2 text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ ($stats['dewormingCount'] ?? 0) + ($stats['checkupCount'] ?? 0) }}</div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">Deworming & wellness checkups</p>
                </div>

            @elseif($reportType === 'compliance')
                {{-- Total Adopters --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Adopters</span>
                    <div class="mt-2 text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ $stats['totalAdopters'] ?? 0 }}</div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">Registered adopters</p>
                </div>

                {{-- Up to Date --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Up to Date</span>
                    <div class="mt-2 text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['submittedCount'] ?? 0 }}</div>
                    <p class="mt-1 text-[11px] text-emerald-600 dark:text-emerald-400 font-medium">
                        {{ $stats['goodStandingRate'] ?? 0 }}% compliance rate
                    </p>
                </div>

                {{-- Due Soon --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Due Soon</span>
                    <div class="mt-2 text-2xl sm:text-3xl font-black text-amber-600 dark:text-amber-400">{{ $stats['dueSoonCount'] ?? 0 }}</div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">Due within 7 days</p>
                </div>

                {{-- Overdue --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.07] shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Overdue</span>
                    <div class="mt-2 text-2xl sm:text-3xl font-black text-rose-600 dark:text-rose-400">{{ $stats['overdueCount'] ?? 0 }}</div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">Requires follow-up</p>
                </div>
            @endif
        </div>

        {{-- Simple Clean Data Records Table --}}
        <div class="bg-white dark:bg-[#12141C] rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] overflow-hidden">
            
            {{-- Table Header Bar --}}
            <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                        @if($reportType === 'overview')
                            Finalized Adoptions
                        @elseif($reportType === 'adoptions')
                            Adoption Applications
                        @elseif($reportType === 'intakes')
                            Pet Admissions
                        @elseif($reportType === 'medical')
                            Medical Logs
                        @elseif($reportType === 'compliance')
                            Adopter Compliance Records
                        @endif
                    </h2>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                        @if(is_object($records) && method_exists($records, 'total'))
                            Showing {{ $records->firstItem() ?? 0 }} to {{ $records->lastItem() ?? 0 }} of {{ $records->total() }} records
                        @else
                            {{ count($records) }} records displayed
                        @endif
                    </p>
                </div>
            </div>

            {{-- Table View --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    
                    @if($reportType === 'adoptions')
                        <thead class="bg-slate-50/80 dark:bg-[#171923] text-[11px] uppercase font-bold text-slate-400 dark:text-slate-400 border-b border-slate-100 dark:border-white/[0.06]">
                            <tr>
                                <th class="py-3 px-4 sm:px-5">Reference</th>
                                <th class="py-3 px-4 sm:px-5">Applicant</th>
                                <th class="py-3 px-4 sm:px-5">Pet</th>
                                <th class="py-3 px-4 sm:px-5">Status</th>
                                <th class="py-3 px-4 sm:px-5">Submitted Date</th>
                                <th class="py-3 px-4 sm:px-5">Evaluator</th>
                                <th class="py-3 px-4 sm:px-5 text-right print:hidden">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                            @forelse($records as $app)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="py-3 px-4 sm:px-5 font-mono font-bold text-slate-900 dark:text-white">
                                        #APP-{{ str_pad($app->id, 4, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="py-3 px-4 sm:px-5">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $app->applicant_name ?? 'N/A' }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $app->applicant_email ?? '' }}</div>
                                    </td>
                                    <td class="py-3 px-4 sm:px-5">
                                        <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $app->pet ? $app->pet->name : 'Pet no. ' . $app->pet_id }}</div>
                                        <div class="text-[11px] text-slate-400 capitalize">{{ $app->pet ? ($app->pet->type ?? 'N/A') : 'N/A' }}</div>
                                    </td>
                                    <td class="py-3 px-4 sm:px-5">
                                        @if($app->status === 'approved')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-emerald-100/80 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">
                                                Approved
                                            </span>
                                        @elseif($app->status === 'under_review')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-amber-100/80 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300">
                                                Under Review
                                            </span>
                                        @elseif($app->status === 'rejected')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-rose-100/80 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300">
                                                Rejected
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 sm:px-5 text-slate-500 whitespace-nowrap">
                                        {{ $app->created_at ? $app->created_at->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 sm:px-5 text-slate-600 dark:text-slate-400">
                                        {{ $app->evaluator_name ?? 'Pending Staff' }}
                                    </td>
                                    <td class="py-3 px-4 sm:px-5 text-right print:hidden">
                                        <a href="{{ route('adoption-applications.show', $app->id) }}"
                                            class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold text-[#199CA4] hover:text-white dark:text-[#41C1CB] hover:bg-[#199CA4] transition-colors">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-slate-400 dark:text-slate-500 text-xs">
                                        No adoption applications recorded in this period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    @elseif($reportType === 'intakes')
                        <thead class="bg-slate-50/80 dark:bg-[#171923] text-[11px] uppercase font-bold text-slate-400 dark:text-slate-400 border-b border-slate-100 dark:border-white/[0.06]">
                            <tr>
                                <th class="py-3 px-4 sm:px-5">Pet Code</th>
                                <th class="py-3 px-4 sm:px-5">Pet Name</th>
                                <th class="py-3 px-4 sm:px-5">Species & Breed</th>
                                <th class="py-3 px-4 sm:px-5">Status</th>
                                <th class="py-3 px-4 sm:px-5">Intake Date</th>
                                <th class="py-3 px-4 sm:px-5 text-right print:hidden">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                            @forelse($records as $pet)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="py-3 px-4 sm:px-5 font-mono font-bold text-slate-900 dark:text-white">
                                        #PET-{{ str_pad($pet->id, 4, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="py-3 px-4 sm:px-5 font-bold text-slate-900 dark:text-white">
                                        {{ $pet->name ?? 'Pet no. ' . $pet->id }}
                                    </td>
                                    <td class="py-3 px-4 sm:px-5">
                                        <div class="font-semibold text-slate-800 dark:text-slate-200 capitalize">{{ $pet->type ?? 'N/A' }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $pet->breed ?? 'Mixed Breed' }}</div>
                                    </td>
                                    <td class="py-3 px-4 sm:px-5">
                                        @if($pet->status === 'available')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-emerald-100/80 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">
                                                Available
                                            </span>
                                        @elseif($pet->status === 'adopted')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB]">
                                                Adopted
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-amber-100/80 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 sm:px-5 text-slate-500 whitespace-nowrap">
                                        {{ $pet->created_at ? $pet->created_at->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 sm:px-5 text-right print:hidden">
                                        <a href="{{ route('pets.show', $pet->id) }}"
                                            class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold text-[#199CA4] hover:text-white dark:text-[#41C1CB] hover:bg-[#199CA4] transition-colors">
                                            Profile
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-slate-400 dark:text-slate-500 text-xs">
                                        No pet admissions found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    @elseif($reportType === 'medical')
                        <thead class="bg-slate-50/80 dark:bg-[#171923] text-[11px] uppercase font-bold text-slate-400 dark:text-slate-400 border-b border-slate-100 dark:border-white/[0.06]">
                            <tr>
                                <th class="py-3 px-4 sm:px-5">Date</th>
                                <th class="py-3 px-4 sm:px-5">Pet</th>
                                <th class="py-3 px-4 sm:px-5">Procedure</th>
                                <th class="py-3 px-4 sm:px-5">Administered By</th>
                                <th class="py-3 px-4 sm:px-5">Next Due</th>
                                <th class="py-3 px-4 sm:px-5 text-right print:hidden">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                            @forelse($records as $log)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="py-3 px-4 sm:px-5 font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                        {{ $log->date ? $log->date->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 sm:px-5">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $log->pet ? $log->pet->name : 'Pet no. ' . $log->pet_id }}</div>
                                        <div class="text-[11px] text-slate-400 capitalize">{{ $log->pet ? ($log->pet->type ?? '') : '' }}</div>
                                    </td>
                                    <td class="py-3 px-4 sm:px-5">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-indigo-100/80 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300">
                                            {{ $log->category ?? 'General' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 sm:px-5 text-slate-700 dark:text-slate-300">
                                        {{ $log->administered_by ?? 'Clinic Staff' }}
                                    </td>
                                    <td class="py-3 px-4 sm:px-5 text-slate-500 whitespace-nowrap">
                                        {{ $log->next_due_date ? $log->next_due_date->format('M d, Y') : 'None' }}
                                    </td>
                                    <td class="py-3 px-4 sm:px-5 text-right print:hidden">
                                        <a href="{{ route('medical-logs.index') }}"
                                            class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold text-[#199CA4] hover:text-white dark:text-[#41C1CB] hover:bg-[#199CA4] transition-colors">
                                            Logs
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-slate-400 dark:text-slate-500 text-xs">
                                        No clinical procedures recorded.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    @elseif($reportType === 'compliance')
                        <thead class="bg-slate-50/80 dark:bg-[#171923] text-[11px] uppercase font-bold text-slate-400 dark:text-slate-400 border-b border-slate-100 dark:border-white/[0.06]">
                            <tr>
                                <th class="py-3 px-4 sm:px-5">Adopter ID</th>
                                <th class="py-3 px-4 sm:px-5">Adopter Name</th>
                                <th class="py-3 px-4 sm:px-5">Location</th>
                                <th class="py-3 px-4 sm:px-5">Adopted Pets</th>
                                <th class="py-3 px-4 sm:px-5">Status</th>
                                <th class="py-3 px-4 sm:px-5">Last Check-In</th>
                                <th class="py-3 px-4 sm:px-5 text-right print:hidden">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                            @forelse($records as $item)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="py-3 px-4 sm:px-5 font-mono font-bold text-slate-900 dark:text-white">
                                        {{ $item['adopter_code'] }}
                                    </td>
                                    <td class="py-3 px-4 sm:px-5">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $item['full_name'] }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $item['phone'] }}</div>
                                    </td>
                                    <td class="py-3 px-4 sm:px-5 text-slate-600 dark:text-slate-400">
                                        {{ $item['location'] }}
                                    </td>
                                    <td class="py-3 px-4 sm:px-5 font-bold text-slate-900 dark:text-white">
                                        {{ $item['adopted_count'] }}
                                    </td>
                                    <td class="py-3 px-4 sm:px-5">
                                        @if($item['status_type'] === 'submitted')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-emerald-100/80 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">
                                                {{ $item['status_label'] }}
                                            </span>
                                        @elseif($item['status_type'] === 'due_soon')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-amber-100/80 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300">
                                                {{ $item['status_label'] }}
                                            </span>
                                        @elseif($item['status_type'] === 'overdue')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-rose-100/80 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300">
                                                {{ $item['status_label'] }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300">
                                                {{ $item['status_label'] }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 sm:px-5 text-slate-500 whitespace-nowrap">
                                        {{ $item['last_check_in_date'] }}
                                    </td>
                                    <td class="py-3 px-4 sm:px-5 text-right print:hidden">
                                        <a href="{{ route('adopters.index') }}"
                                            class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold text-[#199CA4] hover:text-white dark:text-[#41C1CB] hover:bg-[#199CA4] transition-colors">
                                            Profile
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-slate-400 dark:text-slate-500 text-xs">
                                        No adopter compliance records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    @else
                        {{-- Overview: Recent Adoptions --}}
                        <thead class="bg-slate-50/80 dark:bg-[#171923] text-[11px] uppercase font-bold text-slate-400 dark:text-slate-400 border-b border-slate-100 dark:border-white/[0.06]">
                            <tr>
                                <th class="py-3 px-4 sm:px-5">Adoption Ref</th>
                                <th class="py-3 px-4 sm:px-5">Applicant</th>
                                <th class="py-3 px-4 sm:px-5">Pet</th>
                                <th class="py-3 px-4 sm:px-5">Date Approved</th>
                                <th class="py-3 px-4 sm:px-5">Staff</th>
                                <th class="py-3 px-4 sm:px-5 text-right print:hidden">Contract</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                            @forelse($records as $app)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="py-3 px-4 sm:px-5 font-mono font-bold text-slate-900 dark:text-white">
                                        #APP-{{ str_pad($app->id, 4, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="py-3 px-4 sm:px-5">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $app->applicant_name ?? 'N/A' }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $app->applicant_email ?? '' }}</div>
                                    </td>
                                    <td class="py-3 px-4 sm:px-5">
                                        <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $app->pet ? $app->pet->name : 'Pet no. ' . $app->pet_id }}</div>
                                        <div class="text-[11px] text-slate-400 capitalize">{{ $app->pet ? ($app->pet->type ?? '') : '' }}</div>
                                    </td>
                                    <td class="py-3 px-4 sm:px-5 text-slate-500 whitespace-nowrap">
                                        {{ $app->approved_at ? \Carbon\Carbon::parse($app->approved_at)->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 sm:px-5 text-slate-600 dark:text-slate-400">
                                        {{ $app->staff_name ?? ($app->staff ? $app->staff->name : 'CAWS Staff') }}
                                    </td>
                                    <td class="py-3 px-4 sm:px-5 text-right print:hidden">
                                        <a href="{{ route('adoption-applications.contract', $app->id) }}" target="_blank"
                                            class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold text-[#199CA4] hover:text-white dark:text-[#41C1CB] hover:bg-[#199CA4] transition-colors">
                                            Contract PDF
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-slate-400 dark:text-slate-500 text-xs">
                                        No finalized adoptions in this period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    @endif

                </table>
            </div>

            {{-- Pagination footer if paginator object --}}
            @if(is_object($records) && method_exists($records, 'links') && $records->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-white/[0.06] print:hidden">
                    {{ $records->links() }}
                </div>
            @endif

        </div>

    </div>

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
