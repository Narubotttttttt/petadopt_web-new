{{-- Custom Styled Confirmation Modal --}}
<div 
    id="custom-confirm-modal" 
    class="fixed inset-0 z-50 items-center justify-center p-4 sm:p-6 overflow-y-auto hidden"
    style="display: none;"
    role="dialog" 
    aria-modal="true"
>
    {{-- Backdrop --}}
    <div 
        onclick="closeConfirmModal()" 
        class="fixed inset-0 bg-slate-900/60 dark:bg-black/80 backdrop-blur-xs transition-opacity cursor-pointer"
    ></div>

    {{-- Modal Card --}}
    <div class="relative w-full max-w-md bg-white dark:bg-[#12141C] rounded-3xl p-6 sm:p-7 shadow-2xl border border-slate-200/90 dark:border-white/[0.08] overflow-hidden z-10 transition-all scale-100">
        <div class="flex items-start gap-4">
            <div id="confirm-modal-icon-container" class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900/80 flex items-center justify-center shrink-0 text-rose-600 dark:text-rose-400 shadow-2xs">
                <svg id="confirm-modal-icon-danger" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <svg id="confirm-modal-icon-success" class="w-6 h-6 hidden" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="flex-1 min-w-0 pt-0.5">
                <h3 id="confirm-modal-title" class="text-base sm:text-lg font-medium text-slate-800 dark:text-slate-200 tracking-tight leading-snug [&>strong]:font-extrabold [&>strong]:text-slate-900 dark:[&>strong]:text-white">Delete?</h3>
                <p id="confirm-modal-message" class="text-xs text-slate-400 dark:text-slate-500 mt-1">This action cannot be undone.</p>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-white/[0.06]">
            <button 
                type="button" 
                onclick="closeConfirmModal()" 
                id="confirm-modal-cancel-btn"
                class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.06] border border-slate-200 dark:border-white/[0.08] transition-colors cursor-pointer"
            >
                Cancel
            </button>

            <form id="confirm-modal-form" action="" method="POST" class="inline" onsubmit="handleConfirmSubmit(this)">
                @csrf
                <input type="hidden" name="_method" id="confirm-modal-method" value="POST">
                <button 
                    type="submit" 
                    id="confirm-modal-submit-btn"
                    class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-xs transition-all cursor-pointer flex items-center gap-2"
                >
                    <span id="confirm-modal-spinner" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin hidden"></span>
                    <span id="confirm-modal-submit-text">Delete</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    window.openConfirmModal = function(options) {
        if (!options) return;
        var modal = document.getElementById('custom-confirm-modal');
        if (!modal) return;

        var title = options.title || 'Are you sure?';
        var message = options.message || '';
        var confirmText = options.confirmText || options.confirmtext || 'Confirm';
        var cancelText = options.cancelText || options.canceltext || 'Cancel';
        var action = options.action || '';
        var method = (options.method || 'POST').toUpperCase();
        var type = options.type || options.theme || (method === 'DELETE' ? 'danger' : 'info');
        var loadingText = options.loadingText || options.loadingtext || (type === 'success' ? 'Updating...' : (method === 'DELETE' ? 'Deleting...' : 'Processing...'));

        var titleEl = document.getElementById('confirm-modal-title');
        var msgEl = document.getElementById('confirm-modal-message');
        var submitTextEl = document.getElementById('confirm-modal-submit-text');
        var cancelBtnEl = document.getElementById('confirm-modal-cancel-btn');
        var formEl = document.getElementById('confirm-modal-form');
        var methodEl = document.getElementById('confirm-modal-method');
        var iconContainer = document.getElementById('confirm-modal-icon-container');
        var iconDanger = document.getElementById('confirm-modal-icon-danger');
        var iconSuccess = document.getElementById('confirm-modal-icon-success');
        var submitBtn = document.getElementById('confirm-modal-submit-btn');

        if (titleEl) titleEl.innerHTML = title;
        if (msgEl) {
            if (message) {
                msgEl.innerHTML = message;
                msgEl.style.display = 'block';
            } else {
                msgEl.style.display = 'none';
            }
        }
        if (submitTextEl) submitTextEl.textContent = confirmText;
        if (cancelBtnEl) cancelBtnEl.textContent = cancelText;
        if (formEl) formEl.action = action;
        if (methodEl) methodEl.value = method;

        // Theme styling
        if (type === 'success') {
            if (iconContainer) {
                iconContainer.className = "w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-900/80 flex items-center justify-center shrink-0 text-emerald-600 dark:text-emerald-400 shadow-2xs";
            }
            if (iconDanger) iconDanger.classList.add('hidden');
            if (iconSuccess) iconSuccess.classList.remove('hidden');
            if (submitBtn) {
                submitBtn.className = "px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-all cursor-pointer flex items-center gap-2";
            }
        } else {
            if (iconContainer) {
                iconContainer.className = "w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900/80 flex items-center justify-center shrink-0 text-rose-600 dark:text-rose-400 shadow-2xs";
            }
            if (iconDanger) iconDanger.classList.remove('hidden');
            if (iconSuccess) iconSuccess.classList.add('hidden');
            if (submitBtn) {
                submitBtn.className = "px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-xs transition-all cursor-pointer flex items-center gap-2";
            }
        }

        window._currentConfirmLoadingText = loadingText;

        var spinner = document.getElementById('confirm-modal-spinner');
        if (submitBtn) submitBtn.disabled = false;
        if (spinner) spinner.classList.add('hidden');

        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    };

    window.closeConfirmModal = function() {
        var modal = document.getElementById('custom-confirm-modal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    };

    window.handleConfirmSubmit = function(form) {
        var submitBtn = document.getElementById('confirm-modal-submit-btn');
        var spinner = document.getElementById('confirm-modal-spinner');
        var submitText = document.getElementById('confirm-modal-submit-text');
        if (submitBtn && spinner) {
            submitBtn.disabled = true;
            spinner.classList.remove('hidden');
            if (submitText) {
                submitText.textContent = window._currentConfirmLoadingText || 'Processing...';
            }
        }
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeConfirmModal();
        }
    });

    window.addEventListener('open-confirm', function(e) {
        if (e && e.detail) {
            window.openConfirmModal(e.detail);
        }
    });
</script>
