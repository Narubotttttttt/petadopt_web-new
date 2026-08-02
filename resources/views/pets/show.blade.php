<x-app-layout>
    <div class="max-w-4xl mx-auto py-10 px-4">
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold">{{ ucfirst($pet->type ?? 'Pet') }} #{{ $pet->id }}</h1>
                    <p class="text-sm text-gray-500">{{ $pet->breed ?? '—' }} • {{ ucfirst($pet->color ?? '—') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if(in_array(Auth::user()->role, ['admin', 'staff']))
                        <a href="{{ route('pets.edit', $pet) }}" class="px-4 py-2 bg-[#199CA4] text-white rounded-xl text-sm">Edit</a>
                    @endif

                    @unless(in_array(Auth::user()->role, ['admin', 'staff']))
                        @if($pet->status === 'available')
                            <a href="{{ route('adoption-applications.create', $pet) }}" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm hover:bg-green-700 transition">Apply to adopt</a>
                        @else
                            <span class="px-4 py-2 rounded-xl bg-gray-100 text-gray-600 text-sm">{{ ucfirst(str_replace('_', ' ', $pet->status)) }}</span>
                        @endif
                    @endunless

                    <a href="{{ route('pets.index') }}" class="px-4 py-2 border rounded-xl text-sm">Back</a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1">
                    <div class="w-full h-64 bg-gray-50 rounded-xl overflow-hidden flex items-center justify-center border border-gray-100">
                        @if($pet->photo_path)
                            <img src="{{ asset('storage/'.$pet->photo_path) }}" alt="Pet #{{ $pet->id }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-5xl">🐾</span>
                        @endif
                    </div>
                </div>
                <div class="md:col-span-2">
                    <div class="space-y-3">
                        <p><strong class="text-gray-700">Type:</strong> <span class="text-gray-600">{{ ucfirst($pet->type ?? '—') }}</span></p>
                        <p><strong class="text-gray-700">Breed:</strong> <span class="text-gray-600">{{ $pet->breed ?? '—' }}</span></p>
                        <p><strong class="text-gray-700">Color:</strong> <span class="text-gray-600">{{ $pet->color ?? '—' }}</span></p>
                        <p><strong class="text-gray-700">Gender:</strong> <span class="text-gray-600">{{ ucfirst($pet->gender ?? '—') }}</span></p>
                        <p><strong class="text-gray-700">Age:</strong> <span class="text-gray-600">{{ $pet->age ?? '—' }}</span></p>
                        <p><strong class="text-gray-700">Status:</strong> <span class="text-gray-600">{{ ucfirst(str_replace('_', ' ', $pet->status ?? 'available')) }}</span></p>
                        <p><strong class="text-gray-700">Medical history:</strong> <span class="text-gray-600">{{ $pet->medical_history ?? 'Not provided' }}</span></p>

                        <div>
                            <strong class="text-gray-700">Temperament:</strong>
                            @if($pet->temperamentTags->isNotEmpty())
                                <div class="flex flex-wrap gap-2 mt-1">
                                    @foreach($pet->temperamentTags as $tag)
                                        <span class="px-2.5 py-1 bg-[#199CA4]/10 text-[#199CA4] text-xs font-semibold rounded-full">{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-600">Not provided</span>
                            @endif
                        </div>

                        <p><strong class="text-gray-700">Description:</strong> <span class="text-gray-600">{{ $pet->description ?? 'Not provided' }}</span></p>
                        <p><strong class="text-gray-700">Added:</strong> <span class="text-gray-600">{{ $pet->created_at->diffForHumans() }}</span></p>
                    </div>
                </div>
            </div>

            @if(in_array(Auth::user()->role, ['admin', 'staff']))
                <div class="mt-10 pt-6 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900">🩺 Medical History</h2>
                        <a href="{{ route('medical-logs.create-for-pet', $pet) }}" class="px-4 py-2 bg-[#199CA4] text-white rounded-xl text-sm font-semibold hover:bg-[#13787F] transition">+ Add Entry</a>
                    </div>

                    @if($pet->medicalLogs->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($pet->medicalLogs as $log)
                                <div class="border border-gray-100 rounded-xl p-4 bg-gray-50/50">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-xs font-semibold text-gray-500">{{ $log->date->format('M d, Y') }}</span>
                                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-[#199CA4]/10 text-[#199CA4]">{{ ucfirst(str_replace('_', ' ', $log->category)) }}</span>
                                            </div>
                                            @if($log->administered_by)
                                                <p class="text-xs text-gray-400 mt-1">By {{ $log->administered_by }}</p>
                                            @endif
                                            @if($log->next_due_date)
                                                <p class="text-xs text-amber-600 mt-1">Next due: {{ $log->next_due_date->format('M d, Y') }}</p>
                                            @endif
                                        </div>
                                        <a href="{{ route('medical-logs.edit', $log) }}" class="text-xs font-semibold text-gray-500 hover:text-[#199CA4] whitespace-nowrap">Edit</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No medical log entries yet for this pet.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>