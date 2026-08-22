<x-app-layout>
    <div class="w-full py-8 px-4 sm:px-6 lg:px-8 space-y-8 animate-fade-in">
        
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">Admin Dashboard</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Welcome back! Manage pets, adoption applications, and medical records.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-[#0e1d20] border border-[#199CA4]/20 dark:border-slate-800 shadow-2xs text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-[#199CA4] animate-pulse"></span>
                    {{ now()->format('l, F j, Y') }}
                </span>
            </div>
        </div>

        {{-- Metric Cards --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            
            {{-- Total Pets --}}
            <div class="bg-white dark:bg-[#0e1d20] p-6 rounded-2xl shadow-card border border-slate-200/80 dark:border-slate-800 card-hover-effect flex justify-between items-start">
                <div>
                    <p class="text-[11px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-500">Total Pets</p>
                    <h3 class="text-3xl font-extrabold text-slate-800 dark:text-white my-1 tracking-tight">{{ $totalPets }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#199CA4]"></span>
                        Active in catalog
                    </p>
                </div>
                <div class="p-3.5 bg-gradient-to-br from-[#F0FBFB] to-[#D6F4F6] dark:from-[#133036] dark:to-[#17454d] rounded-2xl text-[#199CA4] dark:text-[#41C1CB] shadow-xs ring-1 ring-[#199CA4]/20 dark:ring-[#41C1CB]/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>

            {{-- Approved Adoptions --}}
            <div class="bg-white dark:bg-[#0e1d20] p-6 rounded-2xl shadow-card border border-slate-200/80 dark:border-slate-800 card-hover-effect flex justify-between items-start">
                <div>
                    <p class="text-[11px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-500">Approved Adoptions</p>
                    <h3 class="text-3xl font-extrabold text-slate-800 dark:text-white my-1 tracking-tight">{{ $totalAdoptions }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#199CA4]"></span>
                        Total finalized
                    </p>
                </div>
                <div class="p-3.5 bg-gradient-to-br from-[#F0FBFB] to-[#D6F4F6] dark:from-[#133036] dark:to-[#17454d] rounded-2xl text-[#199CA4] dark:text-[#41C1CB] shadow-xs ring-1 ring-[#199CA4]/20 dark:ring-[#41C1CB]/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            {{-- Total Users --}}
            <div class="bg-white dark:bg-[#0e1d20] p-6 rounded-2xl shadow-card border border-slate-200/80 dark:border-slate-800 card-hover-effect flex justify-between items-start">
                <div>
                    <p class="text-[11px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-500">Total Users</p>
                    <h3 class="text-3xl font-extrabold text-slate-800 dark:text-white my-1 tracking-tight">{{ $totalUsers }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#199CA4]"></span>
                        Authorized staff
                    </p>
                </div>
                <div class="p-3.5 bg-gradient-to-br from-[#F0FBFB] to-[#D6F4F6] dark:from-[#133036] dark:to-[#17454d] rounded-2xl text-[#199CA4] dark:text-[#41C1CB] shadow-xs ring-1 ring-[#199CA4]/20 dark:ring-[#41C1CB]/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>

            {{-- Latest Pet Card --}}
            <a href="{{ $latestPet ? route('pets.show', $latestPet) : route('pets.index') }}" class="group bg-gradient-to-br from-[#0f4d52] via-[#146970] to-[#199CA4] hover:from-[#0d3f43] hover:to-[#178a91] text-white p-6 rounded-2xl shadow-md shadow-[#199CA4]/25 flex justify-between items-start transition-all duration-300 card-hover-effect">
                <div>
                    <span class="inline-block px-2.5 py-0.5 rounded-md bg-white/20 text-[10px] uppercase tracking-wider font-extrabold text-teal-100 mb-1 backdrop-blur-xs">Latest Pet</span>

                    @if($latestPet)
                        <h3 class="text-xl font-extrabold my-1 text-white tracking-tight">Pet #{{ $latestPet->id }}</h3>
                        <p class="text-xs text-teal-100 font-medium">{{ ucfirst($latestPet->type ?? '') }} • {{ $latestPet->breed ?? '—' }}</p>
                        <p class="text-[11px] text-teal-200/80 mt-1">Added {{ $latestPet->created_at->diffForHumans() }}</p>
                    @else
                        <h3 class="text-xl font-extrabold my-1 text-white">No Pets Yet</h3>
                        <p class="text-xs text-teal-200">Add a pet to see entry</p>
                    @endif
                </div>
                <div class="p-3.5 bg-white/15 group-hover:bg-white/25 rounded-2xl text-white transition-colors duration-200 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </a>

        </div>

        {{-- Adoption Trends Chart Card --}}
        <div class="grid grid-cols-1 gap-6">
            <div class="bg-white dark:bg-[#0e1d20] rounded-3xl shadow-card border border-slate-200/80 dark:border-slate-800 p-6 sm:p-8 card-hover-effect relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#199CA4] via-[#41C1CB] to-[#14838B]"></div>
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg font-extrabold text-slate-800 dark:text-white">Adoption Trends</h2>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-[#199CA4]/10 dark:bg-[#199CA4]/25 text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20 dark:border-[#41C1CB]/30 shadow-2xs">
                                {{ $totalYearAdoptions }} Completed in {{ $selectedYear }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 dark:text-slate-400 mt-1">
                            Monthly distribution of finalized and approved adoptions.
                            @if($peakCount > 0)
                                <span class="font-bold text-slate-600 dark:text-slate-300 ml-1">Peak: {{ $peakMonth }} ({{ $peakCount }} {{ Str::plural('adoption', $peakCount) }})</span>
                            @endif
                        </p>
                    </div>

                    {{-- Year Selector Dropdown --}}
                    <div class="flex items-center gap-2">
                        <label for="year-select" class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Year:</label>
                        <select id="year-select" onchange="window.location.href='{{ route('dashboard') }}?year=' + this.value"
                            class="bg-slate-50 dark:bg-[#081215] border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 rounded-xl px-3.5 py-2 focus:ring-4 focus:ring-[#199CA4]/15 focus:border-[#199CA4] focus:outline-none transition shadow-2xs cursor-pointer">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="h-80 relative">
                    <canvas id="adoptionTrendsChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Recent Applications Table --}}
        <div class="bg-white dark:bg-[#0e1d20] rounded-3xl shadow-card border border-slate-200/80 dark:border-slate-800 overflow-hidden relative">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-gradient-to-r from-slate-50/50 via-white to-slate-50/50 dark:from-[#0a171a] dark:via-[#0e1d20] dark:to-[#0a171a]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-sm">📋</div>
                    <h2 class="text-base font-extrabold text-slate-800 dark:text-white">Recent Adoption Applications</h2>
                </div>
                <a href="{{ route('adoption-applications.index') }}" class="text-xs font-extrabold text-[#199CA4] dark:text-[#41C1CB] hover:text-[#146970] dark:hover:text-[#7CD8DF] transition-colors flex items-center gap-1">
                    View All Requests <span class="text-base leading-none">→</span>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-[#091518] border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pet</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Adopter</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @forelse($recentApplications ?? [] as $application)
                            <tr class="table-row-hover text-xs sm:text-sm">
                                <td class="px-6 py-4 whitespace-nowrap text-slate-800 dark:text-white font-bold">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-gradient-to-br from-[#F0FBFB] to-[#D6F4F6] dark:from-[#133036] dark:to-[#17454d] text-[#199CA4] dark:text-[#41C1CB] rounded-xl flex items-center justify-center text-xs font-bold shadow-2xs ring-1 ring-[#199CA4]/15 dark:ring-[#41C1CB]/30">
                                            🐾
                                        </div>
                                        <span>Pet #{{ $application->pet_id }} ({{ $application->pet->breed ?? 'Mixed Breed' }})</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-700 dark:text-slate-300">
                                    <div class="font-extrabold text-slate-800 dark:text-white">{{ $application->applicant_name }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5 font-medium">{{ $application->applicant_phone }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-500 dark:text-slate-400 font-medium">
                                    {{ $application->created_at ? $application->created_at->diffForHumans() : '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold border
                                        {{ $application->status === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : '' }}
                                        {{ $application->status === 'rejected' ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800' : '' }}
                                        {{ $application->status === 'pending' ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800' : '' }}
                                        {{ !in_array($application->status, ['approved', 'rejected', 'pending']) ? 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700' : '' }}">
                                        <span class="w-1.5 h-1.5 rounded-full
                                            {{ $application->status === 'approved' ? 'bg-emerald-500' : '' }}
                                            {{ $application->status === 'rejected' ? 'bg-rose-500' : '' }}
                                            {{ $application->status === 'pending' ? 'bg-amber-500' : '' }}
                                            {{ !in_array($application->status, ['approved', 'rejected', 'pending']) ? 'bg-slate-400' : '' }}"></span>
                                        {{ ucfirst(str_replace('_', ' ', $application->status ?? 'pending')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <a href="{{ route('adoption-applications.show', $application) }}" class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] hover:bg-[#199CA4] hover:text-white font-extrabold text-xs transition duration-200 shadow-2xs border border-[#199CA4]/20 dark:border-[#41C1CB]/30">
                                        Review →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                    <div class="text-3xl mb-2">📋</div>
                                    <p class="font-bold text-slate-600 dark:text-slate-300 mb-0.5">No adoption applications yet</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">New requests submitted from the mobile app will automatically appear here.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Chart.js Script --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('adoptionTrendsChart');
            if (!ctx) return;

            const months = @json($chartMonths);
            const counts = @json($chartCounts);

            function getChartColors() {
                const isDark = document.documentElement.classList.contains('dark');
                return {
                    gridColor: isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(226, 232, 240, 0.6)',
                    tickColor: isDark ? '#64748b' : '#94a3b8',
                    pointBorderColor: isDark ? '#0e1d20' : '#ffffff',
                };
            }

            const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(25, 156, 164, 0.35)');
            gradient.addColorStop(1, 'rgba(25, 156, 164, 0.00)');

            const colors = getChartColors();

            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Completed Adoptions',
                        data: counts,
                        borderColor: '#199CA4',
                        borderWidth: 2.5,
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.38,
                        pointBackgroundColor: '#199CA4',
                        pointBorderColor: colors.pointBorderColor,
                        pointBorderWidth: 2.5,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointHoverBackgroundColor: '#14838B',
                        pointHoverBorderColor: '#ffffff',
                        pointHoverBorderWidth: 3,
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
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: '#0c242a',
                            titleColor: '#ffffff',
                            bodyColor: '#41C1CB',
                            titleFont: { size: 12, weight: 'bold' },
                            bodyFont: { size: 12, weight: 'bold' },
                            padding: 10,
                            cornerRadius: 12,
                            displayColors: false,
                            callbacks: {
                                label: function (context) {
                                    const val = context.parsed.y;
                                    return val + ' ' + (val === 1 ? 'Adoption' : 'Adoptions');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                            ticks: {
                                color: colors.tickColor,
                                font: { size: 11, weight: '600' }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            suggestedMax: Math.max(...counts, 4) + 1,
                            ticks: {
                                stepSize: 1,
                                precision: 0,
                                color: colors.tickColor,
                                font: { size: 11, weight: '600' }
                            },
                            grid: {
                                color: colors.gridColor,
                                strokeDash: [4, 4],
                            },
                            border: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Reactively update Chart colors on theme change
            window.addEventListener('theme-changed', function () {
                const c = getChartColors();
                chart.options.scales.x.ticks.color = c.tickColor;
                chart.options.scales.y.ticks.color = c.tickColor;
                chart.options.scales.y.grid.color = c.gridColor;
                chart.data.datasets[0].pointBorderColor = c.pointBorderColor;
                chart.update();
            });
        });
    </script>
</x-app-layout>