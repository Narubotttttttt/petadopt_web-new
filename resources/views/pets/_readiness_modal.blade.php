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
            <form :action="actionUrl" method="POST" @submit.prevent="if (isFormValid && !isSubmitting) { isSubmitting = true; $el.submit(); }">
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
                            <span class="text-[11px] text-slate-400 dark:text-slate-500">Core vaccinations & deworming (Optional)</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            
                            {{-- Core Vaccination Card --}}
                            <div class="p-4 rounded-2xl border transition-all"
                                 :class="hasVaccine ? 'bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-200/80 dark:border-emerald-800/40' : (logVaccine ? 'bg-sky-50/50 dark:bg-sky-950/20 border-sky-300 dark:border-sky-800' : 'bg-slate-50/60 dark:bg-white/[0.02] border-slate-200 dark:border-white/[0.08]')">
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
                                                <div>
                                                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                                        No vaccination recorded yet
                                                    </div>
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
                                                {{-- Feline Vaccines Dropdown --}}
                                                <div x-show="petType === 'cat'">
                                                    <select x-model="vaccinePreset"
                                                            class="w-full mt-0.5 px-2.5 py-1.5 text-xs bg-white dark:bg-[#171923] border border-slate-200 dark:border-white/[0.08] rounded-xl font-medium focus:ring-1 focus:ring-[#199CA4]">
                                                        <option value="4-in-1 (FVRCP)">4-in-1 (FVRCP) - Core Feline</option>
                                                        <option value="3-in-1 (FVRCP)">3-in-1 (FVRCP)</option>
                                                        <option value="Anti-Rabies">Anti-Rabies</option>
                                                        <option value="FeLV (Feline Leukemia)">FeLV (Feline Leukemia)</option>
                                                        <option value="Other">Other (Specify Custom Vaccine)</option>
                                                    </select>
                                                </div>
                                                {{-- Canine Vaccines Dropdown --}}
                                                <div x-show="petType !== 'cat'">
                                                    <select x-model="vaccinePreset"
                                                            class="w-full mt-0.5 px-2.5 py-1.5 text-xs bg-white dark:bg-[#171923] border border-slate-200 dark:border-white/[0.08] rounded-xl font-medium focus:ring-1 focus:ring-[#199CA4]">
                                                        <option value="5-in-1 (DHPP)">5-in-1 (DHPP) - Core Canine</option>
                                                        <option value="6-in-1 (DHLPP)">6-in-1 (DHLPP)</option>
                                                        <option value="8-in-1 (DHLPPC)">8-in-1 (DHLPPC)</option>
                                                        <option value="Anti-Rabies">Anti-Rabies</option>
                                                        <option value="Bordetella (Kennel Cough)">Bordetella (Kennel Cough)</option>
                                                        <option value="Canine Parvovirus (CPV)">Canine Parvovirus (CPV)</option>
                                                        <option value="Other">Other (Specify Custom Vaccine)</option>
                                                    </select>
                                                </div>
                                                <input type="hidden" name="vaccine_name" :value="vaccinePreset === 'Other' ? (vaccineCustomInput.trim() || (petType === 'cat' ? '4-in-1 (FVRCP)' : '5-in-1 (DHPP)')) : vaccinePreset">
                                                <div x-show="vaccinePreset === 'Other'" class="mt-1.5" x-transition>
                                                    <input type="text" x-model="vaccineCustomInput" placeholder="Specify custom vaccine name..."
                                                           class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] rounded-xl font-medium focus:ring-1 focus:ring-[#199CA4]">
                                                </div>
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
                                 :class="hasDeworming ? 'bg-purple-50/50 dark:bg-purple-950/20 border-purple-200/80 dark:border-purple-800/40' : (logDeworming ? 'bg-sky-50/50 dark:bg-sky-950/20 border-sky-300 dark:border-sky-800' : 'bg-slate-50/60 dark:bg-white/[0.02] border-slate-200 dark:border-white/[0.08]')">
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
                                                <div>
                                                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                                        No deworming recorded yet
                                                    </div>
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
                                                <select x-model="dewormerPreset"
                                                        class="w-full mt-0.5 px-2.5 py-1.5 text-xs bg-white dark:bg-[#171923] border border-slate-200 dark:border-white/[0.08] rounded-xl font-medium focus:ring-1 focus:ring-[#199CA4]">
                                                    <option value="Pyrantel Embonate">Pyrantel Embonate</option>
                                                    <option value="Canex Puppy/Dog">Canex Puppy/Dog</option>
                                                    <option value="Drontal Plus">Drontal Plus</option>
                                                    <option value="Heartgard Plus">Heartgard Plus</option>
                                                    <option value="Broadline (Feline Spot-on)">Broadline (Feline Spot-on)</option>
                                                    <option value="Anthelmintic Dewormer">Anthelmintic Dewormer (Standard)</option>
                                                    <option value="Other">Other (Specify Custom Brand)</option>
                                                </select>
                                                <input type="hidden" name="dewormer_name" :value="dewormerPreset === 'Other' ? (dewormerCustomInput.trim() || 'Anthelmintic Dewormer') : dewormerPreset">
                                                <div x-show="dewormerPreset === 'Other'" class="mt-1.5" x-transition>
                                                    <input type="text" x-model="dewormerCustomInput" placeholder="Specify custom dewormer brand..."
                                                           class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] rounded-xl font-medium focus:ring-1 focus:ring-[#199CA4]">
                                                </div>
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
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-white flex items-center gap-1.5">
                                <span>2. Behavioral Assessment (Petfinder Compatibility)</span>
                                <span class="text-rose-500 font-bold">*</span>
                            </h4>
                            <span class="text-[11px] font-bold"
                                  :class="isTemperamentClear ? 'text-[#199CA4] dark:text-[#41C1CB]' : 'text-rose-500 dark:text-rose-400'">
                                <span x-show="!isTemperamentClear">Required (Select at least 1)</span>
                                <span x-show="isTemperamentClear"><span x-text="selectedTags.length"></span> tag(s) selected</span>
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Select at least 1 behavior tag. Petfinder matches adopter compatibility quiz profiles directly against these traits.
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

                {{-- Live Validation Checklist Banner --}}
                <div x-show="!isFormValid" class="px-4 py-3 bg-amber-50 dark:bg-amber-950/40 border-t border-amber-200/80 dark:border-amber-800/40 flex items-start gap-2.5 text-xs text-amber-900 dark:text-amber-200">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="flex-1">
                        <span class="font-bold">Cannot publish yet — please complete the required readiness criteria:</span>
                        <ul class="list-disc list-inside mt-1 space-y-0.5 text-[11px] text-amber-700 dark:text-amber-300 font-medium">
                            <template x-for="req in missingRequirements" :key="req">
                                <li x-text="req"></li>
                            </template>
                        </ul>
                    </div>
                </div>

                {{-- Modal Footer Actions --}}
                <div class="p-3.5 sm:p-4 border-t border-slate-100 dark:border-white/[0.06] bg-slate-50/50 dark:bg-white/[0.02] flex items-center justify-between gap-2">
                    <div>
                        <template x-if="isFormValid">
                            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                All readiness requirements satisfied
                            </span>
                        </template>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="closeModal()" 
                                class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#1D1F2C] text-xs font-bold transition cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" 
                                :disabled="isSubmitting || !isFormValid"
                                :class="(!isFormValid || isSubmitting) ? 'opacity-50 cursor-not-allowed bg-slate-400 dark:bg-slate-600 text-slate-200 shadow-none' : 'bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer shadow-sm'"
                                :title="!isFormValid ? 'Please complete all requirements above' : 'Ready to launch'"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-extrabold transition-all">
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
            vaccinePreset: '5-in-1 (DHPP)',
            vaccineCustomInput: '',
            vaccineDateInput: new Date().toISOString().split('T')[0],
            vaccineNextDueInput: (() => {
                const d = new Date();
                d.setMonth(d.getMonth() + 6);
                return d.toISOString().split('T')[0];
            })(),
            dewormerPreset: 'Pyrantel Embonate',
            dewormerCustomInput: '',
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

            get isVaccineValid() {
                if (!this.logVaccine) return true;
                if (this.vaccinePreset === 'Other' && !this.vaccineCustomInput.trim()) return false;
                if (!this.vaccineDateInput) return false;
                return true;
            },

            get isDewormingValid() {
                if (!this.logDeworming) return true;
                if (this.dewormerPreset === 'Other' && !this.dewormerCustomInput.trim()) return false;
                if (!this.dewormerDateInput) return false;
                return true;
            },

            get isTemperamentClear() {
                return (this.selectedTags && this.selectedTags.length >= 1) || (this.customTemperament && this.customTemperament.trim().length > 0);
            },

            get isFormValid() {
                return this.isTemperamentClear && this.isVaccineValid && this.isDewormingValid;
            },

            get missingRequirements() {
                const missing = [];
                if (!this.isTemperamentClear) {
                    missing.push('Select at least 1 verified temperament tag (Basis of ML recommendations)');
                }
                if (this.logVaccine) {
                    if (this.vaccinePreset === 'Other' && !this.vaccineCustomInput.trim()) {
                        missing.push('Specify custom vaccine name');
                    }
                    if (!this.vaccineDateInput) {
                        missing.push('Provide vaccine administration date');
                    }
                }
                if (this.logDeworming) {
                    if (this.dewormerPreset === 'Other' && !this.dewormerCustomInput.trim()) {
                        missing.push('Specify custom dewormer brand');
                    }
                    if (!this.dewormerDateInput) {
                        missing.push('Provide deworming administration date');
                    }
                }
                return missing;
            },

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
                this.vaccinePreset = (this.petType === 'cat') ? '4-in-1 (FVRCP)' : '5-in-1 (DHPP)';
                this.vaccineCustomInput = '';
                this.dewormerPreset = (this.petType === 'cat') ? 'Broadline (Feline Spot-on)' : 'Pyrantel Embonate';
                this.dewormerCustomInput = '';

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

