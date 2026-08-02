<div x-data="{ expanded: {{ request()->routeIs('dashboard') ? 'true' : 'false' }} }" class="hidden lg:block fixed inset-y-0 left-0 z-40">
	<aside
		@mouseenter="expanded = true"
		@mouseleave="expanded = {{ request()->routeIs('dashboard') ? 'true' : 'false' }}"
		:class="expanded ? 'w-72' : 'w-20'"
		class="h-full overflow-hidden border-r border-gray-200 bg-white shadow-sm transition-[width] duration-300 ease-out"
	>
		<div class="px-3 py-8">
			<div class="flex items-center gap-3 mb-10 h-10">
				<img src="{{ asset('images/caws-logo.jpg') }}" alt="CAWS Logo" class="w-10 h-10 shrink-0 rounded-xl object-cover border border-gray-200">
				<div x-show="expanded" x-cloak class="whitespace-nowrap">
					<h2 class="text-md font-bold text-gray-900 leading-tight">CDO Animal Welfare</h2>
					<p class="text-xs text-gray-500 font-medium">Society Inc.</p>
				</div>
			</div>

			<nav class="space-y-1.5" aria-label="Main navigation">
				<a href="{{ route('dashboard') }}" title="Dashboard" class="group flex items-center gap-3 px-2 py-3 rounded-xl {{ request()->routeIs('dashboard') ? 'bg-[#199CA4]/10 text-[#199CA4] font-semibold' : 'text-gray-600 hover:bg-[#199CA4]/5 hover:text-[#199CA4]' }} transition-all">
					<span class="inline-flex items-center justify-center w-10 h-8 shrink-0 rounded-lg bg-gray-100 group-hover:bg-[#199CA4]/10">
						<svg class="w-5 h-5 text-gray-500 group-hover:text-[#199CA4]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4v11a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path></svg>
					</span>
					<span x-show="expanded" x-cloak class="whitespace-nowrap">Dashboard</span>
				</a>

				<a href="{{ route('pets.index') }}" title="Pets" class="group flex items-center gap-3 px-2 py-3 rounded-xl {{ request()->routeIs('pets.*') ? 'bg-[#199CA4]/10 text-[#199CA4] font-semibold' : 'text-gray-600 hover:bg-[#199CA4]/5 hover:text-[#199CA4]' }} transition-all">
					<span class="inline-flex items-center justify-center w-10 h-8 shrink-0 rounded-lg bg-gray-100 group-hover:bg-[#199CA4]/10">
						<svg class="w-5 h-5 text-gray-500 group-hover:text-[#199CA4]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c-1.66 0-3 1.34-3 3 0 2 2 3.5 3 3.5s3-1.5 3-3.5c0-1.66-1.34-3-3-3zm-4.5-2c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm9 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/></svg>
					</span>
					<span x-show="expanded" x-cloak class="whitespace-nowrap">Pets</span>
				</a>

				@if(Auth::user()->role === 'admin')
					<a href="{{ route('users.index') }}" title="User Management" class="group flex items-center gap-3 px-2 py-3 rounded-xl {{ request()->routeIs('users.*') ? 'bg-[#199CA4]/10 text-[#199CA4] font-semibold' : 'text-gray-600 hover:bg-[#199CA4]/5 hover:text-[#199CA4]' }} transition-all">
						<span class="inline-flex items-center justify-center w-10 h-8 shrink-0 rounded-lg bg-gray-100 group-hover:bg-[#199CA4]/10">
							<svg class="w-5 h-5 text-gray-500 group-hover:text-[#199CA4]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 15c2.89 0 5.578.92 7.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0zm6 7v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a4 4 0 014-4h6a4 4 0 014 4z" /></svg>
						</span>
						<span x-show="expanded" x-cloak class="whitespace-nowrap">User Management</span>
					</a>
				@endif

				<a href="{{ route('adoption-applications.index') }}" title="Adoption Requests" class="group flex items-center gap-3 px-2 py-3 rounded-xl {{ request()->routeIs('adoption-applications.*') ? 'bg-[#199CA4]/10 text-[#199CA4] font-semibold' : 'text-gray-600 hover:bg-[#199CA4]/5 hover:text-[#199CA4]' }} transition-all">
					<span class="inline-flex items-center justify-center w-10 h-8 shrink-0 rounded-lg bg-gray-100 group-hover:bg-[#199CA4]/10">
						<svg class="w-5 h-5 text-gray-500 group-hover:text-[#199CA4]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 6h18M3 14h18M3 18h18"></path></svg>
					</span>
					<span x-show="expanded" x-cloak class="whitespace-nowrap">Adoption Requests</span>
				</a>

				<a href="{{ route('adopters.index') }}" title="Adopters" class="group flex items-center gap-3 px-2 py-3 rounded-xl {{ request()->routeIs('adopters.*') ? 'bg-[#199CA4]/10 text-[#199CA4] font-semibold' : 'text-gray-600 hover:bg-[#199CA4]/5 hover:text-[#199CA4]' }} transition-all">
					<span class="inline-flex items-center justify-center w-10 h-8 shrink-0 rounded-lg bg-gray-100 group-hover:bg-[#199CA4]/10">
						<svg class="w-5 h-5 text-gray-500 group-hover:text-[#199CA4]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 10-8 0v4M5 11h14v10H5V11z"></path></svg>
					</span>
					<span x-show="expanded" x-cloak class="whitespace-nowrap">Adopters</span>
				</a>

				<a href="{{ route('medical-logs.index') }}" title="Medical Logs" class="group flex items-center gap-3 px-2 py-3 rounded-xl {{ request()->routeIs('medical-logs.*') ? 'bg-[#199CA4]/10 text-[#199CA4] font-semibold' : 'text-gray-600 hover:bg-[#199CA4]/5 hover:text-[#199CA4]' }} transition-all">
					<span class="inline-flex items-center justify-center w-10 h-8 shrink-0 rounded-lg bg-gray-100 group-hover:bg-[#199CA4]/10">
						<svg class="w-5 h-5 text-gray-500 group-hover:text-[#199CA4]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
					</span>
					<span x-show="expanded" x-cloak class="whitespace-nowrap">Medical Logs</span>
				</a>
			</nav>
		</div>
	</aside>
</div>