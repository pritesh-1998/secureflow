<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles/permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create permissions
        $permissions = [
            'manage users',
            'manage roles',
            'view all tickets',
            'manage tickets',
            'create tickets',
            'view own tickets',
            'comment on tickets',
            'delete tickets',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // 2. Create roles and assign permissions

        // Admin - gets everything
        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        $admin->syncPermissions(Permission::where('guard_name', 'web')->get());

        // Agent - support staff
        $agent = Role::firstOrCreate([
            'name' => 'agent',
            'guard_name' => 'web',
        ]);
        $agent->syncPermissions([
            'view all tickets',
            'manage tickets',
            'comment on tickets',
            'view own tickets',
        ]);

        // User - regular customer
        $user = Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'web',
        ]);
        $user->syncPermissions([
            'create tickets',
            'view own tickets',
            'comment on tickets',
        ]);
    }
}
