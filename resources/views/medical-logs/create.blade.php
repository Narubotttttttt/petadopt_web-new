<x-app-layout>
    <div class="max-w-3xl mx-auto py-10 px-4">
        <div class="bg-white dark:bg-[#0e1d20] p-8 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-800">

            <div class="flex items-center gap-3 mb-8 border-b-2 border-[#199CA4] pb-4">
                <span class="text-2xl">🩺</span>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Add Medical Log</h1>
            </div>

            @if($errors->any())
                <div class="mb-6 px-4 py-3 bg-red-50 dark:bg-rose-950/50 border border-red-200 dark:border-rose-800 text-red-700 dark:text-rose-300 rounded-xl">
                    <ul class="list-disc pl-5 text-sm space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('medical-logs.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Pet</label>
                    <select name="pet_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-[#12272b] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white" required>
                        <option value="">Select pet</option>
                        @foreach($pets as $p)
                            <option value="{{ $p->id }}" {{ old('pet_id', optional($pet)->id) == $p->id ? 'selected' : '' }}>
                                {{ $p->name ?? 'Pet no. '.$p->id }} — {{ ucfirst($p->type) }} ({{ $p->breed }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div x-data="{ category: '{{ old('category', '') }}' }">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Date</label>
                            <input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-[#12272b] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white" required>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Category</label>
                            <select name="category" x-model="category" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-[#12272b] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white" required>
                                <option value="">Select category</option>
                                <option value="vaccination">Vaccination</option>
                                <option value="deworming">Deworming</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6">
                        <template x-if="category === 'vaccination'">
                            <div class="bg-blue-50 dark:bg-blue-950/50 border border-blue-100 dark:border-blue-800 rounded-xl p-4 text-sm text-blue-700 dark:text-blue-300">
                                📅 Next due date will be automatically calculated as <strong>6 months</strong> from the date above.
                            </div>
                        </template>
                        <template x-if="category !== 'vaccination' && category !== ''">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Next Due Date <span class="text-gray-400 dark:text-slate-500 font-normal normal-case">(optional)</span></label>
                                <input type="date" name="next_due_date" value="{{ old('next_due_date') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-[#12272b] focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 transition outline-none shadow-sm text-gray-800 dark:text-white">
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex items-center gap-3 justify-end border-t border-gray-100 dark:border-slate-800 pt-6">
                    <a href="{{ route('medical-logs.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-600 dark:text-slate-300 border border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-800 transition">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 bg-[#199CA4] text-white text-sm font-semibold rounded-xl hover:bg-[#13787F] shadow-md shadow-[#199CA4]/10 transition cursor-pointer">Save Entry</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>