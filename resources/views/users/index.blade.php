<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#333634] tracking-tight">User Management</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Manage admin and staff accounts.</p>
            </div>
            <div>
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-xl bg-[#199CA4] px-4 py-2.5 text-xs sm:text-sm font-bold text-white hover:bg-[#13787F] transition-all shadow-xs">
                    + Create User
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/80 border-b border-gray-100 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3.5">User</th>
                            <th class="px-6 py-3.5">Email</th>
                            <th class="px-6 py-3.5">Role</th>
                            <th class="px-6 py-3.5">Created Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs sm:text-sm">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        @if($user->avatar_url)
                                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-2xl object-cover border border-gray-200 shadow-xs flex-shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#EAF5F6] to-[#d3eef1] text-[#199CA4] flex items-center justify-center font-extrabold text-xs border border-[#199CA4]/20 shadow-xs flex-shrink-0">
                                                {{ $user->initials }}
                                            </div>
                                        @endif
                                        <div>
                                            <span class="block text-gray-900 font-bold text-sm leading-tight">{{ $user->name }}</span>
                                            <span class="block text-[11px] text-gray-400 font-normal mt-0.5">ID: #{{ $user->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 font-medium">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $user->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>