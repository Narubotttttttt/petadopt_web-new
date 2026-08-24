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
                    <button type="button" @click="backToList()" class="mt-2 text-xs font-semibold text-[#199CA4] hover:text-[#13787F]">← Choose from list instead</button>
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
    @endphp

    <div class="mb-8">
        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Medical Background</label>
        <p class="text-sm text-gray-500 dark:text-slate-400 mb-3">Choose the options that best describe the pet's health history.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 rounded-2xl border border-gray-200 dark:border-white/[0.08] p-4 bg-gray-50/50 dark:bg-white/[0.02]">
            @php $medicalOptions = ['Vaccinated', 'Spayed/Neutered', 'Dewormed', 'Microchipped', 'Flea & Tick Treated', 'Healthy']; @endphp
            @foreach($medicalOptions as $option)
                <label class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] px-3 py-2 text-sm text-gray-700 dark:text-slate-200">
                    <input type="checkbox" name="medical_history[]" value="{{ $option }}" class="rounded border-gray-300 text-[#199CA4] focus:ring-[#199CA4]" {{ in_array($option, $selectedMedicalHistory, true) ? 'checked' : '' }}>
                    <span>{{ $option }}</span>
                </label>
            @endforeach
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

    <div class="mb-8">
        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Pet Photo</label>
        <div class="relative border-2 border-dashed border-gray-200 dark:border-white/[0.1] hover:border-[#199CA4] rounded-2xl p-6 text-center transition bg-gray-50/50 dark:bg-white/[0.02]">
            <span class="text-3xl block mb-2">📸</span>
            <span class="text-sm text-gray-600 dark:text-slate-300 block font-medium mb-1">Choose a high-quality photo</span>
            <input type="file" name="photo" accept="image/*"
                class="block w-full text-sm text-gray-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-[#199CA4]/10 file:text-[#199CA4] hover:file:bg-[#199CA4]/20 file:transition cursor-pointer">
        </div>
        @if(optional($pet)->photo_path)
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-2">Current photo:</p>
            <div class="w-28 h-28 mt-2 rounded-xl overflow-hidden border border-gray-200 dark:border-white/[0.08]">
                <img src="{{ asset('storage/'.optional($pet)->photo_path) }}" class="w-full h-full object-cover">
            </div>
        @endif
    </div>
</div>