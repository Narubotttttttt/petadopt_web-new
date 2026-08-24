<x-app-layout>
    <div class="w-full py-6 sm:py-8 px-4 sm:px-6 lg:px-8 animate-fade-in space-y-6">
        
        {{-- Header & Actions --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Rescue Pets Directory</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Manage and track all rescued dogs and cats in the CAWS database.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <form method="GET" action="{{ route('pets.index') }}" class="flex items-center gap-2">
                    <div class="relative">
                        <input name="q" value="{{ old('q', request('q')) }}" placeholder="Search by breed, color..." class="pl-9 pr-4 py-2.5 bg-white dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-xs sm:text-sm w-48 sm:w-64 focus:outline-none focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] shadow-2xs transition font-semibold text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl" />
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <button type="submit" class="px-5 py-2.5 bg-[#199CA4] hover:bg-[#13787F] text-white font-extrabold rounded-xl text-xs sm:text-sm shadow-xs transition-all cursor-pointer">Search</button>
                </form>
                @if(in_array(Auth::user()->role, ['admin', 'staff']))
                    <a href="{{ route('pets.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white text-xs sm:text-sm font-extrabold shadow-xs transition-all duration-200">
                        <span>+</span> Add New Pet
                    </a>
                @endif
            </div>
        </div>

        {{-- Table Container Card --}}
        <div class="bg-white dark:bg-[#12141C] rounded-2xl sm:rounded-3xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-slate-200/80 dark:border-white/[0.07] overflow-hidden relative">
            <div class="p-6 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between bg-slate-50/50 dark:bg-[#171923]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08] flex items-center justify-center font-bold text-sm">🐾</div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white">Pet Profiles</h2>
                </div>
                <span class="text-xs text-slate-600 dark:text-slate-300 font-extrabold uppercase tracking-wider bg-slate-100 dark:bg-white/[0.06] px-3 py-1 rounded-full border border-slate-200 dark:border-white/[0.08]">Total: {{ $pets->total() }} Pets</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-[#171923] border-b border-slate-200/80 dark:border-white/[0.06]">
                        <tr>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Pet</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Breed</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Color</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Added</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Type</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                            <th class="px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                        @forelse($pets as $pet)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-[#181A24] text-xs sm:text-sm transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-[#171923] overflow-hidden flex items-center justify-center border border-slate-200 dark:border-white/[0.08] shadow-2xs shrink-0 group relative">
                                            @if($pet->photo_path)
                                                <img src="{{ asset('storage/'.$pet->photo_path) }}" alt="Pet photo" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="hidden w-full h-full bg-slate-100 dark:bg-[#171923] items-center justify-center text-xl text-slate-400">
                                                    🐾
                                                </div>
                                            @else
                                                <span class="text-xl">🐾</span>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-extrabold text-slate-900 dark:text-white">Pet #{{ $pet->id }}</p>
                                            <p class="text-[11px] text-slate-400 dark:text-slate-500 font-semibold">{{ ucfirst($pet->gender ?? 'Unknown') }} • {{ $pet->age ?? 'Age N/A' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-700 dark:text-slate-300 font-bold">{{ $pet->breed ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-400 font-medium">{{ $pet->color ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-500 dark:text-slate-400 font-medium">{{ $pet->created_at->diffForHumans() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 font-bold text-slate-700 dark:text-slate-300">
                                        @if(strtolower($pet->type) === 'dog') 🐕 @elseif(strtolower($pet->type) === 'cat') 🐈 @endif
                                        {{ ucfirst($pet->type ?? 'Unknown') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusStyles = [
                                            'available' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60',
                                            'pending' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60',
                                            'adopted' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60',
                                        ];
                                        $dotColors = [
                                            'available' => 'bg-emerald-500',
                                            'pending' => 'bg-amber-500',
                                            'adopted' => 'bg-indigo-500',
                                        ];
                                        $style = $statusStyles[$pet->status] ?? 'bg-slate-50 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 border-slate-200 dark:border-white/[0.08]';
                                        $dot = $dotColors[$pet->status] ?? 'bg-slate-400';
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold border {{ $style }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dot }} animate-pulse"></span>
                                        {{ ucfirst($pet->status ?? 'Unknown') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('pets.show', $pet) }}" class="px-3.5 py-1.5 rounded-xl bg-slate-900 dark:bg-white/[0.08] text-white dark:text-slate-200 hover:bg-slate-800 dark:hover:bg-white/[0.12] border border-slate-800 dark:border-white/[0.08] font-extrabold transition-all duration-200 shadow-2xs text-xs">View</a>
                                        @if(in_array(Auth::user()->role, ['admin', 'staff']))
                                            <a href="{{ route('pets.edit', $pet) }}" class="px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#171923] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#1D1F2C] hover:text-[#199CA4] dark:hover:text-white font-bold transition-all text-xs">Edit</a>
                                            <form action="{{ route('pets.destroy', $pet) }}" method="POST" onsubmit="return confirm('Delete this pet?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 font-bold hover:bg-rose-100 dark:hover:bg-rose-900/60 transition-colors text-xs cursor-pointer">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500">
                                    <div class="text-3xl mb-2">🐾</div>
                                    <p class="font-bold text-slate-600 dark:text-slate-300 mb-1">No pets found</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">Try adjusting your search query or add a new rescue pet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100 dark:border-white/[0.06] bg-slate-50/50 dark:bg-[#171923]">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Showing {{ $pets->firstItem() ?? 0 }} to {{ $pets->lastItem() ?? 0 }} of {{ $pets->total() }} pets</p>
                    <div>
                        {{ $pets->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>