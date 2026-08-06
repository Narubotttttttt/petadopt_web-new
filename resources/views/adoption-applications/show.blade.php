<x-app-layout>
    <div class="max-w-5xl mx-auto py-10 px-4">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#333634]">
                    Application for {{ $application->pet->name ?: ($application->pet ? $application->pet->breed . ' (Pet #' . $application->pet->id . ')' : 'Pet') }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">Review the applicant details, location verification, and update adoption status.</p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('adoption-applications.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">Back to List</a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <!-- Pet Overview Card -->
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 flex items-center gap-4">
                    @if($application->pet && $application->pet->photo_path)
                        <img src="{{ str_starts_with($application->pet->photo_path, 'http') ? $application->pet->photo_path : asset('storage/' . ltrim($application->pet->photo_path, '/')) }}" class="w-16 h-16 rounded-2xl object-cover border border-gray-100" />
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-[#EAF5F6] text-[#199CA4] flex items-center justify-center text-2xl font-bold">🐾</div>
                    @endif
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">{{ $application->pet->name ?: 'Pet #' . $application->pet->id }}</h3>
                        <p class="text-sm text-gray-500">{{ ucfirst($application->pet->type ?? 'Pet') }} · {{ $application->pet->breed ?? 'Mixed Breed' }} · {{ $application->pet->age ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Applicant Information Card -->
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4">
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <span>👤</span> Applicant Contact Information
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Full Name</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $application->applicant_name }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Email Address</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $application->applicant_email }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Phone Number</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $application->applicant_phone ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Current Status</p>
                            <span class="inline-flex mt-1 items-center px-3 py-1 rounded-full text-xs font-bold 
                                {{ $application->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                {{ $application->status === 'rejected' ? 'bg-rose-100 text-rose-800' : '' }}
                                {{ $application->status === 'under_review' ? 'bg-sky-100 text-sky-800' : '' }}
                                {{ $application->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}">
                                {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Application Details & Questionnaire Card -->
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4">
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <span>📋</span> Mobile Application Questionnaire Responses
                    </h2>
                    
                    @php
                        $lines = explode("\n", $application->message);
                        $reasonText = '';
                        $isReason = false;
                        $questionnaire = [];

                        foreach ($lines as $line) {
                            $line = trim($line);
                            if (empty($line)) continue;
                            if (str_starts_with($line, 'Reason for Adoption:')) {
                                $isReason = true;
                                continue;
                            }
                            if ($isReason) {
                                $reasonText .= ($reasonText ? "\n" : "") . $line;
                            } else {
                                $parts = explode(':', $line, 2);
                                if (count($parts) === 2) {
                                    $questionnaire[trim($parts[0])] = trim($parts[1]);
                                } else {
                                    $questionnaire[] = $line;
                                }
                            }
                        }
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($questionnaire as $key => $val)
                            <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 {{ str_contains(strtolower($key), 'address') || str_contains(strtolower($key), 'other pets') ? 'sm:col-span-2' : '' }}">
                                <p class="text-xs font-semibold uppercase tracking-wider text-[#199CA4]">{{ is_string($key) ? $key : 'Detail' }}</p>
                                <p class="mt-1 text-sm font-bold text-gray-900 leading-relaxed">{{ $val }}</p>
                            </div>
                        @endforeach
                    </div>

                    @if(!empty($reasonText))
                        <div class="bg-[#EAF5F6]/50 p-4 rounded-2xl border border-[#199CA4]/20 space-y-1">
                            <p class="text-xs font-bold uppercase tracking-wider text-[#199CA4]">Reason for Adoption</p>
                            <p class="text-sm font-medium text-gray-800 leading-relaxed whitespace-pre-line">{{ $reasonText }}</p>
                        </div>
                    @endif
                </div>

                <!-- Uploaded Screening Documents Card -->
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4">
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <span>🪪</span> Uploaded Verification Documents
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Valid Government ID -->
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 flex flex-col justify-between space-y-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-[#199CA4]">Valid Government ID</p>
                                <p class="text-xs text-gray-500 mt-0.5">Submitted ID photo</p>
                            </div>
                            @if($application->valid_id_path)
                                <div class="group relative rounded-xl overflow-hidden border border-gray-200 bg-white">
                                    <img src="{{ asset('storage/' . ltrim($application->valid_id_path, '/')) }}" class="w-full h-40 object-cover cursor-pointer hover:scale-105 transition-transform" onclick="openDocModal('{{ asset('storage/' . ltrim($application->valid_id_path, '/')) }}', 'Valid Government ID')" />
                                    <button onclick="openDocModal('{{ asset('storage/' . ltrim($application->valid_id_path, '/')) }}', 'Valid Government ID')" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-bold transition-opacity">🔍 Click to Enlarge</button>
                                </div>
                            @else
                                <div class="p-6 rounded-xl border border-dashed border-gray-200 text-center text-xs text-gray-400">No Valid ID uploaded</div>
                            @endif
                        </div>

                        <!-- Barangay Certificate -->
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 flex flex-col justify-between space-y-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-[#199CA4]">Barangay Certificate</p>
                                <p class="text-xs text-gray-500 mt-0.5">Submitted residency clearance photo</p>
                            </div>
                            @if($application->barangay_certificate_path)
                                <div class="group relative rounded-xl overflow-hidden border border-gray-200 bg-white">
                                    <img src="{{ asset('storage/' . ltrim($application->barangay_certificate_path, '/')) }}" class="w-full h-40 object-cover cursor-pointer hover:scale-105 transition-transform" onclick="openDocModal('{{ asset('storage/' . ltrim($application->barangay_certificate_path, '/')) }}', 'Barangay Certificate')" />
                                    <button onclick="openDocModal('{{ asset('storage/' . ltrim($application->barangay_certificate_path, '/')) }}', 'Barangay Certificate')" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-bold transition-opacity">🔍 Click to Enlarge</button>
                                </div>
                            @else
                                <div class="p-6 rounded-xl border border-dashed border-gray-200 text-center text-xs text-gray-400">No Barangay Certificate uploaded</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Action Sidebar -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-6 h-fit" x-data="{ currentStatus: '{{ old('status', $application->status) }}' }">
                <div>
                    <h2 class="text-base font-bold text-gray-900">Application Status & Actions</h2>
                    @if(Auth::user()->role === 'admin')
                        <p class="text-xs text-gray-500 mt-1">Make the final decision or schedule an adoption event.</p>
                    @else
                        <p class="text-xs text-gray-500 mt-1">Review applicant details and forward to admin for decision.</p>
                    @endif
                </div>

                <form action="{{ route('adoption-applications.update', $application) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">Update Status</label>
                        <select name="status" x-model="currentStatus" class="w-full rounded-2xl border border-gray-200 px-4 py-3 bg-white text-sm text-gray-800 font-semibold focus:ring-2 focus:ring-[#199CA4]">
                            <option value="under_review" {{ $application->status == 'under_review' ? 'selected' : '' }}>Under Review</option>
                            <option value="pending" {{ $application->status == 'pending' ? 'selected' : '' }}>Pending Decision</option>
                            @if(Auth::user()->role === 'admin')
                                <option value="approved" {{ $application->status == 'approved' ? 'selected' : '' }}>Approved</option>
                            @endif
                            <option value="rejected" {{ $application->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <!-- Event Scheduling Details (Only Visible when Approved) -->
                    <div x-show="currentStatus === 'approved'" x-transition class="space-y-4 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">Schedule Sunday Event Date</label>
                            <input type="date" name="scheduled_at" value="{{ old('scheduled_at', $application->scheduled_at ? $application->scheduled_at->format('Y-m-d') : '') }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 bg-white text-sm text-gray-800 font-medium focus:ring-2 focus:ring-[#199CA4]">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">Adoption Event / Mall Location</label>
                            <input type="text" name="event_location" value="{{ old('event_location', $application->event_location) }}" placeholder="e.g. Centrio Mall CDO / SM City CDO" class="w-full rounded-2xl border border-gray-200 px-4 py-3 bg-white text-sm text-gray-800 font-medium focus:ring-2 focus:ring-[#199CA4]">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">Event Instructions & Reminders</label>
                            <textarea name="event_notes" rows="3" placeholder="e.g. Pet release, free anti-rabies vaccination, and spaying/neutering drive." class="w-full rounded-2xl border border-gray-200 px-4 py-3 bg-white text-sm text-gray-800 font-medium focus:ring-2 focus:ring-[#199CA4]">{{ old('event_notes', $application->event_notes) }}</textarea>
                        </div>
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center rounded-2xl bg-[#199CA4] px-4 py-3 text-sm font-bold text-white hover:bg-[#13787F] transition shadow-md shadow-[#199CA4]/20">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Document Modal Previewer -->
    <div id="docModal" class="fixed inset-0 z-50 hidden bg-black/80 flex items-center justify-center p-4" onclick="closeDocModal()">
        <div class="relative max-w-4xl w-full bg-white rounded-3xl overflow-hidden shadow-2xl p-4" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 px-2">
                <h3 id="docModalTitle" class="text-base font-bold text-gray-900">Document Preview</h3>
                <button onclick="closeDocModal()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 font-bold">✕</button>
            </div>
            <div class="py-4 flex justify-center max-h-[75vh] overflow-auto">
                <img id="docModalImage" src="" class="max-w-full max-h-[70vh] rounded-2xl object-contain shadow-md" />
            </div>
        </div>
    </div>

    <script>
        function openDocModal(src, title) {
            document.getElementById('docModalImage').src = src;
            document.getElementById('docModalTitle').innerText = title;
            document.getElementById('docModal').classList.remove('hidden');
        }
        function closeDocModal() {
            document.getElementById('docModal').classList.add('hidden');
        }
    </script>
</x-app-layout>