<x-app-layout>
    <div class="max-w-7xl mx-auto py-10 px-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-[#333634]">Medical Logs</h1>
                <p class="text-sm text-gray-500 mt-1">Health records and treatment history for all pets.</p>
            </div>
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('medical-logs.index') }}" class="flex items-center gap-2">
                    <input name="q" value="{{ old('q', request('q')) }}" placeholder="Search by pet..." class="px-3 py-2 border rounded-xl text-sm w-56 focus:outline-none focus:ring-2 focus:ring-[#199CA4]/30" />
                    <button type="submit" class="px-3 py-2 bg-[#199CA4] text-white rounded-xl text-sm">Search</button>
                </form>
                <a href="{{ route('medical-logs.create') }}" class="px-4 py-2 bg-[#199CA4] text-white rounded-xl text-sm font-semibold hover:bg-[#13787F] transition">+ Add Entry</a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-2">
                <span>✅</span>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Pet</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Date</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Category</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Administered By</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Next Due</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('pets.show', $log->pet) }}" class="font-semibold text-[#199CA4] hover:underline">
                                        {{ $log->pet->name ?? 'Pet #'.$log->pet->id }}
                                    </a>
                                    <p class="text-xs text-gray-400">{{ ucfirst($log->pet->type ?? '') }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $categoryStyles = [
                                            'vaccination' => 'bg-blue-50 text-blue-700 border-blue-100',
                                            'deworming' => 'bg-purple-50 text-purple-700 border-purple-100',
                                            'treatment' => 'bg-orange-50 text-orange-700 border-orange-100',
                                            'checkup' => 'bg-green-50 text-green-700 border-green-100',
                                            'surgery' => 'bg-red-50 text-red-700 border-red-100',
                                            'injury_illness' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                                        ];
                                        $style = $categoryStyles[$log->category] ?? 'bg-gray-50 text-gray-600 border-gray-100';
                                    @endphp
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold border {{ $style }}">
                                        {{ ucfirst(str_replace('_', ' ', $log->category)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->administered_by ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->next_due_date ? $log->next_due_date->format('M d, Y') : '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('medical-logs.edit', $log) }}" class="text-sm text-gray-600 hover:text-[#199CA4]">Edit</a>
                                        @if(Auth::user()->role === 'admin')
                                            <form action="{{ route('medical-logs.destroy', $log) }}" method="POST" onsubmit="return confirm('Delete this medical log entry?');">
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
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                                    No medical log entries yet. Use the button above to add one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500">Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} entries</p>
                    <div>
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>