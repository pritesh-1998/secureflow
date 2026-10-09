<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRolePermissionsRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount(['users', 'permissions'])->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $role = Role::create(['name' => $validated['name']]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        AuditLogger::log(
            action: 'create_role',
            targetType: 'role',
            targetId: (string) $role->id,
            targetName: $role->name,
            oldValues: null,
            newValues: ['name' => $role->name]
        );

        return redirect()->route('admin.roles.index')->with('status', 'Role created successfully.');
    }

    public function show(Role $role): View
    {
        $assignedNames = $role->permissions()->pluck('name')->toArray();
        $allPermissions = Permission::orderBy('name')->get();

        $availablePermissions = $allPermissions->reject(fn ($p) => in_array($p->name, $assignedNames, true))->values();
        $assignedPermissions = $allPermissions->filter(fn ($p) => in_array($p->name, $assignedNames, true))->values();

        return view('admin.roles.show', compact('role', 'availablePermissions', 'assignedPermissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        // Rule 4: The 'admin' role is protected and cannot be renamed
        if ($role->name === 'admin') {
            abort(403, 'The admin role is protected and cannot be renamed.');
        }

        $validated = $request->validated();
        $oldName = $role->name;

        $role->update(['name' => $validated['name']]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        AuditLogger::log(
            action: 'update_role',
            targetType: 'role',
            targetId: (string) $role->id,
            targetName: $role->name,
            oldValues: ['name' => $oldName],
            newValues: ['name' => $validated['name']]
        );

        return redirect()->route('admin.roles.index')->with('status', 'Role renamed successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        // Rule 4: The 'admin' role is protected and cannot be deleted
        if ($role->name === 'admin') {
            abort(403, 'The admin role is protected and cannot be deleted.');
        }

        $roleId = $role->id;
        $roleName = $role->name;

        $role->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        AuditLogger::log(
            action: 'delete_role',
            targetType: 'role',
            targetId: (string) $roleId,
            targetName: $roleName,
            oldValues: ['name' => $roleName],
            newValues: null
        );

        return redirect()->route('admin.roles.index')->with('status', 'Role deleted successfully.');
    }

    public function updatePermissions(UpdateRolePermissionsRequest $request, Role $role): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $permissions = $validated['permissions'];

        // Rule 4: The 'admin' role cannot lose 'manage users' or 'manage roles'
        if ($role->name === 'admin') {
            if (! in_array('manage users', $permissions, true) || ! in_array('manage roles', $permissions, true)) {
                abort(403, "The 'admin' role cannot lose 'manage users' or 'manage roles' permissions.");
            }
        }

        $oldPermissions = $role->permissions()->pluck('name')->toArray();

        $role->syncPermissions($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        AuditLogger::log(
            action: 'update_role_permissions',
            targetType: 'role',
            targetId: (string) $role->id,
            targetName: $role->name,
            oldValues: ['permissions' => $oldPermissions],
            newValues: ['permissions' => $permissions]
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Permissions updated successfully.',
            ]);
        }

        return redirect()->route('admin.roles.show', $role)->with('status', 'Permissions updated successfully.');
    }
}
