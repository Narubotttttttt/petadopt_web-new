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
             reviewPhotoUrl: '',

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
             imgPanX: 0,
             imgPanY: 0,
             isPanning: false,
             panStartX: 0,
             panStartY: 0,
             touchDistance: 0,

             init() {
                 @if(!empty($autoVerifyLogPayload))
                     this.$nextTick(() => {
                         this.openVerifyModal(@js($autoVerifyLogPayload));
                         try {
                             const url = new URL(window.location.href);
                             if (url.searchParams.has('verify_log_id')) {
                                 url.searchParams.delete('verify_log_id');
                                 window.history.replaceState({}, document.title, url.toString());
                             }
                         } catch (e) {
                             // Ignore url cleanup if unsupported
                         }
                     });
                 @endif
             },

             openReviewModal(id, petName, adopterName, date, notes, decision, remarks, photoUrl) {
                 this.reviewReportId = id;
                 this.reviewPetName = petName || 'Companion';
                 this.reviewAdopterName = adopterName || 'Adopter';
                 this.reviewCheckInDate = date || '';
                 this.reviewAdopterNotes = notes || '';
                 this.reviewCurrentDecision = decision || 'cleared_accidental';
                 this.reviewCurrentRemarks = remarks || '';
                 this.reviewPhotoUrl = photoUrl || '';
                 this.showReviewModal = true;
             },

             cleanReason(notes) {
                 if (!notes) return '';
                 const m = notes.match(/Reason & Circumstances:\s*([\s\S]+)/i);
                 if (m) return m[1].trim();
                 let clean = notes.replace(/\[PET LIFE EVENT:[^\]]*\]/gi, '');
                 clean = clean.replace(/Date of Passing:[^\r\n]*/gi, '');
                 clean = clean.replace(/Cause Category:[^\r\n]*/gi, '');
                 return clean.trim() || notes;
             },

             extractCause(notes) {
                 if (!notes) return '';
                 const m = notes.match(/Cause Category:\s*([^\r\n]+)/i);
                 return m ? m[1].trim() : '';
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
                 if (typeof log === 'string') {
                     try { log = JSON.parse(log); } catch(e) { return; }
                 }
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
                 this.imgPanX = 0;
                 this.imgPanY = 0;
                 this.isPanning = false;
                 this.touchDistance = 0;

                 if (log.next_due_date) {
                     this.verifyNextDueDate = typeof log.next_due_date === 'string' ? log.next_due_date.split('T')[0] : log.next_due_date;
                 } else if (log.category === 'deworming') {
                     this.verifyNextDueDate = this.calcDueDateMonths(3, this.verifyDate);
                 } else {
                     this.verifyNextDueDate = this.calcDueDateMonths(12, this.verifyDate);
                 }

                 this.showVerifyModal = true;
             },

             openVerifyFromEl(el) {
                 if (!el) return;
                 const raw = el.getAttribute('data-log') || el.closest('[data-log]')?.getAttribute('data-log');
                 if (!raw) return;
                 try {
                     const log = typeof raw === 'string' ? JSON.parse(raw) : raw;
                     this.openVerifyModal(log);
                 } catch (e) {
                     console.error('Failed to parse log payload', e);
                 }
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
                 this.imgZoom = Math.min(Math.round((this.imgZoom + 0.25) * 100) / 100, 5);
             },

             zoomOut() {
                 const next = Math.max(Math.round((this.imgZoom - 0.25) * 100) / 100, 0.5);
                 this.imgZoom = next;
                 if (this.imgZoom <= 1) {
                     this.imgPanX = 0;
                     this.imgPanY = 0;
                 }
             },

             resetImg() {
                 this.imgRotation = 0;
                 this.imgZoom = 1;
                 this.imgPanX = 0;
                 this.imgPanY = 0;
                 this.isPanning = false;
                 this.touchDistance = 0;
             },

             toggleZoom() {
                 if (this.imgZoom > 1) {
                     this.resetImg();
                 } else {
                     this.imgZoom = 2;
                 }
             },

             handleWheel(e) {
                 if (e.cancelable) {
                     e.preventDefault();
                 }
                 let zoomDelta = 0;
                 if (e.ctrlKey) {
                     // Laptop trackpad pinch gesture
                     zoomDelta = -e.deltaY * 0.015;
                 } else {
                     // Mouse scroll wheel
                     const factor = Math.abs(e.deltaY) > 50 ? 0.2 : 0.08;
                     zoomDelta = (e.deltaY < 0 ? 1 : -1) * factor;
                 }
                 const nextZoom = Math.min(Math.max(this.imgZoom + zoomDelta, 0.5), 5);
                 this.imgZoom = Math.round(nextZoom * 100) / 100;
                 if (this.imgZoom <= 1) {
                     this.imgPanX = 0;
                     this.imgPanY = 0;
                 }
             },

             startPan(e) {
                 if (this.imgZoom <= 1 && e.type === 'mousedown') return;
                 this.isPanning = true;
                 const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                 const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                 this.panStartX = clientX - this.imgPanX;
                 this.panStartY = clientY - this.imgPanY;
             },

             doPan(e) {
                 if (!this.isPanning) return;
                 const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                 const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                 this.imgPanX = clientX - this.panStartX;
                 this.imgPanY = clientY - this.panStartY;
             },

             endPan() {
                 this.isPanning = false;
             },

             startTouch(e) {
                 if (e.touches && e.touches.length === 2) {
                     this.isPanning = false;
                     this.touchDistance = Math.hypot(
                         e.touches[0].clientX - e.touches[1].clientX,
                         e.touches[0].clientY - e.touches[1].clientY
                     );
                 } else if (e.touches && e.touches.length === 1) {
                     this.startPan(e);
                 }
             },

             doTouch(e) {
                 if (e.touches && e.touches.length === 2) {
                     const dist = Math.hypot(
                         e.touches[0].clientX - e.touches[1].clientX,
                         e.touches[0].clientY - e.touches[1].clientY
                     );
                     if (this.touchDistance > 0) {
                         const factor = dist / this.touchDistance;
                         const nextZoom = Math.min(Math.max(this.imgZoom * factor, 0.5), 5);
                         this.imgZoom = Math.round(nextZoom * 100) / 100;
                         this.touchDistance = dist;
                         if (this.imgZoom <= 1) {
                             this.imgPanX = 0;
                             this.imgPanY = 0;
                         }
                     }
                 } else if (e.touches && e.touches.length === 1) {
                     this.doPan(e);
                 }
             },

             endTouch(e) {
                 if (!e.touches || e.touches.length === 0) {
                     this.isPanning = false;
                     this.touchDistance = 0;
                     if (this.imgZoom <= 1) {
                         this.imgPanX = 0;
                         this.imgPanY = 0;
                     }
                 }
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
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ ($tab ?? 'monthly_updates') === 'vet_proofs' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-white/[0.08] text-slate-700 dark:text-slate-300' }}">{{ $totalVetLogsCount }}</span>
                </a>

                <a href="{{ route('adopters.reports', ['tab' => 'pet_dedications']) }}" 
                   class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold transition {{ ($tab ?? 'monthly_updates') === 'pet_dedications' ? 'bg-[#199CA4] text-white shadow-2xs' : 'bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08]' }}">
                    <svg class="w-3.5 h-3.5 {{ ($tab ?? 'monthly_updates') === 'pet_dedications' ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    <span>Pet Dedications</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ ($tab ?? 'monthly_updates') === 'pet_dedications' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-white/[0.08] text-slate-700 dark:text-slate-300' }}">{{ $totalDedicationsCount ?? 0 }}</span>
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
        </div>
    </div>

    {{-- Search & Filter Toolbar for Vet Proofs --}}
    <div class="bg-white dark:bg-[#12141C] p-3 sm:p-4 rounded-2xl sm:rounded-3xl shadow-xs border border-slate-200/80 dark:border-white/[0.07]">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            {{-- Quick Filter Pills --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                <a href="{{ route('adopters.reports', array_merge(request()->except('vet_status', 'page', 'vet_page'), ['tab' => 'vet_proofs', 'vet_status' => 'all'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ ($vetStatus ?? 'all') === 'all' ? 'bg-[#199CA4] text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/[0.1]' }}">
                    All ({{ $totalVetLogsCount }})
                </a>
                <a href="{{ route('adopters.reports', array_merge(request()->except('vet_status', 'page', 'vet_page'), ['tab' => 'vet_proofs', 'vet_status' => 'pending'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ ($vetStatus ?? '') === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40' }}">
                    @if($pendingVetLogsCount > 0)
                        <span class="w-2 h-2 rounded-full {{ ($vetStatus ?? '') === 'pending' ? 'bg-white' : 'bg-amber-500 animate-pulse' }}"></span>
                    @endif
                    <span>Pending ({{ $pendingVetLogsCount }})</span>
                </a>
                <a href="{{ route('adopters.reports', array_merge(request()->except('vet_status', 'page', 'vet_page'), ['tab' => 'vet_proofs', 'vet_status' => 'verified'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ ($vetStatus ?? '') === 'verified' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40' }}">
                    Verified ({{ $verifiedVetLogsCount }})
                </a>
                <a href="{{ route('adopters.reports', array_merge(request()->except('vet_status', 'page', 'vet_page'), ['tab' => 'vet_proofs', 'vet_status' => 'rejected'])) }}"
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

    {{-- External Vet Proofs Grid (Compact dashboard grid) --}}
    @php
        $groupedVetLogs = $vetLogs->getCollection()->groupBy(function($log) {
            return $log->created_by ? ('user_' . $log->created_by) : ('log_' . $log->id);
        });
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5 sm:gap-4">
        @forelse($groupedVetLogs as $groupKey => $group)
            @php
                $firstLog = $group->first();
                $adopter = $firstLog->creator?->adoptersProfile;
                $adopterName = $adopter?->full_name ?: ($firstLog->creator?->name ?: 'Adopter');
                $adopterAvatar = $adopter?->avatar_url;
                $adopterInitials = $adopter?->initials ?: strtoupper(substr($adopterName, 0, 2));
                $adopterCode = $adopter?->adopter_code ?: ($firstLog->creator ? sprintf('ADP-%04d', $firstLog->creator->id) : null);
                $adopterContact = $adopter?->phone ?: ($firstLog->creator?->email ?: null);
                $totalPetsInGroup = $group->count();
            @endphp

            <div class="bg-white dark:bg-[#12141C] rounded-xl sm:rounded-2xl border border-slate-200/80 dark:border-white/[0.07] shadow-2xs overflow-hidden hover:border-[#199CA4]/40 transition-all duration-200 flex flex-col"
                 x-data="{ activeIndex: 0 }">
                
                {{-- Adopter Top Header (Full Width) --}}
                <div class="p-3 sm:p-3.5 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between gap-2.5 bg-slate-50/60 dark:bg-white/[0.02]">
                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                        {{-- Adopter Avatar / Initials --}}
                        <div class="w-8 h-8 rounded-lg overflow-hidden shrink-0 border border-slate-200 dark:border-white/[0.08] bg-slate-100 dark:bg-[#12141C] relative">
                            @if($adopterAvatar)
                                <img src="{{ $adopterAvatar }}" alt="{{ $adopterName }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-[#199CA4] to-[#13787F] text-white flex items-center justify-center font-black text-[11px]">
                                    {{ $adopterInitials }}
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white leading-tight" title="Adopter: {{ $adopterName }}">
                                @if($adopterCode)
                                    <a href="{{ route('adopters.index', ['search' => $adopterCode]) }}" class="hover:text-[#199CA4] hover:underline transition-colors">{{ $adopterName }}</a>
                                @else
                                    {{ $adopterName }}
                                @endif
                            </h3>
                            <div class="flex items-center gap-1.5 mt-0.5 min-w-0">
                                @if($adopterCode)
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-slate-100 dark:bg-white/[0.06] text-slate-500 dark:text-slate-400 border border-slate-200/60 dark:border-white/[0.08] shrink-0 font-mono">{{ $adopterCode }}</span>
                                @endif
                                <span class="text-[10px] text-slate-400 truncate leading-tight font-medium">{{ $adopterContact ?: 'Verified Adopter' }}</span>
                            </div>
                        </div>
                    </div>

                    @if($totalPetsInGroup > 1)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/25 shrink-0" title="{{ $totalPetsInGroup }} pets in this household">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>{{ $totalPetsInGroup }} Pets</span>
                        </span>
                    @endif
                </div>

                {{-- Multi-Pet Switcher Tabs (Only shown if adopter has > 1 pet submission) --}}
                @if($totalPetsInGroup > 1)
                    <div class="px-3 py-1.5 bg-slate-100/70 dark:bg-white/[0.03] border-b border-slate-100 dark:border-white/[0.06] flex items-center gap-1.5 overflow-x-auto scrollbar-none">
                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 shrink-0 mr-0.5">Pet:</span>
                        @foreach($group->values() as $idx => $logItem)
                            @php
                                $tabPet = $logItem->pet;
                                $tabPetName = $tabPet && $tabPet->name ? $tabPet->name : ('Pet #' . $logItem->pet_id);
                                $tabPetType = ucfirst($tabPet?->type ?? 'Pet');
                                $tabIsPending = ($logItem->verification_status === 'pending');
                            @endphp
                            <button type="button"
                                    @click="activeIndex = {{ $idx }}"
                                    :class="activeIndex === {{ $idx }} ? 'bg-[#199CA4] text-white shadow-xs font-bold' : 'bg-white dark:bg-[#12141C] text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200/80 dark:border-white/[0.08] font-medium'"
                                    class="px-2.5 py-1 rounded-lg text-[10px] transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                                <span>{{ $tabPetName }}</span>
                                <span class="text-[9px] opacity-75">({{ $tabPetType }})</span>
                                @if($tabIsPending)
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse" title="Verification Pending"></span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endif

                {{-- Pet Update Details (One per pet in the group) --}}
                @foreach($group->values() as $idx => $log)
                    @php
                        $pet = $log->pet;
                        $petName = $pet && $pet->name ? $pet->name : ('Pet #' . $log->pet_id);
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

                    <div x-show="activeIndex === {{ $idx }}"
                         @if($idx > 0) x-cloak style="display: none;" @endif
                         class="flex-1 flex flex-col sm:flex-row min-w-0"
                         data-log="{{ json_encode($logPayload) }}">
                        {{-- Left: Proof Thumbnail Container (Compact 110-128px on sm+) --}}
                        <div class="relative w-full sm:w-28 md:w-32 shrink-0 h-36 sm:h-auto bg-slate-950 overflow-hidden group cursor-pointer flex items-center justify-center"
                             @click="openVerifyFromEl($el)"
                             title="Click to inspect proof photo">
                            @if($photoUrl)
                                <img src="{{ $photoUrl }}" alt="Vet Card Proof" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <div class="absolute inset-0 bg-black/30 group-hover:bg-black/50 transition-colors flex flex-col items-center justify-center gap-1">
                                    <div class="w-7 h-7 rounded-full bg-black/60 backdrop-blur-xs flex items-center justify-center text-white shadow-2xs group-hover:scale-110 transition-transform">
                                        <svg class="w-3.5 h-3.5 text-[#41C1CB]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                                    </div>
                                    <span class="text-[9px] font-bold text-white bg-black/70 backdrop-blur-xs px-2 py-0.5 rounded-full shadow-2xs">
                                        Inspect
                                    </span>
                                </div>
                            @else
                                <div class="w-full h-full min-h-[110px] flex flex-col items-center justify-center text-slate-500 text-xs p-2 text-center">
                                    <svg class="w-5 h-5 mb-1 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span class="text-[10px]">No Photo</span>
                                </div>
                            @endif
                            <div class="absolute bottom-1.5 left-1.5 px-1.5 py-0.5 rounded bg-black/80 backdrop-blur-xs text-white text-[8px] font-black uppercase tracking-wider">
                                {{ ucfirst(str_replace('_', ' ', $log->category)) }}
                            </div>
                        </div>

                        {{-- Right: Content & Information --}}
                        <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between gap-2 min-w-0">
                            <div>
                                {{-- Top Row: Category / Clinic & Status Pill --}}
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 shrink-0">
                                            {{ $log->vaccine_name ?: ($log->category === 'vaccination' ? 'Vaccine' : 'Deworming') }}
                                        </span>
                                        <span class="text-[11px] font-semibold text-slate-600 dark:text-slate-300 truncate" title="{{ $log->clinic_name ?: 'Private Clinic' }}">
                                            {{ $log->clinic_name ?: 'Private Clinic' }}
                                        </span>
                                    </div>

                                    {{-- Status Pill --}}
                                    @if($isPending)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/25 shrink-0">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Pending
                                        </span>
                                    @elseif($isVerified)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/25 shrink-0">
                                            Verified
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/25 shrink-0">
                                            Rejected
                                        </span>
                                    @endif
                                </div>

                                {{-- Center: Adopted Pet Details --}}
                                <div class="my-2 p-2 rounded-xl bg-slate-50 dark:bg-white/[0.04] border border-slate-100 dark:border-white/[0.06] flex items-center gap-2.5 min-w-0">
                                    @if($pet?->photo_url || $pet?->photo_path)
                                        <img src="{{ $pet->photo_url ?: asset('storage/'.ltrim($pet->photo_path, '/')) }}" alt="{{ $petName }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shrink-0 shadow-2xs">
                                    @else
                                        <div class="w-8 h-8 rounded-lg bg-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-black text-[11px] shrink-0">
                                            #{{ $log->pet_id }}
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <span class="text-[9px] uppercase font-bold text-slate-400 dark:text-slate-500 tracking-wider shrink-0">Adopted Pet:</span>
                                            <strong class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-100 truncate">{{ $petName }}</strong>
                                        </div>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate leading-tight mt-0.5">
                                            {{ ucfirst($pet?->type ?? 'Pet') }} &bull; {{ $pet?->breed ?? 'Mixed Breed' }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Clinical Details --}}
                                <div class="flex items-center justify-between text-[10px] text-slate-500 dark:text-slate-400">
                                    <span class="truncate">Given: <strong class="text-slate-700 dark:text-slate-300 font-medium">{{ $log->date?->format('M d, Y') }}</strong></span>
                                    @if($log->administered_by)
                                        <span class="truncate text-[9px] text-slate-400 dark:text-slate-500">by {{ $log->administered_by }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Action Row & Next Due Date --}}
                            <div class="pt-2 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between gap-2">
                                <div class="min-w-0 text-[10px]">
                                    <span class="text-slate-400 block text-[9px] leading-tight">Next Booster:</span>
                                    @if($log->next_due_date)
                                        <span class="font-bold text-[#199CA4] dark:text-[#41C1CB] truncate block leading-tight">{{ $log->next_due_date->format('M d, Y') }}</span>
                                    @else
                                        <span class="text-amber-600 dark:text-amber-400 font-semibold text-[10px] truncate block leading-tight">Pending review</span>
                                    @endif
                                </div>

                                <button type="button" 
                                        @click="openVerifyFromEl($el)"
                                        class="h-7 px-3 rounded-lg text-xs font-bold transition shadow-2xs cursor-pointer flex items-center justify-center gap-1 shrink-0 {{ $isPending ? 'bg-[#199CA4] hover:bg-[#13787F] text-white shadow-[#199CA4]/20' : 'bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.1] text-slate-700 dark:text-slate-200' }}">
                                    @if($isPending)
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Inspect & Verify</span>
                                    @else
                                        <span>Details</span>
                                    @endif
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
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

@elseif(($tab ?? 'monthly_updates') === 'pet_dedications')
    {{-- ==================== PET DEDICATIONS & MEMORIALS TAB ==================== --}}

    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Pet Dedications & Memorials</h1>
        </div>
        
        {{-- Header Actions --}}
        <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
            <a href="{{ route('adopters.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-bold hover:bg-slate-50 dark:hover:bg-[#171923] hover:text-[#199CA4] dark:hover:text-[#41C1CB] transition shadow-xs">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Adopter Profiles</span>
            </a>
        </div>
    </div>

    {{-- Search & Filter Toolbar for Pet Dedications --}}
    <div class="bg-white dark:bg-[#12141C] p-3 sm:p-4 rounded-2xl sm:rounded-3xl shadow-xs border border-slate-200/80 dark:border-white/[0.07]">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            {{-- Quick Filter Pills --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                <a href="{{ route('adopters.reports', array_merge(request()->except('dedication_filter', 'page', 'dedication_page'), ['tab' => 'pet_dedications', 'dedication_filter' => 'all'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ ($dedicationFilter ?? 'all') === 'all' ? 'bg-[#199CA4] text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/[0.1]' }}">
                    All Dedications ({{ $totalDedicationsCount }})
                </a>
                <a href="{{ route('adopters.reports', array_merge(request()->except('dedication_filter', 'page', 'dedication_page'), ['tab' => 'pet_dedications', 'dedication_filter' => 'pending'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ ($dedicationFilter ?? '') === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40' }}">
                    @if(($pendingDeceasedCount ?? 0) > 0)
                        <span class="w-2 h-2 rounded-full {{ ($dedicationFilter ?? '') === 'pending' ? 'bg-white' : 'bg-amber-500 animate-pulse' }}"></span>
                    @endif
                    <span>Awaiting Review ({{ $pendingDeceasedCount }})</span>
                </a>
                <a href="{{ route('adopters.reports', array_merge(request()->except('dedication_filter', 'page', 'dedication_page'), ['tab' => 'pet_dedications', 'dedication_filter' => 'cleared'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ ($dedicationFilter ?? '') === 'cleared' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40' }}">
                    <span>Natural / Accidental ({{ $clearedDeceasedCount }})</span>
                </a>
                <a href="{{ route('adopters.reports', array_merge(request()->except('dedication_filter', 'page', 'dedication_page'), ['tab' => 'pet_dedications', 'dedication_filter' => 'neglect'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ ($dedicationFilter ?? '') === 'neglect' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40' }}">
                    <span>Neglect Confirmed ({{ $neglectDeceasedCount }})</span>
                </a>
            </div>

            {{-- Search Input --}}
            <form method="GET" action="{{ route('adopters.reports') }}" class="flex items-center gap-2 w-full md:w-auto">
                <input type="hidden" name="tab" value="pet_dedications">
                @if(($dedicationFilter ?? 'all') !== 'all')
                    <input type="hidden" name="dedication_filter" value="{{ $dedicationFilter }}">
                @endif
                <div class="relative flex-1 md:w-80">
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}"
                           placeholder="Search pet, adopter, breed, notes..." 
                           class="w-full pl-9 {{ !empty($search) ? 'pr-8' : 'pr-3' }} py-2 text-xs sm:text-sm bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-slate-800 dark:text-slate-100 rounded-xl focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition placeholder-slate-400 dark:placeholder-slate-500 font-medium">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    @if(!empty($search))
                        <a href="{{ route('adopters.reports', ['tab' => 'pet_dedications', 'dedication_filter' => $dedicationFilter]) }}" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs font-bold" title="Clear search">
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

    {{-- Dedications Grid (Compact dashboard grid matching Monthly Updates & Vaccination updates) --}}
    @php
        $groupedDedications = $dedications->getCollection()->groupBy(function($dedication) {
            return $dedication->user_id ? ('user_' . $dedication->user_id) : ('dedication_' . $dedication->id);
        });
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5 sm:gap-4">
        @forelse($groupedDedications as $groupKey => $group)
            @php
                $firstDedication = $group->first();
                $adopter = $firstDedication->user?->adoptersProfile;
                $adopterName = $adopter?->full_name ?: ($firstDedication->user?->name ?: ($firstDedication->application?->applicant_name ?: 'Adopter'));
                $adopterAvatar = $adopter?->avatar_url;
                $adopterInitials = $adopter?->initials ?: strtoupper(substr($adopterName, 0, 2));
                $adopterCode = $adopter?->adopter_code ?: ($firstDedication->user ? sprintf('ADP-%04d', $firstDedication->user->id) : null);
                $adopterContact = $adopter?->phone ?: ($firstDedication->user?->email ?: null);
                $totalPetsInGroup = $group->count();
            @endphp

            <div class="bg-white dark:bg-[#12141C] rounded-xl sm:rounded-2xl border border-slate-200/80 dark:border-white/[0.07] shadow-2xs overflow-hidden hover:border-[#199CA4]/40 transition-all duration-200 flex flex-col"
                 x-data="{ activeIndex: 0 }">
                
                {{-- Adopter Top Header (Full Width) --}}
                <div class="p-3 sm:p-3.5 border-b border-slate-100 dark:border-white/[0.06] bg-slate-50/60 dark:bg-white/[0.02] flex flex-col gap-2.5">
                    {{-- Top Row: Adopter Info (Left) & Determination Status / Multi-Pet Count (Right) --}}
                    <div class="flex items-center justify-between gap-3 min-w-0">
                        {{-- Adopter Identity --}}
                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                            <div class="w-8 h-8 rounded-lg overflow-hidden shrink-0 border border-slate-200 dark:border-white/[0.08] bg-slate-100 dark:bg-[#12141C] relative">
                                @if($adopterAvatar)
                                    <img src="{{ $adopterAvatar }}" alt="{{ $adopterName }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-[#199CA4] to-[#13787F] text-white flex items-center justify-center font-black text-[11px]">
                                        {{ $adopterInitials }}
                                    </div>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <h3 class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white leading-tight truncate" title="Adopter: {{ $adopterName }}">
                                    @if($adopterCode)
                                        <a href="{{ route('adopters.index', ['search' => $adopterCode]) }}" class="hover:text-[#199CA4] hover:underline transition-colors">{{ $adopterName }}</a>
                                    @else
                                        {{ $adopterName }}
                                    @endif
                                </h3>
                                <div class="flex items-center gap-1.5 mt-0.5 min-w-0">
                                    @if($adopterCode)
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-slate-100 dark:bg-white/[0.06] text-slate-500 dark:text-slate-400 border border-slate-200/60 dark:border-white/[0.08] shrink-0 font-mono">{{ $adopterCode }}</span>
                                    @endif
                                    <span class="text-[10px] text-slate-400 truncate leading-tight font-medium">{{ $adopterContact ?: 'Verified Adopter' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Top Right: Determination Status Pill (On Top) & Multi-Pet Count (Below) --}}
                        <div class="flex flex-col items-end justify-center gap-1 shrink-0">
                            @foreach($group->values() as $idx => $dedicationHeader)
                                @php
                                    $hIsPending = empty($dedicationHeader->review_decision) || $dedicationHeader->review_decision === 'pending';
                                    $hIsCleared = ($dedicationHeader->review_decision === 'cleared_accidental');
                                    $hIsNeglect = ($dedicationHeader->review_decision === 'neglect_confirmed');
                                @endphp
                                <div x-show="activeIndex === {{ $idx }}"
                                     @if($idx > 0) x-cloak style="display: none;" @endif>
                                    @if($hIsPending)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/25 shrink-0">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Pending Review
                                        </span>
                                    @elseif($hIsCleared)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/25 shrink-0">
                                            Natural / Accidental
                                        </span>
                                    @elseif($hIsNeglect)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/25 shrink-0">
                                            Neglect Confirmed
                                        </span>
                                    @endif
                                </div>
                            @endforeach

                            @if($totalPetsInGroup > 1)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/25 shrink-0" title="{{ $totalPetsInGroup }} deceased companions reported">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    <span>{{ $totalPetsInGroup }} Pets</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Bottom Row: Detail of the Pet Below Adopter Details --}}
                    <div class="pt-2 border-t border-slate-100 dark:border-white/[0.04] flex items-center gap-2 min-w-0">
                        @foreach($group->values() as $idx => $dedicationHeader)
                            @php
                                $hPet = $dedicationHeader->pet;
                                $hPetName = $hPet && $hPet->name ? $hPet->name : ('Pet no. ' . $dedicationHeader->pet_id);
                                $hPetType = ucfirst($hPet?->type ?? 'Pet');
                                $hBreedClean = preg_replace('/\s*\(.*?\)/', '', $hPet?->breed ?? 'Mixed Breed');
                            @endphp
                            <div x-show="activeIndex === {{ $idx }}"
                                 @if($idx > 0) x-cloak style="display: none;" @endif
                                 class="flex items-center gap-2 min-w-0 flex-wrap flex-1">
                                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100/90 dark:bg-white/[0.05] border border-slate-200/70 dark:border-white/[0.08] shrink-0">
                                    @if($hPet?->photo_url || $hPet?->photo_path)
                                        <img src="{{ $hPet->photo_url ?: asset('storage/'.ltrim($hPet->photo_path, '/')) }}" alt="{{ $hPetName }}" class="w-5 h-5 rounded-md object-cover shrink-0">
                                    @else
                                        <div class="w-5 h-5 rounded-md bg-[#199CA4]/15 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-bold text-[9px] shrink-0">
                                            #{{ $dedicationHeader->pet_id }}
                                        </div>
                                    @endif
                                    <strong class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">{{ $hPetName }}</strong>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 truncate">({{ $hPetType }} &bull; {{ $hBreedClean }})</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Multi-Pet Switcher Tabs (Only shown if adopter has > 1 pet submission) --}}
                @if($totalPetsInGroup > 1)
                    <div class="px-3 py-1.5 bg-slate-100/70 dark:bg-white/[0.03] border-b border-slate-100 dark:border-white/[0.06] flex items-center gap-1.5 overflow-x-auto scrollbar-none">
                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 shrink-0 mr-0.5">Pet:</span>
                        @foreach($group->values() as $idx => $dedicationItem)
                            @php
                                $tabPet = $dedicationItem->pet;
                                $tabPetName = $tabPet && $tabPet->name ? $tabPet->name : ('Pet no. ' . $dedicationItem->pet_id);
                                $tabPetType = ucfirst($tabPet?->type ?? 'Pet');
                                $tabIsPending = empty($dedicationItem->review_decision) || $dedicationItem->review_decision === 'pending';
                            @endphp
                            <button type="button"
                                    @click="activeIndex = {{ $idx }}"
                                    :class="activeIndex === {{ $idx }} ? 'bg-[#199CA4] text-white shadow-xs font-bold' : 'bg-white dark:bg-[#12141C] text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200/80 dark:border-white/[0.08] font-medium'"
                                    class="px-2.5 py-1 rounded-lg text-[10px] transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                                <span>{{ $tabPetName }}</span>
                                <span class="text-[9px] opacity-75">({{ $tabPetType }})</span>
                                @if($tabIsPending)
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse" title="Shelter Determination Pending"></span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endif

                {{-- Pet Dedication Details (One per pet in the group) --}}
                @foreach($group->values() as $idx => $dedication)
                    @php
                        $dPet = $dedication->pet;
                        $dPetName = $dPet && $dPet->name ? $dPet->name : ('Pet no. ' . $dedication->pet_id);
                        $dPhotoUrl = $dedication->photo_url ?: ($dPet?->photo_url ?: ($dPet?->photo_path ? asset('storage/'.ltrim($dPet->photo_path, '/')) : null));
                        $dDateStr = $dedication->check_in_date ? $dedication->check_in_date->format('M d, Y') : $dedication->created_at->format('M d, Y');
                        $isPending = empty($dedication->review_decision) || $dedication->review_decision === 'pending';
                        $isCleared = ($dedication->review_decision === 'cleared_accidental');
                        $isNeglect = ($dedication->review_decision === 'neglect_confirmed');

                        // Parse structured notes if submitted via Life Event form
                        $rawNotes = $dedication->notes ?? '';
                        $causeCategory = null;
                        $passingDate = null;
                        $reasonText = $rawNotes;

                        if (preg_match('/Cause Category:\s*([^\r\n]+)/i', $rawNotes, $m)) {
                            $causeCategory = trim($m[1]);
                        }
                        if (preg_match('/Date of Passing:\s*([^\r\n]+)/i', $rawNotes, $m)) {
                            $pDateRaw = trim($m[1]);
                            try {
                                $passingDate = \Carbon\Carbon::parse($pDateRaw)->format('M d, Y');
                            } catch (\Throwable $e) {
                                $passingDate = $pDateRaw;
                            }
                        }
                        if (preg_match('/Reason & Circumstances:\s*([\s\S]+)/i', $rawNotes, $m)) {
                            $reasonText = trim($m[1]);
                        } else {
                            $clean = preg_replace('/\[PET LIFE EVENT:[^\]]*\]/i', '', $rawNotes);
                            $clean = preg_replace('/Date of Passing:[^\r\n]*/i', '', $clean);
                            $clean = preg_replace('/Cause Category:[^\r\n]*/i', '', $clean);
                            $clean = trim($clean);
                            if (!empty($clean)) {
                                $reasonText = $clean;
                            }
                        }
                    @endphp

                    <div x-show="activeIndex === {{ $idx }}"
                         @if($idx > 0) x-cloak style="display: none;" @endif
                         class="flex-1 flex flex-col sm:flex-row min-w-0">

                        {{-- Left: Memorial Photo Thumbnail Container (Matching Monthly Updates: w-full sm:w-28 md:w-32 shrink-0 h-36 sm:h-auto) --}}
                        <div class="relative w-full sm:w-28 md:w-32 shrink-0 h-36 sm:h-auto bg-slate-950 overflow-hidden group cursor-pointer flex items-center justify-center"
                             @if($dPhotoUrl)
                             data-photo="{{ $dPhotoUrl }}"
                             data-title="In Memory of {{ $dPetName }}"
                             data-date="{{ $dDateStr }}"
                             data-weight="Memorial"
                             data-status="deceased"
                             data-notes="{{ $dedication->notes }}"
                             @click="openFromCard($event)"
                             @endif
                             title="Click to inspect memorial photo">
                            @if($dPhotoUrl)
                                <img src="{{ $dPhotoUrl }}" alt="In Memory of {{ $dPetName }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <div class="absolute inset-0 bg-black/30 group-hover:bg-black/50 transition-colors flex flex-col items-center justify-center gap-1">
                                    <div class="w-7 h-7 rounded-full bg-black/60 backdrop-blur-xs flex items-center justify-center text-white shadow-2xs group-hover:scale-110 transition-transform">
                                        <svg class="w-3.5 h-3.5 text-[#41C1CB]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                                    </div>
                                    <span class="text-[9px] font-bold text-white bg-black/70 backdrop-blur-xs px-2 py-0.5 rounded-full shadow-2xs">
                                        Inspect
                                    </span>
                                </div>
                            @else
                                <div class="w-full h-full min-h-[110px] flex flex-col items-center justify-center text-slate-500 text-xs p-2 text-center bg-slate-900">
                                    <svg class="w-5 h-5 mb-1 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                    <span class="text-[10px] font-bold text-slate-400">Memorial</span>
                                </div>
                            @endif
                            <div class="absolute bottom-1.5 left-1.5 px-1.5 py-0.5 rounded bg-black/80 backdrop-blur-xs text-white text-[8px] font-black uppercase tracking-wider flex items-center gap-1">
                                <svg class="w-2.5 h-2.5 text-rose-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                <span>Memorial</span>
                            </div>
                        </div>

                        {{-- Right: Content & Information --}}
                        <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between gap-2.5 min-w-0">
                            {{-- Top Metadata Row: Dates, Cause & Logged Time --}}
                            <div class="flex items-center justify-between text-[10px] text-slate-400 pb-1.5 border-b border-slate-100 dark:border-white/[0.06] gap-2 flex-wrap">
                                <div class="flex items-center gap-2 truncate">
                                    <span>Reported: <strong class="text-slate-700 dark:text-slate-300 font-semibold">{{ $dDateStr }}</strong></span>
                                    @if($causeCategory)
                                        <span class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-wider bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-500/20 shrink-0" title="Cause of Passing: {{ $causeCategory }}">
                                            {{ $causeCategory }}
                                        </span>
                                    @endif
                                    @if($passingDate && $passingDate !== $dDateStr)
                                        <span>&bull;</span>
                                        <span>Passed: <strong class="text-slate-700 dark:text-slate-300 font-semibold">{{ $passingDate }}</strong></span>
                                    @endif
                                </div>
                                <span class="text-[9px] text-slate-400 font-medium shrink-0">Logged {{ $dedication->created_at->diffForHumans() }}</span>
                            </div>

                            {{-- Center: Expanded Reason Only of Why Pet Died --}}
                            <div class="flex-1 flex flex-col justify-center min-w-0 my-0.5">
                                <div class="p-3.5 sm:p-4 rounded-xl bg-slate-50/80 dark:bg-white/[0.03] border border-slate-100 dark:border-white/[0.06] flex-1 flex flex-col justify-between gap-2.5 shadow-2xs">
                                    <div>
                                        <div class="flex items-center gap-1.5 text-[9px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">
                                            <svg class="w-3.5 h-3.5 text-[#199CA4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                            <span>Reason for Pet's Passing</span>
                                        </div>
                                        @if(!empty($reasonText))
                                            <p class="text-xs sm:text-[13px] md:text-sm text-slate-800 dark:text-slate-100 font-medium leading-relaxed italic line-clamp-6" title="{{ $reasonText }}">
                                                "{{ $reasonText }}"
                                            </p>
                                        @else
                                            <p class="text-xs text-slate-400 dark:text-slate-500 italic">No explanation recorded.</p>
                                        @endif
                                    </div>

                                    @if(!empty($dedication->staff_remarks))
                                        <div class="mt-2 pt-2 border-t border-slate-200/60 dark:border-white/[0.06] flex items-center gap-2 text-[10px]">
                                            <span class="text-[8px] font-black uppercase tracking-wider text-slate-400 shrink-0">Staff Remark:</span>
                                            <span class="font-semibold text-slate-600 dark:text-slate-300 truncate">{{ $dedication->staff_remarks }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Action Row & Footer --}}
                            <div class="pt-2.5 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-end gap-1.5">
                                @if($dPhotoUrl)
                                    <button type="button" 
                                            data-photo="{{ $dPhotoUrl }}"
                                            data-title="In Memory of {{ $dPetName }}"
                                            data-date="{{ $dDateStr }}"
                                            data-weight="Memorial"
                                            data-status="deceased"
                                            data-notes="{{ $dedication->notes }}"
                                            @click="openFromCard($event)"
                                            class="h-7.5 w-7.5 rounded-lg text-xs font-bold transition shadow-2xs cursor-pointer flex items-center justify-center bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.1] text-slate-700 dark:text-slate-200 shrink-0"
                                            title="Inspect Memorial Photo">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                @endif

                                <button type="button"
                                        @click="openReviewModal({{ $dedication->id }}, @js($dPetName), @js($adopterName), @js($dDateStr), @js($dedication->notes ?? ''), @js($dedication->review_decision ?? 'cleared_accidental'), @js($dedication->staff_remarks ?? ''), @js($dPhotoUrl))"
                                        class="h-7.5 px-3 rounded-lg text-xs font-bold transition shadow-2xs cursor-pointer flex items-center justify-center {{ $isPending ? 'bg-amber-600 hover:bg-amber-700 text-white shadow-amber-600/20' : 'bg-[#199CA4] hover:bg-[#13787F] text-white shadow-[#199CA4]/20' }} shrink-0">
                                    <span>{{ $isPending ? 'Review' : 'Re-evaluate' }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <div class="col-span-full py-16 text-center bg-white dark:bg-[#12141C] rounded-2xl sm:rounded-3xl border border-slate-200/80 dark:border-white/[0.07] p-8">
                <div class="w-14 h-14 rounded-2xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                <h3 class="text-base font-extrabold text-slate-800 dark:text-white">No Pet Dedications Found</h3>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                    No memorial dedication reports matched your search or filters. When adopters report a passing companion, it will appear here for shelter determination.
                </p>
                @if(!empty($search) || ($dedicationFilter ?? 'all') !== 'all')
                    <div class="mt-4">
                        <a href="{{ route('adopters.reports', ['tab' => 'pet_dedications']) }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 text-xs font-bold text-slate-700 dark:text-slate-200 transition">
                            Clear Filters & Search
                        </a>
                    </div>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Pagination for Pet Dedications --}}
    @if($dedications->hasPages())
        <div class="p-4 bg-white dark:bg-[#12141C] rounded-2xl border border-slate-200/80 dark:border-white/[0.07] shadow-xs">
            {{ $dedications->links() }}
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
                    {{ $totalReports }} {{ Str::plural('Record', $totalReports) }}
                </span>
                @if(($attentionReports ?? 0) > 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 animate-pulse">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        {{ $attentionReports }} Needs Attention
                    </span>
                @endif
            </div>
        </div>
        
        {{-- Header Actions --}}
        <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
            <a href="{{ route('adopters.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12141C] text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-bold hover:bg-slate-50 dark:hover:bg-[#171923] hover:text-[#199CA4] dark:hover:text-[#41C1CB] transition shadow-xs">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Adopter Profiles</span>
            </a>
        </div>
    </div>

    {{-- Search & Filter Toolbar for Monthly Updates --}}
    <div class="bg-white dark:bg-[#12141C] p-3 sm:p-4 rounded-2xl sm:rounded-3xl shadow-xs border border-slate-200/80 dark:border-white/[0.07]">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            {{-- Quick Filter Pills --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                <a href="{{ route('adopters.reports', array_merge(request()->except('status', 'page'), ['tab' => 'monthly_updates', 'status' => 'all'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ ($status ?? 'all') === 'all' ? 'bg-[#199CA4] text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/[0.1]' }}">
                    Total Updates ({{ $totalReports }})
                </a>
                <a href="{{ route('adopters.reports', array_merge(request()->except('status', 'page'), ['tab' => 'monthly_updates', 'status' => 'healthy'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ ($status ?? '') === 'healthy' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40' }}">
                    <span>Healthy & Active ({{ $healthyReports }})</span>
                </a>
                <a href="{{ route('adopters.reports', array_merge(request()->except('status', 'page'), ['tab' => 'monthly_updates', 'status' => 'minor_issue'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ in_array(($status ?? ''), ['minor_issue', 'under_treatment']) ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 dark:bg-white/[0.06] text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40' }}">
                    @if(($attentionReports ?? 0) > 0)
                        <span class="w-2 h-2 rounded-full {{ in_array(($status ?? ''), ['minor_issue', 'under_treatment']) ? 'bg-white' : 'bg-amber-500 animate-pulse' }}"></span>
                    @endif
                    <span>Needs Attention ({{ $attentionReports }})</span>
                </a>
            </div>

            {{-- Search Input --}}
            <form method="GET" action="{{ route('adopters.reports') }}" class="flex items-center gap-2 w-full md:w-auto">
                <input type="hidden" name="tab" value="monthly_updates">
                @if(($status ?? 'all') !== 'all')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                @if(($species ?? 'all') !== 'all')
                    <input type="hidden" name="species" value="{{ $species }}">
                @endif
                <div class="relative flex-1 md:w-80">
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}"
                           placeholder="Search adopter, ADP code, pet, breed..." 
                           class="w-full pl-9 {{ !empty($search) ? 'pr-8' : 'pr-3' }} py-2 text-xs sm:text-sm bg-slate-50 dark:bg-[#0C0D13] border border-slate-200 dark:border-white/[0.08] text-slate-800 dark:text-slate-100 rounded-xl focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition placeholder-slate-400 dark:placeholder-slate-500 font-medium">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    @if(!empty($search))
                        <a href="{{ route('adopters.reports', ['tab' => 'monthly_updates', 'status' => $status]) }}" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs font-bold" title="Clear search">
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

    {{-- Monthly Updates Grid (Compact dashboard grid matching Vaccination updates) --}}
    @php
        $groupedReports = $reports->getCollection()->groupBy(function($report) {
            return $report->user_id ? ('user_' . $report->user_id) : ('report_' . $report->id);
        });
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5 sm:gap-4">
        @forelse($groupedReports as $groupKey => $group)
            @php
                $firstReport = $group->first();
                $adopter = $firstReport->user?->adoptersProfile;
                $adopterName = $adopter?->full_name ?: ($firstReport->user?->name ?: 'Adopter');
                $adopterAvatar = $adopter?->avatar_url;
                $adopterInitials = $adopter?->initials ?: strtoupper(substr($adopterName, 0, 2));
                $adopterCode = $adopter?->adopter_code ?: ($firstReport->user ? sprintf('ADP-%04d', $firstReport->user->id) : 'ADP');
                $adopterContact = $adopter?->phone ?: ($firstReport->user?->email ?: null);
                $totalPetsInGroup = $group->count();
            @endphp

            <div class="bg-white dark:bg-[#12141C] rounded-xl sm:rounded-2xl border border-slate-200/80 dark:border-white/[0.07] shadow-2xs overflow-hidden hover:border-[#199CA4]/40 transition-all duration-200 flex flex-col"
                 x-data="{ activeIndex: 0 }">
                
                {{-- Adopter Top Header (Full Width) --}}
                <div class="p-3 sm:p-3.5 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between gap-2.5 bg-slate-50/60 dark:bg-white/[0.02]">
                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                        {{-- Adopter Avatar / Initials --}}
                        <div class="w-8 h-8 rounded-lg overflow-hidden shrink-0 border border-slate-200 dark:border-white/[0.08] bg-slate-100 dark:bg-[#12141C] relative">
                            @if($adopterAvatar)
                                <img src="{{ $adopterAvatar }}" alt="{{ $adopterName }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-[#199CA4] to-[#13787F] text-white flex items-center justify-center font-black text-[11px]">
                                    {{ $adopterInitials }}
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white leading-tight" title="Adopter: {{ $adopterName }}">
                                @if($adopterCode)
                                    <a href="{{ route('adopters.index', ['search' => $adopterCode]) }}" class="hover:text-[#199CA4] hover:underline transition-colors">{{ $adopterName }}</a>
                                @else
                                    {{ $adopterName }}
                                @endif
                            </h3>
                            <div class="flex items-center gap-1.5 mt-0.5 min-w-0">
                                @if($adopterCode)
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-slate-100 dark:bg-white/[0.06] text-slate-500 dark:text-slate-400 border border-slate-200/60 dark:border-white/[0.08] shrink-0 font-mono">{{ $adopterCode }}</span>
                                @endif
                                <span class="text-[10px] text-slate-400 truncate leading-tight font-medium">{{ $adopterContact ?: 'Verified Adopter' }}</span>
                            </div>
                        </div>
                    </div>

                    @if($totalPetsInGroup > 1)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/25 shrink-0" title="{{ $totalPetsInGroup }} pets in this household">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>{{ $totalPetsInGroup }} Pets</span>
                        </span>
                    @endif
                </div>

                {{-- Multi-Pet Switcher Tabs (Only shown if adopter has > 1 pet submission) --}}
                @if($totalPetsInGroup > 1)
                    <div class="px-3 py-1.5 bg-slate-100/70 dark:bg-white/[0.03] border-b border-slate-100 dark:border-white/[0.06] flex items-center gap-1.5 overflow-x-auto scrollbar-none">
                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 shrink-0 mr-0.5">Pet:</span>
                        @foreach($group->values() as $idx => $reportItem)
                            @php
                                $tabPet = $reportItem->pet;
                                $tabPetName = $tabPet && $tabPet->name ? $tabPet->name : ('Pet no. ' . $reportItem->pet_id);
                                $tabPetType = ucfirst($tabPet?->type ?? 'Pet');
                                $tabNeedsAttention = in_array($reportItem->health_status, ['minor_issue', 'under_treatment']) || ($reportItem->health_status === 'deceased' && (empty($reportItem->review_decision) || $reportItem->review_decision === 'pending'));
                            @endphp
                            <button type="button"
                                    @click="activeIndex = {{ $idx }}"
                                    :class="activeIndex === {{ $idx }} ? 'bg-[#199CA4] text-white shadow-xs font-bold' : 'bg-white dark:bg-[#12141C] text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200/80 dark:border-white/[0.08] font-medium'"
                                    class="px-2.5 py-1 rounded-lg text-[10px] transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                                <span>{{ $tabPetName }}</span>
                                <span class="text-[9px] opacity-75">({{ $tabPetType }})</span>
                                @if($tabNeedsAttention)
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse" title="Needs Attention / Review"></span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endif

                {{-- Pet Update Details (One per pet in the group) --}}
                @foreach($group->values() as $idx => $report)
                    @php
                        $pet = $report->pet;
                        $petName = $pet && $pet->name ? $pet->name : 'Pet no. ' . $report->pet_id;
                        $photoUrl = $report->photo_url;
                        $checkInDateStr = $report->check_in_date ? $report->check_in_date->format('M d, Y') : $report->created_at->format('M d, Y');
                        $weightStr = $report->weight ? number_format($report->weight, 1) . ' kg' : null;
                        $isHealthy = ($report->health_status === 'healthy');
                        $isAttention = in_array($report->health_status, ['minor_issue', 'under_treatment']);
                        $isDeceased = ($report->health_status === 'deceased');
                    @endphp

                    <div x-show="activeIndex === {{ $idx }}"
                         @if($idx > 0) x-cloak style="display: none;" @endif
                         class="flex-1 flex flex-col sm:flex-row min-w-0">
                        {{-- Left: Proof / Check-in Photo Thumbnail Container (Compact 110-128px on sm+) --}}
                        <div class="relative w-full sm:w-28 md:w-32 shrink-0 h-36 sm:h-auto bg-slate-950 overflow-hidden group cursor-pointer flex items-center justify-center"
                             @if($photoUrl)
                             data-photo="{{ $photoUrl }}"
                             data-title="{{ $adopterName }} - {{ $petName }} Monthly Check-in"
                             data-date="{{ $checkInDateStr }}"
                             data-weight="{{ $weightStr ?? 'Not recorded' }}"
                             data-status="{{ $report->health_status }}"
                             data-notes="{{ $report->notes }}"
                             @click="openFromCard($event)"
                             @endif
                             title="Click to inspect photo">
                            @if($photoUrl)
                                <img src="{{ $photoUrl }}" alt="{{ $adopterName }} - {{ $petName }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <div class="absolute inset-0 bg-black/30 group-hover:bg-black/50 transition-colors flex flex-col items-center justify-center gap-1">
                                    <div class="w-7 h-7 rounded-full bg-black/60 backdrop-blur-xs flex items-center justify-center text-white shadow-2xs group-hover:scale-110 transition-transform">
                                        <svg class="w-3.5 h-3.5 text-[#41C1CB]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                                    </div>
                                    <span class="text-[9px] font-bold text-white bg-black/70 backdrop-blur-xs px-2 py-0.5 rounded-full shadow-2xs">
                                        Inspect
                                    </span>
                                </div>
                            @else
                                <div class="w-full h-full min-h-[110px] flex flex-col items-center justify-center text-slate-500 text-xs p-2 text-center">
                                    <svg class="w-5 h-5 mb-1 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span class="text-[10px]">No Photo</span>
                                </div>
                            @endif
                            <div class="absolute bottom-1.5 left-1.5 px-1.5 py-0.5 rounded bg-black/80 backdrop-blur-xs text-white text-[8px] font-black uppercase tracking-wider">
                                {{ $weightStr ?? 'Monthly' }}
                            </div>
                        </div>

                        {{-- Right: Content & Information --}}
                        <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between gap-2.5 min-w-0">
                            <div>
                                {{-- Top Row: Check-in Time & Health Status Pill --}}
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 truncate">
                                        <span>Check-in: <strong class="text-slate-700 dark:text-slate-300 font-medium">{{ $checkInDateStr }}</strong></span>
                                    </div>

                                    {{-- Health Status Pill --}}
                                    @if($isHealthy)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/25 shrink-0">
                                            Healthy &amp; Active
                                        </span>
                                    @elseif($report->health_status === 'minor_issue')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/25 shrink-0">
                                            Minor Issue
                                        </span>
                                    @elseif($report->health_status === 'under_treatment')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/25 shrink-0">
                                            Under Treatment
                                        </span>
                                    @elseif($isDeceased)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-slate-900 text-white dark:bg-slate-800 border border-slate-700 shrink-0">
                                            Passed Away
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08] shrink-0">
                                            {{ ucfirst($report->health_status ?? 'Submitted') }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Center: Adopted Pet Details --}}
                                <div class="my-2 p-2 rounded-xl bg-slate-50 dark:bg-white/[0.04] border border-slate-100 dark:border-white/[0.06] flex items-center gap-2.5 min-w-0">
                                    @if($pet?->photo_url || $pet?->photo_path)
                                        <img src="{{ $pet->photo_url ?: asset('storage/'.ltrim($pet->photo_path, '/')) }}" alt="{{ $petName }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shrink-0 shadow-2xs">
                                    @else
                                        <div class="w-8 h-8 rounded-lg bg-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center font-black text-[11px] shrink-0">
                                            #{{ $report->pet_id }}
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <span class="text-[9px] uppercase font-bold text-slate-400 dark:text-slate-500 tracking-wider shrink-0">Adopted Pet:</span>
                                            <strong class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-100 truncate">{{ $petName }}</strong>
                                        </div>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate leading-tight mt-0.5">
                                            {{ ucfirst($pet?->type ?? 'Pet') }} &bull; {{ $pet?->breed ?? 'Mixed Breed' }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Life Event Status Bar (if deceased) --}}
                                @if($isDeceased)
                                    <div class="mb-2 p-1.5 rounded-lg {{ $report->review_decision === 'cleared_accidental' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600 dark:text-emerald-400' : ($report->review_decision === 'neglect_confirmed' ? 'bg-rose-500/10 border-rose-500/20 text-rose-600 dark:text-rose-400' : 'bg-amber-500/10 border-amber-500/20 text-amber-600 dark:text-amber-400') }} border text-[10px] font-bold flex items-center justify-between gap-1">
                                        <span class="truncate">
                                            @if($report->review_decision === 'cleared_accidental')
                                                Passing Verified: Natural / Accidental
                                            @elseif($report->review_decision === 'neglect_confirmed')
                                                Neglect Confirmed: Sanctioned
                                            @else
                                                Life Event: Awaiting Staff Review
                                            @endif
                                        </span>
                                        <span class="uppercase tracking-wider text-[8px] font-black shrink-0">
                                            {{ $report->review_decision ? ucfirst(str_replace('_', ' ', $report->review_decision)) : 'Pending' }}
                                        </span>
                                    </div>
                                @endif

                                {{-- Observation / Notes Snippet --}}
                                <div class="mt-2 space-y-1">
                                    @if(!empty($report->notes))
                                        <p class="text-[11px] text-slate-600 dark:text-slate-300 font-medium line-clamp-2 leading-snug italic" title="{{ $report->notes }}">
                                            "{{ $report->notes }}"
                                        </p>
                                    @else
                                        <p class="text-[10px] text-slate-400 dark:text-slate-500 italic">No notes recorded.</p>
                                    @endif

                                    <div class="flex items-center justify-between text-[10px] text-slate-500 dark:text-slate-400 pt-0.5">
                                        <span class="truncate text-[9px] text-slate-400">{{ $report->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Action Row & Footer --}}
                            <div class="pt-2 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between gap-2">
                                <div class="min-w-0 text-[10px]">
                                    <span class="text-slate-400 block text-[9px] leading-tight">Recorded Weight:</span>
                                    @if($weightStr)
                                        <span class="font-bold text-[#199CA4] dark:text-[#41C1CB] truncate block leading-tight">{{ $weightStr }}</span>
                                    @else
                                        <span class="text-slate-400 font-semibold text-[10px] truncate block leading-tight">Not recorded</span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    @if($isDeceased)
                                        <button type="button"
                                                @click="openReviewModal({{ $report->id }}, '{{ addslashes($petName) }}', '{{ addslashes($adopterName) }}', '{{ addslashes($checkInDateStr) }}', '{{ addslashes(str_replace(["\r", "\n"], ' ', $report->notes ?? '')) }}', '{{ $report->review_decision ?? 'cleared_accidental' }}', '{{ addslashes(str_replace(["\r", "\n"], ' ', $report->staff_remarks ?? '')) }}')"
                                                class="h-7 px-2.5 rounded-lg text-xs font-bold transition shadow-2xs cursor-pointer flex items-center justify-center gap-1 {{ empty($report->review_decision) || $report->review_decision === 'pending' ? 'bg-amber-600 hover:bg-amber-700 text-white shadow-amber-600/20' : 'bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300' }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            <span>{{ empty($report->review_decision) || $report->review_decision === 'pending' ? 'Review' : 'Decision' }}</span>
                                        </button>
                                    @endif

                                    @if($photoUrl)
                                        <button type="button" 
                                                data-photo="{{ $photoUrl }}"
                                                data-title="{{ $adopterName }} - {{ $petName }} Monthly Check-in"
                                                data-date="{{ $checkInDateStr }}"
                                                data-weight="{{ $weightStr ?? 'Not recorded' }}"
                                                data-status="{{ $report->health_status }}"
                                                data-notes="{{ $report->notes }}"
                                                @click="openFromCard($event)"
                                                class="h-7 px-3 rounded-lg text-xs font-bold transition shadow-2xs cursor-pointer flex items-center justify-center gap-1 bg-[#199CA4] hover:bg-[#13787F] text-white shadow-[#199CA4]/20 shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Inspect Photo</span>
                                        </button>
                                    @else
                                        <a href="{{ route('adopters.index', ['search' => $adopterCode]) }}"
                                           class="h-7 px-3 rounded-lg text-xs font-bold bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.1] text-slate-700 dark:text-slate-200 transition shadow-2xs flex items-center justify-center gap-1 shrink-0">
                                            <span>Profile</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <div class="col-span-full py-16 text-center bg-white dark:bg-[#12141C] rounded-3xl border border-slate-200/80 dark:border-white/[0.07] p-8">
                <div class="w-14 h-14 rounded-2xl bg-[#199CA4]/10 dark:bg-[#199CA4]/20 text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-base font-extrabold text-slate-800 dark:text-white">No Monthly Updates Found</h3>
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

    {{-- Pagination for Monthly Updates --}}
    @if($reports->hasPages())
        <div class="p-4 bg-white dark:bg-[#12141C] rounded-2xl border border-slate-200/80 dark:border-white/[0.07] shadow-xs">
            {{ $reports->links() }}
        </div>
    @endif
