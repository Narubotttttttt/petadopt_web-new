<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-[#333634]">Pets</h1>
                <p class="text-sm text-gray-500 mt-1">All pet profiles added to the system.</p>
            </div>
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('pets.index') }}" class="flex items-center gap-2">
                    <input name="q" value="{{ old('q', request('q')) }}" placeholder="Search pets by name, breed, color..." class="px-3 py-2 border rounded-xl text-sm w-56 focus:outline-none focus:ring-2 focus:ring-[#199CA4]/30" />
                    <button type="submit" class="px-3 py-2 bg-[#199CA4] text-white rounded-xl text-sm">Search</button>
                </form>
                @if(in_array(Auth::user()->role, ['admin', 'staff']))
                    <a href="{{ route('pets.create') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-[#199CA4] text-white text-sm font-semibold shadow-sm hover:bg-[#13787F] transition">
                        + Add New Pet
                    </a>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-bold text-[#333634]">Added Pets</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Pet</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Breed</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Color</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Added</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Type</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($pets as $pet)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-2xl bg-[#199CA4]/10 overflow-hidden flex items-center justify-center">
                                            @if($pet->photo_path)
                                                <img src="{{ asset('storage/'.$pet->photo_path) }}" alt="Pet photo" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-base">🐾</span>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $pet->name ?? 'Pet #'.$pet->id }}</p>
                                            <p class="text-xs text-gray-400">{{ ucfirst($pet->gender ?? 'Unknown') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $pet->breed ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $pet->color ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $pet->created_at->diffForHumans() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($pet->type ?? 'Unknown') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $statusStyles = [
                                            'available' => 'bg-green-50 text-green-700 border-green-100',
                                            'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                                            'adopted' => 'bg-[#199CA4]/10 text-[#199CA4] border-[#199CA4]/20',
                                        ];
                                        $style = $statusStyles[$pet->status] ?? 'bg-gray-50 text-gray-600 border-gray-100';
                                    @endphp
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold border {{ $style }}">
                                        {{ ucfirst($pet->status ?? 'Unknown') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('pets.show', $pet) }}" class="text-sm text-[#199CA4] font-medium">View</a>
                                        @if(in_array(Auth::user()->role, ['admin', 'staff']))
                                            <a href="{{ route('pets.edit', $pet) }}" class="text-sm text-gray-600 hover:text-[#199CA4]">Edit</a>
                                            <form action="{{ route('pets.destroy', $pet) }}" method="POST" onsubmit="return confirm('Delete this pet?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm text-red-600 hover:underline">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                                    No pets have been added yet. Use the button above to add your first pet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500">Showing {{ $pets->firstItem() ?? 0 }} to {{ $pets->lastItem() ?? 0 }} of {{ $pets->total() }} pets</p>
                    <div>
                        {{ $pets->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>