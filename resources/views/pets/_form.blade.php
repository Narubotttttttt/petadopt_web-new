@php
    $pet = $pet ?? null;
    $isWizard = $isWizard ?? (!isset($pet));
    $dogBreeds = ['Aspin (Mixed Breed)'];
    $catBreeds = ['Puspin (Mixed Breed)'];

    if (!empty($currentBreed) && !in_array($currentBreed, ['Aspin (Mixed Breed)', 'Puspin (Mixed Breed)', 'Other'], true)) {
        if ($currentType === 'dog') {
            $dogBreeds[] = $currentBreed;
        } elseif ($currentType === 'cat') {
            $catBreeds[] = $currentBreed;
        }
    }
    $currentType = old('type', optional($pet)->type ?? '');
    $currentBreed = old('breed', optional($pet)->breed ?? '');
    $currentColor = old('color', optional($pet)->color ?? '');

    $dogColors = [
        'Black',
        'Brown',
        'White',
        'Golden',
        'Tan',
        'Cream',
        'Gray',
        'Brindle',
        'Tricolor',
        'Bicolor',
        'Sable',
        'Red / Rust',
    ];

    $catColors = [
        'Black',
        'White',
        'Orange',
        'Gray',
        'Brown',
        'Cream',
        'Calico',
        'Tortoiseshell',
        'Orange Tabby',
        'Gray Tabby',
        'Brown Tabby',
        'Tuxedo',
        'Bicolor',
    ];

    $allColorOptions = array_values(array_unique(array_merge($dogColors, $catColors)));
    $relevantColors = ($currentType === 'dog')
        ? $dogColors
        : (($currentType === 'cat') ? $catColors : $allColorOptions);

    $initialColorSelect = '';
    $initialColorOther = '';

    if (!empty($currentColor)) {
        $matchedColor = null;
        foreach ($relevantColors as $opt) {
            if (strcasecmp($opt, $currentColor) === 0) {
                $matchedColor = $opt;
                break;
            }
        }

        // Support 'Tortoise' matching 'Tortoiseshell'
        if ($matchedColor === null && strcasecmp($currentColor, 'Tortoise') === 0 && in_array('Tortoiseshell', $relevantColors, true)) {
            $matchedColor = 'Tortoiseshell';
        }

        if ($matchedColor !== null) {
            $initialColorSelect = $matchedColor;
        } else {
            // Check across all standard colors before falling back to Other Specified
            foreach ($allColorOptions as $opt) {
                if (strcasecmp($opt, $currentColor) === 0) {
                    $matchedColor = $opt;
                    break;
                }
            }
            if ($matchedColor !== null) {
                $initialColorSelect = $matchedColor;
            } else {
                $initialColorSelect = 'Other Specified';
                $initialColorOther = $currentColor;
            }
        }
    }
    $currentGender = old('gender', optional($pet)->gender ?? '');
    $currentAge = old('age', optional($pet)->age ?? '');
    $currentDescription = old('description', optional($pet)->description ?? '');

    $selectedMedicalHistory = old('medical_history', optional($pet)->medical_history);
    if (is_string($selectedMedicalHistory)) {
        $selectedMedicalHistory = array_values(array_filter(array_map('trim', explode(',', $selectedMedicalHistory))));
    } elseif (!is_array($selectedMedicalHistory)) {
        $selectedMedicalHistory = [];
    }

    // Load existing clinical records if editing
    $latestVaccineLog = optional($pet)->medicalLogs ? optional($pet)->medicalLogs->where('category', 'vaccination')->first() : null;
    $latestDewormLog = optional($pet)->medicalLogs ? optional($pet)->medicalLogs->where('category', 'deworming')->first() : null;

    $hasVaccinatedInitial = false;
    $initialVaccineName = ($currentType === 'cat') ? '4-in-1 (FVRCP)' : '5-in-1 (DHPP)';
    $initialVaccineDate = $latestVaccineLog?->date ? $latestVaccineLog->date->format('Y-m-d') : date('Y-m-d');
    $initialVaccineNextDue = $latestVaccineLog?->next_due_date ? $latestVaccineLog->next_due_date->format('Y-m-d') : '';

    $hasDewormedInitial = false;
    $initialDewormerName = 'Heartgard Plus';
    $initialDewormerDate = $latestDewormLog?->date ? $latestDewormLog->date->format('Y-m-d') : date('Y-m-d');
    $initialDewormerNextDue = $latestDewormLog?->next_due_date ? $latestDewormLog->next_due_date->format('Y-m-d') : '';

    $hasSpayedNeuteredInitial = false;

    foreach ($selectedMedicalHistory as $item) {
        if (stripos($item, 'Vaccinated') !== false) {
            $hasVaccinatedInitial = true;
            if (preg_match('/Vaccinated\s*\(([^\)]+)\)/i', $item, $vMatches)) {
                $initialVaccineName = trim($vMatches[1]);
            }
        }
        if (stripos($item, 'Dewormed') !== false) {
            $hasDewormedInitial = true;
            if (preg_match('/Dewormed\s*\(([^\)]+)\)/i', $item, $dMatches)) {
                $initialDewormerName = trim($dMatches[1]);
            }
        }
        if (stripos($item, 'Spayed') !== false || stripos($item, 'Neutered') !== false) {
            $hasSpayedNeuteredInitial = true;
        }
    }
    if ($latestVaccineLog) {
        $hasVaccinatedInitial = true;
        if (!empty($latestVaccineLog->vaccine_name)) {
            $initialVaccineName = $latestVaccineLog->vaccine_name;
        }
    }
    if ($latestDewormLog) {
        $hasDewormedInitial = true;
        if (!empty($latestDewormLog->vaccine_name)) {
            $initialDewormerName = $latestDewormLog->vaccine_name;
        }
    }

    if (old('is_vaccinated') !== null) {
        $hasVaccinatedInitial = old('is_vaccinated') == '1';
    }
    if (old('vaccine_name')) {
        $initialVaccineName = old('vaccine_name');
    }
    if (old('vaccine_date')) {
        $initialVaccineDate = old('vaccine_date');
    }
    if (old('vaccine_next_due')) {
        $initialVaccineNextDue = old('vaccine_next_due');
    }

    if (old('is_dewormed') !== null) {
        $hasDewormedInitial = old('is_dewormed') == '1';
    }
    if (old('dewormer_name')) {
        $initialDewormerName = old('dewormer_name');
    }
    if (old('dewormer_date')) {
        $initialDewormerDate = old('dewormer_date');
    }
    if (old('dewormer_next_due')) {
        $initialDewormerNextDue = old('dewormer_next_due');
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

    $temperamentTags = $temperamentTags ?? \App\Models\TemperamentTag::orderBy('name')->get();
    if ($temperamentTags->isEmpty()) {
        $defaultTags = [
            'Affectionate',
            'Calm',
            'Energetic',
            'Friendly',
            'Independent',
            'Playful',
            'Protective',
            'Shy',
        ];
        foreach ($defaultTags as $name) {
            \App\Models\TemperamentTag::firstOrCreate(['name' => $name]);
        }
        $temperamentTags = \App\Models\TemperamentTag::orderBy('name')->get();
    }

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
    colorSelect: '{{ addslashes($initialColorSelect) }}',
    colorOther: '{{ addslashes($initialColorOther) }}',
    dogColors: {{ json_encode($dogColors) }},
    catColors: {{ json_encode($catColors) }},
    gender: '{{ addslashes($currentGender) }}',
    age: '{{ addslashes($currentAge) }}',
    dogBreeds: {{ json_encode($dogBreeds) }},
    catBreeds: {{ json_encode($catBreeds) }},
    dogAges: ['Puppy', 'Adult'],
    catAges: ['Kitten', 'Adult'],

    // Step 2 fields
    hasDisability: {{ $hasDisabilityInitial ? 'true' : 'false' }},
    disabilityType: '{{ addslashes($initialDisabilityType) }}',
    disabilityOther: '{{ addslashes($initialDisabilityOther) }}',

    isVaccinated: {{ $hasVaccinatedInitial ? 'true' : 'false' }},
    vaccineSelect: '{{ addslashes(str_contains(strtolower($initialVaccineName), "rabies") ? "Anti-Rabies" : (str_contains(strtolower($initialVaccineName), "parvo") ? "Anti-Parvo" : "5-in-1")) }}',
    vaccineDate: '{{ addslashes($initialVaccineDate) }}',
    vaccineNextDue: '{{ addslashes($initialVaccineNextDue) }}',

    isDewormed: {{ $hasDewormedInitial ? 'true' : 'false' }},
    dewormerSelect: '{{ addslashes(in_array($initialDewormerName, ['Heartgard Plus', 'Pyrantel Embonate', 'Drontal Plus', 'NexGard Spectra', 'Broadline (Feline)', 'Revolution Plus']) ? $initialDewormerName : (empty($initialDewormerName) ? 'Heartgard Plus' : 'Other')) }}',
    dewormerOther: '{{ addslashes(!in_array($initialDewormerName, ['Heartgard Plus', 'Pyrantel Embonate', 'Drontal Plus', 'NexGard Spectra', 'Broadline (Feline)', 'Revolution Plus']) ? $initialDewormerName : '') }}',
    dewormerDate: '{{ addslashes($initialDewormerDate) }}',
    dewormerNextDue: '{{ addslashes($initialDewormerNextDue) }}',

    isSpayedNeutered: {{ $hasSpayedNeuteredInitial ? 'true' : 'false' }},

    selectedTags: {{ json_encode(array_map('strval', $selectedTagIds)) }},

    // Step 3 fields
    description: `{{ addslashes($currentDescription) }}`,
    previewUrl: '{{ optional($pet)->photo_path ? asset('storage/'.optional($pet)->photo_path) : '' }}',
    fileName: '',
    fileSize: '',
    isNew: false,

    get color() {
        if (this.colorSelect === 'Other Specified') {
            return this.colorOther ? this.colorOther.trim() : '';
        }
        return this.colorSelect || '';
    },
    get colorOptions() {
        return this.type === 'dog' ? this.dogColors : (this.type === 'cat' ? this.catColors : []);
    },
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
    get finalVaccineName() {
        if (!this.isVaccinated) return '';
        return this.vaccineSelect || '5-in-1';
    },
    get finalDewormerName() {
        if (!this.isDewormed) return '';
        if (this.dewormerSelect === 'Other') {
            return this.dewormerOther ? this.dewormerOther.trim() : 'Dewormed';
        }
        return this.dewormerSelect || 'Heartgard Plus';
    },
    get vaccinatedValue() {
        if (!this.isVaccinated) return '';
        const name = this.finalVaccineName;
        return name ? `Vaccinated (${name})` : 'Vaccinated';
    },
    get dewormedValue() {
        if (!this.isDewormed) return '';
        const name = this.finalDewormerName;
        return name ? `Dewormed (${name})` : 'Dewormed';
    },
    calcDateOffset(baseDate, months) {
        let d;
        if (!baseDate) {
            d = new Date();
        } else {
            const parts = baseDate.split('-');
            if (parts.length !== 3) d = new Date();
            else d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
        }
        d.setMonth(d.getMonth() + months);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    },

    init() {
        if (this.type && this.colorSelect && this.colorSelect !== 'Other Specified') {
            const validColors = this.type === 'dog' ? this.dogColors : (this.type === 'cat' ? this.catColors : []);
            if (validColors.length > 0 && !validColors.includes(this.colorSelect)) {
                this.colorOther = this.colorSelect;
                this.colorSelect = 'Other Specified';
            }
        }
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
            this.errorMessage = 'Please select the pet breed.';
            return false;
        }
        if (!this.colorSelect) {
            this.errorMessage = this.type === 'dog' 
                ? 'Please select a dog color.' 
                : (this.type === 'cat' ? 'Please select a cat color.' : 'Please select a pet color.');
            return false;
        }
        if (this.colorSelect === 'Other Specified' && (!this.colorOther || !this.colorOther.trim())) {
            this.errorMessage = 'Please specify the pet color.';
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
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Species / Pet Type</label>
                <select name="type" x-model="type" @change="breed = (type === 'dog' ? 'Aspin (Mixed Breed)' : (type === 'cat' ? 'Puspin (Mixed Breed)' : '')); age = ''; colorSelect = ''; colorOther = ''; errorMessage = ''"
                    :required="!isWizard"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white">
                    <option value="">Select species</option>
                    <option value="dog">Dog</option>
                    <option value="cat">Cat</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Breed</label>
                <input type="hidden" name="breed" :value="breed" value="{{ $currentBreed }}">

                <select x-show="!type || (type !== 'dog' && type !== 'cat')" disabled
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.06] bg-gray-50 dark:bg-white/[0.02] text-gray-400 dark:text-slate-500 shadow-sm text-sm">
                    <option>Select species first</option>
                </select>

                <select x-show="type === 'dog'" x-model="breed" @change="errorMessage = ''"
                    :required="!isWizard && type === 'dog'"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white text-sm">
                    <option value="">Select breed</option>
                    @foreach($dogBreeds as $b)
                        <option value="{{ $b }}" {{ $currentBreed === $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>

                <select x-show="type === 'cat'" x-model="breed" @change="errorMessage = ''"
                    :required="!isWizard && type === 'cat'"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white text-sm">
                    <option value="">Select breed</option>
                    @foreach($catBreeds as $b)
                        <option value="{{ $b }}" {{ $currentBreed === $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Color</label>
                <input type="hidden" name="color" :value="color" value="{{ $currentColor }}">

                {{-- Disabled state when species is not yet selected --}}
                <select x-show="!type || (type !== 'dog' && type !== 'cat')" disabled
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.06] bg-gray-50 dark:bg-white/[0.02] text-gray-400 dark:text-slate-500 shadow-xs text-sm">
                    <option>Select species first</option>
                </select>

                {{-- Dog Colors Dropdown --}}
                <select x-show="type === 'dog'" x-model="colorSelect"
                    @change="errorMessage = ''; if (colorSelect === 'Other Specified') $nextTick(() => $refs.colorOtherInput?.focus())"
                    :required="!isWizard && type === 'dog'"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-xs text-gray-800 dark:text-white text-sm">
                    <option value="">Select dog color</option>
                    @foreach($dogColors as $option)
                        <option value="{{ $option }}" {{ $initialColorSelect === $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                    <option value="Other Specified" {{ $initialColorSelect === 'Other Specified' ? 'selected' : '' }}>Other Specified</option>
                </select>

                {{-- Cat Colors Dropdown --}}
                <select x-show="type === 'cat'" x-model="colorSelect"
                    @change="errorMessage = ''; if (colorSelect === 'Other Specified') $nextTick(() => $refs.colorOtherInput?.focus())"
                    :required="!isWizard && type === 'cat'"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-xs text-gray-800 dark:text-white text-sm">
                    <option value="">Select cat color</option>
                    @foreach($catColors as $option)
                        <option value="{{ $option }}" {{ $initialColorSelect === $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                    <option value="Other Specified" {{ $initialColorSelect === 'Other Specified' ? 'selected' : '' }}>Other Specified</option>
                </select>

                <div x-show="colorSelect === 'Other Specified'"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="mt-2.5">
                    <input type="text" x-ref="colorOtherInput" x-model="colorOther" @input="errorMessage = ''"
                        placeholder="Please specify color..."
                        class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-xs text-gray-800 dark:text-white text-sm">
                </div>
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
            <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400">Medical Background & Clinical Intake</label>
                <span class="text-[11px] font-semibold text-[#199CA4] dark:text-[#41C1CB]">Auto-logged to Medical History</span>
            </div>
            <p class="text-sm text-gray-500 dark:text-slate-400 mb-3.5">Select treatments administered to this pet. Selecting Vaccinated or Dewormed automatically creates clinical ledger records with booster dates.</p>
            
            <div class="rounded-2xl border border-gray-200 dark:border-white/[0.08] p-4 bg-gray-50/50 dark:bg-white/[0.02] space-y-4">
                
                {{-- 4 Primary Option Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    
                    {{-- 1. Vaccinated Card --}}
                    <label class="flex items-start gap-3 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] p-3 text-sm cursor-pointer hover:border-[#199CA4]/50 transition shadow-2xs"
                           :class="{ 'border-[#199CA4] ring-2 ring-[#199CA4]/20 bg-[#199CA4]/5 dark:bg-[#199CA4]/10': isVaccinated }">
                        <input type="checkbox" x-model="isVaccinated" class="mt-0.5 rounded border-gray-300 text-[#199CA4] focus:ring-[#199CA4]">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-gray-900 dark:text-white text-sm">Vaccinated</span>
                                <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300">Auto-records</span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Specify vaccine name & date administered.</p>
                        </div>
                    </label>

                    {{-- 2. Dewormed Card --}}
                    <label class="flex items-start gap-3 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] p-3 text-sm cursor-pointer hover:border-emerald-500/50 transition shadow-2xs"
                           :class="{ 'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/50 dark:bg-emerald-950/20': isDewormed }">
                        <input type="checkbox" x-model="isDewormed" class="mt-0.5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-gray-900 dark:text-white text-sm">Dewormed</span>
                                <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300">Auto-records</span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Specify dewormer brand & date administered.</p>
                        </div>
                    </label>

                    {{-- 3. Spayed/Neutered Card --}}
                    <label class="flex items-start gap-3 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] p-3 text-sm cursor-pointer hover:border-[#199CA4]/50 transition shadow-2xs"
                           :class="{ 'border-[#199CA4] ring-2 ring-[#199CA4]/20 bg-[#199CA4]/5 dark:bg-[#199CA4]/10': isSpayedNeutered }">
                        <input type="checkbox" x-model="isSpayedNeutered" class="mt-0.5 rounded border-gray-300 text-[#199CA4] focus:ring-[#199CA4]">
                        <div class="flex-1 min-w-0">
                            <span class="font-bold text-gray-900 dark:text-white text-sm block">Spayed / Neutered</span>
                            <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Surgically sterilized.</p>
                        </div>
                    </label>

                    {{-- 4. With Disability Card --}}
                    <label class="flex items-start gap-3 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] p-3 text-sm cursor-pointer hover:border-amber-500/50 transition shadow-2xs"
                           :class="{ 'border-amber-500 ring-2 ring-amber-500/20 bg-amber-50/50 dark:bg-amber-950/20': hasDisability }">
                        <input type="checkbox" x-model="hasDisability" class="mt-0.5 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                        <div class="flex-1 min-w-0">
                            <span class="font-bold text-gray-900 dark:text-white text-sm block">With Disability / Special Needs</span>
                            <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Requires specialized shelter or adopter care.</p>
                        </div>
                    </label>
                </div>

                {{-- Hidden Form Inputs Submitted to Backend --}}
                <input type="hidden" name="is_vaccinated" :value="isVaccinated ? '1' : '0'">
                <input type="hidden" name="vaccine_name" :value="finalVaccineName" :disabled="!isVaccinated">
                <input type="hidden" name="vaccine_date" :value="vaccineDate" :disabled="!isVaccinated">
                <input type="hidden" name="vaccine_next_due" :value="vaccineNextDue" :disabled="!isVaccinated">

                <input type="hidden" name="is_dewormed" :value="isDewormed ? '1' : '0'">
                <input type="hidden" name="dewormer_name" :value="finalDewormerName" :disabled="!isDewormed">
                <input type="hidden" name="dewormer_date" :value="dewormerDate" :disabled="!isDewormed">
                <input type="hidden" name="dewormer_next_due" :value="dewormerNextDue" :disabled="!isDewormed">

                <input type="hidden" name="medical_history[]" :value="vaccinatedValue" :disabled="!isVaccinated">
                <input type="hidden" name="medical_history[]" :value="dewormedValue" :disabled="!isDewormed">
                <input type="hidden" name="medical_history[]" value="Spayed/Neutered" :disabled="!isSpayedNeutered">
                <input type="hidden" name="medical_history[]" :value="disabilityValue" :disabled="!hasDisability">

                {{-- Expandable Panel 1: Vaccination Details --}}
                <div x-show="isVaccinated"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     class="pt-3.5 pb-2 border-t border-blue-200/60 dark:border-blue-800/40 bg-blue-50/30 dark:bg-blue-950/10 p-4 rounded-xl space-y-3.5">
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-300 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-wider text-blue-900 dark:text-blue-200">Vaccination Clinical Details</span>
                        </div>
                        <span class="text-[11px] font-semibold text-blue-700 dark:text-blue-300">Will auto-record in Medical Logs</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Vaccine Name *</label>
                            <select x-model="vaccineSelect"
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition outline-none text-gray-800 dark:text-white text-xs font-semibold">
                                <option value="Anti-Rabies">Anti-Rabies</option>
                                <option value="5-in-1">5-in-1</option>
                                <option value="Anti-Parvo">Anti-Parvo (Parvovirus)</option>
                            </select>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Date Given *</label>
                                <button type="button" @click="vaccineDate = '{{ date('Y-m-d') }}'" class="text-[10px] text-[#199CA4] hover:underline font-bold cursor-pointer">Today</button>
                            </div>
                            <input type="date" x-model="vaccineDate"
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition outline-none text-gray-800 dark:text-white text-xs font-medium">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Next Booster Due</label>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="vaccineNextDue = calcDateOffset(vaccineDate, 6)" class="text-[10px] text-[#199CA4] hover:underline font-bold cursor-pointer">+6M</button>
                                    <button type="button" @click="vaccineNextDue = calcDateOffset(vaccineDate, 12)" class="text-[10px] text-[#199CA4] hover:underline font-bold cursor-pointer">+1Y</button>
                                    <button type="button" @click="vaccineNextDue = ''" x-show="vaccineNextDue" class="text-[10px] text-rose-500 hover:underline font-bold cursor-pointer">Clear</button>
                                </div>
                            </div>
                            <input type="date" x-model="vaccineNextDue"
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition outline-none text-gray-800 dark:text-white text-xs font-medium">
                        </div>
                    </div>
                </div>

                {{-- Expandable Panel 2: Deworming Details --}}
                <div x-show="isDewormed"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     class="pt-3.5 pb-2 border-t border-emerald-200/60 dark:border-emerald-800/40 bg-emerald-50/30 dark:bg-emerald-950/10 p-4 rounded-xl space-y-3.5">
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-300 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-900 dark:text-emerald-200">Deworming Clinical Details</span>
                        </div>
                        <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-300">Will auto-record in Medical Logs</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Dewormer Product *</label>
                            <select x-model="dewormerSelect"
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition outline-none text-gray-800 dark:text-white text-xs font-semibold">
                                <option value="Heartgard Plus">Heartgard Plus</option>
                                <option value="Pyrantel Embonate">Pyrantel Embonate</option>
                                <option value="Drontal Plus">Drontal Plus</option>
                                <option value="NexGard Spectra">NexGard Spectra</option>
                                <option value="Broadline (Feline)">Broadline (Feline)</option>
                                <option value="Revolution Plus">Revolution Plus</option>
                                <option value="Other">Other (Specify below)</option>
                            </select>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Date Given *</label>
                                <button type="button" @click="dewormerDate = '{{ date('Y-m-d') }}'" class="text-[10px] text-emerald-600 hover:underline font-bold cursor-pointer">Today</button>
                            </div>
                            <input type="date" x-model="dewormerDate"
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition outline-none text-gray-800 dark:text-white text-xs font-medium">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Next Due Date</label>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="dewormerNextDue = calcDateOffset(dewormerDate, 1)" class="text-[10px] text-emerald-600 hover:underline font-bold cursor-pointer">+1M</button>
                                    <button type="button" @click="dewormerNextDue = calcDateOffset(dewormerDate, 3)" class="text-[10px] text-emerald-600 hover:underline font-bold cursor-pointer">+3M</button>
                                    <button type="button" @click="dewormerNextDue = ''" x-show="dewormerNextDue" class="text-[10px] text-rose-500 hover:underline font-bold cursor-pointer">Clear</button>
                                </div>
                            </div>
                            <input type="date" x-model="dewormerNextDue"
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition outline-none text-gray-800 dark:text-white text-xs font-medium">
                        </div>
                    </div>

                    <div x-show="dewormerSelect === 'Other'" x-transition class="pt-1">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-slate-400 mb-1">Custom Dewormer Name</label>
                        <input type="text" x-model="dewormerOther" placeholder="e.g., Fenbendazole, Praziquantel..."
                            class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition outline-none text-gray-800 dark:text-white text-xs">
                    </div>
                </div>

                {{-- Expandable Panel 3: Disability Details (Shown when With Disability is checked) --}}
                <div x-show="hasDisability" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     class="pt-3 border-t border-amber-200/60 dark:border-amber-800/40 bg-amber-50/30 dark:bg-amber-950/10 p-4 rounded-xl space-y-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 mb-1.5">
                            Disability Type
                        </label>
                        <select x-model="disabilityType" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition outline-none shadow-xs text-gray-800 dark:text-white text-sm">
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
                            class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition outline-none shadow-xs text-gray-800 dark:text-white text-sm">
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400">Temperament</label>
                <span class="text-[11px] font-semibold text-[#199CA4] dark:text-[#41C1CB]">Select all that apply</span>
            </div>
            <p class="text-sm text-gray-500 dark:text-slate-400 mb-3">Choose the behavioral traits that describe this pet.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 rounded-2xl border border-gray-200 dark:border-white/[0.08] p-4 bg-gray-50/50 dark:bg-white/[0.02]">
                @foreach($temperamentTags as $tag)
                    <label class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] px-3.5 py-2.5 text-sm text-gray-700 dark:text-slate-200 cursor-pointer hover:border-[#199CA4]/40 transition"
                           :class="{ 'border-[#199CA4] bg-[#199CA4]/5 dark:bg-[#199CA4]/10 ring-1 ring-[#199CA4]/30': selectedTags.includes('{{ $tag->id }}') }">
                        <input type="checkbox" name="temperament_tags[]" value="{{ $tag->id }}"
                               x-model="selectedTags"
                               class="rounded border-gray-300 text-[#199CA4] focus:ring-[#199CA4]"
                               {{ in_array($tag->id, $selectedTagIds) ? 'checked' : '' }}>
                        <span class="font-medium">{{ $tag->name }}</span>
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