@endif

        {{-- Deceased Life Event Review Modal --}}
        <div x-show="showReviewModal" 
             x-cloak 
             class="fixed inset-0 z-[75] overflow-y-auto" 
             aria-labelledby="review-modal-title" 
             role="dialog" 
             aria-modal="true"
             @keydown.escape.window="if (!showPhotoModal) showReviewModal = false">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-xs" 
                     @click="showReviewModal = false"
                     x-show="showReviewModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"></div>

                <div class="relative inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-[#12141C] shadow-2xl rounded-3xl border border-slate-200 dark:border-white/[0.1] z-10"
                     x-show="showReviewModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-white/[0.08]">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight" id="review-modal-title">
                                Review Deceased Life Event
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Companion: <span class="font-bold text-slate-700 dark:text-slate-200" x-text="reviewPetName"></span> &bull; Adopter: <span class="font-bold text-slate-700 dark:text-slate-200" x-text="reviewAdopterName"></span>
                            </p>
                        </div>
                        <button type="button" @click="showReviewModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-2xl font-light leading-none cursor-pointer">&times;</button>
                    </div>

                    {{-- Adopter's Submitted Reason & Supporting Documentation --}}
                    <div class="mt-4 p-3.5 sm:p-4 rounded-2xl bg-slate-50 dark:bg-[#171923] border border-slate-200/80 dark:border-white/[0.06] space-y-2">
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                            <span>Reason for Pet's Passing</span>
                            <template x-if="extractCause(reviewAdopterNotes)">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-500/20" x-text="extractCause(reviewAdopterNotes)"></span>
                            </template>
                        </div>
                        <p class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-100 italic leading-relaxed" x-text="'&quot;' + cleanReason(reviewAdopterNotes) + '&quot;'"></p>
                        
                        <template x-if="reviewPhotoUrl">
                            <div class="pt-2 border-t border-slate-200/60 dark:border-white/[0.06] flex items-center justify-between">
                                <span class="text-[10px] text-slate-400 font-medium">Supporting Evidence:</span>
                                <button type="button" 
                                        @click="openPhotoModal(reviewPhotoUrl, 'Submitted Memorial / Vet Proof - ' + reviewPetName, reviewCheckInDate, 'Memorial', 'deceased', reviewAdopterNotes)"
                                        class="inline-flex items-center gap-1.5 text-xs font-bold text-[#199CA4] dark:text-[#41C1CB] hover:underline cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Inspect Uploaded Photo / Document</span>
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- Form --}}
                    <form :action="'{{ url('/adopters/reports') }}/' + reviewReportId + '/review'" method="POST" class="mt-4 space-y-4">
                        @csrf
                        @method('PATCH')

                        {{-- Shelter Verification Protocol (Audit Checklist for Panel Defense) --}}
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#171923] border border-slate-200/80 dark:border-white/[0.06] space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                    Shelter Verification Protocol (Audit Checklist)
                                </span>
                                <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-[#199CA4]/10 text-[#199CA4] dark:text-[#41C1CB] border border-[#199CA4]/20">Standard SOP</span>
                            </div>
                            <div class="space-y-1.5 text-xs">
                                <label class="flex items-center gap-2 text-slate-700 dark:text-slate-300 cursor-pointer select-none">
                                    <input type="checkbox" class="rounded border-slate-300 dark:border-white/[0.1] text-[#199CA4] focus:ring-[#199CA4]/30" checked>
                                    <span class="text-[11px] font-medium">Physical or veterinary documentation inspected</span>
                                </label>
                                <label class="flex items-center gap-2 text-slate-700 dark:text-slate-300 cursor-pointer select-none">
                                    <input type="checkbox" class="rounded border-slate-300 dark:border-white/[0.1] text-[#199CA4] focus:ring-[#199CA4]/30">
                                    <span class="text-[11px] font-medium">Direct phone / SMS contact conducted with adopter</span>
                                </label>
                                <label class="flex items-center gap-2 text-slate-700 dark:text-slate-300 cursor-pointer select-none">
                                    <input type="checkbox" class="rounded border-slate-300 dark:border-white/[0.1] text-[#199CA4] focus:ring-[#199CA4]/30">
                                    <span class="text-[11px] font-medium">Attending veterinary clinic / medical records cross-verified</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-2">
                                Select Decision:
                            </label>
                            <div class="grid grid-cols-1 gap-2">
                                {{-- Option 1: Cleared Accidental / Natural --}}
                                <label class="p-3 rounded-xl border flex items-center justify-between gap-3 cursor-pointer transition select-none"
                                       :class="reviewCurrentDecision === 'cleared_accidental' ? 'border-emerald-500 bg-emerald-50/70 dark:bg-emerald-950/30 ring-1 ring-emerald-500/40' : 'border-slate-200 dark:border-white/[0.08] hover:bg-slate-50 dark:hover:bg-white/[0.02]'">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <input type="radio" name="decision" value="cleared_accidental" x-model="reviewCurrentDecision" class="text-emerald-600 focus:ring-emerald-500 shrink-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white leading-tight">Natural / Accidental</span>
                                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold leading-tight">&bull; No Neglect</span>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 shrink-0">Restores Slot</span>
                                </label>

                                {{-- Option 2: Neglect Confirmed --}}
                                <label class="p-3 rounded-xl border flex items-center justify-between gap-3 cursor-pointer transition select-none"
                                       :class="reviewCurrentDecision === 'neglect_confirmed' ? 'border-rose-500 bg-rose-50/70 dark:bg-rose-950/30 ring-1 ring-rose-500/40' : 'border-slate-200 dark:border-white/[0.08] hover:bg-slate-50 dark:hover:bg-white/[0.02]'">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <input type="radio" name="decision" value="neglect_confirmed" x-model="reviewCurrentDecision" class="text-rose-600 focus:ring-rose-500 shrink-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white leading-tight">Adopter Neglect</span>
                                            <span class="text-[10px] text-rose-600 dark:text-rose-400 font-semibold leading-tight">&bull; Care Violation</span>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300 shrink-0">Restricts Adopter</span>
                                </label>
                            </div>
                        </div>

                        {{-- Staff Remarks --}}
                        <div>
                            <label class="block text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1.5">
                                Shelter Remarks <span class="text-slate-400 dark:text-slate-500 font-normal lowercase">(optional)</span>:
                            </label>
                            <textarea name="staff_remarks" 
                                      rows="2" 
                                      x-model="reviewCurrentRemarks"
                                      placeholder="Optional: Enter shelter remarks or investigation notes..."
                                      class="w-full text-xs rounded-xl border-slate-300 dark:border-white/[0.1] bg-white dark:bg-[#171923] text-slate-900 dark:text-white focus:ring-2 focus:ring-[#199CA4] focus:border-[#199CA4]"></textarea>
                        </div>

                        {{-- Modal Action Buttons --}}
                        <div class="pt-3 border-t border-slate-100 dark:border-white/[0.08] flex items-center justify-end gap-2.5">
                            <button type="button" @click="showReviewModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.1] rounded-xl transition cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-[#199CA4] hover:bg-[#13787F] rounded-xl transition cursor-pointer shadow-xs">
                                Save Decision
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
             @keydown.escape.window="if (!showPhotoModal) showVerifyModal = false">
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
                                    Adopter: <span class="font-bold text-slate-800 dark:text-slate-200" x-text="verifyAdopterName"></span> &bull; Pet: <span class="font-bold text-slate-800 dark:text-slate-200" x-text="verifyPetName"></span>
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
                                    <span x-text="Math.round(imgZoom * 100) + '%'" class="text-[11px] font-bold text-slate-500 dark:text-slate-400 px-1 select-none min-w-[36px] text-center" title="Current zoom level"></span>
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
                            <div class="relative w-full h-[400px] sm:h-[460px] rounded-2xl bg-slate-900/90 dark:bg-black/90 flex items-center justify-center overflow-hidden border border-slate-200/60 dark:border-white/[0.08] select-none touch-none"
                                 :class="imgZoom > 1 ? (isPanning ? 'cursor-grabbing' : 'cursor-grab') : 'cursor-zoom-in'"
                                 @wheel.prevent="handleWheel($event)"
                                 @mousedown="startPan($event)"
                                 @mousemove="doPan($event)"
                                 @mouseup="endPan()"
                                 @mouseup.window="endPan()"
                                 @mouseleave="endPan()"
                                 @touchstart="startTouch($event)"
                                 @touchmove="doTouch($event)"
                                 @touchend="endTouch($event)"
                                 @dblclick="toggleZoom()">
                                <template x-if="verifyProofPhotoUrl">
                                    <img :src="verifyProofPhotoUrl" 
                                         alt="Proof Photo" 
                                         :style="'transform: translate(' + imgPanX + 'px, ' + imgPanY + 'px) rotate(' + imgRotation + 'deg) scale(' + imgZoom + '); transform-origin: center center; transition: ' + (isPanning ? 'none' : 'transform 0.15s ease-out') + ';'"
                                         class="max-w-full max-h-full object-contain pointer-events-none select-none">
                                </template>
                                <template x-if="!verifyProofPhotoUrl">
                                    <div class="text-slate-400 text-xs">No proof photo available for this submission.</div>
                                </template>

                                {{-- Quick Zoom Helper Hint Overlay (fades out when interacting or zoomed) --}}
                                <div x-show="imgZoom === 1 && !isPanning" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 translate-y-1"
                                     class="absolute bottom-2.5 left-1/2 -translate-x-1/2 px-2.5 py-1 rounded-full bg-black/60 backdrop-blur-xs text-[10px] text-white/70 pointer-events-none select-none flex items-center gap-1.5 shadow-sm">
                                    <svg class="w-3 h-3 text-[#199CA4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                                    <span>Scroll or pinch to zoom &bull; Drag to pan</span>
                                </div>
                            </div>

                            <p class="text-[11px] text-slate-400 dark:text-slate-500 text-center mt-2 font-medium">
                                Inspect clinic stamp, veterinarian signature, administered date, and vaccine lot/sticker. Double-click to toggle 2x zoom.
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
                                            <span>Next Vaccination / Due Date</span>
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
                                        Calculated from date administered. If the vet card states a different return date, adjust the date above. When approved, a push notification will automatically notify the adopter of their next vaccination date.
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
                                                <span>Verify Record & Set Next Date</span>
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

        {{-- Photo Zoom / Inspection Modal (Highest Stacking Layer) --}}
        <div x-show="showPhotoModal" 
             x-cloak 
             class="fixed inset-0 z-[100] overflow-y-auto" 
             style="position: fixed; inset: 0; z-index: 99999 !important;"
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
                     x-transition:leave-end="opacity-0"
                     style="position: fixed; inset: 0; z-index: 99998 !important;"></div>

                <div class="inline-block w-full max-w-2xl p-5 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-[#12141C] rounded-3xl shadow-2xl border border-slate-200 dark:border-white/[0.1]"
                     x-show="showPhotoModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     style="position: relative; z-index: 99999 !important;"
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

</x-app-layout>