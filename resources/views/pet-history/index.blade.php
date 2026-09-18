<x-app-layout>
    <div class="w-full py-4 sm:py-6 px-4 sm:px-6 lg:px-8 space-y-5"
         x-data="{
             events: @js($events->getCollection()->keyBy('id')),
             showDetailModal: false,
             selectedEvent: null,
             modalTab: 'timeline',
             showImageModal: false,
             previewImageUrl: '',
             previewImageTitle: '',
             openDetail(target, mode = null) {
                 const ev = (typeof target === 'string' || typeof target === 'number') ? (this.events[target] || null) : target;
                 if (!ev) return;
                 this.selectedEvent = Object.assign({}, ev);
                 this.modalTab = mode || 'timeline';
                 this.showDetailModal = true;
             },
             closeDetail() {
                 this.showDetailModal = false;
                 setTimeout(() => {
                     if (!this.showDetailModal) {
                         this.selectedEvent = null;
                     }
                 }, 200);
             },
             openImagePreview(url, title) {
                 if (!url) return;
                 this.previewImageUrl = url;
                 this.previewImageTitle = title || 'Pet Image Preview';
                 this.showImageModal = true;
             },
             closeImagePreview() {
                 this.showImageModal = false;
                 setTimeout(() => {
                     if (!this.showImageModal) {
                         this.previewImageUrl = '';
                     }
                 }, 200);
             },
             updateScrollLock() {
                 if (this.showDetailModal || this.showImageModal) {
                     document.documentElement.classList.add('overflow-hidden');
                 } else {
                     document.documentElement.classList.remove('overflow-hidden');
                 }
             }
         }"
         x-init="
             $watch('showDetailModal', () => updateScrollLock());
             $watch('showImageModal', () => updateScrollLock());
         "
         @keydown.escape.window="showImageModal ? closeImagePreview() : closeDetail()">

        <div class="space-y-5 animate-fade-in">
            {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Pet History & Records
                    </h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#199CA4]/10 dark:bg-white/[0.06] text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20 dark:border-white/[0.08]">
                        {{ $events->total() }} Pets in History
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Unified chronological life history, clinical milestones, and adoption records of all shelter pets.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('pets.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white text-xs font-bold shadow-xs transition-colors">
                    <span>+</span> Rescued Pet Intake
                </a>
                <a href="{{ route('medical-logs.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.1] text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200/80 dark:border-white/[0.08] shadow-xs transition-colors">
                    <span>+</span> Medical Log
                </a>
            </div>
        </div>


        {{-- Interactive Filter Bar --}}
        <div id="history-records" class="scroll-mt-20 bg-white dark:bg-[#12141C] rounded-2xl p-4 shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] space-y-3">
            <form id="historyFilterForm" method="GET" action="{{ route('pet-history.index') }}#history-records" class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                {{-- Status Filter Tabs --}}
                <div class="flex flex-wrap items-center gap-1.5">
                    @php
                        $statusTabs = [
                            'all'        => 'All Pets',
                            'in_shelter' => 'In Shelter',
                            'adopted'    => 'Adopted',
                        ];
                    @endphp
                    @foreach($statusTabs as $key => $label)
                        <button type="submit" name="status" value="{{ $key }}"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-colors cursor-pointer {{ ($status === $key || ($key === 'all' && $status === 'all' && $eventType === 'all')) ? 'bg-[#199CA4] text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-white/[0.1]' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- Search & Dropdowns --}}
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Intake Year Filter --}}
                    <select name="year" onchange="this.form.submit()"
                        class="bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-xs font-bold text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition cursor-pointer">
                        <option value="">All Intake Years</option>
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>Year {{ $year }}</option>
                        @endforeach
                    </select>

                    {{-- Species Filter --}}
                    <select name="species" onchange="this.form.submit()"
                        class="bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-xs font-bold text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition cursor-pointer">
                        <option value="all" {{ $species === 'all' ? 'selected' : '' }}>All Species</option>
                        <option value="dog" {{ $species === 'dog' ? 'selected' : '' }}>Dogs Only</option>
                        <option value="cat" {{ $species === 'cat' ? 'selected' : '' }}>Cats Only</option>
                    </select>

                    {{-- Search Input --}}
                    <div class="relative">
                        <input type="text" name="q" value="{{ $search }}" placeholder="Search pet, breed, adopter..."
                            class="pl-8 pr-3 py-2 bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-xs w-48 sm:w-60 focus:outline-none focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] shadow-2xs transition" />
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <button type="submit" class="px-3.5 py-2 bg-[#199CA4] hover:bg-[#13787F] text-white font-bold rounded-xl text-xs shadow-xs transition-colors cursor-pointer">
                        Filter
                    </button>

                    @if($search || $status !== 'all' || $species !== 'all' || $petIdFilter || $selectedYear)
                        <a href="{{ route('pet-history.index') }}#history-records" class="px-3 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Unified Historical Records Table of all Pets --}}
        <div class="bg-white dark:bg-[#12141C] rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] overflow-hidden">
            {{-- Desktop Table View (lg and up) --}}
            <div class="hidden lg:block overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-[#171923] border-b border-slate-200/80 dark:border-white/[0.06]">
                        <tr>
                            <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Pet Information</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Shelter Intake</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Adoption History</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Clinical History</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                        @forelse($events as $event)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-[#181A24] transition-colors text-xs sm:text-sm">
                                {{-- Pet Info --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <button type="button" 
                                            @click="openDetail('{{ $event['id'] }}', 'pet')" 
                                            class="shrink-0 cursor-pointer focus:outline-none group"
                                            title="View Pet Details">
                                            @if(!empty($event['pet_photo']))
                                                <img src="{{ $event['pet_photo'] }}" alt="{{ $event['pet_name'] }}" class="w-11 h-11 rounded-2xl object-cover shrink-0 border border-slate-200 dark:border-white/[0.08] group-hover:scale-105 transition" />
                                            @else
                                                <div class="w-11 h-11 rounded-2xl bg-slate-100 dark:bg-white/[0.06] text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-xs shrink-0 border border-slate-200 dark:border-white/[0.08] group-hover:scale-105 transition">
                                                    {{ strtoupper(substr($event['pet_name'], 0, 2)) }}
                                                </div>
                                            @endif
                                        </button>
                                        <div class="min-w-0">
                                            <button type="button" 
                                                @click="openDetail('{{ $event['id'] }}')" 
                                                class="font-extrabold text-slate-900 dark:text-white hover:text-[#199CA4] dark:hover:text-[#41C1CB] hover:underline truncate block max-w-[170px] text-left cursor-pointer">
                                                {{ $event['pet_name'] }}
                                            </button>
                                            <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium truncate max-w-[170px]">
                                                {{ ucfirst($event['pet_type'] ?? '') }}{{ $event['pet_breed'] ? ' • ' . $event['pet_breed'] : '' }}
                                            </p>
                                            <p class="text-[10px] text-slate-400 dark:text-slate-500">
                                                ID #{{ $event['id'] }} • {{ ucfirst($event['pet_gender'] ?? '') }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Shelter Intake Date --}}
                                <td class="px-5 py-3.5 whitespace-nowrap text-slate-700 dark:text-slate-300 font-semibold">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $event['intake_date'] }}</div>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500">{{ $event['intake_diff'] }}</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">By: {{ $event['added_by'] }}</div>
                                </td>

                                {{-- Current Status --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @php
                                        $statusStyles = [
                                            'available' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60',
                                            'pending'   => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60',
                                            'adopted'   => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusStyles[$event['status']] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                        {{ ucfirst($event['status']) }}
                                    </span>
                                </td>

                                {{-- Adoption History --}}
                                <td class="px-5 py-3.5">
                                    @if($event['status'] === 'adopted' && !empty($event['adoption']))
                                        <div class="font-bold text-slate-900 dark:text-white text-xs flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>{{ $event['adoption']['applicant_name'] }}</span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            Rehomed: {{ $event['adoption']['approved_at'] ?? 'Approved' }}
                                        </div>
                                        @if(!empty($event['adoption']['contract_url']))
                                            <a href="{{ $event['adoption']['contract_url'] }}" target="_blank" class="text-[10px] text-[#199CA4] hover:underline font-bold mt-0.5 block">
                                                View Adoption Contract
                                            </a>
                                        @endif
                                    @elseif($event['status'] === 'pending')
                                        <div class="text-xs font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            <span>Adoption Under Review</span>
                                        </div>
                                        <div class="text-[11px] text-slate-400">Processing applications</div>
                                    @else
                                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">In Shelter Care</span>
                                        <div class="text-[10px] text-slate-400">Available for Adoption</div>
                                    @endif
                                </td>

                                {{-- Clinical History Summary --}}
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-extrabold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60">
                                            {{ $event['medical_logs_count'] }} {{ Str::plural('Medical Log', $event['medical_logs_count']) }}
                                        </span>
                                    </div>
                                    @if(!empty($event['latest_medical']))
                                        <div class="text-[11px] text-slate-700 dark:text-slate-300 font-semibold mt-1">
                                            Latest: {{ $event['latest_medical']['category'] }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 dark:text-slate-500">
                                            By {{ $event['latest_medical']['administered_by'] }} • {{ $event['latest_medical']['date'] }}
                                        </div>
                                    @else
                                        <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">No clinical logs recorded</div>
                                    @endif
                                </td>

                                {{-- Action --}}
                                <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                    <button type="button"
                                        @click="openDetail('{{ $event['id'] }}')"
                                        class="inline-flex items-center px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.1] text-slate-700 dark:text-slate-200 font-bold transition text-xs border border-slate-200/80 dark:border-white/[0.08] cursor-pointer shadow-2xs">
                                        View History
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500">
                                    <p class="font-bold text-sm text-slate-600 dark:text-slate-300">No pet history records found</p>
                                    <p class="text-xs text-slate-400 mt-1">Try adjusting your filters or search terms.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile & Tablet Card View (below lg) --}}
            <div class="block lg:hidden divide-y divide-slate-100 dark:divide-white/[0.06]">
                @forelse($events as $event)
                    <div class="p-4 sm:p-5 space-y-3 hover:bg-slate-50/50 dark:hover:bg-[#181A24] transition-colors">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusStyles[$event['status']] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                {{ ucfirst($event['status']) }}
                            </span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold">
                                Rescued: {{ $event['intake_date'] }}
                            </span>
                        </div>

                        <div class="flex items-start gap-3">
                            <button type="button"
                                    @click="openDetail('{{ $event['id'] }}', 'pet')"
                                    class="shrink-0 cursor-pointer focus:outline-none group"
                                    title="View Pet Details">
                                @if(!empty($event['pet_photo']))
                                    <img src="{{ $event['pet_photo'] }}" alt="{{ $event['pet_name'] }}" class="w-12 h-12 rounded-xl object-cover shrink-0 border border-slate-200 dark:border-white/[0.08] group-hover:scale-105 transition" />
                                @else
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-white/[0.06] text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-xs shrink-0 border border-slate-200 dark:border-white/[0.08] group-hover:scale-105 transition">
                                        {{ strtoupper(substr($event['pet_name'], 0, 2)) }}
                                    </div>
                                @endif
                            </button>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <button type="button"
                                            @click="openDetail('{{ $event['id'] }}')"
                                            class="font-extrabold text-slate-900 dark:text-white hover:text-[#199CA4] dark:hover:text-[#41C1CB] hover:underline truncate text-sm text-left cursor-pointer">
                                        {{ $event['pet_name'] }}
                                    </button>
                                    <span class="text-xs text-slate-400 dark:text-slate-500 font-medium truncate">
                                        {{ ucfirst($event['pet_type'] ?? '') }}{{ $event['pet_breed'] ? ' • ' . $event['pet_breed'] : '' }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                                    @if($event['status'] === 'adopted' && !empty($event['adoption']))
                                        Adopted by: <span class="font-bold text-slate-900 dark:text-white">{{ $event['adoption']['applicant_name'] }}</span>
                                    @else
                                        Status: <span class="font-bold">{{ ucfirst($event['status']) }}</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ $event['medical_logs_count'] }} medical logs recorded
                                    @if(!empty($event['latest_medical']))
                                        • Latest: {{ $event['latest_medical']['category'] }} by {{ $event['latest_medical']['administered_by'] }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-white/[0.04] text-xs">
                            <span class="text-slate-400 dark:text-slate-500 truncate max-w-[50%]">Added by {{ $event['added_by'] }}</span>
                            <button type="button"
                                    @click="openDetail('{{ $event['id'] }}')"
                                    class="inline-flex items-center px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.1] text-slate-700 dark:text-slate-200 font-bold transition text-xs border border-slate-200/80 dark:border-white/[0.08] cursor-pointer shadow-2xs">
                                View History
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 dark:text-slate-500">
                        <p class="font-bold text-sm text-slate-600 dark:text-slate-300">No pet history records found</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination Links --}}
            @if($events->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-white/[0.06]">
                    {{ $events->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Comprehensive Pet History & Dossier Modal --}}
    <template x-teleport="body">
        <div x-show="showDetailModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 md:p-6 overflow-y-auto"
             role="dialog"
             aria-modal="true"
             aria-labelledby="history-detail-modal-title">

            {{-- Backdrop --}}
            <div x-show="showDetailModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeDetail()"
                 class="fixed inset-0 bg-slate-900/60 dark:bg-black/80 backdrop-blur-xs transition-opacity"></div>

            {{-- Modal Content Panel --}}
            <div x-show="showDetailModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.stop
                 class="relative z-10 w-full max-w-full sm:max-w-2xl lg:max-w-3xl my-auto max-h-[85vh] sm:max-h-[88vh] flex flex-col rounded-2xl sm:rounded-3xl bg-white dark:bg-[#12141C] text-left shadow-2xl border border-slate-200/80 dark:border-white/[0.08] overflow-hidden">
                
                <template x-if="selectedEvent">
                    <div class="flex flex-col max-h-[85vh] sm:max-h-[88vh] overflow-hidden">
                        {{-- Modal Header --}}
                        <div class="shrink-0 px-4 sm:px-6 py-3.5 sm:py-4 bg-slate-50/90 dark:bg-[#171923] border-b border-slate-200/80 dark:border-white/[0.06] flex flex-col gap-3">
                            <div class="flex items-center justify-between gap-2.5">
                                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                                    {{-- Pet Avatar / Icon --}}
                                    <div class="w-10 h-10 rounded-2xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-extrabold text-sm shrink-0 border border-[#199CA4]/20">
                                        <template x-if="selectedEvent.photo_url">
                                            <img :src="selectedEvent.photo_url" :alt="selectedEvent.pet_name" class="w-full h-full object-cover rounded-2xl" />
                                        </template>
                                        <template x-if="!selectedEvent.photo_url">
                                            <span x-text="selectedEvent.pet_name ? selectedEvent.pet_name.substring(0, 2).toUpperCase() : 'PT'"></span>
                                        </template>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold border bg-[#199CA4]/10 text-[#199CA4] dark:bg-teal-950/40 dark:text-[#41C1CB] border-[#199CA4]/25">
                                                Pet Dossier
                                            </span>
                                            <span class="text-xs text-slate-400 dark:text-slate-500 font-medium" x-text="'Rescued ' + selectedEvent.intake_date"></span>
                                        </div>
                                        <h3 id="history-detail-modal-title" class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white mt-0.5 truncate" x-text="selectedEvent.pet_name + ' • Complete History & Dossier'"></h3>
                                    </div>
                                </div>

                                <button type="button" 
                                        @click="closeDetail()"
                                        aria-label="Close modal"
                                        class="w-8 h-8 rounded-xl shrink-0 bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.1] text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white flex items-center justify-center transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            {{-- In-Modal Interactive View Tab Switcher --}}
                            <div class="flex items-center gap-1.5 p-1 bg-slate-200/70 dark:bg-[#0C0D13] rounded-xl border border-slate-200/80 dark:border-white/[0.06] self-start">
                                <button type="button"
                                        @click="modalTab = 'timeline'"
                                        :class="modalTab === 'timeline' ? 'bg-white dark:bg-[#1E2130] text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                                        class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                                    <span>Lifecycle History Timeline</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#199CA4]"></span>
                                </button>
                                <button type="button"
                                        @click="modalTab = 'pet'"
                                        :class="modalTab === 'pet' ? 'bg-white dark:bg-[#1E2130] text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                                        class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                                    <span>Pet Profile Attributes</span>
                                </button>
                            </div>
                        </div>

                        {{-- Modal Body --}}
                        <div class="flex-1 p-4 sm:p-6 space-y-4 overflow-y-auto overscroll-contain">
                            
                            {{-- TAB 1: COMPLETE LIFECYCLE TIMELINE --}}
                            <template x-if="modalTab === 'timeline'">
                                <div class="space-y-4">
                                    {{-- Hero Overview Banner --}}
                                    <div class="p-3.5 rounded-2xl bg-gradient-to-r from-slate-50 to-white dark:from-[#171923] dark:to-[#12141C] border border-slate-200/80 dark:border-white/[0.08] flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <template x-if="selectedEvent.photo_url">
                                                <img :src="selectedEvent.photo_url" :alt="selectedEvent.pet_name" class="w-12 h-12 rounded-xl object-cover border border-slate-200 dark:border-white/[0.08]" />
                                            </template>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <h4 class="font-extrabold text-slate-900 dark:text-white text-base" x-text="selectedEvent.pet_name"></h4>
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-slate-100 dark:bg-white/[0.08] text-slate-700 dark:text-slate-300" x-text="selectedEvent.status"></span>
                                                </div>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                                    <span x-text="selectedEvent.breed"></span> • <span x-text="selectedEvent.gender"></span> • <span x-text="selectedEvent.age"></span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Catalog ID</span>
                                            <span class="text-xs font-black text-slate-800 dark:text-slate-200" x-text="'#' + selectedEvent.id"></span>
                                        </div>
                                    </div>

                                    {{-- Visual Vertical Timeline of Milestones --}}
                                    <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200 dark:before:bg-white/[0.08]">
                                        <template x-for="(milestone, idx) in selectedEvent.timeline" :key="milestone.id">
                                            <div class="relative group">
                                                {{-- Timeline Indicator Dot --}}
                                                <div class="absolute -left-6 top-1.5 w-5 h-5 rounded-full border-2 border-white dark:border-[#12141C] flex items-center justify-center"
                                                     :class="{
                                                         'bg-[#199CA4]': milestone.event_type === 'intake',
                                                         'bg-indigo-500': milestone.event_type === 'medical',
                                                         'bg-emerald-500': milestone.event_type === 'adoption',
                                                         'bg-teal-500': milestone.event_type === 'health_update'
                                                     }">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                                </div>

                                                {{-- Milestone Card Content --}}
                                                <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-[#171923] border border-slate-200/80 dark:border-white/[0.06] shadow-xs space-y-2">
                                                    <div class="flex items-center justify-between gap-2 flex-wrap">
                                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold border"
                                                              :class="milestone.badge_class"
                                                              x-text="milestone.badge_label"></span>
                                                        <span class="text-xs font-semibold text-slate-400 dark:text-slate-500" x-text="milestone.date"></span>
                                                    </div>

                                                    <h4 class="text-sm font-extrabold text-slate-900 dark:text-white" x-text="milestone.title"></h4>
                                                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed" x-text="milestone.description"></p>

                                                    <div class="pt-2 border-t border-slate-100 dark:border-white/[0.04] flex items-center justify-between text-xs text-slate-400 dark:text-slate-500 flex-wrap gap-2">
                                                        <span x-text="milestone.meta"></span>
                                                        <template x-if="milestone.details && milestone.details.contract_url">
                                                            <a :href="milestone.details.contract_url" target="_blank" class="text-xs font-bold text-[#199CA4] hover:underline">
                                                                Download Contract PDF
                                                            </a>
                                                        </template>
                                                        <template x-if="milestone.details && milestone.details.image_url">
                                                            <button type="button"
                                                                    @click="openImagePreview(milestone.details.image_url, milestone.title)"
                                                                    class="text-xs font-bold text-[#199CA4] hover:underline cursor-pointer">
                                                                View Attachment Image
                                                            </button>
                                                        </template>
                                                        <template x-if="milestone.details && milestone.details.edit_url">
                                                            <a :href="milestone.details.edit_url" class="text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-[#199CA4] hover:underline">
                                                                Edit Clinical Log
                                                            </a>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            {{-- TAB 2: PET ATTRIBUTES & SPECIFICATIONS --}}
                            <template x-if="modalTab === 'pet'">
                                <div class="space-y-4">
                                    {{-- Pet Hero Header Card --}}
                                    <div class="p-4 rounded-2xl bg-gradient-to-r from-slate-50 to-white dark:from-[#171923] dark:to-[#12141C] border border-slate-200/80 dark:border-white/[0.08] flex flex-col sm:flex-row items-center sm:items-start gap-4">
                                        <template x-if="selectedEvent.photo_url">
                                            <div class="relative group cursor-pointer shrink-0" @click="openImagePreview(selectedEvent.photo_url, selectedEvent.pet_name + ' Photo')">
                                                <img :src="selectedEvent.photo_url" :alt="selectedEvent.pet_name" class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border-2 border-white dark:border-[#12141C] shadow-sm hover:opacity-95 transition" />
                                                <div class="absolute inset-0 bg-black/40 rounded-2xl opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-[10px] font-bold transition">
                                                    Enlarge
                                                </div>
                                            </div>
                                        </template>
                                        <template x-if="!selectedEvent.photo_url">
                                            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-br from-[#199CA4]/20 to-[#199CA4]/10 dark:from-[#199CA4]/30 dark:to-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-black text-2xl shrink-0 border border-[#199CA4]/20"
                                                 x-text="selectedEvent.pet_name ? selectedEvent.pet_name.substring(0, 2).toUpperCase() : 'PT'">
                                            </div>
                                        </template>
                                        <div class="min-w-0 flex-1 text-center sm:text-left">
                                            <div class="flex items-center justify-center sm:justify-start gap-2 flex-wrap">
                                                <h3 class="text-xl font-black text-slate-900 dark:text-white" x-text="selectedEvent.pet_name"></h3>
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold uppercase tracking-wider bg-slate-100 dark:bg-white/[0.08] text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08]" x-text="selectedEvent.status"></span>
                                            </div>
                                            <div class="flex items-center justify-center sm:justify-start gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300 mt-1.5 flex-wrap">
                                                <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-white/[0.05] text-slate-700 dark:text-slate-300" x-text="selectedEvent.type ? (selectedEvent.type.charAt(0).toUpperCase() + selectedEvent.type.slice(1)) : 'Pet'"></span>
                                                <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-white/[0.05] text-slate-700 dark:text-slate-300" x-text="selectedEvent.breed"></span>
                                                <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-white/[0.05] text-slate-700 dark:text-slate-300" x-text="selectedEvent.gender"></span>
                                            </div>
                                            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1.5 font-medium" x-text="'Shelter Catalog ID #' + selectedEvent.id"></p>
                                        </div>
                                    </div>

                                    {{-- Pet Specification Matrix --}}
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Age</span>
                                            <span class="text-xs font-extrabold text-slate-900 dark:text-white mt-1 block" x-text="selectedEvent.age || 'Unknown'"></span>
                                        </div>
                                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Coat Color</span>
                                            <span class="text-xs font-extrabold text-slate-900 dark:text-white mt-1 block" x-text="selectedEvent.color || 'N/A'"></span>
                                        </div>
                                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06]">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Intake Date</span>
                                            <span class="text-xs font-extrabold text-slate-900 dark:text-white mt-1 block" x-text="selectedEvent.intake_date"></span>
                                        </div>
                                    </div>

                                    {{-- Background Description --}}
                                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06] space-y-1.5">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Intake & Background Description</h4>
                                        <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed" x-text="selectedEvent.description || 'No background description recorded.'"></p>
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-2">
                                        <a :href="selectedEvent.action_url" class="px-4 py-2 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white text-xs font-bold transition shadow-xs">
                                            Go to Full Pet Profile
                                        </a>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    {{-- Enlarged Image Preview Modal --}}
    <template x-teleport="body">
        <div x-show="showImageModal"
             x-cloak
             class="fixed inset-0 z-[60] flex items-center justify-center p-3 sm:p-6 overflow-y-auto"
             role="dialog"
             aria-modal="true">
            <div x-show="showImageModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeImagePreview()"
                 class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity"></div>

            <div x-show="showImageModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.stop
                 class="relative z-10 max-w-2xl my-auto max-h-[85vh] flex flex-col rounded-2xl bg-[#12141C] border border-white/[0.1] shadow-2xl overflow-hidden">
                <div class="p-3 bg-[#171923] border-b border-white/[0.08] flex items-center justify-between">
                    <span class="text-xs font-bold text-white truncate" x-text="previewImageTitle"></span>
                    <button type="button" @click="closeImagePreview()" class="text-slate-400 hover:text-white p-1 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-4 flex items-center justify-center bg-black/40 overflow-auto">
                    <img :src="previewImageUrl" :alt="previewImageTitle" class="max-w-full max-h-[70vh] object-contain rounded-xl" />
                </div>
            </div>
        </div>
    </template>
</div>

    {{-- Script for Filter Scroll Preservation --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterForm = document.getElementById('historyFilterForm');
            if (filterForm) {
                filterForm.addEventListener('submit', function () {
                    sessionStorage.setItem('pet_history_scroll_pos', window.scrollY.toString());
                });
            }

            const savedScroll = sessionStorage.getItem('pet_history_scroll_pos');
            if (savedScroll !== null) {
                sessionStorage.removeItem('pet_history_scroll_pos');
                window.scrollTo({
                    top: parseInt(savedScroll, 10),
                    behavior: 'instant'
                });
            }
        });
    </script>
</x-app-layout>
