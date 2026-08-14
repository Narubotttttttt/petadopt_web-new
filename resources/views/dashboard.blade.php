<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#333634] tracking-tight">Admin Dashboard</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Welcome back, manager. Control users and system settings from here.</p>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-start">
                <div>
                    <p class="text-xs uppercase tracking-wider font-semibold text-gray-400">Total Pets</p>
                    <h3 class="text-3xl font-extrabold text-[#333634] my-1">{{ $totalPets }}</h3>
                    <p class="text-xs text-gray-500 font-medium">Active in system</p>
                </div>
                <div class="p-3 bg-[#EAF5F6] rounded-xl text-[#199CA4]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-start">
                <div>
                    <p class="text-xs uppercase tracking-wider font-semibold text-gray-400">Approved Adoptions</p>
                    <h3 class="text-3xl font-extrabold text-[#333634] my-1">{{ $totalAdoptions }}</h3>
                    <p class="text-xs text-gray-500 font-medium">Total completed</p>
                </div>
                <div class="p-3 bg-[#EAF5F6] rounded-xl text-[#199CA4]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-start">
                <div>
                    <p class="text-xs uppercase tracking-wider font-semibold text-gray-400">Total Users</p>
                    <h3 class="text-3xl font-extrabold text-[#333634] my-1">{{ $totalUsers }}</h3>
                    <p class="text-xs text-gray-500 font-medium">Admin and staff accounts</p>
                </div>
                <div class="p-3 bg-[#EAF5F6] rounded-xl text-[#199CA4]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
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

        <!-- Adoption Trends Chart Card -->
        <div class="grid grid-cols-1 gap-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-lg font-bold text-[#333634]">Adoption Trends</h2>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#199CA4]/10 text-[#199CA4] border border-[#199CA4]/20">
                                {{ $totalYearAdoptions }} Completed in {{ $selectedYear }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Monthly distribution of finalized and approved adoptions.
                            @if($peakCount > 0)
                                <span class="font-semibold text-gray-600">Peak: {{ $peakMonth }} ({{ $peakCount }} {{ Str::plural('adoption', $peakCount) }})</span>
                            @endif
                        </p>
                    </div>

                    <!-- Year Selector Dropdown -->
                    <div class="flex items-center gap-2">
                        <label for="yearFilter" class="text-xs font-semibold text-gray-500">Year:</label>
                        <select id="yearFilter" onchange="window.location.href='?year=' + this.value"
                            class="text-xs font-bold text-gray-700 bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 focus:ring-2 focus:ring-[#199CA4] focus:border-transparent transition">
                            @foreach($availableYears as $yr)
                                <option value="{{ $yr }}" {{ $selectedYear === $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="h-80 relative">
                    <canvas id="adoptionTrendsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Applications Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-[#333634]">Recent Adoption Applications</h2>
                <a href="{{ route('adoption-applications.index') }}" class="text-xs font-bold text-[#199CA4] hover:underline">View All Requests →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pet Name</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Adopter</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentApplications ?? [] as $application)
                            <tr class="hover:bg-gray-50/70 transition-colors text-xs sm:text-sm">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900 font-bold">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-[#199CA4]/10 text-[#199CA4] rounded-full flex items-center justify-center text-xs font-bold">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c-1.66 0-3 1.34-3 3 0 2 2 3.5 3 3.5s3-1.5 3-3.5c0-1.66-1.34-3-3-3zm-4.5-2c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm9 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/></svg>
                                        </div>
                                        {{ $application->pet->name ?: ($application->pet ? $application->pet->breed . ' (Pet #' . $application->pet->id . ')' : 'Pet #' . $application->pet_id) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                    <div class="font-semibold text-gray-900">{{ $application->applicant_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $application->applicant_phone }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                    {{ $application->created_at ? $application->created_at->diffForHumans() : '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold 
                                        {{ $application->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                        {{ $application->status === 'rejected' ? 'bg-rose-100 text-rose-800' : '' }}
                                        {{ $application->status === 'under_review' ? 'bg-sky-100 text-sky-800' : '' }}
                                        {{ $application->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <a href="{{ route('adoption-applications.show', $application) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-[#EAF5F6] text-[#199CA4] font-bold hover:bg-[#199CA4] hover:text-white transition">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No adoption requests submitted yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('adoptionTrendsChart');
            if (!ctx) return;

            const chartContext = ctx.getContext('2d');
            const gradient = chartContext.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(25, 156, 164, 0.28)');
            gradient.addColorStop(1, 'rgba(25, 156, 164, 0.01)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartMonths ?? []),
                    datasets: [{
                        label: 'Approved Adoptions',
                        data: @json($chartCounts ?? []),
                        borderColor: '#199CA4',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        tension: 0.35,
                        fill: true,
                        pointBackgroundColor: '#FFFFFF',
                        pointBorderColor: '#199CA4',
                        pointBorderWidth: 2.5,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointHoverBackgroundColor: '#199CA4',
                        pointHoverBorderColor: '#FFFFFF',
                        pointHoverBorderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1E293B',
                            titleColor: '#FFFFFF',
                            bodyColor: '#E2E8F0',
                            padding: 10,
                            cornerRadius: 10,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    const count = context.parsed.y;
                                    return count + ' ' + (count === 1 ? 'Adoption' : 'Adoptions') + ' Approved';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMax: 5,
                            ticks: {
                                precision: 0,
                                stepSize: 1,
                                color: '#94A3B8',
                                font: { size: 11, weight: '600' }
                            },
                            grid: {
                                color: '#F1F5F9',
                                drawBorder: false
                            }
                        },
                        x: {
                            ticks: {
                                color: '#64748B',
                                font: { size: 11, weight: '600' }
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>