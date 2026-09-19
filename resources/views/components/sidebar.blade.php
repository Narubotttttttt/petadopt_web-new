@php
	$hasNewAdoptionRequests = $hasNewAdoptionRequests ?? (
		!request()->routeIs('adoption-applications.*') 
		&& \App\Services\AdminNotificationService::getUnviewedAdoptionRequestsCount() > 0
	);
@endphp
<div class="hidden lg:block fixed inset-y-0 left-0 z-40">
	<aside class="w-64 h-full border-r border-[#10565D]/40 dark:border-white/[0.06] bg-[#146970] dark:bg-[#0C0D13] text-teal-50 dark:text-slate-300 shadow-md flex flex-col select-none transition-colors duration-150">
		{{-- Brand Header (Exact h-16 to seamlessly connect with top navigation bar) --}}
		<div class="h-16 shrink-0 flex items-center px-4 border-b border-white/15 dark:border-white/[0.06]">
			<a href="{{ route('dashboard') }}" class="flex items-center gap-3 w-full group">
				<img src="{{ asset('images/caws-logo.png') }}" alt="CDO Animal Welfare Society Inc." class="w-10 h-10 shrink-0 rounded-full object-contain bg-white/15 dark:bg-white/[0.06] p-0.5 border border-white/20 dark:border-white/[0.12] shadow-xs group-hover:scale-105 transition-transform duration-150">
				<div class="whitespace-nowrap min-w-0">
					<h2 class="text-sm font-bold text-white leading-tight truncate group-hover:text-teal-100 transition-colors">CDO Animal Welfare</h2>
					<p class="text-xs text-teal-200/80 dark:text-slate-400 font-medium truncate">Society Inc.</p>
				</div>
			</a>
		</div>

		{{-- Navigation List (Scrollable Area) --}}
		<div class="flex-1 overflow-y-auto px-4 py-4 space-y-1">
			<nav class="space-y-1" aria-label="Main navigation" x-data="{ hasNewAdoption: {{ $hasNewAdoptionRequests ? 'true' : 'false' }} }">
				
				<p class="text-[10px] font-extrabold uppercase tracking-widest text-teal-200 dark:text-slate-500 px-3 pt-1 pb-1 flex items-center gap-1.5">
					<span class="w-1.5 h-1.5 rounded-full bg-teal-300 dark:bg-slate-600"></span>
					Overview
				</p>

				{{-- Dashboard --}}
				<a href="{{ route('dashboard') }}" title="Dashboard"
					class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 {{ request()->routeIs('dashboard') ? 'bg-white/20 dark:bg-white/[0.06] text-white shadow-xs backdrop-blur-xs' : 'text-teal-100/90 dark:text-slate-400 hover:text-white dark:hover:text-white hover:bg-white/10 dark:hover:bg-white/[0.03]' }}">
					@if(request()->routeIs('dashboard'))
						<span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-teal-200 dark:bg-[#199CA4]"></span>
					@endif
					<span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-150 {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white dark:bg-[#199CA4]/15 dark:text-[#41C1CB]' : 'bg-white/10 dark:bg-white/[0.04] text-teal-100/90 dark:text-slate-400 group-hover:bg-white/15 dark:group-hover:bg-white/[0.08] group-hover:text-white dark:group-hover:text-white' }}">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4v11a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path></svg>
					</span>
					<span class="whitespace-nowrap tracking-wide">Dashboard</span>
				</a>

				<p class="text-[10px] font-extrabold uppercase tracking-widest text-teal-200 dark:text-slate-500 px-3 pt-4 pb-1 flex items-center gap-1.5">
					<span class="w-1.5 h-1.5 rounded-full bg-teal-300 dark:bg-slate-600"></span>
					Management
				</p>

				{{-- Pets --}}
				<a href="{{ route('pets.index') }}" title="Pets"
					class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 {{ request()->routeIs('pets.*') ? 'bg-white/20 dark:bg-white/[0.06] text-white shadow-xs backdrop-blur-xs' : 'text-teal-100/90 dark:text-slate-400 hover:text-white dark:hover:text-white hover:bg-white/10 dark:hover:bg-white/[0.03]' }}">
					@if(request()->routeIs('pets.*'))
						<span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-teal-200 dark:bg-[#199CA4]"></span>
					@endif
					<span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-150 {{ request()->routeIs('pets.*') ? 'bg-white/20 text-white dark:bg-[#199CA4]/15 dark:text-[#41C1CB]' : 'bg-white/10 dark:bg-white/[0.04] text-teal-100/90 dark:text-slate-400 group-hover:bg-white/15 dark:group-hover:bg-white/[0.08] group-hover:text-white dark:group-hover:text-white' }}">
						<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
					</span>
					<span class="whitespace-nowrap tracking-wide">Pets</span>
				</a>

				{{-- Staff Management (Admin only) --}}
				@if(Auth::user()?->role === 'admin')
					<a href="{{ route('users.index') }}" title="Staff Management"
						class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 {{ request()->routeIs('users.*') ? 'bg-white/20 dark:bg-white/[0.06] text-white shadow-xs backdrop-blur-xs' : 'text-teal-100/90 dark:text-slate-400 hover:text-white dark:hover:text-white hover:bg-white/10 dark:hover:bg-white/[0.03]' }}">
						@if(request()->routeIs('users.*'))
							<span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-teal-200 dark:bg-[#199CA4]"></span>
						@endif
						<span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-150 {{ request()->routeIs('users.*') ? 'bg-white/20 text-white dark:bg-[#199CA4]/15 dark:text-[#41C1CB]' : 'bg-white/10 dark:bg-white/[0.04] text-teal-100/90 dark:text-slate-400 group-hover:bg-white/15 dark:group-hover:bg-white/[0.08] group-hover:text-white dark:group-hover:text-white' }}">
							<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 15c2.89 0 5.578.92 7.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0zm6 7v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a4 4 0 014-4h6a4 4 0 014 4z" /></svg>
						</span>
						<span class="whitespace-nowrap tracking-wide">Staff Management</span>
					</a>
				@endif

				{{-- Adoption Requests --}}
				<a href="{{ route('adoption-applications.index') }}" title="Adoption Requests"
					@click="hasNewAdoption = false; try { fetch('{{ route('adoption-applications.mark-viewed') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }) } catch(e){}"
					class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 {{ request()->routeIs('adoption-applications.*') ? 'bg-white/20 dark:bg-white/[0.06] text-white shadow-xs backdrop-blur-xs' : 'text-teal-100/90 dark:text-slate-400 hover:text-white dark:hover:text-white hover:bg-white/10 dark:hover:bg-white/[0.03]' }}">
					@if(request()->routeIs('adoption-applications.*'))
						<span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-teal-200 dark:bg-[#199CA4]"></span>
					@endif
					<span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-150 {{ request()->routeIs('adoption-applications.*') ? 'bg-white/20 text-white dark:bg-[#199CA4]/15 dark:text-[#41C1CB]' : 'bg-white/10 dark:bg-white/[0.04] text-teal-100/90 dark:text-slate-400 group-hover:bg-white/15 dark:group-hover:bg-white/[0.08] group-hover:text-white dark:group-hover:text-white' }}">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 6h18M3 14h18M3 18h18"></path></svg>
					</span>
					<span class="whitespace-nowrap tracking-wide">Adoption Requests</span>
					@if($hasNewAdoptionRequests)
						<span x-show="hasNewAdoption" class="ml-auto relative flex h-2.5 w-2.5 shrink-0" data-testid="new-adoption-request-indicator" title="New request awaiting review">
							<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
							<span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500 ring-2 ring-white/30 dark:ring-[#0C0D13]"></span>
						</span>
					@endif
				</a>

				{{-- Adopters --}}
				<a href="{{ route('adopters.index') }}" title="Adopters"
					class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 {{ request()->routeIs('adopters.*') ? 'bg-white/20 dark:bg-white/[0.06] text-white shadow-xs backdrop-blur-xs' : 'text-teal-100/90 dark:text-slate-400 hover:text-white dark:hover:text-white hover:bg-white/10 dark:hover:bg-white/[0.03]' }}">
					@if(request()->routeIs('adopters.*'))
						<span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-teal-200 dark:bg-[#199CA4]"></span>
					@endif
					<span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-150 {{ request()->routeIs('adopters.*') ? 'bg-white/20 text-white dark:bg-[#199CA4]/15 dark:text-[#41C1CB]' : 'bg-white/10 dark:bg-white/[0.04] text-teal-100/90 dark:text-slate-400 group-hover:bg-white/15 dark:group-hover:bg-white/[0.08] group-hover:text-white dark:group-hover:text-white' }}">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 10-8 0v4M5 11h14v10H5V11z"></path></svg>
					</span>
					<span class="whitespace-nowrap tracking-wide">Adopters</span>
				</a>

				<p class="text-[10px] font-extrabold uppercase tracking-widest text-teal-200 dark:text-slate-500 px-3 pt-4 pb-1 flex items-center gap-1.5">
					<span class="w-1.5 h-1.5 rounded-full bg-teal-300 dark:bg-slate-600"></span>
					Records
				</p>

				{{-- Medical Logs --}}
				<a href="{{ route('medical-logs.index') }}" title="Medical Logs"
					class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 {{ request()->routeIs('medical-logs.*') ? 'bg-white/20 dark:bg-white/[0.06] text-white shadow-xs backdrop-blur-xs' : 'text-teal-100/90 dark:text-slate-400 hover:text-white dark:hover:text-white hover:bg-white/10 dark:hover:bg-white/[0.03]' }}">
					@if(request()->routeIs('medical-logs.*'))
						<span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-teal-200 dark:bg-[#199CA4]"></span>
					@endif
					<span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-150 {{ request()->routeIs('medical-logs.*') ? 'bg-white/20 text-white dark:bg-[#199CA4]/15 dark:text-[#41C1CB]' : 'bg-white/10 dark:bg-white/[0.04] text-teal-100/90 dark:text-slate-400 group-hover:bg-white/15 dark:group-hover:bg-white/[0.08] group-hover:text-white dark:group-hover:text-white' }}">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
					</span>
					<span class="whitespace-nowrap tracking-wide">Medical Logs</span>
				</a>

				{{-- Pet History --}}
				<a href="{{ route('pet-history.index') }}" title="Pet History"
					class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 {{ request()->routeIs('pet-history.*') ? 'bg-white/20 dark:bg-white/[0.06] text-white shadow-xs backdrop-blur-xs' : 'text-teal-100/90 dark:text-slate-400 hover:text-white dark:hover:text-white hover:bg-white/10 dark:hover:bg-white/[0.03]' }}">
					@if(request()->routeIs('pet-history.*'))
						<span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-teal-200 dark:bg-[#199CA4]"></span>
					@endif
					<span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-150 {{ request()->routeIs('pet-history.*') ? 'bg-white/20 text-white dark:bg-[#199CA4]/15 dark:text-[#41C1CB]' : 'bg-white/10 dark:bg-white/[0.04] text-teal-100/90 dark:text-slate-400 group-hover:bg-white/15 dark:group-hover:bg-white/[0.08] group-hover:text-white dark:group-hover:text-white' }}">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
					</span>
					<span class="whitespace-nowrap tracking-wide">Pet History</span>
				</a>

				{{-- Reports --}}
				<a href="{{ route('reports.index') }}" title="Reports"
					class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 {{ request()->routeIs('reports.*') ? 'bg-white/20 dark:bg-white/[0.06] text-white shadow-xs backdrop-blur-xs' : 'text-teal-100/90 dark:text-slate-400 hover:text-white dark:hover:text-white hover:bg-white/10 dark:hover:bg-white/[0.03]' }}">
					@if(request()->routeIs('reports.*'))
						<span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-teal-200 dark:bg-[#199CA4]"></span>
					@endif
					<span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-150 {{ request()->routeIs('reports.*') ? 'bg-white/20 text-white dark:bg-[#199CA4]/15 dark:text-[#41C1CB]' : 'bg-white/10 dark:bg-white/[0.04] text-teal-100/90 dark:text-slate-400 group-hover:bg-white/15 dark:group-hover:bg-white/[0.08] group-hover:text-white dark:group-hover:text-white' }}">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
					</span>
					<span class="whitespace-nowrap tracking-wide">Reports</span>
				</a>
			</nav>
		</div>

		{{-- Sidebar Footer Card --}}
		<div class="shrink-0 p-4 border-t border-white/10 dark:border-white/[0.06] bg-black/10 dark:bg-[#0C0D13]">
			<div class="p-2.5 bg-white/10 dark:bg-[#12141C] rounded-2xl border border-white/10 dark:border-white/[0.06] flex items-center gap-3">
				<div class="min-w-0 flex-1 text-center">
					<p class="text-[12px] font-bold text-white tracking-wide truncate">CDO Animal Welfare</p>
					<p class="text-[11px] text-teal-200/80 dark:text-slate-400 font-medium tracking-wide">Pet Adoption Shelter</p>
				</div>
			</div>
		</div>
	</aside>
</div>

{{-- Mobile Off-Canvas Drawer Navigation (< lg screens) --}}
<div class="lg:hidden" x-cloak>
    {{-- Backdrop Overlay --}}
    <div 
        x-show="mobileNavOpen"
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="mobileNavOpen = false"
        class="fixed inset-0 bg-slate-950/70 dark:bg-black/80 backdrop-blur-xs z-50 transition-opacity"
        aria-hidden="true"
    ></div>

    {{-- Off-Canvas Sidebar Panel --}}
    <div 
        x-show="mobileNavOpen"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-50 w-72 sm:w-80 max-w-[86vw] bg-[#146970] dark:bg-[#0C0D13] text-teal-50 dark:text-slate-300 shadow-2xl flex flex-col border-r border-[#10565D]/40 dark:border-white/[0.08] select-none"
        role="dialog"
        aria-modal="true"
        aria-label="Mobile Navigation"
    >
        {{-- Mobile Drawer Header --}}
        <div class="h-16 shrink-0 flex items-center justify-between px-4 border-b border-white/15 dark:border-white/[0.06]">
            <a href="{{ route('dashboard') }}" @click="mobileNavOpen = false" class="flex items-center gap-3 min-w-0 group">
                <img src="{{ asset('images/caws-logo.png') }}" alt="CDO Animal Welfare Society Inc." class="w-9 h-9 shrink-0 rounded-full object-contain bg-white/15 dark:bg-white/[0.06] p-0.5 border border-white/20 dark:border-white/[0.12] shadow-xs group-hover:scale-105 transition-transform duration-150">
                <div class="whitespace-nowrap min-w-0">
                    <h2 class="text-sm font-bold text-white leading-tight truncate group-hover:text-teal-100 transition-colors">CDO Animal Welfare</h2>
                    <p class="text-[11px] text-teal-200/80 dark:text-slate-400 font-medium truncate">Society Inc.</p>
                </div>
            </a>
            <button 
                type="button" 
                @click="mobileNavOpen = false" 
                class="p-2 rounded-xl text-teal-100 hover:text-white hover:bg-white/10 dark:text-slate-400 dark:hover:text-white dark:hover:bg-white/[0.06] transition-colors focus:outline-none cursor-pointer shrink-0" 
                aria-label="Close navigation menu"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Mobile User Profile & Appearance Card --}}
        <div class="p-3.5 shrink-0">
            <div class="p-3 rounded-2xl bg-black/15 dark:bg-white/[0.04] border border-white/10 dark:border-white/[0.06] backdrop-blur-xs space-y-2.5">
                <div class="flex items-center gap-3 min-w-0">
                    @if(Auth::user()?->avatar_url)
                        <img src="{{ Auth::user()?->avatar_url }}" alt="{{ Auth::user()?->name }}" class="w-10 h-10 rounded-xl object-cover border border-white/20 dark:border-white/[0.12] shadow-xs shrink-0">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-white/20 dark:bg-white/[0.08] text-white flex items-center justify-center font-extrabold text-sm border border-white/20 dark:border-white/[0.12] shadow-xs shrink-0">
                            {{ Auth::user()?->initials }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="font-bold text-xs sm:text-sm text-white truncate">{{ Auth::user()?->name }}</span>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wide bg-teal-300/20 text-teal-200 border border-teal-200/30 dark:bg-[#199CA4]/20 dark:text-[#41C1CB] dark:border-[#199CA4]/30 shrink-0">
                                {{ Auth::user()?->role }}
                            </span>
                        </div>
                        <div class="text-[11px] text-teal-200/80 dark:text-slate-400 truncate">{{ Auth::user()?->email }}</div>
                    </div>
                </div>

                {{-- Mobile Theme Switcher Pill --}}
                <div class="pt-2 border-t border-white/10 dark:border-white/[0.06] flex items-center justify-between">
                    <span class="text-xs font-semibold text-teal-100/90 dark:text-slate-400 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                        Theme
                    </span>
                    <div x-data="{
                        darkMode: document.documentElement.classList.contains('dark'),
                        init() {
                            window.addEventListener('theme-changed', (e) => {
                                this.darkMode = e.detail.isDark;
                            });
                        },
                        setTheme(dark) {
                            if (this.darkMode === dark) return;
                            this.darkMode = dark;
                            if (this.darkMode) {
                                document.documentElement.classList.add('dark');
                                localStorage.theme = 'dark';
                            } else {
                                document.documentElement.classList.remove('dark');
                                localStorage.theme = 'light';
                            }
                            window.dispatchEvent(new CustomEvent('theme-changed', { detail: { isDark: this.darkMode } }));
                        }
                    }" class="flex items-center">
                        <div class="inline-flex items-center p-0.5 rounded-full bg-black/20 dark:bg-black/40 border border-white/10 dark:border-white/[0.08] select-none gap-0.5">
                            <button 
                                type="button" 
                                @click="setTheme(false)"
                                :class="!darkMode ? 'bg-white text-[#146970] shadow-xs' : 'text-teal-200/70 dark:text-slate-400 hover:text-white'"
                                class="px-2 py-0.5 rounded-full text-[11px] font-bold transition-all duration-150 flex items-center gap-1 cursor-pointer"
                                title="Switch to Light Mode"
                            >
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
                                <span>Light</span>
                            </button>
                            <button 
                                type="button" 
                                @click="setTheme(true)"
                                :class="darkMode ? 'bg-[#1E2230] text-[#818CF8] shadow-xs border border-white/[0.08]' : 'text-teal-200/70 dark:text-slate-400 hover:text-white'"
                                class="px-2 py-0.5 rounded-full text-[11px] font-bold transition-all duration-150 flex items-center gap-1 cursor-pointer"
                                title="Switch to Dark Mode"
                            >
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                                <span>Dark</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Navigation List (Scrollable Area) --}}
        <div class="flex-1 overflow-y-auto px-4 py-2 space-y-1">
            <nav class="space-y-1" aria-label="Mobile navigation" x-data="{ hasNewAdoption: {{ $hasNewAdoptionRequests ? 'true' : 'false' }} }">
                <p class="text-[10px] font-extrabold uppercase tracking-widest text-teal-200 dark:text-slate-500 px-3 pt-1 pb-1 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-300 dark:bg-slate-600"></span>
                    Overview
                </p>

                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}" @click="mobileNavOpen = false" title="Dashboard"
                    class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 {{ request()->routeIs('dashboard') ? 'bg-white/20 dark:bg-white/[0.06] text-white shadow-xs backdrop-blur-xs' : 'text-teal-100/90 dark:text-slate-400 hover:text-white dark:hover:text-white hover:bg-white/10 dark:hover:bg-white/[0.03]' }}">
                    @if(request()->routeIs('dashboard'))
                        <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-teal-200 dark:bg-[#199CA4]"></span>
                    @endif
                    <span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-150 {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white dark:bg-[#199CA4]/15 dark:text-[#41C1CB]' : 'bg-white/10 dark:bg-white/[0.04] text-teal-100/90 dark:text-slate-400 group-hover:bg-white/15 dark:group-hover:bg-white/[0.08] group-hover:text-white dark:group-hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4v11a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path></svg>
                    </span>
                    <span class="whitespace-nowrap tracking-wide">Dashboard</span>
                </a>

                <p class="text-[10px] font-extrabold uppercase tracking-widest text-teal-200 dark:text-slate-500 px-3 pt-3 pb-1 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-300 dark:bg-slate-600"></span>
                    Management
                </p>

                {{-- Pets --}}
                <a href="{{ route('pets.index') }}" @click="mobileNavOpen = false" title="Pets Catalog"
                    class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 {{ request()->routeIs('pets.*') ? 'bg-white/20 dark:bg-white/[0.06] text-white shadow-xs backdrop-blur-xs' : 'text-teal-100/90 dark:text-slate-400 hover:text-white dark:hover:text-white hover:bg-white/10 dark:hover:bg-white/[0.03]' }}">
                    @if(request()->routeIs('pets.*'))
                        <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-teal-200 dark:bg-[#199CA4]"></span>
                    @endif
                    <span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-150 {{ request()->routeIs('pets.*') ? 'bg-white/20 text-white dark:bg-[#199CA4]/15 dark:text-[#41C1CB]' : 'bg-white/10 dark:bg-white/[0.04] text-teal-100/90 dark:text-slate-400 group-hover:bg-white/15 dark:group-hover:bg-white/[0.08] group-hover:text-white dark:group-hover:text-white' }}">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                    </span>
                    <span class="whitespace-nowrap tracking-wide">Pets Catalog</span>
                </a>

                {{-- Staff Management (Admin only) --}}
                @if(Auth::user()?->role === 'admin')
                    <a href="{{ route('users.index') }}" @click="mobileNavOpen = false" title="Staff Management"
                        class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 {{ request()->routeIs('users.*') ? 'bg-white/20 dark:bg-white/[0.06] text-white shadow-xs backdrop-blur-xs' : 'text-teal-100/90 dark:text-slate-400 hover:text-white dark:hover:text-white hover:bg-white/10 dark:hover:bg-white/[0.03]' }}">
                        @if(request()->routeIs('users.*'))
                            <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-teal-200 dark:bg-[#199CA4]"></span>
                        @endif
                        <span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-150 {{ request()->routeIs('users.*') ? 'bg-white/20 text-white dark:bg-[#199CA4]/15 dark:text-[#41C1CB]' : 'bg-white/10 dark:bg-white/[0.04] text-teal-100/90 dark:text-slate-400 group-hover:bg-white/15 dark:group-hover:bg-white/[0.08] group-hover:text-white dark:group-hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 15c2.89 0 5.578.92 7.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0zm6 7v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a4 4 0 014-4h6a4 4 0 014 4z" /></svg>
                        </span>
                        <span class="whitespace-nowrap tracking-wide">Staff Management</span>
                    </a>
                @endif

                {{-- Adoption Requests --}}
                <a href="{{ route('adoption-applications.index') }}" @click="mobileNavOpen = false; hasNewAdoption = false; try { fetch('{{ route('adoption-applications.mark-viewed') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }) } catch(e){}" title="Adoption Requests"
                    class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 {{ request()->routeIs('adoption-applications.*') ? 'bg-white/20 dark:bg-white/[0.06] text-white shadow-xs backdrop-blur-xs' : 'text-teal-100/90 dark:text-slate-400 hover:text-white dark:hover:text-white hover:bg-white/10 dark:hover:bg-white/[0.03]' }}">
                    @if(request()->routeIs('adoption-applications.*'))
                        <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-teal-200 dark:bg-[#199CA4]"></span>
                    @endif
                    <span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-150 {{ request()->routeIs('adoption-applications.*') ? 'bg-white/20 text-white dark:bg-[#199CA4]/15 dark:text-[#41C1CB]' : 'bg-white/10 dark:bg-white/[0.04] text-teal-100/90 dark:text-slate-400 group-hover:bg-white/15 dark:group-hover:bg-white/[0.08] group-hover:text-white dark:group-hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 6h18M3 14h18M3 18h18"></path></svg>
                    </span>
                    <span class="whitespace-nowrap tracking-wide">Adoption Requests</span>
                    @if($hasNewAdoptionRequests)
                        <span x-show="hasNewAdoption" class="ml-auto relative flex h-2.5 w-2.5 shrink-0" data-testid="new-adoption-request-indicator" title="New request awaiting review">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500 ring-2 ring-white/30 dark:ring-[#0C0D13]"></span>
                        </span>
                    @endif
                </a>

                {{-- Adopters --}}
                <a href="{{ route('adopters.index') }}" @click="mobileNavOpen = false" title="Adopters Directory"
                    class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 {{ request()->routeIs('adopters.*') ? 'bg-white/20 dark:bg-white/[0.06] text-white shadow-xs backdrop-blur-xs' : 'text-teal-100/90 dark:text-slate-400 hover:text-white dark:hover:text-white hover:bg-white/10 dark:hover:bg-white/[0.03]' }}">
                    @if(request()->routeIs('adopters.*'))
                        <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-teal-200 dark:bg-[#199CA4]"></span>
                    @endif
                    <span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-150 {{ request()->routeIs('adopters.*') ? 'bg-white/20 text-white dark:bg-[#199CA4]/15 dark:text-[#41C1CB]' : 'bg-white/10 dark:bg-white/[0.04] text-teal-100/90 dark:text-slate-400 group-hover:bg-white/15 dark:group-hover:bg-white/[0.08] group-hover:text-white dark:group-hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 10-8 0v4M5 11h14v10H5V11z"></path></svg>
                    </span>
                    <span class="whitespace-nowrap tracking-wide">Adopters Directory</span>
                </a>

                <p class="text-[10px] font-extrabold uppercase tracking-widest text-teal-200 dark:text-slate-500 px-3 pt-3 pb-1 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-300 dark:bg-slate-600"></span>
                    Records
                </p>

                {{-- Medical Logs --}}
                <a href="{{ route('medical-logs.index') }}" @click="mobileNavOpen = false" title="Medical Logs"
                    class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 {{ request()->routeIs('medical-logs.*') ? 'bg-white/20 dark:bg-white/[0.06] text-white shadow-xs backdrop-blur-xs' : 'text-teal-100/90 dark:text-slate-400 hover:text-white dark:hover:text-white hover:bg-white/10 dark:hover:bg-white/[0.03]' }}">
                    @if(request()->routeIs('medical-logs.*'))
                        <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-teal-200 dark:bg-[#199CA4]"></span>
                    @endif
                    <span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-150 {{ request()->routeIs('medical-logs.*') ? 'bg-white/20 text-white dark:bg-[#199CA4]/15 dark:text-[#41C1CB]' : 'bg-white/10 dark:bg-white/[0.04] text-teal-100/90 dark:text-slate-400 group-hover:bg-white/15 dark:group-hover:bg-white/[0.08] group-hover:text-white dark:group-hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </span>
                    <span class="whitespace-nowrap tracking-wide">Medical Logs</span>
                </a>

                {{-- Pet History --}}
                <a href="{{ route('pet-history.index') }}" @click="mobileNavOpen = false" title="Pet History"
                    class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 {{ request()->routeIs('pet-history.*') ? 'bg-white/20 dark:bg-white/[0.06] text-white shadow-xs backdrop-blur-xs' : 'text-teal-100/90 dark:text-slate-400 hover:text-white dark:hover:text-white hover:bg-white/10 dark:hover:bg-white/[0.03]' }}">
                    @if(request()->routeIs('pet-history.*'))
                        <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-teal-200 dark:bg-[#199CA4]"></span>
                    @endif
                    <span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-150 {{ request()->routeIs('pet-history.*') ? 'bg-white/20 text-white dark:bg-[#199CA4]/15 dark:text-[#41C1CB]' : 'bg-white/10 dark:bg-white/[0.04] text-teal-100/90 dark:text-slate-400 group-hover:bg-white/15 dark:group-hover:bg-white/[0.08] group-hover:text-white dark:group-hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                    <span class="whitespace-nowrap tracking-wide">Pet History</span>
                </a>

                {{-- Reports --}}
                <a href="{{ route('reports.index') }}" @click="mobileNavOpen = false" title="Reports"
                    class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 {{ request()->routeIs('reports.*') ? 'bg-white/20 dark:bg-white/[0.06] text-white shadow-xs backdrop-blur-xs' : 'text-teal-100/90 dark:text-slate-400 hover:text-white dark:hover:text-white hover:bg-white/10 dark:hover:bg-white/[0.03]' }}">
                    @if(request()->routeIs('reports.*'))
                        <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-teal-200 dark:bg-[#199CA4]"></span>
                    @endif
                    <span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-150 {{ request()->routeIs('reports.*') ? 'bg-white/20 text-white dark:bg-[#199CA4]/15 dark:text-[#41C1CB]' : 'bg-white/10 dark:bg-white/[0.04] text-teal-100/90 dark:text-slate-400 group-hover:bg-white/15 dark:group-hover:bg-white/[0.08] group-hover:text-white dark:group-hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    </span>
                    <span class="whitespace-nowrap tracking-wide">Reports</span>
                </a>

                <p class="text-[10px] font-extrabold uppercase tracking-widest text-teal-200 dark:text-slate-500 px-3 pt-3 pb-1 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-300 dark:bg-slate-600"></span>
                    Account
                </p>

                {{-- Profile --}}
                <a href="{{ route('profile.edit') }}" @click="mobileNavOpen = false" title="Profile Settings"
                    class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 {{ request()->routeIs('profile.edit') ? 'bg-white/20 dark:bg-white/[0.06] text-white shadow-xs backdrop-blur-xs' : 'text-teal-100/90 dark:text-slate-400 hover:text-white dark:hover:text-white hover:bg-white/10 dark:hover:bg-white/[0.03]' }}">
                    @if(request()->routeIs('profile.edit'))
                        <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-teal-200 dark:bg-[#199CA4]"></span>
                    @endif
                    <span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-150 {{ request()->routeIs('profile.edit') ? 'bg-white/20 text-white dark:bg-[#199CA4]/15 dark:text-[#41C1CB]' : 'bg-white/10 dark:bg-white/[0.04] text-teal-100/90 dark:text-slate-400 group-hover:bg-white/15 dark:group-hover:bg-white/[0.08] group-hover:text-white dark:group-hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </span>
                    <span class="whitespace-nowrap tracking-wide">Profile Settings</span>
                </a>

                {{-- Log Out --}}
                <form method="POST" action="{{ route('logout') }}" class="pt-1">
                    @csrf
                    <button type="submit" 
                        class="w-full group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-rose-200 dark:text-rose-400 hover:text-white dark:hover:text-white hover:bg-rose-500/20 dark:hover:bg-rose-950/40 transition-all duration-150 cursor-pointer"
                    >
                        <span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl bg-rose-500/20 dark:bg-rose-950/50 text-rose-300 dark:text-rose-400 group-hover:bg-rose-500 group-hover:text-white transition-colors duration-150">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        </span>
                        <span class="whitespace-nowrap tracking-wide">Log Out</span>
                    </button>
                </form>
            </nav>
        </div>

        {{-- Mobile Drawer Footer Card --}}
        <div class="shrink-0 p-4 border-t border-white/10 dark:border-white/[0.06] bg-black/15 dark:bg-[#0C0D13]">
            <div class="p-2.5 bg-white/10 dark:bg-[#12141C] rounded-2xl border border-white/10 dark:border-white/[0.06] flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-[12px] font-bold text-white tracking-wide truncate">CDO Animal Welfare Society</p>
                    <p class="text-[10px] text-teal-200/80 dark:text-slate-400 font-medium tracking-wide">Shelter Management Portal</p>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-mono font-bold bg-white/15 dark:bg-white/[0.08] text-teal-100 dark:text-slate-300 border border-white/10 dark:border-white/[0.06]">
                    Portal
                </span>
            </div>
        </div>
    </div>
</div>

