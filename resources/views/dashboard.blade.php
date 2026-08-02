<x-app-layout>
    <div class="min-h-screen bg-gray-50 flex flex-col lg:flex-row lg:pl-52">
        
        <aside class="hidden">
            <div class="px-6 py-8">
                <div class="flex items-center gap-3 mb-10">
                    <img src="{{ asset('images/caws-logo.jpg') }}" alt="CAWS Logo" class="w-10 h-10 rounded-xl object-cover border border-gray-200">
                    <div>
                        <h2 class="text-md font-bold text-gray-900 leading-tight">CDO Animal Welfare</h2>
                        <p class="text-xs text-gray-500 font-medium">Society Inc.</p>
                    </div>
                </div>

                <nav class="space-y-1.5">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-[#199CA4]/10 text-[#199CA4] font-semibold transition-all">
                        <span class="inline-flex items-center justify-center w-8 h-8 bg-[#199CA4]/20 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4v11a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path>
                            </svg>
                        </span>
                        Dashboard
                    </a>
                   
                    <a href="{{ route('pets.index') }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-[#199CA4]/5 hover:text-[#199CA4] transition-all">
                        <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 rounded-lg group-hover:bg-[#199CA4]/10 transition-colors">
                            <svg class="w-5 h-5 text-gray-500 group-hover:text-[#199CA4] transition-colors" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 14c-1.66 0-3 1.34-3 3 0 2 2 3.5 3 3.5s3-1.5 3-3.5c0-1.66-1.34-3-3-3zm-4.5-2c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm9 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm-12.25 5c.69 0 1.25-.56 1.25-1.25v-1.5c0-.69-.56-1.25-1.25-1.25s-1.25.56-1.25 1.25v1.5c0 .69.56 1.25 1.25 1.25zm15.5 0c.69 0 1.25-.56 1.25-1.25v-1.5c0-.69-.56-1.25-1.25-1.25s-1.25.56-1.25 1.25v1.5c0 .69.56 1.25 1.25 1.25z"/>
                            </svg>
                        </span>
                        <span class="font-medium">Pets</span>
                    </a>

                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('users.index') }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-[#199CA4]/5 hover:text-[#199CA4] transition-all">
                            <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 rounded-lg group-hover:bg-[#199CA4]/10 transition-colors">
                                <svg class="w-5 h-5 text-gray-500 group-hover:text-[#199CA4] transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 15c2.89 0 5.578.92 7.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0zm6 7v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a4 4 0 014-4h6a4 4 0 014 4z" />
                                </svg>
                            </span>
                            <span class="font-medium">User Management</span>
                        </a>
                    @endif

                    <a href="{{ route('adoption-applications.index') }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-[#199CA4]/5 hover:text-[#199CA4] transition-all">
                        <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 rounded-lg group-hover:bg-[#199CA4]/10 transition-colors">
                            <svg class="w-5 h-5 text-gray-500 group-hover:text-[#199CA4] transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 6h18M3 14h18M3 18h18"></path>
                            </svg>
                        </span>
                        <span class="font-medium">Adoption Requests</span>
                    </a>

                    <a href="{{ route('adopters.index') }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-[#199CA4]/5 hover:text-[#199CA4] transition-all">
                        <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 rounded-lg group-hover:bg-[#199CA4]/10 transition-colors">
                            <svg class="w-5 h-5 text-gray-500 group-hover:text-[#199CA4] transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 10-8 0v4M5 11h14v10H5V11z"></path>
                            </svg>
                        </span>
                        <span class="font-medium">Adopters</span>
                    </a>
                </nav>
                <a href="#" class="group flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-[#199CA4]/5 hover:text-[#199CA4] transition-all">
    <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 rounded-lg group-hover:bg-[#199CA4]/10 transition-colors">
        <svg class="w-5 h-5 text-gray-500 group-hover:text-[#199CA4] transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
    </span>
    <span class="font-medium">Medical Logs</span>
</a>
            </div>
        </aside>

        <main class="flex-1 p-6 lg:p-10 overflow-y-auto">
            <div class="max-w-7xl mx-auto space-y-8">
                
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between pb-2">
                    <div>
                        <h1 class="text-2xl font-bold text-[#333634]">
                            {{ Auth::user()->role === 'admin' ? 'Admin Dashboard' : 'Staff Dashboard' }}
                        </h1>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ Auth::user()->role === 'admin' ? 'Welcome back, manager. Control users and system settings from here.' : 'Welcome back, team member. View pets and manage assigned tasks.' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Total Pets</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalPets ?? 0 }}</p>
                                <p class="text-xs text-gray-400 mt-2">Active in system</p>
                            </div>
                            <div class="bg-[#199CA4]/10 p-3 rounded-xl text-[#199CA4]">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Adoptions</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">0</p>
                                <p class="text-xs text-gray-400 mt-2">This month</p>
                            </div>
                            <div class="bg-[#199CA4]/10 p-3 rounded-xl text-[#199CA4]">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">
                                    {{ Auth::user()->role === 'admin' ? 'Total Users' : 'Assigned Pets' }}
                                </p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalUsers ?? 0 }}</p>
                                <p class="text-xs text-gray-400 mt-2">
                                    {{ Auth::user()->role === 'admin' ? 'Admin and staff accounts' : 'Your currently managed pet records' }}
                                </p>
                            </div>
                            <div class="bg-[#199CA4]/10 p-3 rounded-xl text-[#199CA4]">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM6 20H1v-2a6 6 0 016-6v0"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <a href="{{ $latestPet ? route('pets.show', $latestPet) : route('pets.index') }}" class="bg-teal-900 hover:bg-teal-800 text-white p-6 rounded-2xl shadow-sm flex justify-between items-start transition-colors">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-teal-200">Latest Pet</p>

                            @if($latestPet)
                                <h3 class="text-xl font-bold my-1 text-white">{{ $latestPet->name ?? 'Pet #'.$latestPet->id }}</h3>
                                <p class="text-xs text-teal-300">{{ ucfirst($latestPet->type ?? '') }} • {{ $latestPet->breed ?? '—' }}</p>
                                <p class="text-xs text-teal-300 mt-1">Added {{ $latestPet->created_at->diffForHumans() }}</p>
                            @else
                                <h3 class="text-xl font-bold my-1 text-white">No Pets Yet</h3>
                                <p class="text-xs text-teal-300">Add a pet to see the latest entry</p>
                            @endif
                        </div>
                        <div class="p-3 bg-teal-800 rounded-xl text-teal-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </a>

                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-[#333634] mb-4">Adoption Trends</h2>
                        <div class="h-80">
                            <canvas id="adoptionTrendsChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-lg font-bold text-[#333634]">Recent Adoptions</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pet Name</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Adopter</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr class="hover:bg-gray-50/70 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-[#199CA4]/10 rounded-full flex items-center justify-center">
                                                <span class="text-xs text-[#199CA4] font-bold">🐾</span>
                                            </div>
                                            No Data
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">—</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">—</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">—</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="inline-flex px-2.5 py-1 bg-yellow-50 text-yellow-700 rounded-full text-xs font-semibold border border-yellow-100">
                                            
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script>
        const ctx = document.getElementById('adoptionTrendsChart');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartMonths ?? []),
                datasets: [{
                    label: 'Adoptions',
                    data: @json($chartCounts ?? []),
                    borderColor: '#199CA4',
                    backgroundColor: 'rgba(25, 156, 164, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#199CA4',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    </script>
</x-app-layout>