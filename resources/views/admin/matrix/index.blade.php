<x-admin-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('Permission Matrix') }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ __('Configure complete system access at a glance by toggling permissions across all roles.') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.matrix.update') }}" class="space-y-6">
            @csrf

            <!-- Sticky Top Save Bar -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 flex flex-col sm:flex-row items-center justify-between gap-3 sticky top-20 z-30">
                <div class="text-xs text-slate-600">
                    <span class="font-semibold text-slate-800">{{ __('Instructions:') }}</span>
                    {{ __('Toggle permissions for each role below and click Save Matrix to update assignments across the entire system.') }}
                </div>
                <x-primary-button type="submit">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ __('Save Matrix') }}
                </x-primary-button>
            </div>

            <!-- Matrix Grid Table -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-900 text-white text-xs uppercase font-semibold">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider sticky left-0 bg-slate-900 z-10 w-72">
                                    {{ __('Permissions / Roles') }}
                                </th>
                                @foreach ($roles as $role)
                                    <th scope="col" class="px-6 py-4 text-center font-bold tracking-wider">
                                        <div class="flex flex-col items-center">
                                            <span>{{ ucfirst($role->name) }}</span>
                                            @if ($role->name === 'admin')
                                                <span class="mt-1 px-1.5 py-0.2 rounded text-[10px] font-bold bg-red-600 text-white uppercase">
                                                    {{ __('Admin') }}
                                                </span>
                                            @endif
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach ($permissions as $permission)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-6 py-3.5 whitespace-nowrap font-mono text-xs font-semibold text-slate-900 sticky left-0 bg-white shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                                        <div class="flex items-center space-x-2">
                                            <span>{{ $permission->name }}</span>
                                            @if (in_array($permission->name, ['manage users', 'manage roles']))
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-amber-100 text-amber-800">
                                                    {{ __('Core') }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    @foreach ($roles as $role)
                                        @php
                                            $isChecked = in_array($permission->name, $rolePermissions[$role->name] ?? [], true);
                                            $isAdminProtected = $role->name === 'admin' && in_array($permission->name, ['manage users', 'manage roles']);
                                        @endphp
                                        <td class="px-6 py-3.5 whitespace-nowrap text-center">
                                            @if ($isAdminProtected)
                                                <!-- Always included for admin as per Rule 4 -->
                                                <input type="hidden" name="matrix[{{ $role->name }}][]" value="{{ $permission->name }}">
                                                <input type="checkbox"
                                                       checked
                                                       disabled
                                                       title="{{ __('Required by admin security rule') }}"
                                                       class="w-4 h-4 rounded border-slate-300 text-slate-400 bg-slate-100 cursor-not-allowed focus:ring-0">
                                            @else
                                                <input type="checkbox"
                                                       name="matrix[{{ $role->name }}][]"
                                                       value="{{ $permission->name }}"
                                                       {{ $isChecked ? 'checked' : '' }}
                                                       class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer transition">
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bottom Save Button -->
            <div class="flex justify-end pt-2">
                <x-primary-button type="submit">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ __('Save Matrix') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-admin-layout>
