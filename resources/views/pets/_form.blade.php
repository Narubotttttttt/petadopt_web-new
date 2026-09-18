@php
    $pet = $pet ?? null;
    $isWizard = $isWizard ?? (!isset($pet));
    $dogBreeds = ['Aspin (Mixed Breed)', 'Shih Tzu / Aspin Mix', 'Labrador/ Mix', 'Other'];
    $catBreeds = ['Puspin (Mixed Breed)', 'Other'];
    $currentType = old('type', optional($pet)->type ?? '');
    $currentBreed = old('breed', optional($pet)->breed ?? '');
    $currentColor = old('color', optional($pet)->color ?? '');
    $currentGender = old('gender', optional($pet)->gender ?? '');
    $currentAge = old('age', optional($pet)->age ?? '');
    $currentDescription = old('description', optional($pet)->description ?? '');

    $selectedMedicalHistory = old('medical_history', optional($pet)->medical_history);
    if (is_string($selectedMedicalHistory)) {
        $selectedMedicalHistory = array_values(array_filter(array_map('trim', explode(',', $selectedMedicalHistory))));
    } elseif (!is_array($selectedMedicalHistory)) {
        $selectedMedicalHistory = [];
    }

    $standardDisabilityOptions = [
        'Blind / Visually Impaired',
        'Deaf / Hearing Impaired',
        'Amputee / Missing Limb (Tripod)',
        'Mobility Impaired / Paralyzed',
        'Neurological / Special Needs',
    ];

    $hasDisabilityInitial = false;
    $initialDisabilityType = '';
    $initialDisabilityOther = '';

    foreach ($selectedMedicalHistory as $item) {
        if (stripos($item, 'disability') !== false || in_array($item, $standardDisabilityOptions, true)) {
            $hasDisabilityInitial = true;
            if (preg_match('/(?:With Disability\s*[\(:—\-]\s*|\(\s*)([^\)]+)\)?/i', $item, $matches)) {
                $detail = trim($matches[1]);
                if (in_array($detail, $standardDisabilityOptions, true)) {
                    $initialDisabilityType = $detail;
                } else {
                    $initialDisabilityType = 'Other';
                    $initialDisabilityOther = $detail;
                }
            } elseif (in_array($item, $standardDisabilityOptions, true)) {
                $initialDisabilityType = $item;
            }
            break;
        }
    }

    $selectedTagIds = old('temperament_tags', $selectedTagIds ?? []);

    $initialStep = 1;
    if ($errors->has('photo') || $errors->has('description')) {
        $initialStep = 3;
    } elseif ($errors->has('medical_history') || $errors->has('temperament_tags')) {
        $initialStep = 2;
    }
@endphp

