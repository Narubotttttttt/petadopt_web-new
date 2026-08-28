<x-app-layout>
    <div class="w-full py-8 px-4 sm:px-6 lg:px-8 animate-fade-in">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">
                    Application for Pet no. {{ $application->pet_id }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Review applicant details, verification documents, and update adoption status.</p>
            </div>
            <div class="space-x-2 flex items-center">
                @if(in_array($application->status, ['approved', 'adopted']))
                    <a href="{{ route('adoption-applications.contract', $application) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-xs sm:text-sm font-bold border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-600 hover:text-white transition shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Print Contract</span>
                    </a>
                @endif
                <a href="{{ route('adoption-applications.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs sm:text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">Back to List</a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                {{-- Pet Overview Card --}}
                <div class="bg-white dark:bg-[#0e1d20] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-card p-6 flex items-center gap-4 card-hover-effect">
                    @if($application->pet && $application->pet->photo_path)
                        <div class="w-16 h-16 rounded-2xl overflow-hidden shrink-0 border border-slate-200 dark:border-slate-700 shadow-2xs relative bg-[#F0FBFB] dark:bg-[#122b30]">
                            <img src="{{ str_starts_with($application->pet->photo_path, 'http') ? $application->pet->photo_path : asset('storage/' . ltrim($application->pet->photo_path, '/')) }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                            <div class="hidden w-full h-full bg-gradient-to-br from-[#F0FBFB] to-[#D6F4F6] dark:from-[#133036] dark:to-[#17454d] text-[#199CA4] dark:text-[#41C1CB] items-center justify-center">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M4.5 10.5C3.67 10.5 3 11.17 3 12s.67 1.5 1.5 1.5S6 12.83 6 12s-.67-1.5-1.5-1.5zm15 0c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm-11-4C7.67 6.5 7 7.17 7 8s.67 1.5 1.5 1.5S10 8.83 10 8s-.67-1.5-1.5-1.5zm7 0c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zM12 11.5c-2.48 0-4.5 2.02-4.5 4.5 0 2.48 2.02 4.5 4.5 4.5s4.5-2.02 4.5-4.5c0-2.48-2.02-4.5-4.5-4.5z"/></svg>
                            </div>
                        </div>
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#F0FBFB] to-[#D6F4F6] dark:from-[#133036] dark:to-[#17454d] text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center shrink-0">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M4.5 10.5C3.67 10.5 3 11.17 3 12s.67 1.5 1.5 1.5S6 12.83 6 12s-.67-1.5-1.5-1.5zm15 0c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm-11-4C7.67 6.5 7 7.17 7 8s.67 1.5 1.5 1.5S10 8.83 10 8s-.67-1.5-1.5-1.5zm7 0c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zM12 11.5c-2.48 0-4.5 2.02-4.5 4.5 0 2.48 2.02 4.5 4.5 4.5s4.5-2.02 4.5-4.5c0-2.48-2.02-4.5-4.5-4.5z"/></svg>
                        </div>
                    @endif
                    <div>
                        <h3 class="text-base font-extrabold text-slate-800 dark:text-white">Pet no. {{ $application->pet_id }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">{{ ucfirst($application->pet->type ?? 'Pet') }} · {{ $application->pet->breed ?? 'Mixed Breed' }} · {{ $application->pet->age ?? 'N/A' }}</p>
                    </div>
                </div>

                {{-- Applicant Information Card --}}
                <div class="bg-white dark:bg-[#0e1d20] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-card p-6 space-y-4">
                    <h2 class="text-base font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#199CA4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Applicant Contact Information</span>
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-slate-50/80 dark:bg-[#12272b] p-4 rounded-2xl border border-slate-100 dark:border-slate-800">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Full Name</p>
                            <p class="mt-1 text-sm font-extrabold text-slate-800 dark:text-white">{{ $application->applicant_name }}</p>
                        </div>
                        <div class="bg-slate-50/80 dark:bg-[#12272b] p-4 rounded-2xl border border-slate-100 dark:border-slate-800">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Email Address</p>
                            <p class="mt-1 text-sm font-extrabold text-slate-800 dark:text-white">{{ $application->applicant_email }}</p>
                        </div>
                        <div class="bg-slate-50/80 dark:bg-[#12272b] p-4 rounded-2xl border border-slate-100 dark:border-slate-800">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Phone Number</p>
                            <p class="mt-1 text-sm font-extrabold text-slate-800 dark:text-white">{{ $application->applicant_phone ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-slate-50/80 dark:bg-[#12272b] p-4 rounded-2xl border border-slate-100 dark:border-slate-800">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Current Status</p>
                            <span class="inline-flex mt-1 items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border
                                {{ $application->status === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : '' }}
                                {{ $application->status === 'rejected' ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800' : '' }}
                                {{ $application->status === 'under_review' ? 'bg-[#199CA4]/10 dark:bg-[#199CA4]/25 text-[#199CA4] dark:text-[#41C1CB] border-[#199CA4]/20 dark:border-[#41C1CB]/30' : '' }}
                                {{ $application->status === 'pending' ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800' : '' }}">
                                <span class="w-1.5 h-1.5 rounded-full
                                    {{ $application->status === 'approved' ? 'bg-emerald-500' : '' }}
                                    {{ $application->status === 'rejected' ? 'bg-rose-500' : '' }}
                                    {{ $application->status === 'under_review' ? 'bg-[#199CA4]' : '' }}
                                    {{ $application->status === 'pending' ? 'bg-amber-500' : '' }}"></span>
                                {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Application Details & Questionnaire Card --}}
                <div class="bg-white dark:bg-[#0e1d20] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-card p-6 space-y-4">
                    <h2 class="text-base font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#199CA4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Mobile Application Questionnaire Responses</span>
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
                            <div class="bg-slate-50/80 dark:bg-[#12272b] p-4 rounded-2xl border border-slate-100 dark:border-slate-800 {{ str_contains(strtolower($key), 'address') || str_contains(strtolower($key), 'other pets') ? 'sm:col-span-2' : '' }}">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#199CA4] dark:text-[#41C1CB]">{{ is_string($key) ? $key : 'Detail' }}</p>
                                <p class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-200 leading-relaxed">{{ $val }}</p>
                            </div>
                        @endforeach
                    </div>

                    @if(!empty($reasonText))
                        <div class="bg-gradient-to-br from-[#F0FBFB] to-[#D6F4F6]/50 dark:from-[#133036] dark:to-[#17454d]/50 p-4 rounded-2xl border border-[#199CA4]/20 dark:border-[#41C1CB]/30 space-y-1">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-[#199CA4] dark:text-[#41C1CB]">Reason for Adoption</p>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 leading-relaxed whitespace-pre-line">{{ $reasonText }}</p>
                        </div>
                    @endif
                </div>

                {{-- Uploaded Screening Documents Card --}}
                <div class="bg-white dark:bg-[#0e1d20] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-card p-6 space-y-4">
                    <h2 class="text-base font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#199CA4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                        <span>Uploaded Verification Documents</span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Valid Government ID --}}
                        <div class="bg-slate-50/80 dark:bg-[#12272b] p-4 rounded-2xl border border-slate-100 dark:border-slate-800 flex flex-col justify-between space-y-3">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#199CA4] dark:text-[#41C1CB]">Valid Government ID</p>
                                <p class="text-xs text-slate-400 mt-0.5">Submitted ID photo</p>
                            </div>
                            @if($application->valid_id_path)
                                <div class="group relative rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0a171a]">
                                    <img src="{{ asset('storage/' . ltrim($application->valid_id_path, '/')) }}" class="w-full h-40 object-cover cursor-pointer hover:scale-105 transition-transform duration-300" onclick="openDocModal('{{ asset('storage/' . ltrim($application->valid_id_path, '/')) }}', 'Valid Government ID')" />
                                    <button onclick="openDocModal('{{ asset('storage/' . ltrim($application->valid_id_path, '/')) }}', 'Valid Government ID')" class="absolute inset-0 bg-slate-900/50 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-bold transition-opacity cursor-pointer">Click to Enlarge</button>
                                </div>
                            @else
                                <div class="p-6 rounded-xl border border-dashed border-slate-200 dark:border-slate-700 text-center text-xs text-slate-400">No Valid ID uploaded</div>
                            @endif
                        </div>

                        {{-- Barangay Certificate --}}
                        <div class="bg-slate-50/80 dark:bg-[#12272b] p-4 rounded-2xl border border-slate-100 dark:border-slate-800 flex flex-col justify-between space-y-3">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#199CA4] dark:text-[#41C1CB]">Barangay Certificate</p>
                                <p class="text-xs text-slate-400 mt-0.5">Submitted residency clearance photo</p>
                            </div>
                            @if($application->barangay_certificate_path)
                                <div class="group relative rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0a171a]">
                                    <img src="{{ asset('storage/' . ltrim($application->barangay_certificate_path, '/')) }}" class="w-full h-40 object-cover cursor-pointer hover:scale-105 transition-transform duration-300" onclick="openDocModal('{{ asset('storage/' . ltrim($application->barangay_certificate_path, '/')) }}', 'Barangay Certificate')" />
                                    <button onclick="openDocModal('{{ asset('storage/' . ltrim($application->barangay_certificate_path, '/')) }}', 'Barangay Certificate')" class="absolute inset-0 bg-slate-900/50 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-bold transition-opacity cursor-pointer">Click to Enlarge</button>
                                </div>
                            @else
                                <div class="p-6 rounded-xl border border-dashed border-slate-200 dark:border-slate-700 text-center text-xs text-slate-400">No Barangay Certificate uploaded</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Adoption Contract & Digital Signatures Card --}}
                <div class="bg-white dark:bg-[#0e1d20] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-card p-6 space-y-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#199CA4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            <span>Adoption Contract Digital Signatures</span>
                        </h2>
                        @if(in_array($application->status, ['approved', 'adopted']))
                            <a href="{{ route('adoption-applications.contract', $application) }}" target="_blank" class="text-xs font-bold text-[#199CA4] hover:text-[#13787F] dark:text-[#41C1CB] hover:underline flex items-center gap-1">
                                <span>Preview Full PDF</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        {{-- 1. Adopter Signature Box --}}
                        <div class="bg-slate-50/80 dark:bg-[#12272b] p-4 sm:p-5 rounded-2xl border border-slate-100 dark:border-slate-800 flex flex-col justify-between space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">1. Adopter Signature</span>
                                @if($application->signature_path)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Signed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Awaiting
                                    </span>
                                @endif
                            </div>

                            @if($application->signature_path)
                                <div class="bg-white dark:bg-[#0a171a] p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-center">
                                    <img src="{{ $application->signature_url }}" class="h-12 max-w-full mx-auto object-contain cursor-pointer" onclick="openDocModal('{{ $application->signature_url }}', 'Adopter Digital Signature')" alt="Adopter Signature" />
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                    <p class="font-bold text-slate-800 dark:text-white">{{ $application->applicant_name }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Signed: {{ $application->signed_at ? $application->signed_at->format('M d, Y h:i A') : 'On file' }}</p>
                                </div>
                            @else
                                <div class="py-4 text-center text-slate-400 dark:text-slate-500">
                                    <p class="text-xs font-semibold">No adopter signature yet.</p>
                                    <p class="text-[10px] mt-0.5">Will be signed via mobile app upon approval.</p>
                                </div>
                            @endif
                        </div>

                        {{-- 2. Staff Signature Box --}}
                        <div class="bg-slate-50/80 dark:bg-[#12272b] p-4 sm:p-5 rounded-2xl border border-slate-100 dark:border-slate-800 flex flex-col justify-between space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">2. CAWS Staff Representative</span>
                                @if($application->staff_signature_path || ($application->staff && $application->staff->digital_signature_path))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Endorsed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Pending Staff Sign
                                    </span>
                                @endif
                            </div>

                            @if($application->staff_signature_path || ($application->staff && $application->staff->digital_signature_path))
                                @php
                                    $staffSigSrc = $application->staff_signature_url ?: ($application->staff ? $application->staff->digital_signature_url : null);
                                    $staffRepName = $application->staff_name ?: ($application->staff?->name ?? Auth::user()->name);
                                @endphp
                                <div class="bg-white dark:bg-[#0a171a] p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-center">
                                    <img src="{{ $staffSigSrc }}" class="h-12 max-w-full mx-auto object-contain cursor-pointer" onclick="openDocModal('{{ $staffSigSrc }}', 'Staff Digital Signature')" alt="Staff Signature" />
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                    <p class="font-bold text-slate-800 dark:text-white">{{ $staffRepName }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">CAWS Authorized Representative</p>
                                </div>
                            @else
                                <div class="py-2 text-center text-slate-400 dark:text-slate-500 space-y-2">
                                    <p class="text-xs font-semibold">Staff signature not attached.</p>
                                    @if(Auth::user()->digital_signature_path)
                                        <form action="{{ route('adoption-applications.sign-as-staff', $application) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="use_saved_signature" value="1">
                                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white text-xs font-bold shadow-2xs transition cursor-pointer">
                                                Attach My Saved Signature
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('profile.edit') }}" class="inline-block text-[11px] text-[#199CA4] hover:underline font-bold">
                                            Set up signature in Profile →
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

            {{-- Quick Action Sidebar --}}
            <div class="bg-white dark:bg-[#0e1d20] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-card p-6 space-y-6 h-fit" x-data="{ currentStatus: '{{ old('status', $application->status) }}' }">
                <div>
                    <h2 class="text-base font-extrabold text-slate-800 dark:text-white">Application Decision</h2>
                    @if(Auth::user()->role === 'admin')
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Make the final decision or schedule an adoption event.</p>
                    @else
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Review applicant details and forward to admin for decision.</p>
                    @endif
                </div>

                <form action="{{ route('adoption-applications.update', $application) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Update Status</label>
                        <select name="status" x-model="currentStatus" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 bg-white dark:bg-[#12272b] text-xs sm:text-sm text-slate-800 dark:text-white font-bold focus:ring-4 focus:ring-[#199CA4]/15 focus:border-[#199CA4] shadow-2xs transition">
                            <option value="under_review" {{ $application->status == 'under_review' ? 'selected' : '' }}>Under Review</option>
                            <option value="pending" {{ $application->status == 'pending' ? 'selected' : '' }}>Pending Decision</option>
                            @if(Auth::user()->role === 'admin')
                                <option value="approved" {{ $application->status == 'approved' ? 'selected' : '' }}>Approved</option>
                            @endif
                            <option value="rejected" {{ $application->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    {{-- Event Scheduling Details (Only Visible when Approved) --}}
                    <div x-show="currentStatus === 'approved'" x-transition class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Schedule Sunday Event Date</label>
                            <input type="date" name="scheduled_at" value="{{ old('scheduled_at', $application->scheduled_at ? $application->scheduled_at->format('Y-m-d') : '') }}" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 bg-white dark:bg-[#12272b] text-xs sm:text-sm text-slate-800 dark:text-white font-semibold focus:ring-4 focus:ring-[#199CA4]/15 focus:border-[#199CA4] shadow-2xs transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Adoption Event / Mall Location</label>
                            <input type="text" name="event_location" value="{{ old('event_location', $application->event_location) }}" placeholder="e.g. Centrio Mall CDO / SM City CDO" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 bg-white dark:bg-[#12272b] text-xs sm:text-sm text-slate-800 dark:text-white font-semibold placeholder-slate-400 dark:placeholder-slate-500 focus:ring-4 focus:ring-[#199CA4]/15 focus:border-[#199CA4] shadow-2xs transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Event Instructions & Reminders</label>
                            <textarea name="event_notes" rows="3" placeholder="e.g. Pet release, free anti-rabies vaccination, and spaying/neutering drive." class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 bg-white dark:bg-[#12272b] text-xs sm:text-sm text-slate-800 dark:text-white font-medium placeholder-slate-400 dark:placeholder-slate-500 focus:ring-4 focus:ring-[#199CA4]/15 focus:border-[#199CA4] shadow-2xs transition">{{ old('event_notes', $application->event_notes) }}</textarea>
                        </div>
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-[#199CA4] to-[#14838B] px-4 py-3 text-xs sm:text-sm font-bold text-white hover:from-[#146970] hover:to-[#12585e] transition shadow-md shadow-[#199CA4]/25 cursor-pointer">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Document Modal Previewer --}}
    <div id="docModal" class="fixed inset-0 z-50 hidden bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4" onclick="closeDocModal()">
        <div class="relative max-w-4xl w-full bg-white dark:bg-[#0e1d20] border border-slate-200/80 dark:border-slate-800 rounded-3xl overflow-hidden shadow-2xl p-4" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 px-2">
                <h3 id="docModalTitle" class="text-sm font-extrabold text-slate-800 dark:text-white">Document Preview</h3>
                <button onclick="closeDocModal()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 font-bold transition cursor-pointer">✕</button>
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