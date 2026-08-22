<div class="hidden lg:block fixed inset-y-0 left-0 z-40">
	<aside class="w-64 h-full overflow-y-auto border-r border-[#199CA4]/15 bg-gradient-to-b from-[#081b20] via-[#0b242a] to-[#061519] text-slate-300 shadow-2xl flex flex-col justify-between select-none">
		<div class="px-4 py-6">
			{{-- Brand Header (Clean unboxed style) --}}
			<div class="flex items-center gap-3 mb-8 h-10 px-1">
				<img src="{{ asset('images/caws-logo.jpg') }}" alt="CAWS Logo" class="w-10 h-10 shrink-0 rounded-xl object-cover ring-1 ring-white/20 shadow-sm">
				<div class="whitespace-nowrap min-w-0">
					<h2 class="text-sm font-bold text-white leading-tight truncate">CDO Animal Welfare</h2>
					<p class="text-xs text-slate-400 font-medium truncate">Society Inc.</p>
				</div>
			</div>

			{{-- Navigation List --}}
			<nav class="space-y-1.5" aria-label="Main navigation">
				
				<p class="text-[10px] font-extrabold uppercase tracking-widest text-[#7CD8DF]/50 px-3 pt-2 pb-1 flex items-center gap-1.5">
					<span class="w-1.5 h-1.5 rounded-full bg-[#199CA4]/60"></span>
					Overview
				</p>

				{{-- Dashboard --}}
				<a href="{{ route('dashboard') }}" title="Dashboard"
					class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-[#199CA4] via-[#15838B] to-[#0f4d52] text-white shadow-lg shadow-[#199CA4]/30 ring-1 ring-white/20' : 'text-slate-400 hover:text-white hover:bg-white/[0.07]' }}">
					@if(request()->routeIs('dashboard'))
						<span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-[#41C1CB] shadow-sm shadow-[#41C1CB]"></span>
					@endif
					<span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white shadow-inner' : 'bg-white/5 text-slate-400 group-hover:bg-[#199CA4]/20 group-hover:text-[#41C1CB]' }}">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4v11a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path></svg>
					</span>
					<span class="whitespace-nowrap tracking-wide">Dashboard</span>
				</a>

				<p class="text-[10px] font-extrabold uppercase tracking-widest text-[#7CD8DF]/50 px-3 pt-4 pb-1 flex items-center gap-1.5">
					<span class="w-1.5 h-1.5 rounded-full bg-[#199CA4]/60"></span>
					Management
				</p>

				{{-- Pets --}}
				<a href="{{ route('pets.index') }}" title="Pets"
					class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 {{ request()->routeIs('pets.*') ? 'bg-gradient-to-r from-[#199CA4] via-[#15838B] to-[#0f4d52] text-white shadow-lg shadow-[#199CA4]/30 ring-1 ring-white/20' : 'text-slate-400 hover:text-white hover:bg-white/[0.07]' }}">
					@if(request()->routeIs('pets.*'))
						<span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-[#41C1CB] shadow-sm shadow-[#41C1CB]"></span>
					@endif
					<span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-200 {{ request()->routeIs('pets.*') ? 'bg-white/20 text-white shadow-inner' : 'bg-white/5 text-slate-400 group-hover:bg-[#199CA4]/20 group-hover:text-[#41C1CB]' }}">
						<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c-1.66 0-3 1.34-3 3 0 2 2 3.5 3 3.5s3-1.5 3-3.5c0-1.66-1.34-3-3-3zm-4.5-2c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm9 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/></svg>
					</span>
					<span class="whitespace-nowrap tracking-wide">Pets</span>
				</a>

				{{-- Users (Admin only) --}}
				@if(Auth::user()->role === 'admin')
					<a href="{{ route('users.index') }}" title="User Management"
						class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-gradient-to-r from-[#199CA4] via-[#15838B] to-[#0f4d52] text-white shadow-lg shadow-[#199CA4]/30 ring-1 ring-white/20' : 'text-slate-400 hover:text-white hover:bg-white/[0.07]' }}">
						@if(request()->routeIs('users.*'))
							<span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-[#41C1CB] shadow-sm shadow-[#41C1CB]"></span>
						@endif
						<span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-white/20 text-white shadow-inner' : 'bg-white/5 text-slate-400 group-hover:bg-[#199CA4]/20 group-hover:text-[#41C1CB]' }}">
							<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 15c2.89 0 5.578.92 7.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0zm6 7v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a4 4 0 014-4h6a4 4 0 014 4z" /></svg>
						</span>
						<span class="whitespace-nowrap tracking-wide">User Management</span>
					</a>
				@endif

				{{-- Adoption Requests --}}
				<a href="{{ route('adoption-applications.index') }}" title="Adoption Requests"
					class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 {{ request()->routeIs('adoption-applications.*') ? 'bg-gradient-to-r from-[#199CA4] via-[#15838B] to-[#0f4d52] text-white shadow-lg shadow-[#199CA4]/30 ring-1 ring-white/20' : 'text-slate-400 hover:text-white hover:bg-white/[0.07]' }}">
					@if(request()->routeIs('adoption-applications.*'))
						<span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-[#41C1CB] shadow-sm shadow-[#41C1CB]"></span>
					@endif
					<span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-200 {{ request()->routeIs('adoption-applications.*') ? 'bg-white/20 text-white shadow-inner' : 'bg-white/5 text-slate-400 group-hover:bg-[#199CA4]/20 group-hover:text-[#41C1CB]' }}">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 6h18M3 14h18M3 18h18"></path></svg>
					</span>
					<span class="whitespace-nowrap tracking-wide">Adoption Requests</span>
				</a>

				{{-- Adopters --}}
				<a href="{{ route('adopters.index') }}" title="Adopters"
					class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 {{ request()->routeIs('adopters.*') ? 'bg-gradient-to-r from-[#199CA4] via-[#15838B] to-[#0f4d52] text-white shadow-lg shadow-[#199CA4]/30 ring-1 ring-white/20' : 'text-slate-400 hover:text-white hover:bg-white/[0.07]' }}">
					@if(request()->routeIs('adopters.*'))
						<span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-[#41C1CB] shadow-sm shadow-[#41C1CB]"></span>
					@endif
					<span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-200 {{ request()->routeIs('adopters.*') ? 'bg-white/20 text-white shadow-inner' : 'bg-white/5 text-slate-400 group-hover:bg-[#199CA4]/20 group-hover:text-[#41C1CB]' }}">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 10-8 0v4M5 11h14v10H5V11z"></path></svg>
					</span>
					<span class="whitespace-nowrap tracking-wide">Adopters</span>
				</a>

				<p class="text-[10px] font-extrabold uppercase tracking-widest text-[#7CD8DF]/50 px-3 pt-4 pb-1 flex items-center gap-1.5">
					<span class="w-1.5 h-1.5 rounded-full bg-[#199CA4]/60"></span>
					Records
				</p>

				{{-- Medical Logs --}}
				<a href="{{ route('medical-logs.index') }}" title="Medical Logs"
					class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 {{ request()->routeIs('medical-logs.*') ? 'bg-gradient-to-r from-[#199CA4] via-[#15838B] to-[#0f4d52] text-white shadow-lg shadow-[#199CA4]/30 ring-1 ring-white/20' : 'text-slate-400 hover:text-white hover:bg-white/[0.07]' }}">
					@if(request()->routeIs('medical-logs.*'))
						<span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-[#41C1CB] shadow-sm shadow-[#41C1CB]"></span>
					@endif
					<span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-xl transition-all duration-200 {{ request()->routeIs('medical-logs.*') ? 'bg-white/20 text-white shadow-inner' : 'bg-white/5 text-slate-400 group-hover:bg-[#199CA4]/20 group-hover:text-[#41C1CB]' }}">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
					</span>
					<span class="whitespace-nowrap tracking-wide">Medical Logs</span>
				</a>
			</nav>
		</div>

		{{-- Sidebar Footer Card --}}
		<div class="p-4 border-t border-white/10 bg-black/20">
			<div class="p-3 bg-gradient-to-br from-white/5 to-white/[0.02] rounded-2xl border border-white/10 flex items-center gap-3 backdrop-blur-xs">
				<div class="relative flex items-center justify-center">
					<span class="w-2.5 h-2.5 bg-emerald-400 rounded-full animate-ping absolute"></span>
					<span class="w-2.5 h-2.5 bg-emerald-400 rounded-full relative shadow-sm shadow-emerald-400"></span>
				</div>
				<div class="min-w-0 flex-1">
					<p class="text-[11px] font-bold text-white tracking-wide truncate">System Active</p>
					<p class="text-[10px] text-[#41C1CB] font-medium tracking-wide">CAWS Cloud v2.0</p>
				</div>
			</div>
		</div>
	</aside>
</div>
