<x-app-layout>
    <div class="max-w-4xl mx-auto py-10 px-4 animate-fade-in">
        <div class="bg-white dark:bg-[#0e1d20] p-8 sm:p-10 rounded-3xl shadow-card border border-slate-200/80 dark:border-slate-800 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#199CA4] via-[#41C1CB] to-[#14838B]"></div>

            <div class="flex items-center gap-3.5 mb-8 border-b border-slate-100 dark:border-slate-800 pb-6">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#F0FBFB] to-[#D6F4F6] dark:from-[#133036] dark:to-[#17454d] text-[#199CA4] dark:text-[#41C1CB] flex items-center justify-center text-xl font-bold shadow-2xs ring-1 ring-[#199CA4]/20 dark:ring-[#41C1CB]/30">
                    🐾
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 dark:text-white tracking-tight">Edit Pet Profile</h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Update details, medical tags, or status for Pet #{{ $pet->id }}.</p>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 px-4 py-3.5 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 rounded-2xl flex items-center gap-2.5 text-sm font-bold shadow-2xs">
                    <span>✅</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 px-5 py-4 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 rounded-2xl shadow-2xs">
                    <div class="flex items-center gap-2 mb-2 font-extrabold text-sm">
                        <span>⚠️</span>
                        <span>Please fix the following issues:</span>
                    </div>
                    <ul class="list-disc pl-5 text-xs sm:text-sm space-y-1 font-medium">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('pets.update', $pet) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                @include('pets._form')

                <div class="flex items-center gap-3 justify-end border-t border-slate-100 dark:border-slate-800 pt-6 mt-8">
                    <a href="{{ route('pets.index') }}"
                        class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition">Cancel</a>
                    <button type="submit"
                        class="px-6 py-2.5 bg-gradient-to-r from-[#199CA4] to-[#14838B] hover:from-[#146970] hover:to-[#12585e] text-white text-xs sm:text-sm font-extrabold rounded-xl shadow-md shadow-[#199CA4]/25 hover:shadow-lg hover:shadow-[#199CA4]/30 transition-all cursor-pointer">Update Pet Profile</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