<div x-data="{
    isWizard: {{ $isWizard ? 'true' : 'false' }},
    step: {{ $initialStep }},
    maxStep: 3,
    submitting: false,
    errorMessage: '',

    // Step 1 fields
    type: '{{ addslashes($currentType) }}',
    breed: '{{ addslashes($currentBreed) }}',
    color: '{{ addslashes($currentColor) }}',
    gender: '{{ addslashes($currentGender) }}',
    age: '{{ addslashes($currentAge) }}',
    breedMode: 'select',
    dogBreeds: {{ json_encode($dogBreeds) }},
    catBreeds: {{ json_encode($catBreeds) }},
    dogAges: ['Puppy', 'Adult'],
    catAges: ['Kitten', 'Adult'],

    // Step 2 fields
    hasDisability: {{ $hasDisabilityInitial ? 'true' : 'false' }},
    disabilityType: '{{ addslashes($initialDisabilityType) }}',
    disabilityOther: '{{ addslashes($initialDisabilityOther) }}',
    selectedMedical: {{ json_encode(array_values(array_intersect($selectedMedicalHistory, ['Vaccinated', 'Spayed/Neutered', 'Dewormed']))) }},
    selectedTags: {{ json_encode(array_map('strval', $selectedTagIds)) }},

    // Step 3 fields
    description: `{{ addslashes($currentDescription) }}`,
    previewUrl: '{{ optional($pet)->photo_path ? asset('storage/'.optional($pet)->photo_path) : '' }}',
    fileName: '',
    fileSize: '',
    isNew: false,

    get breedOptions() {
        return this.type === 'dog' ? this.dogBreeds : (this.type === 'cat' ? this.catBreeds : []);
    },
    get ageOptions() {
        return this.type === 'dog' ? this.dogAges : (this.type === 'cat' ? this.catAges : []);
    },
    get disabilityValue() {
        if (!this.hasDisability) return '';
        if (this.disabilityType === 'Other') {
            return this.disabilityOther.trim() ? `With Disability (${this.disabilityOther.trim()})` : 'With Disability';
        }
        if (this.disabilityType) {
            return `With Disability (${this.disabilityType})`;
        }
        return 'With Disability';
    },

    init() {
        if (this.breed && !this.breedOptions.includes(this.breed)) {
            this.breedMode = 'other';
        }
    },
    selectBreed(value) {
        if (value === 'Other') {
            this.breed = '';
            this.breedMode = 'other';
        } else {
            this.breed = value;
        }
    },
    backToList() {
        this.breed = '';
        this.breedMode = 'select';
    },
    handleFileChange(e) {
        const file = e.target.files[0];
        if (!file) return;

        if (this.previewUrl && this.previewUrl.startsWith('blob:')) {
            URL.revokeObjectURL(this.previewUrl);
        }

        this.fileName = file.name;
        this.fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        this.isNew = true;
        this.errorMessage = '';
        this.previewUrl = URL.createObjectURL(file);

        // Auto-optimize large images on client side (e.g. mobile photos)
        if (file.type.startsWith('image/') && file.size > 1.2 * 1024 * 1024) {
            const input = e.target;
            const img = new Image();
            const tempUrl = URL.createObjectURL(file);
            img.onload = () => {
                URL.revokeObjectURL(tempUrl);
                const maxDim = 1280;
                let w = img.width;
                let h = img.height;
                if (w > maxDim || h > maxDim) {
                    if (w > h) {
                        h = Math.round((h * maxDim) / w);
                        w = maxDim;
                    } else {
                        w = Math.round((w * maxDim) / h);
                        h = maxDim;
                    }
                }
                const canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, w, h);
                canvas.toBlob((blob) => {
                    if (blob && blob.size < file.size) {
                        try {
                            const newFile = new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), {
                                type: 'image/jpeg',
                                lastModified: Date.now()
                            });
                            const dt = new DataTransfer();
                            dt.items.add(newFile);
                            input.files = dt.files;
                            this.fileName = newFile.name;
                            this.fileSize = (newFile.size / 1024).toFixed(0) + ' KB (Optimized)';
                            if (this.previewUrl && this.previewUrl.startsWith('blob:')) {
                                URL.revokeObjectURL(this.previewUrl);
                            }
                            this.previewUrl = URL.createObjectURL(newFile);
                        } catch(err) {
                            // DataTransfer fallback
                        }
                    }
                }, 'image/jpeg', 0.82);
            };
            img.src = tempUrl;
        }
    },
    clearSelection() {
        if (this.previewUrl && this.previewUrl.startsWith('blob:')) {
            URL.revokeObjectURL(this.previewUrl);
        }
        this.previewUrl = '{{ optional($pet)->photo_path ? asset('storage/'.optional($pet)->photo_path) : '' }}';
        this.fileName = '';
        this.fileSize = '';
        this.isNew = false;
        const input = document.getElementById('pet_photo_input');
        if (input) input.value = '';
    },

    // Step Validations
    validateStep1() {
        this.errorMessage = '';
        if (!this.type) {
            this.errorMessage = 'Please select a pet type (Dog or Cat).';
            return false;
        }
        if (!this.breed || !this.breed.trim()) {
            this.errorMessage = 'Please select or enter the pet breed.';
            return false;
        }
        if (!this.color || !this.color.trim()) {
            this.errorMessage = 'Please enter the pet color.';
            return false;
        }
        if (!this.gender) {
            this.errorMessage = 'Please select a gender (Male or Female).';
            return false;
        }
        if (!this.age) {
            this.errorMessage = 'Please select the pet age classification.';
            return false;
        }
        return true;
    },
    validateStep2() {
        this.errorMessage = '';
        if (this.hasDisability && this.disabilityType === 'Other' && (!this.disabilityOther || !this.disabilityOther.trim())) {
            this.errorMessage = 'Please specify the disability details.';
            return false;
        }
        return true;
    },
    validateStep3() {
        this.errorMessage = '';
        const input = document.getElementById('pet_photo_input');
        @if(!$pet)
            if (!this.previewUrl && (!input || !input.files || !input.files.length)) {
                this.errorMessage = 'Please upload a photo for the pet.';
                return false;
            }
        @endif
        if (input && input.files && input.files[0]) {
            if (input.files[0].size > 2048 * 1024) {
                this.errorMessage = 'Photo must be 2MB or smaller. Selected file is ' + (input.files[0].size / 1024 / 1024).toFixed(2) + ' MB.';
                return false;
            }
        }
        return true;
    },
    nextStep(current) {
        if (current === 1) {
            if (this.validateStep1()) {
                this.step = 2;
                this.errorMessage = '';
                this.scrollToTop();
            }
        } else if (current === 2) {
            if (this.validateStep2()) {
                this.step = 3;
                this.errorMessage = '';
                this.scrollToTop();
            }
        }
    },
    prevStep(current) {
        if (current > 1) {
            this.step = current - 1;
            this.errorMessage = '';
            this.scrollToTop();
        }
    },
    goToStep(target) {
        if (!this.isWizard) return;
        if (target === 1) {
            this.step = 1;
            this.errorMessage = '';
            this.scrollToTop();
        } else if (target === 2) {
            if (this.validateStep1()) {
                this.step = 2;
                this.errorMessage = '';
                this.scrollToTop();
            }
        } else if (target === 3) {
            if (this.validateStep1() && this.validateStep2()) {
                this.step = 3;
                this.errorMessage = '';
                this.scrollToTop();
            }
        }
    },
    scrollToTop() {
        const topEl = this.$el.closest('.max-w-4xl') || this.$el;
        topEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },
    submitWizard(e) {
        if (e) e.preventDefault();
        if (this.submitting) return false;

        if (!this.validateStep1()) {
            this.step = 1;
            this.scrollToTop();
            return false;
        }
        if (!this.validateStep2()) {
            this.step = 2;
            this.scrollToTop();
            return false;
        }
        if (!this.validateStep3()) {
            this.step = 3;
            this.scrollToTop();
            return false;
        }

        this.submitting = true;
        const form = this.$el.closest('form');
        if (form) {
            form.submit();
        }
        return true;
    },
    submitFromKey() {
        this.submitWizard();
    }
}"
@keydown.enter="if (isWizard && step < 3 && $event.target.tagName !== 'TEXTAREA') { $event.preventDefault(); nextStep(step); } else if (isWizard && step === 3 && $event.target.tagName !== 'TEXTAREA') { $event.preventDefault(); submitFromKey(); }">

    {{-- Stepper Progress Header (Only visible in Wizard Mode) --}}
    <div class="mb-8" x-show="isWizard" x-cloak>
        {{-- Desktop Stepper --}}
        <div class="hidden sm:flex items-center justify-between relative px-2">
            <div class="absolute left-10 right-10 top-5 h-1 bg-gray-200 dark:bg-white/[0.08] z-0"></div>
            <div class="absolute left-10 top-5 h-1 bg-[#199CA4] transition-all duration-300 z-0"
                 :style="'width: ' + (step === 1 ? '0%' : (step === 2 ? '50%' : 'calc(100% - 5rem)'))"></div>

            {{-- Step 1 Button --}}
            <button type="button" @click="goToStep(1)" class="relative z-10 flex flex-col items-center group cursor-pointer">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-200"
                     :class="step === 1 
                        ? 'bg-[#199CA4] text-white ring-4 ring-[#199CA4]/20 shadow-md' 
                        : (step > 1 
                            ? 'bg-[#199CA4] text-white hover:bg-[#13787F]' 
                            : 'bg-white dark:bg-[#12141C] border-2 border-gray-300 dark:border-white/[0.15] text-gray-400 dark:text-slate-500')">
                    <template x-if="step > 1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </template>
                    <template x-if="step <= 1">
                        <span>1</span>
                    </template>
                </div>
                <div class="mt-2 text-center">
                    <span class="block text-xs font-bold transition"
                          :class="step === 1 ? 'text-[#199CA4]' : (step > 1 ? 'text-gray-700 dark:text-slate-300' : 'text-gray-400 dark:text-slate-500')">
                        General Details
                    </span>
                    <span class="block text-[11px] text-gray-400 dark:text-slate-500">Identity & Breed</span>
                </div>
            </button>

            {{-- Step 2 Button --}}
            <button type="button" @click="goToStep(2)" class="relative z-10 flex flex-col items-center group cursor-pointer">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-200"
                     :class="step === 2 
                        ? 'bg-[#199CA4] text-white ring-4 ring-[#199CA4]/20 shadow-md' 
                        : (step > 2 
                            ? 'bg-[#199CA4] text-white hover:bg-[#13787F]' 
                            : 'bg-white dark:bg-[#12141C] border-2 border-gray-300 dark:border-white/[0.15] text-gray-400 dark:text-slate-500')">
                    <template x-if="step > 2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </template>
                    <template x-if="step <= 2">
                        <span>2</span>
                    </template>
                </div>
                <div class="mt-2 text-center">
                    <span class="block text-xs font-bold transition"
                          :class="step === 2 ? 'text-[#199CA4]' : (step > 2 ? 'text-gray-700 dark:text-slate-300' : 'text-gray-400 dark:text-slate-500')">
                        Health & Traits
                    </span>
                    <span class="block text-[11px] text-gray-400 dark:text-slate-500">Medical & Personality</span>
                </div>
            </button>

            {{-- Step 3 Button --}}
            <button type="button" @click="goToStep(3)" class="relative z-10 flex flex-col items-center group cursor-pointer">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-200"
                     :class="step === 3 
                        ? 'bg-[#199CA4] text-white ring-4 ring-[#199CA4]/20 shadow-md' 
                        : 'bg-white dark:bg-[#12141C] border-2 border-gray-300 dark:border-white/[0.15] text-gray-400 dark:text-slate-500'">
                    <span>3</span>
                </div>
                <div class="mt-2 text-center">
                    <span class="block text-xs font-bold transition"
                          :class="step === 3 ? 'text-[#199CA4]' : 'text-gray-400 dark:text-slate-500'">
                        Photo & Story
                    </span>
                    <span class="block text-[11px] text-gray-400 dark:text-slate-500">Upload & Bio</span>
                </div>
            </button>
        </div>

        {{-- Mobile Stepper Header --}}
        <div class="sm:hidden space-y-2.5">
            <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-[#199CA4] uppercase tracking-wider"
                      x-text="step === 1 ? 'Step 1 of 3: General Details' : (step === 2 ? 'Step 2 of 3: Health & Traits' : 'Step 3 of 3: Photo & Story')">
                </span>
                <span class="font-semibold text-gray-400 dark:text-slate-500"
                      x-text="step === 1 ? '33%' : (step === 2 ? '66%' : '100%')">
                </span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-white/[0.08] h-2 rounded-full overflow-hidden">
                <div class="h-full bg-[#199CA4] transition-all duration-300 rounded-full"
                     :style="'width: ' + (step === 1 ? '33.3%' : (step === 2 ? '66.6%' : '100%'))"></div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-slate-400 pt-0.5">
                <button type="button" @click="goToStep(1)" class="font-medium hover:text-[#199CA4]" :class="{ 'text-[#199CA4] font-bold': step === 1 }">1. General</button>
                <button type="button" @click="goToStep(2)" class="font-medium hover:text-[#199CA4]" :class="{ 'text-[#199CA4] font-bold': step === 2 }">2. Health</button>
                <button type="button" @click="goToStep(3)" class="font-medium hover:text-[#199CA4]" :class="{ 'text-[#199CA4] font-bold': step === 3 }">3. Photo</button>
            </div>
        </div>
    </div>

    {{-- Inline Step Validation Alert --}}
    <div x-show="errorMessage" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="mb-6 px-4 py-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-amber-800 dark:text-amber-300 rounded-xl flex items-center justify-between text-sm shadow-xs">
        <div class="flex items-center gap-2 font-medium">
            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" stroke-width="2"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/>
            </svg>
            <span x-text="errorMessage"></span>
        </div>
        <button type="button" @click="errorMessage = ''" class="text-amber-600 dark:text-amber-400 hover:opacity-75 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- ==================== STEP 1: GENERAL DETAILS ==================== --}}
    <div x-show="!isWizard || step === 1"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Pet Type</label>
                <select name="type" x-model="type" @change="breed = ''; age = ''; breedMode = 'select'; errorMessage = ''"
                    :required="!isWizard"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white">
                    <option value="">Select pet type</option>
                    <option value="dog">Dog</option>
                    <option value="cat">Cat</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Breed</label>
                <input type="hidden" name="breed" x-model="breed">

                <template x-if="!type">
                    <select disabled class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.06] bg-gray-50 dark:bg-white/[0.02] text-gray-400 dark:text-slate-500 shadow-sm">
                        <option>Select pet type first</option>
                    </select>
                </template>

                <template x-if="type && breedMode === 'select'">
                    <select @change="selectBreed($event.target.value); errorMessage = ''"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white">
                        <option value="">Select breed</option>
                        <template x-for="option in breedOptions" :key="option">
                            <option :value="option" :selected="option === breed" x-text="option"></option>
                        </template>
                    </select>
                </template>

                <template x-if="type && breedMode === 'other'">
                    <div>
                        <input type="text" x-model="breed" placeholder="Enter breed"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white">
                        <button type="button" @click="backToList()" class="mt-2 text-xs font-semibold text-[#199CA4] hover:text-[#13787F]">Choose from list instead</button>
                    </div>
                </template>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Color</label>
                <input type="text" name="color" x-model="color" value="{{ $currentColor }}"
                    :required="!isWizard"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white"
                    placeholder="e.g., Brown, White/Brown">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Gender</label>
                <select name="gender" x-model="gender"
                    :required="!isWizard"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white">
                    <option value="">Select gender</option>
                    <option value="male" {{ $currentGender == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ $currentGender == 'female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Age</label>
                <select name="age" x-model="age" x-show="type"
                    :required="!isWizard && type !== ''"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white">
                    <option value="">Select age</option>
                    <template x-for="option in ageOptions" :key="option">
                        <option :value="option" :selected="option === age" x-text="option"></option>
                    </template>
                </select>
                <select x-show="!type" disabled class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.06] bg-gray-50 dark:bg-white/[0.02] text-gray-400 dark:text-slate-500 shadow-sm">
                    <option>Select pet type first</option>
                </select>
            </div>
        </div>

        @if(optional($pet)->id)
            <div class="mb-8">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Status</label>
                <select name="status"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white" required>
                    <option value="available" {{ old('status', optional($pet)->status ?? 'available') == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="pending" {{ old('status', optional($pet)->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="adopted" {{ old('status', optional($pet)->status) == 'adopted' ? 'selected' : '' }}>Adopted</option>
                </select>
            </div>
        @else
            <input type="hidden" name="status" value="available">
        @endif

        {{-- Step 1 Navigation Buttons (Wizard Mode Only) --}}
        <div x-show="isWizard" class="flex items-center justify-between border-t border-gray-100 dark:border-white/[0.06] pt-6 mt-8">
            <a href="{{ route('pets.index') }}"
                class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-600 dark:text-slate-300 border border-gray-200 dark:border-white/[0.08] hover:bg-gray-50 dark:hover:bg-white/[0.06] transition">
                Cancel
            </a>
            <button type="button" @click="nextStep(1)"
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#199CA4] text-white text-sm font-semibold rounded-xl hover:bg-[#13787F] shadow-md shadow-[#199CA4]/10 transition cursor-pointer">
                <span>Next: Health & Traits</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>
    </div>

    {{-- ==================== STEP 2: HEALTH & TRAITS ==================== --}}
    <div x-show="!isWizard || step === 2"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-cloak>
        
        <div class="mb-8">
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Medical Background</label>
            <p class="text-sm text-gray-500 dark:text-slate-400 mb-3">Choose the options that best describe the pet's health history.</p>
            <div class="rounded-2xl border border-gray-200 dark:border-white/[0.08] p-4 bg-gray-50/50 dark:bg-white/[0.02] space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @php $medicalStandardOptions = ['Vaccinated', 'Spayed/Neutered', 'Dewormed']; @endphp
                    @foreach($medicalStandardOptions as $option)
                        <label class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] px-3 py-2 text-sm text-gray-700 dark:text-slate-200 cursor-pointer hover:border-[#199CA4]/40 transition">
                            <input type="checkbox" name="medical_history[]" value="{{ $option }}"
                                   x-model="selectedMedical"
                                   class="rounded border-gray-300 text-[#199CA4] focus:ring-[#199CA4]"
                                   {{ in_array($option, $selectedMedicalHistory, true) ? 'checked' : '' }}>
                            <span>{{ $option }}</span>
                        </label>
                    @endforeach

                    {{-- With Disability Checkbox --}}
                    <label class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] px-3 py-2 text-sm text-gray-700 dark:text-slate-200 cursor-pointer hover:border-[#199CA4]/40 transition"
                        :class="{ 'border-[#199CA4] ring-1 ring-[#199CA4]/30': hasDisability }">
                        <input type="checkbox" x-model="hasDisability" class="rounded border-gray-300 text-[#199CA4] focus:ring-[#199CA4]">
                        <span>With Disability</span>
                    </label>
                    <input type="hidden" name="medical_history[]" :value="disabilityValue" :disabled="!hasDisability">
                </div>

                {{-- Disability Dropdown Panel (Shown when With Disability is checked) --}}
                <div x-show="hasDisability" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     class="pt-3 border-t border-gray-200 dark:border-white/[0.06] space-y-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#199CA4] dark:text-teal-400 mb-1.5">
                            Disability Type
                        </label>
                        <select x-model="disabilityType" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-xs text-gray-800 dark:text-white text-sm">
                            <option value="">Select disability type (optional)</option>
                            <option value="Blind / Visually Impaired">Blind / Visually Impaired</option>
                            <option value="Deaf / Hearing Impaired">Deaf / Hearing Impaired</option>
                            <option value="Amputee / Missing Limb (Tripod)">Amputee / Missing Limb (Tripod)</option>
                            <option value="Mobility Impaired / Paralyzed">Mobility Impaired / Paralyzed</option>
                            <option value="Neurological / Special Needs">Neurological / Special Needs</option>
                            <option value="Other">Other Disability (Specify)</option>
                        </select>
                    </div>

                    <div x-show="disabilityType === 'Other'" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-slate-400 mb-1.5">Please specify the disability</label>
                        <input type="text" x-model="disabilityOther" placeholder="e.g., Partial vision loss, limb deformity..."
                            class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-xs text-gray-800 dark:text-white text-sm">
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Temperament</label>
            <p class="text-sm text-gray-500 dark:text-slate-400 mb-3">Choose all traits that describe this pet.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 rounded-2xl border border-gray-200 dark:border-white/[0.08] p-4 bg-gray-50/50 dark:bg-white/[0.02]">
                @foreach($temperamentTags as $tag)
                    <label class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] px-3 py-2 text-sm text-gray-700 dark:text-slate-200 cursor-pointer hover:border-[#199CA4]/40 transition">
                        <input type="checkbox" name="temperament_tags[]" value="{{ $tag->id }}"
                               x-model="selectedTags"
                               class="rounded border-gray-300 text-[#199CA4] focus:ring-[#199CA4]"
                               {{ in_array($tag->id, $selectedTagIds) ? 'checked' : '' }}>
                        <span>{{ $tag->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Step 2 Navigation Buttons (Wizard Mode Only) --}}
        <div x-show="isWizard" class="flex items-center justify-between border-t border-gray-100 dark:border-white/[0.06] pt-6 mt-8">
            <button type="button" @click="prevStep(2)"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-600 dark:text-slate-300 border border-gray-200 dark:border-white/[0.08] hover:bg-gray-50 dark:hover:bg-white/[0.06] transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to General Details</span>
            </button>
            <button type="button" @click="nextStep(2)"
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#199CA4] text-white text-sm font-semibold rounded-xl hover:bg-[#13787F] shadow-md shadow-[#199CA4]/10 transition cursor-pointer">
                <span>Next: Photo & Bio</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>
    </div>

    {{-- ==================== STEP 3: PHOTO & STORY ==================== --}}
    <div x-show="!isWizard || step === 3"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-cloak>
        
        {{-- Pet Photo Upload --}}
        <div class="mb-8">
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Pet Photo</label>
            
            <div class="border-2 border-dashed border-gray-200 dark:border-white/[0.1] hover:border-[#199CA4] rounded-2xl p-5 transition bg-gray-50/50 dark:bg-white/[0.02]">
                
                {{-- When an image is present (preview or current) --}}
                <div x-show="previewUrl" class="flex flex-col sm:flex-row items-center gap-5">
                    <div class="relative w-36 h-36 sm:w-44 sm:h-44 rounded-2xl overflow-hidden border-2 border-slate-200 dark:border-white/[0.12] shadow-md shrink-0 bg-slate-100 dark:bg-slate-800">
                        <img :src="previewUrl" alt="Pet preview" class="w-full h-full object-cover">
                        <span x-show="isNew" class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-[#199CA4] text-white text-[10px] font-extrabold shadow-sm">
                            New Photo
                        </span>
                        <span x-show="!isNew" class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-slate-800/80 text-white text-[10px] font-bold shadow-sm backdrop-blur-xs">
                            Current Photo
                        </span>
                    </div>

                    <div class="flex-1 text-center sm:text-left space-y-2 w-full">
                        <div>
                            <p class="text-sm font-bold text-gray-800 dark:text-white truncate max-w-md" x-text="isNew ? (fileName || 'New photo chosen') : 'Current Pet Photo'"></p>
                            <p class="text-xs text-gray-500 dark:text-slate-400" x-text="isNew ? (fileSize ? 'File size: ' + fileSize : 'Ready to upload') : 'Stored in database'"></p>
                        </div>

                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-1">
                            <label for="pet_photo_input" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-[#171923] border border-gray-200 dark:border-white/[0.12] text-xs font-bold text-gray-700 dark:text-slate-200 hover:border-[#199CA4] hover:text-[#199CA4] shadow-2xs transition cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Choose Different Photo
                            </label>
                            <button type="button" x-show="isNew" @click="clearSelection()" class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 text-xs font-bold hover:bg-rose-100 dark:hover:bg-rose-500/20 transition cursor-pointer">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Empty State (No photo chosen and no current photo) --}}
                <div x-show="!previewUrl" class="text-center py-4 space-y-2">
                    <div class="w-12 h-12 mx-auto rounded-full bg-[#199CA4]/10 text-[#199CA4] flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-600 dark:text-slate-300 block font-medium">Choose a high-quality photo</span>
                    <p class="text-xs text-gray-400 dark:text-slate-500">PNG, JPG, JPEG up to 2MB</p>
                </div>

                <input type="file" id="pet_photo_input" name="photo" accept="image/*" @change="handleFileChange($event)"
                    class="block w-full text-sm text-gray-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-[#199CA4]/10 file:text-[#199CA4] hover:file:bg-[#199CA4]/20 file:transition cursor-pointer"
                    :class="{ 'mt-3': !previewUrl, 'sr-only': previewUrl }"
                    :required="!isWizard && !{{ optional($pet)->id ? 'true' : 'false' }}">
            </div>
        </div>

        {{-- Description Textarea --}}
        <div class="mb-8">
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Pet Description</label>
            <textarea name="description" rows="4" x-model="description"
                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white"
                placeholder="Add a short summary that adopters can read...">{{ $currentDescription }}</textarea>
        </div>

        {{-- Profile Review Summary Card (Wizard Mode Only) --}}
        <div x-show="isWizard" class="rounded-2xl border border-gray-200 dark:border-white/[0.08] bg-gray-50/70 dark:bg-white/[0.02] p-5 mb-8">
            <div class="flex items-center justify-between mb-3 border-b border-gray-200/60 dark:border-white/[0.05] pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#199CA4]">Profile Summary</span>
                    <span class="text-xs text-gray-400 dark:text-slate-500">Review before saving</span>
                </div>
                <button type="button" @click="goToStep(1)" class="text-xs font-bold text-[#199CA4] hover:text-[#13787F] cursor-pointer">
                    Edit Details
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="text-gray-400 dark:text-slate-500 min-w-16">Pet:</span>
                        <span class="font-bold text-gray-800 dark:text-white capitalize" x-text="(type ? type : 'Pet') + ' — ' + (breed || 'Breed not set')"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-400 dark:text-slate-500 min-w-16">Traits:</span>
                        <span class="font-semibold text-gray-700 dark:text-slate-300 capitalize" x-text="(gender || 'Gender') + ' • ' + (age || 'Age') + ' • ' + (color || 'Color')"></span>
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex items-start gap-2">
                        <span class="text-gray-400 dark:text-slate-500 min-w-16">Medical:</span>
                        <div class="flex flex-wrap gap-1">
                            <template x-for="item in selectedMedical" :key="item">
                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-medium text-[11px]" x-text="item"></span>
                            </template>
                            <template x-if="hasDisability">
                                <span class="px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-medium text-[11px]" x-text="disabilityValue || 'With Disability'"></span>
                            </template>
                            <template x-if="selectedMedical.length === 0 && !hasDisability">
                                <span class="text-gray-400 dark:text-slate-500 italic">No medical records specified</span>
                            </template>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-400 dark:text-slate-500 min-w-16">Personality:</span>
                        <span class="text-gray-700 dark:text-slate-300 font-medium" x-text="selectedTags.length > 0 ? selectedTags.length + ' traits selected' : 'No traits selected'"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3 Navigation & Save Buttons (Wizard Mode Only) --}}
        <div x-show="isWizard" class="flex items-center justify-between border-t border-gray-100 dark:border-white/[0.06] pt-6 mt-8">
            <button type="button" @click="prevStep(3)"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-600 dark:text-slate-300 border border-gray-200 dark:border-white/[0.08] hover:bg-gray-50 dark:hover:bg-white/[0.06] transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Health & Traits</span>
            </button>
            <button type="button"
                @click="submitWizard($event)"
                :disabled="submitting"
                :class="{ 'opacity-60 cursor-not-allowed pointer-events-none': submitting }"
                class="inline-flex items-center gap-2 px-7 py-2.5 bg-[#199CA4] text-white text-sm font-semibold rounded-xl hover:bg-[#13787F] shadow-md shadow-[#199CA4]/10 transition cursor-pointer">
                <svg x-show="submitting" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span x-text="submitting ? 'Saving Pet...' : 'Save Pet Profile'">Save Pet Profile</span>
            </button>
        </div>
    </div>
</div>