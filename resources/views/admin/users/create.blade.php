<x-admin-layout>
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('Create New User') }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ __('Add a user account and assign their system role.') }}</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
                &larr; {{ __('Back to Users') }}
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
                @csrf

                <!-- Name -->
                <div>
                    <x-input-label for="name" :value="__('Full Name')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Email -->
                <div>
                    <x-input-label for="email" :value="__('Email Address')" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <x-input-label for="password" :value="__('Temporary Password')" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    <p class="text-xs text-slate-500 mt-1">{{ __('Minimum 8 characters. Passwords are automatically hashed and never stored in plain text.') }}</p>
                </div>

                <!-- Role -->
                <div>
                    <x-input-label for="role" :value="__('Assigned Role')" />
                    <select id="role" name="role" class="mt-1 block w-full border-slate-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                        <option value="" disabled {{ old('role') ? '' : 'selected' }}>{{ __('Select a role') }}</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.users.index') }}"
                       class="px-4 py-2 text-sm font-medium rounded-lg text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                        {{ __('Cancel') }}
                    </a>
                    <x-primary-button>
                        {{ __('Create User') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
