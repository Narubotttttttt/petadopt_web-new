@php
    $modalTemperamentTags = collect($allTemperamentTags ?? [])
        ->reject(fn($tag) => strcasecmp(trim($tag->name), 'Under Behavioral Observation') === 0)
        ->values();
@endphp

{{-- Adoption Readiness & Launch Modal Component --}}
<div x-data="readinessModalHandler()"
     x-cloak
     x-show="isOpen"
     @open-readiness-modal.window="openModal($event.detail)"
     @keydown.escape.window="closeModal()"
     class="fixed inset-0 z-50 overflow-y-auto" 
     aria-labelledby="readiness-modal-title" 
     role="dialog" 
     aria-modal="true">
    
    {{-- Backdrop --}}
    <div x-show="isOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeModal()"
         class="fixed inset-0 bg-slate-900/60 dark:bg-black/80 backdrop-blur-xs transition-opacity"></div>

    {{-- Modal Dialog Panel --}}
    <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-center">
        <div x-show="isOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.stop
             class="relative transform overflow-hidden rounded-2xl sm:rounded-3xl bg-white dark:bg-[#12141C] text-left shadow-2xl transition-all w-full max-w-2xl border border-slate-200/90 dark:border-white/[0.08]">

            {{-- Form Header --}}
            <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-white/[0.06] flex items-start justify-between gap-4 bg-slate-50/50 dark:bg-white/[0.02]">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center shrink-0 border border-[#199CA4]/20 shadow-2xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white" id="readiness-modal-title">
                                Adoption Readiness & Clearance
                            </h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100/80 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">
                                Step to Publish
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Review clinical records and assign temperament tags for <strong class="text-slate-700 dark:text-slate-200" x-text="petName"></strong>.
                        </p>
                    </div>
                </div>

                <button type="button" 
                        @click="closeModal()" 
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/[0.06] transition cursor-pointer"
                        title="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Form Body --}}
            <form :action="actionUrl" method="POST" @submit="isSubmitting = true">
                @csrf

                {{-- Hidden Inputs for Selected Temperament Tag IDs --}}
                <template x-for="tagId in selectedTags" :key="tagId">
                    <input type="hidden" name="temperament_tags[]" :value="tagId">
                </template>

                <div class="p-4 sm:p-5 space-y-4 max-h-[calc(88vh-130px)] overflow-y-auto no-scrollbar">
                    
                    {{-- Section 1: Clinical & Health Clearance Check --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-white flex items-center gap-1.5">
                                <span>1. Clinical Clearance Check</span>
                            </h4>
                            <span class="text-[11px] text-slate-400 dark:text-slate-500">Core vaccinations & deworming</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            
                            {{-- Core Vaccination Card --}}
                            <div class="p-4 rounded-2xl border transition-all"
                                 :class="hasVaccine ? 'bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-200/80 dark:border-emerald-800/40' : (logVaccine ? 'bg-sky-50/50 dark:bg-sky-950/20 border-sky-300 dark:border-sky-800' : 'bg-amber-50/40 dark:bg-amber-950/20 border-amber-200/80 dark:border-amber-900/40')">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <span class="text-[10px] uppercase font-extrabold tracking-wider text-slate-400 block">Core Vaccination</span>
                                        <div class="mt-1 flex items-center gap-1.5">
                                            <template x-if="hasVaccine">
                                                <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-700 dark:text-emerald-300">
                                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                    <span x-text="'On file: ' + (vaccineName || 'Core vaccine')"></span>
                                                </div>
                                            </template>
                                            <template x-if="!hasVaccine">
                                                <div class="text-xs font-semibold text-amber-700 dark:text-amber-300">
                                                    No vaccination recorded yet
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- Quick-Log Vaccine Option if not on file --}}
                                <template x-if="!hasVaccine">
                                    <div class="mt-3 pt-3 border-t border-amber-200/60 dark:border-amber-900/40">
                                        <label class="flex items-center gap-2 cursor-pointer select-none">
                                            <input type="checkbox" name="log_vaccine" value="1" x-model="logVaccine"
                                                   class="rounded text-[#199CA4] focus:ring-[#199CA4] h-4 w-4 border-slate-300">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                                Administer & log core vaccine now
                                            </span>
                                        </label>

                                        {{-- Expanded inputs when checked --}}
                                        <div x-show="logVaccine" class="mt-2.5 space-y-2" x-transition>
                                            <div>
                                                <label class="text-[10px] font-bold text-slate-500 uppercase">Vaccine Name</label>
                                                <input type="text" name="vaccine_name" x-model="vaccineNameInput" 
                                                       class="w-full mt-0.5 px-2.5 py-1.5 text-xs bg-white dark:bg-[#171923] border border-slate-200 dark:border-white/[0.08] rounded-xl font-medium focus:ring-1 focus:ring-[#199CA4]">
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase">Administered</label>
                                                    <input type="date" name="vaccine_date" x-model="vaccineDateInput"
                                                           class="w-full mt-0.5 px-2.5 py-1.5 text-xs bg-white dark:bg-[#171923] border border-slate-200 dark:border-white/[0.08] rounded-xl font-medium focus:ring-1 focus:ring-[#199CA4]">
                                                </div>
                                                <div>
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase">Next Booster</label>
                                                    <input type="date" name="vaccine_next_due" x-model="vaccineNextDueInput"
                                                           class="w-full mt-0.5 px-2.5 py-1.5 text-xs bg-white dark:bg-[#171923] border border-slate-200 dark:border-white/[0.08] rounded-xl font-medium focus:ring-1 focus:ring-[#199CA4]">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Deworming Card --}}
                            <div class="p-4 rounded-2xl border transition-all"
                                 :class="hasDeworming ? 'bg-purple-50/50 dark:bg-purple-950/20 border-purple-200/80 dark:border-purple-800/40' : (logDeworming ? 'bg-sky-50/50 dark:bg-sky-950/20 border-sky-300 dark:border-sky-800' : 'bg-amber-50/40 dark:bg-amber-950/20 border-amber-200/80 dark:border-amber-900/40')">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <span class="text-[10px] uppercase font-extrabold tracking-wider text-slate-400 block">Deworming</span>
                                        <div class="mt-1 flex items-center gap-1.5">
                                            <template x-if="hasDeworming">
                                                <div class="flex items-center gap-1.5 text-xs font-bold text-purple-700 dark:text-purple-300">
                                                    <svg class="w-4 h-4 text-purple-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                    <span x-text="'On file: ' + (dewormingName || 'Deworming')"></span>
                                                </div>
                                            </template>
                                            <template x-if="!hasDeworming">
                                                <div class="text-xs font-semibold text-amber-700 dark:text-amber-300">
                                                    No deworming recorded yet
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- Quick-Log Deworming Option if not on file --}}
                                <template x-if="!hasDeworming">
                                    <div class="mt-3 pt-3 border-t border-amber-200/60 dark:border-amber-900/40">
                                        <label class="flex items-center gap-2 cursor-pointer select-none">
                                            <input type="checkbox" name="log_deworming" value="1" x-model="logDeworming"
                                                   class="rounded text-[#199CA4] focus:ring-[#199CA4] h-4 w-4 border-slate-300">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                                Administer & log deworming now
                                            </span>
                                        </label>

                                        {{-- Expanded inputs when checked --}}
                                        <div x-show="logDeworming" class="mt-2.5 space-y-2" x-transition>
                                            <div>
                                                <label class="text-[10px] font-bold text-slate-500 uppercase">Dewormer Product</label>
                                                <input type="text" name="dewormer_name" x-model="dewormerNameInput" 
                                                       class="w-full mt-0.5 px-2.5 py-1.5 text-xs bg-white dark:bg-[#171923] border border-slate-200 dark:border-white/[0.08] rounded-xl font-medium focus:ring-1 focus:ring-[#199CA4]">
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase">Administered</label>
                                                    <input type="date" name="dewormer_date" x-model="dewormerDateInput"
                                                           class="w-full mt-0.5 px-2.5 py-1.5 text-xs bg-white dark:bg-[#171923] border border-slate-200 dark:border-white/[0.08] rounded-xl font-medium focus:ring-1 focus:ring-[#199CA4]">
                                                </div>
                                                <div>
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase">Next Due</label>
                                                    <input type="date" name="dewormer_next_due" x-model="dewormerNextDueInput"
                                                           class="w-full mt-0.5 px-2.5 py-1.5 text-xs bg-white dark:bg-[#171923] border border-slate-200 dark:border-white/[0.08] rounded-xl font-medium focus:ring-1 focus:ring-[#199CA4]">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                        </div>
                    </div>

                    {{-- Section 2: Behavioral Assessment & Temperament Tags --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-white">
                                2. Behavioral Assessment (Petfinder Compatibility)
                            </h4>
                            <span class="text-[11px] font-semibold text-[#199CA4] dark:text-[#41C1CB]">
                                <span x-text="selectedTags.length"></span> tag(s) selected
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Select 2 or more behavior tags. Petfinder matches adopter compatibility quiz profiles directly against these traits.
                        </p>

                        {{-- Clickable Tag Chips --}}
                        <div class="flex flex-wrap gap-2 pt-1">
                            <template x-for="tag in allTags" :key="tag.id">
                                <button type="button" 
                                        @click="toggleTag(tag.id)"
                                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all border cursor-pointer flex items-center gap-1.5"
                                        :class="selectedTags.includes(tag.id)
                                            ? 'bg-[#199CA4] text-white border-[#199CA4] shadow-xs'
                                            : 'bg-white dark:bg-[#171923] text-slate-700 dark:text-slate-300 border-slate-200 dark:border-white/[0.08] hover:bg-slate-50 dark:hover:bg-white/[0.04]'">
                                    <template x-if="selectedTags.includes(tag.id)">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </template>
                                    <span x-text="tag.name"></span>
                                </button>
                            </template>
                        </div>

                        {{-- Custom Temperament Note --}}
                        <div class="pt-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Additional Behavior Notes</label>
                            <input type="text" 
                                   name="custom_temperament" 
                                   x-model="customTemperament"
                                   placeholder="e.g. Good with small children, Leash trained, Gentle" 
                                   class="w-full mt-1 px-3 py-2 text-xs bg-white dark:bg-[#171923] border border-slate-200 dark:border-white/[0.08] rounded-xl font-medium focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] text-slate-800 dark:text-slate-100 placeholder-slate-400">
                        </div>
                    </div>

                    {{-- Section 3: Public Bio / Description (Optional) --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-white block">
                            3. Public Adoption Profile Bio
                        </label>
                        <p class="text-[11px] text-slate-400">Brief summary displayed to prospective adopters browsing the mobile app.</p>
                        <textarea name="description" 
                                  x-model="description" 
                                  rows="2" 
                                  placeholder="Provide an overview of the pet's story, personality, and adoption highlights..."
                                  class="w-full px-3 py-2 text-xs bg-white dark:bg-[#171923] border border-slate-200 dark:border-white/[0.08] rounded-xl font-medium focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] text-slate-800 dark:text-slate-100 placeholder-slate-400"></textarea>
                    </div>

                </div>

                {{-- Modal Footer Actions --}}
                <div class="p-3.5 sm:p-4 border-t border-slate-100 dark:border-white/[0.06] bg-slate-50/50 dark:bg-white/[0.02] flex items-center justify-end gap-2">
                    <button type="button" 
                            @click="closeModal()" 
                            class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#1D1F2C] text-xs font-bold transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" 
                            :disabled="isSubmitting"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-extrabold shadow-sm transition-all cursor-pointer">
                        <template x-if="isSubmitting">
                            <span class="inline-block animate-spin mr-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                            </span>
                        </template>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span x-text="isSubmitting ? 'Promoting...' : 'Confirm & Launch to Adoption Directory'"></span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    function readinessModalHandler() {
        return {
            isOpen: false,
            isSubmitting: false,
            petId: null,
            petName: '',
            petType: 'dog',
            actionUrl: '',
            hasVaccine: false,
            vaccineName: '',
            hasDeworming: false,
            dewormingName: '',
            logVaccine: false,
            logDeworming: false,
            vaccineNameInput: '5-in-1 (DHPP)',
            vaccineDateInput: new Date().toISOString().split('T')[0],
            vaccineNextDueInput: (() => {
                const d = new Date();
                d.setMonth(d.getMonth() + 6);
                return d.toISOString().split('T')[0];
            })(),
            dewormerNameInput: 'Anthelmintic Dewormer',
            dewormerDateInput: new Date().toISOString().split('T')[0],
            dewormerNextDueInput: (() => {
                const d = new Date();
                d.setMonth(d.getMonth() + 3);
                return d.toISOString().split('T')[0];
            })(),
            selectedTags: [],
            customTemperament: '',
            description: '',
            allTags: @json($modalTemperamentTags),

            openModal(detail) {
                this.petId = detail.id;
                this.petName = detail.name || ('Pet no. ' + detail.id);
                this.petType = detail.type || 'dog';
                this.actionUrl = detail.action;
                this.hasVaccine = !!detail.hasVaccine;
                this.vaccineName = detail.vaccineName || '';
                this.hasDeworming = !!detail.hasDeworming;
                this.dewormingName = detail.dewormingName || '';
                this.logVaccine = false;
                this.logDeworming = false;
                this.vaccineNameInput = (this.petType === 'cat') ? '4-in-1 (FVRCP)' : '5-in-1 (DHPP)';
                this.dewormerNameInput = 'Anthelmintic Dewormer';

                const allowedTagIds = this.allTags.map(t => Number(t.id));
                this.selectedTags = Array.isArray(detail.selectedTags)
                    ? detail.selectedTags.map(Number).filter(id => allowedTagIds.includes(id))
                    : [];

                this.customTemperament = '';
                this.description = detail.description || '';
                this.isSubmitting = false;
                this.isOpen = true;
                document.body.classList.add('overflow-hidden');
            },

            closeModal() {
                this.isOpen = false;
                document.body.classList.remove('overflow-hidden');
            },

            toggleTag(tagId) {
                const idx = this.selectedTags.indexOf(tagId);
                if (idx > -1) {
                    this.selectedTags.splice(idx, 1);
                } else {
                    this.selectedTags.push(tagId);
                }
            }
        };
    }
</script>

