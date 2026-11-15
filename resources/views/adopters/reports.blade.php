<x-app-layout>
    <div class="w-full py-6 sm:py-8 px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="{
             showPhotoModal: false,
             photoModalUrl: '',
             photoModalTitle: '',
             photoModalDate: '',
             photoModalWeight: '',
             photoModalStatus: '',
             photoModalNotes: '',

             showReviewModal: false,
             reviewReportId: null,
             reviewPetName: '',
             reviewAdopterName: '',
             reviewAdopterNotes: '',
             reviewCheckInDate: '',
             reviewCurrentDecision: 'cleared_accidental',
             reviewCurrentRemarks: '',

             // Side-by-Side Vet Verification Modal State
             showVerifyModal: false,
             verifyLogId: null,
             verifyPetName: '',
             verifyPetBreed: '',
             verifyPetId: '',
             verifyPetPhoto: '',
             verifyAdopterName: '',
             verifyAdopterPhone: '',
             verifyCategory: '',
             verifyVaccineName: '',
             verifyDate: '',
             verifyClinicName: '',
             verifyVeterinarian: '',
             verifyProofPhotoUrl: '',
             verifyNextDueDate: '',
             verifyActionUrl: '',
             verifyStatus: '',
             showRejectInput: false,
             rejectionReason: '',
             imgRotation: 0,
             imgZoom: 1,

             openReviewModal(id, petName, adopterName, date, notes, decision, remarks) {
                 this.reviewReportId = id;
                 this.reviewPetName = petName || 'Companion';
                 this.reviewAdopterName = adopterName || 'Adopter';
                 this.reviewCheckInDate = date || '';
                 this.reviewAdopterNotes = notes || '';
                 this.reviewCurrentDecision = decision || 'cleared_accidental';
                 this.reviewCurrentRemarks = remarks || '';
                 this.showReviewModal = true;
             },

             openPhotoModal(url, title, date, weight, status, notes) {
                 if (!url) return;
                 this.photoModalUrl = url;
                 this.photoModalTitle = title || 'Health Check-in Photo';
                 this.photoModalDate = date || '';
                 this.photoModalWeight = weight || '';
                 this.photoModalStatus = status || '';
                 this.photoModalNotes = notes || '';
                 this.showPhotoModal = true;
             },

             openFromCard(event) {
                 const target = event.currentTarget;
                 if (!target) return;
                 const url = target.getAttribute('data-photo');
                 if (!url) return;
                 this.photoModalUrl = url;
                 this.photoModalTitle = target.getAttribute('data-title') || 'Health Check-in Photo';
                 this.photoModalDate = target.getAttribute('data-date') || '';
                 this.photoModalWeight = target.getAttribute('data-weight') || '';
                 this.photoModalStatus = target.getAttribute('data-status') || '';
                 this.photoModalNotes = target.getAttribute('data-notes') || '';
                 this.showPhotoModal = true;
             },

             openVerifyModal(log) {
                 if (!log) return;
                 this.verifyLogId = log.id;
                 this.verifyPetName = log.pet?.name || ('Pet no. ' + log.pet_id);
                 this.verifyPetBreed = (log.pet?.type ? (log.pet.type.charAt(0).toUpperCase() + log.pet.type.slice(1)) : 'Pet') + ' • ' + (log.pet?.breed || 'Mixed');
                 this.verifyPetId = log.pet_id;
                 this.verifyPetPhoto = log.pet?.photo_url || (log.pet?.photo_path ? ('/storage/' + log.pet.photo_path.replace(/^\//, '')) : '');
                 this.verifyAdopterName = log.creator?.name || 'Authorized Adopter';
                 this.verifyAdopterPhone = log.creator?.adopters_profile?.phone || 'N/A';
                 this.verifyCategory = log.category || 'vaccination';
                 this.verifyVaccineName = log.vaccine_name || (log.category === 'vaccination' ? 'Core Vaccine' : 'Routine Deworming');
                 this.verifyDate = log.date ? (typeof log.date === 'string' ? log.date.split('T')[0] : log.date) : '';
                 this.verifyClinicName = log.clinic_name || 'Private Veterinary Clinic';
                 this.verifyVeterinarian = log.administered_by || '';
                 this.verifyProofPhotoUrl = log.proof_photo_url || '';
                 this.verifyActionUrl = '/medical-logs/' + log.id + '/verify';
                 this.verifyStatus = log.verification_status || 'pending';
                 this.showRejectInput = false;
                 this.rejectionReason = '';
                 this.imgRotation = 0;
                 this.imgZoom = 1;

                 if (log.next_due_date) {
                     this.verifyNextDueDate = typeof log.next_due_date === 'string' ? log.next_due_date.split('T')[0] : log.next_due_date;
                 } else if (log.category === 'deworming') {
                     this.verifyNextDueDate = this.calcDueDateMonths(3, this.verifyDate);
                 } else {
                     this.verifyNextDueDate = this.calcDueDateMonths(12, this.verifyDate);
                 }

                 this.showVerifyModal = true;
             },

             calcDueDateMonths(months, baseDate) {
                 const d = baseDate ? new Date(baseDate) : new Date();
                 if (isNaN(d.getTime())) return '';
                 d.setMonth(d.getMonth() + months);
                 const y = d.getFullYear();
                 const m = String(d.getMonth() + 1).padStart(2, '0');
                 const day = String(d.getDate()).padStart(2, '0');
                 return y + '-' + m + '-' + day;
             },

             setDueMonths(months) {
                 this.verifyNextDueDate = this.calcDueDateMonths(months, this.verifyDate);
             },

             rotateImage() {
                 this.imgRotation = (this.imgRotation + 90) % 360;
             },

             zoomIn() {
                 this.imgZoom = Math.min(this.imgZoom + 0.25, 3);
             },

             zoomOut() {
                 this.imgZoom = Math.max(this.imgZoom - 0.25, 0.75);
             },

             resetImg() {
                 this.imgRotation = 0;
                 this.imgZoom = 1;
             }
         }">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="px-4 py-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 rounded-2xl flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 flex items-center justify-center text-emerald-600 dark:text-emerald-300 flex-shrink-0 font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-xs sm:text-sm font-semibold">{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-lg leading-none cursor-pointer">&times;</button>
            </div>
        @endif

        {{-- Section Navigation Tabs (Compact) --}}
        <div class="flex items-center justify-between gap-2 border-b border-slate-200 dark:border-white/[0.08] pb-2.5 flex-wrap">
            <div class="flex items-center gap-1.5 flex-wrap">
                <a href="{{ route('adopters.index') }}" 
                   class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold transition bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08]">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                    <span>Adopter Profiles</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-white/[0.08] text-slate-700 dark:text-slate-300">{{ $totalApprovedAdopters }}</span>
                </a>

                <a href="{{ route('adopters.reports', ['tab' => 'monthly_updates']) }}" 
                   class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold transition {{ ($tab ?? 'monthly_updates') === 'monthly_updates' ? 'bg-[#199CA4] text-white shadow-2xs' : 'bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08]' }}">
                    <svg class="w-3.5 h-3.5 {{ ($tab ?? 'monthly_updates') === 'monthly_updates' ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Monthly Pet Updates</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ ($tab ?? 'monthly_updates') === 'monthly_updates' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-white/[0.08] text-slate-700 dark:text-slate-300' }}">{{ $totalReports }}</span>
                </a>

                <a href="{{ route('adopters.reports', ['tab' => 'vet_proofs']) }}" 
                   class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold transition {{ ($tab ?? 'monthly_updates') === 'vet_proofs' ? 'bg-[#199CA4] text-white shadow-2xs' : 'bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08]' }}">
                    <svg class="w-3.5 h-3.5 {{ ($tab ?? 'monthly_updates') === 'vet_proofs' ? 'text-white' : 'text-[#199CA4]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Vaccination updates</span>
                    @if($pendingVetLogsCount > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-white animate-pulse">{{ $pendingVetLogsCount }} Pending</span>
                    @else
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ ($tab ?? 'monthly_updates') === 'vet_proofs' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-white/[0.08] text-slate-700 dark:text-slate-300' }}">{{ $totalVetLogsCount }}</span>
                    @endif
                </a>
            </div>
        </div>

@if(($tab ?? 'monthly_updates') === 'vet_proofs')
    {{-- ==================== VET CLINIC PROOFS TAB ==================== --}}
    
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Vaccination updates</h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[#199CA4]/10 text-[#199CA4] dark:bg-[#199CA4]/20 dark:text-[#41C1CB] border border-[#199CA4]/20 dark:border-[#41C1CB]/30">
                    {{ $totalVetLogsCount }} {{ Str::plural('Record', $totalVetLogsCount) }}
                </span>
                @if($pendingVetLogsCount > 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 animate-pulse">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        {{ $pendingVetLogsCount }} Pending Review
                    </span>
                @endif
            </div>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Private veterinary clinic vaccination booklet stickers and receipts uploaded by adopters. Verify the proof photo to auto-calculate and activate the next booster schedule.</p>
        </div>
        
        {{-- Header Actions --}}
        <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
            <a href="{{ route('medical-logs.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-bold hover:bg-slate-50 dark:hover:bg-[#171923] hover:text-[#199CA4] dark:hover:text-[#41C1CB] transition shadow-xs">
                <svg class="w-4 h-4 text-[#199CA4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                <span>Medical Logs Registry</span>
            </a>
        </div>
    </div>

    {{-- Search & Filter Toolbar for Vet Proofs --}}
    <div class="bg-white dark:bg-[#12141C] p-3 sm:p-4 rounded-2xl sm:rounded-3xl shadow-xs border border-slate-200/80 dark:border-white/[0.07]">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            {{-- Quick Filter Pills --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                <a href="{{ route('adopters.reports', array_merge(request()->except('vet_status', 'page'), ['tab' => 'vet_proofs', 'vet_status' => 'all'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ ($vetStatus ?? 'all') === 'all' ? 'bg-[#199CA4] text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/[0.1]' }}">
                    All ({{ $totalVetLogsCount }})
                </a>
                <a href="{{ route('adopters.reports', array_merge(request()->except('vet_status', 'page'), ['tab' => 'vet_proofs', 'vet_status' => 'pending'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ ($vetStatus ?? '') === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40' }}">
                    @if($pendingVetLogsCount > 0)
                        <span class="w-2 h-2 rounded-full {{ ($vetStatus ?? '') === 'pending' ? 'bg-white' : 'bg-amber-500 animate-pulse' }}"></span>
                    @endif
                    <span>Pending ({{ $pendingVetLogsCount }})</span>
                </a>
                <a href="{{ route('adopters.reports', array_merge(request()->except('vet_status', 'page'), ['tab' => 'vet_proofs', 'vet_status' => 'verified'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ ($vetStatus ?? '') === 'verified' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40' }}">
                    Verified ({{ $verifiedVetLogsCount }})
                </a>
                <a href="{{ route('adopters.reports', array_merge(request()->except('vet_status', 'page'), ['tab' => 'vet_proofs', 'vet_status' => 'rejected'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ ($vetStatus ?? '') === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40' }}">
                    Rejected ({{ $rejectedVetLogsCount }})
                </a>
            </div>

            {{-- Search Input --}}
            <form method="GET" action="{{ route('adopters.reports') }}" class="flex items-center gap-2 w-full md:w-auto">
                <input type="hidden" name="tab" value="vet_proofs">
                @if(($vetStatus ?? 'all') !== 'all')
                    <input type="hidden" name="vet_status" value="{{ $vetStatus }}">
                @endif
                <div class="relative flex-1 md:w-80">
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}"
                           placeholder="Search pet, clinic, vaccine, adopter..." 
                           class="w-full pl-9 {{ !empty($search) ? 'pr-8' : 'pr-3' }} py-2 text-xs sm:text-sm bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-slate-800 dark:text-slate-100 rounded-xl focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition placeholder-slate-400 dark:placeholder-slate-500 font-medium">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    @if(!empty($search))
                        <a href="{{ route('adopters.reports', ['tab' => 'vet_proofs', 'vet_status' => $vetStatus]) }}" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs font-bold" title="Clear search">
                            &times;
                        </a>
                    @endif
                </div>
                <button type="submit" class="px-4 py-2 bg-[#199CA4] hover:bg-[#13787F] text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-xs shrink-0 cursor-pointer">
                    Search
                </button>
            </form>
        </div>
    </div>

    {{-- External Vet Proofs Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-5">
        @forelse($vetLogs as $log)
            @php
                $pet = $log->pet;
                $petName = $pet && $pet->name ? $pet->name : ('Pet #' . $log->pet_id);
                $adopter = $log->creator?->adoptersProfile;
                $adopterName = $adopter?->full_name ?: ($log->creator?->name ?: 'Adopter');
                $photoUrl = $log->proof_photo_url;
                $isPending = ($log->verification_status === 'pending');
                $isVerified = ($log->verification_status === 'verified');

                $logPayload = [
                    'id'                  => $log->id,
                    'pet_id'              => $log->pet_id,
                    'date'                => $log->date?->format('Y-m-d'),
                    'category'            => $log->category,
                    'vaccine_name'        => $log->vaccine_name,
                    'clinic_name'         => $log->clinic_name,
                    'administered_by'     => $log->administered_by,
                    'next_due_date'       => $log->next_due_date?->format('Y-m-d'),
                    'proof_photo_url'     => $log->proof_photo_url,
                    'verification_status' => $log->verification_status,
                    'pet' => [
                        'id'         => $pet?->id,
                        'name'       => $petName,
                        'type'       => $pet?->type,
                        'breed'      => $pet?->breed,
                        'photo_url'  => $pet?->photo_url,
                        'photo_path' => $pet?->photo_path,
                    ],
                    'creator' => [
                        'name'             => $adopterName,
                        'email'            => $log->creator?->email,
                        'adopters_profile' => [
                            'phone' => $adopter?->phone,
                        ],
                    ],
                ];
            @endphp

            <div class="bg-white dark:bg-[#12141C] rounded-2xl sm:rounded-3xl border border-slate-200/80 dark:border-white/[0.07] shadow-sm dark:shadow-xl dark:shadow-black/40 overflow-hidden hover:border-[#199CA4]/40 transition-all duration-200 flex flex-col sm:flex-row">
                {{-- Left: Proof Thumbnail Container (Compact 140px on sm+) --}}
                <div class="relative sm:w-36 md:w-44 shrink-0 h-44 sm:h-auto bg-slate-950 overflow-hidden group cursor-pointer"
                     @click='openVerifyModal(@json($logPayload))'
                     title="Click to inspect proof photo">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="Vet Card Proof" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <div class="absolute inset-0 bg-black/30 group-hover:bg-black/50 transition-colors flex flex-col items-center justify-center gap-1.5">
                            <div class="w-9 h-9 rounded-full bg-black/60 backdrop-blur-xs flex items-center justify-center text-white shadow-md group-hover:scale-110 transition-transform">
                                <svg class="w-4 h-4 text-[#41C1CB]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                            </div>
                            <span class="text-[10px] font-bold text-white bg-black/70 backdrop-blur-xs px-2.5 py-0.5 rounded-full shadow-xs">
                                Inspect Proof
                            </span>
                        </div>
                    @else
                        <div class="w-full h-full min-h-[140px] flex flex-col items-center justify-center text-slate-500 text-xs p-3 text-center">
                            <svg class="w-6 h-6 mb-1 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>No Photo</span>
                        </div>
                    @endif
                    <div class="absolute bottom-2 left-2 px-2 py-0.5 rounded-md bg-black/80 backdrop-blur-xs text-white text-[9px] font-black uppercase tracking-wider">
                        {{ ucfirst(str_replace('_', ' ', $log->category)) }}
                    </div>
                </div>

                {{-- Right: Content & Information --}}
                <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between gap-3 min-w-0">
                    <div>
                        {{-- Top Header Row: Pet Identity & Status --}}
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                @if($pet?->photo_path)
                                    <img src="{{ asset('storage/'.ltrim($pet->photo_path, '/')) }}" alt="{{ $petName }}" class="w-9 h-9 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                                @else
                                    <div class="w-9 h-9 rounded-xl bg-[#199CA4]/10 text-[#199CA4] flex items-center justify-center font-black text-xs shrink-0">
                                        #{{ $log->pet_id }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white truncate">{{ $petName }}</h3>
                                        <span class="text-[10px] text-slate-400 font-mono">#{{ $log->pet_id }}</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 truncate">{{ ucfirst($pet?->type ?? 'Pet') }} &bull; {{ $pet?->breed ?? 'Mixed Breed' }}</p>
                                </div>
                            </div>

                            {{-- Status Pill --}}
                            @if($isPending)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/25 shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Pending
                                </span>
                            @elseif($isVerified)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/25 shrink-0">
                                    Verified
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/25 shrink-0">
                                    Rejected
                                </span>
                            @endif
                        </div>

                        {{-- Clinical Details --}}
                        <div class="mt-2.5 space-y-2">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Vaccine / Treatment</span>
                                <p class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-white truncate">
                                    {{ $log->vaccine_name ?: ($log->category === 'vaccination' ? 'Core Vaccine' : 'Routine Deworming') }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-[11px] p-2.5 rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-100 dark:border-white/[0.04]">
                                <div>
                                    <span class="text-slate-400 block text-[10px] font-medium">Clinic:</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300 truncate block">{{ $log->clinic_name ?: 'Private Clinic' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px] font-medium">Adopter:</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300 truncate block">{{ $adopterName }}</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-[11px] pt-0.5">
                                <div class="flex items-center gap-1 text-slate-500 dark:text-slate-400">
                                    <span>Given:</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ $log->date?->format('M d, Y') }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="text-slate-400">Next Booster:</span>
                                    @if($log->next_due_date)
                                        <span class="font-bold text-[#199CA4] dark:text-[#41C1CB]">{{ $log->next_due_date->format('M d, Y') }}</span>
                                    @else
                                        <span class="text-amber-600 dark:text-amber-400 font-semibold text-[10px]">Pending review</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Row --}}
                    <div class="pt-2 border-t border-slate-100 dark:border-white/[0.06]">
                        <button type="button" 
                                @click='openVerifyModal(@json($logPayload))'
                                class="w-full py-2 px-3.5 rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center justify-center gap-1.5 {{ $isPending ? 'bg-[#199CA4] hover:bg-[#13787F] text-white shadow-[#199CA4]/20' : 'bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.1] text-slate-700 dark:text-slate-200' }}">
                            @if($isPending)
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Inspect Proof & Verify</span>
                            @else
                                <span>View Submission Details</span>
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center bg-white dark:bg-[#12141C] rounded-3xl border border-slate-200/80 dark:border-white/[0.07] p-8">
                <div class="w-14 h-14 rounded-2xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h3 class="text-base font-extrabold text-slate-800 dark:text-white">No Vaccination Updates Found</h3>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                    When adopters upload vaccination booklet stickers or clinic receipts from their private veterinary visits, they will appear here for staff verification.
                </p>
                @if(!empty($search) || ($vetStatus ?? 'all') !== 'all')
                    <div class="mt-4">
                        <a href="{{ route('adopters.reports', ['tab' => 'vet_proofs']) }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 text-xs font-bold text-slate-700 dark:text-slate-200 transition">
                            Clear Filters & Search
                        </a>
                    </div>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Pagination for Vet Proofs --}}
    @if($vetLogs->hasPages())
        <div class="p-4 bg-white dark:bg-[#12141C] rounded-2xl border border-slate-200/80 dark:border-white/[0.07] shadow-xs">
            {{ $vetLogs->links() }}
        </div>
    @endif

@else
    {{-- ==================== MONTHLY PET UPDATES TAB ==================== --}}

    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Monthly Pet Updates Report</h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[#199CA4]/10 text-[#199CA4] dark:bg-[#199CA4]/20 dark:text-[#41C1CB] border border-[#199CA4]/20 dark:border-[#41C1CB]/30">
                    {{ $totalReports }} Check-in Reports
                </span>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Official post-adoption health reports, condition photos, and recorded weights submitted monthly by adopters via the mobile application.</p>
        </div>
        
        {{-- Header Actions --}}
        <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
            <a href="{{ route('adopters.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-bold hover:bg-slate-50 dark:hover:bg-[#171923] hover:text-[#199CA4] dark:hover:text-[#41C1CB] transition shadow-xs">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Adopter Profiles</span>
            </a>
        </div>
    </div>

        {{-- Metric Overview Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            {{-- 1. Total Reports --}}
            <a href="{{ route('adopters.reports', ['status' => 'all', 'period' => 'all']) }}"
               class="p-4 rounded-2xl border transition-all duration-200 flex flex-col justify-between {{ ($status ?? 'all') === 'all' && ($period ?? 'all') === 'all' ? 'bg-[#199CA4]/10 border-[#199CA4] dark:bg-[#199CA4]/15 dark:border-[#41C1CB] ring-2 ring-[#199CA4]/20' : 'bg-white dark:bg-[#12141C] border-slate-200/80 dark:border-white/[0.07] hover:border-slate-300 dark:hover:border-white/[0.15] hover:shadow-xs' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Updates</span>
                    <div class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black text-slate-900 dark:text-white">{{ $totalReports }}</span>
                    <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500">all time</span>
                </div>
            </a>

            {{-- 2. This Month Reports --}}
            <a href="{{ route('adopters.reports', ['period' => 'this_month']) }}"
               class="p-4 rounded-2xl border transition-all duration-200 flex flex-col justify-between {{ ($period ?? '') === 'this_month' ? 'bg-blue-50 border-blue-500 dark:bg-blue-950/30 dark:border-blue-500 ring-2 ring-blue-500/20' : 'bg-white dark:bg-[#12141C] border-slate-200/80 dark:border-white/[0.07] hover:border-slate-300 dark:hover:border-white/[0.15] hover:shadow-xs' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400">This Month</span>
                    <div class="w-7 h-7 rounded-xl bg-blue-100 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black text-blue-700 dark:text-blue-400">{{ $thisMonthReports }}</span>
                    <span class="text-[11px] font-semibold text-blue-600/70 dark:text-blue-500/70">submitted</span>
                </div>
            </a>

            {{-- 3. Healthy & Active --}}
            <a href="{{ route('adopters.reports', ['status' => 'healthy']) }}"
               class="p-4 rounded-2xl border transition-all duration-200 flex flex-col justify-between {{ ($status ?? '') === 'healthy' ? 'bg-emerald-50 border-emerald-500 dark:bg-emerald-950/30 dark:border-emerald-500 ring-2 ring-emerald-500/20' : 'bg-white dark:bg-[#12141C] border-slate-200/80 dark:border-white/[0.07] hover:border-slate-300 dark:hover:border-white/[0.15] hover:shadow-xs' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Healthy & Active</span>
                    <div class="w-7 h-7 rounded-xl bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black text-emerald-700 dark:text-emerald-400">{{ $healthyReports }}</span>
                    <span class="text-[11px] font-semibold text-emerald-600/70 dark:text-emerald-500/70">in good condition</span>
                </div>
            </a>

            {{-- 4. Attention Needed --}}
            <a href="{{ route('adopters.reports', ['status' => 'minor_issue']) }}"
               class="p-4 rounded-2xl border transition-all duration-200 flex flex-col justify-between {{ in_array(($status ?? ''), ['minor_issue', 'under_treatment']) ? 'bg-amber-50 border-amber-500 dark:bg-amber-950/30 dark:border-amber-500 ring-2 ring-amber-500/20' : 'bg-white dark:bg-[#12141C] border-slate-200/80 dark:border-white/[0.07] hover:border-slate-300 dark:hover:border-white/[0.15] hover:shadow-xs' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Needs Attention</span>
                    <div class="w-7 h-7 rounded-xl bg-amber-100 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black text-amber-700 dark:text-amber-400">{{ $attentionReports }}</span>
                    <span class="text-[11px] font-semibold text-amber-600/70 dark:text-amber-500/70">minor / treatment</span>
                </div>
            </a>

            {{-- 5. Deceased / Memorial --}}
            <a href="{{ route('adopters.reports', ['status' => 'deceased']) }}"
               class="p-4 rounded-2xl border transition-all duration-200 flex flex-col justify-between {{ ($status ?? '') === 'deceased' ? 'bg-slate-900 text-white border-slate-700 dark:bg-slate-800 dark:border-slate-600 ring-2 ring-slate-500/30' : 'bg-white dark:bg-[#12141C] border-slate-200/80 dark:border-white/[0.07] hover:border-slate-300 dark:hover:border-white/[0.15] hover:shadow-xs' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider {{ ($status ?? '') === 'deceased' ? 'text-slate-200' : 'text-slate-600 dark:text-slate-400' }}">Passed Away</span>
                    <div class="w-7 h-7 rounded-xl {{ ($status ?? '') === 'deceased' ? 'bg-white/10 text-white' : 'bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300' }} flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black {{ ($status ?? '') === 'deceased' ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ $deceasedReports ?? 0 }}</span>
                    <span class="text-[11px] font-semibold {{ ($status ?? '') === 'deceased' ? 'text-slate-300' : 'text-slate-400 dark:text-slate-500' }}">memorial</span>
                </div>
            </a>
        </div>

        {{-- Search & Filter Toolbar --}}
        <div class="bg-white dark:bg-[#12141C] p-4 rounded-2xl sm:rounded-3xl shadow-xs border border-slate-200/80 dark:border-white/[0.07]">
            <form method="GET" action="{{ route('adopters.reports') }}" class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 sm:gap-4">
                
                {{-- Left filters (Status, Period, Species) --}}
                <div class="flex items-center gap-2.5 flex-wrap">
                    {{-- Status Filter --}}
                    <div>
                        <select name="status" onchange="this.form.submit()"
                                class="bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition cursor-pointer">
                            <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>All Health Statuses</option>
                            <option value="healthy" {{ ($status ?? '') === 'healthy' ? 'selected' : '' }}>Healthy & Active</option>
                            <option value="minor_issue" {{ ($status ?? '') === 'minor_issue' ? 'selected' : '' }}>Minor Issue</option>
                            <option value="under_treatment" {{ ($status ?? '') === 'under_treatment' ? 'selected' : '' }}>Under Treatment</option>
                            <option value="deceased" {{ ($status ?? '') === 'deceased' ? 'selected' : '' }}>Passed Away / Deceased</option>
                        </select>
                    </div>

                    {{-- Period Filter --}}
                    <div>
                        <select name="period" onchange="this.form.submit()"
                                class="bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition cursor-pointer">
                            <option value="all" {{ ($period ?? 'all') === 'all' ? 'selected' : '' }}>All Time</option>
                            <option value="this_month" {{ ($period ?? '') === 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="last_month" {{ ($period ?? '') === 'last_month' ? 'selected' : '' }}>Last Month</option>
                            <option value="last_3_months" {{ ($period ?? '') === 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
                        </select>
                    </div>

                    {{-- Species Filter --}}
                    <div>
                        <select name="species" onchange="this.form.submit()"
                                class="bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] focus:outline-none transition cursor-pointer">
                            <option value="all" {{ ($species ?? 'all') === 'all' ? 'selected' : '' }}>All Species</option>
                            <option value="dog" {{ ($species ?? '') === 'dog' ? 'selected' : '' }}>Dogs</option>
                            <option value="cat" {{ ($species ?? '') === 'cat' ? 'selected' : '' }}>Cats</option>
                        </select>
                    </div>

                    @if(($status ?? 'all') !== 'all' || ($period ?? 'all') !== 'all' || ($species ?? 'all') !== 'all' || !empty($search))
                        <a href="{{ route('adopters.reports') }}" class="text-xs font-bold text-[#199CA4] hover:underline ml-1 cursor-pointer">
                            Reset Filters
                        </a>
                    @endif
                </div>

                {{-- Search Bar --}}
                <div class="flex items-center gap-2 w-full lg:w-auto">
                    <div class="relative flex-1 lg:w-80">
                        <input type="text" 
                               name="search" 
                               value="{{ $search }}"
                                placeholder="Search by adopter, ADP code, pet, breed..." 
                                class="w-full pl-10 pr-8 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-slate-800 dark:text-slate-100 rounded-xl focus:bg-white dark:focus:bg-[#0C0D13] focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition placeholder-slate-400 dark:placeholder-slate-500 font-medium">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        @if(!empty($search))
                            <a href="{{ route('adopters.reports', request()->except('search', 'page')) }}" class="absolute right-3 top-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm font-bold">
                                &times;
                            </a>
                        @endif
                    </div>
                    <button type="submit" class="px-4 py-2 bg-[#199CA4] hover:bg-[#13787F] text-white text-xs sm:text-sm font-extrabold rounded-xl transition shadow-xs flex items-center gap-1.5 shrink-0 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <span>Search</span>
                    </button>
                </div>

            </form>
        </div>

        {{-- Reports Feed Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @forelse($reports as $report)
                @php
                    $pet = $report->pet;
                    $petName = $pet && $pet->name ? $pet->name : 'Pet no. ' . $report->pet_id;
                    $adopter = $report->user?->adoptersProfile;
                    $adopterName = $adopter?->full_name ?: ($report->user?->name ?: 'Adopter');
                    $adopterCode = $adopter?->adopter_code ?: ($report->user ? sprintf('ADP-%04d', $report->user->id) : 'ADP');
                    $adopterAvatar = $adopter?->avatar_url;
                    $adopterInitials = $adopter?->initials ?: strtoupper(substr($adopterName, 0, 2));
                    $adopterContact = $adopter?->phone ?: ($report->user?->email ?: 'Verified Adopter');
                    $photoUrl = $report->photo_url;
                    $checkInDateStr = $report->check_in_date ? $report->check_in_date->format('F d, Y') : $report->created_at->format('F d, Y');
                    $weightStr = $report->weight ? number_format($report->weight, 1) . ' kg' : 'Not recorded';
                @endphp

                <div class="bg-white dark:bg-[#12141C] rounded-2xl sm:rounded-3xl border border-slate-200/80 dark:border-white/[0.07] shadow-sm dark:shadow-xl dark:shadow-black/40 overflow-hidden hover:border-[#199CA4]/40 transition-all duration-200 flex flex-col justify-between">
                    
                    <div>
                        {{-- Card Header: Adopter Identity First --}}
                        <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-white/[0.06] bg-slate-50/50 dark:bg-[#171923] flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                {{-- Adopter Avatar / Initials --}}
                                <div class="w-11 h-11 rounded-xl overflow-hidden shrink-0 border border-slate-200 dark:border-white/[0.08] bg-slate-100 dark:bg-[#12141C] relative">
                                    @if($adopterAvatar)
                                        <img src="{{ $adopterAvatar }}" alt="{{ $adopterName }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-[#199CA4] to-[#13787F] text-white flex items-center justify-center font-black text-xs">
                                            {{ $adopterInitials }}
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white truncate leading-tight">
                                            {{ $adopterName }}
                                        </h3>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold font-mono bg-slate-200/80 dark:bg-white/[0.08] text-slate-700 dark:text-slate-300 border border-slate-300/60 dark:border-white/[0.08]">
                                            {{ $adopterCode }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5 font-medium">
                                        {{ $adopterContact }}
                                    </p>
                                </div>
                            </div>

                            {{-- Health Status Badge --}}
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold tracking-wide shrink-0 border
                                {{ $report->health_status === 'healthy' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60' : '' }}
                                {{ $report->health_status === 'minor_issue' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60' : '' }}
                                {{ $report->health_status === 'under_treatment' ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60' : '' }}
                                {{ $report->health_status === 'deceased' ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-slate-100 border-slate-700 font-extrabold shadow-2xs' : (!in_array($report->health_status, ['healthy', 'minor_issue', 'under_treatment', 'deceased']) ? 'bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 border-slate-200 dark:border-white/[0.08]' : '') }}">
                                {{ $report->health_status === 'healthy' ? 'Healthy & Active' : ($report->health_status === 'minor_issue' ? 'Minor Issue' : ($report->health_status === 'under_treatment' ? 'Under Treatment' : ($report->health_status === 'deceased' ? 'Passed Away / Deceased' : ucfirst($report->health_status ?? 'Submitted')))) }}
                            </span>
                        </div>

                        {{-- Life Event Banner for Deceased Reports --}}
                        @if($report->health_status === 'deceased')
                            @if($report->review_decision === 'cleared_accidental')
                                <div class="px-4 sm:px-5 py-2.5 bg-emerald-950 text-white flex items-center justify-between text-xs font-bold border-b border-emerald-800">
                                    <div class="flex items-center gap-2 text-emerald-300">
                                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Life Event: Passed Away (Verified Accidental / Natural)</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase tracking-wider font-extrabold bg-emerald-500/20 text-emerald-200 border border-emerald-400/30">Slot Restored</span>
                                </div>
                            @elseif($report->review_decision === 'neglect_confirmed')
                                <div class="px-4 sm:px-5 py-2.5 bg-rose-950 text-white flex items-center justify-between text-xs font-bold border-b border-rose-800">
                                    <div class="flex items-center gap-2 text-rose-300">
                                        <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>Life Event: Neglect Confirmed (Adopter Restricted)</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase tracking-wider font-extrabold bg-rose-500/20 text-rose-200 border border-rose-400/30">Sanctioned</span>
                                </div>
                            @else
                                <div class="px-4 sm:px-5 py-2.5 bg-amber-950 text-white flex items-center justify-between text-xs font-bold border-b border-amber-800">
                                    <div class="flex items-center gap-2 text-amber-300">
                                        <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Life Event: Pet Passed Away (Awaiting Staff Verification)</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase tracking-wider font-extrabold bg-amber-500/20 text-amber-200 border border-amber-400/30">Needs Review</span>
                                </div>
                            @endif
                        @endif

                        {{-- Adopted Pet Subheader Bar --}}
                        <div class="px-4 sm:px-5 py-2.5 bg-slate-100/70 dark:bg-[#0E1017] border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                {{-- Mini Pet Avatar --}}
                                <div class="w-6 h-6 rounded-lg overflow-hidden shrink-0 border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#171923]">
                                    @if($pet && $pet->photo_url)
                                        <img src="{{ $pet->photo_url }}" alt="{{ $petName }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-[10px]">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 512 512"><path d="M226.5 92.9c14.3 42.9-.3 86.2-32.6 96.8s-70.1-15.6-84.4-58.5s.3-86.2 32.6-96.8s70.1 15.6 84.4 58.5zM100.4 198.6c18.9 32.4 14.3 70.1-10.2 84.1s-59.7-.9-78.5-33.3S-2.7 179.3 21.8 165.3s59.7 .9 78.5 33.3zM69.2 401.2C121.6 259.9 214.7 224 256 224s134.4 35.9 186.8 177.2c3.6 9.7 5.2 20.1 5.2 30.5l0 1.6c0 25.8-20.9 46.7-46.7 46.7c-11.5 0-22.9-1.4-34-4.2l-88-22c-15.3-3.8-31.3-3.8-46.6 0l-88 22c-11.1 2.8-22.5 4.2-34 4.2C84.9 480 64 459.1 64 433.3l0-1.6c0-10.4 1.6-20.8 5.2-30.5zM421.8 282.7c-24.5-14-29.1-51.7-10.2-84.1s54-47.3 78.5-33.3s29.1 51.7 10.2 84.1s-54 47.3-78.5 33.3zM318.1 189.7c-32.3-10.6-46.9-53.9-32.6-96.8s52.1-69.1 84.4-58.5s46.9 53.9 32.6 96.8s-52.1 69.1-84.4 58.5z"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate">
                                    Adopted Pet: <span class="font-extrabold text-[#199CA4] dark:text-[#41C1CB]">{{ $petName }}</span>
                                </span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 truncate hidden sm:inline">
                                    ({{ $pet ? ucfirst($pet->type) . ' • ' . ($pet->breed ?? 'Mixed') : 'Adopted' }})
                                </span>
                            </div>
                            <span class="text-[11px] text-slate-400 font-medium whitespace-nowrap shrink-0">
                                {{ $report->created_at->diffForHumans() }}
                            </span>
                        </div>

                        {{-- Photo Preview Container --}}
                        <div class="relative w-full h-52 sm:h-56 bg-slate-100 dark:bg-[#0C0D13] overflow-hidden group {{ $photoUrl ? 'cursor-pointer' : '' }}"
                             @if($photoUrl)
                             data-photo="{{ $photoUrl }}"
                             data-title="{{ $adopterName }} - {{ $petName }} Monthly Check-in"
                             data-date="{{ $checkInDateStr }}"
                             data-weight="{{ $weightStr }}"
                             data-status="{{ $report->health_status }}"
                             data-notes="{{ $report->notes }}"
                             @click="openFromCard($event)"
                             @endif>
                            @if($photoUrl)
                                <img src="{{ $photoUrl }}" alt="{{ $adopterName }} - {{ $petName }} Check-in" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-1.5 text-white p-3">
                                    <svg class="w-7 h-7 drop-shadow-md" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                                    <span class="text-xs font-bold uppercase tracking-wider">Click to View Full Photo</span>
                                </div>
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 dark:text-slate-500 gap-2 p-4 text-center">
                                    <svg class="w-8 h-8 opacity-60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span class="text-xs font-semibold">No Check-in Photo Provided</span>
                                </div>
                            @endif

                            {{-- Floating Weight Pill --}}
                            @if($report->weight)
                                <div class="absolute bottom-3 left-3 px-3 py-1 rounded-xl bg-black/70 backdrop-blur-xs text-white text-xs font-extrabold flex items-center gap-1.5 border border-white/20 shadow-md">
                                    <svg class="w-3.5 h-3.5 text-[#41C1CB]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                                    <span>{{ $weightStr }}</span>
                                </div>
                            @endif

                            {{-- Floating Date Pill --}}
                            <div class="absolute top-3 right-3 px-2.5 py-1 rounded-lg bg-black/60 backdrop-blur-xs text-white text-[11px] font-bold border border-white/15">
                                {{ $checkInDateStr }}
                            </div>
                        </div>

                        {{-- Card Details: Remarks & Assessments --}}
                        <div class="p-4 sm:p-5 space-y-3">

                            {{-- Adopter Remarks / Notes --}}
                            @if(!empty($report->notes))
                                @if($report->health_status === 'deceased')
                                    <div class="p-3.5 rounded-xl bg-rose-50/60 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/60 text-xs text-slate-800 dark:text-slate-200 space-y-1.5">
                                        <div class="flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-wider text-rose-700 dark:text-rose-400">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Reported Passing & Circumstances:</span>
                                        </div>
                                        <p class="font-medium leading-relaxed whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $report->notes }}</p>
                                    </div>
                                @else
                                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06] text-xs text-slate-700 dark:text-slate-300 space-y-1">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Adopter Observations:</span>
                                        <p class="font-medium leading-relaxed italic">"{{ $report->notes }}"</p>
                                    </div>
                                @endif
                            @else
                                <div class="p-3 rounded-xl bg-slate-50/50 dark:bg-[#171923]/50 border border-slate-100 dark:border-white/[0.04] text-xs text-slate-400 dark:text-slate-500 italic">
                                    No written remarks provided for this update.
                                </div>
                            @endif

                            {{-- Staff Remarks (if recorded) --}}
                            @if(!empty($report->staff_remarks))
                                <div class="p-2.5 rounded-xl bg-teal-50/70 dark:bg-[#199CA4]/10 border border-teal-200/70 dark:border-[#199CA4]/30 text-xs text-teal-900 dark:text-teal-200 space-y-0.5">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-teal-700 dark:text-teal-300 block">Staff Assessment:</span>
                                    <p class="font-medium whitespace-pre-line">{{ $report->staff_remarks }}</p>
                                </div>
                            @endif

                        </div>
                    </div>

                    {{-- Card Footer --}}
                    <div class="p-3 sm:p-4 border-t border-slate-100 dark:border-white/[0.06] bg-slate-50/40 dark:bg-[#171923]/40 flex items-center justify-between gap-2">
                        @if($photoUrl)
                            <button type="button"
                                    data-photo="{{ $photoUrl }}"
                                    data-title="{{ $adopterName }} - {{ $petName }} Monthly Check-in"
                                    data-date="{{ $checkInDateStr }}"
                                    data-weight="{{ $weightStr }}"
                                    data-status="{{ $report->health_status }}"
                                    data-notes="{{ $report->notes }}"
                                    @click="openFromCard($event)"
                                    class="text-xs font-bold text-[#199CA4] hover:text-[#13787F] dark:text-[#41C1CB] hover:underline flex items-center gap-1 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Inspect Photo</span>
                            </button>
                        @else
                            <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>No Photo</span>
                            </span>
                        @endif

                        <div class="flex items-center gap-2">
                            @if($report->health_status === 'deceased')
                                <button type="button"
                                        @click="openReviewModal({{ $report->id }}, '{{ addslashes($petName) }}', '{{ addslashes($adopterName) }}', '{{ addslashes($checkInDateStr) }}', '{{ addslashes(str_replace(["\r", "\n"], ' ', $report->notes ?? '')) }}', '{{ $report->review_decision ?? 'cleared_accidental' }}', '{{ addslashes(str_replace(["\r", "\n"], ' ', $report->staff_remarks ?? '')) }}')"
                                        class="px-2.5 py-1.5 rounded-xl {{ empty($report->review_decision) || $report->review_decision === 'pending' ? 'bg-amber-600 hover:bg-amber-700 text-white shadow-xs font-bold' : 'bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 text-slate-700 dark:text-slate-300 font-semibold' }} text-xs flex items-center gap-1 transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    <span>{{ empty($report->review_decision) || $report->review_decision === 'pending' ? 'Review Life Event' : 'Edit Decision' }}</span>
                                </button>
                            @endif

                            <a href="{{ route('adopters.index', ['search' => $adopterCode]) }}"
                               class="text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors flex items-center gap-1">
                                <span>Adopter Profile</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>

                </div>
            @empty
                <div class="col-span-full bg-white dark:bg-[#12141C] rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 dark:border-white/[0.07] p-12 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-white/[0.04] text-slate-400 flex items-center justify-center text-xl mx-auto mb-3 font-bold">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">No Monthly Updates Found</h3>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                        No monthly pet health updates matched your criteria. When adopters submit check-in reports via the mobile app, they will appear here.
                    </p>
                    @if(!empty($search) || ($status ?? 'all') !== 'all' || ($period ?? 'all') !== 'all' || ($species ?? 'all') !== 'all')
                        <div class="mt-4">
                            <a href="{{ route('adopters.reports') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 text-xs font-bold text-slate-700 dark:text-slate-200 transition">
                                Clear Filters & Search
                            </a>
                        </div>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($reports->hasPages())
            <div class="p-4 bg-white dark:bg-[#12141C] rounded-2xl border border-slate-200/80 dark:border-white/[0.07] shadow-xs">
                {{ $reports->links() }}
            </div>
        @endif
@endif

        {{-- Photo Zoom Modal --}}
        <div x-show="showPhotoModal" 
             x-cloak 
             class="fixed inset-0 z-[70] overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true"
             @keydown.escape.window="showPhotoModal = false">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/85 backdrop-blur-md" 
                     @click="showPhotoModal = false"
                     x-show="showPhotoModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"></div>

                <div class="inline-block w-full max-w-2xl p-5 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-[#12141C] rounded-3xl shadow-2xl border border-slate-200 dark:border-white/[0.1] relative z-10"
                     x-show="showPhotoModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.outside="showPhotoModal = false">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-start justify-between pb-3 mb-3 border-b border-slate-200/80 dark:border-white/[0.06] gap-3">
                        <div class="min-w-0">
                            <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white truncate" x-text="photoModalTitle"></h3>
                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium" x-text="photoModalDate"></span>
                                <template x-if="photoModalWeight">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB]">
                                        Weight: <span class="ml-1" x-text="photoModalWeight"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                        <button type="button" @click="showPhotoModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-2xl font-light leading-none cursor-pointer shrink-0">&times;</button>
                    </div>

                    {{-- High-Res Image Display --}}
                    <div class="relative w-full max-h-[65vh] rounded-2xl overflow-hidden bg-black/90 flex items-center justify-center border border-slate-200 dark:border-white/[0.08]">
                        <img :src="photoModalUrl" :alt="photoModalTitle" class="max-w-full max-h-[65vh] object-contain rounded-xl">
                    </div>

                    {{-- Observations Note if present --}}
                    <template x-if="photoModalNotes">
                        <div class="mt-3 p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-100 dark:border-white/[0.06] text-xs text-slate-700 dark:text-slate-300">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-0.5">Adopter Observations:</span>
                            <p class="font-medium italic" x-text="'&quot;' + photoModalNotes + '&quot;'"></p>
                        </div>
                    </template>

                    {{-- Additional Modal Metadata & Actions --}}
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between gap-3 flex-wrap">
                        <a :href="photoModalUrl" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-[#199CA4] hover:text-[#13787F] dark:text-[#41C1CB] hover:underline inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span>Open Original Full Size</span>
                        </a>

                        <button type="button" @click="showPhotoModal = false" class="px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.1] rounded-xl transition cursor-pointer">
                            Close
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>

        {{-- Deceased Life Event Review Modal --}}
        <div x-show="showReviewModal" 
             x-cloak 
             class="fixed inset-0 z-[75] overflow-y-auto" 
             aria-labelledby="review-modal-title" 
             role="dialog" 
             aria-modal="true"
             @keydown.escape.window="showReviewModal = false">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-xs" 
                     @click="showReviewModal = false"
                     x-show="showReviewModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"></div>

                <div class="relative inline-block w-full max-w-xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-[#12141C] shadow-2xl rounded-3xl border border-slate-200 dark:border-white/[0.1] z-10"
                     x-show="showReviewModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-white/[0.08]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white" id="review-modal-title">
                                    Review Deceased Life Event
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Companion: <span class="font-bold text-slate-700 dark:text-slate-200" x-text="reviewPetName"></span> | Adopter: <span class="font-bold text-slate-700 dark:text-slate-200" x-text="reviewAdopterName"></span>
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="showReviewModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-2xl font-light leading-none cursor-pointer">&times;</button>
                    </div>

                    {{-- Adopter's Submitted Statement --}}
                    <div class="mt-4 p-4 rounded-2xl bg-slate-50 dark:bg-[#171923] border border-slate-200/80 dark:border-white/[0.06] space-y-1">
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <span>Adopter's Reported Circumstances:</span>
                            <span x-text="reviewCheckInDate"></span>
                        </div>
                        <p class="text-xs font-medium text-slate-800 dark:text-slate-200 leading-relaxed italic whitespace-pre-line" x-text="'&quot;' + reviewAdopterNotes + '&quot;'"></p>
                    </div>

                    {{-- Form --}}
                    <form :action="'{{ url('/adopters/reports') }}/' + reviewReportId + '/review'" method="POST" class="mt-5 space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-2">
                                Official Shelter Determination:
                            </label>
                            <div class="space-y-2.5">
                                {{-- Option 1: Cleared Accidental / Natural --}}
                                <label class="p-3.5 rounded-2xl border flex items-start gap-3 cursor-pointer transition"
                                       :class="reviewCurrentDecision === 'cleared_accidental' ? 'border-emerald-500 bg-emerald-50/60 dark:bg-emerald-950/20 ring-1 ring-emerald-500/40' : 'border-slate-200 dark:border-white/[0.08] hover:bg-slate-50 dark:hover:bg-white/[0.02]'">
                                    <input type="radio" name="decision" value="cleared_accidental" x-model="reviewCurrentDecision" class="mt-1 text-emerald-600 focus:ring-emerald-500">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-extrabold text-emerald-900 dark:text-emerald-300">Acceptable Cause (Natural Passing / Accidental - No Neglect)</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200">Restores Slot</span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 dark:text-slate-400 leading-normal">
                                            Passing was due to natural causes, illness despite care, or unavoidable accident. Adopter remains in good standing. This deceased companion is removed from their active count, freeing up 1 slot under the 3-pet household limit.
                                        </p>
                                    </div>
                                </label>

                                {{-- Option 2: Neglect Confirmed --}}
                                <label class="p-3.5 rounded-2xl border flex items-start gap-3 cursor-pointer transition"
                                       :class="reviewCurrentDecision === 'neglect_confirmed' ? 'border-rose-500 bg-rose-50/60 dark:bg-rose-950/20 ring-1 ring-rose-500/40' : 'border-slate-200 dark:border-white/[0.08] hover:bg-slate-50 dark:hover:bg-white/[0.02]'">
                                    <input type="radio" name="decision" value="neglect_confirmed" x-model="reviewCurrentDecision" class="mt-1 text-rose-600 focus:ring-rose-500">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-extrabold text-rose-900 dark:text-rose-300">Adopter Neglect / Welfare Violation Confirmed</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-rose-100 dark:bg-rose-900 text-rose-800 dark:text-rose-200">Restricts Adopter</span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 dark:text-slate-400 leading-normal">
                                            Investigation confirms death was caused by neglect, abandonment, or care violation. The adopter's profile will be flagged as Restricted, blocking them from submitting future adoption applications.
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Staff Remarks --}}
                        <div>
                            <label class="block text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1.5">
                                Shelter Assessment Remarks & Evidence (Required):
                            </label>
                            <textarea name="staff_remarks" 
                                      rows="3" 
                                      required 
                                      x-model="reviewCurrentRemarks"
                                      placeholder="Provide the official shelter explanation, veterinary documentation, or justification for this decision..."
                                      class="w-full text-xs rounded-xl border-slate-300 dark:border-white/[0.1] bg-white dark:bg-[#171923] text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                        </div>

                        {{-- Modal Action Buttons --}}
                        <div class="pt-3 border-t border-slate-100 dark:border-white/[0.08] flex items-center justify-end gap-3">
                            <button type="button" @click="showReviewModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.1] rounded-xl transition cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-black dark:bg-[#199CA4] dark:hover:bg-[#13787F] rounded-xl transition cursor-pointer shadow-sm">
                                Save Official Decision
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- Side-by-Side Vet Clinic Proof Verification Modal --}}
        <div x-show="showVerifyModal" 
             x-cloak 
             class="fixed inset-0 z-[80] overflow-y-auto" 
             aria-labelledby="verify-modal-title" 
             role="dialog" 
             aria-modal="true"
             @keydown.escape.window="showVerifyModal = false">
            <div class="flex items-center justify-center min-h-screen px-3 sm:px-4 py-6 text-center">
                <div class="fixed inset-0 transition-opacity bg-black/85 backdrop-blur-sm" 
                     @click="showVerifyModal = false"
                     x-show="showVerifyModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"></div>

                <div class="relative inline-block w-full max-w-5xl my-4 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-[#12141C] shadow-2xl rounded-3xl border border-slate-200 dark:border-white/[0.1] z-10"
                     x-show="showVerifyModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-150"
                     @click.outside="showVerifyModal = false">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between p-4 sm:p-5 border-b border-slate-100 dark:border-white/[0.08]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white" id="verify-modal-title">
                                    Veterinary Proof Verification
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Pet: <span class="font-bold text-slate-800 dark:text-slate-200" x-text="verifyPetName"></span> &bull; Adopter: <span class="font-bold text-slate-800 dark:text-slate-200" x-text="verifyAdopterName"></span>
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="showVerifyModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-2xl font-light leading-none cursor-pointer">&times;</button>
                    </div>

                    {{-- Modal Body: 2-Column Side-by-Side Layout --}}
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-0 divide-y lg:divide-y-0 lg:divide-x divide-slate-100 dark:divide-white/[0.08]">
                        
                        {{-- Left Column: 7 cols - Proof Photo Viewer with Zoom/Rotate --}}
                        <div class="lg:col-span-7 p-4 sm:p-5 flex flex-col justify-between bg-slate-50/50 dark:bg-[#0E1018]">
                            {{-- Image Controls Toolbar --}}
                            <div class="flex items-center justify-between pb-3 gap-2 flex-wrap">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Proof Document Preview</span>
                                <div class="flex items-center gap-1.5 bg-white dark:bg-[#161824] p-1 rounded-xl border border-slate-200 dark:border-white/[0.08] shadow-2xs">
                                    <button type="button" @click="rotateImage()" title="Rotate 90 degrees" class="px-2 py-1 text-xs font-bold rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.06] transition flex items-center gap-1 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        <span>Rotate</span>
                                    </button>
                                    <button type="button" @click="zoomIn()" title="Zoom In" class="px-2 py-1 text-xs font-bold rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.06] transition cursor-pointer">+</button>
                                    <button type="button" @click="zoomOut()" title="Zoom Out" class="px-2 py-1 text-xs font-bold rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.06] transition cursor-pointer">-</button>
                                    <button type="button" @click="resetImg()" title="Reset Zoom & Rotation" class="px-2 py-1 text-[11px] font-bold rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white transition cursor-pointer">Reset</button>
                                    <template x-if="verifyProofPhotoUrl">
                                        <a :href="verifyProofPhotoUrl" target="_blank" title="Open full size" class="px-2 py-1 text-xs font-bold rounded-lg text-[#199CA4] hover:bg-[#199CA4]/10 transition flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    </template>
                                </div>
                            </div>

                            {{-- Image Canvas --}}
                            <div class="relative w-full h-[400px] sm:h-[460px] rounded-2xl bg-slate-900/90 dark:bg-black/90 flex items-center justify-center overflow-hidden border border-slate-200/60 dark:border-white/[0.08]">
                                <template x-if="verifyProofPhotoUrl">
                                    <img :src="verifyProofPhotoUrl" 
                                         alt="Proof Photo" 
                                         :style="'transform: rotate(' + imgRotation + 'deg) scale(' + imgZoom + '); transform-origin: center center; transition: transform 0.2s ease-out;'"
                                         class="max-w-full max-h-full object-contain pointer-events-none select-none">
                                </template>
                                <template x-if="!verifyProofPhotoUrl">
                                    <div class="text-slate-400 text-xs">No proof photo available for this submission.</div>
                                </template>
                            </div>

                            <p class="text-[11px] text-slate-400 dark:text-slate-500 text-center mt-2 font-medium">
                                Inspect clinic stamp, veterinarian signature, administered date, and vaccine lot/sticker.
                            </p>
                        </div>

                        {{-- Right Column: 5 cols - Verification Form & Due Date Calculator --}}
                        <div class="lg:col-span-5 p-5 sm:p-6 space-y-4 flex flex-col justify-between">
                            <div class="space-y-4">
                                {{-- Submission Summary Box --}}
                                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-[#161824] border border-slate-200/80 dark:border-white/[0.08] space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Treatment</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB] uppercase" x-text="verifyCategory"></span>
                                    </div>
                                    <p class="text-sm font-extrabold text-slate-900 dark:text-white" x-text="verifyVaccineName"></p>
                                    
                                    <div class="grid grid-cols-2 gap-2 text-xs pt-1 border-t border-slate-200/60 dark:border-white/[0.06]">
                                        <div>
                                            <span class="text-[10px] text-slate-400 block font-medium">Date Administered:</span>
                                            <span class="font-bold text-slate-800 dark:text-slate-200" x-text="verifyDate"></span>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-400 block font-medium">Private Clinic:</span>
                                            <span class="font-bold text-slate-800 dark:text-slate-200 truncate block" x-text="verifyClinicName"></span>
                                        </div>
                                    </div>

                                    <template x-if="verifyVeterinarian">
                                        <div class="text-xs pt-1 text-slate-500 dark:text-slate-400">
                                            <span>Attending Vet: </span>
                                            <span class="font-bold text-slate-700 dark:text-slate-300" x-text="verifyVeterinarian"></span>
                                        </div>
                                    </template>
                                </div>

                                {{-- Auto-Calculated Next Due Date Box --}}
                                <div class="p-4 rounded-2xl bg-[#199CA4]/[0.06] dark:bg-[#199CA4]/10 border border-[#199CA4]/25 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <label class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-white flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-[#199CA4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span>Next Due Date</span>
                                        </label>
                                        <span class="text-[10px] font-bold text-[#199CA4] dark:text-[#41C1CB]">CAWS Auto-Rule</span>
                                    </div>

                                    <input type="date" 
                                           x-model="verifyNextDueDate" 
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C] text-xs sm:text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#199CA4]/20 focus:border-[#199CA4] shadow-2xs">

                                    {{-- Preset Interval Quick Chips --}}
                                    <div>
                                        <span class="text-[10px] font-bold text-slate-400 block mb-1.5 uppercase tracking-wider">Quick Intervals:</span>
                                        <div class="grid grid-cols-4 gap-1.5">
                                            <button type="button" 
                                                    @click="setDueMonths(1)" 
                                                    class="px-2 py-1.5 rounded-lg text-[10px] font-extrabold border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#161824] hover:bg-slate-100 dark:hover:bg-white/[0.06] text-slate-700 dark:text-slate-200 transition cursor-pointer">
                                                +1M (Puppy)
                                            </button>
                                            <button type="button" 
                                                    @click="setDueMonths(3)" 
                                                    class="px-2 py-1.5 rounded-lg text-[10px] font-extrabold border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#161824] hover:bg-slate-100 dark:hover:bg-white/[0.06] text-slate-700 dark:text-slate-200 transition cursor-pointer">
                                                +3M (Deworm)
                                            </button>
                                            <button type="button" 
                                                    @click="setDueMonths(6)" 
                                                    class="px-2 py-1.5 rounded-lg text-[10px] font-extrabold border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#161824] hover:bg-slate-100 dark:hover:bg-white/[0.06] text-slate-700 dark:text-slate-200 transition cursor-pointer">
                                                +6M
                                            </button>
                                            <button type="button" 
                                                    @click="setDueMonths(12)" 
                                                    class="px-2 py-1.5 rounded-lg text-[10px] font-extrabold border border-[#199CA4]/40 bg-[#199CA4]/10 hover:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] transition cursor-pointer">
                                                +1Y (Annual)
                                            </button>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-tight">
                                        Calculated from date administered. If the vet card states a different return date, adjust the date input above before approving.
                                    </p>
                                </div>

                                {{-- Rejection Input Box (Collapsible) --}}
                                <div x-show="showRejectInput" x-transition class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50 space-y-2">
                                    <label class="block text-xs font-bold text-rose-800 dark:text-rose-300">
                                        Reason for Declining / Re-upload Request:
                                    </label>
                                    <textarea x-model="rejectionReason"
                                              rows="2"
                                              placeholder="e.g. Photo of vaccine sticker is blurry. Please re-upload a clear shot..."
                                              class="w-full text-xs rounded-xl border border-rose-200 dark:border-rose-800/60 bg-white dark:bg-[#12141C] text-slate-800 dark:text-slate-200 p-2.5 focus:ring-2 focus:ring-rose-400"></textarea>
                                </div>
                            </div>

                            {{-- Decision Buttons --}}
                            <div class="pt-4 border-t border-slate-100 dark:border-white/[0.08] space-y-2">
                                <template x-if="!showRejectInput">
                                    <div class="space-y-2">
                                        <form :action="verifyActionUrl" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="verified">
                                            <input type="hidden" name="next_due_date" :value="verifyNextDueDate">
                                            <button type="submit" 
                                                    class="w-full py-3 px-4 rounded-xl text-xs sm:text-sm font-extrabold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2 cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span>Approve & Activate Schedule</span>
                                            </button>
                                        </form>

                                        <button type="button" 
                                                @click="showRejectInput = true" 
                                                class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/60 border border-rose-200/80 dark:border-rose-900/50 transition cursor-pointer">
                                            Reject / Request Clearer Proof
                                        </button>
                                    </div>
                                </template>

                                <template x-if="showRejectInput">
                                    <div class="space-y-2">
                                        <form :action="verifyActionUrl" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="rejected">
                                            <input type="hidden" name="rejection_reason" :value="rejectionReason">
                                            <button type="submit" 
                                                    class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-md shadow-rose-600/20 transition flex items-center justify-center gap-2 cursor-pointer">
                                                <span>Confirm Rejection & Notify Adopter</span>
                                            </button>
                                        </form>

                                        <button type="button" 
                                                @click="showRejectInput = false" 
                                                class="w-full py-2 text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition cursor-pointer">
                                            Back to Approval
                                        </button>
                                    </div>
                                </template>
                            </div>

                        </div>

                    </div>

                </div>
            </div>
        </div>

</x-app-layout>