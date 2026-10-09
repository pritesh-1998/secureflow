<x-admin-layout>
    <div class="space-y-6" x-data="rolePermissionsManager()">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center space-x-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                        {{ __('Role: ') }} <span class="capitalize">{{ $role->name }}</span>
                    </h1>
                    @if ($role->name === 'admin')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                            {{ __('Protected System Role') }}
                        </span>
                    @endif
                </div>
                <p class="text-sm text-slate-500 mt-1">
                    {{ __('Drag and drop permissions between columns to assign or unassign them in real time.') }}
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.roles.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
                    &larr; {{ __('Back to Roles') }}
                </a>
            </div>
        </div>

        @if ($role->name === 'admin')
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center shadow-sm">
                <svg class="h-5 w-5 text-amber-600 mr-2 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <span>{{ __('Security Rule: The admin role is protected and must always retain \'manage users\' and \'manage roles\' permissions.') }}</span>
            </div>
        @endif

        <!-- Fallback Form and Plain Save Button -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-xs text-slate-500 flex items-center">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2 inline-block animate-pulse"></span>
                {{ __('Auto-saves instantly on drag and drop. Or click Save to submit via form.') }}
            </div>

            <!-- Plain Save Button Fallback -->
            <form id="fallback-permission-form" method="POST" action="{{ route('admin.roles.permissions.update', $role) }}" class="flex items-center">
                @csrf
                @method('PATCH')
                <div id="fallback-hidden-inputs">
                    @foreach ($assignedPermissions as $assigned)
                        <input type="hidden" name="permissions[]" value="{{ $assigned->name }}">
                    @endforeach
                </div>
                <x-primary-button type="submit">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    {{ __('Save (Fallback Form)') }}
                </x-primary-button>
            </form>
        </div>

        <!-- Drag and Drop Two-Column Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Column 1: Available Permissions -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">{{ __('Available Permissions') }}</h2>
                        <p class="text-xs text-slate-400 mt-0.5">{{ __('Not assigned to this role') }}</p>
                    </div>
                    <span id="available-count" class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-200 text-slate-700">
                        {{ $availablePermissions->count() }}
                    </span>
                </div>

                <div id="available-permissions" class="p-4 space-y-2 flex-1 min-h-[320px] bg-slate-50/50">
                    @foreach ($availablePermissions as $permission)
                        <div data-permission="{{ $permission->name }}"
                             class="permission-item flex items-center justify-between p-3.5 bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow transition cursor-grab active:cursor-grabbing hover:border-indigo-300">
                            <div class="flex items-center space-x-3">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                <span class="text-sm font-medium text-slate-800 font-mono">{{ $permission->name }}</span>
                            </div>
                            <span class="text-[11px] text-slate-400">&larr; {{ __('Drag to Assign') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Column 2: Assigned Permissions -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                <div class="px-5 py-4 bg-indigo-50/60 border-b border-indigo-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-indigo-900">{{ __('Assigned Permissions') }}</h2>
                        <p class="text-xs text-indigo-600/80 mt-0.5">{{ __('Currently active for this role') }}</p>
                    </div>
                    <span id="assigned-count" class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                        {{ $assignedPermissions->count() }}
                    </span>
                </div>

                <div id="assigned-permissions" class="p-4 space-y-2 flex-1 min-h-[320px] bg-indigo-50/20">
                    @foreach ($assignedPermissions as $permission)
                        <div data-permission="{{ $permission->name }}"
                             class="permission-item flex items-center justify-between p-3.5 bg-white rounded-xl border border-indigo-200 shadow-sm hover:shadow transition cursor-grab active:cursor-grabbing">
                            <div class="flex items-center space-x-3">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                <span class="text-sm font-medium text-slate-900 font-mono">{{ $permission->name }}</span>
                                @if ($role->name === 'admin' && in_array($permission->name, ['manage users', 'manage roles']))
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800">
                                        {{ __('Required') }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-[11px] text-indigo-600 font-medium">{{ __('Assigned') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Drag and Drop SortableJS Initialization Script -->
    <script>
        function rolePermissionsManager() {
            return {
                init() {
                    const availableEl = document.getElementById('available-permissions');
                    const assignedEl = document.getElementById('assigned-permissions');

                    if (!window.Sortable || !availableEl || !assignedEl) {
                        return;
                    }

                    const updateSortableState = async () => {
                        const assignedItems = Array.from(assignedEl.querySelectorAll('[data-permission]'));
                        const availableItems = Array.from(availableEl.querySelectorAll('[data-permission]'));

                        // Update counters
                        document.getElementById('assigned-count').textContent = assignedItems.length;
                        document.getElementById('available-count').textContent = availableItems.length;

                        // Synchronize fallback hidden inputs
                        const hiddenContainer = document.getElementById('fallback-hidden-inputs');
                        hiddenContainer.innerHTML = '';
                        const permissionNames = assignedItems.map(el => el.getAttribute('data-permission'));
                        permissionNames.forEach(name => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'permissions[]';
                            input.value = name;
                            hiddenContainer.appendChild(input);
                        });

                        // Send async PATCH request with CSRF token
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        try {
                            const res = await fetch("{{ route('admin.roles.permissions.update', $role) }}", {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                                body: JSON.stringify({ permissions: permissionNames })
                            });

                            const data = await res.json();
                            if (res.ok) {
                                window.dispatchEvent(new CustomEvent('toast', {
                                    detail: { message: data.message || 'Permissions updated successfully.', type: 'success' }
                                }));
                            } else {
                                window.dispatchEvent(new CustomEvent('toast', {
                                    detail: { message: data.message || 'Failed to update permissions.', type: 'error' }
                                }));
                            }
                        } catch (err) {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { message: 'Network error occurred while saving permissions.', type: 'error' }
                            }));
                        }
                    };

                    new window.Sortable(availableEl, {
                        group: 'role-permissions',
                        animation: 150,
                        ghostClass: 'opacity-40',
                        onAdd: updateSortableState,
                        onRemove: updateSortableState,
                    });

                    new window.Sortable(assignedEl, {
                        group: 'role-permissions',
                        animation: 150,
                        ghostClass: 'opacity-40',
                        onAdd: updateSortableState,
                        onRemove: updateSortableState,
                    });
                }
            };
        }
    </script>
</x-admin-layout>
