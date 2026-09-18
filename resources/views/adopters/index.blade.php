<x-app-layout>
    <div class="w-full py-6 sm:py-8 px-4 sm:px-6 lg:px-8 space-y-6" 
         x-data="{
             showAddModal: false,
             showHistoryModal: false,
             showHealthModal: false,
             showStatusModal: false,
             statusProfileId: null,
             statusAdopterName: '',
             selectedStatus: 'active',
             statusAdminNotes: '',
             healthLogs: [],
             healthPetName: '',
             healthAdopterName: '',
             modalPetId: null,
             modalPetName: '',
             modalAdopterName: '',
             selectedCategory: 'vaccination',
             historyLogs: [],
             historyPetName: '',
             historyAdopterName: '',
             showAvatarModal: false,
             avatarModalUrl: '',
             avatarModalName: '',
             avatarModalInitials: '',
             avatarModalIdCode: '',
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

             openAvatarModal(url, name, initials, idCode) {
                 this.avatarModalUrl = url || '';
                 this.avatarModalName = name || '';
                 this.avatarModalInitials = initials || '';
                 this.avatarModalIdCode = idCode || '';
                 this.showAvatarModal = true;
             },

             openStatusModal(profileId, adopterName, status, notes) {
                 this.statusProfileId = profileId;
                 this.statusAdopterName = adopterName;
                 this.selectedStatus = status || 'active';
                 this.statusAdminNotes = notes || '';
                 this.showStatusModal = true;
             },

             openAddModal(petId, petName, adopterName) {
                 this.modalPetId = petId;
                 this.modalPetName = petName;
                 this.modalAdopterName = adopterName;
                 this.selectedCategory = 'vaccination';
                 this.showAddModal = true;
             },

             openHistoryModal(logs, petName, adopterName) {
                 this.historyLogs = logs || [];
                 this.historyPetName = petName;
                 this.historyAdopterName = adopterName;
                 this.showHistoryModal = true;
             },

             openHealthModal(logs, petName, adopterName) {
                 this.healthLogs = logs || [];
                 this.healthPetName = petName;
                 this.healthAdopterName = adopterName;
                 this.showHealthModal = true;
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

        {{-- Adopters Section Navigation Tabs --}}
        <div class="flex items-center gap-2 border-b border-slate-200 dark:border-white/[0.08] pb-3">
            <a href="{{ route('adopters.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-extrabold transition shadow-xs bg-[#199CA4] text-white">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                <span>Adopter Profiles</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white">{{ $totalApprovedAdopters }}</span>
            </a>

            <a href="{{ route('adopters.reports') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-extrabold transition shadow-xs bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08]">
                <svg class="w-4 h-4 text-[#199CA4] dark:text-[#41C1CB]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Monthly Pet Updates</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#199CA4]/10 dark:bg-white/[0.08] text-[#199CA4] dark:text-[#41C1CB]">{{ $totalMonthlyReports ?? 0 }}</span>
            </a>
        </div>

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Adopter Profiles</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[#199CA4]/10 text-[#199CA4] dark:bg-[#199CA4]/20 dark:text-[#41C1CB] border border-[#199CA4]/20 dark:border-[#41C1CB]/30">
                        {{ $totalApprovedAdopters }} Adopters &bull; {{ $totalApprovedApplications }} Pets
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Manage approved adopters, shelter safety standing, and monthly health update compliance.</p>
            </div>
            
            {{-- Header Action --}}
            <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
                <a href="{{ route('adopters.reports') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white text-xs sm:text-sm font-extrabold transition shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Monthly Updates Report</span>
                </a>

                <a href="{{ route('adoption-applications.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-bold hover:bg-slate-50 dark:hover:bg-[#171923] hover:text-[#199CA4] dark:hover:text-[#41C1CB] transition shadow-xs">
                    <svg class="w-4 h-4 text-[#199CA4] dark:text-[#41C1CB]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span>Review Requests</span>
                </a>
            </div>
        </div>

        {{-- Metric Overview / Filter Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            {{-- 1. All Adopters --}}
            <a href="{{ route('adopters.index', array_merge(request()->except('filter', 'page'), ['filter' => 'all'])) }}"
               class="p-4 rounded-2xl border transition-all duration-200 flex flex-col justify-between {{ ($filter ?? 'all') === 'all' ? 'bg-[#199CA4]/10 border-[#199CA4] dark:bg-[#199CA4]/15 dark:border-[#41C1CB] ring-2 ring-[#199CA4]/20' : 'bg-white dark:bg-[#12141C] border-slate-200/80 dark:border-white/[0.07] hover:border-slate-300 dark:hover:border-white/[0.15] hover:shadow-xs' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">All Adopters</span>
                    <div class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black text-slate-900 dark:text-white">{{ $totalApprovedAdopters }}</span>
                    <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500">({{ $totalApprovedApplications }} pets)</span>
                </div>
            </a>

            {{-- 2. Active & Compliant --}}
            <a href="{{ route('adopters.index', array_merge(request()->except('filter', 'page'), ['filter' => 'active'])) }}"
               class="p-4 rounded-2xl border transition-all duration-200 flex flex-col justify-between {{ ($filter ?? '') === 'active' ? 'bg-emerald-50 border-emerald-500 dark:bg-emerald-950/30 dark:border-emerald-500 ring-2 ring-emerald-500/20' : 'bg-white dark:bg-[#12141C] border-slate-200/80 dark:border-white/[0.07] hover:border-slate-300 dark:hover:border-white/[0.15] hover:shadow-xs' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Active / Good</span>
                    <div class="w-7 h-7 rounded-xl bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black text-emerald-700 dark:text-emerald-400">{{ $activeCount ?? 0 }}</span>
                    <span class="text-[11px] font-semibold text-emerald-600/70 dark:text-emerald-500/70">compliant</span>
                </div>
            </a>

            {{-- 3. Reports Overdue --}}
            <a href="{{ route('adopters.index', array_merge(request()->except('filter', 'page'), ['filter' => 'inactive'])) }}"
               class="p-4 rounded-2xl border transition-all duration-200 flex flex-col justify-between {{ ($filter ?? '') === 'inactive' ? 'bg-rose-50 border-rose-500 dark:bg-rose-950/30 dark:border-rose-500 ring-2 ring-rose-500/20' : 'bg-white dark:bg-[#12141C] border-slate-200/80 dark:border-white/[0.07] hover:border-slate-300 dark:hover:border-white/[0.15] hover:shadow-xs' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400">Reports Overdue</span>
                    <div class="w-7 h-7 rounded-xl bg-rose-100 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black text-rose-700 dark:text-rose-400">{{ $inactiveCount ?? 0 }}</span>
                    <span class="text-[11px] font-semibold text-rose-600/70 dark:text-rose-500/70">pending updates</span>
                </div>
            </a>

            {{-- 4. Restricted / Blacklisted --}}
            <a href="{{ route('adopters.index', array_merge(request()->except('filter', 'page'), ['filter' => 'restricted'])) }}"
               class="p-4 rounded-2xl border transition-all duration-200 flex flex-col justify-between {{ ($filter ?? '') === 'restricted' ? 'bg-amber-50 border-amber-500 dark:bg-amber-950/30 dark:border-amber-500 ring-2 ring-amber-500/20' : 'bg-white dark:bg-[#12141C] border-slate-200/80 dark:border-white/[0.07] hover:border-slate-300 dark:hover:border-white/[0.15] hover:shadow-xs' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Restricted / Ban</span>
                    <div class="w-7 h-7 rounded-xl bg-amber-100 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black text-amber-700 dark:text-amber-400">{{ $restrictedCount ?? 0 }}</span>
                    <span class="text-[11px] font-semibold text-amber-600/70 dark:text-amber-500/70">sanctioned</span>
                </div>
            </a>

            {{-- 5. Monthly Pet Reports --}}
            <a href="{{ route('adopters.reports') }}"
               class="col-span-2 sm:col-span-1 p-4 rounded-2xl border transition-all duration-200 flex flex-col justify-between bg-white dark:bg-[#12141C] border-slate-200/80 dark:border-white/[0.07] hover:border-[#199CA4] dark:hover:border-[#41C1CB] hover:shadow-xs group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#199CA4] dark:text-[#41C1CB]">Monthly Reports</span>
                    <div class="w-7 h-7 rounded-xl bg-[#199CA4]/10 dark:bg-white/[0.06] text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black text-[#199CA4] dark:text-[#41C1CB]">{{ $totalMonthlyReports ?? 0 }}</span>
                    <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500">submitted check-ins</span>
                </div>
            </a>
        </div>

        {{-- Search & Controls Toolbar --}}
        <div class="bg-white dark:bg-[#12141C] p-4 rounded-2xl sm:rounded-3xl shadow-xs border border-slate-200/80 dark:border-white/[0.07] flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Filter Applied:</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08]">
                    {{ ucfirst($filter ?? 'all') }}
                </span>
                @if(($filter ?? 'all') !== 'all' || !empty($search))
                    <a href="{{ route('adopters.index') }}" class="text-xs font-bold text-[#199CA4] hover:underline ml-1 cursor-pointer">Reset Filters</a>
                @endif
            </div>

            {{-- Search Bar --}}
            <form method="GET" action="{{ route('adopters.index') }}" class="flex items-center gap-2 w-full md:w-auto">
                @if(request('filter'))
                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                @endif
                <div class="relative flex-1 md:w-80">
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}"
                           placeholder="Search by ADP ID, name, email, pet..." 
                           class="w-full pl-10 pr-8 py-2.5 text-xs sm:text-sm bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-slate-800 dark:text-slate-100 rounded-xl focus:bg-white dark:focus:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition placeholder-slate-400 dark:placeholder-slate-500 font-medium">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    @if($search)
                        <a href="{{ route('adopters.index', request()->except('search', 'page')) }}" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm font-bold">
                            &times;
                        </a>
                    @endif
                </div>
                <button type="submit" class="px-5 py-2.5 bg-[#199CA4] hover:bg-[#13787F] text-white text-xs sm:text-sm font-extrabold rounded-xl transition shadow-xs flex items-center gap-1.5 shrink-0 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <span>Search</span>
                </button>
            </form>
        </div>

        {{-- Main Adopters Structured Card Feed --}}
        <div class="space-y-6">
            @forelse($adopters as $adopter)
                <div x-data="{ showPets: false }"
                     class="bg-white dark:bg-[#12141C] rounded-2xl sm:rounded-3xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] overflow-hidden transition-all duration-200 hover:border-slate-300 dark:hover:border-white/[0.12]">
                    
                    {{-- 1. Adopter Profile Header Bar --}}
                    <div class="p-5 sm:p-6 bg-slate-50/70 dark:bg-[#171923] border-b border-slate-200/80 dark:border-white/[0.06]">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            
                            {{-- Adopter Identity & Status --}}
                            <div class="flex items-start sm:items-center gap-4">
                                @php
                                    $adopterNameParts = preg_split('/\s+/', trim($adopter->applicant_name));
                                    $adopterInitials = count($adopterNameParts) >= 2 
                                        ? strtoupper(mb_substr($adopterNameParts[0], 0, 1) . mb_substr(end($adopterNameParts), 0, 1))
                                        : strtoupper(mb_substr($adopter->applicant_name, 0, 1));
                                    $adopterAvatarUrl = $adopter->avatar ?? '';
                                @endphp

                                <button type="button" 
                                    @click='openAvatarModal(@json($adopterAvatarUrl), @json($adopter->applicant_name), @json($adopterInitials), @json($adopter->adopter_id_code))'
                                    class="relative group rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#199CA4] transition cursor-pointer flex-shrink-0"
                                    title="Click to view profile picture">
                                    @if($adopter->avatar)
                                        <img src="{{ $adopter->avatar }}" alt="{{ $adopter->applicant_name }}" class="w-14 h-14 rounded-2xl object-cover border-2 border-white dark:border-[#12141C] shadow-sm group-hover:scale-105 group-hover:ring-2 group-hover:ring-[#199CA4] transition-all duration-200">
                                        <div class="absolute inset-0 bg-black/30 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white">
                                            <svg class="w-4 h-4 drop-shadow-sm" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                                        </div>
                                    @else
                                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-white/[0.08] dark:to-white/[0.02] text-slate-800 dark:text-slate-200 flex items-center justify-center font-black text-lg border border-slate-200 dark:border-white/[0.08] shadow-xs group-hover:scale-105 group-hover:ring-2 group-hover:ring-[#199CA4] transition-all duration-200">
                                            {{ $adopterInitials }}
                                        </div>
                                    @endif
                                </button>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2.5 flex-wrap">
                                        <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white leading-tight">
                                            {{ $adopter->applicant_name }}
                                        </h2>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-mono font-extrabold bg-slate-200/80 dark:bg-white/[0.08] text-slate-700 dark:text-slate-300 border border-slate-300/60 dark:border-white/[0.1]">
                                            {{ $adopter->adopter_id_code }}
                                        </span>

                                        {{-- Adopter Standing Badge --}}
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold border {{ $adopter->badge_theme === 'rose' ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60' : ($adopter->badge_theme === 'amber' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60') }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $adopter->badge_theme === 'rose' ? 'bg-rose-500' : ($adopter->badge_theme === 'amber' ? 'bg-amber-500' : 'bg-emerald-500') }} animate-pulse"></span>
                                            {{ $adopter->status_label }}
                                        </span>
                                    </div>

                                    {{-- Contact & Location Info --}}
                                    <div class="flex items-center gap-x-4 gap-y-1.5 flex-wrap text-xs text-slate-500 dark:text-slate-400 mt-2 font-medium">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            <span class="text-slate-700 dark:text-slate-300">{{ $adopter->applicant_email }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $adopter->applicant_phone }}</span>
                                        </div>
                                        @if(!empty($adopter->address))
                                            <div class="flex items-center gap-1.5 max-w-sm truncate" title="{{ $adopter->address }}">
                                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <span class="truncate">{{ $adopter->address }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Actions & Dropdown Pet Toggle Button --}}
                            <div class="flex items-center gap-2.5 sm:self-start lg:self-center shrink-0">
                                {{-- Interactive Dropdown Button for Adopted Pets --}}
                                <button type="button"
                                    @click="showPets = !showPets"
                                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-extrabold transition shadow-xs cursor-pointer border"
                                    :class="showPets 
                                        ? 'bg-[#199CA4] text-white border-[#199CA4] shadow-sm' 
                                        : 'bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.06] dark:hover:bg-white/[0.1] text-slate-800 dark:text-slate-200 border-slate-200 dark:border-white/[0.08]'">
                                    <svg class="w-4 h-4 text-current" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                    <span>{{ $adopter->pets_count }} {{ Str::plural('Adopted Pet', $adopter->pets_count) }}</span>
                                    <svg class="w-4 h-4 transition-transform duration-200 shrink-0" 
                                         :class="showPets ? 'rotate-180' : ''" 
                                         fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                @if($adopter->profile_id && Auth::user()?->role === 'admin')
                                    <button type="button" 
                                        @click='openStatusModal(@json($adopter->profile_id), @json($adopter->applicant_name), @json($adopter->status ?? "active"), @json($adopter->admin_notes ?? ""))'
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-200 text-xs font-bold hover:bg-slate-100 dark:hover:bg-[#1D1F2C] transition shadow-2xs cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-[#199CA4] dark:text-[#41C1CB]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        <span>Edit Status & Sanctions</span>
                                    </button>
                                @endif
                            </div>

                        </div>

                        {{-- Sanction / Shelter Safety Notes Alert Banner --}}
                        @if(!empty($adopter->admin_notes))
                            <div class="mt-4 p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/40 text-amber-900 dark:text-amber-200 text-xs flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <div>
                                    <span class="font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wider text-[10px] block">Shelter Remark / Official Sanction Note:</span>
                                    <p class="font-medium mt-0.5">{{ $adopter->admin_notes }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- 2. Adopted Pets & Clinical Records Dropdown Table --}}
                    <div x-show="showPets" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="overflow-x-auto border-t border-slate-200/80 dark:border-white/[0.06] bg-slate-50/30 dark:bg-black/10">
                        <table class="w-full text-left border-collapse min-w-[850px]">
                            <thead class="bg-slate-100/70 dark:bg-[#12141C] border-b border-slate-200/80 dark:border-white/[0.06] text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <tr>
                                    <th class="py-3 px-5 w-72">Adopted Pet</th>
                                    <th class="py-3 px-5 w-72">Clinical History</th>
                                    <th class="py-3 px-5 text-center w-52">Monthly Health Reports</th>
                                    <th class="py-3 px-5 text-right w-48">Clinical Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                                @foreach($adopter->applications as $app)
                                    @php 
                                        $pet = $app->pet; 
                                        $petImg = $pet && $pet->photo_path ? asset('storage/' . ltrim($pet->photo_path, '/')) : ($pet ? $pet->primary_image_url : null);
                                        $petNameStr = $pet && $pet->name ? $pet->name : 'Pet no. '.$app->pet_id;

                                        // Medical logs & vaccination status
                                        $latestLog = $pet ? $pet->medicalLogs->first() : null;
                                        $vaccineLogs = $pet ? $pet->medicalLogs->where('category', 'vaccination') : collect();
                                        $latestVaccine = $vaccineLogs->first();

                                        // Monthly health updates
                                        $healthUpdates = $pet ? $pet->healthUpdates : collect();
                                        $latestCheckin = $healthUpdates->first();
                                        $healthData = $healthUpdates->map(function($h) {
                                            return [
                                                'id' => $h->id,
                                                'photo_url' => $h->photo_path ? asset('storage/' . ltrim($h->photo_path, '/')) : ($h->photo_url ?? null),
                                                'health_status' => $h->health_status,
                                                'weight' => $h->weight,
                                                'notes' => $h->notes,
                                                'check_in_date' => $h->check_in_date ? $h->check_in_date->format('M d, Y') : '',
                                            ];
                                        })->values()->toArray();

                                        // History logs for timeline modal
                                        $historyData = $pet ? $pet->medicalLogs->map(function($m) {
                                            return [
                                                'id' => $m->id,
                                                'category' => $m->category,
                                                'name' => ucfirst($m->category),
                                                'date' => $m->date ? $m->date->format('M d, Y') : '',
                                                'next_due_date' => $m->next_due_date ? $m->next_due_date->format('M d, Y') : null,
                                                'administered_by' => $m->administered_by ?: ($m->creator ? $m->creator->name : 'Staff'),
                                            ];
                                        })->values()->toArray() : [];
                                    @endphp

                                    <tr class="hover:bg-slate-50/70 dark:hover:bg-[#181A24]/60 transition-colors">
                                        
                                        {{-- Pet Profile Column --}}
                                        <td class="py-3.5 px-5 align-middle">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 rounded-2xl overflow-hidden shrink-0 border border-slate-200 dark:border-white/[0.08] shadow-2xs relative bg-slate-100 dark:bg-[#171923]">
                                                    @if($petImg)
                                                        <img src="{{ $petImg }}" alt="{{ $petNameStr }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                        <div class="hidden w-full h-full bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] items-center justify-center font-bold text-sm">
                                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                                        </div>
                                                    @else
                                                        <div class="w-full h-full bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center">
                                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-extrabold text-slate-900 dark:text-white text-sm truncate block leading-tight">
                                                            {{ $petNameStr }}
                                                        </span>
                                                        @if($app->contract_pdf_path)
                                                            <a href="{{ route('adoption-applications.contract', $app->id) }}" title="Download Adoption Agreement PDF" target="_blank"
                                                                class="p-1 rounded-md text-slate-400 hover:text-[#199CA4] dark:hover:text-[#41C1CB] hover:bg-slate-100 dark:hover:bg-white/[0.06] transition shrink-0">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                            </a>
                                                        @endif
                                                    </div>
                                                    <span class="text-xs text-slate-500 dark:text-slate-400 block truncate mt-0.5 font-medium">
                                                        {{ $pet ? (ucfirst($pet->type) . ' • ' . $pet->breed) : 'Adopted' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Clinical History Column --}}
                                        <td class="py-3.5 px-5 align-middle">
                                            <div class="flex flex-col gap-1">
                                                @if($latestLog)
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold {{ $latestLog->category === 'vaccination' ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60' }}">
                                                            {{ $latestLog->category === 'vaccination' ? 'Vaccine' : 'Deworming' }}
                                                        </span>
                                                        <span class="text-xs text-slate-600 dark:text-slate-300 font-semibold truncate">{{ $latestLog->date ? $latestLog->date->format('M d, Y') : '' }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-xs text-slate-400 dark:text-slate-500 italic">No medical logs</span>
                                                @endif

                                                @if($latestVaccine && $latestVaccine->next_due_date)
                                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                                        Scheduled: {{ $latestVaccine->next_due_date->format('M d, Y') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Monthly Reports Column --}}
                                        <td class="py-3.5 px-5 align-middle text-center">
                                            <div class="inline-flex flex-col items-center gap-1">
                                                <button type="button"
                                                    @click='openHealthModal(@json($healthData), @json($petNameStr), @json($adopter->applicant_name))'
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold text-xs transition cursor-pointer {{ $healthUpdates->count() > 0 ? 'bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] hover:bg-[#199CA4]/20 dark:hover:bg-[#199CA4]/30 border border-[#199CA4]/20 dark:border-[#41C1CB]/30' : 'bg-slate-100 dark:bg-white/[0.04] text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-white/[0.08] border border-slate-200 dark:border-white/[0.08]' }}">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    <span>Reports ({{ $healthUpdates->count() }})</span>
                                                </button>

                                                @if(!empty($app->is_report_overdue))
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 px-2 py-0.5 rounded-md border border-rose-200 dark:border-rose-800/40">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                                        Overdue ({{ $app->report_overdue_days }}d)
                                                    </span>
                                                @elseif(isset($app->report_due_days))
                                                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">
                                                        Due in {{ $app->report_due_days }}d
                                                    </span>
                                                @else
                                                    <span class="text-[10px] text-slate-400 dark:text-slate-500">
                                                        {{ $latestCheckin && $latestCheckin->check_in_date ? 'Last: ' . $latestCheckin->check_in_date->format('M d') : 'No check-ins' }}
                                                    </span>
                                                @endif

                                                <a href="{{ route('adopters.reports', ['search' => $pet && $pet->name ? $pet->name : $adopter->applicant_name]) }}" 
                                                   class="text-[10px] font-bold text-[#199CA4] hover:underline dark:text-[#41C1CB] mt-0.5">
                                                    View All Reports &rarr;
                                                </a>
                                            </div>
                                        </td>

                                        {{-- Clinical Actions Column --}}
                                        <td class="py-3.5 px-5 align-middle text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button" 
                                                    @click='openHistoryModal(@json($historyData), @json($petNameStr), @json($adopter->applicant_name))'
                                                    class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/[0.08] text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.06] transition shadow-2xs cursor-pointer inline-flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span>History ({{ count($historyData) }})</span>
                                                </button>

                                                <button type="button" 
                                                    @click='openAddModal(@json($app->pet_id), @json($petNameStr), @json($adopter->applicant_name))'
                                                    class="px-3.5 py-1.5 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white text-xs font-extrabold transition shadow-xs flex items-center gap-1 cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                                    <span>Record</span>
                                                </button>
                                            </div>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            @empty
                <div class="bg-white dark:bg-[#12141C] rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 dark:border-white/[0.07] p-12 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-white/[0.04] text-slate-400 flex items-center justify-center text-xl mx-auto mb-3 font-bold">
                        <svg class="w-7 h-7 text-slate-400" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">No Adopter Records Found</h3>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">When adoption applications are approved, the adopters and their clinical history will appear here.</p>
                    @if(!empty($search) || ($filter ?? 'all') !== 'all')
                        <div class="mt-4">
                            <a href="{{ route('adopters.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 text-xs font-bold text-slate-700 dark:text-slate-200 transition">
                                Clear Filters & Search
                            </a>
                        </div>
                    @endif
                </div>
            @endforelse

            {{-- Pagination --}}
            @if($adopters->hasPages())
                <div class="p-4 bg-white dark:bg-[#12141C] rounded-2xl border border-slate-200/80 dark:border-white/[0.07] shadow-xs">
                    {{ $adopters->links() }}
                </div>
            @endif
        </div>

        {{-- Modal 1: Add Medical Log Modal --}}
        <div x-show="showAddModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-xs" @click="showAddModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block px-5 sm:px-6 pt-5 pb-6 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-[#12141C] rounded-3xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 dark:border-white/[0.08]">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-slate-200/80 dark:border-white/[0.06]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#199CA4] to-[#13787F] text-white flex items-center justify-center text-sm font-bold shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white">Record Medical Log</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Pet: <span class="font-bold text-[#199CA4] dark:text-[#41C1CB]" x-text="modalPetName"></span> | Adopter: <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="modalAdopterName"></span>
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-2xl font-light leading-none cursor-pointer">&times;</button>
                    </div>

                    {{-- Modal Form --}}
                    <form method="POST" action="{{ route('medical-logs.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="pet_id" :value="modalPetId">

                        {{-- Category Selector --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Record Type</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" 
                                    @click="selectedCategory = 'vaccination'"
                                    :class="selectedCategory === 'vaccination' ? 'bg-[#199CA4] text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/[0.1]'"
                                    class="py-2.5 rounded-xl font-bold text-xs transition cursor-pointer">
                                     Vaccination
                                </button>
                                <button type="button" 
                                    @click="selectedCategory = 'deworming'"
                                    :class="selectedCategory === 'deworming' ? 'bg-[#199CA4] text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/[0.1]'"
                                    class="py-2.5 rounded-xl font-bold text-xs transition cursor-pointer">
                                     Deworming
                                </button>
                            </div>
                            <input type="hidden" name="category" :value="selectedCategory">
                        </div>

                        {{-- Dynamic Fields --}}
                        <div x-show="selectedCategory === 'vaccination'" class="space-y-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Vaccine Name *</label>
                                <input type="text" name="vaccine_name" placeholder="e.g. Anti-Rabies, 5-in-1, DHPP" 
                                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-slate-800 dark:text-white rounded-xl focus:bg-white dark:focus:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition placeholder-slate-400 dark:placeholder-slate-500 font-medium">
                            </div>
                        </div>

                        <div x-show="selectedCategory === 'deworming'" class="space-y-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Deworming Brand / Medicine *</label>
                                <input type="text" name="deworming_name" placeholder="e.g. Canex, Pyrantel, Drontal" 
                                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-slate-800 dark:text-white rounded-xl focus:bg-white dark:focus:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition placeholder-slate-400 dark:placeholder-slate-500 font-medium">
                            </div>
                        </div>

                        {{-- Date & Next Due Date --}}
                        <div class="grid grid-cols-2 gap-3" x-data="{
                            administeredDate: '{{ date('Y-m-d') }}',
                            nextDueDate: '',
                            calcDueDate(months) {
                                if (!this.administeredDate) return '';
                                const d = new Date(this.administeredDate + 'T00:00:00');
                                if (isNaN(d.getTime())) return '';
                                d.setMonth(d.getMonth() + months);
                                const yyyy = d.getFullYear();
                                const mm = String(d.getMonth() + 1).padStart(2, '0');
                                const dd = String(d.getDate()).padStart(2, '0');
                                return `${yyyy}-${mm}-${dd}`;
                            },
                            get formattedDueDate() {
                                if (!this.nextDueDate) {
                                    return (selectedCategory === 'deworming') ? 'None (Optional)' : 'Select date';
                                }
                                const d = new Date(this.nextDueDate + 'T00:00:00');
                                if (isNaN(d.getTime())) return this.nextDueDate;
                                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                            },
                            updateForCategory(cat) {
                                if (cat === 'vaccination') {
                                    this.nextDueDate = this.calcDueDate(6);
                                } else {
                                    this.nextDueDate = '';
                                }
                            },
                            init() {
                                this.updateForCategory(selectedCategory);
                                this.$watch('selectedCategory', (val) => {
                                    this.updateForCategory(val);
                                });
                                this.$watch('administeredDate', () => {
                                    if (selectedCategory === 'vaccination') {
                                        this.nextDueDate = this.calcDueDate(6);
                                    }
                                });
                            }
                        }">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Date Administered *</label>
                                <input type="date" name="date" x-model="administeredDate" required
                                    class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-slate-800 dark:text-white rounded-xl focus:bg-white dark:focus:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition font-medium">
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        <span x-show="selectedCategory === 'vaccination'">Next Due Date</span>
                                        <span x-show="selectedCategory === 'deworming'">Next Due (Optional)</span>
                                    </label>
                                    <template x-if="selectedCategory === 'deworming' && !nextDueDate">
                                        <button type="button" @click="nextDueDate = calcDueDate(3)" class="text-[10px] text-[#199CA4] hover:underline font-bold cursor-pointer">+3 Months</button>
                                    </template>
                                    <template x-if="selectedCategory === 'deworming' && nextDueDate">
                                        <button type="button" @click="nextDueDate = ''" class="text-[10px] text-rose-500 hover:underline font-bold cursor-pointer">Clear</button>
                                    </template>
                                </div>
                                <div class="relative flex items-center">
                                    <input type="date" name="next_due_date" x-model="nextDueDate"
                                        class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-transparent focus:text-slate-800 dark:focus:text-white rounded-xl focus:bg-white dark:focus:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition cursor-pointer font-medium">
                                    <span class="absolute left-3 pointer-events-none text-xs font-bold"
                                        :class="nextDueDate ? 'text-[#199CA4] dark:text-[#41C1CB]' : 'text-slate-400 dark:text-slate-500'"
                                        x-text="formattedDueDate"></span>
                                </div>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">
                                    <span x-show="selectedCategory === 'vaccination'" class="font-semibold text-[#199CA4] dark:text-[#41C1CB]">Auto-calculated: 6 months ahead</span>
                                    <span x-show="selectedCategory === 'deworming'" class="font-normal text-slate-400">Optional for deworming routine</span>
                                </p>
                            </div>
                        </div>

                        {{-- Administered By (Auto-detected) --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Administered By (Staff / Admin)</label>
                            <div class="flex items-center justify-between px-3.5 py-2.5 bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/40 rounded-xl">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="text-xs font-bold text-emerald-900 dark:text-emerald-300">{{ Auth::user()?->name }}</span>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/80 text-emerald-800 dark:text-emerald-200 uppercase">{{ Auth::user()?->role }}</span>
                                </div>
                                <span class="text-[11px] text-emerald-700 dark:text-emerald-400 font-medium">Logged in staff</span>
                            </div>
                            <input type="hidden" name="administered_by" value="{{ Auth::user()?->name }}">
                        </div>

                        {{-- Modal Actions --}}
                        <div class="pt-3 border-t border-slate-200/80 dark:border-white/[0.06] flex items-center justify-end gap-2.5">
                            <button type="button" @click="showAddModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.06] rounded-xl transition cursor-pointer">Cancel</button>
                            <button type="submit" class="px-5 py-2 text-xs sm:text-sm font-extrabold text-white bg-[#199CA4] hover:bg-[#13787F] rounded-xl shadow-xs transition cursor-pointer">Save Record</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal 2: History Timeline Modal --}}
        <div x-show="showHistoryModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-xs" @click="showHistoryModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block px-5 sm:px-6 pt-5 pb-6 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-[#12141C] rounded-3xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-white/[0.08]">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-slate-200/80 dark:border-white/[0.06]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#199CA4] to-[#13787F] text-white flex items-center justify-center text-sm font-bold shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white">Medical History Timeline</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Pet: <span class="font-bold text-[#199CA4] dark:text-[#41C1CB]" x-text="historyPetName"></span> | Adopter: <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="historyAdopterName"></span>
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="showHistoryModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-2xl font-light leading-none cursor-pointer">&times;</button>
                    </div>

                    {{-- Timeline Content --}}
                    <div class="max-h-[420px] overflow-y-auto pr-1 space-y-3.5">
                        <template x-if="historyLogs.length === 0">
                            <div class="py-10 text-center">
                                <div class="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-white/[0.04] text-slate-400 flex items-center justify-center text-sm mx-auto mb-2 font-bold">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300">No medical records registered yet</p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Click "+ Record" on the table to add the first vaccine or deworming entry.</p>
                            </div>
                        </template>

                        <div class="space-y-3" x-show="historyLogs.length > 0">
                            <template x-for="log in historyLogs" :key="log.id">
                                <div class="p-3.5 rounded-2xl border border-slate-200 dark:border-white/[0.06] bg-slate-50/70 dark:bg-[#171923] hover:bg-white dark:hover:bg-[#1D1F2C] hover:border-[#199CA4]/30 hover:shadow-xs transition">
                                    <div class="flex items-center justify-between gap-2 mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                                :class="log.category === 'vaccination' ? 'bg-blue-100 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60' : 'bg-emerald-100 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60'"
                                                x-text="log.category === 'vaccination' ? 'Vaccination' : 'Deworming'">
                                            </span>
                                            <span class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white" x-text="log.name"></span>
                                        </div>
                                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400" x-text="log.date"></span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        <span x-show="log.administered_by" class="font-semibold text-slate-700 dark:text-slate-300" x-text="'Administered by: ' + log.administered_by"></span>
                                        <span x-show="log.next_due_date" class="font-semibold text-amber-700 dark:text-amber-400" x-text="'Next Due: ' + log.next_due_date"></span>
                                    </div>
                                    <p x-show="log.notes" class="text-xs text-slate-600 dark:text-slate-300 mt-1.5 bg-white dark:bg-[#0C0D13] p-2 rounded-lg border border-slate-200/80 dark:border-white/[0.06]" x-text="log.notes"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-3.5 mt-3.5 border-t border-slate-200/80 dark:border-white/[0.06] flex justify-end">
                        <button type="button" @click="showHistoryModal = false" class="px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.1] rounded-xl transition cursor-pointer">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal 3: Monthly Pet Health Reports Modal --}}
        <div x-show="showHealthModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-xs" @click="showHealthModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block px-5 sm:px-6 pt-5 pb-6 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-[#12141C] rounded-3xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-white/[0.08]">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-slate-200/80 dark:border-white/[0.06]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#199CA4] to-[#13787F] text-white flex items-center justify-center text-sm font-bold shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white">Monthly Pet Health Reports</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Pet: <span class="font-bold text-[#199CA4] dark:text-[#41C1CB]" x-text="healthPetName"></span> | Adopter: <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="healthAdopterName"></span>
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="showHealthModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-2xl font-light leading-none cursor-pointer">&times;</button>
                    </div>

                    {{-- Reports Content --}}
                    <div class="max-h-[420px] overflow-y-auto pr-1 space-y-3.5">
                        <template x-if="healthLogs.length === 0">
                            <div class="py-10 text-center">
                                <div class="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-white/[0.04] text-slate-400 flex items-center justify-center text-sm mx-auto mb-2 font-bold">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <p class="text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300">No monthly check-ins submitted yet</p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">When the adopter submits monthly photos and health updates via the mobile app, they will appear here.</p>
                            </div>
                        </template>

                        <div class="space-y-3.5" x-show="healthLogs.length > 0">
                            <template x-for="item in healthLogs" :key="item.id">
                                <div class="p-4 rounded-2xl border border-slate-200 dark:border-white/[0.06] bg-slate-50/70 dark:bg-[#171923] hover:bg-white dark:hover:bg-[#1D1F2C] hover:border-[#199CA4]/30 hover:shadow-xs transition flex flex-col sm:flex-row gap-4">
                                    {{-- Pet Photo Thumbnail with Zoom Trigger --}}
                                    <div class="relative w-full sm:w-32 h-44 sm:h-32 rounded-xl overflow-hidden bg-slate-200 dark:bg-[#0C0D13] flex-shrink-0 border border-slate-200 dark:border-white/[0.08] group cursor-pointer"
                                         @click="openPhotoModal(item.photo_url, healthPetName + ' Check-in', item.check_in_date, item.weight, item.health_status, item.notes)"
                                         title="Click to view full photo">
                                        <img :src="item.photo_url" alt="Pet Checkin" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-1 text-white p-2">
                                            <svg class="w-6 h-6 drop-shadow-md" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                                            <span class="text-[10px] font-extrabold tracking-wide uppercase">View Full Photo</span>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1 space-y-2">
                                        <div class="flex items-center justify-between gap-2 flex-wrap">
                                            <span class="text-sm font-extrabold text-slate-900 dark:text-white" x-text="item.check_in_date"></span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                                :class="{
                                                    'bg-emerald-100 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60': item.health_status === 'healthy',
                                                    'bg-amber-100 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60': item.health_status === 'minor_issue',
                                                    'bg-rose-100 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60': item.health_status === 'under_treatment'
                                                }"
                                                x-text="item.health_status === 'healthy' ? 'Healthy & Active' : (item.health_status === 'minor_issue' ? 'Minor Issue' : 'Under Treatment')">
                                            </span>
                                        </div>
                                        <template x-if="item.weight">
                                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                Recorded Weight: <span class="font-extrabold text-[#199CA4] dark:text-[#41C1CB]" x-text="item.weight + ' kg'"></span>
                                            </p>
                                        </template>
                                        <template x-if="item.notes">
                                            <div class="text-xs text-slate-700 dark:text-slate-300 bg-white dark:bg-[#0C0D13] p-2.5 rounded-xl border border-slate-200/80 dark:border-white/[0.06] space-y-0.5">
                                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Adopter Remarks:</span>
                                                <p class="font-medium" x-text="item.notes"></p>
                                            </div>
                                        </template>
                                        <div class="pt-0.5">
                                            <button type="button" 
                                                @click="openPhotoModal(item.photo_url, healthPetName + ' Check-in', item.check_in_date, item.weight, item.health_status, item.notes)"
                                                class="inline-flex items-center gap-1.5 text-xs font-bold text-[#199CA4] hover:text-[#13787F] dark:text-[#41C1CB] dark:hover:text-teal-300 transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>Click to View Full Photo</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-3.5 mt-3.5 border-t border-slate-200/80 dark:border-white/[0.06] flex justify-end">
                        <button type="button" @click="showHealthModal = false" class="px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.1] rounded-xl transition cursor-pointer">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal 4: Edit Adopter Shelter Safety Status & Notes Modal (Admin Only) --}}
        @if(Auth::user()?->role === 'admin')
        <div x-show="showStatusModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-xs" @click="showStatusModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block px-5 sm:px-6 pt-5 pb-6 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-[#12141C] rounded-3xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 dark:border-white/[0.08]">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-slate-200/80 dark:border-white/[0.06]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#199CA4] to-[#13787F] text-white flex items-center justify-center text-sm font-bold shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white">Shelter Safety Status</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Adopter: <span class="font-bold text-[#199CA4] dark:text-[#41C1CB]" x-text="statusAdopterName"></span>
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="showStatusModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-2xl font-light leading-none cursor-pointer">&times;</button>
                    </div>

                    {{-- Modal Form --}}
                    <form method="POST" :action="'/adopters/' + statusProfileId + '/status'" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        {{-- Status Selection --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Adopter Standing</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] hover:bg-slate-50 dark:hover:bg-white/[0.04] cursor-pointer">
                                    <input type="radio" name="status" value="active" x-model="selectedStatus" class="text-[#199CA4] focus:ring-[#199CA4]">
                                    <div>
                                        <span class="text-xs font-bold text-slate-900 dark:text-white block">Active</span>
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">Standard verified adopter in regular standing.</span>
                                    </div>
                                </label>

                                <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-emerald-200 dark:border-emerald-800/60 bg-emerald-50/30 dark:bg-emerald-950/20 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 cursor-pointer">
                                    <input type="radio" name="status" value="good_standing" x-model="selectedStatus" class="text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 block">Good Standing</span>
                                        <span class="text-[11px] text-emerald-700 dark:text-emerald-400">Excellent track record with timely check-ins.</span>
                                    </div>
                                </label>

                                <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-amber-200 dark:border-amber-800/60 bg-amber-50/30 dark:bg-amber-950/20 hover:bg-amber-50 dark:hover:bg-amber-950/40 cursor-pointer">
                                    <input type="radio" name="status" value="restricted" x-model="selectedStatus" class="text-amber-600 focus:ring-amber-500">
                                    <div>
                                        <span class="text-xs font-bold text-amber-800 dark:text-amber-300 block">Restricted</span>
                                        <span class="text-[11px] text-amber-700 dark:text-amber-400">Temporarily frozen from adoptions (Requires shelter review).</span>
                                    </div>
                                </label>

                                <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-rose-200 dark:border-rose-800/60 bg-rose-50/30 dark:bg-rose-950/20 hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer">
                                    <input type="radio" name="status" value="blacklisted" x-model="selectedStatus" class="text-rose-600 focus:ring-rose-500">
                                    <div>
                                        <span class="text-xs font-bold text-rose-800 dark:text-rose-300 block">Banned / Blacklisted</span>
                                        <span class="text-[11px] text-rose-700 dark:text-rose-400">Permanently banned from adoptions; mobile access revoked.</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Internal Shelter Notes & Official Violation Reasons --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Sanction Reason / Remarks</label>
                                <span class="text-[10px] text-slate-400">Visible to adopter on mobile</span>
                            </div>
                            
                            {{-- Quick Preset Tags --}}
                            <div class="flex flex-wrap gap-1.5 mb-2">
                                <button type="button" @click="statusAdminNotes = 'Failure to submit mandatory 30-day monthly health reports.'" class="text-[10px] font-semibold px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/[0.12] transition cursor-pointer">Missing monthly reports</button>
                                <button type="button" @click="statusAdminNotes = 'Violation of signed Adoption Agreement (unauthorized re-homing/sale).'" class="text-[10px] font-semibold px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/[0.12] transition cursor-pointer">Contract violation</button>
                                <button type="button" @click="statusAdminNotes = 'Unsafe home environment reported during shelter follow-up inspection.'" class="text-[10px] font-semibold px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/[0.12] transition cursor-pointer">Unsafe home</button>
                                <button type="button" @click="statusAdminNotes = 'Providing false information or unverified application credentials.'" class="text-[10px] font-semibold px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/[0.12] transition cursor-pointer">False info</button>
                            </div>

                            <textarea name="admin_notes" rows="3" x-model="statusAdminNotes" placeholder="Reason for ban/restriction (e.g. Failure to submit mandatory 30-day health updates)..."
                                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-slate-800 dark:text-white rounded-xl focus:bg-white dark:focus:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition placeholder-slate-400 dark:placeholder-slate-500 font-medium"></textarea>
                        </div>

                        {{-- Modal Actions --}}
                        <div class="pt-3 border-t border-slate-200/80 dark:border-white/[0.06] flex items-center justify-end gap-2.5">
                            <button type="button" @click="showStatusModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.06] rounded-xl transition cursor-pointer">Cancel</button>
                            <button type="submit" class="px-5 py-2 text-xs sm:text-sm font-extrabold text-white bg-[#199CA4] hover:bg-[#13787F] rounded-xl shadow-xs transition cursor-pointer">Update Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- Modal 5: Adopter Profile Picture Preview Lightbox Modal --}}
        <div x-show="showAvatarModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" @keydown.escape.window="showAvatarModal = false">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-md" @click="showAvatarModal = false"></div>

                <div class="inline-block p-5 sm:p-6 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-[#12141C] rounded-3xl shadow-2xl my-8 max-w-lg w-full border border-slate-200 dark:border-white/[0.1] relative z-10">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-slate-200/80 dark:border-white/[0.06]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-[#199CA4]/10 text-[#199CA4] flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white" x-text="avatarModalName"></h3>
                                <p class="text-xs text-slate-400 font-mono font-bold" x-text="avatarModalIdCode"></p>
                            </div>
                        </div>
                        <button type="button" @click="showAvatarModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-2xl font-light leading-none cursor-pointer">&times;</button>
                    </div>

                    {{-- Image Display Area --}}
                    <div class="flex flex-col items-center justify-center py-2">
                        <template x-if="avatarModalUrl">
                            <div class="relative max-h-[65vh] w-full flex items-center justify-center bg-slate-950/40 rounded-2xl overflow-hidden border border-slate-200 dark:border-white/[0.08] p-2">
                                <img :src="avatarModalUrl" :alt="avatarModalName" class="max-h-[60vh] max-w-full w-auto object-contain rounded-xl shadow-lg">
                            </div>
                        </template>

                        <template x-if="!avatarModalUrl">
                            <div class="py-8 text-center space-y-3">
                                <div class="w-28 h-28 mx-auto rounded-3xl bg-gradient-to-br from-[#199CA4] to-[#13787F] text-white flex items-center justify-center font-black text-3xl shadow-xl shadow-[#199CA4]/20 border-2 border-white/20" x-text="avatarModalInitials"></div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-200">Default Initials Avatar</p>
                                    <p class="text-xs text-slate-400">This adopter has not uploaded a custom profile picture yet.</p>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-4 mt-3 border-t border-slate-200/80 dark:border-white/[0.06] flex items-center justify-between">
                        <template x-if="avatarModalUrl">
                            <a :href="avatarModalUrl" target="_blank" class="text-xs font-bold text-[#199CA4] hover:text-[#13787F] dark:text-[#41C1CB] dark:hover:text-teal-300 inline-flex items-center gap-1">
                                Open Original
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </template>
                        <template x-if="!avatarModalUrl">
                            <span></span>
                        </template>
                        <button type="button" @click="showAvatarModal = false" class="px-5 py-2 text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.1] rounded-xl transition cursor-pointer">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal 6: High-Resolution Health Check-in Photo Preview Lightbox Modal --}}
        <div x-show="showPhotoModal" x-cloak style="display: none;" class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" @keydown.escape.window="showPhotoModal = false">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/85 backdrop-blur-md" @click="showPhotoModal = false"></div>

                <div class="inline-block p-5 sm:p-6 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-[#12141C] rounded-3xl shadow-2xl my-8 max-w-2xl w-full border border-slate-200 dark:border-white/[0.1] relative z-[61]">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-slate-200/80 dark:border-white/[0.06]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-[#199CA4]/10 text-[#199CA4] flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white" x-text="photoModalTitle"></h3>
                                <p class="text-xs text-slate-400 font-medium" x-text="photoModalDate"></p>
                            </div>
                        </div>
                        <button type="button" @click="showPhotoModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-2xl font-light leading-none cursor-pointer">&times;</button>
                    </div>

                    {{-- Image Display Area --}}
                    <div class="space-y-3">
                        <div class="relative max-h-[65vh] w-full flex items-center justify-center bg-slate-950/60 rounded-2xl overflow-hidden border border-slate-200 dark:border-white/[0.08] p-2">
                            <img :src="photoModalUrl" :alt="photoModalTitle" class="max-h-[60vh] max-w-full w-auto object-contain rounded-xl shadow-lg">
                        </div>

                        {{-- Details Bar if weight / status / notes are present --}}
                        <div x-show="photoModalWeight || photoModalNotes" class="p-3.5 rounded-2xl bg-slate-50 dark:bg-[#171923] border border-slate-200 dark:border-white/[0.06] space-y-1.5">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <template x-if="photoModalWeight">
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                        Pet Weight: <span class="text-[#199CA4] dark:text-[#41C1CB]" x-text="photoModalWeight + ' kg'"></span>
                                    </span>
                                </template>
                                <template x-if="photoModalStatus">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-[#199CA4]/10 text-[#199CA4] border border-[#199CA4]/20" x-text="photoModalStatus === 'healthy' ? 'Healthy & Active' : photoModalStatus"></span>
                                </template>
                            </div>
                            <template x-if="photoModalNotes">
                                <p class="text-xs text-slate-600 dark:text-slate-300 italic" x-text="'“' + photoModalNotes + '”'"></p>
                            </template>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-4 mt-3 border-t border-slate-200/80 dark:border-white/[0.06] flex items-center justify-between">
                        <a :href="photoModalUrl" target="_blank" class="text-xs font-bold text-[#199CA4] hover:text-[#13787F] dark:text-[#41C1CB] dark:hover:text-teal-300 inline-flex items-center gap-1">
                            Open Original Photo
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                        <button type="button" @click="showPhotoModal = false" class="px-5 py-2 text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.1] rounded-xl transition cursor-pointer">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>