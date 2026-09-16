<x-app-layout>
    <div class="max-w-3xl mx-auto py-10 px-4">
        <div class="bg-white dark:bg-[#0e1d20] p-8 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-800">

            <div class="flex items-center gap-3 mb-8 border-b-2 border-[#199CA4] pb-4">
                
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Add Medical Log</h1>
            </div>

            @if($errors->any())
                <div class="mb-6 px-4 py-3 bg-red-50 dark:bg-rose-950/50 border border-red-200 dark:border-rose-800 text-red-700 dark:text-rose-300 rounded-xl">
                    <ul class="list-disc pl-5 text-sm space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('medical-logs.store') }}" method="POST" class="space-y-6" x-data="{ submitting: false }" @submit="if(submitting) { $event.preventDefault(); return false; } submitting = true;">
                @csrf

                @php
                    $petsData = $pets->map(function($p) {
                        return [
                            'id' => $p->id,
                            'name' => $p->name ?: ('Pet no. ' . $p->id),
                            'breed' => $p->breed ?? 'Mixed',
                            'type' => ucfirst($p->type ?? 'Dog'),
                            'color' => $p->color ?? '',
                            'gender' => ucfirst($p->gender ?? ''),
                            'status' => ucfirst($p->status ?? 'Available'),
                            'photo_url' => $p->photo_path ? asset('storage/' . ltrim($p->photo_path, '/')) : null,
                        ];
                    });
                    $initialPetId = old('pet_id', optional($pet)->id ?: request('pet_id', ''));
                @endphp

                <div x-data="{
                    pets: @js($petsData),
                    selectedPetId: '{{ $initialPetId }}',
                    selectedPet: null,
                    searchQuery: '',
                    isOpen: false,
                    
                    init() {
                        if (this.selectedPetId) {
                            this.selectById(this.selectedPetId);
                        }
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
                    
                    selectPet(pet) {
                        this.selectedPet = pet;
                        this.selectedPetId = pet.id;
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
                        }).slice(0, 8);
                    },
                    
                    handleEnter() {
                        const q = this.searchQuery.trim();
                        if (!q) return;
                        const exactIdMatch = this.pets.find(p => String(p.id) === q);
                        if (exactIdMatch) {
                            this.selectPet(exactIdMatch);
                            return;
                        }
                        if (this.filteredPets.length > 0) {
                            this.selectPet(this.filteredPets[0]);
                        }
                    }
                }" class="relative">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2 flex items-center justify-between">
                        <span>Pet Selection</span>
                        <span class="text-[11px] font-medium text-slate-400 dark:text-slate-500 normal-case">Search by Pet ID or Name</span>
                    </label>

                    {{-- Hidden form field for standard submission --}}
                    <input type="hidden" name="pet_id" :value="selectedPetId" required>

                    {{-- Search Input (shown when no pet is selected) --}}
                    <div x-show="!selectedPet" class="relative">
                        <div class="relative">
                            <input 
                                x-ref="petSearchInput"
                                type="text" 
                                x-model="searchQuery"
                                @focus="isOpen = true"
                                @click.away="isOpen = false"
                                @keydown.enter.prevent="handleEnter()"
                                placeholder="Type Pet ID (e.g. 5, 12) or name..."
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-[#12272b] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white text-sm placeholder-slate-400 dark:placeholder-slate-500 font-medium"
                            />
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <template x-if="searchQuery">
                                <button type="button" @click="searchQuery = ''" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </template>
                        </div>

                        {{-- Dropdown Search Results --}}
                        <div 
                            x-show="isOpen && searchQuery.trim().length > 0"
                            x-transition
                            class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-[#12272b] rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl z-30 max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800"
                            style="display: none;"
                        >
                            <template x-for="p in filteredPets" :key="p.id">
                                <div 
                                    @click="selectPet(p)"
                                    class="p-3 hover:bg-[#199CA4]/10 dark:hover:bg-white/[0.06] cursor-pointer flex items-center justify-between transition-colors"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700">
                                            <template x-if="p.photo_url">
                                                <img :src="p.photo_url" class="w-full h-full object-cover" />
                                            </template>
                                            <template x-if="!p.photo_url">
                                                <svg class="w-4 h-4 text-[#199CA4]" fill="currentColor" viewBox="0 0 512 512">
                                                    <path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/>
                                                </svg>
                                            </template>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-sm text-slate-800 dark:text-white" x-text="p.name"></span>
                                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#199CA4]/10 text-[#199CA4] dark:bg-[#199CA4]/20 dark:text-[#41C1CB]" x-text="'ID: #' + p.id"></span>
                                            </div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400" x-text="p.type + ' • ' + p.breed + (p.color ? ' • ' + p.color : '')"></p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-semibold text-[#199CA4] hover:underline">Select</span>
                                </div>
                            </template>

                            <div x-show="filteredPets.length === 0" class="p-4 text-center text-sm text-slate-500 dark:text-slate-400">
                                <span>No pet found matching "</span><span class="font-bold text-slate-700 dark:text-slate-300" x-text="searchQuery"></span><span>". Check the Pet ID and try again.</span>
                            </div>
                        </div>
                    </div>

                    {{-- Confirmed Selected Pet Card --}}
                    <template x-if="selectedPet">
                        <div class="p-3.5 rounded-xl border border-[#199CA4]/30 bg-[#199CA4]/5 dark:bg-[#199CA4]/10 flex items-center justify-between transition-all">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-white dark:bg-[#12272b] border border-slate-200 dark:border-slate-700 overflow-hidden flex items-center justify-center shrink-0 shadow-2xs">
                                    <template x-if="selectedPet.photo_url">
                                        <img :src="selectedPet.photo_url" class="w-full h-full object-cover" />
                                    </template>
                                    <template x-if="!selectedPet.photo_url">
                                        <svg class="w-5 h-5 text-[#199CA4]" fill="currentColor" viewBox="0 0 512 512">
                                            <path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/>
                                        </svg>
                                    </template>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-extrabold text-sm text-slate-900 dark:text-white" x-text="selectedPet.name"></span>
                                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-[#199CA4] text-white" x-text="'ID #' + selectedPet.id"></span>
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300" x-text="selectedPet.status"></span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" x-text="selectedPet.type + ' • ' + selectedPet.breed + (selectedPet.color ? ' • ' + selectedPet.color : '') + (selectedPet.gender ? ' • ' + selectedPet.gender : '')"></p>
                                </div>
                            </div>
                            <button 
                                type="button" 
                                @click="clearPet()"
                                class="inline-flex items-center gap-1 text-xs font-bold text-[#199CA4] hover:text-[#13787F] dark:text-[#41C1CB] px-3 py-1.5 rounded-lg border border-[#199CA4]/30 hover:bg-[#199CA4]/10 transition cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                <span>Change Pet</span>
                            </button>
                        </div>
                    </template>
                </div>

                <div x-data="{ 
                    category: '{{ old('category', 'vaccination') }}',
                    date: '{{ old('date', now()->format('Y-m-d')) }}',
                    nextDueDate: '{{ old('next_due_date', '') }}',
                    calcDueDate(months) {
                        if (!this.date) return '';
                        const d = new Date(this.date + 'T00:00:00');
                        if (isNaN(d.getTime())) return '';
                        d.setMonth(d.getMonth() + months);
                        const yyyy = d.getFullYear();
                        const mm = String(d.getMonth() + 1).padStart(2, '0');
                        const dd = String(d.getDate()).padStart(2, '0');
                        return `${yyyy}-${mm}-${dd}`;
                    },
                    get formattedDueDate() {
                        if (!this.nextDueDate) {
                            return (this.category === 'deworming') ? 'None (Optional)' : 'Select date';
                        }
                        const d = new Date(this.nextDueDate + 'T00:00:00');
                        if (isNaN(d.getTime())) return this.nextDueDate;
                        return d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                    },
                    updateForCategory(cat) {
                        if (cat === 'vaccination') {
                            this.nextDueDate = this.calcDueDate(6);
                        } else {
                            this.nextDueDate = '';
                        }
                    },
                    init() {
                        this.updateForCategory(this.category);
                        this.$watch('category', (val) => {
                            this.updateForCategory(val);
                        });
                        this.$watch('date', () => {
                            if (this.category === 'vaccination') {
                                this.nextDueDate = this.calcDueDate(6);
                            }
                        });
                    }
                }">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Date Administered</label>
                            <input type="date" name="date" x-model="date"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-[#12272b] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white" required>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Category</label>
                            <select name="category" x-model="category" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-[#12272b] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white" required>
                                <option value="vaccination">Vaccination</option>
                                <option value="deworming">Deworming</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400">
                                <span x-show="category === 'vaccination'">Next Due Date</span>
                                <span x-show="category === 'deworming'">Next Due Date (Optional)</span>
                            </label>
                            <template x-if="category === 'deworming' && !nextDueDate">
                                <button type="button" @click="nextDueDate = calcDueDate(3)" class="text-xs text-[#199CA4] hover:underline font-bold cursor-pointer">+3 Months</button>
                            </template>
                            <template x-if="category === 'deworming' && nextDueDate">
                                <button type="button" @click="nextDueDate = ''" class="text-xs text-rose-500 hover:underline font-bold cursor-pointer">Clear</button>
                            </template>
                        </div>
                        <div class="relative flex items-center">
                            <input type="date" name="next_due_date" x-model="nextDueDate" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-[#12272b] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-transparent focus:text-gray-800 dark:focus:text-white cursor-pointer">
                            <span class="absolute left-4 pointer-events-none text-sm font-bold"
                                :class="nextDueDate ? 'text-[#199CA4] dark:text-[#41C1CB]' : 'text-gray-400 dark:text-slate-500'"
                                x-text="formattedDueDate"></span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-2">
                            <span x-show="category === 'vaccination'" class="font-semibold text-[#199CA4] dark:text-[#41C1CB]">Auto-calculated: 6 months ahead for next vaccination booster</span>
                            <span x-show="category === 'deworming'" class="font-normal text-gray-400 dark:text-slate-500">Optional for routine deworming</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 justify-end border-t border-gray-100 dark:border-slate-800 pt-6">
                    <a href="{{ route('medical-logs.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-600 dark:text-slate-300 border border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-800 transition">Cancel</a>
                    <button type="submit"
                        :disabled="submitting"
                        :class="{ 'opacity-60 cursor-not-allowed pointer-events-none': submitting }"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#199CA4] text-white text-sm font-semibold rounded-xl hover:bg-[#13787F] shadow-md shadow-[#199CA4]/10 transition cursor-pointer">
                        <svg x-show="submitting" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="submitting ? 'Saving Entry...' : 'Save Entry'">Save Entry</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>