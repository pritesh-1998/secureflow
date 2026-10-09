<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UsersController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $query = User::with('roles')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(10)->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles', 'search'));
    }

    public function create(): View
    {
        $roles = Role::all();

        return view('admin.users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->assignRole($validated['role']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        AuditLogger::log(
            action: 'create_user',
            targetType: 'user',
            targetId: (string) $user->id,
            targetName: $user->name,
            oldValues: null,
            newValues: [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $validated['role'],
            ]
        );

        return redirect()->route('admin.users.index')->with('status', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $roles = Role::all();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        // Rule 5: An admin cannot remove their own admin role
        if ($user->id === auth()->id() && $user->hasRole('admin') && $validated['role'] !== 'admin') {
            abort(403, 'An admin cannot remove their own admin role.');
        }

        // Rule 6: System must always have at least one admin
        if ($user->hasRole('admin') && $validated['role'] !== 'admin' && User::role('admin')->count() <= 1) {
            abort(403, 'Cannot remove the last remaining admin in the system.');
        }

        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->pluck('name')->first(),
        ];

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $user->syncRoles([$validated['role']]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $newValues = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $validated['role'],
        ];

        AuditLogger::log(
            action: 'update_user',
            targetType: 'user',
            targetId: (string) $user->id,
            targetName: $user->name,
            oldValues: $oldValues,
            newValues: $newValues
        );

        return redirect()->route('admin.users.index')->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Rule 5: An admin cannot delete their own account
        if ($user->id === auth()->id()) {
            abort(403, 'An admin cannot delete their own account.');
        }

        // Rule 6: System must always have at least one admin
        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            abort(403, 'Cannot delete the last remaining admin in the system.');
        }

        $userId = $user->id;
        $userName = $user->name;
        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name')->toArray(),
        ];

        $user->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        AuditLogger::log(
            action: 'delete_user',
            targetType: 'user',
            targetId: (string) $userId,
            targetName: $userName,
            oldValues: $oldValues,
            newValues: null
        );

        return redirect()->route('admin.users.index')->with('status', 'User deleted successfully.');
    }
}
