<x-app-layout>
    <x-slot name="header">
        <div class="rounded-lg bg-sky-700 px-6 py-5 text-white shadow">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('User Dashboard') }}
            </h2>
            <p class="mt-1 text-sm text-sky-100">{{ __('Your personal support workspace') }}</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium">{{ __('Welcome, :name', ['name' => auth()->user()->name]) }}</h3>
                    <p class="mt-2 text-sm text-gray-600">{{ __('View and manage your support tickets from here.') }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
