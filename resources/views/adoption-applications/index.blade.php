<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-[#333634]">Adoption Requests</h1>
                <p class="text-sm text-gray-500 mt-1">Review incoming applications from mobile adopters and schedule meet-and-greets.</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-gray-50/80 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Pet</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Applicant</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Submitted Date</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($applications as $application)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    {{ $application->pet->name ?: ($application->pet ? $application->pet->breed . ' (Pet #' . $application->pet->id . ')' : 'Pet #' . $application->pet_id) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <div class="font-semibold text-gray-900">{{ $application->applicant_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $application->applicant_phone }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold 
                                        {{ $application->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                        {{ $application->status === 'rejected' ? 'bg-rose-100 text-rose-800' : '' }}
                                        {{ $application->status === 'under_review' ? 'bg-sky-100 text-sky-800' : '' }}
                                        {{ $application->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $application->created_at ? $application->created_at->format('M d, Y · h:i A') : '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-1.5">
                                    @if(in_array($application->status, ['approved', 'adopted']))
                                        <a href="{{ route('adoption-applications.contract', $application) }}" target="_blank" title="Print Contract" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 font-bold hover:bg-emerald-600 hover:text-white transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                            <span>Print</span>
                                        </a>
                                    @endif
                                    <a href="{{ route('adoption-applications.show', $application) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-[#EAF5F6] text-[#199CA4] font-bold hover:bg-[#199CA4] hover:text-white transition">View Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No adoption applications submitted yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100">
                {{ $applications->links() }}
            </div>
        </div>
    </div>
</x-app-layout>