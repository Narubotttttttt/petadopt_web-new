<x-app-layout>
    <div class="w-full max-w-6xl mx-auto py-5 sm:py-8 px-4 sm:px-6 lg:px-8 animate-fade-in space-y-6">

        @php
            $petsData = $pets->map(function($p) {
                $today = now()->startOfDay();
                $latestVaccine = $p->medicalLogs->where('category', 'vaccination')->first();
                $latestDeworming = $p->medicalLogs->where('category', 'deworming')->first();
                
                $vaccineDueDate = $latestVaccine?->next_due_date ? $latestVaccine->next_due_date->copy()->startOfDay() : null;
                $isVaccineOverdue = $vaccineDueDate && $vaccineDueDate->lt($today);
                $isVaccineDueSoon = $vaccineDueDate && !$isVaccineOverdue && $vaccineDueDate->diffInDays($today) <= 30;

                $dewormDueDate = $latestDeworming?->next_due_date ? $latestDeworming->next_due_date->copy()->startOfDay() : null;
                $isDewormOverdue = $dewormDueDate && $dewormDueDate->lt($today);
                $isDewormDueSoon = $dewormDueDate && !$isDewormOverdue && $dewormDueDate->diffInDays($today) <= 30;

                return [
                    'id' => $p->id,
                    'name' => $p->name ?: ('Pet no. ' . $p->id),
                    'breed' => $p->breed ?? 'Mixed Breed',
                    'type' => ucfirst($p->type ?? 'Dog'),
                    'color' => $p->color ?? '',
                    'gender' => ucfirst($p->gender ?? 'Unknown'),
                    'age' => $p->age ?? 'Age N/A',
                    'status' => ucfirst($p->status ?? 'Available'),
                    'medical_history' => $p->medical_history ?? null,
                    'photo_url' => $p->photo_path ? (str_starts_with($p->photo_path, 'http') ? $p->photo_path : asset('storage/' . ltrim($p->photo_path, '/'))) : null,
                    'vaccine_status' => $latestVaccine ? ($isVaccineOverdue ? 'Overdue' : ($isVaccineDueSoon ? 'Due Soon' : 'Up to Date')) : 'None logged',
                    'latest_vaccine_name' => $latestVaccine?->vaccine_name,
                    'vaccine_date' => $latestVaccine?->date ? $latestVaccine->date->format('M d, Y') : null,
                    'vaccine_due_date' => $latestVaccine?->next_due_date ? $latestVaccine->next_due_date->format('M d, Y') : null,
                    'deworming_status' => $latestDeworming ? ($isDewormOverdue ? 'Overdue' : ($isDewormDueSoon ? 'Due Soon' : 'Up to Date')) : 'None logged',
                    'latest_deworming_name' => $latestDeworming?->vaccine_name,
                    'deworming_date' => $latestDeworming?->date ? $latestDeworming->date->format('M d, Y') : null,
                    'deworming_due_date' => $latestDeworming?->next_due_date ? $latestDeworming->next_due_date->format('M d, Y') : null,
                    'recent_logs' => $p->medicalLogs->map(function($l) {
                        return [
                            'id' => $l->id,
                            'category' => ucfirst(str_replace('_', ' ', $l->category)),
                            'raw_category' => $l->category,
                            'vaccine_name' => $l->vaccine_name,
                            'date' => $l->date ? $l->date->format('M d, Y') : '—',
                            'administered_by' => $l->administered_by ?: ($l->creator?->name ?? 'Staff'),
                            'next_due_date' => $l->next_due_date ? $l->next_due_date->format('M d, Y') : null,
                        ];
                    })->values()->all(),
                ];
            });

            $initialPetId = old('pet_id', optional($pet)->id ?: request('pet_id', ''));
        @endphp

        {{-- Top Navigation Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ $pet ? route('pets.show', $pet) : route('medical-logs.index') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white transition mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>{{ $pet ? 'Back to Pet Profile' : 'Back to Medical Logs' }}</span>
                </a>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Add Medical Log</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Record shelter immunizations and routine dewormings with automatic adopter push reminders.</p>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-auto">
                <a href="{{ route('medical-logs.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-[#171923] text-xs font-bold transition shadow-2xs">
                    View All Logs
                </a>
            </div>
        </div>

        @if(isset($errors) && $errors->any())
            <div role="alert" class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-rose-700 dark:text-rose-300 rounded-2xl text-xs space-y-1 shadow-2xs">
                <p class="font-extrabold">Please check the required fields:</p>
                <ul class="list-disc pl-5 space-y-0.5 font-medium">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Alpine Container for Form & Dynamic Dossier --}}
        <div 
            x-data="{
                pets: @js($petsData),
                selectedPetId: '{{ $initialPetId }}',
                selectedPet: null,
                searchQuery: '',
                isOpen: false,
                submitting: false,

                category: '{{ old('category', 'vaccination') }}',
                vaccineName: '{{ old('vaccine_name', '') }}',
                date: '{{ old('date', now()->format('Y-m-d')) }}',
                administeredBy: '{{ old('administered_by', Auth::user()->name) }}',
                nextDueDate: '{{ old('next_due_date', '') }}',

                init() {
                    if (this.selectedPetId) {
                        this.selectById(this.selectedPetId);
                    }
                    this.updateForCategory(this.category);
                    this.$watch('category', (val) => this.updateForCategory(val));
                    this.$watch('date', () => {
                        if (this.category === 'vaccination') {
                            this.nextDueDate = this.calcDueDate(6);
                        } else if (this.category === 'deworming') {
                            this.nextDueDate = this.calcDueDate(3);
                        }
                    });
                },

                selectById(id) {
                    const found = this.pets.find(p => String(p.id) === String(id).trim());
                    if (found) {
                        this.selectedPet = found;
                        this.selectedPetId = found.id;
                        this.searchQuery = '';
                        this.isOpen = false;
                    }
                },

                selectPet(p) {
                    this.selectedPet = p;
                    this.selectedPetId = p.id;
                    this.searchQuery = '';
                    this.isOpen = false;
                },

                clearPet() {
                    this.selectedPet = null;
                    this.selectedPetId = '';
                    this.searchQuery = '';
                    this.$nextTick(() => {
                        this.$refs.petSearchInput?.focus();
                    });
                },

                get filteredPets() {
                    const q = this.searchQuery.trim().toLowerCase();
                    if (!q) return [];
                    return this.pets.filter(p => {
                        const idStr = String(p.id);
                        return idStr === q || 
                               idStr.startsWith(q) || 
                               idStr.includes(q) || 
                               p.name.toLowerCase().includes(q) || 
                               p.breed.toLowerCase().includes(q);
                    }).slice(0, 10);
                },

                handleEnter() {
                    const q = this.searchQuery.trim();
                    if (!q) return;
                    const exact = this.pets.find(p => String(p.id) === q);
                    if (exact) {
                        this.selectPet(exact);
                        return;
                    }
                    if (this.filteredPets.length > 0) {
                        this.selectPet(this.filteredPets[0]);
                    }
                },

                calcDueDate(monthsAhead) {
                    if (!this.date) return '';
                    const d = new Date(this.date);
                    if (isNaN(d.getTime())) return '';
                    d.setMonth(d.getMonth() + monthsAhead);
                    const year = d.getFullYear();
                    const month = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                },

                updateForCategory(cat) {
                    if (cat === 'vaccination') {
                        this.nextDueDate = this.calcDueDate(6);
                    } else if (cat === 'deworming') {
                        this.nextDueDate = this.calcDueDate(3);
                    }
                },

                get formattedDueDate() {
                    if (!this.nextDueDate) return 'None set';
                    const d = new Date(this.nextDueDate + 'T00:00:00');
                    if (isNaN(d.getTime())) return this.nextDueDate;
                    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                }
            }"
            class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start"
        >

            {{-- LEFT COLUMN: 7 cols - Clinical Recording Workstation Form --}}
            <div class="lg:col-span-7 bg-white dark:bg-[#12141C] p-5 sm:p-7 rounded-2xl sm:rounded-3xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] space-y-6">

                <form method="POST" action="{{ route('medical-logs.store') }}" class="space-y-6" @submit="submitting = true">
                    @csrf

                    <input type="hidden" name="pet_id" :value="selectedPetId" required>
                    <input type="hidden" name="category" :value="category">

                    {{-- 1. Patient Selection --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                Search by Pet ID or Name
                            </label>
                            <span class="text-[11px] font-medium text-slate-400">Search by Pet ID or Name</span>
                        </div>

                        {{-- Search Input (shown when no pet confirmed or when searching) --}}
                        <div class="relative" x-show="!selectedPet">
                            <div class="relative">
                                <input 
                                    x-ref="petSearchInput"
                                    type="text" 
                                    x-model="searchQuery" 
                                    @focus="isOpen = true"
                                    @keydown.enter.prevent="handleEnter()"
                                    @click.away="isOpen = false"
                                    placeholder="Type Pet ID (e.g. 1, 31) or name..."
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50/50 dark:bg-[#171923] focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition outline-none text-slate-800 dark:text-slate-100 text-sm font-semibold placeholder-slate-400 dark:placeholder-slate-500"
                                />
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                            </div>

                            {{-- Dropdown Results --}}
                            <div 
                                x-show="isOpen && searchQuery.trim().length > 0"
                                x-transition
                                class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-[#171923] rounded-2xl border border-slate-200 dark:border-white/[0.08] shadow-xl z-30 max-h-60 overflow-y-auto divide-y divide-slate-100 dark:divide-white/[0.06]"
                                style="display: none;"
                            >
                                <template x-for="p in filteredPets" :key="p.id">
                                    <div 
                                        @click="selectPet(p)"
                                        class="p-3 hover:bg-[#199CA4]/10 dark:hover:bg-white/[0.06] cursor-pointer flex items-center justify-between transition-colors"
                                    >
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-[#12141C] overflow-hidden flex items-center justify-center shrink-0 border border-slate-200 dark:border-white/[0.08]">
                                                <template x-if="p.photo_url">
                                                    <img :src="p.photo_url" class="w-full h-full object-cover" />
                                                </template>
                                                <template x-if="!p.photo_url">
                                                    <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                                </template>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-extrabold text-xs text-slate-900 dark:text-white truncate" x-text="p.name"></span>
                                                    <span class="text-[10px] font-mono font-bold text-[#199CA4] dark:text-[#41C1CB]" x-text="'#' + p.id"></span>
                                                </div>
                                                <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate" x-text="p.type + ' • ' + p.breed"></p>
                                            </div>
                                        </div>
                                        <span class="text-xs font-bold text-[#199CA4] dark:text-[#41C1CB]">Select</span>
                                    </div>
                                </template>
                                <div x-show="filteredPets.length === 0" class="p-3 text-center text-xs text-slate-400">
                                    No pet found matching "<span class="font-bold" x-text="searchQuery"></span>".
                                </div>
                            </div>
                        </div>

                        {{-- Confirmed Selected Pet Card --}}
                        <template x-if="selectedPet">
                            <div class="p-3.5 rounded-2xl border border-slate-200/80 dark:border-white/[0.08] bg-slate-50/70 dark:bg-[#171923] flex items-center justify-between gap-3 transition-all">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-12 h-12 rounded-xl bg-white dark:bg-[#12141C] border border-slate-200 dark:border-white/[0.08] overflow-hidden flex items-center justify-center shrink-0 shadow-2xs">
                                        <template x-if="selectedPet.photo_url">
                                            <img :src="selectedPet.photo_url" class="w-full h-full object-cover" />
                                        </template>
                                        <template x-if="!selectedPet.photo_url">
                                            <svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                        </template>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-extrabold text-sm text-slate-900 dark:text-white truncate" x-text="selectedPet.name"></span>
                                            <span class="text-[10px] font-mono font-bold text-[#199CA4] dark:text-[#41C1CB] bg-[#199CA4]/10 dark:bg-[#199CA4]/20 px-1.5 py-0.5 rounded" x-text="'ID #' + selectedPet.id"></span>
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full border border-slate-200 dark:border-white/[0.08] text-slate-600 dark:text-slate-300" x-text="selectedPet.status"></span>
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate" x-text="selectedPet.type + ' • ' + selectedPet.breed + (selectedPet.color ? ' • ' + selectedPet.color : '') + ' • ' + selectedPet.gender"></p>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    @click="clearPet()"
                                    class="inline-flex items-center gap-1 text-xs font-bold text-[#199CA4] hover:text-[#13787F] dark:text-[#41C1CB] px-3 py-1.5 rounded-xl border border-[#199CA4]/30 bg-white dark:bg-[#12141C] hover:bg-[#199CA4]/10 transition shadow-2xs shrink-0 cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    <span>Change</span>
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- 2. Clinical Category Selector (Strictly Vaccination & Deworming) --}}
                    <div class="space-y-2">
                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                            Clinical Record Type
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            {{-- Vaccination Card --}}
                            <button 
                                type="button" 
                                @click="category = 'vaccination'; if (!vaccineName) vaccineName = '5-in-1 (DHPP)'; nextDueDate = calcDueDate(6);"
                                :class="category === 'vaccination' ? 'border-[#199CA4] bg-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB] ring-2 ring-[#199CA4]/20' : 'border-slate-200/80 dark:border-white/[0.08] bg-slate-50/50 dark:bg-[#171923] text-slate-700 dark:text-slate-300 hover:bg-slate-100/70 dark:hover:bg-[#1D1F2C]'"
                                class="p-3.5 rounded-2xl border text-left transition-all cursor-pointer flex items-center gap-3"
                            >
                                <div class="w-10 h-10 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </div>
                                <div>
                                    <span class="block text-sm font-extrabold">Vaccination</span>
                                    <span class="block text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Immunization & Booster</span>
                                </div>
                            </button>

                            {{-- Deworming Card --}}
                            <button 
                                type="button" 
                                @click="category = 'deworming'; nextDueDate = calcDueDate(3);"
                                :class="category === 'deworming' ? 'border-[#4F46E5] bg-[#4F46E5]/10 text-[#4F46E5] dark:text-[#818CF8] ring-2 ring-[#4F46E5]/20' : 'border-slate-200/80 dark:border-white/[0.08] bg-slate-50/50 dark:bg-[#171923] text-slate-700 dark:text-slate-300 hover:bg-slate-100/70 dark:hover:bg-[#1D1F2C]'"
                                class="p-3.5 rounded-2xl border text-left transition-all cursor-pointer flex items-center gap-3"
                            >
                                <div class="w-10 h-10 rounded-xl bg-[#4F46E5]/10 dark:bg-[#6366F1]/20 text-[#4F46E5] dark:text-[#818CF8] flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                </div>
                                <div>
                                    <span class="block text-sm font-extrabold">Deworming</span>
                                    <span class="block text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Routine Parasite Dose</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    {{-- 3. Vaccine or Medicine Detail Field with Quick-Select Chips --}}
                    <div class="space-y-2 p-4 rounded-2xl bg-slate-50/70 dark:bg-[#171923] border border-slate-200/80 dark:border-white/[0.06]">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                <span x-text="category === 'vaccination' ? 'Vaccine Type / Antigen' : 'Deworming Medicine / Brand'"></span>
                            </label>
                            <span class="text-[11px] text-slate-400">Select preset or type custom</span>
                        </div>

                        {{-- Quick Select Chips --}}
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            <template x-if="category === 'vaccination'">
                                <div class="flex flex-wrap gap-1.5">
                                    @php
                                        $vaccinePresets = [
                                            '5-in-1 (DHPP)',
                                            '6-in-1',
                                            '8-in-1',
                                            'Anti-Rabies',
                                            'Bordetella (Kennel Cough)',
                                            '4-in-1 (FVRCP)',
                                            'FeLV',
                                        ];
                                    @endphp
                                    @foreach($vaccinePresets as $vp)
                                        <button 
                                            type="button" 
                                            @click="vaccineName = '{{ $vp }}'"
                                            :class="vaccineName === '{{ $vp }}' ? 'bg-[#199CA4] text-white' : 'bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-300 hover:bg-[#199CA4]/10 border border-slate-200 dark:border-white/[0.08]'"
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold transition shadow-2xs cursor-pointer"
                                        >
                                            {{ $vp }}
                                        </button>
                                    @endforeach
                                </div>
                            </template>

                            <template x-if="category === 'deworming'">
                                <div class="flex flex-wrap gap-1.5">
                                    @php
                                        $dewormingPresets = [
                                            'Canex Puppy/Dog',
                                            'Pyrantel Embonate',
                                            'Drontal Plus',
                                            'Heartgard Plus',
                                            'Milbemax',
                                        ];
                                    @endphp
                                    @foreach($dewormingPresets as $dp)
                                        <button 
                                            type="button" 
                                            @click="vaccineName = '{{ $dp }}'"
                                            :class="vaccineName === '{{ $dp }}' ? 'bg-[#4F46E5] text-white' : 'bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-300 hover:bg-[#4F46E5]/10 border border-slate-200 dark:border-white/[0.08]'"
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold transition shadow-2xs cursor-pointer"
                                        >
                                            {{ $dp }}
                                        </button>
                                    @endforeach
                                </div>
                            </template>
                        </div>

                        <input 
                            type="text" 
                            name="vaccine_name" 
                            x-model="vaccineName"
                            :placeholder="category === 'vaccination' ? 'e.g. 5-in-1 (DHPP), Anti-Rabies, 6-in-1...' : 'e.g. Canex, Pyrantel Embonate...'"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C] text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] shadow-2xs mt-2"
                        >
                    </div>

                    {{-- 4. Date & Attending Staff (2 cols) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                Date Administered
                            </label>
                            <input 
                                type="date" 
                                name="date" 
                                x-model="date"
                                required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50/50 dark:bg-[#171923] text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] shadow-2xs"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                Administered By
                            </label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="administered_by" 
                                    x-model="administeredBy"
                                    readonly
                                    class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-100/90 dark:bg-white/[0.04] text-xs font-bold text-slate-700 dark:text-slate-300 cursor-not-allowed select-none shadow-2xs focus:outline-none"
                                >
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Next Due Date & Booster Schedule --}}
                    <div class="p-4 rounded-2xl bg-slate-50/70 dark:bg-[#171923] border border-slate-200/80 dark:border-white/[0.06] space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="block text-xs font-extrabold text-slate-800 dark:text-white uppercase tracking-wider">
                                    Next Booster / Due Date
                                </span>
                                <span class="block text-[11px] text-slate-400 dark:text-slate-500 mt-0.5" x-text="formattedDueDate"></span>
                            </div>
                            
                            {{-- Quick Preset Buttons --}}
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <button 
                                    type="button" 
                                    @click="nextDueDate = calcDueDate(6)"
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-300 hover:bg-[#199CA4]/10 transition shadow-2xs cursor-pointer"
                                >
                                    +6 Mos (Core Booster)
                                </button>
                                <button 
                                    type="button" 
                                    @click="nextDueDate = calcDueDate(12)"
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-300 hover:bg-[#199CA4]/10 transition shadow-2xs cursor-pointer"
                                >
                                    +1 Year (Annual)
                                </button>
                                <button 
                                    type="button" 
                                    @click="nextDueDate = calcDueDate(3)"
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-300 hover:bg-[#199CA4]/10 transition shadow-2xs cursor-pointer"
                                >
                                    +3 Mos (Deworming)
                                </button>
                                <button 
                                    type="button" 
                                    @click="nextDueDate = ''"
                                    class="px-2 py-1 rounded-lg text-[11px] font-bold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition cursor-pointer"
                                >
                                    Clear
                                </button>
                            </div>
                        </div>

                        <input 
                            type="date" 
                            name="next_due_date" 
                            x-model="nextDueDate"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C] text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] shadow-2xs"
                        >

                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Once this pet is adopted, CAWS push notifications are automatically dispatched to the adopter's mobile app at 1 month, 7 days, 3 days, and on the day of vaccination.
                        </p>
                    </div>

                    {{-- Form Actions --}}
                    <div class="pt-2 flex items-center justify-end gap-3">
                        <a 
                            href="{{ $pet ? route('pets.show', $pet) : route('medical-logs.index') }}" 
                            class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C] hover:bg-slate-50 dark:hover:bg-[#171923] text-slate-700 dark:text-slate-300 text-xs font-bold transition shadow-2xs cursor-pointer"
                        >
                            Cancel
                        </a>
                        <button 
                            type="submit" 
                            :disabled="!selectedPetId || submitting"
                            :class="(!selectedPetId || submitting) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#13787F] cursor-pointer shadow-xs'"
                            class="px-6 py-2.5 rounded-xl bg-[#199CA4] text-white text-xs font-bold transition-all duration-200 flex items-center gap-2"
                        >
                            <svg x-show="submitting" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span x-text="submitting ? 'Saving Entry...' : 'Save Entry'"></span>
                        </button>
                    </div>
                </form>

            </div>

            {{-- RIGHT COLUMN: 5 cols - Live Pet Card & Treatment Timeline --}}
            <div class="lg:col-span-5 space-y-6">

                {{-- Card 1: Official Pet Card Preview --}}
                <div class="bg-white dark:bg-[#12141C] p-5 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] space-y-4">
                    
                    <template x-if="selectedPet">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-white/[0.06]">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-white">Pet Card</span>
                                    <span class="text-[10px] font-bold text-[#199CA4] dark:text-[#41C1CB] bg-[#199CA4]/10 dark:bg-[#199CA4]/20 px-2 py-0.5 rounded-full">Mobile Passport Preview</span>
                                </div>
                                <a :href="'/pets/' + selectedPet.id" target="_blank" class="text-xs font-bold text-[#199CA4] dark:text-[#41C1CB] hover:underline inline-flex items-center gap-1">
                                    <span>Profile</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </div>

                            {{-- Patient Header --}}
                            <div class="flex items-center gap-3">
                                <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-[#171923] overflow-hidden flex items-center justify-center shrink-0 border border-slate-200 dark:border-white/[0.08] shadow-2xs">
                                    <template x-if="selectedPet.photo_url">
                                        <img :src="selectedPet.photo_url" class="w-full h-full object-cover" />
                                    </template>
                                    <template x-if="!selectedPet.photo_url">
                                        <svg class="w-6 h-6 text-slate-400" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                    </template>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-extrabold text-base text-slate-900 dark:text-white truncate" x-text="selectedPet.name"></h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate" x-text="selectedPet.breed + ' • ' + selectedPet.gender + ' • ' + selectedPet.age"></p>
                                    <p class="text-[11px] font-mono text-[#199CA4] dark:text-[#41C1CB] mt-0.5" x-text="'Registry CAWS-PET-' + String(selectedPet.id).padStart(5, '0')"></p>
                                </div>
                            </div>

                            {{-- Pet Card Clinical Overview (Vaccine & Deworming) --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                                {{-- Vaccine Record Tile --}}
                                <div class="p-3 rounded-2xl bg-slate-50/80 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.04] space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400">Vaccination</span>
                                        <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded-full"
                                            :class="{
                                                'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300': selectedPet.vaccine_status === 'Up to Date',
                                                'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300': selectedPet.vaccine_status === 'Due Soon',
                                                'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300': selectedPet.vaccine_status === 'Overdue',
                                                'bg-slate-100 text-slate-500 dark:bg-white/[0.06] dark:text-slate-400': selectedPet.vaccine_status === 'None logged'
                                            }"
                                            x-text="selectedPet.vaccine_status"
                                        ></span>
                                    </div>
                                    <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 block truncate" x-text="selectedPet.latest_vaccine_name || 'Core Vaccine'"></span>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between pt-0.5">
                                        <span x-show="selectedPet.vaccine_date" x-text="'Dose: ' + selectedPet.vaccine_date"></span>
                                        <span class="font-mono font-bold text-[#199CA4] dark:text-[#41C1CB]" x-show="selectedPet.vaccine_due_date" x-text="'Due: ' + selectedPet.vaccine_due_date"></span>
                                    </div>
                                </div>

                                {{-- Deworming Record Tile --}}
                                <div class="p-3 rounded-2xl bg-slate-50/80 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.04] space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400">Deworming</span>
                                        <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded-full"
                                            :class="{
                                                'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300': selectedPet.deworming_status === 'Up to Date',
                                                'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300': selectedPet.deworming_status === 'Due Soon',
                                                'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300': selectedPet.deworming_status === 'Overdue',
                                                'bg-slate-100 text-slate-500 dark:bg-white/[0.06] dark:text-slate-400': selectedPet.deworming_status === 'None logged'
                                            }"
                                            x-text="selectedPet.deworming_status"
                                        ></span>
                                    </div>
                                    <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 block truncate" x-text="selectedPet.latest_deworming_name || 'Routine Dewormer'"></span>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between pt-0.5">
                                        <span x-show="selectedPet.deworming_date" x-text="'Dose: ' + selectedPet.deworming_date"></span>
                                        <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400" x-show="selectedPet.deworming_due_date" x-text="'Due: ' + selectedPet.deworming_due_date"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes from pet profile --}}
                            <template x-if="selectedPet.medical_history">
                                <div class="p-3 rounded-xl bg-slate-50/60 dark:bg-white/[0.02] border border-slate-100 dark:border-white/[0.04] text-xs text-slate-600 dark:text-slate-400">
                                    <span class="font-bold text-slate-800 dark:text-slate-200">Medical Notes:</span>
                                    <p class="mt-0.5 leading-relaxed" x-text="selectedPet.medical_history"></p>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="!selectedPet">
                        <div class="text-center py-6 text-slate-400 dark:text-slate-500 space-y-2">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-white/[0.04] text-slate-400 flex items-center justify-center mx-auto">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <p class="font-bold text-xs text-slate-700 dark:text-slate-300">No Patient Selected</p>
                            <p class="text-[11px] max-w-xs mx-auto">Select a pet in the form to view their clinical background, vaccination status, and previous shelter doses.</p>
                        </div>
                    </template>
                </div>

                {{-- Card 2: Patient's Previous Medical Timeline --}}
                <div class="bg-white dark:bg-[#12141C] p-5 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-white/[0.06]">
                        <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                            Clinical History Timeline
                        </span>
                        <template x-if="selectedPet && selectedPet.recent_logs.length > 0">
                            <a :href="'/medical-logs?q=' + selectedPet.id" target="_blank" class="text-[11px] font-bold text-[#199CA4] dark:text-[#41C1CB] hover:underline">
                                View All
                            </a>
                        </template>
                    </div>

                    <template x-if="selectedPet && selectedPet.recent_logs.length > 0">
                        <div class="divide-y divide-slate-100 dark:divide-white/[0.04] border border-slate-100 dark:border-white/[0.04] rounded-xl overflow-hidden">
                            <template x-for="log in selectedPet.recent_logs" :key="log.id">
                                <div class="p-2.5 flex items-center justify-between text-xs hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition">
                                    <div class="min-w-0 pr-2">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-extrabold text-slate-900 dark:text-white" x-text="log.date"></span>
                                            <span 
                                                class="px-1.5 py-0.5 rounded text-[10px] font-bold border"
                                                :class="{
                                                    'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60': log.raw_category === 'vaccination',
                                                    'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60': log.raw_category === 'deworming'
                                                }"
                                                x-text="log.category"
                                            ></span>
                                        </div>
                                        <template x-if="log.vaccine_name">
                                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-200 block truncate mt-0.5" x-text="log.vaccine_name"></span>
                                        </template>
                                        <span class="text-[11px] text-slate-400 dark:text-slate-500 block truncate" x-text="'By: ' + log.administered_by"></span>
                                    </div>
                                    <template x-if="log.next_due_date">
                                        <div class="text-right shrink-0">
                                            <span class="text-[10px] text-slate-400 block">Next Due:</span>
                                            <span class="text-[11px] font-mono font-bold text-amber-600 dark:text-amber-400" x-text="log.next_due_date"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="selectedPet && selectedPet.recent_logs.length === 0">
                        <div class="p-4 text-center text-xs text-slate-400">
                            No clinical records logged yet for this pet. This entry will be their initial record.
                        </div>
                    </template>

                    <template x-if="!selectedPet">
                        <div class="p-4 text-center text-xs text-slate-400">
                            Select a pet to view their treatment timeline.
                        </div>
                    </template>
                </div>

                {{-- Card 3: Shelter Clinical Protocols --}}
                <div class="bg-white dark:bg-[#12141C] p-5 rounded-2xl sm:rounded-3xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] space-y-2.5">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">
                        Shelter Clinical Protocols
                    </span>
                    <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-400">
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#199CA4] mt-1.5 shrink-0"></span>
                            <span><strong>Core Vaccines:</strong> 5-in-1 / DHPP at puppyhood; booster scheduled at 6 months or annually.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#199CA4] mt-1.5 shrink-0"></span>
                            <span><strong>Anti-Rabies:</strong> Mandated annual booster for all dogs & cats.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#199CA4] mt-1.5 shrink-0"></span>
                            <span><strong>Routine Deworming:</strong> Administered every 3 months for internal parasite prevention.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#199CA4] mt-1.5 shrink-0"></span>
                            <span><strong>Mobile Push Alerts:</strong> Automatically sent to adopters at 1 month, 7 days, 3 days, and on booster day.</span>
                        </li>
                    </ul>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>