@php $pet = $pet ?? null; @endphp

@php
    $dogBreeds = ['Aspin (Mixed Breed)', 'Shih Tzu / Aspin Mix', 'Labrador/ Mix', 'Other'];
    $catBreeds = ['Puspin (Mixed Breed)', 'Other'];
    $currentType = old('type', optional($pet)->type);
    $currentBreed = old('breed', optional($pet)->breed);
    $currentAge = old('age', optional($pet)->age);
@endphp

<div x-data="{
    type: '{{ $currentType }}',
    breed: '{{ $currentBreed }}',
    age: '{{ $currentAge }}',
    breedMode: 'select',
    dogBreeds: {{ json_encode($dogBreeds) }},
    catBreeds: {{ json_encode($catBreeds) }},
    dogAges: ['Puppy', 'Adult'],
    catAges: ['Kitten', 'Adult'],
    get breedOptions() {
        return this.type === 'dog' ? this.dogBreeds : (this.type === 'cat' ? this.catBreeds : []);
    },
    get ageOptions() {
        return this.type === 'dog' ? this.dogAges : (this.type === 'cat' ? this.catAges : []);
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
    }
}">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Pet Type</label>
            <select name="type" x-model="type" @change="breed = ''; age = ''; breedMode = 'select'"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white" required>
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
                <select @change="selectBreed($event.target.value)"
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
            <input type="text" name="color" value="{{ old('color', optional($pet)->color) }}"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white"
                placeholder="e.g., Brown, or White/Brown for mixed colors" required>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Gender</label>
            <select name="gender"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white" required>
                <option value="">Select gender</option>
                <option value="male" {{ old('gender', optional($pet)->gender) == 'male' ? 'selected' : '' }}>Male</option>
                <option value="female" {{ old('gender', optional($pet)->gender) == 'female' ? 'selected' : '' }}>Female</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Age</label>
            <select name="age" x-model="age" x-show="type" :required="type !== ''"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white">
                <option value="">Select age</option>
                <template x-for="option in ageOptions" :key="option">
                    <option :value="option" x-text="option"></option>
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

    @php
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
    @endphp

    <div class="mb-8" x-data="{
        hasDisability: {{ $hasDisabilityInitial ? 'true' : 'false' }},
        disabilityType: '{{ addslashes($initialDisabilityType) }}',
        disabilityOther: '{{ addslashes($initialDisabilityOther) }}',
        get disabilityValue() {
            if (!this.hasDisability) return '';
            if (this.disabilityType === 'Other') {
                return this.disabilityOther.trim() ? `With Disability (${this.disabilityOther.trim()})` : 'With Disability';
            }
            if (this.disabilityType) {
                return `With Disability (${this.disabilityType})`;
            }
            return 'With Disability';
        }
    }">
        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Medical Background</label>
        <p class="text-sm text-gray-500 dark:text-slate-400 mb-3">Choose the options that best describe the pet's health history.</p>
        <div class="rounded-2xl border border-gray-200 dark:border-white/[0.08] p-4 bg-gray-50/50 dark:bg-white/[0.02] space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @php $medicalStandardOptions = ['Vaccinated', 'Spayed/Neutered', 'Dewormed']; @endphp
                @foreach($medicalStandardOptions as $option)
                    <label class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] px-3 py-2 text-sm text-gray-700 dark:text-slate-200 cursor-pointer hover:border-[#199CA4]/40 transition">
                        <input type="checkbox" name="medical_history[]" value="{{ $option }}" class="rounded border-gray-300 text-[#199CA4] focus:ring-[#199CA4]" {{ in_array($option, $selectedMedicalHistory, true) ? 'checked' : '' }}>
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

    @php
        $selectedTagIds = old('temperament_tags', $selectedTagIds ?? []);
    @endphp

    <div class="mb-8">
        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Temperament</label>
        <p class="text-sm text-gray-500 dark:text-slate-400 mb-3">Choose all traits that describe this pet.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 rounded-2xl border border-gray-200 dark:border-white/[0.08] p-4 bg-gray-50/50 dark:bg-white/[0.02]">
            @foreach($temperamentTags as $tag)
                <label class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] px-3 py-2 text-sm text-gray-700 dark:text-slate-200">
                    <input type="checkbox" name="temperament_tags[]" value="{{ $tag->id }}" class="rounded border-gray-300 text-[#199CA4] focus:ring-[#199CA4]" {{ in_array($tag->id, $selectedTagIds) ? 'checked' : '' }}>
                    <span>{{ $tag->name }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="mb-8">
        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Pet Description</label>
        <textarea name="description" rows="4"
            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white" placeholder="Add a short summary that adopters can read...">{{ old('description', optional($pet)->description) }}</textarea>
    </div>

    <div class="mb-8" x-data="{
        previewUrl: '{{ optional($pet)->photo_path ? asset('storage/'.optional($pet)->photo_path) : '' }}',
        fileName: '',
        fileSize: '',
        isNew: false,
        handleFileChange(e) {
            const file = e.target.files[0];
            if (file) {
                this.fileName = file.name;
                this.fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                this.isNew = true;
                const reader = new FileReader();
                reader.onload = (event) => {
                    this.previewUrl = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        clearSelection() {
            this.previewUrl = '{{ optional($pet)->photo_path ? asset('storage/'.optional($pet)->photo_path) : '' }}';
            this.fileName = '';
            this.fileSize = '';
            this.isNew = false;
            const input = document.getElementById('pet_photo_input');
            if (input) input.value = '';
        }
    }">
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
                {{ optional($pet)->id ? '' : 'required' }}>
        </div>
    </div>
</div>