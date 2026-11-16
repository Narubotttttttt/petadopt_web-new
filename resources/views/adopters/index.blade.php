<x-app-layout>
    <div class="w-full py-6 sm:py-8 px-4 sm:px-6 lg:px-8 space-y-6" 
         x-data="{
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
                 this.selectedStatus = (status === 'blacklisted' || status === 'restricted') ? 'blacklisted' : 'active';
                 this.statusAdminNotes = notes || '';
                 this.showStatusModal = true;
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

        {{-- Adopters Section Navigation Tabs (Compact) --}}
        <div class="flex items-center gap-1.5 border-b border-slate-200 dark:border-white/[0.08] pb-2.5 overflow-x-auto">
            <a href="{{ route('adopters.index') }}" 
               class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-2xs bg-[#199CA4] text-white">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                <span>Adopter Profiles</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white">{{ $totalApprovedAdopters }}</span>
            </a>

            <a href="{{ route('adopters.reports') }}" 
               class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold transition bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08]">
                <svg class="w-3.5 h-3.5 text-[#199CA4] dark:text-[#41C1CB]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Monthly Pet Updates</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#199CA4]/10 dark:bg-white/[0.08] text-[#199CA4] dark:text-[#41C1CB]">{{ $totalMonthlyReports ?? 0 }}</span>
            </a>

            <a href="{{ route('adopters.reports', ['tab' => 'vet_proofs']) }}" 
               class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold transition bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08]">
                <svg class="w-3.5 h-3.5 text-[#199CA4] dark:text-[#41C1CB]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>Vaccination updates</span>
                @if(($pendingVetLogsCount ?? 0) > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-white animate-pulse">{{ $pendingVetLogsCount }} Pending</span>
                @else
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#199CA4]/10 dark:bg-white/[0.08] text-[#199CA4] dark:text-[#41C1CB]">{{ $totalVetLogsCount ?? 0 }}</span>
                @endif
            </a>
        </div>

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Adopter Profiles</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#199CA4]/10 text-[#199CA4] dark:bg-[#199CA4]/20 dark:text-[#41C1CB] border border-[#199CA4]/20 dark:border-[#41C1CB]/30">
                        {{ $totalApprovedAdopters }} {{ Str::plural('Adopter', $totalApprovedAdopters) }} &bull; {{ $totalApprovedApplications }} {{ Str::plural('Pet', $totalApprovedApplications) }}
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Manage approved adopters, shelter safety standing, and monthly health update compliance.</p>
            </div>
            
            {{-- Header Action --}}
            <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
                <a href="{{ route('adoption-applications.index', ['status' => 'pending']) }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-bold hover:bg-slate-50 dark:hover:bg-[#171923] hover:text-[#199CA4] dark:hover:text-[#41C1CB] transition shadow-2xs">
                    <svg class="w-4 h-4 text-[#199CA4] dark:text-[#41C1CB]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span>Review Requests</span>
                </a>
            </div>
        </div>

        {{-- Search & Controls Toolbar with Quick Status Filters --}}
        <div class="bg-white dark:bg-[#12141C] p-3 sm:p-4 rounded-2xl sm:rounded-3xl shadow-xs border border-slate-200/80 dark:border-white/[0.07]">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                {{-- Quick Filter Pills (Active, Reports Overdue, Blacklisted) --}}
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                    <a href="{{ route('adopters.index', array_merge(request()->except('filter', 'page'), ['filter' => 'active'])) }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ ($filter ?? 'active') === 'active' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40' }}">
                        <span>Active ({{ $activeCount ?? 0 }})</span>
                    </a>

                    <a href="{{ route('adopters.index', array_merge(request()->except('filter', 'page'), ['filter' => 'inactive'])) }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ ($filter ?? '') === 'inactive' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40' }}">
                        @if(($inactiveCount ?? 0) > 0)
                            <span class="w-1.5 h-1.5 rounded-full {{ ($filter ?? '') === 'inactive' ? 'bg-white' : 'bg-rose-500 animate-pulse' }}"></span>
                        @endif
                        <span>Reports Overdue ({{ $inactiveCount ?? 0 }})</span>
                    </a>

                    <a href="{{ route('adopters.index', array_merge(request()->except('filter', 'page'), ['filter' => 'blacklisted'])) }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ in_array(($filter ?? ''), ['blacklisted', 'restricted']) ? 'bg-slate-800 text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-white/[0.1]' }}">
                        Blacklisted ({{ $blacklistedCount ?? $restrictedCount ?? 0 }})
                    </a>
                </div>

                {{-- Search Bar --}}
                <form method="GET" action="{{ route('adopters.index') }}" class="flex items-center gap-2 w-full md:w-auto">
                    <input type="hidden" name="filter" value="{{ $filter ?? 'active' }}">
                    <div class="relative flex-1 md:w-80">
                        <input type="text" 
                               name="search" 
                               value="{{ $search }}"
                               placeholder="Search ADP ID, name, email, pet..." 
                               class="w-full pl-9 {{ !empty($search) ? 'pr-8' : 'pr-3' }} py-2 text-xs sm:text-sm bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-slate-800 dark:text-slate-100 rounded-xl focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition placeholder-slate-400 dark:placeholder-slate-500 font-medium">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        @if($search)
                            <a href="{{ route('adopters.index', array_merge(request()->except('search', 'page'), ['filter' => $filter ?? 'active'])) }}" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs font-bold" title="Clear search">
                                &times;
                            </a>
                        @endif
                    </div>
                    <button type="submit" class="px-4 py-2 bg-[#199CA4] hover:bg-[#13787F] text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-xs shrink-0 cursor-pointer">
                        Search
                    </button>
                </form>
            </div>
        </div>

        {{-- Main Adopters Structured Card Feed --}}
        <div class="space-y-3.5 sm:space-y-4">
            @forelse($adopters as $adopter)
                <div class="bg-white dark:bg-[#12141C] rounded-xl sm:rounded-2xl shadow-xs border border-slate-200/80 dark:border-white/[0.07] overflow-hidden transition-all duration-200 hover:border-slate-300 dark:hover:border-white/[0.12]">
                    
                    {{-- 1. Compact Adopter Profile Header Bar --}}
                    <div class="px-4 py-3 sm:px-5 sm:py-3.5 bg-slate-50/70 dark:bg-[#171923] border-b border-slate-200/70 dark:border-white/[0.06]">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            
                            {{-- Adopter Identity & Status --}}
                            <div class="flex items-center gap-3 min-w-0">
                                @php
                                    $adopterNameParts = preg_split('/\s+/', trim($adopter->applicant_name));
                                    $adopterInitials = count($adopterNameParts) >= 2 
                                        ? strtoupper(mb_substr($adopterNameParts[0], 0, 1) . mb_substr(end($adopterNameParts), 0, 1))
                                        : strtoupper(mb_substr($adopter->applicant_name, 0, 1));
                                    $adopterAvatarUrl = $adopter->avatar ?? '';
                                @endphp

                                <button type="button" 
                                    @click='openAvatarModal(@json($adopterAvatarUrl), @json($adopter->applicant_name), @json($adopterInitials), @json($adopter->adopter_id_code))'
                                    class="relative group rounded-xl focus:outline-none focus:ring-2 focus:ring-[#199CA4] transition cursor-pointer shrink-0"
                                    title="Click to view profile picture">
                                    @if($adopter->avatar)
                                        <img src="{{ $adopter->avatar }}" alt="{{ $adopter->applicant_name }}" class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl object-cover border border-slate-200 dark:border-white/[0.1] shadow-2xs group-hover:scale-105 group-hover:ring-2 group-hover:ring-[#199CA4] transition-all duration-200">
                                        <div class="absolute inset-0 bg-black/30 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white">
                                            <svg class="w-3.5 h-3.5 drop-shadow-sm" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                                        </div>
                                    @else
                                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-white/[0.08] dark:to-white/[0.02] text-slate-800 dark:text-slate-200 flex items-center justify-center font-bold text-xs sm:text-sm border border-slate-200 dark:border-white/[0.08] shadow-2xs group-hover:scale-105 group-hover:ring-2 group-hover:ring-[#199CA4] transition-all duration-200">
                                            {{ $adopterInitials }}
                                        </div>
                                    @endif
                                </button>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white leading-tight truncate">
                                            {{ $adopter->applicant_name }}
                                        </h2>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-mono font-bold bg-slate-200/80 dark:bg-white/[0.08] text-slate-700 dark:text-slate-300 border border-slate-300/60 dark:border-white/[0.1]">
                                            {{ $adopter->adopter_id_code }}
                                        </span>

                                        {{-- Adopter Standing Badge --}}
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $adopter->badge_theme === 'rose' ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60' : ($adopter->badge_theme === 'amber' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60') }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $adopter->badge_theme === 'rose' ? 'bg-rose-500' : ($adopter->badge_theme === 'amber' ? 'bg-amber-500' : 'bg-emerald-500') }} animate-pulse"></span>
                                            {{ $adopter->status_label }}
                                        </span>
                                    </div>

                                    {{-- Contact & Location Info --}}
                                    <div class="flex items-center gap-x-3.5 gap-y-1 flex-wrap text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
                                        <div class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            <span class="text-slate-700 dark:text-slate-300">{{ $adopter->applicant_email }}</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $adopter->applicant_phone }}</span>
                                        </div>
                                        @if(!empty($adopter->address))
                                            <div class="flex items-center gap-1 max-w-xs sm:max-w-md truncate" title="{{ $adopter->address }}">
                                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <span class="truncate">{{ $adopter->address }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Header Badges & Actions --}}
                            <div class="flex items-center gap-2 shrink-0 self-start sm:self-center">
                                <span class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg text-xs font-bold bg-[#199CA4]/10 text-[#199CA4] dark:bg-[#199CA4]/20 dark:text-[#41C1CB] border border-[#199CA4]/20 dark:border-[#41C1CB]/30 shrink-0">
                                    <svg class="w-3.5 h-3.5 text-current shrink-0" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                    <span>{{ $adopter->pets_count }} {{ Str::plural('Pet', $adopter->pets_count) }}</span>
                                </span>

                                @if($adopter->profile_id && Auth::user()?->role === 'admin')
                                    <button type="button" 
                                        @click='openStatusModal(@json($adopter->profile_id), @json($adopter->applicant_name), @json($adopter->status ?? "active"), @json($adopter->admin_notes ?? ""))'
                                        class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-200 text-xs font-bold hover:bg-slate-100 dark:hover:bg-[#1D1F2C] transition shadow-2xs cursor-pointer shrink-0">
                                        <svg class="w-3.5 h-3.5 text-[#199CA4] dark:text-[#41C1CB] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        <span>Edit Status</span>
                                    </button>
                                @endif
                            </div>

                        </div>

                        {{-- Sanction / Shelter Safety Notes Alert Banner --}}
                        @if(!empty($adopter->admin_notes))
                            <div class="mt-3 p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/40 text-amber-900 dark:text-amber-200 text-xs flex items-start gap-2">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <div>
                                    <span class="font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wider text-[10px] block">Shelter Remark / Official Sanction Note:</span>
                                    <p class="font-medium mt-0.5">{{ $adopter->admin_notes }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- 2. Seamless Integrated Adopted Pet Rows --}}
                    <div class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                        @forelse($adopter->applications as $app)
                            @php 
                                $pet = $app->pet; 
                                $petImg = $pet && $pet->photo_path ? asset('storage/' . ltrim($pet->photo_path, '/')) : ($pet ? $pet->primary_image_url : null);
                                $petNameStr = $pet && $pet->name ? $pet->name : 'Pet no. '.$app->pet_id;
                                $adoptedDate = $app->approved_at ?? $app->signed_at ?? $app->updated_at ?? $app->created_at;

                                // Medical logs & vaccination status (Official verified records only)
                                $verifiedMedicalLogs = $pet ? $pet->medicalLogs->filter(fn($m) => !$m->is_external || $m->verification_status === 'verified') : collect();
                                $pendingExternalLog = $pet ? $pet->medicalLogs->firstWhere('verification_status', 'pending') : null;
                                $latestLog = $verifiedMedicalLogs->first();
                                $vaccineLogs = $verifiedMedicalLogs->where('category', 'vaccination');
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
                                        'name' => $m->vaccine_name ?: ucfirst($m->category),
                                        'date' => $m->date ? $m->date->format('M d, Y') : '',
                                        'next_due_date' => $m->next_due_date ? $m->next_due_date->format('M d, Y') : null,
                                        'administered_by' => $m->administered_by ?: ($m->creator ? $m->creator->name : 'Staff'),
                                        'is_external' => (bool) $m->is_external,
                                        'clinic_name' => $m->clinic_name,
                                        'proof_photo_url' => $m->proof_photo_url,
                                        'verification_status' => $m->verification_status ?? 'verified',
                                        'notes' => $m->notes,
                                    ];
                                })->values()->toArray() : [];
                            @endphp

                            <div class="px-4 py-3 sm:px-5 sm:py-3.5 hover:bg-slate-50/60 dark:hover:bg-white/[0.02] transition-colors">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 sm:gap-4 items-center">
                                    
                                    {{-- Pet Thumbnail & Bio (Col 1-4) --}}
                                    <div class="md:col-span-4 flex items-center gap-3 min-w-0">
                                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl overflow-hidden shrink-0 border border-slate-200 dark:border-white/[0.08] shadow-2xs relative bg-slate-100 dark:bg-[#171923]">
                                            @if($petImg)
                                                <img src="{{ $petImg }}" alt="{{ $petNameStr }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="hidden w-full h-full bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] items-center justify-center font-bold text-xs">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                                </div>
                                            @else
                                                <div class="w-full h-full bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm truncate block leading-tight">
                                                    {{ $petNameStr }}
                                                </span>
                                                @if($app->contract_pdf_path)
                                                    <a href="{{ route('adoption-applications.contract', $app->id) }}" title="Download Adoption Agreement PDF" target="_blank"
                                                        class="p-0.5 rounded text-slate-400 hover:text-[#199CA4] dark:hover:text-[#41C1CB] hover:bg-slate-100 dark:hover:bg-white/[0.06] transition shrink-0">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                    </a>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 font-medium flex-wrap">
                                                @php
                                                    $cleanBreed = $pet ? trim(str_ireplace([' (Mixed Breed)', '(Mixed Breed)'], '', $pet->breed ?? '')) : '';
                                                    $cleanBreed = trim(trim($cleanBreed), '•-');
                                                    $petTypeBreed = $pet ? (ucfirst($pet->type) . ($cleanBreed ? ' • ' . $cleanBreed : '')) : 'Adopted';
                                                @endphp
                                                <span>{{ $petTypeBreed }}</span>
                                                @if($adoptedDate)
                                                    <span class="text-slate-300 dark:text-white/20">•</span>
                                                    <span class="text-slate-400 dark:text-slate-500">Adopted {{ \Carbon\Carbon::parse($adoptedDate)->format('M d, Y') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Monthly Welfare Updates (Col 5-7) --}}
                                    <div class="md:col-span-3 flex items-center gap-2.5 flex-wrap min-w-0">
                                        @if(!empty($app->is_pet_deceased))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                Memorial
                                            </span>
                                        @elseif(!empty($app->is_report_overdue))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60 shadow-2xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                                Overdue by {{ $app->report_overdue_days }}d
                                            </span>
                                        @elseif(isset($app->report_due_days))
                                            @if($app->report_due_days <= 7)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    Due Soon • {{ $app->report_due_days }}d left
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Up to Date • Due in {{ $app->report_due_days }}d
                                                </span>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08]">
                                                Compliant
                                            </span>
                                        @endif

                                        @if($healthUpdates->count() > 0)
                                            <button type="button"
                                                @click='openHealthModal(@json($healthData), @json($petNameStr), @json($adopter->applicant_name))'
                                                class="inline-flex items-center gap-1 text-[11px] font-bold text-[#199CA4] hover:text-[#13787F] dark:text-[#41C1CB] dark:hover:text-[#5fe1ec] transition cursor-pointer group">
                                                <svg class="w-3.5 h-3.5 text-[#199CA4] dark:text-[#41C1CB]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span>{{ $healthUpdates->count() }} {{ Str::plural('Check-in', $healthUpdates->count()) }}</span>
                                                @if($latestCheckin && $latestCheckin->check_in_date)
                                                    <span class="text-slate-400 font-normal">({{ $latestCheckin->check_in_date->format('M d') }})</span>
                                                @endif
                                                <svg class="w-2.5 h-2.5 text-slate-400 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Clinical History & Actions (Col 8-12) --}}
                                    <div class="md:col-span-5 flex items-center justify-between gap-3 min-w-0">
                                        <div class="min-w-0">
                                            @if($latestLog)
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold {{ $latestLog->category === 'vaccination' ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60' }}">
                                                        {{ $latestLog->vaccine_name ?: ($latestLog->category === 'vaccination' ? 'Vaccine' : 'Deworming') }}
                                                    </span>
                                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-semibold truncate">{{ $latestLog->date ? $latestLog->date->format('M d, Y') : '' }}</span>
                                                </div>
                                                <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-0.5 truncate">
                                                    @php
                                                        $nextDue = $latestVaccine?->next_due_date ?? $latestLog?->next_due_date;
                                                        $dueLabel = ($latestLog->category === 'deworming') ? 'Next dose' : 'Booster';
                                                    @endphp
                                                    @if($nextDue)
                                                        @if($nextDue->isPast())
                                                            <span class="text-rose-600 dark:text-rose-400 font-bold">{{ $dueLabel }}: Due {{ $nextDue->format('M d, Y') }} (Overdue)</span>
                                                        @else
                                                            {{ $dueLabel }}: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $nextDue->format('M d, Y') }}</span>
                                                        @endif
                                                    @else
                                                        {{ $dueLabel }}: <span class="font-semibold text-emerald-600 dark:text-emerald-400">Up to date</span>
                                                    @endif
                                                </div>
                                            @elseif($pendingExternalLog)
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                                        Pending Verification
                                                    </span>
                                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold truncate">{{ $pendingExternalLog->vaccine_name ?: 'Vaccination Proof' }}</span>
                                                </div>
                                                <div class="text-[11px] text-amber-600 dark:text-amber-400 font-medium mt-0.5 truncate">
                                                    Booster: Awaiting staff review
                                                </div>
                                            @else
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xs text-slate-400 dark:text-slate-500 italic">No medical logs</span>
                                                </div>
                                                <div class="text-[11px] text-slate-400 dark:text-slate-600 mt-0.5">
                                                    Booster: <span class="text-slate-400 dark:text-slate-500 font-normal">None pending</span>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <button type="button" 
                                                @click='openHistoryModal(@json($historyData), @json($petNameStr), @json($adopter->applicant_name))'
                                                class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C] text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#1D1F2C] transition shadow-2xs cursor-pointer">
                                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>History ({{ count($historyData) }})</span>
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @empty
                            <div class="px-4 py-3 sm:px-5 sm:py-3.5 text-xs text-slate-400 dark:text-slate-500 italic">
                                No active adopted pets registered to this profile.
                            </div>
                        @endforelse
                    </div>

                </div>
            @empty
                <div class="bg-white dark:bg-[#12141C] rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 dark:border-white/[0.07] p-12 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-white/[0.04] text-slate-400 flex items-center justify-center text-xl mx-auto mb-3 font-bold">
                        <svg class="w-7 h-7 text-slate-400" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                    </div>
                    @if(($filter ?? 'active') === 'inactive')
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">No Overdue Reports</h3>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">Great news! All approved adopters are currently up to date with their required monthly pet welfare updates.</p>
                    @elseif(in_array(($filter ?? ''), ['blacklisted', 'restricted']))
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">No Blacklisted Adopters</h3>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">There are currently no adopters with safety sanctions or blacklist restrictions.</p>
                    @else
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">No Active Adopter Records Found</h3>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">When adoption applications are approved, active adopters and their clinical history will appear here.</p>
                    @endif
                    @if(!empty($search) || ($filter ?? 'active') !== 'active')
                        <div class="mt-4">
                            <a href="{{ route('adopters.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 text-xs font-bold text-slate-700 dark:text-slate-200 transition">
                                Reset to Active Adopters
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

        {{-- History Timeline Modal --}}
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
                                    <div class="flex items-center justify-between gap-2 mb-1.5 flex-wrap">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                                :class="log.category === 'vaccination' ? 'bg-blue-100 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60' : 'bg-emerald-100 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60'"
                                                x-text="log.category === 'vaccination' ? 'Vaccination' : 'Deworming'">
                                            </span>
                                            <template x-if="log.is_external">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-violet-100 dark:bg-violet-950/40 text-violet-800 dark:text-violet-300 border border-violet-200 dark:border-violet-800/60"
                                                    x-text="log.clinic_name ? 'Vet: ' + log.clinic_name : 'External Vet'">
                                                </span>
                                            </template>
                                            <template x-if="log.is_external && log.verification_status === 'pending'">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-100 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                                    Pending Review
                                                </span>
                                            </template>
                                            <span class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white" x-text="log.name"></span>
                                        </div>
                                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400" x-text="log.date"></span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mt-1 flex-wrap gap-2">
                                        <span x-show="log.administered_by" class="font-semibold text-slate-700 dark:text-slate-300" x-text="'Administered by: ' + log.administered_by"></span>
                                        <span x-show="log.next_due_date" class="font-semibold text-amber-700 dark:text-amber-400" x-text="'Next Due: ' + log.next_due_date"></span>
                                    </div>
                                    <p x-show="log.notes" class="text-xs text-slate-600 dark:text-slate-300 mt-1.5 bg-white dark:bg-[#0C0D13] p-2 rounded-lg border border-slate-200/80 dark:border-white/[0.06]" x-text="log.notes"></p>

                                    {{-- Proof photo and Verification actions --}}
                                    <div x-show="log.proof_photo_url || (log.is_external && log.verification_status === 'pending')" class="mt-2.5 pt-2 border-t border-slate-200/60 dark:border-white/[0.06] flex items-center justify-between gap-2 flex-wrap">
                                        <template x-if="log.proof_photo_url">
                                            <a :href="log.proof_photo_url" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#199CA4] hover:text-[#13787F] dark:text-[#41C1CB] hover:underline">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span>View Vet Card Proof</span>
                                            </a>
                                        </template>
                                        <template x-if="log.is_external && log.verification_status === 'pending'">
                                            <a :href="'{{ route('adopters.reports', ['tab' => 'vet_proofs']) }}&search=' + encodeURIComponent(log.name)" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold bg-[#199CA4] hover:bg-[#13787F] text-white rounded-lg transition shadow-2xs ms-auto">
                                                <span>Verify in Vaccination Updates &rarr;</span>
                                            </a>
                                        </template>
                                    </div>
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
                                                    'bg-rose-100 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60': item.health_status === 'under_treatment',
                                                    'bg-slate-900 text-white dark:bg-slate-800 dark:text-slate-200 border border-slate-700': item.health_status === 'deceased'
                                                }"
                                                x-text="item.health_status === 'healthy' ? 'Healthy & Active' : (item.health_status === 'minor_issue' ? 'Minor Issue' : (item.health_status === 'deceased' ? 'Passed Away / Deceased' : 'Under Treatment'))">
                                            </span>
                                        </div>
                                        <template x-if="item.weight">
                                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                Recorded Weight: <span class="font-extrabold text-[#199CA4] dark:text-[#41C1CB]" x-text="item.weight + ' kg'"></span>
                                            </p>
                                        </template>
                                        <template x-if="item.notes">
                                            <div class="text-xs text-slate-700 dark:text-slate-300 bg-white dark:bg-[#0C0D13] p-2.5 rounded-xl border border-slate-200/80 dark:border-white/[0.06] space-y-0.5"
                                                 :class="item.health_status === 'deceased' ? 'border-rose-300/80 dark:border-rose-900/60 bg-rose-50/50 dark:bg-rose-950/20' : ''">
                                                <span class="text-[10px] font-bold uppercase tracking-wider block"
                                                      :class="item.health_status === 'deceased' ? 'text-rose-700 dark:text-rose-400 font-extrabold' : 'text-slate-400 dark:text-slate-500'"
                                                      x-text="item.health_status === 'deceased' ? 'Reason of Passing & Circumstances:' : 'Adopter Remarks:'"></span>
                                                <p class="font-medium whitespace-pre-line" x-text="item.notes"></p>
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

                                <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-rose-200 dark:border-rose-800/60 bg-rose-50/30 dark:bg-rose-950/20 hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer">
                                    <input type="radio" name="status" value="blacklisted" x-model="selectedStatus" class="text-rose-600 focus:ring-rose-500">
                                    <div>
                                        <span class="text-xs font-bold text-rose-800 dark:text-rose-300 block">Blacklisted</span>
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
                                <button type="button" @click="statusAdminNotes = 'Providing false information or unverified application credentials.'" class="text-[10px] font-semibold px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/[0.12] transition cursor-pointer">False info</button>
                            </div>

                            <textarea name="admin_notes" rows="3" x-model="statusAdminNotes" placeholder="Reason for blacklisting (e.g. Failure to submit mandatory 30-day health updates)..."
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