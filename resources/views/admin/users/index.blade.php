<x-admin-layout>
    <div class="space-y-6" x-data="{ deleteModalOpen: false, deleteActionUrl: '', deleteUserName: '' }">
        <!-- Header & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('User Management') }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ __('Manage registered user accounts, assign security roles, and monitor access.') }}</p>
            </div>
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Create User') }}
            </a>
        </div>

        <!-- Search Bar -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="{{ __('Search users by name or email...') }}"
                           class="block w-full pl-10 pr-3 py-2 text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" />
                </div>
                <button type="submit"
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium rounded-lg transition">
                    {{ __('Search') }}
                </button>
                @if ($search)
                    <a href="{{ route('admin.users.index') }}"
                       class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-lg text-center transition">
                        {{ __('Clear') }}
                    </a>
                @endif
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold">
                        <tr>
                            <th scope="col" class="px-6 py-3.5">{{ __('Name') }}</th>
                            <th scope="col" class="px-6 py-3.5">{{ __('Email') }}</th>
                            <th scope="col" class="px-6 py-3.5">{{ __('Roles') }}</th>
                            <th scope="col" class="px-6 py-3.5">{{ __('Created Date') }}</th>
                            <th scope="col" class="px-6 py-3.5 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($users as $user)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-slate-900">{{ $user->name }}</div>
                                    @if ($user->id === auth()->id())
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-100 text-blue-800">
                                            {{ __('You') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                                    {{ $user->email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($user->roles as $role)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $role->name === 'admin' ? 'bg-red-100 text-red-800' : ($role->name === 'agent' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700') }}">
                                                {{ $role->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-slate-400">{{ __('No role') }}</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 font-mono">
                                    {{ $user->created_at->format('Y-m-d') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                        {{ __('Edit') }}
                                    </a>

                                    @if ($user->id !== auth()->id())
                                        <button type="button"
                                                @click="deleteModalOpen = true; deleteActionUrl = '{{ route('admin.users.destroy', $user) }}'; deleteUserName = '{{ addslashes($user->name) }}'"
                                                class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded bg-rose-50 hover:bg-rose-100 text-rose-700 transition">
                                            {{ __('Delete') }}
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400 italic">
                                            {{ __('Cannot delete self') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-400">
                                    {{ __('No users found matching your search.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="p-4 border-t border-slate-200">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        <!-- Delete Confirmation Modal (Alpine.js) -->
        <div x-show="deleteModalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4"
             style="display: none;">
            <div @click.away="deleteModalOpen = false"
                 class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center space-x-3 text-rose-600 mb-4">
                    <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">{{ __('Confirm Delete User') }}</h3>
                </div>

                <p class="text-sm text-slate-600">
                    {{ __('Are you sure you want to delete user') }}
                    <span class="font-bold text-slate-900" x-text="deleteUserName"></span>?
                    {{ __('This action cannot be undone.') }}
                </p>

                <form method="POST" :action="deleteActionUrl" class="mt-6 flex justify-end space-x-3">
                    @csrf
                    @method('DELETE')
                    <button type="button"
                            @click="deleteModalOpen = false"
                            class="px-4 py-2 text-sm font-medium rounded-lg text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-rose-600 hover:bg-rose-700 transition">
                        {{ __('Delete User') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
