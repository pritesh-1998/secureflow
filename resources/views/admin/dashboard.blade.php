<x-admin-layout>
    <div class="space-y-6">
        <!-- Page Title -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('Admin Dashboard') }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ __('Overview of system users, security roles, permissions, and recent audit activity.') }}</p>
            </div>
            <div class="text-xs text-slate-400">
                {{ __('Updated: ') }}{{ now()->format('M d, Y H:i') }}
            </div>
        </div>

        <!-- Metric Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Total Users Card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Total Users') }}</div>
                    <div class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ $totalUsers }}</div>
                </div>
            </div>

            <!-- Users Per Role Card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">{{ __('Users per Role') }}</div>
                <div class="flex flex-wrap gap-1.5">
                    @forelse ($usersPerRole as $roleItem)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800 border border-slate-200">
                            <span class="font-semibold mr-1">{{ $roleItem->name }}:</span> {{ $roleItem->users_count }}
                        </span>
                    @empty
                        <span class="text-xs text-slate-400">{{ __('No roles configured') }}</span>
                    @endforelse
                </div>
            </div>

            <!-- Total Roles Card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Total Roles') }}</div>
                    <div class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ $totalRoles }}</div>
                </div>
            </div>

            <!-- Total Permissions Card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Total Permissions') }}</div>
                    <div class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ $totalPermissions }}</div>
                </div>
            </div>
        </div>

        <!-- Recent Audit Activity Table -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">{{ __('Recent Admin Actions') }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('Displaying the 10 most recent actions recorded in the audit log.') }}</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                    {{ __('Audit Log') }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold">
                        <tr>
                            <th scope="col" class="px-6 py-3.5">{{ __('Timestamp') }}</th>
                            <th scope="col" class="px-6 py-3.5">{{ __('Admin') }}</th>
                            <th scope="col" class="px-6 py-3.5">{{ __('Action') }}</th>
                            <th scope="col" class="px-6 py-3.5">{{ __('Target') }}</th>
                            <th scope="col" class="px-6 py-3.5">{{ __('Details') }}</th>
                            <th scope="col" class="px-6 py-3.5">{{ __('IP Address') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($recentLogs as $log)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 font-mono">
                                    {{ $log->created_at->format('Y-m-d H:i:s') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-slate-900 text-xs">{{ $log->user_email ?? __('System / Unknown') }}</div>
                                    @if ($log->user_id)
                                        <div class="text-[11px] text-slate-400">ID: {{ $log->user_id }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        {{ str_replace('_', ' ', $log->action) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    <span class="font-medium text-slate-900 capitalize">{{ $log->target_type }}</span>
                                    @if ($log->target_name)
                                        <span class="text-slate-500">: {{ $log->target_name }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs font-mono text-slate-600 max-w-xs truncate">
                                    @if ($log->new_values)
                                        <span title="{{ json_encode($log->new_values) }}">{{ json_encode($log->new_values) }}</span>
                                    @elseif ($log->old_values)
                                        <span title="{{ json_encode($log->old_values) }}">{{ json_encode($log->old_values) }}</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-slate-500">
                                    {{ $log->ip_address ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-400">
                                    {{ __('No audit log actions recorded yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
