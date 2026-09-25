<x-app-layout>
    <script>
        function adoptionRealtime(config) {
            return {
                latestId: config.latestId || 0,
                activeTab: config.activeTab || 'all',
                checkUrl: config.checkUrl,
                markViewedUrl: config.markViewedUrl,
                csrfToken: config.csrfToken,
                counts: config.initialCounts || { all: 0, pending: 0, today_pending: 0, scheduled: 0, finalized: 0, rejected: 0 },
                showPendingIndicator: config.initialHasUnseen,
                newBadgeCount: config.initialNewBadgeCount,
                hasNewIncoming: false,
                incomingCount: 0,
                toasts: [],
                pollInterval: null,
                isPolling: false,
                refreshing: false,

                initHeartbeat() {
                    // Check every 7 seconds for new applications
                    this.pollInterval = setInterval(() => {
                        this.checkRealtime();
                    }, 7000);

                    window.addEventListener('beforeunload', () => {
                        if (this.pollInterval) clearInterval(this.pollInterval);
                    });
                },

                handleVisibilityChange() {
                    if (document.visibilityState === 'visible') {
                        this.checkRealtime(true);
                    }
                },

                async checkRealtime(isImmediate = false) {
                    if (this.isPolling) return;
                    this.isPolling = true;

                    try {
                        const response = await fetch(`${this.checkUrl}?latest_id=${this.latestId}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) return;

                        const data = await response.json();
                        if (!data.success) return;

                        if (data.counts) {
                            this.counts = Object.assign({}, this.counts, data.counts);
                        }

                        if (data.has_new && Array.isArray(data.new_applications) && data.new_applications.length > 0) {
                            this.hasNewIncoming = true;
                            this.incomingCount += data.new_count;
                            this.latestId = Math.max(this.latestId, data.latest_id);

                            if (this.activeTab !== 'pending') {
                                this.showPendingIndicator = true;
                                this.newBadgeCount = data.new_badge_count;
                            }

                            data.new_applications.forEach(app => {
                                this.addToast(app);
                            });
                        } else if (!this.hasNewIncoming) {
                            if (this.activeTab !== 'pending') {
                                this.showPendingIndicator = data.has_unseen_pending;
                                this.newBadgeCount = data.new_badge_count;
                            }
                        }
                    } catch (err) {
                        // Network error: suppress silently to prevent disruption
                    } finally {
                        this.isPolling = false;
                    }
                },

                addToast(app) {
                    if (this.toasts.some(t => t.id === app.id)) return;

                    const toastItem = {
                        id: app.id,
                        applicant_name: app.applicant_name,
                        pet_name: app.pet_name,
                        created_at_human: app.created_at_human || 'Just now'
                    };

                    if (this.toasts.length >= 3) {
                        this.toasts.shift();
                    }

                    this.toasts.push(toastItem);

                    setTimeout(() => {
                        this.removeToast(app.id);
                    }, 8000);
                },

                removeToast(id) {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                },

                onPendingTabClick() {
                    this.showPendingIndicator = false;
                    try {
                        fetch(this.markViewedUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            keepalive: true
                        });
                    } catch (e) {}
                },

                refreshList() {
                    this.refreshing = true;
                    window.location.reload();
                }
            };
        }
    </script>

    <div class="w-full py-6 sm:py-8 px-4 sm:px-6 lg:px-8 animate-fade-in space-y-6"
         x-data="adoptionRealtime({
             latestId: {{ (int) $latestId }},
             activeTab: '{{ $activeTab }}',
             checkUrl: '{{ route('adoption-applications.realtime-check') }}',
             markViewedUrl: '{{ route('adoption-applications.mark-viewed') }}',
             csrfToken: '{{ csrf_token() }}',
             initialCounts: {{ json_encode($counts) }},
             initialHasUnseen: {{ $hasUnseenPending ? 'true' : 'false' }},
             initialNewBadgeCount: {{ (int) ($newBadgeCount ?? 0) }}
         })"
         x-init="initHeartbeat()"
         @visibilitychange.window="handleVisibilityChange()">

        {{-- Floating Real-time Notification Toasts --}}
        <div class="fixed top-20 right-4 sm:right-6 z-50 flex flex-col gap-3 max-w-sm w-full pointer-events-none"
             aria-live="polite">
            <template x-for="toast in toasts" :key="toast.id">
                <div x-transition:enter="transform ease-out duration-300 transition"
                     x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
                     x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="pointer-events-auto bg-white/95 dark:bg-[#12141C]/95 backdrop-blur-md rounded-2xl p-4 shadow-xl border border-[#199CA4]/30 dark:border-[#41C1CB]/30 text-slate-800 dark:text-white space-y-2.5 transition">
                    
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2.5 w-2.5 shrink-0">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                            </span>
                            <span class="text-xs font-extrabold uppercase tracking-wider text-[#199CA4] dark:text-[#41C1CB]">New Application</span>
                            <span class="text-[10px] text-slate-400 font-medium" x-text="toast.created_at_human"></span>
                        </div>
                        <button @click="removeToast(toast.id)" 
                                type="button" 
                                class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition p-0.5 rounded-lg cursor-pointer"
                                title="Dismiss notification">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="text-xs text-slate-600 dark:text-slate-300">
                        <span class="font-extrabold text-slate-900 dark:text-white" x-text="toast.applicant_name"></span>
                        applied for
                        <span class="font-bold text-[#199CA4] dark:text-[#41C1CB]" x-text="toast.pet_name"></span>.
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <a :href="'/adoption-applications/' + toast.id" 
                           class="flex-1 py-1.5 px-3 rounded-xl bg-[#199CA4] hover:bg-[#147a80] text-white text-center text-xs font-bold transition shadow-2xs">
                            View Application
                        </a>
                        <button @click="refreshList()" 
                                type="button" 
                                class="py-1.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.08] dark:hover:bg-white/[0.12] text-slate-700 dark:text-slate-200 text-xs font-bold transition cursor-pointer">
                            Refresh List
                        </button>
                    </div>
                </div>
            </template>
        </div>
        
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm font-semibold flex items-center justify-between">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Page Header & Search Bar --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Adoption Requests</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Review incoming applications, schedule meet-and-greets, and manage finalized adoptions.</p>
            </div>

            {{-- Quick Search Form --}}
            <form method="GET" action="{{ route('adoption-applications.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="status" value="{{ $activeTab }}">
                <div class="relative w-full sm:w-72">
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}" 
                           placeholder="Search applicant, pet, phone..." 
                           class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C] text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-[#199CA4] focus:border-transparent transition shadow-2xs">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button type="submit" class="px-3.5 py-2.5 rounded-xl bg-[#199CA4] hover:bg-[#147a80] text-white text-xs font-bold transition shadow-2xs shrink-0 cursor-pointer">
                    Search
                </button>
                @if(!empty($search))
                    <a href="{{ route('adoption-applications.index', ['status' => $activeTab]) }}" class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.08] dark:hover:bg-white/[0.12] text-slate-600 dark:text-slate-300 text-xs font-bold transition shrink-0" title="Clear Search">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- Status Filter Tabs Bar --}}
        <div class="flex items-center gap-2 overflow-x-auto pt-3.5 pb-1 scrollbar-none">
            {{-- Tab 1: All Requests --}}
            <a href="{{ route('adoption-applications.index', ['status' => 'all', 'search' => $search]) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 border
               {{ $activeTab === 'all' 
                   ? 'bg-[#199CA4] text-white border-[#199CA4] shadow-xs' 
                   : 'bg-white dark:bg-[#12141C] text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#181A24] border-slate-200/80 dark:border-white/[0.07]' }}">
                <span>All Requests</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $activeTab === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-white/[0.08] text-slate-600 dark:text-slate-300' }}"
                      x-text="counts.all">
                    {{ $counts['all'] }}
                </span>
            </a>

            {{-- Tab 2: Pending (Pending & Under Review) --}}
            <a href="{{ route('adoption-applications.index', ['status' => 'pending', 'search' => $search]) }}" 
               @click="onPendingTabClick()"
               class="relative inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 border
               {{ $activeTab === 'pending' 
                   ? 'bg-[#199CA4] text-white border-[#199CA4] shadow-xs' 
                   : 'bg-white dark:bg-[#12141C] text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#181A24] border-slate-200/80 dark:border-white/[0.07]' }}">
                
                {{-- Floating Small Tag at Top of Pending Tab --}}
                <span x-show="showPendingIndicator && newBadgeCount > 0" 
                      x-cloak
                      class="absolute -top-3 left-1/2 -translate-x-1/2 px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-rose-500 text-white shadow-2xs leading-none whitespace-nowrap border border-white dark:border-[#090A0F] pointer-events-none">
                    <span class="hidden sm:inline" x-text="newBadgeCount + (newBadgeCount === 1 ? ' new request' : ' new requests')">
                        {{ $newBadgeCount }} new {{ \Illuminate\Support\Str::plural('request', $newBadgeCount) }}
                    </span>
                    <span class="sm:hidden" x-text="newBadgeCount + ' new'">
                        {{ $newBadgeCount }} new
                    </span>
                </span>

                {{-- Red dot indicator --}}
                <span x-show="showPendingIndicator" x-cloak class="relative flex h-2 w-2 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                </span>

                <span>Pending</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold"
                      :class="activeTab === 'pending' ? 'bg-white/20 text-white' : (showPendingIndicator ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/60 dark:border-rose-900/60' : 'bg-slate-100 dark:bg-white/[0.08] text-slate-600 dark:text-slate-300')"
                      x-text="counts.pending">
                    {{ $counts['pending'] }}
                </span>
            </a>

            {{-- Tab 3: Scheduled / Approved --}}
            <a href="{{ route('adoption-applications.index', ['status' => 'scheduled', 'search' => $search]) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 border
               {{ $activeTab === 'scheduled' 
                   ? 'bg-[#199CA4] text-white border-[#199CA4] shadow-xs' 
                   : 'bg-white dark:bg-[#12141C] text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#181A24] border-slate-200/80 dark:border-white/[0.07]' }}">
                <span>Scheduled</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $activeTab === 'scheduled' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-white/[0.08] text-slate-600 dark:text-slate-300' }}"
                      x-text="counts.scheduled">
                    {{ $counts['scheduled'] }}
                </span>
            </a>

            {{-- Tab 4: Finalized / Adopted --}}
            <a href="{{ route('adoption-applications.index', ['status' => 'finalized', 'search' => $search]) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 border
               {{ $activeTab === 'finalized' 
                   ? 'bg-[#199CA4] text-white border-[#199CA4] shadow-xs' 
                   : 'bg-white dark:bg-[#12141C] text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#181A24] border-slate-200/80 dark:border-white/[0.07]' }}">
                <span>Finalized</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $activeTab === 'finalized' ? 'bg-white/20 text-white' : 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300' }}"
                      x-text="counts.finalized">
                    {{ $counts['finalized'] }}
                </span>
            </a>

            {{-- Tab 5: Archived / Rejected --}}
            <a href="{{ route('adoption-applications.index', ['status' => 'rejected', 'search' => $search]) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 border
               {{ $activeTab === 'rejected' 
                   ? 'bg-[#199CA4] text-white border-[#199CA4] shadow-xs' 
                   : 'bg-white dark:bg-[#12141C] text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#181A24] border-slate-200/80 dark:border-white/[0.07]' }}">
                <span>Archived / Rejected</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $activeTab === 'rejected' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-white/[0.08] text-slate-600 dark:text-slate-300' }}"
                      x-text="counts.rejected">
                    {{ $counts['rejected'] }}
                </span>
            </a>
        </div>

        {{-- Real-time New Requests Banner --}}
        <div x-show="hasNewIncoming" 
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="p-4 rounded-2xl bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800/60 flex items-center justify-between gap-4 shadow-xs">
            <div class="flex items-center gap-3">
                <span class="relative flex h-3 w-3 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#199CA4] opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-[#199CA4]"></span>
                </span>
                <div>
                    <p class="text-xs sm:text-sm font-extrabold text-teal-950 dark:text-teal-200">
                        <span x-text="incomingCount"></span> new adoption <span x-text="incomingCount === 1 ? 'request' : 'requests'"></span> received.
                    </p>
                    <p class="text-[11px] text-teal-700 dark:text-teal-300">
                        New applications have been submitted. Click refresh to view the latest records.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button @click="refreshList()" 
                        type="button" 
                        class="px-3.5 py-1.5 rounded-xl bg-[#199CA4] hover:bg-[#147a80] text-white text-xs font-extrabold transition shadow-2xs cursor-pointer flex items-center gap-1.5 shrink-0">
                    <svg class="w-3.5 h-3.5" :class="refreshing ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Refresh Table</span>
                </button>
                <button @click="hasNewIncoming = false" 
                        type="button" 
                        class="p-1.5 rounded-xl hover:bg-teal-100 dark:hover:bg-teal-900/40 text-teal-600 dark:text-teal-400 transition cursor-pointer shrink-0" 
                        title="Dismiss notification">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Main Table Container --}}
        <div class="bg-white dark:bg-[#12141C] rounded-2xl sm:rounded-3xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between bg-slate-50/50 dark:bg-[#171923]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20 dark:border-[#41C1CB]/30 flex items-center justify-center font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white">
                            @if($activeTab === 'pending') Pending Applications
                            @elseif($activeTab === 'scheduled') Scheduled Screening Applications
                            @elseif($activeTab === 'finalized') Finalized Adoption Records
                            @elseif($activeTab === 'rejected') Archived & Declined Applications
                            @else All Adoption Applications
                            @endif
                        </h2>
                        @if(!empty($search))
                            <p class="text-[11px] text-slate-400">Filtering results matching "{{ $search }}"</p>
                        @endif
                    </div>
                </div>

                {{-- Live connection & refresh action --}}
                <div class="flex items-center gap-2">
                    <button @click="refreshList()" 
                            type="button" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200/80 dark:border-white/[0.08] bg-white dark:bg-[#12141C] text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#181A24] text-xs font-bold transition shadow-2xs cursor-pointer"
                            title="Refresh table content">
                        <svg class="w-3.5 h-3.5 text-slate-400" :class="refreshing ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span class="hidden sm:inline">Refresh</span>
                    </button>
                    <div class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 dark:bg-white/[0.06] text-slate-500 dark:text-slate-400 text-[10px] font-semibold border border-slate-200/60 dark:border-white/[0.06]"
                         title="Polling real-time updates every 7s">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Live Sync</span>
                    </div>
                </div>
            </div>

            {{-- Mobile Card Feed (<md) --}}
            <div class="block md:hidden divide-y divide-slate-100 dark:divide-white/[0.06]">
                @forelse($applications as $application)
                    @php
                        $pet = $application->pet;
                        $petImg = $pet && $pet->photo_path ? (str_starts_with($pet->photo_path, 'http') ? $pet->photo_path : asset('storage/' . ltrim($pet->photo_path, '/'))) : null;
                    @endphp
                    <div class="p-4 space-y-3.5">
                        {{-- Top row: Pet info + status --}}
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                @if($petImg)
                                    <div class="w-12 h-12 rounded-2xl overflow-hidden shrink-0 border border-slate-200 dark:border-white/[0.08] shadow-2xs relative bg-slate-100 dark:bg-[#171923]">
                                        <img src="{{ $petImg }}" alt="{{ $pet->name ?? 'Pet' }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="hidden w-full h-full bg-slate-100 dark:bg-white/[0.06] text-slate-500 dark:text-slate-300 items-center justify-center font-bold text-sm">
                                            <svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                        </div>
                                    </div>
                                @else
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-white/[0.06] text-slate-400 border border-slate-200 dark:border-white/[0.08] flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-extrabold text-slate-900 dark:text-white text-sm truncate">
                                            {{ $pet && !empty($pet->name) ? $pet->name : 'Pet no. ' . $application->pet_id }}
                                        </span>
                                        @if($application->competing_active_count > 1)
                                            @if($application->is_waitlisted_backup)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 shrink-0">
                                                    Backup #{{ $application->queue_position }}
                                                </span>
                                            @elseif($application->status === 'approved')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shrink-0">
                                                    Primary
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shrink-0">
                                                    {{ $application->competing_active_count }} Queue
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 truncate">
                                        Pet no. {{ $application->pet_id }} · {{ $pet->breed ?? 'Mixed Breed' }}
                                    </div>
                                </div>
                            </div>
                            @if($application->status === 'rejected')
                                @if($application->is_archived_due_to_adoption)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold border shrink-0 bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700" title="Closed because pet was adopted by another applicant">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500"></span>
                                        Archived
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold border shrink-0 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Rejected
                                    </span>
                                @endif
                            @elseif($application->is_waitlisted_backup)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold border shrink-0 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60" title="Pet scheduled with {{ $application->scheduled_competing_application->applicant_name ?? 'another applicant' }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                    Waitlisted (Backup)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold border shrink-0
                                    {{ $application->status === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60' : '' }}
                                    {{ $application->status === 'under_review' ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60' : '' }}
                                    {{ $application->status === 'pending' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60' : '' }}">
                                    <span class="w-1.5 h-1.5 rounded-full
                                        {{ $application->status === 'approved' ? 'bg-emerald-500' : '' }}
                                        {{ $application->status === 'under_review' ? 'bg-indigo-500' : '' }}
                                        {{ $application->status === 'pending' ? 'bg-amber-500' : '' }}"></span>
                                    {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                </span>
                            @endif
                        </div>

                        {{-- Middle applicant details card --}}
                        <div class="bg-slate-50/80 dark:bg-[#171923] p-3 rounded-xl border border-slate-100 dark:border-white/[0.04] space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400 dark:text-slate-500 font-medium">Applicant</span>
                                <span class="font-extrabold text-slate-900 dark:text-white">{{ $application->applicant_name }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400 dark:text-slate-500 font-medium">Origin</span>
                                @if($application->application_source === 'recommendation')
                                    <span class="font-extrabold text-emerald-600 dark:text-emerald-400 inline-flex items-center gap-1">
                                        AI Match {{ $application->compatibility_score !== null ? '(' . number_format($application->compatibility_score, 0) . '%)' : '' }}
                                    </span>
                                @else
                                    <span class="font-semibold text-slate-600 dark:text-slate-400">Manual Catalog</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400 dark:text-slate-500 font-medium">Contact Phone</span>
                                <a href="tel:{{ $application->applicant_phone }}" class="font-mono font-semibold text-[#199CA4] hover:underline">{{ $application->applicant_phone }}</a>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400 dark:text-slate-500 font-medium">Submitted</span>
                                <span class="text-slate-600 dark:text-slate-400 font-medium">{{ $application->created_at ? $application->created_at->format('M d, Y · h:i A') : '—' }}</span>
                            </div>
                        </div>

                        {{-- Action buttons --}}
                        <div class="flex items-center gap-2 pt-1">
                            @if(in_array($application->status, ['approved', 'adopted']))
                                @if($application->signature_path)
                                    <a href="{{ route('adoption-applications.contract', $application) }}" target="_blank" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-[#1D1F2C] font-bold transition shadow-2xs text-xs shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                        <span>Print</span>
                                    </a>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1.5 rounded-xl text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 shrink-0">
                                        Unsigned
                                    </span>
                                @endif
                            @endif
                            <a href="{{ route('adoption-applications.show', $application) }}" class="flex-1 inline-flex items-center justify-center px-4 py-2 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white font-extrabold transition shadow-2xs text-xs">
                                View Details
                            </a>
                            <form action="{{ route('adoption-applications.destroy', $application) }}" method="POST" onsubmit="return confirm('Permanently delete this adoption application from {{ $application->applicant_name }}?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-rose-50 hover:border-rose-200 text-slate-400 hover:text-rose-600 dark:hover:bg-rose-950/30 transition cursor-pointer" title="Delete application">
                                    <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center text-sm text-slate-400 dark:text-slate-500">
                        <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        No adoption applications found for this filter.
                    </div>
                @endforelse
            </div>

            {{-- Desktop Data Table (>=md) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-[#171923] border-b border-slate-200/80 dark:border-white/[0.06]">
                        <tr>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Pet</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Applicant</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Origin / Match</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Submitted Date</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                        @forelse($applications as $application)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-[#181A24] text-xs sm:text-sm transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-slate-900 dark:text-white font-extrabold">
                                    <div class="flex items-center gap-3">
                                        @php
                                            $pet = $application->pet;
                                            $petImg = $pet && $pet->photo_path ? (str_starts_with($pet->photo_path, 'http') ? $pet->photo_path : asset('storage/' . ltrim($pet->photo_path, '/'))) : null;
                                        @endphp
                                        @if($petImg)
                                            <div class="w-10 h-10 rounded-xl overflow-hidden shrink-0 border border-slate-200 dark:border-white/[0.08] shadow-2xs relative bg-slate-100 dark:bg-[#171923]">
                                                <img src="{{ $petImg }}" alt="{{ $pet->name ?? 'Pet' }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="hidden w-full h-full bg-slate-100 dark:bg-white/[0.06] text-slate-500 dark:text-slate-300 items-center justify-center font-bold text-sm">
                                                    <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                                </div>
                                            </div>
                                        @else
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/[0.06] text-slate-400 border border-slate-200 dark:border-white/[0.08] flex items-center justify-center shrink-0">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-extrabold text-slate-900 dark:text-white">
                                                    {{ $pet && !empty($pet->name) ? $pet->name : 'Pet no. ' . $application->pet_id }}
                                                </span>
                                                @if($application->competing_active_count > 1)
                                                    @if($application->is_waitlisted_backup)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-extrabold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800" title="Screening currently scheduled with {{ $application->scheduled_competing_application->applicant_name ?? 'another applicant' }}">
                                                            Backup · Queue #{{ $application->queue_position }}
                                                        </span>
                                                    @elseif($application->status === 'approved')
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                            Primary Candidate
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-extrabold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800" title="{{ $application->competing_active_count }} applicants competing for this pet">
                                                            {{ $application->competing_active_count }} in Queue · {{ $application->is_lead_candidate ? 'Lead' : '#' . $application->queue_position }}
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                            <div class="text-[11px] font-semibold text-slate-400 dark:text-slate-500">
                                                Pet no. {{ $application->pet_id }} · {{ $pet->breed ?? 'Mixed Breed' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-700 dark:text-slate-300">
                                    <div class="font-extrabold text-slate-900 dark:text-white">{{ $application->applicant_name }}</div>
                                    <div class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 font-medium">{{ $application->applicant_phone }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($application->application_source === 'recommendation')
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                AI Match
                                            </span>
                                            @if($application->compatibility_score !== null)
                                                <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 font-mono">
                                                    {{ number_format($application->compatibility_score, 0) }}%
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-white/[0.08]">
                                            Manual Catalog
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        @if($application->status === 'rejected')
                                            @if($application->is_archived_due_to_adoption)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold border w-fit bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700" title="Closed because pet was adopted by another applicant">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500"></span>
                                                    Archived
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold border w-fit bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                    Rejected
                                                </span>
                                            @endif
                                        @elseif($application->is_waitlisted_backup)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold border w-fit bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60" title="Screening scheduled with {{ $application->scheduled_competing_application->applicant_name ?? 'another applicant' }}">
                                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                                Waitlisted (Backup)
                                            </span>
                                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">On standby for {{ $application->scheduled_competing_application->applicant_name ?? 'primary applicant' }}</span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold border w-fit
                                                {{ $application->status === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60' : '' }}
                                                {{ $application->status === 'under_review' ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60' : '' }}
                                                {{ $application->status === 'pending' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60' : '' }}">
                                                <span class="w-1.5 h-1.5 rounded-full
                                                    {{ $application->status === 'approved' ? 'bg-emerald-500' : '' }}
                                                    {{ $application->status === 'under_review' ? 'bg-indigo-500' : '' }}
                                                    {{ $application->status === 'pending' ? 'bg-amber-500' : '' }}"></span>
                                                {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-500 dark:text-slate-400 font-medium">
                                    {{ $application->created_at ? $application->created_at->format('M d, Y · h:i A') : '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right space-x-1.5">
                                    @if(in_array($application->status, ['approved', 'adopted']))
                                        @if($application->signature_path)
                                            <a href="{{ route('adoption-applications.contract', $application) }}" target="_blank" title="Print Contract" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-[#1D1F2C] font-bold transition shadow-2xs text-xs">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                </svg>
                                                <span>Print</span>
                                            </a>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60" title="Awaiting adopter signature">
                                                Unsigned
                                            </span>
                                        @endif
                                    @endif
                                    <a href="{{ route('adoption-applications.show', $application) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white font-extrabold transition-all duration-200 shadow-2xs text-xs">View Details</a>
                                    
                                    {{-- Delete Application Button --}}
                                    <form action="{{ route('adoption-applications.destroy', $application) }}" method="POST" onsubmit="return confirm('Permanently delete this adoption application from {{ $application->applicant_name }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-rose-50 hover:border-rose-200 text-slate-400 hover:text-rose-600 dark:hover:bg-rose-950/30 transition cursor-pointer" title="Delete application">
                                            <svg class="w-3.5 h-3.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-400 dark:text-slate-500">
                                    <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    No adoption applications found for this filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 dark:border-white/[0.06] bg-slate-50/50 dark:bg-[#171923]">
                {{ $applications->links() }}
            </div>
        </div>
    </div>
</x-app-layout>