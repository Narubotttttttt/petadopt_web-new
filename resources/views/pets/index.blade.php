<x-app-layout>
    <div class="w-full py-6 sm:py-8 px-4 sm:px-6 lg:px-8 animate-fade-in space-y-5 sm:space-y-6" 
         x-data="{ 
             registrationModal: false,
             init() {
                 this.$watch('registrationModal', val => {
                     if (val) {
                         document.body.classList.add('overflow-hidden');
                     } else {
                         document.body.classList.remove('overflow-hidden');
                     }
                 });
             }
         }">
        
        {{-- Header & Search Actions --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Rescue Pets Directory</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Manage and track all rescued dogs and cats in the CAWS database.</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3 w-full sm:w-auto">
                <form method="GET" action="{{ route('pets.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
                    <input type="hidden" name="status" value="{{ $activeTab ?? 'in_shelter' }}">
                    <div class="relative flex-1 sm:w-72 md:w-80">
                        <input 
                            name="q" 
                            value="{{ old('q', request('q')) }}" 
                            placeholder="Search by name, ID (e.g. Pet no. 1), breed..." 
                            class="w-full pl-9 {{ request('q') ? 'pr-8' : 'pr-4' }} py-2.5 bg-white dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] shadow-2xs transition font-semibold text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl" 
                        />
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        @if(request('q'))
                            <a href="{{ route('pets.index', ['status' => $activeTab ?? 'in_shelter']) }}" title="Clear search" class="absolute right-2.5 top-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </a>
                        @endif
                    </div>
                    <button type="submit" class="px-4 sm:px-5 py-2.5 bg-[#199CA4] hover:bg-[#13787F] text-white font-extrabold rounded-xl text-xs sm:text-sm shadow-xs transition-all cursor-pointer shrink-0">
                        Search
                    </button>
                </form>
                @if(in_array(Auth::user()?->role, ['admin', 'staff']))
                    <button type="button" 
                            @click="registrationModal = true" 
                            class="inline-flex items-center justify-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white text-xs sm:text-sm font-extrabold shadow-xs transition-all duration-200 shrink-0 cursor-pointer">
                        <span class="text-base leading-none">+</span> Add New Pet
                    </button>
                @endif
            </div>
        </div>

        {{-- Active Search Filter Indicator --}}
        @if(request('q'))
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-white/[0.04] px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/[0.06] w-fit">
                <span>Filtering by: "<strong>{{ request('q') }}</strong>"</span>
                <a href="{{ route('pets.index', ['status' => $activeTab ?? 'in_shelter']) }}" class="text-[#199CA4] dark:text-[#41C1CB] hover:underline font-extrabold ms-1">Clear filter</a>
            </div>
        @endif

        {{-- Flash Feedback Notification --}}
        @if(session('success'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0" 
                 x-transition:leave-end="opacity-0 -translate-y-2" 
                 class="p-4 sm:p-5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700/60 text-emerald-900 dark:text-emerald-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm shadow-emerald-600/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-extrabold text-emerald-950 dark:text-emerald-100">
                                Pet Added Successfully
                            </h3>
                            @if(session('new_pet_id'))
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-600 text-white">
                                    Confirmed
                                </span>
                            @endif
                        </div>
                        <p class="text-xs sm:text-sm text-emerald-800 dark:text-emerald-300 font-medium mt-0.5">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                    @if(session('new_pet_id'))
                        <a href="{{ route('pets.show', session('new_pet_id')) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer">
                            <span>View Profile</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endif
                    <button type="button" @click="show = false" class="p-2 rounded-xl text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition cursor-pointer" title="Dismiss">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        @endif

        {{-- Status Filter Tabs Bar (matching Adoption Requests layout) --}}
        @if(in_array(Auth::user()?->role, ['admin', 'staff']))
            <div class="flex items-center gap-2 overflow-x-auto pt-1 pb-1 scrollbar-none">
                {{-- Tab 1: In Shelter (Newly rescued, under care/treatment) --}}
                <a href="{{ route('pets.index', ['status' => 'in_shelter', 'q' => $q]) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 border
                   {{ ($activeTab ?? 'in_shelter') === 'in_shelter' 
                       ? 'bg-[#199CA4] text-white border-[#199CA4] shadow-xs' 
                       : 'bg-white dark:bg-[#12141C] text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#181A24] border-slate-200/80 dark:border-white/[0.07]' }}">
                    <span>In Shelter</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ ($activeTab ?? 'in_shelter') === 'in_shelter' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-white/[0.08] text-slate-600 dark:text-slate-300' }}">
                        {{ $counts['in_shelter'] ?? 0 }}
                    </span>
                </a>

                {{-- Tab 2: Ready for Adoption (Available) --}}
                <a href="{{ route('pets.index', ['status' => 'available', 'q' => $q]) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 border
                   {{ ($activeTab ?? 'in_shelter') === 'available' 
                       ? 'bg-[#199CA4] text-white border-[#199CA4] shadow-xs' 
                       : 'bg-white dark:bg-[#12141C] text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#181A24] border-slate-200/80 dark:border-white/[0.07]' }}">
                    <span>Ready for Adoption</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ ($activeTab ?? 'in_shelter') === 'available' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-white/[0.08] text-slate-600 dark:text-slate-300' }}">
                        {{ $counts['available'] ?? 0 }}
                    </span>
                </a>

                {{-- Tab 3: Pending --}}
                <a href="{{ route('pets.index', ['status' => 'pending', 'q' => $q]) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 border
                   {{ ($activeTab ?? 'in_shelter') === 'pending' 
                       ? 'bg-[#199CA4] text-white border-[#199CA4] shadow-xs' 
                       : 'bg-white dark:bg-[#12141C] text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#181A24] border-slate-200/80 dark:border-white/[0.07]' }}">
                    <span>Pending</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ ($activeTab ?? 'in_shelter') === 'pending' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-white/[0.08] text-slate-600 dark:text-slate-300' }}">
                        {{ $counts['pending'] ?? 0 }}
                    </span>
                </a>

                {{-- Tab 4: Adopted --}}
                <a href="{{ route('pets.index', ['status' => 'adopted', 'q' => $q]) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 border
                   {{ ($activeTab ?? 'in_shelter') === 'adopted' 
                       ? 'bg-[#199CA4] text-white border-[#199CA4] shadow-xs' 
                       : 'bg-white dark:bg-[#12141C] text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#181A24] border-slate-200/80 dark:border-white/[0.07]' }}">
                    <span>Adopted</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ ($activeTab ?? 'in_shelter') === 'adopted' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-white/[0.08] text-slate-600 dark:text-slate-300' }}">
                        {{ $counts['adopted'] ?? 0 }}
                    </span>
                </a>
            </div>
        @endif

        {{-- Main Container Card --}}
        <div class="bg-white dark:bg-[#12141C] rounded-2xl sm:rounded-3xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] overflow-hidden relative">
            
            {{-- Card Header --}}
            <div class="p-4 sm:p-6 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between bg-slate-50/50 dark:bg-[#171923]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20 dark:border-[#41C1CB]/30 flex items-center justify-center font-bold text-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                    </div>
                    <h2 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white">Pet Profiles</h2>
                </div>
                <span class="text-[11px] sm:text-xs text-slate-600 dark:text-slate-300 font-extrabold uppercase tracking-wider bg-slate-100 dark:bg-white/[0.06] px-2.5 sm:px-3 py-1 rounded-full border border-slate-200 dark:border-white/[0.08]">
                    Total: {{ $pets->total() }} Pets
                </span>
            </div>

            @php
                $statusStyles = [
                    'in_shelter' => 'bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800/60',
                    'available' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60',
                    'pending' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60',
                    'adopted' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60',
                    'deceased' => 'bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-400 border-slate-300 dark:border-white/[0.1]',
                ];
                $dotColors = [
                    'in_shelter' => 'bg-sky-500',
                    'available' => 'bg-emerald-500',
                    'pending' => 'bg-amber-500',
                    'adopted' => 'bg-indigo-500',
                    'deceased' => 'bg-slate-400',
                ];
                $statusLabels = [
                    'in_shelter' => 'In Shelter',
                    'available' => 'Available',
                    'pending' => 'Pending',
                    'adopted' => 'Adopted',
                    'deceased' => 'Deceased',
                ];
            @endphp

            {{-- 1. Mobile Feed Presentation (< md screens) --}}
            <div class="block md:hidden divide-y divide-slate-100 dark:divide-white/[0.06]">
                @forelse($pets as $pet)
                    @php
                        $style = $statusStyles[$pet->status] ?? 'bg-slate-50 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 border-slate-200 dark:border-white/[0.08]';
                        $dot = $dotColors[$pet->status] ?? 'bg-slate-400';
                        $isNewlyAdded = session('new_pet_id') && session('new_pet_id') == $pet->id;
                    @endphp
                    <div class="p-4 space-y-3 transition-colors {{ $isNewlyAdded ? 'bg-emerald-50/70 dark:bg-emerald-950/30 border-l-4 border-l-emerald-500' : 'hover:bg-slate-50/50 dark:hover:bg-[#161822]' }}">
                        
                        {{-- Top Header Row: Thumbnail, Name & Status --}}
                        <div class="flex items-start gap-3">
                            <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-[#171923] overflow-hidden flex items-center justify-center border border-slate-200 dark:border-white/[0.08] shadow-2xs shrink-0 relative">
                                @if($pet->photo_path)
                                    <img src="{{ asset('storage/'.$pet->photo_path) }}" alt="Pet photo" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="hidden w-full h-full bg-[#199CA4]/10 dark:bg-[#199CA4]/20 items-center justify-center text-[#199CA4] dark:text-[#41C1CB]">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                    </div>
                                @else
                                    <div class="w-full h-full bg-[#199CA4]/10 dark:bg-[#199CA4]/20 flex items-center justify-center text-[#199CA4] dark:text-[#41C1CB]">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                    </div>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5 truncate">
                                        <h3 class="font-extrabold text-sm text-slate-900 dark:text-white truncate">
                                            {{ $pet->name ?: ('Pet no. ' . $pet->id) }}
                                        </h3>
                                        @if($isNewlyAdded)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#199CA4] text-white shrink-0 tracking-wide uppercase shadow-2xs">
                                                Just Added
                                            </span>
                                        @endif
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold border shrink-0 {{ $style }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dot }} animate-pulse"></span>
                                        {{ $statusLabels[$pet->status] ?? ucfirst(str_replace('_', ' ', $pet->status ?? 'Unknown')) }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-300 font-semibold mt-0.5 truncate">
                                    {{ $pet->breed ?? 'Mixed Breed' }} &bull; {{ ucfirst($pet->color ?? '—') }}
                                </p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                    {{ ucfirst($pet->type ?? 'Pet') }} &bull; {{ ucfirst($pet->size ?? 'Medium') }} &bull; {{ ucfirst($pet->gender ?? 'Unknown') }} &bull; {{ $pet->age ?? 'Age N/A' }}
                                </p>
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20">
                                        {{ $pet->days_in_shelter_formatted }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Minimal Actions Row --}}
                        <div class="flex items-center gap-2 pt-0.5">
                            @if(in_array(Auth::user()?->role, ['admin', 'staff']) && $pet->status === 'in_shelter')
                                <button type="button" 
                                        data-title='Promote "<strong>{{ $pet->name ?: ('Pet no. ' . $pet->id) }}</strong>" to Ready for Adoption?'
                                        data-message="This pet will become visible to the public in the adoption directory."
                                        data-action="{{ route('pets.mark-available', $pet) }}"
                                        data-confirm-text="Mark Ready"
                                        data-loading-text="Promoting..."
                                        data-type="success"
                                        data-method="POST"
                                        onclick="openConfirmModal(this.dataset)"
                                        class="flex-1 py-2 text-center rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs transition shadow-2xs cursor-pointer">
                                    Mark Ready
                                </button>
                            @endif
                            <a href="{{ route('pets.show', $pet) }}" class="flex-1 py-2 text-center rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white font-extrabold text-xs transition shadow-2xs">
                                View Details
                            </a>
                            @if(in_array(Auth::user()?->role, ['admin', 'staff']))
                                <a href="{{ route('pets.edit', $pet) }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#1D1F2C] hover:text-[#199CA4] dark:hover:text-white font-bold text-xs transition text-center">
                                    Edit
                                </a>
                                @if(Auth::user()?->role === 'admin')
                                    <button 
                                        type="button" 
                                        data-title='Delete "<strong>{{ $pet->name ?: ('Pet no. ' . $pet->id) }}</strong>"?'
                                        data-message="This action cannot be undone."
                                        data-action="{{ route('pets.destroy', $pet) }}"
                                        data-confirm-text="Delete"
                                        data-method="DELETE"
                                        onclick="openConfirmModal(this.dataset)"
                                        class="px-3.5 py-2 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 font-bold hover:bg-rose-100 dark:hover:bg-rose-900/60 transition text-xs cursor-pointer"
                                    >
                                        Delete
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 sm:p-12 text-center text-slate-400 dark:text-slate-500">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-white/[0.04] text-slate-400 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                        </div>
                        <p class="font-extrabold text-slate-700 dark:text-slate-300 text-sm mb-1">No pets found</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500">Try adjusting your search query or add a new rescue pet.</p>
                        @if(request('q'))
                            <a href="{{ route('pets.index', ['status' => $activeTab ?? 'in_shelter']) }}" class="inline-block mt-3 text-xs font-bold text-[#199CA4] hover:underline">Clear search filter</a>
                        @endif
                    </div>
                @endforelse
            </div>

            {{-- 2. Desktop High-Density Data Table (>= md screens) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-[#171923] border-b border-slate-200/80 dark:border-white/[0.06]">
                        <tr>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Pet</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Breed</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Color</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Stay Duration</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Type</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                        @forelse($pets as $pet)
                            @php
                                $style = $statusStyles[$pet->status] ?? 'bg-slate-50 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 border-slate-200 dark:border-white/[0.08]';
                                $dot = $dotColors[$pet->status] ?? 'bg-slate-400';
                                $isNewlyAdded = session('new_pet_id') && session('new_pet_id') == $pet->id;
                            @endphp
                            <tr class="text-xs sm:text-sm transition-colors {{ $isNewlyAdded ? 'bg-emerald-50/70 dark:bg-emerald-950/30 border-l-4 border-l-emerald-500' : 'hover:bg-slate-50/70 dark:hover:bg-[#181A24]' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-[#171923] overflow-hidden flex items-center justify-center border border-slate-200 dark:border-white/[0.08] shadow-2xs shrink-0 group relative">
                                            @if($pet->photo_path)
                                                <img src="{{ asset('storage/'.$pet->photo_path) }}" alt="Pet photo" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="hidden w-full h-full bg-[#199CA4]/10 dark:bg-[#199CA4]/20 items-center justify-center text-[#199CA4] dark:text-[#41C1CB]">
                                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                                </div>
                                            @else
                                                <div class="w-full h-full bg-[#199CA4]/10 dark:bg-[#199CA4]/20 flex items-center justify-center text-[#199CA4] dark:text-[#41C1CB]">
                                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="font-extrabold text-slate-900 dark:text-white">{{ $pet->name ?: ('Pet no. ' . $pet->id) }}</p>
                                                @if($isNewlyAdded)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#199CA4] text-white shrink-0 tracking-wide uppercase shadow-2xs">
                                                        Just Added
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-slate-400 dark:text-slate-500 font-semibold">{{ ucfirst($pet->size ?? 'Medium') }} &bull; {{ ucfirst($pet->gender ?? 'Unknown') }} &bull; {{ $pet->age ?? 'Age N/A' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-700 dark:text-slate-300 font-bold">{{ $pet->breed ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-400 font-medium">{{ $pet->color ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-extrabold text-slate-800 dark:text-slate-200 block text-xs">
                                        {{ $pet->days_in_shelter_formatted }}
                                    </span>
                                    <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium block mt-0.5">
                                        Added {{ $pet->created_at ? $pet->created_at->diffForHumans() : 'recently' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 font-bold text-slate-700 dark:text-slate-300">
                                        {{ ucfirst($pet->type ?? 'Unknown') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold border {{ $style }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dot }} animate-pulse"></span>
                                        {{ $statusLabels[$pet->status] ?? ucfirst(str_replace('_', ' ', $pet->status ?? 'Unknown')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        @if(in_array(Auth::user()?->role, ['admin', 'staff']) && $pet->status === 'in_shelter')
                                            <button type="button" 
                                                    data-title='Promote "<strong>{{ $pet->name ?: ('Pet no. ' . $pet->id) }}</strong>" to Ready for Adoption?'
                                                    data-message="This pet will become visible to the public in the adoption directory."
                                                    data-action="{{ route('pets.mark-available', $pet) }}"
                                                    data-confirm-text="Mark Ready"
                                                    data-loading-text="Promoting..."
                                                    data-type="success"
                                                    data-method="POST"
                                                    onclick="openConfirmModal(this.dataset)"
                                                    class="px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold transition-all duration-200 shadow-2xs text-xs whitespace-nowrap cursor-pointer"
                                                    title="Promote to Ready for Adoption">
                                                Mark Ready
                                            </button>
                                        @endif
                                        <a href="{{ route('pets.show', $pet) }}" class="px-3.5 py-1.5 rounded-xl bg-slate-900 dark:bg-white/[0.08] text-white dark:text-slate-200 hover:bg-slate-800 dark:hover:bg-white/[0.12] border border-slate-800 dark:border-white/[0.08] font-extrabold transition-all duration-200 shadow-2xs text-xs">View</a>
                                        @if(in_array(Auth::user()?->role, ['admin', 'staff']))
                                            <a href="{{ route('pets.edit', $pet) }}" class="px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#1D1F2C] hover:text-[#199CA4] dark:hover:text-white font-bold transition-all text-xs">Edit</a>
                                            @if(Auth::user()?->role === 'admin')
                                                <button 
                                                    type="button" 
                                                    data-title='Delete "<strong>{{ $pet->name ?: ('Pet no. ' . $pet->id) }}</strong>"?'
                                                    data-message="This action cannot be undone."
                                                    data-action="{{ route('pets.destroy', $pet) }}"
                                                    data-confirm-text="Delete"
                                                    data-method="DELETE"
                                                    onclick="openConfirmModal(this.dataset)"
                                                    class="px-3.5 py-1.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 font-bold hover:bg-rose-100 dark:hover:bg-rose-900/60 transition-colors text-xs cursor-pointer"
                                                >
                                                    Delete
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500">
                                    <p class="font-bold text-slate-600 dark:text-slate-300 mb-1">No pets found</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">Try adjusting your search query or add a new rescue pet.</p>
                                    @if(request('q'))
                                        <a href="{{ route('pets.index', ['status' => $activeTab ?? 'in_shelter']) }}" class="inline-block mt-3 text-xs font-bold text-[#199CA4] hover:underline">Clear search filter</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer Pagination --}}
            <div class="p-4 border-t border-slate-100 dark:border-white/[0.06] bg-slate-50/50 dark:bg-[#171923]">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                        Showing {{ $pets->firstItem() ?? 0 }} to {{ $pets->lastItem() ?? 0 }} of {{ $pets->total() }} pets
                    </p>
                    <div class="w-full sm:w-auto flex justify-center">
                        {{ $pets->links() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Registration Mode Selector Modal --}}
        <template x-teleport="body">
            <div x-show="registrationModal"
                 x-cloak
                 class="fixed inset-0 z-[70] overflow-y-auto"
                 aria-labelledby="modal-title" 
                 role="dialog" 
                 aria-modal="true"
                 @keydown.escape.window="registrationModal = false">
            
            {{-- Backdrop --}}
            <div x-show="registrationModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="registrationModal = false"
                 class="fixed inset-0 bg-slate-900/60 dark:bg-black/80 backdrop-blur-xs transition-opacity"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="registrationModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     @click.away="registrationModal = false"
                     class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-[#12141C] p-6 sm:p-8 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200/80 dark:border-white/[0.08]">
                     
                     {{-- Modal Header --}}
                     <div class="flex items-start justify-between mb-6">
                         <div>
                             <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB] mb-2">
                                 Pet Intake Registry
                             </span>
                             <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight" id="modal-title">
                                 Select Registration Type
                             </h3>
                             <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                                 Choose the intake workflow according to the pet's current medical and shelter status.
                             </p>
                         </div>
                         <button type="button" 
                                 @click="registrationModal = false"
                                 class="rounded-xl p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/[0.06] transition-colors focus:outline-none cursor-pointer shrink-0 ml-3">
                             <span class="sr-only">Close</span>
                             <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                             </svg>
                         </button>
                     </div>

                     {{-- Options Grid --}}
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                         {{-- Card 1: Newly Rescued Pet --}}
                         <a href="{{ route('pets.create', ['status' => 'in_shelter']) }}"
                            class="group relative flex flex-col justify-between p-5 rounded-2xl border-2 border-slate-200/80 dark:border-white/[0.08] hover:border-[#199CA4] dark:hover:border-[#199CA4] bg-slate-50/60 dark:bg-[#151722] hover:bg-teal-50/40 dark:hover:bg-[#199CA4]/5 transition-all duration-200 shadow-2xs hover:shadow-md cursor-pointer text-left">
                             
                             <div>
                                 <div class="flex items-center justify-between mb-4">
                                     <div class="w-12 h-12 rounded-xl bg-teal-100/70 dark:bg-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center group-hover:scale-105 transition-transform">
                                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                         </svg>
                                     </div>
                                     <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                         Fast 2-Step
                                     </span>
                                 </div>

                                 <h4 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-[#199CA4] dark:group-hover:text-[#41C1CB] transition-colors">
                                     Newly Rescued Pet
                                 </h4>
                                 <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-4 leading-relaxed">
                                     For stray animals rescued from CDO streets or the City Pound undergoing intake, isolation, or treatment.
                                 </p>

                                 <ul class="text-xs text-slate-600 dark:text-slate-300 space-y-2 mb-5">
                                     <li class="flex items-start gap-2">
                                         <svg class="w-4 h-4 text-[#199CA4] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                         <span>Rescue origin (Barangay & landmark)</span>
                                     </li>
                                     <li class="flex items-start gap-2">
                                         <svg class="w-4 h-4 text-[#199CA4] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                         <span>Intake triage (Disabilities & treatments)</span>
                                     </li>
                                     <li class="flex items-start gap-2">
                                         <svg class="w-4 h-4 text-[#199CA4] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                         <span>Auto-assigned observation status</span>
                                     </li>
                                 </ul>
                             </div>

                             <div class="pt-3 border-t border-slate-200/60 dark:border-white/[0.06] flex items-center justify-between text-xs font-extrabold text-[#199CA4] dark:text-[#41C1CB]">
                                 <span>Start Shelter Intake</span>
                                 <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                             </div>
                         </a>

                         {{-- Card 2: Ready for Adoption --}}
                         <a href="{{ route('pets.create', ['status' => 'available']) }}"
                            class="group relative flex flex-col justify-between p-5 rounded-2xl border-2 border-slate-200/80 dark:border-white/[0.08] hover:border-emerald-500 dark:hover:border-emerald-500 bg-slate-50/60 dark:bg-[#151722] hover:bg-emerald-50/40 dark:hover:bg-emerald-500/5 transition-all duration-200 shadow-2xs hover:shadow-md cursor-pointer text-left">
                             
                             <div>
                                 <div class="flex items-center justify-between mb-4">
                                     <div class="w-12 h-12 rounded-xl bg-emerald-100/70 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:scale-105 transition-transform">
                                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                         </svg>
                                     </div>
                                     <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                         Full 3-Step
                                     </span>
                                 </div>

                                 <h4 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                     Ready for Adoption
                                 </h4>
                                 <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-4 leading-relaxed">
                                     For evaluated, healthy pets with known temperaments ready to be listed publicly for adopter applications.
                                 </p>

                                 <ul class="text-xs text-slate-600 dark:text-slate-300 space-y-2 mb-5">
                                     <li class="flex items-start gap-2">
                                         <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                         <span>Full temperament & personality tags</span>
                                     </li>
                                     <li class="flex items-start gap-2">
                                         <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                         <span>Public adoption story & profile photo</span>
                                     </li>
                                     <li class="flex items-start gap-2">
                                         <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                         <span>Instant AI recommender matching</span>
                                     </li>
                                 </ul>
                             </div>

                             <div class="pt-3 border-t border-slate-200/60 dark:border-white/[0.06] flex items-center justify-between text-xs font-extrabold text-emerald-600 dark:text-emerald-400">
                                 <span>Create Adoption Listing</span>
                                 <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                             </div>
                         </a>
                     </div>
                </div>
            </div>
        </div>
    </template>
    </div>
</x-app-layout>