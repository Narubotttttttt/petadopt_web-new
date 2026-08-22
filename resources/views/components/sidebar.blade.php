<div class="hidden lg:block fixed inset-y-0 left-0 z-40">
	<aside class="w-64 h-full overflow-y-auto border-r border-gray-200 dark:border-slate-800 bg-white dark:bg-[#0c181b] shadow-sm transition-colors duration-150">
		<div class="px-4 py-6">
			{{-- Brand Header --}}
			<div class="flex items-center gap-3 mb-8 h-10 px-1">
				<img src="{{ asset('images/caws-logo.jpg') }}" alt="CAWS Logo" class="w-10 h-10 shrink-0 rounded-xl object-cover border border-gray-200 dark:border-slate-700 shadow-xs">
				<div class="whitespace-nowrap min-w-0">
					<h2 class="text-sm font-bold text-gray-900 dark:text-white leading-tight truncate">CDO Animal Welfare</h2>
					<p class="text-xs text-gray-500 dark:text-slate-400 font-medium truncate">Society Inc.</p>
				</div>
			</div>

			{{-- Navigation List --}}
			<nav class="space-y-1.5" aria-label="Main navigation">
				<a href="{{ route('dashboard') }}" title="Dashboard" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('dashboard') ? 'bg-[#199CA4]/10 text-[#199CA4] font-bold' : 'text-gray-600 dark:text-slate-300 hover:bg-[#199CA4]/5 dark:hover:bg-slate-800 hover:text-[#199CA4] dark:hover:text-[#41C1CB] font-medium' }} transition-all text-xs sm:text-sm">
					<span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB]' : 'bg-gray-100 dark:bg-slate-800 group-hover:bg-[#199CA4]/10 text-gray-500 dark:text-slate-400 group-hover:text-[#199CA4] dark:group-hover:text-[#41C1CB]' }}">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4v11a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path></svg>
					</span>
					<span class="whitespace-nowrap">Dashboard</span>
				</a>

				<a href="{{ route('pets.index') }}" title="Pets" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('pets.*') ? 'bg-[#199CA4]/10 text-[#199CA4] font-bold' : 'text-gray-600 dark:text-slate-300 hover:bg-[#199CA4]/5 dark:hover:bg-slate-800 hover:text-[#199CA4] dark:hover:text-[#41C1CB] font-medium' }} transition-all text-xs sm:text-sm">
					<span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-lg {{ request()->routeIs('pets.*') ? 'bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB]' : 'bg-gray-100 dark:bg-slate-800 group-hover:bg-[#199CA4]/10 text-gray-500 dark:text-slate-400 group-hover:text-[#199CA4] dark:group-hover:text-[#41C1CB]' }}">
						<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c-1.66 0-3 1.34-3 3 0 2 2 3.5 3 3.5s3-1.5 3-3.5c0-1.66-1.34-3-3-3zm-4.5-2c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm9 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/></svg>
					</span>
					<span class="whitespace-nowrap">Pets</span>
				</a>

				@if(Auth::user()->role === 'admin')
					<a href="{{ route('users.index') }}" title="User Management" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('users.*') ? 'bg-[#199CA4]/10 text-[#199CA4] font-bold' : 'text-gray-600 dark:text-slate-300 hover:bg-[#199CA4]/5 dark:hover:bg-slate-800 hover:text-[#199CA4] dark:hover:text-[#41C1CB] font-medium' }} transition-all text-xs sm:text-sm">
						<span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-lg {{ request()->routeIs('users.*') ? 'bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB]' : 'bg-gray-100 dark:bg-slate-800 group-hover:bg-[#199CA4]/10 text-gray-500 dark:text-slate-400 group-hover:text-[#199CA4] dark:group-hover:text-[#41C1CB]' }}">
							<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 15c2.89 0 5.578.92 7.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0zm6 7v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a4 4 0 014-4h6a4 4 0 014 4z" /></svg>
						</span>
						<span class="whitespace-nowrap">User Management</span>
					</a>
				@endif

				<a href="{{ route('adoption-applications.index') }}" title="Adoption Requests" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('adoption-applications.*') ? 'bg-[#199CA4]/10 text-[#199CA4] font-bold' : 'text-gray-600 dark:text-slate-300 hover:bg-[#199CA4]/5 dark:hover:bg-slate-800 hover:text-[#199CA4] dark:hover:text-[#41C1CB] font-medium' }} transition-all text-xs sm:text-sm">
					<span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-lg {{ request()->routeIs('adoption-applications.*') ? 'bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB]' : 'bg-gray-100 dark:bg-slate-800 group-hover:bg-[#199CA4]/10 text-gray-500 dark:text-slate-400 group-hover:text-[#199CA4] dark:group-hover:text-[#41C1CB]' }}">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 6h18M3 14h18M3 18h18"></path></svg>
					</span>
					<span class="whitespace-nowrap">Adoption Requests</span>
				</a>

				<a href="{{ route('adopters.index') }}" title="Adopters" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('adopters.*') ? 'bg-[#199CA4]/10 text-[#199CA4] font-bold' : 'text-gray-600 dark:text-slate-300 hover:bg-[#199CA4]/5 dark:hover:bg-slate-800 hover:text-[#199CA4] dark:hover:text-[#41C1CB] font-medium' }} transition-all text-xs sm:text-sm">
					<span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-lg {{ request()->routeIs('adopters.*') ? 'bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB]' : 'bg-gray-100 dark:bg-slate-800 group-hover:bg-[#199CA4]/10 text-gray-500 dark:text-slate-400 group-hover:text-[#199CA4] dark:group-hover:text-[#41C1CB]' }}">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 10-8 0v4M5 11h14v10H5V11z"></path></svg>
					</span>
					<span class="whitespace-nowrap">Adopters</span>
				</a>

				<a href="{{ route('medical-logs.index') }}" title="Medical Logs" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('medical-logs.*') ? 'bg-[#199CA4]/10 text-[#199CA4] font-bold' : 'text-gray-600 dark:text-slate-300 hover:bg-[#199CA4]/5 dark:hover:bg-slate-800 hover:text-[#199CA4] dark:hover:text-[#41C1CB] font-medium' }} transition-all text-xs sm:text-sm">
					<span class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-lg {{ request()->routeIs('medical-logs.*') ? 'bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB]' : 'bg-gray-100 dark:bg-slate-800 group-hover:bg-[#199CA4]/10 text-gray-500 dark:text-slate-400 group-hover:text-[#199CA4] dark:group-hover:text-[#41C1CB]' }}">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
					</span>
					<span class="whitespace-nowrap">Medical Logs</span>
				</a>
			</nav>
		</div>
	</aside>
</div>
