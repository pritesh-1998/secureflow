<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePermissionMatrixRequest;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionMatrixController extends Controller
{
    public function index(): View
    {
        $roles = Role::orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        $rolePermissions = [];
        foreach ($roles as $role) {
            $rolePermissions[$role->name] = $role->permissions()->pluck('name')->toArray();
        }

        return view('admin.matrix.index', compact('roles', 'permissions', 'rolePermissions'));
    }

    public function update(UpdatePermissionMatrixRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $matrix = $validated['matrix'] ?? [];

        // Rule 4: Admin role must retain 'manage users' and 'manage roles'
        $adminPerms = $matrix['admin'] ?? [];
        if (! in_array('manage users', $adminPerms, true) || ! in_array('manage roles', $adminPerms, true)) {
            abort(403, "The 'admin' role cannot lose 'manage users' or 'manage roles' permissions.");
        }

        $allRoles = Role::all();
        $oldMatrix = [];

        foreach ($allRoles as $role) {
            $oldMatrix[$role->name] = $role->permissions()->pluck('name')->toArray();
            $newPermissions = $matrix[$role->name] ?? [];
            $role->syncPermissions($newPermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        AuditLogger::log(
            action: 'update_permission_matrix',
            targetType: 'matrix',
            targetId: null,
            targetName: 'Permission Matrix',
            oldValues: $oldMatrix,
            newValues: $matrix
        );

        return redirect()->route('admin.matrix.index')->with('status', 'Permission matrix updated successfully.');
    }
}
