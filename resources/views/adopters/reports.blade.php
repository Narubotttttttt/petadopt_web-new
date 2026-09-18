<x-app-layout>
    <div class="w-full py-6 sm:py-8 px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="{
             showPhotoModal: false,
             photoModalUrl: '',
             photoModalTitle: '',
             photoModalDate: '',
             photoModalWeight: '',
             photoModalStatus: '',
             photoModalNotes: '',

             openPhotoModal(url, title, date, weight, status, notes) {
                 if (!url) return;
                 this.photoModalUrl = url;
                 this.photoModalTitle = title || 'Health Check-in Photo';
                 this.photoModalDate = date || '';
                 this.photoModalWeight = weight || '';
                 this.photoModalStatus = status || '';
                 this.photoModalNotes = notes || '';
                 this.showPhotoModal = true;
             },

             openFromCard(event) {
                 const target = event.currentTarget;
                 if (!target) return;
                 const url = target.getAttribute('data-photo');
                 if (!url) return;
                 this.photoModalUrl = url;
                 this.photoModalTitle = target.getAttribute('data-title') || 'Health Check-in Photo';
                 this.photoModalDate = target.getAttribute('data-date') || '';
                 this.photoModalWeight = target.getAttribute('data-weight') || '';
                 this.photoModalStatus = target.getAttribute('data-status') || '';
                 this.photoModalNotes = target.getAttribute('data-notes') || '';
                 this.showPhotoModal = true;
             }
         }">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="px-4 py-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 rounded-2xl flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 flex items-center justify-center text-emerald-600 dark:text-emerald-300 flex-shrink-0 font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-xs sm:text-sm font-semibold">{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-lg leading-none cursor-pointer">&times;</button>
            </div>
        @endif

        {{-- Section Navigation Tabs --}}
        <div class="flex items-center gap-2 border-b border-slate-200 dark:border-white/[0.08] pb-3">
            <a href="{{ route('adopters.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-extrabold transition shadow-xs bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08]">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                <span>Adopter Profiles</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-white/[0.08] text-slate-700 dark:text-slate-300">{{ $totalApprovedAdopters }}</span>
            </a>

            <a href="{{ route('adopters.reports') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-extrabold transition shadow-xs bg-[#199CA4] text-white">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Monthly Pet Updates</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white">{{ $totalReports }}</span>
            </a>
        </div>

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Monthly Pet Updates Report</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[#199CA4]/10 text-[#199CA4] dark:bg-[#199CA4]/20 dark:text-[#41C1CB] border border-[#199CA4]/20 dark:border-[#41C1CB]/30">
                        {{ $totalReports }} Check-in Reports
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Official post-adoption health reports, condition photos, and recorded weights submitted monthly by adopters via the mobile application.</p>
            </div>
            
            {{-- Header Actions --}}
            <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
                <a href="{{ route('adopters.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-bold hover:bg-slate-50 dark:hover:bg-[#171923] hover:text-[#199CA4] dark:hover:text-[#41C1CB] transition shadow-xs">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Adopter Profiles</span>
                </a>
            </div>
        </div>

        {{-- Metric Overview Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            {{-- 1. Total Reports --}}
            <a href="{{ route('adopters.reports', ['status' => 'all', 'period' => 'all']) }}"
               class="p-4 rounded-2xl border transition-all duration-200 flex flex-col justify-between {{ ($status ?? 'all') === 'all' && ($period ?? 'all') === 'all' ? 'bg-[#199CA4]/10 border-[#199CA4] dark:bg-[#199CA4]/15 dark:border-[#41C1CB] ring-2 ring-[#199CA4]/20' : 'bg-white dark:bg-[#12141C] border-slate-200/80 dark:border-white/[0.07] hover:border-slate-300 dark:hover:border-white/[0.15] hover:shadow-xs' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Updates</span>
                    <div class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black text-slate-900 dark:text-white">{{ $totalReports }}</span>
                    <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500">all time</span>
                </div>
            </a>

            {{-- 2. This Month Reports --}}
            <a href="{{ route('adopters.reports', ['period' => 'this_month']) }}"
               class="p-4 rounded-2xl border transition-all duration-200 flex flex-col justify-between {{ ($period ?? '') === 'this_month' ? 'bg-blue-50 border-blue-500 dark:bg-blue-950/30 dark:border-blue-500 ring-2 ring-blue-500/20' : 'bg-white dark:bg-[#12141C] border-slate-200/80 dark:border-white/[0.07] hover:border-slate-300 dark:hover:border-white/[0.15] hover:shadow-xs' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400">This Month</span>
                    <div class="w-7 h-7 rounded-xl bg-blue-100 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black text-blue-700 dark:text-blue-400">{{ $thisMonthReports }}</span>
                    <span class="text-[11px] font-semibold text-blue-600/70 dark:text-blue-500/70">submitted</span>
                </div>
            </a>

            {{-- 3. Healthy & Active --}}
            <a href="{{ route('adopters.reports', ['status' => 'healthy']) }}"
               class="p-4 rounded-2xl border transition-all duration-200 flex flex-col justify-between {{ ($status ?? '') === 'healthy' ? 'bg-emerald-50 border-emerald-500 dark:bg-emerald-950/30 dark:border-emerald-500 ring-2 ring-emerald-500/20' : 'bg-white dark:bg-[#12141C] border-slate-200/80 dark:border-white/[0.07] hover:border-slate-300 dark:hover:border-white/[0.15] hover:shadow-xs' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Healthy & Active</span>
                    <div class="w-7 h-7 rounded-xl bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black text-emerald-700 dark:text-emerald-400">{{ $healthyReports }}</span>
                    <span class="text-[11px] font-semibold text-emerald-600/70 dark:text-emerald-500/70">in good condition</span>
                </div>
            </a>

            {{-- 4. Attention Needed --}}
            <a href="{{ route('adopters.reports', ['status' => 'minor_issue']) }}"
               class="p-4 rounded-2xl border transition-all duration-200 flex flex-col justify-between {{ in_array(($status ?? ''), ['minor_issue', 'under_treatment']) ? 'bg-amber-50 border-amber-500 dark:bg-amber-950/30 dark:border-amber-500 ring-2 ring-amber-500/20' : 'bg-white dark:bg-[#12141C] border-slate-200/80 dark:border-white/[0.07] hover:border-slate-300 dark:hover:border-white/[0.15] hover:shadow-xs' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Needs Attention</span>
                    <div class="w-7 h-7 rounded-xl bg-amber-100 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black text-amber-700 dark:text-amber-400">{{ $attentionReports }}</span>
                    <span class="text-[11px] font-semibold text-amber-600/70 dark:text-amber-500/70">minor / treatment</span>
                </div>
            </a>
        </div>

        {{-- Search & Filter Toolbar --}}
        <div class="bg-white dark:bg-[#12141C] p-4 rounded-2xl sm:rounded-3xl shadow-xs border border-slate-200/80 dark:border-white/[0.07]">
            <form method="GET" action="{{ route('adopters.reports') }}" class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 sm:gap-4">
                
                {{-- Left filters (Status, Period, Species) --}}
                <div class="flex items-center gap-2.5 flex-wrap">
                    {{-- Status Filter --}}
                    <div>
                        <select name="status" onchange="this.form.submit()"
                                class="bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition cursor-pointer">
                            <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>All Health Statuses</option>
                            <option value="healthy" {{ ($status ?? '') === 'healthy' ? 'selected' : '' }}>Healthy & Active</option>
                            <option value="minor_issue" {{ ($status ?? '') === 'minor_issue' ? 'selected' : '' }}>Minor Issue</option>
                            <option value="under_treatment" {{ ($status ?? '') === 'under_treatment' ? 'selected' : '' }}>Under Treatment</option>
                        </select>
                    </div>

                    {{-- Period Filter --}}
                    <div>
                        <select name="period" onchange="this.form.submit()"
                                class="bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition cursor-pointer">
                            <option value="all" {{ ($period ?? 'all') === 'all' ? 'selected' : '' }}>All Time</option>
                            <option value="this_month" {{ ($period ?? '') === 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="last_month" {{ ($period ?? '') === 'last_month' ? 'selected' : '' }}>Last Month</option>
                            <option value="last_3_months" {{ ($period ?? '') === 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
                        </select>
                    </div>

                    {{-- Species Filter --}}
                    <div>
                        <select name="species" onchange="this.form.submit()"
                                class="bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition cursor-pointer">
                            <option value="all" {{ ($species ?? 'all') === 'all' ? 'selected' : '' }}>All Species</option>
                            <option value="dog" {{ ($species ?? '') === 'dog' ? 'selected' : '' }}>Dogs</option>
                            <option value="cat" {{ ($species ?? '') === 'cat' ? 'selected' : '' }}>Cats</option>
                        </select>
                    </div>

                    @if(($status ?? 'all') !== 'all' || ($period ?? 'all') !== 'all' || ($species ?? 'all') !== 'all' || !empty($search))
                        <a href="{{ route('adopters.reports') }}" class="text-xs font-bold text-[#199CA4] hover:underline ml-1 cursor-pointer">
                            Reset Filters
                        </a>
                    @endif
                </div>

                {{-- Search Bar --}}
                <div class="flex items-center gap-2 w-full lg:w-auto">
                    <div class="relative flex-1 lg:w-80">
                        <input type="text" 
                               name="search" 
                               value="{{ $search }}"
                                placeholder="Search by adopter, ADP code, pet, breed..." 
                                class="w-full pl-10 pr-8 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-slate-800 dark:text-slate-100 rounded-xl focus:bg-white dark:focus:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition placeholder-slate-400 dark:placeholder-slate-500 font-medium">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        @if(!empty($search))
                            <a href="{{ route('adopters.reports', request()->except('search', 'page')) }}" class="absolute right-3 top-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm font-bold">
                                &times;
                            </a>
                        @endif
                    </div>
                    <button type="submit" class="px-4 py-2 bg-[#199CA4] hover:bg-[#13787F] text-white text-xs sm:text-sm font-extrabold rounded-xl transition shadow-xs flex items-center gap-1.5 shrink-0 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <span>Search</span>
                    </button>
                </div>

            </form>
        </div>

        {{-- Reports Feed Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @forelse($reports as $report)
                @php
                    $pet = $report->pet;
                    $petName = $pet && $pet->name ? $pet->name : 'Pet no. ' . $report->pet_id;
                    $adopter = $report->user?->adoptersProfile;
                    $adopterName = $adopter?->full_name ?: ($report->user?->name ?: 'Adopter');
                    $adopterCode = $adopter?->adopter_code ?: ($report->user ? sprintf('ADP-%04d', $report->user->id) : 'ADP');
                    $adopterAvatar = $adopter?->avatar_url;
                    $adopterInitials = $adopter?->initials ?: strtoupper(substr($adopterName, 0, 2));
                    $adopterContact = $adopter?->phone ?: ($report->user?->email ?: 'Verified Adopter');
                    $photoUrl = $report->photo_url;
                    $checkInDateStr = $report->check_in_date ? $report->check_in_date->format('F d, Y') : $report->created_at->format('F d, Y');
                    $weightStr = $report->weight ? number_format($report->weight, 1) . ' kg' : 'Not recorded';
                @endphp

                <div class="bg-white dark:bg-[#12141C] rounded-2xl sm:rounded-3xl border border-slate-200/80 dark:border-white/[0.07] shadow-sm dark:shadow-xl dark:shadow-black/40 overflow-hidden hover:border-[#199CA4]/40 transition-all duration-200 flex flex-col justify-between">
                    
                    <div>
                        {{-- Card Header: Adopter Identity First --}}
                        <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-white/[0.06] bg-slate-50/50 dark:bg-[#171923] flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                {{-- Adopter Avatar / Initials --}}
                                <div class="w-11 h-11 rounded-xl overflow-hidden shrink-0 border border-slate-200 dark:border-white/[0.08] bg-slate-100 dark:bg-[#12141C] relative">
                                    @if($adopterAvatar)
                                        <img src="{{ $adopterAvatar }}" alt="{{ $adopterName }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-[#199CA4] to-[#13787F] text-white flex items-center justify-center font-black text-xs">
                                            {{ $adopterInitials }}
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white truncate leading-tight">
                                            {{ $adopterName }}
                                        </h3>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold font-mono bg-slate-200/80 dark:bg-white/[0.08] text-slate-700 dark:text-slate-300 border border-slate-300/60 dark:border-white/[0.08]">
                                            {{ $adopterCode }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5 font-medium">
                                        {{ $adopterContact }}
                                    </p>
                                </div>
                            </div>

                            {{-- Health Status Badge --}}
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold tracking-wide shrink-0 border
                                {{ $report->health_status === 'healthy' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60' : '' }}
                                {{ $report->health_status === 'minor_issue' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60' : '' }}
                                {{ $report->health_status === 'under_treatment' ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60' : '' }}
                                {{ !in_array($report->health_status, ['healthy', 'minor_issue', 'under_treatment']) ? 'bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 border-slate-200 dark:border-white/[0.08]' : '' }}">
                                {{ $report->health_status === 'healthy' ? 'Healthy & Active' : ($report->health_status === 'minor_issue' ? 'Minor Issue' : ($report->health_status === 'under_treatment' ? 'Under Treatment' : ucfirst($report->health_status ?? 'Submitted'))) }}
                            </span>
                        </div>

                        {{-- Adopted Pet Subheader Bar --}}
                        <div class="px-4 sm:px-5 py-2.5 bg-slate-100/70 dark:bg-[#0E1017] border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                {{-- Mini Pet Avatar --}}
                                <div class="w-6 h-6 rounded-lg overflow-hidden shrink-0 border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#171923]">
                                    @if($pet && $pet->photo_url)
                                        <img src="{{ $pet->photo_url }}" alt="{{ $petName }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-[10px]">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate">
                                    Adopted Pet: <span class="font-extrabold text-[#199CA4] dark:text-[#41C1CB]">{{ $petName }}</span>
                                </span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 truncate hidden sm:inline">
                                    ({{ $pet ? ucfirst($pet->type) . ' • ' . ($pet->breed ?? 'Mixed') : 'Adopted' }})
                                </span>
                            </div>
                            <span class="text-[11px] text-slate-400 font-medium whitespace-nowrap shrink-0">
                                {{ $report->created_at->diffForHumans() }}
                            </span>
                        </div>

                        {{-- Photo Preview Container --}}
                        <div class="relative w-full h-52 sm:h-56 bg-slate-100 dark:bg-[#0C0D13] overflow-hidden group {{ $photoUrl ? 'cursor-pointer' : '' }}"
                             @if($photoUrl)
                             data-photo="{{ $photoUrl }}"
                             data-title="{{ $adopterName }} - {{ $petName }} Monthly Check-in"
                             data-date="{{ $checkInDateStr }}"
                             data-weight="{{ $weightStr }}"
                             data-status="{{ $report->health_status }}"
                             data-notes="{{ $report->notes }}"
                             @click="openFromCard($event)"
                             @endif>
                            @if($photoUrl)
                                <img src="{{ $photoUrl }}" alt="{{ $adopterName }} - {{ $petName }} Check-in" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-1.5 text-white p-3">
                                    <svg class="w-7 h-7 drop-shadow-md" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                                    <span class="text-xs font-bold uppercase tracking-wider">Click to View Full Photo</span>
                                </div>
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 dark:text-slate-500 gap-2 p-4 text-center">
                                    <svg class="w-8 h-8 opacity-60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span class="text-xs font-semibold">No Check-in Photo Provided</span>
                                </div>
                            @endif

                            {{-- Floating Weight Pill --}}
                            @if($report->weight)
                                <div class="absolute bottom-3 left-3 px-3 py-1 rounded-xl bg-black/70 backdrop-blur-xs text-white text-xs font-extrabold flex items-center gap-1.5 border border-white/20 shadow-md">
                                    <svg class="w-3.5 h-3.5 text-[#41C1CB]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                                    <span>{{ $weightStr }}</span>
                                </div>
                            @endif

                            {{-- Floating Date Pill --}}
                            <div class="absolute top-3 right-3 px-2.5 py-1 rounded-lg bg-black/60 backdrop-blur-xs text-white text-[11px] font-bold border border-white/15">
                                {{ $checkInDateStr }}
                            </div>
                        </div>

                        {{-- Card Details: Remarks & Assessments --}}
                        <div class="p-4 sm:p-5 space-y-3">

                            {{-- Adopter Remarks / Notes --}}
                            @if(!empty($report->notes))
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06] text-xs text-slate-700 dark:text-slate-300 space-y-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Adopter Observations:</span>
                                    <p class="font-medium leading-relaxed italic">"{{ $report->notes }}"</p>
                                </div>
                            @else
                                <div class="p-3 rounded-xl bg-slate-50/50 dark:bg-[#171923]/50 border border-slate-100 dark:border-white/[0.04] text-xs text-slate-400 dark:text-slate-500 italic">
                                    No written remarks provided for this update.
                                </div>
                            @endif

                            {{-- Staff Remarks (if recorded) --}}
                            @if(!empty($report->staff_remarks))
                                <div class="p-2.5 rounded-xl bg-teal-50/70 dark:bg-[#199CA4]/10 border border-teal-200/70 dark:border-[#199CA4]/30 text-xs text-teal-900 dark:text-teal-200 space-y-0.5">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-teal-700 dark:text-teal-300 block">Staff Assessment:</span>
                                    <p class="font-medium">{{ $report->staff_remarks }}</p>
                                </div>
                            @endif

                        </div>
                    </div>

                    {{-- Card Footer --}}
                    <div class="p-3 sm:p-4 border-t border-slate-100 dark:border-white/[0.06] bg-slate-50/40 dark:bg-[#171923]/40 flex items-center justify-between gap-2">
                        @if($photoUrl)
                            <button type="button"
                                    data-photo="{{ $photoUrl }}"
                                    data-title="{{ $adopterName }} - {{ $petName }} Monthly Check-in"
                                    data-date="{{ $checkInDateStr }}"
                                    data-weight="{{ $weightStr }}"
                                    data-status="{{ $report->health_status }}"
                                    data-notes="{{ $report->notes }}"
                                    @click="openFromCard($event)"
                                    class="text-xs font-bold text-[#199CA4] hover:text-[#13787F] dark:text-[#41C1CB] hover:underline flex items-center gap-1 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Inspect Photo</span>
                            </button>
                        @else
                            <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>No Photo</span>
                            </span>
                        @endif

                        <a href="{{ route('adopters.index', ['search' => $adopterCode]) }}"
                           class="text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors flex items-center gap-1">
                            <span>Adopter Profile</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                </div>
            @empty
                <div class="col-span-full bg-white dark:bg-[#12141C] rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 dark:border-white/[0.07] p-12 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-white/[0.04] text-slate-400 flex items-center justify-center text-xl mx-auto mb-3 font-bold">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">No Monthly Updates Found</h3>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                        No monthly pet health updates matched your criteria. When adopters submit check-in reports via the mobile app, they will appear here.
                    </p>
                    @if(!empty($search) || ($status ?? 'all') !== 'all' || ($period ?? 'all') !== 'all' || ($species ?? 'all') !== 'all')
                        <div class="mt-4">
                            <a href="{{ route('adopters.reports') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 text-xs font-bold text-slate-700 dark:text-slate-200 transition">
                                Clear Filters & Search
                            </a>
                        </div>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($reports->hasPages())
            <div class="p-4 bg-white dark:bg-[#12141C] rounded-2xl border border-slate-200/80 dark:border-white/[0.07] shadow-xs">
                {{ $reports->links() }}
            </div>
        @endif

        {{-- Photo Zoom Modal --}}
        <div x-show="showPhotoModal" 
             x-cloak 
             class="fixed inset-0 z-[70] overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true"
             @keydown.escape.window="showPhotoModal = false">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/85 backdrop-blur-md" 
                     @click="showPhotoModal = false"
                     x-show="showPhotoModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"></div>

                <div class="inline-block w-full max-w-2xl p-5 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-[#12141C] rounded-3xl shadow-2xl border border-slate-200 dark:border-white/[0.1] relative z-10"
                     x-show="showPhotoModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.outside="showPhotoModal = false">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-start justify-between pb-3 mb-3 border-b border-slate-200/80 dark:border-white/[0.06] gap-3">
                        <div class="min-w-0">
                            <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white truncate" x-text="photoModalTitle"></h3>
                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium" x-text="photoModalDate"></span>
                                <template x-if="photoModalWeight">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB]">
                                        Weight: <span class="ml-1" x-text="photoModalWeight"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                        <button type="button" @click="showPhotoModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-2xl font-light leading-none cursor-pointer shrink-0">&times;</button>
                    </div>

                    {{-- High-Res Image Display --}}
                    <div class="relative w-full max-h-[65vh] rounded-2xl overflow-hidden bg-black/90 flex items-center justify-center border border-slate-200 dark:border-white/[0.08]">
                        <img :src="photoModalUrl" :alt="photoModalTitle" class="max-w-full max-h-[65vh] object-contain rounded-xl">
                    </div>

                    {{-- Observations Note if present --}}
                    <template x-if="photoModalNotes">
                        <div class="mt-3 p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06] text-xs text-slate-700 dark:text-slate-300">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-0.5">Adopter Observations:</span>
                            <p class="font-medium italic" x-text="'&quot;' + photoModalNotes + '&quot;'"></p>
                        </div>
                    </template>

                    {{-- Additional Modal Metadata & Actions --}}
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between gap-3 flex-wrap">
                        <a :href="photoModalUrl" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-[#199CA4] hover:text-[#13787F] dark:text-[#41C1CB] hover:underline inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span>Open Original Full Size</span>
                        </a>

                        <button type="button" @click="showPhotoModal = false" class="px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.1] rounded-xl transition cursor-pointer">
                            Close
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</x-app-layout>
