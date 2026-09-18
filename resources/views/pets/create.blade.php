<x-app-layout>
    <div class="max-w-4xl mx-auto py-10 px-4 animate-fade-in">
        <div class="bg-white dark:bg-[#12141C] p-8 rounded-2xl shadow-sm dark:shadow-xl dark:shadow-black/40 border border-gray-100 dark:border-white/[0.07]">
            
            <div class="flex items-center gap-3 mb-8 border-b-2 border-[#199CA4] pb-4">
                
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Add New Pet</h1>
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