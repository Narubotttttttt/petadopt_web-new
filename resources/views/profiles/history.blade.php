<x-app-layout>
    <div class="w-full py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#333634] dark:text-white tracking-tight">Adopter History</h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-slate-400 mt-1">Past and current adoption applications for {{ $user->name }}.</p>
            </div>
            <a href="{{ route('adoption-applications.index') }}" class="inline-flex items-center rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-[#0e1d20] px-4 py-2 text-xs sm:text-sm font-bold text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-800 transition shadow-2xs">Back to requests</a>
        </div>

        <div class="bg-white dark:bg-[#0e1d20] rounded-2xl sm:rounded-3xl border border-gray-100 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-gray-50/80 dark:bg-[#091518] border-b border-gray-100 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400">Pet</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400">Status</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400">Requested</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400">Scheduled</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 text-right">View</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                        @forelse($applications as $application)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-[#12272b]/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">{{ $application->pet->name ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ ucfirst(str_replace('_', ' ', $application->status)) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ $application->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ $application->scheduled_at ? $application->scheduled_at->format('M d, Y H:i') : 'Not scheduled' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('adoption-applications.show', $application) }}" class="text-[#199CA4] dark:text-[#41C1CB] hover:text-[#13787F] dark:hover:text-[#199CA4] font-bold">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-slate-400">No adoption history available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($applications->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-slate-800 bg-gray-50/50 dark:bg-[#091518]">
                    {{ $applications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
