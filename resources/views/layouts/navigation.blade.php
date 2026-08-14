<nav x-data="{ mobileNavOpen: false }" class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-2xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            
            {{-- Left area --}}
            <div class="flex items-center">
            </div>
                
            {{-- Right Header Actions --}}
            <div class="hidden sm:flex sm:items-center sm:gap-3 sm:ms-6">
                
                {{-- Grouped Adopter Notification Bell Component --}}
                @php
                    $notifData = $adminNotificationData ?? [
                        'notifications' => [],
                        'unread_count' => 0,
                        'counts' => ['all' => 0, 'checkins' => 0, 'overdue' => 0, 'requests' => 0]
                    ];
                @endphp
                <div class="relative" 
                    x-data="{
                        openNotif: false,
                        activeTab: 'all',
                        unreadCount: {{ (int)($notifData['unread_count'] ?? 0) }},
                        notifications: @js($notifData['notifications'] ?? []),
                        counts: @js($notifData['counts'] ?? ['all' => 0, 'checkins' => 0, 'overdue' => 0, 'requests' => 0]),
                        get list() {
                            if (this.activeTab === 'checkin') {
                                return this.notifications.filter(n => n.category === 'checkin');
                            } else if (this.activeTab === 'overdue') {
                                return this.notifications.filter(n => n.category === 'overdue');
                            } else if (this.activeTab === 'request') {
                                return this.notifications.filter(n => n.category === 'request');
                            }
                            return this.notifications;
                        },
                        async refresh() {
                            try {
                                const res = await fetch('{{ route("admin.notifications.index") }}', {
                                    headers: { 'Accept': 'application/json' }
                                });
                                if (res.ok) {
                                    const d = await res.json();
                                    this.notifications = d.notifications || [];
                                    this.unreadCount = d.unread_count || 0;
                                    this.counts = d.counts || this.counts;
                                }
                            } catch (e) {}
                        },
                        async markAll() {
                            this.unreadCount = 0;
                            this.notifications = this.notifications.map(n => ({ ...n, is_read: true }));
                            try {
                                await fetch('{{ route("admin.notifications.markAllRead") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                });
                            } catch (e) {}
                        },
                        async markItem(id) {
                            try {
                                fetch('{{ route("admin.notifications.markRead") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ id })
                                });
                            } catch (e) {}
                        }
                    }" 
                    x-init="setInterval(() => refresh(), 5000)"
                    @click.outside="openNotif = false">
                    
                    {{-- Bell Trigger Button --}}
                    <button type="button" 
                        @click.stop="openNotif = !openNotif"
                        class="relative p-2.5 rounded-xl text-gray-500 hover:text-[#199CA4] hover:bg-[#199CA4]/10 focus:outline-none transition cursor-pointer"
                        title="Notifications">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>

                        {{-- Unread Badge --}}
                        <template x-if="unreadCount > 0">
                            <span class="absolute top-1.5 right-1.5 flex h-4 min-w-[16px] px-1 items-center justify-center rounded-full bg-rose-500 text-white text-[10px] font-extrabold shadow-sm animate-pulse">
                                <span x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
                            </span>
                        </template>
                    </button>

                    {{-- Dropdown Modal / Popover --}}
                    <div x-show="openNotif" x-cloak style="display: none;" 
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                        class="absolute right-0 mt-2 w-[420px] max-w-[92vw] bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden z-50">
                        
                        {{-- Dropdown Header --}}
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-extrabold text-gray-900">Notifications</h3>
                                <template x-if="unreadCount > 0">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700" x-text="unreadCount + ' new'"></span>
                                </template>
                            </div>
                            <button type="button" 
                                @click="markAll()"
                                class="text-xs font-bold text-[#199CA4] hover:underline cursor-pointer">
                                Mark all as read
                            </button>
                        </div>

                        {{-- Filter Tabs --}}
                        <div class="px-3 py-2 border-b border-gray-100 flex items-center gap-1 overflow-x-auto scrollbar-none bg-white">
                            <button type="button" 
                                @click="activeTab = 'all'"
                                :class="activeTab === 'all' ? 'bg-[#199CA4] text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'"
                                class="px-3 py-1 rounded-lg text-xs font-bold transition whitespace-nowrap cursor-pointer">
                                All (<span x-text="counts.all"></span>)
                            </button>
                            <button type="button" 
                                @click="activeTab = 'checkin'"
                                :class="activeTab === 'checkin' ? 'bg-[#199CA4] text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'"
                                class="px-3 py-1 rounded-lg text-xs font-bold transition whitespace-nowrap cursor-pointer">
                                Check-ins (<span x-text="counts.checkins"></span>)
                            </button>
                            <button type="button" 
                                @click="activeTab = 'overdue'"
                                :class="activeTab === 'overdue' ? 'bg-rose-600 text-white shadow-xs' : 'text-rose-600 hover:bg-rose-50'"
                                class="px-3 py-1 rounded-lg text-xs font-bold transition whitespace-nowrap cursor-pointer">
                                Overdue (<span x-text="counts.overdue"></span>)
                            </button>
                            <button type="button" 
                                @click="activeTab = 'request'"
                                :class="activeTab === 'request' ? 'bg-[#199CA4] text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'"
                                class="px-3 py-1 rounded-lg text-xs font-bold transition whitespace-nowrap cursor-pointer">
                                Requests (<span x-text="counts.requests"></span>)
                            </button>
                        </div>

                        {{-- Notifications List --}}
                        <div class="max-h-[420px] overflow-y-auto divide-y divide-gray-100">
                            
                            {{-- Empty State --}}
                            <template x-if="list.length === 0">
                                <div class="py-12 px-4 text-center">
                                    <div class="w-10 h-10 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-2 font-bold">
                                        🔔
                                    </div>
                                    <p class="text-xs font-bold text-gray-700">No notifications in this tab</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">You're all caught up with monthly check-ins and adoption requests.</p>
                                </div>
                            </template>

                            {{-- Grouped Adopter Notification Item Cards --}}
                            <template x-for="item in list" :key="item.id">
                                <a :href="item.url" 
                                    @click="markItem(item.id)"
                                    :class="item.is_read ? 'bg-white hover:bg-gray-50/90' : 'bg-teal-50/40 hover:bg-teal-50/80'"
                                    class="p-4 flex gap-3.5 transition block cursor-pointer group">
                                    
                                    {{-- Adopter Avatar / Initials --}}
                                    <div class="flex-shrink-0 pt-0.5">
                                        <template x-if="item.avatar">
                                            <img :src="item.avatar" alt="Adopter" class="w-11 h-11 rounded-2xl object-cover border border-gray-200 shadow-xs">
                                        </template>
                                        <template x-if="!item.avatar">
                                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#EAF5F6] to-[#d3eef1] text-[#199CA4] flex items-center justify-center font-extrabold text-xs border border-[#199CA4]/20 shadow-xs"
                                                x-text="item.initials">
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Grouped Details --}}
                                    <div class="min-w-0 flex-1 space-y-2">
                                        
                                        {{-- Adopter Header Line --}}
                                        <div class="flex items-center justify-between gap-1">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <span class="text-xs sm:text-sm font-extrabold text-gray-900 group-hover:text-[#199CA4] transition truncate" x-text="item.adopter_name"></span>
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold font-mono bg-gray-100 text-gray-600 border border-gray-200" x-text="item.adopter_id_code"></span>
                                            </div>
                                            <span class="text-[10px] font-semibold text-gray-400 whitespace-nowrap" x-text="item.time"></span>
                                        </div>

                                        {{-- Title & Pet Count Pill --}}
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-xs font-semibold text-gray-600 truncate" x-text="item.title"></span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#199CA4]/10 text-[#199CA4] border border-[#199CA4]/20 flex-shrink-0">
                                                <span x-text="item.pets_count + (item.pets_count === 1 ? ' Pet Adopted' : ' Pets Adopted')"></span>
                                            </span>
                                        </div>

                                        {{-- Multi-Pet Breakdown Chips --}}
                                        <div class="space-y-1.5 pt-1">
                                            <template x-for="(pet, idx) in item.pet_details" :key="idx">
                                                <div class="flex items-center justify-between gap-2 p-2 rounded-xl bg-gray-50/80 border border-gray-100 group-hover:bg-white group-hover:border-gray-200 transition">
                                                    <div class="flex items-center gap-1.5 min-w-0">
                                                        <span class="text-xs font-bold text-gray-900 truncate" x-text="'🐾 ' + pet.pet_name"></span>
                                                    </div>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-extrabold"
                                                        :class="{
                                                            'bg-emerald-100 text-emerald-800 border border-emerald-200': pet.status_type === 'submitted',
                                                            'bg-rose-100 text-rose-800 border border-rose-200': pet.status_type === 'overdue',
                                                            'bg-amber-100 text-amber-800 border border-amber-200': pet.status_type === 'due_soon',
                                                            'bg-sky-100 text-sky-800 border border-sky-200': pet.status_type === 'pending_request',
                                                            'bg-gray-100 text-gray-600 border border-gray-200': pet.status_type === 'pending_first'
                                                        }"
                                                        x-text="pet.status_label">
                                                    </span>
                                                </div>
                                            </template>
                                        </div>

                                        {{-- Click Action Hint --}}
                                        <div class="pt-0.5 text-right">
                                            <span class="text-[10px] font-bold text-[#199CA4] group-hover:underline">
                                                View all adopted pets in directory →
                                            </span>
                                        </div>

                                    </div>

                                </a>
                            </template>

                        </div>

                        {{-- Dropdown Footer --}}
                        <div class="p-3 border-t border-gray-100 bg-gray-50/50 text-center">
                            <a href="{{ route('adopters.index') }}" class="text-xs font-bold text-[#199CA4] hover:underline">
                                Open Full Adopter Profiles Directory →
                            </a>
                        </div>

                    </div>

                </div>

                {{-- User Profile Dropdown --}}
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2.5 px-3 py-1.5 border border-gray-200 rounded-xl text-xs sm:text-sm font-bold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition shadow-2xs cursor-pointer">
                            @if(Auth::user()->avatar_url)
                                <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-8 h-8 rounded-xl object-cover border border-gray-200 shadow-xs">
                            @else
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#EAF5F6] to-[#d3eef1] text-[#199CA4] flex items-center justify-center font-extrabold text-xs border border-[#199CA4]/20 shadow-xs">
                                    {{ Auth::user()->initials }}
                                </div>
                            @endif
                            <div class="text-left">
                                <span class="block text-xs font-bold text-gray-900 leading-tight">{{ Auth::user()->name }}</span>
                                <span class="block text-[10px] text-gray-400 font-medium capitalize">{{ Auth::user()->role }}</span>
                            </div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- Mobile hamburger --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="mobileNavOpen = ! mobileNavOpen" class="inline-flex items-center justify-center p-2 rounded-xl text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': mobileNavOpen, 'inline-flex': ! mobileNavOpen }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! mobileNavOpen, 'inline-flex': mobileNavOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Responsive Menu --}}
    <div :class="{'block': mobileNavOpen, 'hidden': ! mobileNavOpen}" class="hidden sm:hidden bg-white border-t border-gray-100">
        <div class="pt-4 pb-2 px-4 space-y-2">
            <div class="font-bold text-sm text-gray-900">{{ Auth::user()->name }}</div>
            <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>

            <div class="pt-2 border-t border-gray-100 space-y-1">
                <a href="{{ route('adopters.index') }}" class="block py-2 text-xs font-bold text-[#199CA4]">
                    Adopters Directory
                </a>
                <a href="{{ route('profile.edit') }}" class="block py-2 text-xs font-medium text-gray-700">
                    {{ __('Profile') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left py-2 text-xs font-medium text-rose-600">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>