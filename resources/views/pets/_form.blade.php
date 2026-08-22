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
    dogAges: ['Puppy', 'Adult', 'Senior'],
    catAges: ['Kitten', 'Adult', 'Senior'],
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
}" class="space-y-6">

    {{-- ── SECTION 1: BASIC INFORMATION ── --}}
    <div class="p-6 bg-white dark:bg-[#0e1d20] rounded-2xl border border-[#199CA4]/20 dark:border-slate-800 shadow-2xs space-y-6">
        <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
            <span class="w-8 h-8 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-sm">🐾</span>
            <div>
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-white uppercase tracking-wider">Basic Pet Classification</h3>
                <p class="text-[11px] text-slate-400 font-medium">Specify species, breed, color, gender, and age group</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Pet Type <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <select name="type" x-model="type" @change="breed = ''; age = ''; breedMode = 'select'"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#12272b] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/15 transition outline-none shadow-2xs text-slate-800 dark:text-white font-bold text-xs sm:text-sm cursor-pointer" required>
                        <option value="">Select Pet Type</option>
                        <option value="dog">🐕 Dog</option>
                        <option value="cat">🐈 Cat</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Breed <span class="text-rose-500">*</span></label>

                <input type="hidden" name="breed" x-model="breed">

                <template x-if="!type">
                    <select disabled class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#0a171a] text-slate-400 dark:text-slate-600 shadow-2xs text-xs sm:text-sm font-medium cursor-not-allowed">
                        <option>Select Pet Type First</option>
                    </select>
                </template>

                <template x-if="type && breedMode === 'select'">
                    <select @change="selectBreed($event.target.value)"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#12272b] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/15 transition outline-none shadow-2xs text-slate-800 dark:text-white font-bold text-xs sm:text-sm cursor-pointer">
                        <option value="">Select Breed</option>
                        <template x-for="option in breedOptions" :key="option">
                            <option :value="option" :selected="option === breed" x-text="option"></option>
                        </template>
                    </select>
                </template>

                <template x-if="type && breedMode === 'other'">
                    <div>
                        <input type="text" x-model="breed" placeholder="Enter custom breed..."
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#12272b] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/15 transition outline-none shadow-2xs text-slate-800 dark:text-white font-bold text-xs sm:text-sm">
                        <button type="button" @click="backToList()" class="mt-2 text-xs font-extrabold text-[#199CA4] dark:text-[#41C1CB] hover:underline transition flex items-center gap-1">← Choose from list instead</button>
                    </div>
                </template>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Color Pattern <span class="text-rose-500">*</span></label>
                <input type="text" name="color" value="{{ old('color', optional($pet)->color) }}"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#12272b] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/15 transition outline-none shadow-2xs text-slate-800 dark:text-white font-bold text-xs sm:text-sm placeholder-slate-400 dark:placeholder-slate-500"
                    placeholder="e.g., Brown, White/Black" required>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Gender <span class="text-rose-500">*</span></label>
                <select name="gender"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#12272b] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/15 transition outline-none shadow-2xs text-slate-800 dark:text-white font-bold text-xs sm:text-sm cursor-pointer" required>
                    <option value="">Select Gender</option>
                    <option value="male" {{ old('gender', optional($pet)->gender) == 'male' ? 'selected' : '' }}>Male (♂)</option>
                    <option value="female" {{ old('gender', optional($pet)->gender) == 'female' ? 'selected' : '' }}>Female (♀)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Age Stage</label>
                <select name="age" x-model="age" x-show="type" :required="type !== ''"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#12272b] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/15 transition outline-none shadow-2xs text-slate-800 dark:text-white font-bold text-xs sm:text-sm cursor-pointer">
                    <option value="">Select Age Group</option>
                    <template x-for="option in ageOptions" :key="option">
                        <option :value="option" x-text="option"></option>
                    </template>
                </select>
                <select x-show="!type" disabled class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#0a171a] text-slate-400 dark:text-slate-600 shadow-2xs text-xs sm:text-sm font-medium cursor-not-allowed">
                    <option>Select Type First</option>
                </select>
            </div>
        </div>

        {{-- If editing existing pet, allow status change. If registering new pet, automatically Available --}}
        @if($pet && $pet->id)
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Adoption Status <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-3 gap-3">
                    @php
                        $currentStatus = old('status', optional($pet)->status ?? 'available');
                    @endphp
                    <label class="flex items-center justify-center gap-2 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#12272b] hover:border-[#199CA4] hover:bg-[#F0FBFB] dark:hover:bg-[#15343a] transition cursor-pointer text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 has-[:checked]:border-[#199CA4] has-[:checked]:bg-[#199CA4]/10 dark:has-[:checked]:bg-[#199CA4]/25 has-[:checked]:text-[#199CA4] dark:has-[:checked]:text-[#41C1CB] has-[:checked]:ring-2 has-[:checked]:ring-[#199CA4]/20">
                        <input type="radio" name="status" value="available" class="hidden" {{ $currentStatus == 'available' ? 'checked' : '' }}>
                        <span class="w-2 h-2 rounded-full bg-[#199CA4]"></span>
                        <span>Available</span>
                    </label>
                    <label class="flex items-center justify-center gap-2 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#12272b] hover:border-[#199CA4] hover:bg-[#F0FBFB] dark:hover:bg-[#15343a] transition cursor-pointer text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50/70 dark:has-[:checked]:bg-amber-950/50 has-[:checked]:text-amber-800 dark:has-[:checked]:text-amber-300 has-[:checked]:ring-2 has-[:checked]:ring-amber-500/20">
                        <input type="radio" name="status" value="pending" class="hidden" {{ $currentStatus == 'pending' ? 'checked' : '' }}>
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>Pending</span>
                    </label>
                    <label class="flex items-center justify-center gap-2 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#12272b] hover:border-[#199CA4] hover:bg-[#F0FBFB] dark:hover:bg-[#15343a] transition cursor-pointer text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 has-[:checked]:border-slate-800 dark:has-[:checked]:border-slate-500 has-[:checked]:bg-slate-100 dark:has-[:checked]:bg-slate-800 has-[:checked]:text-slate-800 dark:has-[:checked]:text-white has-[:checked]:ring-2 has-[:checked]:ring-slate-400/20">
                        <input type="radio" name="status" value="adopted" class="hidden" {{ $currentStatus == 'adopted' ? 'checked' : '' }}>
                        <span class="w-2 h-2 rounded-full bg-slate-700 dark:bg-slate-300"></span>
                        <span>Adopted</span>
                    </label>
                </div>
            </div>
        @else
            {{-- Automatically set status to available for all new pets --}}
            <input type="hidden" name="status" value="available">
        @endif
    </div>

    {{-- ── SECTION 2: MEDICAL BACKGROUND ── --}}
    @php
        $selectedMedicalHistory = old('medical_history', optional($pet)->medical_history);
        if (is_string($selectedMedicalHistory)) {
            $selectedMedicalHistory = array_values(array_filter(array_map('trim', explode(',', $selectedMedicalHistory))));
        } elseif (!is_array($selectedMedicalHistory)) {
            $selectedMedicalHistory = [];
        }
    @endphp

    <div class="p-6 bg-white dark:bg-[#0e1d20] rounded-2xl border border-[#199CA4]/20 dark:border-slate-800 shadow-2xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-sm">🩺</span>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-800 dark:text-white uppercase tracking-wider">Medical & Health History</h3>
                    <p class="text-[11px] text-slate-400 font-medium">Checked records will be visible on pet profile</p>
                </div>
            </div>
            <span class="text-[11px] text-slate-400 font-semibold">Select all that apply</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @php $medicalOptions = ['Vaccinated', 'Spayed/Neutered', 'Dewormed', 'Microchipped', 'Flea & Tick Treated', 'Healthy']; @endphp
            @foreach($medicalOptions as $option)
                <label class="flex items-center gap-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#12272b] px-3.5 py-3 text-xs sm:text-sm text-slate-700 dark:text-slate-300 font-semibold hover:border-[#199CA4]/50 hover:bg-[#F0FBFB] dark:hover:bg-[#15343a] transition cursor-pointer shadow-2xs has-[:checked]:border-[#199CA4] has-[:checked]:bg-[#199CA4]/10 dark:has-[:checked]:bg-[#199CA4]/25 has-[:checked]:text-[#199CA4] dark:has-[:checked]:text-[#41C1CB] has-[:checked]:ring-2 has-[:checked]:ring-[#199CA4]/15">
                    <input type="checkbox" name="medical_history[]" value="{{ $option }}" class="rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-[#0e1d20] text-[#199CA4] focus:ring-[#199CA4] w-4 h-4" {{ in_array($option, $selectedMedicalHistory, true) ? 'checked' : '' }}>
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- ── SECTION 3: TEMPERAMENT TRAITS ── --}}
    @php
        $selectedTagIds = old('temperament_tags', $selectedTagIds ?? []);
    @endphp

    <div class="p-6 bg-white dark:bg-[#0e1d20] rounded-2xl border border-[#199CA4]/20 dark:border-slate-800 shadow-2xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-sm">🏷️</span>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-800 dark:text-white uppercase tracking-wider">Temperament Traits</h3>
                    <p class="text-[11px] text-slate-400 font-medium">Used for smart match recommendations</p>
                </div>
            </div>
            <span class="text-[11px] text-[#199CA4] dark:text-[#41C1CB] font-bold">ML Tags</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach($temperamentTags as $tag)
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#12272b] px-3 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 hover:border-[#199CA4]/50 hover:bg-[#F0FBFB] dark:hover:bg-[#15343a] transition cursor-pointer shadow-2xs has-[:checked]:border-[#199CA4] has-[:checked]:bg-[#199CA4]/10 dark:has-[:checked]:bg-[#199CA4]/25 has-[:checked]:text-[#199CA4] dark:has-[:checked]:text-[#41C1CB] has-[:checked]:ring-2 has-[:checked]:ring-[#199CA4]/15">
                    <input type="checkbox" name="temperament_tags[]" value="{{ $tag->id }}" class="rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-[#0e1d20] text-[#199CA4] focus:ring-[#199CA4] w-3.5 h-3.5" {{ in_array($tag->id, $selectedTagIds) ? 'checked' : '' }}>
                    <span>{{ $tag->name }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- ── SECTION 4: PHOTO UPLOAD ── --}}
    <div class="p-6 bg-white dark:bg-[#0e1d20] rounded-2xl border border-[#199CA4]/20 dark:border-slate-800 shadow-2xs space-y-4">
        <div class="flex items-center gap-2.5 pb-2">
            <span class="w-8 h-8 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-sm">📸</span>
            <div>
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-white uppercase tracking-wider">Pet Photo</h3>
                <p class="text-[11px] text-slate-400 font-medium">High resolution front-facing photo recommended</p>
            </div>
        </div>

        <div class="relative border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-[#199CA4] rounded-2xl p-6 text-center transition-all bg-slate-50/50 dark:bg-[#12272b]/50 hover:bg-[#F0FBFB]/30 dark:hover:bg-[#15343a]/50 group cursor-pointer shadow-2xs">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#F0FBFB] to-[#D6F4F6] dark:from-[#133036] dark:to-[#17454d] text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center text-xl font-bold mx-auto mb-2 group-hover:scale-110 transition-transform ring-1 ring-[#199CA4]/20 dark:ring-[#41C1CB]/30">
                📷
            </div>
            <span class="text-xs sm:text-sm text-slate-700 dark:text-slate-200 block font-bold mb-1">Click or drag a clear pet photo here</span>
            <p class="text-[11px] text-slate-400 mb-3">Supported formats: JPG, PNG, WEBP (Max 10MB)</p>
            <input type="file" name="photo" accept="image/*"
                class="block w-full max-w-xs mx-auto text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#199CA4]/10 dark:file:bg-[#199CA4]/25 file:text-[#199CA4] dark:file:text-[#41C1CB] hover:file:bg-[#199CA4]/20 file:transition cursor-pointer">
        </div>

        @if(optional($pet)->photo_path)
            <div class="flex items-center gap-4 pt-2">
                <div class="w-20 h-20 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-sm shrink-0">
                    <img src="{{ asset('storage/'.optional($pet)->photo_path) }}" class="w-full h-full object-cover">
                </div>
                <div>
                    <span class="inline-block px-2.5 py-0.5 rounded-md bg-[#199CA4]/10 dark:bg-[#199CA4]/25 text-[10px] uppercase font-extrabold text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20 dark:border-[#41C1CB]/30">Current Photo</span>
                    <p class="text-xs text-slate-600 dark:text-slate-400 font-semibold mt-1">Upload a new photo above only if you wish to replace it.</p>
                </div>
            </div>
        @endif
    </div>

</div>