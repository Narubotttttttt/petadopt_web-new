<x-app-layout>
    <div class="max-w-4xl mx-auto py-10 px-4">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#333634]">Application for {{ $application->pet->name ?? 'Pet' }}</h1>
                <p class="text-sm text-gray-500 mt-1">Review the applicant details and update the adoption status.</p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('adoption-applications.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">Back</a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <div class="space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Applicant</h2>
                        <p class="text-sm text-gray-500">{{ $application->applicant_name }} · {{ $application->applicant_email }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-3xl border border-gray-100">
                            <p class="text-xs uppercase tracking-wider text-gray-500">Phone</p>
                            <p class="mt-2 text-sm text-gray-900">{{ $application->applicant_phone ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-3xl border border-gray-100">
                            <p class="text-xs uppercase tracking-wider text-gray-500">Status</p>
                            <p class="mt-2 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $application->status)) }}</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <p class="text-xs uppercase tracking-wider text-gray-500">Message</p>
                        <div class="rounded-3xl border border-gray-100 bg-gray-50 p-4 text-sm text-gray-700">{{ $application->message }}</div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-3xl border border-gray-100">
                            <p class="text-xs uppercase tracking-wider text-gray-500">Pet type</p>
                            <p class="mt-2 text-sm text-gray-900">{{ ucfirst($application->pet->type ?? '—') }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-3xl border border-gray-100">
                            <p class="text-xs uppercase tracking-wider text-gray-500">Adoption date</p>
                            <p class="mt-2 text-sm text-gray-900">{{ $application->scheduled_at ? $application->scheduled_at->format('M d, Y H:i') : 'Not scheduled' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Quick actions</h2>
                    @if(Auth::user()->role === 'admin')
                        <p class="text-sm text-gray-500">Make the final decision or schedule a meet-and-greet.</p>
                    @else
                        <p class="text-sm text-gray-500">Review requirements and forward to admin for final decision.</p>
                    @endif
                </div>

                <form action="{{ route('adoption-applications.update', $application) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Status</label>
                        <select name="status" class="w-full rounded-2xl border border-gray-200 px-4 py-3 bg-white text-sm text-gray-800">
                            <option value="under_review" {{ $application->status == 'under_review' ? 'selected' : '' }}>Under review</option>
                            @if(Auth::user()->role === 'admin')
                                <option value="approved" {{ $application->status == 'approved' ? 'selected' : '' }}>Approved</option>
                            @else
                                <option value="pending" {{ $application->status == 'pending' ? 'selected' : '' }}>Pending admin decision</option>
                            @endif
                            <option value="rejected" {{ $application->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        @if(Auth::user()->role !== 'admin')
                            <p class="text-xs text-gray-400 mt-2">Only an admin can give final approval. Mark as "Pending admin decision" once requirements are complete.</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Schedule</label>
                        <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $application->scheduled_at ? $application->scheduled_at->format('Y-m-d\TH:i') : '') }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 bg-white text-sm text-gray-800">
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#199CA4] px-4 py-3 text-sm font-semibold text-white hover:bg-[#13787F] transition">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>