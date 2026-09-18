<x-app-layout>
    <div class="w-full min-h-[calc(100vh-4rem)] lg:h-[calc(100vh-4rem)] flex flex-col p-4 sm:p-5 lg:p-6 gap-3.5 sm:gap-4 animate-fade-in overflow-y-auto lg:overflow-hidden box-border">
        
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5 shrink-0">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ Auth::user()?->role === 'admin' ? 'Admin Dashboard' : 'Staff Dashboard' }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Welcome back, {{ Auth::user()?->name }}! Overview of pets, adoption applications, and medical records.</p>
            </div>
        </div>

        {{-- Metric Cards Row (2 columns on mobile, 4 columns on desktop) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-3.5 lg:gap-4 shrink-0">
            
            {{-- Total Pets --}}
            <div class="bg-white dark:bg-[#12141C] p-3.5 sm:p-4 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex justify-between items-center min-w-0">
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] sm:text-[11px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-500 truncate">Available Pets</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white my-0.5 tracking-tight">{{ $totalPets }}</h3>
                    <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5 truncate">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#199CA4] shrink-0"></span>
                        <span class="truncate">Active in catalog</span>
                    </p>
                </div>
                <div class="p-2.5 bg-slate-100 dark:bg-white/[0.06] rounded-xl text-[#199CA4] dark:text-[#41C1CB] border border-slate-200 dark:border-white/[0.08] shadow-2xs shrink-0 ms-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>

            {{-- Approved Adoptions --}}
            <div class="bg-white dark:bg-[#12141C] p-3.5 sm:p-4 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex justify-between items-center min-w-0">
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] sm:text-[11px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-500 truncate">Approved Adoptions</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white my-0.5 tracking-tight">{{ $totalAdoptions }}</h3>
                    <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5 truncate">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#199CA4] shrink-0"></span>
                        <span class="truncate">Total finalized</span>
                    </p>
                </div>
                <div class="p-2.5 bg-slate-100 dark:bg-white/[0.06] rounded-xl text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-white/[0.08] shadow-2xs shrink-0 ms-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            {{-- Total Users --}}
            <div class="bg-white dark:bg-[#12141C] p-3.5 sm:p-4 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex justify-between items-center min-w-0">
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] sm:text-[11px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-500 truncate">Total Users</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white my-0.5 tracking-tight">{{ $totalUsers }}</h3>
                    <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5 truncate">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500 shrink-0"></span>
                        <span class="truncate">Authorized staff</span>
                    </p>
                </div>
                <div class="p-2.5 bg-slate-100 dark:bg-white/[0.06] rounded-xl text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-white/[0.08] shadow-2xs shrink-0 ms-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>

            {{-- Latest Pet Card --}}
            <a href="{{ $latestPet ? route('pets.show', $latestPet) : route('pets.index') }}" 
               class="group bg-gradient-to-br from-[#0f4d52] via-[#146970] to-[#199CA4] hover:from-[#0d3f43] hover:to-[#178a91] text-white p-3.5 sm:p-4 rounded-2xl shadow-md shadow-[#199CA4]/25 dark:shadow-[#199CA4]/20 border border-[#199CA4]/40 flex justify-between items-center transition-all duration-300 card-hover-effect min-w-0">
                <div class="min-w-0 flex-1">
                    <span class="inline-block px-2 py-0.5 rounded bg-white/20 text-[10px] sm:text-[11px] uppercase tracking-wider font-extrabold text-teal-50 mb-1 backdrop-blur-xs border border-white/15">Latest Pet</span>

                    @if($latestPet)
                        <h3 class="text-lg sm:text-xl font-extrabold my-0.5 text-white tracking-tight truncate">Pet no. {{ $latestPet->id }}</h3>
                        <p class="text-xs text-teal-100 font-medium truncate">{{ ucfirst($latestPet->type ?? '') }} • {{ $latestPet->breed ?? '—' }}</p>
                        <p class="text-[11px] text-teal-200/90 truncate mt-0.5">Added {{ $latestPet->created_at->diffForHumans() }}</p>
                    @else
                        <h3 class="text-lg sm:text-xl font-extrabold my-0.5 text-white">No Pets</h3>
                        <p class="text-xs text-teal-200">Catalog empty</p>
                    @endif
                </div>
                <div class="p-2.5 bg-white/15 group-hover:bg-white/25 rounded-xl text-white transition-colors duration-200 border border-white/15 shadow-inner shrink-0 ms-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </a>

        </div>

        {{-- Side-by-Side Main Section: Adoption Trends Chart (Left 7 cols) & Recent Requests (Right 5 cols) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 sm:gap-4 lg:gap-5 items-stretch flex-1 min-h-0">
            
            {{-- Adoption Trends Chart Card (7 cols) --}}
            <div class="lg:col-span-7 bg-white dark:bg-[#12141C] rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] p-4 sm:p-5 flex flex-col lg:h-full min-h-0">
                <div class="flex items-center justify-between gap-2 mb-2 shrink-0">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Adoption Trends</h2>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#199CA4]/10 dark:bg-white/[0.06] text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20 dark:border-white/[0.08]">
                                {{ $totalYearAdoptions }} in {{ $selectedYear }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Monthly finalized adoptions.
                            @if($peakCount > 0)
                                <span class="font-bold text-slate-700 dark:text-slate-300">Peak: {{ $peakMonth }} ({{ $peakCount }})</span>
                            @endif
                        </p>
                    </div>

                    {{-- Year Selector Dropdown --}}
                    <div class="flex items-center gap-1.5 shrink-0">
                        <label for="year-select" class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Year:</label>
                        <select id="year-select" onchange="window.location.href='{{ route('dashboard') }}?year=' + this.value"
                            class="bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-xs font-bold text-slate-700 dark:text-slate-200 rounded-lg px-2 py-1 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition cursor-pointer">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Chart Canvas Container: responsive explicit mobile height with desktop flex-fill --}}
                <div class="relative w-full h-[240px] sm:h-[280px] lg:h-auto lg:flex-1 lg:min-h-0 pt-2">
                    <div class="absolute inset-0">
                        <canvas id="adoptionTrendsChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Recent Adoption Applications (5 cols) --}}
            <div class="lg:col-span-5 bg-white dark:bg-[#12141C] rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] flex flex-col lg:h-full min-h-0 overflow-hidden">
                <div class="p-3.5 sm:p-4 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between bg-slate-50/50 dark:bg-[#171923] shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-[#199CA4]/10 dark:bg-white/[0.06] text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Recent Requests</h2>
                    </div>
                    <a href="{{ route('adoption-applications.index') }}" class="text-xs font-bold text-[#199CA4] hover:text-[#13787F] dark:text-[#41C1CB] dark:hover:underline transition-colors">
                        View All
                    </a>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-white/[0.06] overflow-y-auto max-h-[340px] sm:max-h-[380px] lg:max-h-none lg:flex-1 lg:min-h-0">
                    @forelse($recentApplications ?? [] as $application)
                        <div class="px-3.5 py-2.5 sm:px-4 sm:py-3 hover:bg-slate-50/70 dark:hover:bg-[#181A24] transition-colors flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                @php
                                    $adopterAvatar = $application->user?->avatar_url;
                                    $nameParts = preg_split('/\s+/', trim($application->applicant_name ?? 'Adopter'));
                                    $initials = count($nameParts) >= 2 
                                        ? strtoupper(mb_substr($nameParts[0], 0, 1) . mb_substr(end($nameParts), 0, 1))
                                        : strtoupper(mb_substr($application->applicant_name ?? 'A', 0, 1));
                                @endphp
                                @if($adopterAvatar)
                                    <img src="{{ $adopterAvatar }}" alt="{{ $application->applicant_name }}" class="w-8 h-8 rounded-xl object-cover shrink-0 border border-slate-200 dark:border-white/[0.08]" />
                                @else
                                    <div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-white/[0.06] text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center text-xs font-bold shrink-0 border border-slate-200 dark:border-white/[0.08]">
                                        {{ $initials }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate">{{ $application->applicant_name }}</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">Pet no. {{ $application->pet_id }} ({{ $application->pet->name ?? ($application->pet->breed ?? 'Pet') }})</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold tracking-wide border
                                    {{ $application->status === 'approved' ? 'bg-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB] border-[#199CA4]/30' : '' }}
                                    {{ $application->status === 'rejected' ? 'bg-slate-100 dark:bg-white/[0.06] text-slate-500 dark:text-slate-400 border-slate-200 dark:border-white/[0.08]' : '' }}
                                    {{ $application->status === 'pending' ? 'bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 border-slate-300 dark:border-white/[0.12]' : '' }}
                                    {{ !in_array($application->status, ['approved', 'rejected', 'pending']) ? 'bg-slate-50 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 border-slate-200 dark:border-white/[0.08]' : '' }}">
                                    {{ ucfirst($application->status ?? 'pending') }}
                                </span>
                                <a href="{{ route('adoption-applications.show', $application) }}" class="text-xs font-bold text-[#199CA4] hover:text-[#13787F] dark:text-[#41C1CB] dark:hover:underline transition-colors">
                                    View
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 dark:text-slate-500">
                            <p class="text-xs sm:text-sm font-bold text-slate-600 dark:text-slate-400">No requests yet</p>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">New applications will appear here.</p>
                        </div>
                    @endforelse
                </div>
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
                    gridColor: isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(226, 232, 240, 0.6)',
                    tickColor: isDark ? '#64748b' : '#94a3b8',
                    pointBorderColor: isDark ? '#12141C' : '#ffffff',
                };
            }

            const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 480);
            gradient.addColorStop(0, 'rgba(25, 156, 164, 0.35)');
            gradient.addColorStop(1, 'rgba(25, 156, 164, 0.00)');

            const colors = getChartColors();

            // Adoption Trends Line Chart
            const adoptionChart = new Chart(ctx, {
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
                        pointRadius: 4,
                        pointHoverRadius: 6,
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
                            backgroundColor: '#12141C',
                            borderColor: 'rgba(255,255,255,0.1)',
                            borderWidth: 1,
                            titleColor: '#ffffff',
                            bodyColor: '#41C1CB',
                            titleFont: { size: 11, weight: 'bold' },
                            bodyFont: { size: 11, weight: '600' },
                            padding: 8,
                            cornerRadius: 10,
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
                                strokeDash: [3, 3],
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
                
                if (adoptionChart) {
                    adoptionChart.options.scales.x.ticks.color = c.tickColor;
                    adoptionChart.options.scales.y.ticks.color = c.tickColor;
                    adoptionChart.options.scales.y.grid.color = c.gridColor;
                    adoptionChart.data.datasets[0].pointBorderColor = c.pointBorderColor;
                    adoptionChart.update();
                }

            });
        });
    </script>
</x-app-layout>