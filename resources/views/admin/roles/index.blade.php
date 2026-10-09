<x-admin-layout>
    <div class="space-y-6" x-data="{ createModalOpen: false, renameModalOpen: false, renameActionUrl: '', renameRoleName: '', deleteModalOpen: false, deleteActionUrl: '', deleteRoleName: '' }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('Roles Management') }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ __('Define roles, inspect member counts, and manage granular permissions.') }}</p>
            </div>
            <button type="button"
                    @click="createModalOpen = true"
                    class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Create Role') }}
            </button>
        </div>

        <!-- Roles Table -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold">
                        <tr>
                            <th scope="col" class="px-6 py-3.5">{{ __('Role Name') }}</th>
                            <th scope="col" class="px-6 py-3.5">{{ __('Users Count') }}</th>
                            <th scope="col" class="px-6 py-3.5">{{ __('Permissions Count') }}</th>
                            <th scope="col" class="px-6 py-3.5 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach ($roles as $role)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-bold text-slate-900">{{ ucfirst($role->name) }}</span>
                                        @if ($role->name === 'admin')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-red-100 text-red-800 border border-red-200">
                                                {{ __('Protected') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-400 font-mono mt-0.5">{{ $role->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-800">
                                        {{ $role->users_count }} {{ __('users') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                                        {{ $role->permissions_count }} {{ __('permissions') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                    <a href="{{ route('admin.roles.show', $role) }}"
                                       class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition">
                                        {{ __('Permissions & Detail') }} &rarr;
                                    </a>

                                    @if ($role->name !== 'admin')
                                        <button type="button"
                                                @click="renameModalOpen = true; renameActionUrl = '{{ route('admin.roles.update', $role) }}'; renameRoleName = '{{ addslashes($role->name) }}'"
                                                class="inline-flex items-center px-2.5 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                            {{ __('Rename') }}
                                        </button>

                                        <button type="button"
                                                @click="deleteModalOpen = true; deleteActionUrl = '{{ route('admin.roles.destroy', $role) }}'; deleteRoleName = '{{ addslashes($role->name) }}'"
                                                class="inline-flex items-center px-2.5 py-1.5 text-xs font-semibold rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition">
                                            {{ __('Delete') }}
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400 italic">
                                            {{ __('Protected System Role') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create Role Modal -->
        <div x-show="createModalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4"
             style="display: none;">
            <div @click.away="createModalOpen = false"
                 class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <h3 class="text-lg font-bold text-slate-900 mb-4">{{ __('Create New Role') }}</h3>
                <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="create_role_name" :value="__('Role Identifier / Name')" />
                        <x-text-input id="create_role_name" name="name" type="text" class="mt-1 block w-full" placeholder="e.g. auditor, manager" required />
                    </div>
                    <div class="flex justify-end space-x-3 pt-3">
                        <button type="button"
                                @click="createModalOpen = false"
                                class="px-4 py-2 text-sm font-medium rounded-lg text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                            {{ __('Cancel') }}
                        </button>
                        <x-primary-button>
                            {{ __('Create Role') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Rename Role Modal -->
        <div x-show="renameModalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4"
             style="display: none;">
            <div @click.away="renameModalOpen = false"
                 class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <h3 class="text-lg font-bold text-slate-900 mb-4">{{ __('Rename Role') }}</h3>
                <form method="POST" :action="renameActionUrl" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-input-label for="rename_role_name" :value="__('New Role Name')" />
                        <x-text-input id="rename_role_name" name="name" type="text" class="mt-1 block w-full" x-model="renameRoleName" required />
                    </div>
                    <div class="flex justify-end space-x-3 pt-3">
                        <button type="button"
                                @click="renameModalOpen = false"
                                class="px-4 py-2 text-sm font-medium rounded-lg text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                            {{ __('Cancel') }}
                        </button>
                        <x-primary-button>
                            {{ __('Save New Name') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Role Confirmation Modal -->
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
                    <h3 class="text-lg font-bold text-slate-900">{{ __('Confirm Delete Role') }}</h3>
                </div>

                <p class="text-sm text-slate-600">
                    {{ __('Are you sure you want to delete role') }}
                    <span class="font-bold text-slate-900" x-text="deleteRoleName"></span>?
                    {{ __('Users with this role will lose its permissions. This action cannot be undone.') }}
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
                        {{ __('Delete Role') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
