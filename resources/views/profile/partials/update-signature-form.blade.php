<section class="bg-white dark:bg-[#12141C] p-6 sm:p-8 rounded-3xl border border-gray-100 dark:border-white/[0.07] shadow-sm space-y-6">
    
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-[#199CA4] dark:text-[#41C1CB] border border-teal-100 dark:border-teal-900/50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </span>
                <h3 class="text-base sm:text-lg font-extrabold text-gray-900 dark:text-white tracking-tight">
                    Official Staff E-Signature
                </h3>
            </div>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-slate-400 mt-1">
                Your digital signature is automatically attached as the authorized CAWS representative when approving adoption contracts.
            </p>
        </div>

        @if($user->digital_signature_url)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 self-start sm:self-auto">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Verified on Record</span>
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 self-start sm:self-auto">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span>Not Set Yet</span>
            </span>
        @endif
    </div>

    @if (session('status') === 'signature-updated')
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)"
            class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-xs sm:text-sm font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>Staff digital signature saved successfully on record!</span>
        </div>
    @endif

    {{-- Grid: Current Signature Display (Left) & Drawing Pad (Right) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        {{-- Left: Active Signature Preview --}}
        <div class="lg:col-span-5 space-y-3">
            <label class="block text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-slate-400">
                Signature on File
            </label>

            <div class="h-44 rounded-2xl border-2 border-dashed border-gray-200 dark:border-white/[0.1] bg-gray-50/70 dark:bg-[#0C0D13] p-4 flex flex-col items-center justify-center text-center relative overflow-hidden group">
                @if($user->digital_signature_url)
                    <img src="{{ $user->digital_signature_url }}" alt="Staff Signature" class="max-h-28 max-w-full object-contain filter dark:invert dark:brightness-200">
                    <p class="text-[10px] text-gray-400 dark:text-slate-500 mt-2 font-medium">
                        Active on all CAWS adoption contracts
                    </p>
                @else
                    <div class="p-3 rounded-2xl bg-gray-100 dark:bg-white/[0.04] text-gray-400 dark:text-slate-500 mb-2">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </div>
                    <p class="text-xs font-bold text-gray-500 dark:text-slate-400">No Signature on File</p>
                    <p class="text-[11px] text-gray-400 dark:text-slate-500 mt-0.5">Use the drawing pad to create your official signature.</p>
                @endif
            </div>
        </div>

        {{-- Right: Drawing Pad Form --}}
        <div class="lg:col-span-7 space-y-3">
            <div class="flex items-center justify-between">
                <label class="block text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-slate-400">
                    Draw New Signature
                </label>
                <button type="button" id="clearStaffSigBtn" class="text-xs font-bold text-[#199CA4] hover:text-[#13787F] dark:text-[#41C1CB] hover:underline cursor-pointer">
                    Clear Pad
                </button>
            </div>

            <form id="staffSignatureForm" method="POST" action="{{ route('profile.signature.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="signature_data" id="staffSignatureDataInput">

                {{-- Canvas Box --}}
                <div class="relative rounded-2xl border border-gray-300 dark:border-white/[0.15] bg-white dark:bg-[#0C0D13] shadow-xs overflow-hidden">
                    <canvas id="staffSignatureCanvas" class="w-full h-44 cursor-crosshair touch-none block"></canvas>
                    
                    {{-- Guideline Baseline --}}
                    <div class="absolute bottom-6 left-6 right-6 border-b border-gray-300/80 dark:border-white/10 pointer-events-none flex justify-between items-center text-[10px] text-gray-400 dark:text-slate-600 select-none pb-1">
                        <span>Staff Signature Line</span>
                        
                    </div>

                    {{-- Placeholder hint --}}
                    <div id="sigPlaceholderHint" class="absolute inset-0 flex items-center justify-center pointer-events-none text-xs text-gray-400 dark:text-slate-600 font-medium">
                        Sign with mouse or stylus here
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 pt-1">
                    <p class="text-[11px] text-gray-400 dark:text-slate-500">
                        Signatures are saved securely in high-resolution transparent format.
                    </p>
                    <button type="submit" id="saveStaffSigBtn" class="px-5 py-2.5 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white text-xs sm:text-sm font-extrabold shadow-sm transition cursor-pointer flex items-center gap-2 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Save Staff Signature</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

</section>

{{-- Signature Canvas Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('staffSignatureCanvas');
        const hint = document.getElementById('sigPlaceholderHint');
        const clearBtn = document.getElementById('clearStaffSigBtn');
        const form = document.getElementById('staffSignatureForm');
        const input = document.getElementById('staffSignatureDataInput');

        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let hasDrawn = false;

        // Resize canvas for sharp resolution (Retina / High-DPI support)
        function resizeCanvas() {
            const rect = canvas.getBoundingClientRect();
            const ratio = window.devicePixelRatio || 1;
            canvas.width = rect.width * ratio;
            canvas.height = rect.height * ratio;
            ctx.scale(ratio, ratio);
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.lineWidth = 2.5;
            ctx.strokeStyle = document.documentElement.classList.contains('dark') ? '#41C1CB' : '#0f172a';
        }

        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        function startDrawing(e) {
            e.preventDefault();
            isDrawing = true;
            hasDrawn = true;
            if (hint) hint.style.display = 'none';
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        }

        function stopDrawing() {
            isDrawing = false;
        }

        // Pointer / Touch Events
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        window.addEventListener('mouseup', stopDrawing);

        canvas.addEventListener('touchstart', startDrawing, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        window.addEventListener('touchend', stopDrawing);

        // Clear button
        clearBtn.addEventListener('click', function () {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            hasDrawn = false;
            if (hint) hint.style.display = 'flex';
        });

        // Form Submit
        form.addEventListener('submit', function (e) {
            if (!hasDrawn) {
                e.preventDefault();
                alert('Please draw your signature before saving.');
                return;
            }

            // Export pure transparent PNG from canvas
            // Create temporary canvas to ensure dark stroke in exported PNG
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = canvas.width;
            tempCanvas.height = canvas.height;
            const tempCtx = tempCanvas.getContext('2d');
            
            // Draw original content
            tempCtx.drawImage(canvas, 0, 0);

            input.value = canvas.toDataURL('image/png');
        });
    });
</script>
