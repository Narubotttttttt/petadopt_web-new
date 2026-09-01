<x-app-layout>
    <div class="w-full py-4 sm:py-5 px-4 sm:px-6 lg:px-8 space-y-4 animate-fade-in">
        
        {{-- Page Header (Compact) --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ Auth::user()?->role === 'admin' ? 'Admin Dashboard' : 'Staff Dashboard' }}
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Welcome back, {{ Auth::user()?->name }}! Overview of pets, adoption applications, and medical records.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white dark:bg-[#12141C] border border-slate-200/80 dark:border-white/[0.08] shadow-2xs text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ now()->format('l, F j, Y') }}
                </span>
            </div>
        </div>

        {{-- Metric Cards Row --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            
            {{-- Total Pets --}}
            <div class="bg-white dark:bg-[#12141C] p-4 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex justify-between items-center">
                <div>
                    <p class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-500">Total Pets</p>
                    <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white my-0.5 tracking-tight">{{ $totalPets }}</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#199CA4]"></span>
                        Active in catalog
                    </p>
                </div>
                <div class="p-2.5 bg-slate-100 dark:bg-white/[0.06] rounded-xl text-[#199CA4] dark:text-[#41C1CB] border border-slate-200 dark:border-white/[0.08] shadow-2xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>

            {{-- Approved Adoptions --}}
            <div class="bg-white dark:bg-[#12141C] p-4 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex justify-between items-center">
                <div>
                    <p class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-500">Approved Adoptions</p>
                    <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white my-0.5 tracking-tight">{{ $totalAdoptions }}</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Total finalized
                    </p>
                </div>
                <div class="p-2.5 bg-slate-100 dark:bg-white/[0.06] rounded-xl text-emerald-600 dark:text-emerald-400 border border-slate-200 dark:border-white/[0.08] shadow-2xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            {{-- Total Users --}}
            <div class="bg-white dark:bg-[#12141C] p-4 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] card-hover-effect flex justify-between items-center">
                <div>
                    <p class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400 dark:text-slate-500">Total Users</p>
                    <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white my-0.5 tracking-tight">{{ $totalUsers }}</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                        Authorized staff
                    </p>
                </div>
                <div class="p-2.5 bg-slate-100 dark:bg-white/[0.06] rounded-xl text-indigo-600 dark:text-indigo-400 border border-slate-200 dark:border-white/[0.08] shadow-2xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>

            {{-- Latest Pet Card (Vibrant Teal in both Light & Dark Theme) --}}
            <a href="{{ $latestPet ? route('pets.show', $latestPet) : route('pets.index') }}" 
               class="group bg-gradient-to-br from-[#0f4d52] via-[#146970] to-[#199CA4] hover:from-[#0d3f43] hover:to-[#178a91] text-white p-4 rounded-2xl shadow-md shadow-[#199CA4]/25 dark:shadow-[#199CA4]/20 border border-[#199CA4]/40 flex justify-between items-center transition-all duration-300 card-hover-effect">
                <div>
                    <span class="inline-block px-2 py-0.5 rounded bg-white/20 text-[9px] uppercase tracking-wider font-extrabold text-teal-50 mb-0.5 backdrop-blur-xs border border-white/15">Latest Pet</span>

                    @if($latestPet)
                        <h3 class="text-xl font-extrabold my-0.5 text-white tracking-tight">Pet no. {{ $latestPet->id }}</h3>
                        <p class="text-[11px] text-teal-100 font-medium truncate max-w-[130px]">{{ ucfirst($latestPet->type ?? '') }} • {{ $latestPet->breed ?? '—' }}</p>
                        <p class="text-[10px] text-teal-200/90">Added {{ $latestPet->created_at->diffForHumans() }}</p>
                    @else
                        <h3 class="text-xl font-extrabold my-0.5 text-white">No Pets</h3>
                        <p class="text-[11px] text-teal-200">Catalog empty</p>
                    @endif
                </div>
                <div class="p-2.5 bg-white/15 group-hover:bg-white/25 rounded-xl text-white transition-colors duration-200 border border-white/15 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </a>

        </div>

        {{-- Side-by-Side Main Section: Adoption Trends Chart (Left 7 cols) & Recent Requests (Right 5 cols) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-stretch">
            
            {{-- Adoption Trends Chart Card (7 cols) --}}
            <div class="lg:col-span-7 bg-white dark:bg-[#12141C] rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] p-4 sm:p-5 flex flex-col justify-between">
                <div class="flex items-center justify-between gap-2 mb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white">Adoption Trends</h2>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#199CA4]/10 dark:bg-white/[0.06] text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20 dark:border-white/[0.08]">
                                {{ $totalYearAdoptions }} in {{ $selectedYear }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 dark:text-slate-400 mt-0.5">
                            Monthly finalized adoptions.
                            @if($peakCount > 0)
                                <span class="font-bold text-slate-600 dark:text-slate-300">Peak: {{ $peakMonth }} ({{ $peakCount }})</span>
                            @endif
                        </p>
                    </div>

                    {{-- Year Selector Dropdown --}}
                    <div class="flex items-center gap-1.5">
                        <label for="year-select" class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Year:</label>
                        <select id="year-select" onchange="window.location.href='{{ route('dashboard') }}?year=' + this.value"
                            class="bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-xs font-bold text-slate-700 dark:text-slate-200 rounded-lg px-2.5 py-1 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition cursor-pointer">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Chart Canvas --}}
                <div class="h-56 sm:h-60 relative w-full">
                    <canvas id="adoptionTrendsChart"></canvas>
                </div>
            </div>

            {{-- Recent Adoption Applications (5 cols) --}}
            <div class="lg:col-span-5 bg-white dark:bg-[#12141C] rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] flex flex-col justify-between overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between bg-slate-50/50 dark:bg-[#171923]">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-[#199CA4]/10 dark:bg-white/[0.06] text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-xs"></div>
                        <h2 class="text-sm font-extrabold text-slate-900 dark:text-white">Recent Requests</h2>
                    </div>
                    <a href="{{ route('adoption-applications.index') }}" class="text-xs font-bold text-[#199CA4] hover:text-[#13787F] dark:text-slate-400 dark:hover:text-white transition-colors">
                        View All →
                    </a>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-white/[0.06] overflow-y-auto max-h-[260px] flex-1">
                    @forelse($recentApplications ?? [] as $application)
                        <div class="p-3 hover:bg-slate-50/70 dark:hover:bg-[#181A24] transition-colors flex items-center justify-between gap-2.5">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-white/[0.06] text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center text-xs font-bold shrink-0 border border-slate-200 dark:border-white/[0.08]">
                                    
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $application->applicant_name }}</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">Pet no. {{ $application->pet_id }} ({{ $application->pet->name ?? ($application->pet->breed ?? 'Pet') }})</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold border
                                    {{ $application->status === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60' : '' }}
                                    {{ $application->status === 'rejected' ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60' : '' }}
                                    {{ $application->status === 'pending' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60' : '' }}
                                    {{ !in_array($application->status, ['approved', 'rejected', 'pending']) ? 'bg-slate-50 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 border-slate-200 dark:border-white/[0.08]' : '' }}">
                                    {{ ucfirst($application->status ?? 'pending') }}
                                </span>
                                <a href="{{ route('adoption-applications.show', $application) }}" class="p-1 text-slate-400 hover:text-[#199CA4] dark:hover:text-white transition">
                                    →
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 dark:text-slate-500">
                            <p class="text-xs font-bold text-slate-600 dark:text-slate-400">No requests yet</p>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500">New applications will appear here.</p>
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

            const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 240);
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
                            bodyFont: { size: 11, weight: 'bold' },
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
                                font: { size: 10, weight: '600' }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            suggestedMax: Math.max(...counts, 4) + 1,
                            ticks: {
                                stepSize: 1,
                                precision: 0,
                                color: colors.tickColor,
                                font: { size: 10, weight: '600' }
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
                chart.options.scales.x.ticks.color = c.tickColor;
                chart.options.scales.y.ticks.color = c.tickColor;
                chart.options.scales.y.grid.color = c.gridColor;
                chart.data.datasets[0].pointBorderColor = c.pointBorderColor;
                chart.update();
            });
        });
    </script>
</x-app-layout>