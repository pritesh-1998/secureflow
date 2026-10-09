<x-app-layout>
    <x-slot name="header">
        <div class="rounded-lg bg-emerald-700 px-6 py-5 text-white shadow">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('Agent Dashboard') }}
            </h2>
            <p class="mt-1 text-sm text-emerald-100">{{ __('Your support ticket workspace') }}</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium">{{ __('Welcome, :name', ['name' => auth()->user()->name]) }}</h3>
                    <p class="mt-2 text-sm text-gray-600">{{ __('Review assigned tickets and help customers get the support they need.') }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
