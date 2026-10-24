<x-app-layout>
    <div class="max-w-4xl mx-auto py-10 px-4 animate-fade-in">
        <div class="bg-white dark:bg-[#12141C] p-8 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-gray-100 dark:border-white/[0.07]">
            
            <div class="flex items-center justify-between mb-8 border-b-2 border-[#199CA4] pb-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                        @if(($pet->status ?? '') === 'in_shelter')
                            Shelter Pet Intake Registration
                        @elseif(($pet->status ?? '') === 'available')
                            Ready for Adoption Registration
                        @else
                            Add New Pet
                        @endif
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        @if(($pet->status ?? '') === 'in_shelter')
                            Streamlined 2-step intake triage for newly rescued stray animals.
                        @elseif(($pet->status ?? '') === 'available')
                            Full 3-step listing profile with temperament tagging and adoption matching.
                        @else
                            Register a new animal in the CAWS shelter database.
                        @endif
                    </p>
                </div>
                <a href="{{ route('pets.index') }}" class="inline-flex items-center justify-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl bg-[#199CA4] hover:bg-[#13787F] text-white text-xs sm:text-sm font-extrabold shadow-xs transition-all duration-200 shrink-0 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Directory
                </a>
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

            <form action="{{ route('pets.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                @include('pets._form', ['isWizard' => true])
            </form>
        </div>
    </div>
</x-app-layout>