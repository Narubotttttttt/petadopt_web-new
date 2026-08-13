<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 px-3 sm:px-6 lg:px-8" x-data="adopterMedicalManager()">
        
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 px-4 py-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0 font-bold">
                        ✓
                    </div>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-lg leading-none">&times;</button>
            </div>
        @endif

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#333634] tracking-tight">Adopter Profiles</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#199CA4]/10 text-[#199CA4] border border-[#199CA4]/20">
                        {{ $totalApprovedAdopters }} Adopters ({{ $totalApprovedApplications }} Pets)
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Manage approved adopters, monitor vaccine schedules, and record clinical medical logs.
                </p>
            </div>
            
            {{-- Header Actions --}}
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('medical-logs.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-gray-200 bg-white text-xs sm:text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition shadow-sm">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    <span>Medical Logs</span>
                </a>
                <a href="{{ route('adoption-applications.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#199CA4] text-xs sm:text-sm font-semibold text-white hover:bg-[#13787F] transition shadow-sm">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Review Requests</span>
                </a>
            </div>
        </div>

        {{-- Main Container (Single-page, no side scrolling) --}}
        <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            
            {{-- Controls Bar: Search & Status Filter Tabs --}}
            <div class="p-4 sm:p-5 border-b border-gray-100 bg-gray-50/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    
                    {{-- Filter Tabs --}}
                    <div class="flex flex-wrap items-center gap-1 p-1 bg-gray-100/90 rounded-xl border border-gray-200/70">
                        <a href="{{ route('adopters.index', array_merge(request()->except('filter', 'page'), ['filter' => 'all'])) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $filter === 'all' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                            All ({{ $totalApprovedAdopters }})
                        </a>
                        <a href="{{ route('adopters.index', array_merge(request()->except('filter', 'page'), ['filter' => 'overdue'])) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $filter === 'overdue' ? 'bg-red-500 text-white shadow-sm' : 'text-red-700 hover:bg-red-50' }}">
                            🚨 Overdue ({{ $overdueCount }})
                        </a>
                        <a href="{{ route('adopters.index', array_merge(request()->except('filter', 'page'), ['filter' => 'due_soon'])) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $filter === 'due_soon' ? 'bg-amber-500 text-white shadow-sm' : 'text-amber-700 hover:bg-amber-50' }}">
                            ⏰ Due ({{ $dueSoonCount }})
                        </a>
                        <a href="{{ route('adopters.index', array_merge(request()->except('filter', 'page'), ['filter' => 'up_to_date'])) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $filter === 'up_to_date' ? 'bg-emerald-600 text-white shadow-sm' : 'text-emerald-700 hover:bg-emerald-50' }}">
                            ✨ Valid
                        </a>
                    </div>

                    {{-- Search Form --}}
                    <form method="GET" action="{{ route('adopters.index') }}" class="flex items-center gap-2">
                        @if(request('filter'))
                            <input type="hidden" name="filter" value="{{ request('filter') }}">
                        @endif
                        <div class="relative w-full sm:w-64">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text"
                                   name="search"
                                   value="{{ $search }}"
                                   placeholder="Search by Adopter ID (ADP-0001), name, email, pet..."
                                   class="w-full pl-8 pr-7 py-1.5 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#199CA4]/30 focus:border-[#199CA4] transition">
                            @if($search)
                                <a href="{{ route('adopters.index', request()->except('search', 'page')) }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600 font-bold text-sm">
                                    &times;
                                </a>
                            @endif
                        </div>
                        <button type="submit" class="px-3.5 py-1.5 bg-[#199CA4] hover:bg-[#13787F] text-white font-semibold rounded-xl text-xs sm:text-sm transition shadow-sm">
                            Search
                        </button>
                    </form>
                </div>
            </div>

            {{-- 1. Desktop Grouped Layout (Fits 100% width cleanly) --}}
            <div class="hidden lg:block w-full">
                <table class="w-full text-left border-collapse table-auto">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                            <th class="px-5 py-3.5 w-4/12">Adopter Profile</th>
                            <th class="px-5 py-3.5 w-8/12">Adopted Companion(s) & Health Records</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs sm:text-sm">
                        @forelse($adopters as $adopter)
                            @php
                                $nameParts = explode(' ', trim($adopter->applicant_name));
                                $initials = strtoupper(substr($nameParts[0] ?? 'A', 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                                $hasMultiple = $adopter->pets_count > 1;
                            @endphp
                            <tr class="hover:bg-teal-50/10 transition-colors">
                                
                                {{-- Adopter Info Column --}}
                                <td class="px-5 py-4 align-top">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#199CA4] to-[#13787F] text-white font-bold text-xs flex items-center justify-center shadow-sm flex-shrink-0">
                                            {{ $initials }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center flex-wrap gap-1.5">
                                                <p class="font-bold text-gray-900 text-sm">
                                                    {{ $adopter->applicant_name }}
                                                </p>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-black bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                    🏷️ {{ $adopter->adopter_id_code }}
                                                </span>
                                                @if($hasMultiple)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#199CA4]/10 text-[#199CA4] border border-[#199CA4]/20">
                                                        🐾 {{ $adopter->pets_count }} Pets
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-gray-400 mt-0.5 truncate">
                                                {{ $adopter->applicant_email ?? 'No email' }}
                                            </p>
                                            @if($adopter->applicant_phone)
                                                <p class="text-[11px] text-gray-500 mt-0.5">
                                                    📞 {{ $adopter->applicant_phone }}
                                                </p>
                                            @endif
                                            <span class="text-[10px] text-gray-400 block mt-1">
                                                Latest adoption: {{ $adopter->latest_updated_at ? \Carbon\Carbon::parse($adopter->latest_updated_at)->format('M d, Y') : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Adopted Pets Column (Nested cleanly for single or multiple pets) --}}
                                <td class="px-5 py-3.5 align-middle">
                                    <div class="space-y-2.5">
                                        @foreach($adopter->applications as $app)
                                            @php
                                                $pet = $app->pet;
                                                $latestLog = $pet ? $pet->medicalLogs->first() : null;
                                                $vaccineLogs = $pet ? $pet->medicalLogs->where('category', 'vaccination') : collect();
                                                $latestVaccine = $vaccineLogs->first();
                                            @endphp
                                            <div class="p-3 rounded-2xl border border-gray-100 bg-gray-50/60 hover:bg-white hover:border-[#199CA4]/30 hover:shadow-sm transition flex items-center justify-between gap-3">
                                                
                                                {{-- Pet Identity --}}
                                                <div class="flex items-center gap-3 min-w-0 w-4/12">
                                                    <div class="w-10 h-10 rounded-xl bg-white border border-gray-200 overflow-hidden flex items-center justify-center flex-shrink-0 shadow-inner">
                                                        @if($pet && $pet->photo_path)
                                                            <img src="{{ asset('storage/'.$pet->photo_path) }}" alt="Pet" class="w-full h-full object-cover">
                                                        @else
                                                            <span class="text-sm">🐾</span>
                                                        @endif
                                                    </div>
                                                    <div class="min-w-0">
                                                        <div class="flex items-center gap-1.5">
                                                            <span class="text-xs sm:text-sm font-bold text-gray-900 truncate">
                                                                {{ $pet->name ?? 'Pet #'.($pet->id ?? '') }}
                                                            </span>
                                                            @if($pet && $pet->gender)
                                                                <span class="text-[11px] font-bold {{ strtolower($pet->gender) === 'male' ? 'text-blue-500' : 'text-pink-500' }}">
                                                                    {{ strtolower($pet->gender) === 'male' ? '♂' : '♀' }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <p class="text-[11px] text-gray-500 truncate capitalize">
                                                            {{ $pet->type ?? 'Pet' }} • {{ $pet->breed ?? 'Mixed' }}
                                                        </p>
                                                    </div>
                                                </div>

                                                {{-- Pet Medical & Vaccine Status --}}
                                                <div class="w-5/12 min-w-0 space-y-1">
                                                    @if($latestLog)
                                                        <div class="flex items-center gap-1.5">
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $latestLog->category === 'vaccination' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                                                {{ $latestLog->category === 'vaccination' ? '💉 Vaccine' : '💊 Deworming' }}
                                                            </span>
                                                            <span class="text-[11px] text-gray-400">{{ $latestLog->date ? $latestLog->date->format('M d') : '' }}</span>
                                                        </div>
                                                    @else
                                                        <span class="text-[11px] text-gray-400 italic">No medical logs yet</span>
                                                    @endif

                                                    @if($latestVaccine && $latestVaccine->next_due_date)
                                                        @php
                                                            $isPastDue = $latestVaccine->next_due_date->isPast();
                                                            $isSoon = $latestVaccine->next_due_date->isBetween(now(), now()->addDays(14));
                                                        @endphp
                                                        @if($isPastDue)
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 border border-red-200">
                                                                🚨 Overdue: {{ $latestVaccine->next_due_date->format('M d') }}
                                                            </span>
                                                        @elseif($isSoon)
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200">
                                                                ⏰ Due: {{ $latestVaccine->next_due_date->format('M d') }}
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                                🛡️ Valid ({{ $latestVaccine->next_due_date->format('M d') }})
                                                            </span>
                                                        @endif
                                                    @endif
                                                </div>

                                                {{-- Action Buttons per Pet --}}
                                                <div class="flex items-center justify-end gap-1.5 w-3/12">
                                                    @if($pet)
                                                        <button type="button"
                                                            @click="openAddModal({{ $pet->id }}, '{{ addslashes($pet->name ?? 'Pet #'.$pet->id) }}', '{{ addslashes($adopter->applicant_name) }}')"
                                                            class="px-2.5 py-1.5 rounded-lg bg-[#199CA4]/10 text-[#199CA4] hover:bg-[#199CA4] hover:text-white transition font-bold text-[11px] shadow-sm">
                                                            + Log
                                                        </button>

                                                        <button type="button"
                                                            @click="openHistoryModal({{ json_encode($pet->medicalLogs) }}, '{{ addslashes($pet->name ?? 'Pet #'.$pet->id) }}', '{{ addslashes($adopter->applicant_name) }}')"
                                                            class="px-2 py-1.5 rounded-lg border border-gray-200 text-gray-700 bg-white hover:bg-gray-100 transition font-semibold text-[11px]">
                                                            History ({{ $pet->medicalLogs->count() }})
                                                        </button>
                                                    @endif

                                                    <a href="{{ route('adoption-applications.show', $app) }}"
                                                       title="View Application"
                                                       class="p-1 text-gray-400 hover:text-gray-700 rounded hover:bg-gray-100 transition">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-6 py-12 text-center text-gray-400 text-xs">
                                    No adopter profiles found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- 2. Mobile & Tablet Grouped Card Layout --}}
            <div class="lg:hidden divide-y divide-gray-100">
                @forelse($adopters as $adopter)
                    @php
                        $nameParts = explode(' ', trim($adopter->applicant_name));
                        $initials = strtoupper(substr($nameParts[0] ?? 'A', 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                        $hasMultiple = $adopter->pets_count > 1;
                    @endphp
                    <div class="p-4 space-y-3 hover:bg-gray-50/40 transition">
                        
                        {{-- Adopter Header --}}
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#199CA4] to-[#13787F] text-white font-bold text-xs flex items-center justify-center shadow-sm flex-shrink-0">
                                    {{ $initials }}
                                </div>
                                <div>
                                    <div class="flex items-center flex-wrap gap-1.5">
                                        <h4 class="font-bold text-gray-900 text-sm">{{ $adopter->applicant_name }}</h4>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            🏷️ {{ $adopter->adopter_id_code }}
                                        </span>
                                        @if($hasMultiple)
                                            <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#199CA4]/10 text-[#199CA4]">
                                                🐾 {{ $adopter->pets_count }} Pets
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-400">{{ $adopter->applicant_email ?? $adopter->applicant_phone ?? 'No contact' }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Pets Stack --}}
                        <div class="space-y-2">
                            @foreach($adopter->applications as $app)
                                @php
                                    $pet = $app->pet;
                                    $latestVaccine = $pet ? $pet->medicalLogs->where('category', 'vaccination')->first() : null;
                                @endphp
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-lg bg-white border border-gray-200 overflow-hidden flex items-center justify-center flex-shrink-0">
                                                @if($pet && $pet->photo_path)
                                                    <img src="{{ asset('storage/'.$pet->photo_path) }}" alt="Pet" class="w-full h-full object-cover">
                                                @else
                                                    <span>🐾</span>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-900">{{ $pet->name ?? 'Pet #'.($pet->id ?? '') }}</p>
                                                <p class="text-[10px] text-gray-500 capitalize">{{ $pet->type ?? 'Pet' }} • {{ $pet->breed ?? '' }}</p>
                                            </div>
                                        </div>

                                        <a href="{{ route('adoption-applications.show', $app) }}" class="text-xs text-[#199CA4] font-semibold hover:underline">
                                            Application &rarr;
                                        </a>
                                    </div>

                                    @if($pet)
                                        <div class="flex items-center gap-2 pt-1 border-t border-gray-200/60">
                                            <button type="button"
                                                @click="openAddModal({{ $pet->id }}, '{{ addslashes($pet->name ?? 'Pet #'.$pet->id) }}', '{{ addslashes($adopter->applicant_name) }}')"
                                                class="flex-1 py-1.5 rounded-lg bg-[#199CA4] text-white text-xs font-bold hover:bg-[#13787F] transition text-center shadow-sm">
                                                + Log Health
                                            </button>
                                            <button type="button"
                                                @click="openHistoryModal({{ json_encode($pet->medicalLogs) }}, '{{ addslashes($pet->name ?? 'Pet #'.$pet->id) }}', '{{ addslashes($adopter->applicant_name) }}')"
                                                class="flex-1 py-1.5 rounded-lg border border-gray-200 bg-white text-gray-700 text-xs font-semibold hover:bg-gray-50 transition text-center">
                                                History ({{ $pet->medicalLogs->count() }})
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                    </div>
                @empty
                    <div class="p-8 text-center text-gray-400 text-xs">
                        No adopter profiles found.
                    </div>
                @endforelse
            </div>

            {{-- Pagination Footer --}}
            @if($adopters->hasPages())
                <div class="p-3.5 border-t border-gray-100 bg-gray-50/50">
                    {{ $adopters->links() }}
                </div>
            @endif
        </div>

        {{-- Modal 1: Add Medical Log Modal (ONLY Vaccination & Deworming) --}}
        <div x-show="showAddModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-sm" @click="showAddModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block px-5 sm:px-6 pt-5 pb-6 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-3xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-3.5 mb-3.5 border-b border-gray-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-2xl bg-[#199CA4]/10 text-[#199CA4] flex items-center justify-center text-lg font-bold">
                                🩺
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-gray-900">Record Medical Event</h3>
                                <p class="text-xs text-gray-500">Pet: <span class="font-bold text-[#199CA4]" x-text="modalPetName"></span> | Adopter: <span class="font-semibold text-gray-700" x-text="modalAdopterName"></span></p>
                            </div>
                        </div>
                        <button type="button" @click="showAddModal = false" class="text-gray-400 hover:text-gray-600 text-2xl font-light leading-none">&times;</button>
                    </div>

                    <form action="{{ route('medical-logs.store') }}" method="POST" class="space-y-3.5">
                        @csrf
                        <input type="hidden" name="pet_id" :value="modalPetId">

                        {{-- Care Category: ONLY Vaccination & Deworming --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Care Category *</label>
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button"
                                    @click="selectedCategory = 'vaccination'"
                                    :class="selectedCategory === 'vaccination' ? 'bg-blue-600 text-white border-blue-600 shadow-md ring-2 ring-blue-300' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'"
                                    class="py-3 px-3 rounded-2xl text-xs font-bold border transition flex flex-col items-center gap-1.5">
                                    <span class="text-xl">💉</span>
                                    <span class="text-sm">Vaccination</span>
                                    <span class="text-[10px] font-normal" :class="selectedCategory === 'vaccination' ? 'text-blue-100' : 'text-gray-500'">Auto push notification</span>
                                </button>

                                <button type="button"
                                    @click="selectedCategory = 'deworming'"
                                    :class="selectedCategory === 'deworming' ? 'bg-emerald-600 text-white border-emerald-600 shadow-md ring-2 ring-emerald-300' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'"
                                    class="py-3 px-3 rounded-2xl text-xs font-bold border transition flex flex-col items-center gap-1.5">
                                    <span class="text-xl">💊</span>
                                    <span class="text-sm">Deworming</span>
                                    <span class="text-[10px] font-normal" :class="selectedCategory === 'deworming' ? 'text-emerald-100' : 'text-gray-500'">Clinical care record</span>
                                </button>
                            </div>
                            <input type="hidden" name="category" :value="selectedCategory">
                        </div>

                        {{-- Dynamic Reminder Alert Banner --}}
                        <div class="p-3 rounded-2xl text-xs transition border"
                            :class="selectedCategory === 'vaccination' ? 'bg-blue-50/80 text-blue-900 border-blue-200' : 'bg-emerald-50/80 text-emerald-900 border-emerald-200'">
                            <template x-if="selectedCategory === 'vaccination'">
                                <div class="flex items-start gap-2">
                                    <span class="text-base">🔔</span>
                                    <div>
                                        <p class="font-bold text-blue-900">Push Notification Reminder</p>
                                        <p class="text-blue-700 mt-0.5">A mobile reminder & vaccine notification will be scheduled for <strong x-text="modalAdopterName"></strong>.</p>
                                    </div>
                                </div>
                            </template>
                            <template x-if="selectedCategory === 'deworming'">
                                <div class="flex items-start gap-2">
                                    <span class="text-base">💊</span>
                                    <div>
                                        <p class="font-bold text-emerald-900">Deworming Record</p>
                                        <p class="text-emerald-700 mt-0.5">Recorded directly in <strong x-text="modalPetName"></strong>'s medical history timeline.</p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Administered By --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Administered By</label>
                            <input type="text"
                                   name="administered_by"
                                   placeholder="e.g. Dr. Santos / Clinic Staff"
                                   class="w-full rounded-xl border-gray-200 text-xs sm:text-sm focus:border-[#199CA4] focus:ring-[#199CA4] shadow-sm">
                        </div>

                        {{-- Date Grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Date Administered *</label>
                                <input type="date"
                                       name="date"
                                       value="{{ date('Y-m-d') }}"
                                       required
                                       class="w-full rounded-xl border-gray-200 text-xs sm:text-sm focus:border-[#199CA4] focus:ring-[#199CA4] shadow-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Next Due Date
                                </label>
                                <input type="date"
                                       name="next_due_date"
                                       class="w-full rounded-xl border-gray-200 text-xs sm:text-sm focus:border-[#199CA4] focus:ring-[#199CA4] shadow-sm">
                                <span x-show="selectedCategory === 'vaccination'" class="text-[10px] text-blue-600 block mt-0.5">Auto-sets to +6 mos if empty</span>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="pt-3.5 flex items-center justify-end gap-2.5 border-t border-gray-100">
                            <button type="button" @click="showAddModal = false" class="px-3.5 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-xl transition">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-[#199CA4] hover:bg-[#13787F] rounded-xl transition shadow-md flex items-center gap-1">
                                <span>Save Medical Log</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal 2: Medical History Timeline Modal --}}
        <div x-show="showHistoryModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-sm" @click="showHistoryModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block px-5 sm:px-6 pt-5 pb-6 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-3xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-100">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-gray-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-[#199CA4] to-[#13787F] text-white flex items-center justify-center text-lg shadow-sm">
                                📋
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-gray-900">Medical Care Timeline</h3>
                                <p class="text-xs text-gray-500">
                                    Pet: <span class="font-bold text-[#199CA4]" x-text="historyPetName"></span> | Adopter: <span class="font-semibold text-gray-700" x-text="historyAdopterName"></span>
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="showHistoryModal = false" class="text-gray-400 hover:text-gray-600 text-2xl font-light leading-none">&times;</button>
                    </div>

                    {{-- Timeline Body --}}
                    <div class="max-h-[380px] overflow-y-auto pr-1 space-y-3.5">
                        <template x-if="historyLogs.length === 0">
                            <div class="py-10 text-center">
                                <div class="w-10 h-10 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center text-xl mx-auto mb-2">
                                    🩺
                                </div>
                                <p class="text-xs sm:text-sm font-semibold text-gray-700">No medical records yet</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">Records logged for this pet will appear here.</p>
                            </div>
                        </template>

                        {{-- Timeline items --}}
                        <div class="relative pl-5 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-200" x-show="historyLogs.length > 0">
                            <template x-for="(log, idx) in historyLogs" :key="log.id || idx">
                                <div class="relative">
                                    <div class="absolute -left-[23px] top-1.5 w-3.5 h-3.5 rounded-full border-2 border-white shadow-sm"
                                         :class="log.category === 'vaccination' ? 'bg-blue-500 ring-2 ring-blue-100' : 'bg-emerald-500 ring-2 ring-emerald-100'">
                                    </div>

                                    <div class="p-3.5 rounded-2xl border border-gray-100 bg-gray-50/70 hover:bg-white hover:border-[#199CA4]/30 hover:shadow-sm transition">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider"
                                                :class="log.category === 'vaccination' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800'"
                                                x-text="log.category === 'vaccination' ? '💉 Vaccination' : '💊 Deworming'">
                                            </span>
                                            <span class="text-[11px] font-bold text-gray-500" x-text="formatDate(log.date)"></span>
                                        </div>

                                        <div class="mt-2 pt-2 border-t border-gray-200/60 flex flex-wrap items-center justify-between text-[11px] text-gray-600 gap-1.5">
                                            <div>
                                                <span>Administered by: </span>
                                                <strong class="text-gray-900" x-text="log.administered_by || 'Staff / Clinic'"></strong>
                                            </div>
                                            <template x-if="log.next_due_date">
                                                <span class="inline-flex items-center gap-1 text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 font-medium">
                                                    <span>Next Due:</span>
                                                    <strong x-text="formatDate(log.next_due_date)"></strong>
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-3.5 mt-3.5 border-t border-gray-100 flex justify-end">
                        <button type="button" @click="showHistoryModal = false" class="px-4 py-2 text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function adopterMedicalManager() {
            return {
                showAddModal: false,
                showHistoryModal: false,
                modalPetId: null,
                modalPetName: '',
                modalAdopterName: '',
                selectedCategory: 'vaccination',
                historyLogs: [],
                historyPetName: '',
                historyAdopterName: '',

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

                formatDate(dateString) {
                    if (!dateString) return '';
                    const d = new Date(dateString);
                    if (isNaN(d.getTime())) return dateString;
                    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                }
            }
        }
    </script>
</x-app-layout>
