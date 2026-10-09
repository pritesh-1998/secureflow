<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SecureFlow') }} - {{ __('Admin Management') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-100 text-slate-800" x-data="{ sidebarOpen: false, toast: { show: false, message: '', type: 'success' } }" @toast.window="toast.show = true; toast.message = $event.detail.message; toast.type = $event.detail.type || 'success'; setTimeout(() => toast.show = false, 4000)">
        <div class="min-h-screen flex flex-col">
            <!-- Dedicated Admin Header (Dark Slate/Indigo theme distinct from normal white header) -->
            <header class="bg-slate-900 border-b border-slate-800 text-white sticky top-0 z-40">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16 items-center">
                        <div class="flex items-center space-x-4">
                            <!-- Mobile menu button -->
                            <button @click="sidebarOpen = !sidebarOpen" class="md:hidden p-2 rounded-md text-slate-300 hover:text-white hover:bg-slate-800 focus:outline-none">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{'hidden': sidebarOpen, 'inline-flex': !sidebarOpen }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{'hidden': !sidebarOpen, 'inline-flex': sidebarOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3">
                                <span class="text-xl font-bold tracking-tight text-white">{{ __('Admin Dashboard') }}</span>
                            </a>

                            <!-- Clear Admin Badge -->
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-red-600 text-white shadow-sm ring-1 ring-red-400">
                                Admin
                            </span>
                        </div>

                        <!-- Right Header: User info & Dropdown -->
                        <div class="flex items-center space-x-6">
                            <div class="hidden sm:block text-right">
                                <div class="text-sm font-semibold text-white">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-slate-300">Role: {{ Auth::user()->getRoleNames()->implode(', ') }}</div>
                            </div>

                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="flex items-center text-sm font-medium text-slate-200 hover:text-white focus:outline-none transition">
                                        <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center font-bold text-white text-xs ring-2 ring-indigo-500">
                                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                        </div>
                                        <svg class="ms-1.5 h-4 w-4 fill-current text-slate-400" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <div class="px-4 py-2 border-b border-gray-100 text-xs text-gray-500 sm:hidden">
                                        <div class="font-medium text-gray-800">{{ Auth::user()->name }}</div>
                                        <div>Role: {{ Auth::user()->getRoleNames()->implode(', ') }}</div>
                                    </div>

                                    <x-dropdown-link :href="route('profile.edit')">
                                        {{ __('Profile') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link :href="route('dashboard')">
                                        {{ __('Standard Dashboard') }}
                                    </x-dropdown-link>

                                    <!-- Authentication -->
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                            {{ __('Log Out') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Body Layout with Sidebar -->
            <div class="flex-1 flex max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <!-- Sidebar -->
                <aside :class="{'block': sidebarOpen, 'hidden': !sidebarOpen}" class="hidden md:block w-64 shrink-0 pr-6 space-y-6">
                    <nav class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 space-y-1">
                        <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
                            {{ __('Management') }}
                        </div>

                        <a href="{{ route('admin.dashboard') }}"
                           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700 font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.dashboard') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            {{ __('Dashboard') }}
                        </a>

                        @can('manage users')
                        <a href="{{ route('admin.users.index') }}"
                           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition {{ request()->routeIs('admin.users.*') ? 'bg-indigo-50 text-indigo-700 font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.users.*') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            {{ __('Users') }}
                        </a>
                        @endcan

                        @can('manage roles')
                        <a href="{{ route('admin.roles.index') }}"
                           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition {{ request()->routeIs('admin.roles.*') ? 'bg-indigo-50 text-indigo-700 font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.roles.*') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            {{ __('Roles') }}
                        </a>

                        <a href="{{ route('admin.permissions.index') }}"
                           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition {{ request()->routeIs('admin.permissions.*') ? 'bg-indigo-50 text-indigo-700 font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.permissions.*') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            {{ __('Permissions') }}
                        </a>

                        <a href="{{ route('admin.matrix.index') }}"
                           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition {{ request()->routeIs('admin.matrix.*') ? 'bg-indigo-50 text-indigo-700 font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.matrix.*') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            {{ __('Permission Matrix') }}
                        </a>
                        @endcan
                    </nav>
                </aside>

                <!-- Main Content Area -->
                <main class="flex-1 min-w-0">
                    <!-- Session Status Alerts -->
                    @if (session('status'))
                        <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center shadow-sm">
                            <svg class="h-5 w-5 text-emerald-500 mr-2 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium flex items-center shadow-sm">
                            <svg class="h-5 w-5 text-rose-500 mr-2 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium shadow-sm">
                            <div class="font-semibold mb-1">{{ __('Please correct the following errors:') }}</div>
                            <ul class="list-disc pl-5 space-y-1 text-xs">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>

            <!-- Toast Container (for Drag & Drop & Async Actions) -->
            <div x-show="toast.show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform translate-y-4"
                 class="fixed bottom-6 right-6 z-50 max-w-sm w-full p-4 rounded-xl shadow-xl border flex items-center space-x-3"
                 :class="toast.type === 'success' ? 'bg-emerald-900 border-emerald-700 text-white' : 'bg-rose-900 border-rose-700 text-white'"
                 style="display: none;">
                <div class="shrink-0">
                    <template x-if="toast.type === 'success'">
                        <svg class="h-6 w-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </template>
                    <template x-if="toast.type !== 'success'">
                        <svg class="h-6 w-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </template>
                </div>
                <div class="text-sm font-medium" x-text="toast.message"></div>
                <button @click="toast.show = false" class="ml-auto text-slate-300 hover:text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </body>
</html>
