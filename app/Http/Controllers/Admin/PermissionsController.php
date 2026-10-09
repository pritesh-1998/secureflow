<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionsController extends Controller
{
    public function index(): View
    {
        $permissions = Permission::with('roles')->orderBy('name')->get();

        return view('admin.permissions.index', compact('permissions'));
    }

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $permission = Permission::create(['name' => $validated['name']]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        AuditLogger::log(
            action: 'create_permission',
            targetType: 'permission',
            targetId: (string) $permission->id,
            targetName: $permission->name,
            oldValues: null,
            newValues: ['name' => $permission->name]
        );

        return redirect()->route('admin.permissions.index')->with('status', 'Permission created successfully.');
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        if (in_array($permission->name, ['manage users', 'manage roles'], true)) {
            abort(403, 'Protected permissions cannot be renamed.');
        }

        $validated = $request->validated();
        $oldName = $permission->name;

        $permission->update(['name' => $validated['name']]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        AuditLogger::log(
            action: 'update_permission',
            targetType: 'permission',
            targetId: (string) $permission->id,
            targetName: $permission->name,
            oldValues: ['name' => $oldName],
            newValues: ['name' => $validated['name']]
        );

        return redirect()->route('admin.permissions.index')->with('status', 'Permission renamed successfully.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        if (in_array($permission->name, ['manage users', 'manage roles'], true)) {
            abort(403, "The 'manage users' and 'manage roles' permissions are protected and cannot be deleted.");
        }

        $permissionId = $permission->id;
        $permissionName = $permission->name;

        $permission->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        AuditLogger::log(
            action: 'delete_permission',
            targetType: 'permission',
            targetId: (string) $permissionId,
            targetName: $permissionName,
            oldValues: ['name' => $permissionName],
            newValues: null
        );

        return redirect()->route('admin.permissions.index')->with('status', 'Permission deleted successfully.');
    }
}
