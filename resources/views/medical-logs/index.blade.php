<x-app-layout>
    <div class="w-full py-6 sm:py-8 px-4 sm:px-6 lg:px-8 animate-fade-in space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Medical & Vaccination Logs</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Vaccination, deworming, checkups, and surgical history of rescued pets.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <form method="GET" action="{{ route('medical-logs.index') }}" class="flex items-center gap-2">
                    <div class="relative">
                        <input name="q" value="{{ old('q', request('q')) }}" placeholder="Search by pet..." class="pl-9 pr-4 py-2 bg-white dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-xl text-xs sm:text-sm w-48 sm:w-60 focus:outline-none focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] shadow-2xs transition" />
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-[#199CA4] hover:bg-[#13787F] text-white font-bold rounded-xl text-xs sm:text-sm shadow-xs transition-colors cursor-pointer">Search</button>
                </form>
                <a href="{{ route('medical-logs.create') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white text-xs sm:text-sm font-bold shadow-xs transition-all duration-200">
                    <span>+</span> Add Entry
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 px-4 py-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-700 dark:text-emerald-300 rounded-2xl flex items-center gap-2 text-sm font-semibold shadow-2xs">
                
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-[#12141C] rounded-2xl sm:rounded-3xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-[#171923] border-b border-slate-200/80 dark:border-white/[0.06]">
                        <tr>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Pet</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Date</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Category</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Administered By</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Next Due</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-[#181A24] transition-colors text-xs sm:text-sm">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('pets.show', $log->pet) }}" class="font-extrabold text-slate-900 dark:text-white hover:text-[#199CA4] dark:hover:text-teal-400 hover:underline">
                                        {{ $log->pet->name ?? 'Pet no. '.$log->pet->id }}
                                    </a>
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium mt-0.5">{{ ucfirst($log->pet->type ?? '') }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300 font-medium">{{ $log->date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $categoryStyles = [
                                            'vaccination' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60',
                                            'deworming' => 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800/60',
                                            'treatment' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60',
                                            'checkup' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60',
                                            'surgery' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60',
                                            'injury_illness' => 'bg-orange-50 dark:bg-orange-950/40 text-orange-700 dark:text-orange-300 border-orange-200 dark:border-orange-800/60',
                                        ];
                                        $style = $categoryStyles[$log->category] ?? 'bg-slate-50 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 border-slate-200 dark:border-white/[0.08]';
                                    @endphp
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold border {{ $style }}">
                                        {{ ucfirst(str_replace('_', ' ', $log->category)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300 font-medium">{{ $log->administered_by ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($log->next_due_date)
                                        <span class="text-xs font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-md border border-amber-200 dark:border-amber-800/60">{{ $log->next_due_date->format('M d, Y') }}</span>
                                    @else
                                        <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('medical-logs.edit', $log) }}" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#1D1F2C] font-bold transition text-xs shadow-2xs">Edit</a>
                                        @if(Auth::user()?->role === 'admin')
                                            <form action="{{ route('medical-logs.destroy', $log) }}" method="POST" onsubmit="return confirm('Delete this medical log entry?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 font-bold hover:bg-rose-100 dark:hover:bg-rose-900/60 transition text-xs cursor-pointer">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500">
                                    <p class="font-bold text-sm text-slate-600 dark:text-slate-300">No medical log entries yet</p>
                                    <p class="text-xs text-slate-400 mt-1">Use the "+ Add Entry" button above to record clinical logs.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100 dark:border-white/[0.06] bg-slate-50/50 dark:bg-[#171923]">
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