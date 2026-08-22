<x-app-layout>
    <div class="w-full py-8 px-4 sm:px-6 lg:px-8 animate-fade-in space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">Medical & Vaccination Logs</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Vaccination, deworming, checkups, and surgical history of rescued pets.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <form method="GET" action="{{ route('medical-logs.index') }}" class="flex items-center gap-2">
                    <div class="relative">
                        <input name="q" value="{{ old('q', request('q')) }}" placeholder="Search by pet..." class="pl-9 pr-4 py-2 bg-white dark:bg-[#12272b] border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-xs sm:text-sm w-48 sm:w-60 focus:outline-none focus:ring-4 focus:ring-[#199CA4]/15 focus:border-[#199CA4] shadow-2xs transition" />
                        <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-[#199CA4] hover:bg-[#146970] text-white font-bold rounded-xl text-xs sm:text-sm shadow-xs transition-colors cursor-pointer">Search</button>
                </form>
                <a href="{{ route('medical-logs.create') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-[#199CA4] to-[#14838B] text-white text-xs sm:text-sm font-bold shadow-md shadow-[#199CA4]/25 hover:from-[#146970] hover:to-[#12585e] transition-all duration-200">
                    <span>+</span> Add Entry
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 px-4 py-3 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 rounded-2xl flex items-center gap-2 text-sm font-semibold shadow-2xs">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-[#0e1d20] rounded-2xl shadow-card border border-slate-200/80 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-[#091518] border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Pet</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Date</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Category</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Administered By</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Next Due</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-[#12272b]/50 transition-colors text-xs sm:text-sm">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('pets.show', $log->pet) }}" class="font-extrabold text-[#199CA4] dark:text-[#41C1CB] hover:underline">
                                        {{ $log->pet->name ?? 'Pet #'.$log->pet->id }}
                                    </a>
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium mt-0.5">{{ ucfirst($log->pet->type ?? '') }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300 font-medium">{{ $log->date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $categoryStyles = [
                                            'vaccination' => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
                                            'deworming' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800',
                                            'treatment' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                            'checkup' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                            'surgery' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                                            'injury_illness' => 'bg-orange-50 dark:bg-orange-950/60 text-orange-700 dark:text-orange-300 border-orange-200 dark:border-orange-800',
                                        ];
                                        $style = $categoryStyles[$log->category] ?? 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700';
                                    @endphp
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold border {{ $style }}">
                                        {{ ucfirst(str_replace('_', ' ', $log->category)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300 font-medium">{{ $log->administered_by ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($log->next_due_date)
                                        <span class="text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/60 px-2 py-0.5 rounded-md border border-amber-100 dark:border-amber-800">{{ $log->next_due_date->format('M d, Y') }}</span>
                                    @else
                                        <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('medical-logs.edit', $log) }}" class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition text-xs">Edit</a>
                                        @if(Auth::user()->role === 'admin')
                                            <form action="{{ route('medical-logs.destroy', $log) }}" method="POST" onsubmit="return confirm('Delete this medical log entry?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 font-bold hover:bg-rose-100 dark:hover:bg-rose-900/60 transition text-xs cursor-pointer">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500">
                                    <div class="text-3xl mb-2">🩺</div>
                                    No medical log entries yet. Use the button above to add one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-[#091518]">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} entries</p>
                    <div>
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>