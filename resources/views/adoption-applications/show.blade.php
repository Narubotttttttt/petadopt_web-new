<x-app-layout>
    <script>
        window.openHandoverModal = window.openHandoverModal || function() {
            const modal = document.getElementById('handoverSigningModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'block';
                document.body.classList.add('overflow-hidden');
                if (typeof initHandoverCanvas === 'function') {
                    setTimeout(initHandoverCanvas, 50);
                }
            }
        };
        window.closeHandoverModal = window.closeHandoverModal || function() {
            const modal = document.getElementById('handoverSigningModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
                document.body.classList.remove('overflow-hidden');
            }
        };
    </script>
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
                    @if($application->is_finalized)
                        <a href="{{ route('adoption-applications.contract', $application) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-xs sm:text-sm font-bold border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-600 hover:text-white transition shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Print Official Contract</span>
                        </a>
                    @else
                        <button type="button" onclick="openHandoverModal()" data-trigger="open-handover" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-2xs transition cursor-pointer">
                            <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="pointer-events-none">Verify & Finalize Handover</span>
                        </button>
                        @if($application->signature_path)
                            <a href="{{ route('adoption-applications.contract', $application) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-bold border border-slate-200 dark:border-slate-700 hover:bg-slate-100 transition shadow-2xs" title="Pre-signed by adopter; pending physical verification">
                                <svg class="w-4 h-4 text-[#199CA4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>Preview Draft Contract</span>
                            </a>
                        @else
                            <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 text-xs sm:text-sm font-bold border border-amber-200 dark:border-amber-800/60 cursor-not-allowed opacity-90" title="The adopter can digitally pre-sign the agreement via the mobile app before contract can be printed">
                                <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>Awaiting Adopter Pre-Signature</span>
                            </span>
                        @endif
                    @endif
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
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <h3 class="text-base font-extrabold text-slate-800 dark:text-white">
                                    {{ $application->pet && !empty($application->pet->name) ? $application->pet->name : ('Pet no. ' . $application->pet_id) }}
                                    @if($application->pet && !empty($application->pet->name))
                                        <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 ml-1">(Pet no. {{ $application->pet_id }})</span>
                                    @endif
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">{{ ucfirst($application->pet->type ?? 'Pet') }} · {{ $application->pet->breed ?? 'Mixed Breed' }} · {{ $application->pet->age ?? 'N/A' }}</p>
                            </div>
                            @if($application->pet)
                                <a href="{{ route('pets.show', $application->pet) }}" class="shrink-0 text-xs font-bold text-[#199CA4] hover:text-[#13787F] dark:text-[#41C1CB] hover:underline flex items-center gap-1">
                                    <span>View Pet Profile</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Adoption Contract & Document Verification Card --}}
                <div id="in-person-handover-card" class="bg-white dark:bg-[#0e1d20] rounded-2xl border-2 border-emerald-500/40 dark:border-emerald-500/30 dark:border-slate-800 shadow-card p-6 space-y-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#199CA4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Adoption Contract & In-Person Verification</span>
                        </h2>
                        @if(in_array($application->status, ['approved', 'adopted']) && $application->signature_path)
                            <a href="{{ route('adoption-applications.contract', $application) }}" target="_blank" class="text-xs font-bold text-[#199CA4] hover:text-[#13787F] dark:text-[#41C1CB] hover:underline flex items-center gap-1">
                                <span>Preview Contract PDF</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        {{-- 1. Adopter Signature Box --}}
                        <div class="bg-slate-50/80 dark:bg-[#12272b] p-4 sm:p-5 rounded-2xl border border-slate-100 dark:border-slate-800 flex flex-col justify-between space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">1. Adopter Agreement</span>
                                @if($application->signature_path)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Pre-Signed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Pending Pre-Sign
                                    </span>
                                @endif
                            </div>

                            @if($application->signature_path)
                                <div class="bg-white dark:bg-[#0a171a] p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-center flex items-center justify-center min-h-[56px]">
                                    <img src="{{ $application->signature_url }}" class="h-12 max-w-full mx-auto object-contain cursor-pointer filter dark:invert dark:brightness-200 transition-all hover:scale-105" onclick="openDocModal('{{ $application->signature_url }}', 'Adopter Digital Signature')" alt="Adopter Signature" />
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                    <p class="font-bold text-slate-800 dark:text-white">{{ $application->applicant_name }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Signed: {{ $application->signed_at ? $application->signed_at->format('M d, Y h:i A') : 'On file' }}</p>
                                    <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium mt-1">Pre-signed via mobile app in advance.</p>
                                </div>
                            @else
                                <div class="py-3 px-2 text-center text-slate-400 dark:text-slate-500 bg-amber-50/50 dark:bg-amber-950/20 rounded-xl border border-amber-100 dark:border-amber-900/40">
                                    <p class="text-xs font-bold text-amber-700 dark:text-amber-400">Pre-Signature Pending</p>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">The applicant can pre-sign the agreement on their mobile app in advance prior to the meet-and-greet event.</p>
                                </div>
                            @endif
                        </div>

                        {{-- 2. Physical Document Verification & Staff Endorsement Box --}}
                        <div class="bg-slate-50/80 dark:bg-[#12272b] p-4 sm:p-5 rounded-2xl border border-slate-100 dark:border-slate-800 flex flex-col justify-between space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">2. Venue Verification & Endorsement</span>
                                @if($application->is_finalized)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Handover Finalized
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Pending Verification
                                    </span>
                                @endif
                            </div>

                            @if($application->is_finalized)
                                @php
                                    $staffSigSrc = $application->staff_signature_url;
                                    $staffSigner = $application->staff ?: Auth::user();
                                    $staffRepName = $application->staff_name ?: ($staffSigner?->name ?? 'CAWS Representative');
                                    $signerRoleTitle = ($staffSigner && $staffSigner->role === 'admin') 
                                        ? 'Shelter Administrator' 
                                        : ($staffSigner?->staffProfile?->position_title ?? 'CAWS Authorized Representative');
                                    $verifierName = $application->documentVerifier?->name ?? $staffRepName;
                                @endphp
                                <div class="bg-white dark:bg-[#0a171a] p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-center flex items-center justify-center min-h-[56px]">
                                    <img src="{{ $staffSigSrc }}" class="h-12 max-w-full mx-auto object-contain cursor-pointer filter dark:invert dark:brightness-200 transition-all hover:scale-105" onclick="openDocModal('{{ $staffSigSrc }}', 'Staff Digital Signature')" alt="Staff Signature" />
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 space-y-1">
                                    <p class="font-bold text-slate-800 dark:text-white">{{ $staffRepName }}</p>
                                    <p class="text-[10px] text-[#199CA4] dark:text-[#41C1CB] font-semibold">{{ $signerRoleTitle }}</p>
                                    <div class="pt-1.5 border-t border-slate-200/60 dark:border-slate-700/60 text-[10px] space-y-0.5">
                                        <p class="text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            Original Valid ID Physically Verified
                                        </p>
                                        <p class="text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            Original Barangay Certificate Verified
                                        </p>
                                        <p class="text-slate-400 dark:text-slate-500 pt-0.5">
                                            Verified by {{ $verifierName }} on {{ $application->documents_verified_at?->format('M d, Y h:i A') }}
                                        </p>
                                        <p class="text-emerald-700 dark:text-emerald-300 font-medium">Official Contract PDF unlocked for adopter.</p>
                                    </div>
                                    <div class="pt-2 flex items-center gap-2 border-t border-slate-200/60 dark:border-slate-700/60">
                                        <button type="button" onclick="openHandoverModal()" data-trigger="open-handover" class="flex-1 py-1.5 px-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-[11px] font-bold transition flex items-center justify-center gap-1 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-[#199CA4] pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            <span class="pointer-events-none">Update / Re-Sign</span>
                                        </button>
                                        <form action="{{ route('adoption-applications.reset-handover', $application) }}" method="POST" class="inline" onsubmit="return confirm('Reset handover verification? This will clear the staff endorsement signature, re-lock the contract, and allow you to re-verify or test again.');">
                                            @csrf
                                            <button type="submit" class="py-1.5 px-2.5 rounded-xl border border-amber-200 dark:border-amber-900/60 bg-amber-50/60 dark:bg-amber-950/30 hover:bg-amber-100 text-amber-800 dark:text-amber-300 text-[11px] font-bold transition flex items-center justify-center gap-1 cursor-pointer" title="Reset signature and verification to re-test">
                                                <svg class="w-3.5 h-3.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                <span class="pointer-events-none">Reset Handover</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @elseif(in_array($application->status, ['approved', 'adopted']))
                                <div class="space-y-3">
                                    <div class="space-y-2 bg-white dark:bg-[#0a171a] p-3 rounded-xl border border-slate-200 dark:border-slate-700">
                                        <div class="flex items-center justify-between">
                                            <p class="text-[11px] font-bold text-slate-700 dark:text-slate-200">Physical In-Person Inspection</p>
                                            <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-full border border-amber-200 dark:border-amber-800">Pending Inspection</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                            Original Valid ID and Barangay Certificate must be inspected and endorsed in person before releasing the pet.
                                        </p>
                                    </div>

                                    <button type="button" onclick="openHandoverModal()" data-trigger="open-handover" class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-2xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                                        <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="pointer-events-none">Verify Documents & Sign Handover</span>
                                    </button>

                                    @if(Auth::user()?->digital_signature_path)
                                        <p class="text-[10px] text-center text-slate-400">
                                            Saved signature ready ({{ Auth::user()->name }}). You can endorse with 1 click or draw a new signature on the spot.
                                        </p>
                                    @else
                                        <p class="text-[10px] text-center text-emerald-600 dark:text-emerald-400 font-medium">
                                            No profile signature required in advance. You can draw your official signature directly inside the signing modal.
                                        </p>
                                    @endif
                                </div>
                            @else
                                <div class="py-3 px-2 text-center text-slate-400 dark:text-slate-500 bg-slate-100/50 dark:bg-slate-800/30 rounded-xl">
                                    <p class="text-xs font-medium">Physical verification and CAWS endorsement are completed at the meet-and-greet event after application acceptance.</p>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

                {{-- Competing Applicants & Waitlist Queue Card (Option B) --}}
                @if(isset($competingApplications) && $competingApplications->isNotEmpty())
                    @php
                        $otherScheduled = $competingApplications->firstWhere('status', 'approved');
                        $isCurrentScheduled = $application->status === 'approved';
                    @endphp
                    <div class="bg-white dark:bg-[#0e1d20] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-card p-6 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800/80 pb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-extrabold text-slate-800 dark:text-white">Applicant Queue and Priority Ranking</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Multiple applicants have submitted requests for this pet.</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20 self-start sm:self-center">
                                {{ $competingApplications->count() + 1 }} Total Applicants
                            </span>
                        </div>

                        {{-- Screening Status Advisory Banner --}}
                        @if($isCurrentScheduled)
                            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 text-xs text-emerald-800 dark:text-emerald-300">
                                <svg class="w-4 h-4 shrink-0 text-emerald-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <div>
                                    <span class="font-bold">Primary Candidate:</span> This applicant is currently scheduled for final screening. Competing applicants are placed on the priority waitlist and can be considered if this candidate is not finalized.
                                </div>
                            </div>
                        @elseif($otherScheduled)
                            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 text-xs text-amber-800 dark:text-amber-300">
                                <svg class="w-4 h-4 shrink-0 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <div>
                                    <span class="font-bold">Waitlist Notice:</span> Another applicant (<a href="{{ route('adoption-applications.show', $otherScheduled) }}" class="underline font-bold hover:text-amber-950 dark:hover:text-amber-100">{{ $otherScheduled->applicant_name }}</a>) is currently scheduled for screening. This applicant is held on the waitlist.
                                </div>
                            </div>
                        @else
                            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-slate-50 dark:bg-[#12272b] border border-slate-200/80 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-300">
                                <svg class="w-4 h-4 shrink-0 text-[#199CA4] mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <div>
                                    <span class="font-bold">Comparative Evaluation:</span> Compare ML compatibility scores and questionnaire responses to select the best-matched candidate to invite for final screening.
                                </div>
                            </div>
                        @endif

                        {{-- Competing Applicants List --}}
                        <div class="divide-y divide-slate-100 dark:divide-slate-800/80 rounded-xl border border-slate-200/80 dark:border-slate-800 overflow-hidden bg-slate-50/50 dark:bg-[#0a171a]/50">
                            {{-- Active/Viewing Applicant Row --}}
                            <div class="p-3.5 bg-[#199CA4]/5 dark:bg-[#199CA4]/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-l-4 border-[#199CA4]">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-[#199CA4] text-white flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ strtoupper(substr($application->applicant_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-slate-800 dark:text-white">{{ $application->applicant_name }}</span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20">Currently Viewing</span>
                                        </div>
                                        <p class="text-[11px] text-slate-400 mt-0.5">Applied {{ $application->created_at ? $application->created_at->format('M d, Y') : 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 self-end sm:self-center">
                                    @if($application->compatibility_score !== null)
                                        <span class="text-xs font-extrabold text-emerald-600 dark:text-emerald-400">
                                            {{ number_format($application->compatibility_score, 0) }}% Match
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold
                                        {{ $application->status === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200' : '' }}
                                        {{ $application->status === 'under_review' ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200' : '' }}
                                        {{ $application->status === 'pending' ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200' : '' }}
                                        {{ $application->status === 'rejected' ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200' : '' }}">
                                        {{ $application->status === 'approved' ? 'Scheduled' : ($application->status === 'under_review' ? 'Waitlisted' : ucfirst($application->status)) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Other Applicants Rows --}}
                            @foreach($competingApplications as $compApp)
                                <div class="p-3.5 hover:bg-white dark:hover:bg-[#12272b] flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ strtoupper(substr($compApp->applicant_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-800 dark:text-white">{{ $compApp->applicant_name }}</p>
                                            <p class="text-[11px] text-slate-400 mt-0.5">Applied {{ $compApp->created_at ? $compApp->created_at->format('M d, Y') : 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 self-end sm:self-center">
                                        @if($compApp->compatibility_score !== null)
                                            <span class="text-xs font-extrabold text-emerald-600 dark:text-emerald-400">
                                                {{ number_format($compApp->compatibility_score, 0) }}% Match
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold
                                            {{ $compApp->status === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200' : '' }}
                                            {{ $compApp->status === 'under_review' ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200' : '' }}
                                            {{ $compApp->status === 'pending' ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200' : '' }}
                                            {{ $compApp->status === 'rejected' ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200' : '' }}">
                                            {{ $compApp->status === 'approved' ? 'Scheduled' : ($compApp->status === 'under_review' ? 'Waitlisted' : ucfirst($compApp->status)) }}
                                        </span>
                                        <a href="{{ route('adoption-applications.show', $compApp) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#199CA4] text-slate-700 hover:text-white dark:bg-white/[0.08] dark:hover:bg-[#199CA4] dark:text-slate-300 dark:hover:text-white text-[11px] font-bold transition">
                                            <span>Switch</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Application Origin & Machine Learning Compatibility Card --}}
                @if($application->application_source === 'recommendation')
                    <div class="bg-gradient-to-br from-emerald-50/80 via-teal-50/40 to-white dark:from-emerald-950/30 dark:via-teal-950/20 dark:to-[#0e1d20] rounded-2xl border border-emerald-200/90 dark:border-emerald-800/60 shadow-card p-6 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center font-bold shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h2 class="text-base font-extrabold text-slate-900 dark:text-white">Pet Recommendation Match</h2>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            AI / ML Origin
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Application initiated through the Machine Learning Recommendation Engine.</p>
                                </div>
                            </div>
                            @if($application->compatibility_score !== null)
                                <div class="text-left sm:text-right shrink-0">
                                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">{{ number_format($application->compatibility_score, 0) }}%</span>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Match Score</p>
                                </div>
                            @endif
                        </div>

                        @if($application->compatibility_score !== null)
                            <div class="space-y-1.5">
                                <div class="flex justify-between items-center text-xs font-semibold text-slate-600 dark:text-slate-300">
                                    <span>Compatibility Index</span>
                                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">
                                        {{ $application->compatibility_score >= 80 ? 'High Compatibility' : ($application->compatibility_score >= 60 ? 'Moderate Compatibility' : 'Standard Match') }}
                                    </span>
                                </div>
                                <div class="w-full bg-slate-200/80 dark:bg-white/[0.08] h-2.5 rounded-full overflow-hidden">
                                    <div class="bg-gradient-to-r from-[#199CA4] to-emerald-500 h-full rounded-full transition-all duration-500" style="width: {{ min(100, max(5, $application->compatibility_score)) }}%"></div>
                                </div>
                            </div>
                        @endif

                        <div class="p-3.5 rounded-xl bg-white/80 dark:bg-[#12272b] border border-emerald-100 dark:border-emerald-900/40 text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                            <span class="font-bold text-slate-800 dark:text-white">Recommendation Details:</span>
                            The applicant answered the adopter compatibility questionnaire on mobile (housing type, activity level, pet experience, household dynamics, and preferences). The Scikit-Learn Random Forest recommendation model evaluated available shelter animals and suggested this pet as a suitable match.
                        </div>
                    </div>
                @else
                    <div class="bg-white dark:bg-[#0e1d20] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-card p-6 space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/[0.06] text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-white/[0.08] flex items-center justify-center font-bold shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white">Manual Catalog Browsing</h2>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 dark:bg-white/[0.08] text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08]">
                                        Manual Origin
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">The applicant discovered and applied for this pet directly from the public shelter listings without utilizing the recommendation engine.</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Pet Medical Clearance & Immunization Card --}}
                @if($application->pet)
                    @php
                        $medLogs = $application->pet->medicalLogs ?? collect();
                        $latestVaccine = $medLogs->where('category', 'vaccination')->sortByDesc('date')->first();
                        $latestDeworming = $medLogs->where('category', 'deworming')->sortByDesc('date')->first();
                        $nowDay = now()->startOfDay();
                        $vacDue = $latestVaccine?->next_due_date ? $latestVaccine->next_due_date->copy()->startOfDay() : null;
                        $isVacOverdue = $vacDue && $vacDue->lt($nowDay);
                        $isVacDueSoon = $vacDue && !$isVacOverdue && $vacDue->lte($nowDay->copy()->addDays(30)->endOfDay());
                        $hasVaccine = (bool) $latestVaccine;
                    @endphp
                    <div class="bg-white dark:bg-[#0e1d20] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-card p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-[#199CA4]/10 text-[#199CA4] flex items-center justify-center font-bold text-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                </div>
                                <div>
                                    <h2 class="text-base font-extrabold text-slate-800 dark:text-white">Clinical & Vaccination Clearance</h2>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Verify pet health status before finalizing adoption approval.</p>
                                </div>
                            </div>
                            @if(in_array(Auth::user()?->role, ['admin', 'staff']))
                                <a href="{{ route('medical-logs.create-for-pet', $application->pet->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#199CA4]/10 hover:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] text-xs font-bold transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Log Treatment</span>
                                </a>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            {{-- Vaccination Clearance Item --}}
                            <div class="p-3.5 rounded-xl border {{ $hasVaccine ? ($isVacOverdue ? 'bg-rose-50/60 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/40' : ($isVacDueSoon ? 'bg-amber-50/60 dark:bg-amber-950/20 border-amber-200 dark:border-amber-900/40' : 'bg-emerald-50/60 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/40')) : 'bg-rose-50/60 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/40' }}">
                                <div class="flex items-center justify-between">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Vaccination Status</p>
                                    @if(!$hasVaccine)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300">
                                            Missing Record
                                        </span>
                                    @elseif($isVacOverdue)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300">
                                            Overdue
                                        </span>
                                    @elseif($isVacDueSoon)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300">
                                            Due Soon
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300">
                                            Cleared
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-2">
                                    @if($hasVaccine)
                                        <p class="text-xs font-extrabold text-slate-800 dark:text-white">Core Vaccination Dose</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Administered: {{ $latestVaccine->date?->format('M d, Y') ?? 'N/A' }}</p>
                                        <p class="text-[11px] font-semibold {{ $isVacOverdue ? 'text-rose-600 dark:text-rose-400' : ($isVacDueSoon ? 'text-amber-600 dark:text-amber-400' : 'text-slate-600 dark:text-slate-300') }} mt-0.5">
                                            Booster Due: {{ $latestVaccine->next_due_date ? $latestVaccine->next_due_date->format('M d, Y') : 'None scheduled' }}
                                        </p>
                                    @else
                                        <p class="text-xs font-bold text-rose-700 dark:text-rose-400">No vaccination recorded</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Ensure pet receives initial vaccine prior to release.</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Deworming Status Item --}}
                            <div class="p-3.5 rounded-xl border bg-slate-50/80 dark:bg-[#12272b] border-slate-200/60 dark:border-slate-800">
                                <div class="flex items-center justify-between">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Deworming Status</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $latestDeworming ? 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                                        {{ $latestDeworming ? 'Administered' : 'Optional / Pending' }}
                                    </span>
                                </div>
                                <div class="mt-2">
                                    @if($latestDeworming)
                                        <p class="text-xs font-extrabold text-slate-800 dark:text-white">Preventative Deworming</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Given on: {{ $latestDeworming->date?->format('M d, Y') ?? 'N/A' }}</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Next Due: {{ $latestDeworming->next_due_date ? $latestDeworming->next_due_date->format('M d, Y') : 'As advised' }}</p>
                                    @else
                                        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">No deworming record logged</p>
                                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Optional preventative care.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if(!$hasVaccine || $isVacOverdue)
                            <div class="flex items-center gap-2 p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs">
                                <svg class="w-4 h-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>Clinical Advisory: Please confirm pet vaccination schedule is updated before approving hand-over.</span>
                            </div>
                        @endif
                    </div>
                @endif

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
                        @if($application->status === 'rejected' && !empty($application->rejection_reason ?? $application->evaluation_notes))
                            <div class="sm:col-span-2 bg-rose-50/80 dark:bg-rose-950/40 p-3.5 rounded-2xl border border-rose-200/80 dark:border-rose-900/60">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-rose-500 dark:text-rose-400">Rejection Reason Given to Adopter</p>
                                <p class="mt-1 text-xs font-semibold text-rose-800 dark:text-rose-200 leading-relaxed">{{ $application->rejection_reason ?? $application->evaluation_notes }}</p>
                            </div>
                        @endif
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
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#199CA4] dark:text-[#41C1CB]">Valid Government ID</p>
                                    <p class="text-xs font-bold text-slate-800 dark:text-white mt-0.5">{{ $application->id_type ?? 'Government Issued ID' }}</p>
                                </div>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Valid ID Attached
                                </span>
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

            </div>

            {{-- Decision Sidebar --}}
            <div class="bg-white dark:bg-[#0e1d20] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-card p-6 space-y-6 h-fit" 
                 x-data="{ 
                     currentStatus: '{{ old('status', in_array($application->status, ['approved', 'rejected']) ? $application->status : '') }}',
                     presetLocation: ['Centrio Mall CDO', 'SM City CDO Uptown', 'SM CDO Downtown Premier', 'Limketkai Center CDO', 'CAWS Shelter & Adoption Center'].includes('{{ addslashes(old('event_location', $application->event_location ?? '')) }}') ? '{{ addslashes(old('event_location', $application->event_location ?? '')) }}' : '',
                     eventLocation: '{{ addslashes(old('event_location', $application->event_location ?? '')) }}',
                     eventNotes: '{{ addslashes(old('event_notes', $application->event_notes ?? '')) }}',
                     presetReason: '',
                     rejectionReason: '{{ addslashes(old('rejection_reason', $application->rejection_reason ?? $application->evaluation_notes ?? '')) }}',
                     get isSaveDisabled() {
                         if (!this.currentStatus) return true;
                         if (this.currentStatus === 'approved') {
                             return !this.eventLocation || !this.eventLocation.trim() || !this.eventNotes || !this.eventNotes.trim();
                         }
                         if (this.currentStatus === 'rejected') {
                             return !this.rejectionReason || !this.rejectionReason.trim();
                         }
                         return false;
                     }
                 }">
                
                <div>
                    <h2 class="text-base font-extrabold text-slate-800 dark:text-white">Adoption Decision & Final Screening</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Review applicant screening details and record the adoption decision.</p>
                </div>

                {{-- In-Person Handover Quick Action Widget --}}
                @if(in_array($application->status, ['approved', 'adopted']))
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-[#F0FBFB] to-[#E6F7F8] dark:from-[#133036] dark:to-[#17454d] border border-[#199CA4]/30 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-[#199CA4] dark:text-[#41C1CB]">Meet-and-Greet Handover</span>
                            @if($application->is_finalized)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                    Handover Finalized
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                    Pending Check
                                </span>
                            @endif
                        </div>

                        <div class="text-xs text-slate-700 dark:text-slate-300 space-y-1">
                            <p class="font-bold text-slate-900 dark:text-white">Applicant: {{ $application->applicant_name }}</p>
                            <p class="text-[11px] text-slate-600 dark:text-slate-400">
                                Agreement: {{ $application->signature_path ? 'Pre-signed via mobile' : 'Pre-signature pending' }}
                            </p>
                        </div>

                        @if($application->is_finalized)
                            <div class="pt-2 border-t border-[#199CA4]/20 flex items-center justify-between">
                                <span class="text-[11px] text-emerald-700 dark:text-emerald-300 font-bold">Contract Unlocked</span>
                                <a href="{{ route('adoption-applications.contract', $application) }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold transition">
                                    Print Contract
                                </a>
                            </div>
                        @else
                            <button type="button" onclick="openHandoverModal()" data-trigger="open-handover" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs cursor-pointer">
                                <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="pointer-events-none">Verify & Finalize Handover</span>
                            </button>
                        @endif
                    </div>
                @endif

                <form action="{{ route('adoption-applications.update', $application) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Decision</label>
                        <select name="status" x-model="currentStatus" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 bg-white dark:bg-[#12272b] text-xs sm:text-sm text-slate-800 dark:text-white font-bold focus:ring-4 focus:ring-[#199CA4]/15 focus:border-[#199CA4] shadow-2xs transition">
                            <option value="" disabled {{ !in_array($application->status, ['approved', 'rejected']) ? 'selected' : '' }}>Select Decision...</option>
                            <option value="approved" {{ $application->status == 'approved' ? 'selected' : '' }}>Approve and Schedule Final Screening</option>
                            <option value="rejected" {{ $application->status == 'rejected' ? 'selected' : '' }}>Reject Application</option>
                        </select>
                    </div>

                    {{-- Event Scheduling Details --}}
                    <div x-show="currentStatus === 'approved'" x-transition class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <div class="p-3 rounded-xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 border border-[#199CA4]/20 text-xs text-slate-700 dark:text-slate-300 leading-relaxed space-y-1">
                            <p class="font-bold text-[#199CA4] dark:text-[#41C1CB]">In-Person Screening Notice:</p>
                            <p class="text-[11px]">Approving this application schedules the applicant for final in-person screening. The applicant will be instructed to bring physical copies of their Valid ID and Barangay Certificate to the venue for identity verification.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Schedule Sunday Event Date</label>
                            <input type="date" name="scheduled_at" value="{{ old('scheduled_at', $application->scheduled_at ? $application->scheduled_at->format('Y-m-d') : '') }}" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 bg-white dark:bg-[#12272b] text-xs sm:text-sm text-slate-800 dark:text-white font-semibold focus:ring-4 focus:ring-[#199CA4]/15 focus:border-[#199CA4] shadow-2xs transition">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Adoption Event / Mall Location <span class="text-rose-500 font-bold">*</span>
                            </label>

                            {{-- Standard Location Presets Dropdown --}}
                            <select x-model="presetLocation" 
                                    @change="if (presetLocation && presetLocation !== 'custom') { eventLocation = presetLocation; } else if (presetLocation === 'custom') { if (['Centrio Mall CDO', 'SM City CDO Uptown', 'SM CDO Downtown Premier', 'Limketkai Center CDO', 'CAWS Shelter & Adoption Center'].includes(eventLocation)) { eventLocation = ''; } $nextTick(() => $refs.customLocationInput.focus()); }" 
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 bg-white dark:bg-[#12272b] text-xs sm:text-sm text-slate-800 dark:text-white font-semibold focus:ring-4 focus:ring-[#199CA4]/15 focus:border-[#199CA4] shadow-2xs transition">
                                <option value="">Select standard venue (or type custom below)...</option>
                                <option value="Centrio Mall CDO">Centrio Mall CDO</option>
                                <option value="SM City CDO Uptown">SM City CDO Uptown</option>
                                <option value="SM CDO Downtown Premier">SM CDO Downtown Premier</option>
                                <option value="Limketkai Center CDO">Limketkai Center CDO</option>
                                <option value="CAWS Shelter & Adoption Center">CAWS Shelter & Adoption Center</option>
                                <option value="custom">Other / Custom Location...</option>
                            </select>

                            {{-- Editable Location Input --}}
                            <input type="text" 
                                   name="event_location" 
                                   x-ref="customLocationInput"
                                   x-model="eventLocation"
                                   placeholder="e.g. Centrio Mall CDO, Activity Center Booth #2" 
                                   class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 bg-white dark:bg-[#12272b] text-xs sm:text-sm text-slate-800 dark:text-white font-semibold placeholder-slate-400 dark:placeholder-slate-500 focus:ring-4 focus:ring-[#199CA4]/15 focus:border-[#199CA4] shadow-2xs transition">
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">Pick a preset venue above to auto-fill, then customize booth or activity area details as needed.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                                Event Instructions & Reminders <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <textarea name="event_notes" 
                                      x-model="eventNotes"
                                      rows="3" 
                                      placeholder="e.g. Pet release, free anti-rabies vaccination, and spaying/neutering drive." 
                                      class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 bg-white dark:bg-[#12272b] text-xs sm:text-sm text-slate-800 dark:text-white font-medium placeholder-slate-400 dark:placeholder-slate-500 focus:ring-4 focus:ring-[#199CA4]/15 focus:border-[#199CA4] shadow-2xs transition">{{ old('event_notes', $application->event_notes) }}</textarea>
                        </div>
                    </div>

                    {{-- Rejection Details & Reason for Adopter --}}
                    <div x-show="currentStatus === 'rejected'" x-transition class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <div class="p-3.5 rounded-xl bg-rose-50/70 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-900/60">
                            <p class="text-xs font-bold text-rose-800 dark:text-rose-300">Feedback for Adopter</p>
                            <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-0.5">
                                Please provide a clear reason explaining why this application was not approved. This feedback will be sent directly to the applicant via push notification.
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Preset Reason Template</label>
                            <select x-model="presetReason" @change="if (presetReason) { rejectionReason = presetReason; }" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 bg-white dark:bg-[#12272b] text-xs sm:text-sm text-slate-800 dark:text-white font-semibold focus:ring-4 focus:ring-[#199CA4]/15 focus:border-[#199CA4] shadow-2xs transition">
                                <option value="">Select a common reason or type custom...</option>
                                <option value="Incomplete or unverifiable identity and proof of residence documents.">Incomplete or Unverifiable Documents</option>
                                <option value="Rental property lease or landlord rules do not permit keeping pets.">Landlord or Rental Pet Restrictions</option>
                                <option value="Living space or fencing security does not meet requirements for this pet.">Inadequate Fencing or Living Environment</option>
                                <option value="Care schedule and time commitment do not match this pet's requirements.">Schedule and Care Commitment Mismatch</option>
                                <option value="Household has already reached the safe pet capacity limit.">Maximum Pet Capacity Reached</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                                Reason for Rejection <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <textarea 
                                name="rejection_reason" 
                                x-model="rejectionReason"
                                :required="currentStatus === 'rejected'" 
                                rows="3" 
                                placeholder="Explain specifically what needs improvement or why the application was declined..." 
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 bg-white dark:bg-[#12272b] text-xs sm:text-sm text-slate-800 dark:text-white font-medium placeholder-slate-400 dark:placeholder-slate-500 focus:ring-4 focus:ring-[#199CA4]/15 focus:border-[#199CA4] shadow-2xs transition"></textarea>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                                :disabled="isSaveDisabled"
                                :class="isSaveDisabled ? 'opacity-40 cursor-not-allowed bg-slate-300 dark:bg-slate-700 text-slate-500 dark:text-slate-400 shadow-none pointer-events-none' : 'bg-gradient-to-r from-[#199CA4] to-[#14838B] text-white hover:from-[#146970] hover:to-[#12585e] shadow-md shadow-[#199CA4]/25 cursor-pointer'"
                                class="w-full inline-flex items-center justify-center rounded-xl px-4 py-3 text-xs sm:text-sm font-bold transition">
                            Save Decision & Screening Schedule
                        </button>

                        <p x-show="currentStatus === 'approved' && isSaveDisabled" class="text-[11px] text-amber-600 dark:text-amber-400 font-semibold text-center mt-2">
                            Please provide both event location and instructions to approve.
                        </p>
                        <p x-show="currentStatus === 'rejected' && isSaveDisabled" class="text-[11px] text-amber-600 dark:text-amber-400 font-semibold text-center mt-2">
                            Please provide a rejection reason to proceed.
                        </p>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- Document Modal Previewer --}}
    <div id="docModal" class="fixed inset-0 z-50 hidden bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4" onclick="closeDocModal()">
        <div class="relative max-w-4xl w-full bg-white dark:bg-[#0e1d20] border border-slate-200/80 dark:border-slate-800 rounded-3xl overflow-hidden shadow-2xl p-4" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 px-2">
                <h3 id="docModalTitle" class="text-sm font-extrabold text-slate-800 dark:text-white">Document Preview</h3>
                <button onclick="closeDocModal()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 font-bold transition cursor-pointer" aria-label="Close">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="py-4 flex justify-center max-h-[75vh] overflow-auto">
                <img id="docModalImage" src="" class="max-w-full max-h-[70vh] rounded-2xl object-contain shadow-md transition-all" />
            </div>
        </div>
    </div>

    {{-- In-Person Handover & Staff Signing Modal --}}
    @if(in_array($application->status, ['approved', 'adopted']))
    <div id="handoverSigningModal" 
         class="fixed inset-0 z-50 hidden bg-slate-900/80 backdrop-blur-sm overflow-y-auto p-4 sm:p-6" 
         style="display: none;"
         onclick="if (event.target === this) closeHandoverModal()">
        
        <div class="min-h-full flex items-center justify-center pointer-events-none py-6">
            <div class="relative w-full max-w-xl bg-white dark:bg-[#0e1d20] border border-slate-200/80 dark:border-slate-800 rounded-3xl overflow-hidden shadow-2xl transition-all pointer-events-auto"
                 onclick="event.stopPropagation()">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-800 dark:text-white">
                            Adoption Handover & Staff Endorsement
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Pet: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $application->pet->name ?? 'Pet #' . $application->pet_id }}</span> • 
                            Adopter: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $application->applicant_name }}</span>
                        </p>
                    </div>
                    <button type="button" onclick="closeHandoverModal()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 font-bold transition cursor-pointer" aria-label="Close">
                        <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal Form --}}
                <form id="handoverFinalizeForm" action="{{ route('adoption-applications.finalize-handover', $application) }}" method="POST" onsubmit="return handleHandoverSubmit(event)" class="p-6 space-y-5">
                    @csrf
                    <input type="hidden" name="use_saved_signature" id="useSavedSignatureInput" value="{{ Auth::user()?->digital_signature_path ? '1' : '0' }}">
                    <input type="hidden" name="signature_data" id="modalSignatureDataInput">

                    {{-- Step 1: Physical Document Inspection --}}
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-[#199CA4] dark:text-[#41C1CB]">Step 1: Physical Inspection Checklist</span>
                            <span class="text-[10px] font-bold text-slate-400">Required</span>
                        </div>
                        <div class="space-y-2.5 bg-slate-50 dark:bg-[#12272b] p-3.5 rounded-2xl border border-slate-100 dark:border-slate-800">
                            <label class="flex items-start gap-3 text-xs text-slate-700 dark:text-slate-200 cursor-pointer select-none">
                                <input type="checkbox" name="id_document_verified" id="modalIdVerifiedCheck" value="1" {{ $application->id_document_verified ? 'checked' : '' }} required class="mt-0.5 rounded border-slate-300 text-[#199CA4] focus:ring-[#199CA4]">
                                <span>Original <strong>Valid Government ID</strong> physically inspected and verified against application details.</span>
                            </label>
                            <label class="flex items-start gap-3 text-xs text-slate-700 dark:text-slate-200 cursor-pointer select-none">
                                <input type="checkbox" name="barangay_cert_verified" id="modalCertVerifiedCheck" value="1" {{ $application->barangay_cert_verified ? 'checked' : '' }} required class="mt-0.5 rounded border-slate-300 text-[#199CA4] focus:ring-[#199CA4]">
                                <span>Original <strong>Barangay Certificate of Residency</strong> physically inspected and verified.</span>
                            </label>
                        </div>
                    </div>

                    {{-- Step 2: Signature Endorsement --}}
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-[#199CA4] dark:text-[#41C1CB]">Step 2: Staff Endorsement Signature</span>
                            
                            {{-- Toggle between Saved and Draw if user has saved signature --}}
                            @if(Auth::user()?->digital_signature_path)
                                <div class="inline-flex rounded-xl bg-slate-100 dark:bg-slate-800 p-0.5 text-[11px] font-bold">
                                    <button type="button" 
                                            id="tabSavedSigBtn"
                                            onclick="switchHandoverSigMode('saved')"
                                            class="px-2.5 py-1 rounded-lg transition cursor-pointer bg-white dark:bg-[#199CA4] text-slate-900 dark:text-white shadow-2xs">
                                        Saved Signature
                                    </button>
                                    <button type="button" 
                                            id="tabDrawSigBtn"
                                            onclick="switchHandoverSigMode('draw')"
                                            class="px-2.5 py-1 rounded-lg transition cursor-pointer text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white">
                                        Draw New
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- Option A: Saved Signature View --}}
                        @if(Auth::user()?->digital_signature_path)
                            <div id="handoverSavedSigView" class="space-y-2">
                                <div class="p-3.5 bg-slate-50 dark:bg-[#12272b] rounded-2xl border border-slate-100 dark:border-slate-800 flex items-center gap-4">
                                    <div class="w-32 h-16 bg-white dark:bg-[#0a171a] rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-center p-1.5 shrink-0">
                                        @if(Auth::user()?->digital_signature_url)
                                            <img src="{{ Auth::user()->digital_signature_url }}" alt="Saved Signature" class="max-h-full max-w-full object-contain filter dark:invert dark:brightness-200">
                                        @endif
                                    </div>
                                    <div class="flex-1 text-xs">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-slate-800 dark:text-white">{{ Auth::user()?->name }}</span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                Verified on File
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            Authorized representative signature will be affixed to the legal adoption contract.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Option B: Drawing Canvas Pad --}}
                        <div id="handoverDrawSigView" class="space-y-2 {{ Auth::user()?->digital_signature_path ? 'hidden' : '' }}">
                            <div class="relative rounded-2xl border border-slate-300 dark:border-slate-600 bg-white p-1 overflow-hidden shadow-2xs">
                                <canvas id="handoverSignatureCanvas" class="w-full h-36 cursor-crosshair touch-none block bg-white"></canvas>
                                
                                {{-- Watermark / Guide Line --}}
                                <div class="absolute bottom-6 left-6 right-6 border-b border-dashed border-slate-300 pointer-events-none flex justify-between items-center text-[10px] text-slate-400">
                                    <span>Sign on the line above</span>
                                    <span>Authorized Staff Signature</span>
                                </div>

                                {{-- Floating Clear Pad Button --}}
                                <button type="button" onclick="clearHandoverSignature()" class="absolute top-2 right-2 px-2.5 py-1 text-[11px] font-bold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition cursor-pointer shadow-2xs">
                                    Clear Pad
                                </button>
                            </div>

                            <div class="flex items-center justify-between text-xs pt-1">
                                <label class="flex items-center gap-2 text-slate-600 dark:text-slate-300 cursor-pointer select-none text-[11px]">
                                    <input type="checkbox" name="save_signature_to_profile" value="1" checked class="rounded border-slate-300 text-[#199CA4] focus:ring-[#199CA4]">
                                    <span>Save this signature to my staff profile for future handovers</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer Actions --}}
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                        <button type="button" onclick="closeHandoverModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" id="handoverSubmitBtn" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span id="handoverSubmitText" class="pointer-events-none">Confirm & Finalize Handover</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    @endif

    <script>
        let handoverSigMode = '{{ Auth::user()?->digital_signature_path ? 'saved' : 'draw' }}';
        let handoverCanvas = null;
        let handoverCtx = null;
        let hasDrawnHandoverSig = false;

        window.openHandoverModal = function() {
            const modal = document.getElementById('handoverSigningModal');
            if (!modal) {
                console.warn('[HandoverModal] modal element #handoverSigningModal not found');
                return;
            }
            modal.classList.remove('hidden');
            modal.style.display = 'block';
            document.body.classList.add('overflow-hidden');
            if (handoverSigMode === 'draw') {
                setTimeout(initHandoverCanvas, 50);
            }
        };

        window.closeHandoverModal = function() {
            const modal = document.getElementById('handoverSigningModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.style.display = 'none';
            document.body.classList.remove('overflow-hidden');
        };

        window.switchHandoverSigMode = function(mode) {
            handoverSigMode = mode;
            const savedInput = document.getElementById('useSavedSignatureInput');
            const savedView = document.getElementById('handoverSavedSigView');
            const drawView = document.getElementById('handoverDrawSigView');
            const tabSaved = document.getElementById('tabSavedSigBtn');
            const tabDraw = document.getElementById('tabDrawSigBtn');

            if (mode === 'saved') {
                if (savedInput) savedInput.value = '1';
                if (savedView) savedView.classList.remove('hidden');
                if (drawView) drawView.classList.add('hidden');
                if (tabSaved) {
                    tabSaved.className = 'px-2.5 py-1 rounded-lg transition cursor-pointer bg-white dark:bg-[#199CA4] text-slate-900 dark:text-white shadow-2xs';
                }
                if (tabDraw) {
                    tabDraw.className = 'px-2.5 py-1 rounded-lg transition cursor-pointer text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white';
                }
            } else {
                if (savedInput) savedInput.value = '0';
                if (savedView) savedView.classList.add('hidden');
                if (drawView) drawView.classList.remove('hidden');
                if (tabSaved) {
                    tabSaved.className = 'px-2.5 py-1 rounded-lg transition cursor-pointer text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white';
                }
                if (tabDraw) {
                    tabDraw.className = 'px-2.5 py-1 rounded-lg transition cursor-pointer bg-white dark:bg-[#199CA4] text-slate-900 dark:text-white shadow-2xs';
                }
                setTimeout(initHandoverCanvas, 50);
            }
        };

        function initHandoverCanvas() {
            const canvas = document.getElementById('handoverSignatureCanvas');
            if (!canvas) return;
            const rect = canvas.getBoundingClientRect();
            if (rect.width === 0) {
                setTimeout(initHandoverCanvas, 50);
                return;
            }

            const ratio = window.devicePixelRatio || 1;
            canvas.width = Math.round(rect.width * ratio);
            canvas.height = Math.round(rect.height * ratio);
            const ctx = canvas.getContext('2d');
            ctx.scale(ratio, ratio);
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.lineWidth = 2.5;
            ctx.strokeStyle = '#0f172a';
            handoverCtx = ctx;
            handoverCanvas = canvas;

            if (!canvas._listenersAttached) {
                let isDrawing = false;
                const getPos = (e) => {
                    const r = canvas.getBoundingClientRect();
                    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                    return { x: clientX - r.left, y: clientY - r.top };
                };
                const start = (e) => {
                    e.preventDefault();
                    isDrawing = true;
                    hasDrawnHandoverSig = true;
                    const pos = getPos(e);
                    ctx.beginPath();
                    ctx.moveTo(pos.x, pos.y);
                };
                const draw = (e) => {
                    if (!isDrawing) return;
                    e.preventDefault();
                    const pos = getPos(e);
                    ctx.lineTo(pos.x, pos.y);
                    ctx.stroke();
                };
                const stop = () => { isDrawing = false; };

                canvas.addEventListener('mousedown', start);
                canvas.addEventListener('mousemove', draw);
                window.addEventListener('mouseup', stop);

                canvas.addEventListener('touchstart', start, { passive: false });
                canvas.addEventListener('touchmove', draw, { passive: false });
                window.addEventListener('touchend', stop);
                canvas._listenersAttached = true;
            }
        }

        window.clearHandoverSignature = function() {
            if (handoverCanvas && handoverCtx) {
                handoverCtx.save();
                handoverCtx.setTransform(1, 0, 0, 1, 0, 0);
                handoverCtx.clearRect(0, 0, handoverCanvas.width, handoverCanvas.height);
                handoverCtx.restore();
                hasDrawnHandoverSig = false;
            }
        };

        window.handleHandoverSubmit = function(e) {
            const idCheck = document.getElementById('modalIdVerifiedCheck');
            const certCheck = document.getElementById('modalCertVerifiedCheck');
            if (!idCheck || !idCheck.checked || !certCheck || !certCheck.checked) {
                e.preventDefault();
                alert('Please physically inspect and verify both the original Valid ID and Barangay Certificate before finalizing.');
                return false;
            }

            if (handoverSigMode === 'draw') {
                if (!hasDrawnHandoverSig || !handoverCanvas) {
                    e.preventDefault();
                    alert('Please draw your official staff signature before submitting.');
                    return false;
                }
                const sigInput = document.getElementById('modalSignatureDataInput');
                if (sigInput) {
                    sigInput.value = handoverCanvas.toDataURL('image/png');
                }
            }

            const submitBtn = document.getElementById('handoverSubmitBtn');
            const submitText = document.getElementById('handoverSubmitText');
            if (submitBtn) submitBtn.disabled = true;
            if (submitText) submitText.innerText = 'Finalizing Handover...';
            return true;
        };

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeHandoverModal();
            }
        });

        // Event delegation fallback for buttons
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('[data-trigger="open-handover"]');
            if (btn) {
                e.preventDefault();
                window.openHandoverModal();
            }
        });

        function openDocModal(src, title) {
            const img = document.getElementById('docModalImage');
            img.src = src;
            document.getElementById('docModalTitle').innerText = title;
            if (title && title.toLowerCase().includes('signature')) {
                img.classList.add('filter', 'dark:invert', 'dark:brightness-200');
            } else {
                img.classList.remove('filter', 'dark:invert', 'dark:brightness-200');
            }
            document.getElementById('docModal').classList.remove('hidden');
        }
        function closeDocModal() {
            document.getElementById('docModal').classList.add('hidden');
        }
    </script>
</x-app-layout>