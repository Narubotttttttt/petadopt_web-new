<x-app-layout>
    <div class="w-full py-6 px-4 sm:px-6 lg:px-8 space-y-6" 
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
            <div class="mb-6 px-4 py-3.5 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-2xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 flex items-center justify-center text-emerald-600 dark:text-emerald-300 flex-shrink-0 font-bold">
                        ✓
                    </div>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-lg leading-none cursor-pointer">&times;</button>
            </div>
        @endif

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#333634] dark:text-white tracking-tight">Adopter Profiles</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs sm:text-sm font-bold bg-[#199CA4]/10 dark:bg-[#199CA4]/25 text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20 dark:border-[#41C1CB]/30">
                        {{ $totalApprovedAdopters }} Adopters ({{ $totalApprovedApplications }} Pets)
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-slate-400 mt-1">Manage approved adopters, shelter safety status, vaccine schedules, and clinical medical logs.</p>
            </div>
            
            {{-- Header Action --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('adoption-applications.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-[#0e1d20] text-gray-700 dark:text-slate-200 text-xs sm:text-sm font-bold hover:bg-gray-50 dark:hover:bg-slate-800 hover:text-[#199CA4] transition shadow-xs">
                    <svg class="w-4 h-4 text-gray-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span>Review Requests</span>
                </a>
            </div>
        </div>

        {{-- Filters & Search Row --}}
        <div class="bg-white dark:bg-[#0e1d20] p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            {{-- Filter Tabs --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none">
                <a href="{{ route('adopters.index', array_merge(request()->except('filter', 'page'), ['filter' => 'all'])) }}"
                   class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap {{ ($filter ?? 'all') === 'all' ? 'bg-[#199CA4] text-white shadow-xs' : 'text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-800' }}">
                    All ({{ $totalApprovedAdopters }})
                </a>
                <a href="{{ route('adopters.index', array_merge(request()->except('filter', 'page'), ['filter' => 'overdue'])) }}"
                   class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap {{ ($filter ?? '') === 'overdue' ? 'bg-rose-600 text-white shadow-xs' : 'text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40' }}">
                    Overdue ({{ $overdueCount ?? 0 }})
                </a>
                <a href="{{ route('adopters.index', array_merge(request()->except('filter', 'page'), ['filter' => 'due_soon'])) }}"
                   class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap {{ ($filter ?? '') === 'due_soon' ? 'bg-amber-500 text-white shadow-xs' : 'text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40' }}">
                    Due Soon ({{ $dueSoonCount ?? 0 }})
                </a>
                <a href="{{ route('adopters.index', array_merge(request()->except('filter', 'page'), ['filter' => 'up_to_date'])) }}"
                   class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap {{ ($filter ?? '') === 'up_to_date' ? 'bg-emerald-600 text-white shadow-xs' : 'text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40' }}">
                    Up to Date
                </a>
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
                           class="w-full pl-10 pr-8 py-2.5 text-xs sm:text-sm bg-gray-50 dark:bg-[#12272b] border border-gray-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 rounded-xl focus:bg-white dark:focus:bg-[#12272b] focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition placeholder-gray-400 dark:placeholder-slate-500">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    @if($search)
                        <a href="{{ route('adopters.index', request()->except('search', 'page')) }}" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 text-xs">
                            &times;
                        </a>
                    @endif
                </div>
                <button type="submit" class="px-5 py-2.5 bg-[#199CA4] hover:bg-[#13787F] text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-xs flex items-center gap-1.5 shrink-0 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <span>Search</span>
                </button>
            </form>
        </div>

        {{-- Main Adopter Table --}}
        <div class="bg-white dark:bg-[#0e1d20] rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[950px]">
                    <thead class="bg-gray-50/80 dark:bg-[#091518] border-b border-gray-100 dark:border-slate-800 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400">
                        <tr>
                            <th class="py-4 px-5 w-72">Adopter Profile</th>
                            <th class="py-4 px-5 w-72">Adopted Pet(s)</th>
                            <th class="py-4 px-5">Pet Medical & Vaccine Status</th>
                            <th class="py-4 px-5 text-center w-40">Monthly Reports</th>
                            <th class="py-4 px-5 text-right w-44">Clinical Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-800/80">
                        @forelse($adopters as $adopter)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-[#12272b]/50 transition-colors">
                                
                                {{-- 1. Adopter Profile --}}
                                <td class="py-4 px-5 align-top">
                                    <div class="flex items-start gap-3.5">
                                        @if($adopter->avatar)
                                            <img src="{{ $adopter->avatar }}" alt="{{ $adopter->applicant_name }}" class="w-12 h-12 rounded-2xl object-cover border border-gray-200 dark:border-slate-700 shadow-xs flex-shrink-0">
                                        @else
                                            @php
                                                $adopterNameParts = preg_split('/\s+/', trim($adopter->applicant_name));
                                                $adopterInitials = count($adopterNameParts) >= 2 
                                                    ? strtoupper(mb_substr($adopterNameParts[0], 0, 1) . mb_substr(end($adopterNameParts), 0, 1))
                                                    : strtoupper(mb_substr($adopter->applicant_name, 0, 1));
                                            @endphp
                                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#EAF5F6] to-[#d3eef1] dark:from-[#133036] dark:to-[#17454d] text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-extrabold text-sm border border-[#199CA4]/20 dark:border-[#41C1CB]/30 flex-shrink-0 shadow-xs">
                                                {{ $adopterInitials }}
                                            </div>
                                        @endif

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-extrabold text-gray-900 dark:text-white text-sm sm:text-base leading-tight">{{ $adopter->applicant_name }}</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-extrabold font-mono bg-cyan-50 dark:bg-cyan-950/60 text-cyan-800 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800">
                                                    {{ $adopter->adopter_id_code }}
                                                </span>
                                            </div>
                                            
                                            {{-- Adopter Status Chip --}}
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $adopter->status === 'blacklisted' ? 'bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800' : ($adopter->status === 'restricted' ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800' : 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $adopter->status ?? 'active')) }}
                                                </span>

                                                @if($adopter->profile_id)
                                                    <button type="button" 
                                                        @click='openStatusModal(@json($adopter->profile_id), @json($adopter->applicant_name), @json($adopter->status ?? "active"), @json($adopter->admin_notes ?? ""))'
                                                        class="text-[11px] font-bold text-[#199CA4] dark:text-[#41C1CB] hover:underline cursor-pointer">
                                                        Edit Status
                                                    </button>
                                                @endif
                                            </div>

                                            <div class="text-xs text-gray-500 dark:text-slate-400 mt-1 truncate">{{ $adopter->applicant_email }}</div>
                                            <div class="text-xs font-semibold text-gray-600 dark:text-slate-300 mt-0.5">{{ $adopter->applicant_phone }}</div>

                                            @if(!empty($adopter->address))
                                                <div class="text-[11px] text-gray-500 dark:text-slate-400 mt-1 flex items-start gap-1 leading-snug">
                                                    <span class="text-gray-400">📍</span>
                                                    <span class="truncate max-w-[200px]" title="{{ $adopter->address }}">{{ $adopter->address }}</span>
                                                </div>
                                            @endif

                                            @if(!empty($adopter->admin_notes))
                                                <p class="text-[11px] text-gray-600 dark:text-slate-300 bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-800/60 p-1.5 rounded-lg mt-1.5 italic">
                                                    Note: {{ $adopter->admin_notes }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- 2. Adopted Pets --}}
                                <td class="py-4 px-5 align-top">
                                    <div class="space-y-2.5">
                                        @foreach($adopter->applications as $app)
                                            @php 
                                                $pet = $app->pet; 
                                                $petImg = $pet && $pet->photo_path ? asset('storage/' . ltrim($pet->photo_path, '/')) : ($pet ? $pet->primary_image_url : null);
                                            @endphp
                                            <div class="flex items-center gap-3 p-2 rounded-2xl bg-slate-50/80 dark:bg-[#12272b] border border-slate-100 dark:border-slate-800 hover:bg-white dark:hover:bg-[#15343a] hover:border-[#199CA4]/30 transition shadow-2xs">
                                                @if($petImg)
                                                    <div class="w-12 h-12 rounded-xl overflow-hidden shrink-0 border border-slate-200 dark:border-slate-700 shadow-2xs relative bg-[#F0FBFB] dark:bg-[#0a171a]">
                                                        <img src="{{ $petImg }}" alt="{{ $pet->name ?? 'Pet' }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                        <div class="hidden w-full h-full bg-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB] items-center justify-center font-bold text-base">
                                                            🐾
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="w-12 h-12 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-base shrink-0">
                                                        🐾
                                                    </div>
                                                @endif

                                                <div class="min-w-0 flex-1">
                                                    <span class="font-extrabold text-slate-800 dark:text-white block truncate text-sm sm:text-base leading-tight">{{ $pet && $pet->name ? $pet->name : 'Pet #'.$app->pet_id }}</span>
                                                    <span class="text-xs text-slate-500 dark:text-slate-400 block truncate mt-0.5 font-medium">{{ $pet ? (ucfirst($pet->type) . ' • ' . $pet->breed) : 'Adopted' }}</span>
                                                </div>

                                                @if($app->contract_pdf_path)
                                                    <a href="{{ route('adoption-applications.contract', $app->id) }}" title="Download Contract" target="_blank"
                                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#199CA4] dark:hover:text-[#41C1CB] hover:bg-[#F0FBFB] dark:hover:bg-[#142e33] transition shrink-0">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                    </a>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </td>

                                {{-- 3. Pet Medical & Vaccine Status --}}
                                <td class="py-4 px-5 align-top">
                                    <div class="space-y-2.5">
                                        @foreach($adopter->applications as $app)
                                            @php 
                                                $pet = $app->pet; 
                                                $latestLog = $pet ? $pet->medicalLogs->first() : null;
                                                $vaccineLogs = $pet ? $pet->medicalLogs->where('category', 'vaccination') : collect();
                                                $latestVaccine = $vaccineLogs->first();
                                            @endphp
                                            <div class="p-2 rounded-2xl bg-gray-50/80 dark:bg-[#12272b] border border-gray-100 dark:border-slate-800 flex items-center justify-between gap-2 min-h-[50px]">
                                                @if($latestLog)
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold {{ $latestLog->category === 'vaccination' ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' }}">
                                                            {{ $latestLog->category === 'vaccination' ? 'Vaccine' : 'Deworming' }}
                                                        </span>
                                                        <span class="text-xs text-gray-600 dark:text-slate-300 font-medium truncate">{{ $latestLog->date ? $latestLog->date->format('M d, Y') : '' }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-xs text-gray-400 dark:text-slate-500 italic px-1">No medical logs</span>
                                                @endif

                                                @if($latestVaccine && $latestVaccine->next_due_date)
                                                    @php
                                                        $isPastDue = $latestVaccine->next_due_date->isPast();
                                                        $isSoon = $latestVaccine->next_due_date->isBetween(now(), now()->addDays(14));
                                                    @endphp
                                                    <span class="text-xs font-bold px-2.5 py-1 rounded-lg flex-shrink-0 {{ $isPastDue ? 'bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300' : ($isSoon ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300' : 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300') }}">
                                                        {{ $isPastDue ? 'Overdue' : ($isSoon ? 'Due Soon' : 'Valid') }}: {{ $latestVaccine->next_due_date->format('M d') }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </td>

                                {{-- 4. Monthly Reports --}}
                                <td class="py-4 px-5 align-top text-center">
                                    <div class="space-y-2.5">
                                        @foreach($adopter->applications as $app)
                                            @php 
                                                $pet = $app->pet;
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
                                                $petNameStr = $pet ? ($pet->name ?: 'Pet #'.$pet->id) : 'Pet #'.$app->pet_id;
                                            @endphp
                                            <div class="flex flex-col items-center justify-center min-h-[50px] p-1">
                                                <button type="button"
                                                    @click='openHealthModal(@json($healthData), @json($petNameStr), @json($adopter->applicant_name))'
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold text-xs transition cursor-pointer {{ $healthUpdates->count() > 0 ? 'bg-[#EAF5F6] dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] hover:bg-[#199CA4] hover:text-white' : 'bg-gray-100 dark:bg-slate-800 text-gray-500 dark:text-slate-400 hover:bg-gray-200 dark:hover:bg-slate-700' }}">
                                                    <span>Reports ({{ $healthUpdates->count() }})</span>
                                                </button>
                                                <span class="text-[11px] text-gray-400 dark:text-slate-500 mt-1">
                                                    {{ $latestCheckin && $latestCheckin->check_in_date ? $latestCheckin->check_in_date->format('M d') : 'None' }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>

                                {{-- 5. Clinical Actions --}}
                                <td class="py-4 px-5 align-top text-right">
                                    <div class="space-y-2.5">
                                        @foreach($adopter->applications as $app)
                                            @php 
                                                $pet = $app->pet;
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
                                                $petNameStr = $pet ? ($pet->name ?: 'Pet #'.$pet->id) : 'Pet #'.$app->pet_id;
                                            @endphp
                                            <div class="flex items-center justify-end gap-2 min-h-[50px]">
                                                <button type="button" 
                                                    @click='openHistoryModal(@json($historyData), @json($petNameStr), @json($adopter->applicant_name))'
                                                    class="px-3 py-1.5 rounded-xl border border-gray-200 dark:border-slate-700 text-xs font-bold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition shadow-2xs cursor-pointer">
                                                    History ({{ count($historyData) }})
                                                </button>

                                                <button type="button" 
                                                    @click='openAddModal(@json($app->pet_id), @json($petNameStr), @json($adopter->applicant_name))'
                                                    class="px-3.5 py-1.5 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white text-xs font-bold transition shadow-xs flex items-center gap-1 cursor-pointer">
                                                    <span>+ Record</span>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center">
                                    <div class="w-12 h-12 rounded-2xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center text-lg mx-auto mb-3 font-bold">
                                        🐾
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">No Adopter Records Found</h3>
                                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">When adoption applications are approved, the adopters and their pets will appear here.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($adopters->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-slate-800 bg-gray-50/50 dark:bg-[#091518]">
                    {{ $adopters->links() }}
                </div>
            @endif
        </div>

        {{-- Modal 1: Add Medical Log Modal --}}
        <div x-show="showAddModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-sm" @click="showAddModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block px-5 sm:px-6 pt-5 pb-6 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-[#0e1d20] rounded-3xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 dark:border-slate-800">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-gray-100 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-[#199CA4] to-[#13787F] text-white flex items-center justify-center text-sm font-bold shadow-sm">
                                🩺
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-gray-900 dark:text-white">Record Medical Log</h3>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    Pet: <span class="font-bold text-[#199CA4] dark:text-[#41C1CB]" x-text="modalPetName"></span> | Adopter: <span class="font-semibold text-gray-700 dark:text-slate-300" x-text="modalAdopterName"></span>
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="showAddModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-2xl font-light leading-none cursor-pointer">&times;</button>
                    </div>

                    {{-- Modal Form --}}
                    <form method="POST" action="{{ route('medical-logs.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="pet_id" :value="modalPetId">

                        {{-- Category Selector --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Record Type</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" 
                                    @click="selectedCategory = 'vaccination'"
                                    :class="selectedCategory === 'vaccination' ? 'bg-[#199CA4] text-white shadow-xs' : 'bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-slate-300 hover:bg-gray-200 dark:hover:bg-slate-700'"
                                    class="py-2.5 rounded-xl font-bold text-xs transition cursor-pointer">
                                    💉 Vaccination
                                </button>
                                <button type="button" 
                                    @click="selectedCategory = 'deworming'"
                                    :class="selectedCategory === 'deworming' ? 'bg-[#199CA4] text-white shadow-xs' : 'bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-slate-300 hover:bg-gray-200 dark:hover:bg-slate-700'"
                                    class="py-2.5 rounded-xl font-bold text-xs transition cursor-pointer">
                                    💊 Deworming
                                </button>
                            </div>
                            <input type="hidden" name="category" :value="selectedCategory">
                        </div>

                        {{-- Dynamic Fields --}}
                        <div x-show="selectedCategory === 'vaccination'" class="space-y-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1">Vaccine Name *</label>
                                <input type="text" name="vaccine_name" placeholder="e.g. Anti-Rabies, 5-in-1, DHPP" 
                                    class="w-full px-3.5 py-2 text-xs sm:text-sm bg-gray-50 dark:bg-[#12272b] border border-gray-200 dark:border-slate-700 text-slate-800 dark:text-white rounded-xl focus:bg-white dark:focus:bg-[#12272b] focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition placeholder-gray-400 dark:placeholder-slate-500">
                            </div>
                        </div>

                        <div x-show="selectedCategory === 'deworming'" class="space-y-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1">Deworming Brand / Medicine *</label>
                                <input type="text" name="deworming_name" placeholder="e.g. Canex, Pyrantel, Drontal" 
                                    class="w-full px-3.5 py-2 text-xs sm:text-sm bg-gray-50 dark:bg-[#12272b] border border-gray-200 dark:border-slate-700 text-slate-800 dark:text-white rounded-xl focus:bg-white dark:focus:bg-[#12272b] focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition placeholder-gray-400 dark:placeholder-slate-500">
                            </div>
                        </div>

                        {{-- Date & Next Due Date --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1">Date Administered *</label>
                                <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                                    class="w-full px-3 py-2 text-xs bg-gray-50 dark:bg-[#12272b] border border-gray-200 dark:border-slate-700 text-slate-800 dark:text-white rounded-xl focus:bg-white dark:focus:bg-[#12272b] focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1">Next Due Date (Optional)</label>
                                <input type="date" name="next_due_date" 
                                    class="w-full px-3 py-2 text-xs bg-gray-50 dark:bg-[#12272b] border border-gray-200 dark:border-slate-700 text-slate-800 dark:text-white rounded-xl focus:bg-white dark:focus:bg-[#12272b] focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition">
                                <p class="text-[10px] text-gray-400 dark:text-slate-500 mt-1">
                                    <span x-show="selectedCategory === 'vaccination'">Auto-calculates +6 months if left empty</span>
                                    <span x-show="selectedCategory === 'deworming'">Auto-calculates +3 months if left empty</span>
                                </p>
                            </div>
                        </div>

                        {{-- Administered By (Auto-detected) --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1">Administered By (Staff / Admin)</label>
                            <div class="flex items-center justify-between px-3.5 py-2.5 bg-emerald-50/70 dark:bg-emerald-950/50 border border-emerald-200/80 dark:border-emerald-800 rounded-xl">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="text-xs font-bold text-emerald-900 dark:text-emerald-300">{{ Auth::user()->name }}</span>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/80 text-emerald-800 dark:text-emerald-200 uppercase">{{ Auth::user()->role }}</span>
                                </div>
                                <span class="text-[11px] text-emerald-700 dark:text-emerald-400 font-medium">Logged in staff</span>
                            </div>
                            <input type="hidden" name="administered_by" value="{{ Auth::user()->name }}">
                        </div>

                        {{-- Modal Actions --}}
                        <div class="pt-3 border-t border-gray-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button type="button" @click="showAddModal = false" class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer">Cancel</button>
                            <button type="submit" class="px-5 py-2 text-xs sm:text-sm font-bold text-white bg-[#199CA4] hover:bg-[#13787F] rounded-xl shadow-xs transition cursor-pointer">Save Record</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal 2: History Timeline Modal --}}
        <div x-show="showHistoryModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-sm" @click="showHistoryModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block px-5 sm:px-6 pt-5 pb-6 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-[#0e1d20] rounded-3xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100 dark:border-slate-800">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-gray-100 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-[#199CA4] to-[#13787F] text-white flex items-center justify-center text-sm font-bold shadow-sm">
                                📜
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-gray-900 dark:text-white">Medical History Timeline</h3>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    Pet: <span class="font-bold text-[#199CA4] dark:text-[#41C1CB]" x-text="historyPetName"></span> | Adopter: <span class="font-semibold text-gray-700 dark:text-slate-300" x-text="historyAdopterName"></span>
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="showHistoryModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-2xl font-light leading-none cursor-pointer">&times;</button>
                    </div>

                    {{-- Timeline Content --}}
                    <div class="max-h-[420px] overflow-y-auto pr-1 space-y-3.5">
                        <template x-if="historyLogs.length === 0">
                            <div class="py-10 text-center">
                                <div class="w-10 h-10 rounded-2xl bg-gray-100 dark:bg-slate-800 text-gray-400 dark:text-slate-500 flex items-center justify-center text-sm mx-auto mb-2 font-bold">
                                    🩺
                                </div>
                                <p class="text-xs sm:text-sm font-semibold text-gray-700 dark:text-slate-300">No medical records registered yet</p>
                                <p class="text-[11px] text-gray-400 dark:text-slate-500 mt-0.5">Click "+ Record" on the table to add the first vaccine or deworming entry.</p>
                            </div>
                        </template>

                        <div class="space-y-3" x-show="historyLogs.length > 0">
                            <template x-for="log in historyLogs" :key="log.id">
                                <div class="p-3.5 rounded-2xl border border-gray-100 dark:border-slate-800 bg-gray-50/70 dark:bg-[#12272b] hover:bg-white dark:hover:bg-[#15343a] hover:border-[#199CA4]/30 hover:shadow-sm transition">
                                    <div class="flex items-center justify-between gap-2 mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                                :class="log.category === 'vaccination' ? 'bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300' : 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300'"
                                                x-text="log.category === 'vaccination' ? 'Vaccination' : 'Deworming'">
                                            </span>
                                            <span class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white" x-text="log.name"></span>
                                        </div>
                                        <span class="text-xs font-semibold text-gray-500 dark:text-slate-400" x-text="log.date"></span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-slate-400 mt-1">
                                        <span x-show="log.administered_by" class="font-semibold text-gray-700 dark:text-slate-300" x-text="'Administered by: ' + log.administered_by"></span>
                                        <span x-show="log.next_due_date" class="font-semibold text-amber-700 dark:text-amber-400" x-text="'Next Due: ' + log.next_due_date"></span>
                                    </div>
                                    <p x-show="log.notes" class="text-xs text-gray-600 dark:text-slate-300 mt-1.5 bg-white dark:bg-[#0a171a] p-2 rounded-lg border border-gray-100 dark:border-slate-800" x-text="log.notes"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-3.5 mt-3.5 border-t border-gray-100 dark:border-slate-800 flex justify-end">
                        <button type="button" @click="showHistoryModal = false" class="px-4 py-2 text-xs font-bold text-gray-700 dark:text-slate-200 bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 dark:hover:bg-slate-700 rounded-xl transition cursor-pointer">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal 3: Monthly Pet Health Reports Modal --}}
        <div x-show="showHealthModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-sm" @click="showHealthModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block px-5 sm:px-6 pt-5 pb-6 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-[#0e1d20] rounded-3xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100 dark:border-slate-800">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-gray-100 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-[#199CA4] to-[#13787F] text-white flex items-center justify-center text-sm font-bold shadow-sm">
                                📋
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-gray-900 dark:text-white">Monthly Pet Health Reports</h3>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    Pet: <span class="font-bold text-[#199CA4] dark:text-[#41C1CB]" x-text="healthPetName"></span> | Adopter: <span class="font-semibold text-gray-700 dark:text-slate-300" x-text="healthAdopterName"></span>
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="showHealthModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-2xl font-light leading-none cursor-pointer">&times;</button>
                    </div>

                    {{-- Reports Content --}}
                    <div class="max-h-[420px] overflow-y-auto pr-1 space-y-3.5">
                        <template x-if="healthLogs.length === 0">
                            <div class="py-10 text-center">
                                <div class="w-10 h-10 rounded-2xl bg-gray-100 dark:bg-slate-800 text-gray-400 dark:text-slate-500 flex items-center justify-center text-sm mx-auto mb-2 font-bold">
                                    📋
                                </div>
                                <p class="text-xs sm:text-sm font-semibold text-gray-700 dark:text-slate-300">No monthly check-ins submitted yet</p>
                                <p class="text-[11px] text-gray-400 dark:text-slate-500 mt-0.5">When the adopter submits monthly photos and health updates via the mobile app, they will appear here.</p>
                            </div>
                        </template>

                        <div class="space-y-3" x-show="healthLogs.length > 0">
                            <template x-for="item in healthLogs" :key="item.id">
                                <div class="p-3.5 rounded-2xl border border-gray-100 dark:border-slate-800 bg-gray-50/70 dark:bg-[#12272b] hover:bg-white dark:hover:bg-[#15343a] hover:border-[#199CA4]/30 hover:shadow-sm transition flex gap-3.5">
                                    <div class="w-20 h-20 rounded-xl overflow-hidden bg-gray-200 dark:bg-slate-700 flex-shrink-0 border border-gray-200 dark:border-slate-700">
                                        <img :src="item.photo_url" alt="Pet Checkin" class="w-full h-full object-cover">
                                    </div>
                                    <div class="min-w-0 flex-1 space-y-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-xs font-bold text-gray-900 dark:text-white" x-text="item.check_in_date"></span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                                :class="{
                                                    'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300': item.health_status === 'healthy',
                                                    'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300': item.health_status === 'minor_issue',
                                                    'bg-red-100 dark:bg-rose-950/60 text-red-800 dark:text-rose-300': item.health_status === 'under_treatment'
                                                }"
                                                x-text="item.health_status === 'healthy' ? 'Healthy & Active' : (item.health_status === 'minor_issue' ? 'Minor Issue' : 'Under Treatment')">
                                            </span>
                                        </div>
                                        <template x-if="item.weight">
                                            <p class="text-[11px] font-semibold text-gray-700 dark:text-slate-300">
                                                Weight: <span class="font-bold text-[#199CA4] dark:text-[#41C1CB]" x-text="item.weight + ' kg'"></span>
                                            </p>
                                        </template>
                                        <template x-if="item.notes">
                                            <p class="text-[11px] text-gray-600 dark:text-slate-300 bg-white dark:bg-[#0a171a] p-2 rounded-lg border border-gray-100 dark:border-slate-800" x-text="item.notes"></p>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-3.5 mt-3.5 border-t border-gray-100 dark:border-slate-800 flex justify-end">
                        <button type="button" @click="showHealthModal = false" class="px-4 py-2 text-xs font-bold text-gray-700 dark:text-slate-200 bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 dark:hover:bg-slate-700 rounded-xl transition cursor-pointer">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal 4: Edit Adopter Shelter Safety Status & Notes Modal --}}
        <div x-show="showStatusModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-sm" @click="showStatusModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block px-5 sm:px-6 pt-5 pb-6 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-[#0e1d20] rounded-3xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100 dark:border-slate-800">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-gray-100 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-[#199CA4] to-[#13787F] text-white flex items-center justify-center text-sm font-bold shadow-sm">
                                🛡️
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-gray-900 dark:text-white">Shelter Safety Status</h3>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    Adopter: <span class="font-bold text-[#199CA4] dark:text-[#41C1CB]" x-text="statusAdopterName"></span>
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="showStatusModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-2xl font-light leading-none cursor-pointer">&times;</button>
                    </div>

                    {{-- Modal Form --}}
                    <form method="POST" :action="'/adopters/' + statusProfileId + '/status'" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        {{-- Status Selection --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Adopter Standing</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-[#12272b] hover:bg-gray-50 dark:hover:bg-[#15343a] cursor-pointer">
                                    <input type="radio" name="status" value="active" x-model="selectedStatus" class="text-[#199CA4] focus:ring-[#199CA4]">
                                    <div>
                                        <span class="text-xs font-bold text-gray-900 dark:text-white block">Active</span>
                                        <span class="text-[11px] text-gray-500 dark:text-slate-400">Standard verified adopter in regular standing.</span>
                                    </div>
                                </label>

                                <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50/30 dark:bg-emerald-950/40 hover:bg-emerald-50 dark:hover:bg-emerald-900/40 cursor-pointer">
                                    <input type="radio" name="status" value="good_standing" x-model="selectedStatus" class="text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 block">Good Standing ⭐</span>
                                        <span class="text-[11px] text-emerald-700 dark:text-emerald-400">Excellent track record with timely check-ins.</span>
                                    </div>
                                </label>

                                <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50/30 dark:bg-amber-950/40 hover:bg-amber-50 dark:hover:bg-amber-900/40 cursor-pointer">
                                    <input type="radio" name="status" value="restricted" x-model="selectedStatus" class="text-amber-600 focus:ring-amber-500">
                                    <div>
                                        <span class="text-xs font-bold text-amber-800 dark:text-amber-300 block">Restricted ⚠️</span>
                                        <span class="text-[11px] text-amber-700 dark:text-amber-400">Requires additional verification or home visits.</span>
                                    </div>
                                </label>

                                <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50/30 dark:bg-rose-950/40 hover:bg-rose-50 dark:hover:bg-rose-900/40 cursor-pointer">
                                    <input type="radio" name="status" value="blacklisted" x-model="selectedStatus" class="text-rose-600 focus:ring-rose-500">
                                    <div>
                                        <span class="text-xs font-bold text-rose-800 dark:text-rose-300 block">Blacklisted 🚫</span>
                                        <span class="text-[11px] text-rose-700 dark:text-rose-400">Banned from future pet adoptions due to violations.</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Internal Shelter Notes --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1">Internal Shelter Remarks (Private)</label>
                            <textarea name="admin_notes" rows="3" x-model="statusAdminNotes" placeholder="Private shelter observations, housing conditions, or warnings..."
                                class="w-full px-3 py-2 text-xs bg-gray-50 dark:bg-[#12272b] border border-gray-200 dark:border-slate-700 text-slate-800 dark:text-white rounded-xl focus:bg-white dark:focus:bg-[#12272b] focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition placeholder-gray-400 dark:placeholder-slate-500"></textarea>
                        </div>

                        {{-- Modal Actions --}}
                        <div class="pt-3 border-t border-gray-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button type="button" @click="showStatusModal = false" class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer">Cancel</button>
                            <button type="submit" class="px-5 py-2 text-xs sm:text-sm font-bold text-white bg-[#199CA4] hover:bg-[#13787F] rounded-xl shadow-xs transition cursor-pointer">Update Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>