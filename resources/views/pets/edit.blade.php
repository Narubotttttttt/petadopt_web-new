<x-app-layout>
    <div class="max-w-4xl mx-auto py-10 px-4 animate-fade-in">
        <div class="bg-white dark:bg-[#12141C] p-8 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-gray-100 dark:border-white/[0.07]">
            
            <div class="flex items-center gap-3 mb-8 border-b-2 border-[#199CA4] pb-4">
                
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Edit Pet Profile</h1>
            </div>

            @if(session('success'))
                <div class="mb-6 px-4 py-3 bg-green-50 dark:bg-emerald-950/40 border border-green-200 dark:border-emerald-800/60 text-green-700 dark:text-emerald-300 rounded-xl flex items-center gap-2">
                    
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 px-4 py-3 bg-red-50 dark:bg-rose-950/40 border border-red-200 dark:border-rose-800/60 text-red-700 dark:text-rose-300 rounded-xl">
                    <div class="flex items-center gap-2 mb-2 font-semibold">
                        
                        <span>Please fix the following errors:</span>
                    </div>
                    <ul class="list-disc pl-5 text-sm space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('pets.update', $pet) }}" method="POST" enctype="multipart/form-data" x-data="{ submitting: false }" @submit="if(submitting) { $event.preventDefault(); return false; } submitting = true;">
                @csrf
                @method('PATCH')

                @include('pets._form')

                <div class="flex items-center gap-3 justify-end border-t border-gray-100 dark:border-white/[0.06] pt-6">
                    <a href="{{ route('pets.index') }}"
                        class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-600 dark:text-slate-300 border border-gray-200 dark:border-white/[0.08] hover:bg-gray-50 dark:hover:bg-white/[0.06] transition">Cancel</a>
                    <button type="submit"
                        :disabled="submitting"
                        :class="{ 'opacity-60 cursor-not-allowed pointer-events-none': submitting }"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#199CA4] text-white text-sm font-semibold rounded-xl hover:bg-[#13787F] shadow-md shadow-[#199CA4]/10 transition cursor-pointer">
                        <svg x-show="submitting" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="submitting ? 'Updating Pet...' : 'Update Pet Profile'">Update Pet Profile</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
