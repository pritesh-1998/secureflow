<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $agent;

    protected User $normalUser;

    protected Role $adminRole;

    protected Role $agentRole;

    protected Role $userRole;

    protected Permission $manageUsers;

    protected Permission $manageRoles;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Seed roles
        $this->adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->agentRole = Role::firstOrCreate(['name' => 'agent', 'guard_name' => 'web']);
        $this->userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Seed permissions
        $this->manageUsers = Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'web']);
        $this->manageRoles = Permission::firstOrCreate(['name' => 'manage roles', 'guard_name' => 'web']);
        $otherPermissions = [
            'view all tickets',
            'manage tickets',
            'create tickets',
            'view own tickets',
            'comment on tickets',
            'delete tickets',
        ];
        foreach ($otherPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Assign core permissions to admin role
        $this->adminRole->givePermissionTo([$this->manageUsers, $this->manageRoles]);

        // Create test users
        $this->admin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
        ]);
        $this->admin->assignRole('admin');

        $this->agent = User::factory()->create([
            'name' => 'Support Agent',
            'email' => 'agentuser@example.com',
        ]);
        $this->agent->assignRole('agent');

        $this->normalUser = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'regularuser@example.com',
        ]);
        $this->normalUser->assignRole('user');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Helper to retrieve all registered /admin routes with parameter substitution.
     */
    protected function getAdminRoutesWithMethods(): array
    {
        $dummyUser = User::factory()->create();
        $dummyUser->assignRole('user');
        $dummyRole = Role::firstOrCreate(['name' => 'test-role', 'guard_name' => 'web']);
        $dummyPermission = Permission::firstOrCreate(['name' => 'test-permission', 'guard_name' => 'web']);

        $routes = [];

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            if ($uri === 'admin' || str_starts_with($uri, 'admin/')) {
                // Replace parameters
                $resolvedUri = str_replace(
                    ['{user}', '{role}', '{permission}'],
                    [$dummyUser->id, $dummyRole->id, $dummyPermission->id],
                    $uri
                );

                foreach ($route->methods() as $method) {
                    if (in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
                        $routes[] = [
                            'method' => $method,
                            'uri' => '/'.$resolvedUri,
                            'name' => $route->getName(),
                        ];
                    }
                }
            }
        }

        return $routes;
    }

    public function test_guests_are_redirected_to_login_on_every_admin_route(): void
    {
        $adminRoutes = $this->getAdminRoutesWithMethods();
        $this->assertNotEmpty($adminRoutes);

        foreach ($adminRoutes as $routeInfo) {
            $method = $routeInfo['method'];
            $uri = $routeInfo['uri'];

            $response = $this->call($method, $uri);
            $response->assertRedirect(route('login'));
        }
    }

    public function test_users_with_role_agent_get_403_on_every_admin_route(): void
    {
        $adminRoutes = $this->getAdminRoutesWithMethods();
        $this->assertNotEmpty($adminRoutes);

        foreach ($adminRoutes as $routeInfo) {
            $method = $routeInfo['method'];
            $uri = $routeInfo['uri'];

            $response = $this->actingAs($this->agent)->call($method, $uri);
            $this->assertEquals(
                403,
                $response->getStatusCode(),
                "Agent did not receive 403 on [{$method}] {$uri}"
            );
        }
    }

    public function test_users_with_role_user_get_403_on_every_admin_route(): void
    {
        $adminRoutes = $this->getAdminRoutesWithMethods();
        $this->assertNotEmpty($adminRoutes);

        foreach ($adminRoutes as $routeInfo) {
            $method = $routeInfo['method'];
            $uri = $routeInfo['uri'];

            $response = $this->actingAs($this->normalUser)->call($method, $uri);
            $this->assertEquals(
                403,
                $response->getStatusCode(),
                "User did not receive 403 on [{$method}] {$uri}"
            );
        }
    }

    public function test_admin_can_access_dashboard_and_see_metrics(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Admin Dashboard');
        $response->assertSee('Total Users');
        $response->assertSee('Total Roles');
        $response->assertSee('Total Permissions');
        $response->assertSee('Role: admin');
    }

    public function test_admin_can_perform_user_crud_operations(): void
    {
        // 1. List users & search
        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['search' => 'Super Admin']));
        $response->assertOk();
        $response->assertSee('Super Admin');

        // 2. Render create form
        $this->actingAs($this->admin)->get(route('admin.users.create'))->assertOk();

        // 3. Create user
        $storeResponse = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Alice Admin',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'role' => 'agent',
        ]);
        $storeResponse->assertRedirect(route('admin.users.index'));
        $createdUser = User::where('email', 'alice@example.com')->first();
        $this->assertNotNull($createdUser);
        $this->assertTrue($createdUser->hasRole('agent'));

        // 4. Render edit form
        $this->actingAs($this->admin)->get(route('admin.users.edit', $createdUser))->assertOk();

        // 5. Update user
        $updateResponse = $this->actingAs($this->admin)->put(route('admin.users.update', $createdUser), [
            'name' => 'Alice Updated',
            'email' => 'alice.updated@example.com',
            'role' => 'user',
        ]);
        $updateResponse->assertRedirect(route('admin.users.index'));
        $createdUser->refresh();
        $this->assertEquals('Alice Updated', $createdUser->name);
        $this->assertTrue($createdUser->hasRole('user'));

        // 6. Delete user
        $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $createdUser));
        $deleteResponse->assertRedirect(route('admin.users.index'));
        $this->assertNull(User::find($createdUser->id));
    }

    public function test_admin_can_perform_role_and_permission_crud_operations(): void
    {
        // 1. Create role
        $storeRole = $this->actingAs($this->admin)->post(route('admin.roles.store'), [
            'name' => 'moderator',
        ]);
        $storeRole->assertRedirect(route('admin.roles.index'));
        $role = Role::findByName('moderator');
        $this->assertNotNull($role);

        // 2. View role detail
        $this->actingAs($this->admin)->get(route('admin.roles.show', $role))->assertOk();

        // 3. Rename role
        $renameResponse = $this->actingAs($this->admin)->put(route('admin.roles.update', $role), [
            'name' => 'senior-moderator',
        ]);
        $renameResponse->assertRedirect(route('admin.roles.index'));
        $this->assertTrue(Role::where('name', 'senior-moderator')->exists());
        $role = Role::findByName('senior-moderator');

        // 4. Update role permissions via Drag-and-drop PATCH JSON request
        $patchResponse = $this->actingAs($this->admin)->patchJson(route('admin.roles.permissions.update', $role), [
            'permissions' => ['create tickets', 'view own tickets'],
        ]);
        $patchResponse->assertOk()->assertJson(['status' => 'success']);
        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('create tickets'));
        $this->assertTrue($role->hasPermissionTo('view own tickets'));

        // 5. Delete role
        $deleteRole = $this->actingAs($this->admin)->delete(route('admin.roles.destroy', $role));
        $deleteRole->assertRedirect(route('admin.roles.index'));
        $this->assertFalse(Role::where('name', 'senior-moderator')->exists());

        // 6. Permission CRUD
        $createPerm = $this->actingAs($this->admin)->post(route('admin.permissions.store'), [
            'name' => 'export data',
        ]);
        $createPerm->assertRedirect(route('admin.permissions.index'));
        $permission = Permission::findByName('export data');
        $this->assertNotNull($permission);

        $renamePerm = $this->actingAs($this->admin)->put(route('admin.permissions.update', $permission), [
            'name' => 'export raw data',
        ]);
        $renamePerm->assertRedirect(route('admin.permissions.index'));
        $this->assertTrue(Permission::where('name', 'export raw data')->exists());
        $permission = Permission::findByName('export raw data');

        $deletePerm = $this->actingAs($this->admin)->delete(route('admin.permissions.destroy', $permission));
        $deletePerm->assertRedirect(route('admin.permissions.index'));
        $this->assertFalse(Permission::where('name', 'export raw data')->exists());
    }

    public function test_admin_can_update_permission_matrix(): void
    {
        $this->actingAs($this->admin)->get(route('admin.matrix.index'))->assertOk();

        $matrixPayload = [
            'matrix' => [
                'admin' => ['manage users', 'manage roles', 'view all tickets'],
                'agent' => ['create tickets', 'view own tickets'],
                'user' => ['view own tickets'],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.matrix.update'), $matrixPayload);
        $response->assertRedirect(route('admin.matrix.index'));

        $this->adminRole->refresh();
        $this->assertTrue($this->adminRole->hasPermissionTo('view all tickets'));

        $this->agentRole->refresh();
        $this->assertTrue($this->agentRole->hasPermissionTo('create tickets'));
        $this->assertFalse($this->agentRole->hasPermissionTo('manage users'));
    }

    public function test_security_rule_4_admin_role_cannot_be_renamed_or_deleted(): void
    {
        // Renaming admin role must be forbidden (403)
        $renameResponse = $this->actingAs($this->admin)->put(route('admin.roles.update', $this->adminRole), [
            'name' => 'super-admin-renamed',
        ]);
        $renameResponse->assertForbidden();
        $this->assertTrue(Role::where('name', 'admin')->exists());

        // Deleting admin role must be forbidden (403)
        $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.roles.destroy', $this->adminRole));
        $deleteResponse->assertForbidden();
        $this->assertTrue(Role::where('name', 'admin')->exists());
    }

    public function test_security_rule_4_admin_role_cannot_lose_manage_users_or_manage_roles(): void
    {
        // Attempt to remove manage users from admin role via PATCH
        $patchResponse = $this->actingAs($this->admin)->patchJson(route('admin.roles.permissions.update', $this->adminRole), [
            'permissions' => ['manage roles'], // missing 'manage users'
        ]);
        $patchResponse->assertForbidden();

        // Attempt to remove manage roles from admin role via PATCH
        $patchResponse2 = $this->actingAs($this->admin)->patchJson(route('admin.roles.permissions.update', $this->adminRole), [
            'permissions' => ['manage users'], // missing 'manage roles'
        ]);
        $patchResponse2->assertForbidden();

        // Attempt to remove manage users or manage roles via matrix
        $matrixResponse = $this->actingAs($this->admin)->post(route('admin.matrix.update'), [
            'matrix' => [
                'admin' => ['view all tickets'], // missing manage users & manage roles
            ],
        ]);
        $matrixResponse->assertForbidden();

        // Core permissions cannot be deleted
        $deleteManageUsers = $this->actingAs($this->admin)->delete(route('admin.permissions.destroy', $this->manageUsers));
        $deleteManageUsers->assertForbidden();
        $this->assertTrue(Permission::where('name', 'manage users')->exists());

        $deleteManageRoles = $this->actingAs($this->admin)->delete(route('admin.permissions.destroy', $this->manageRoles));
        $deleteManageRoles->assertForbidden();
        $this->assertTrue(Permission::where('name', 'manage roles')->exists());
    }

    public function test_security_rule_5_admin_cannot_remove_own_admin_role_or_delete_own_account(): void
    {
        // Create second admin so rule 6 doesn't interfere
        $secondAdmin = User::factory()->create(['email' => 'secondadmin@example.com']);
        $secondAdmin->assignRole('admin');

        // Admin attempting to remove their own admin role
        $updateResponse = $this->actingAs($this->admin)->put(route('admin.users.update', $this->admin), [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'role' => 'user',
        ]);
        $updateResponse->assertForbidden();
        $this->admin->refresh();
        $this->assertTrue($this->admin->hasRole('admin'));

        // Admin attempting to delete own account
        $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));
        $deleteResponse->assertForbidden();
        $this->assertNotNull(User::find($this->admin->id));
    }

    public function test_security_rule_6_system_blocks_removing_the_last_admin(): void
    {
        // Currently $this->admin is the only admin
        $this->assertEquals(1, User::role('admin')->count());

        // Create another user
        $anotherAdmin = User::factory()->create();
        $anotherAdmin->assignRole('admin');
        $this->assertEquals(2, User::role('admin')->count());

        // Now delete one admin (not self)
        $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $anotherAdmin));
        $deleteResponse->assertRedirect(route('admin.users.index'));
        $this->assertEquals(1, User::role('admin')->count());

        // Now attempt to change the remaining admin's role
        $demoteResponse = $this->actingAs($this->admin)->put(route('admin.users.update', $this->admin), [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'role' => 'agent',
        ]);
        $demoteResponse->assertForbidden();
        $this->assertEquals(1, User::role('admin')->count());
    }

    public function test_invalid_role_or_permission_names_are_rejected(): void
    {
        // 1. Invalid role on user creation
        $invalidRoleStore = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Bad Role User',
            'email' => 'badrole@example.com',
            'password' => 'secret123',
            'role' => 'super-god-mode', // non-existent role
        ]);
        $invalidRoleStore->assertSessionHasErrors(['role']);

        // 2. Invalid role on user update
        $invalidRoleUpdate = $this->actingAs($this->admin)->put(route('admin.users.update', $this->normalUser), [
            'name' => $this->normalUser->name,
            'email' => $this->normalUser->email,
            'role' => 'nonexistent_role',
        ]);
        $invalidRoleUpdate->assertSessionHasErrors(['role']);

        // 3. Invalid permission name in drag and drop / role update
        $invalidPermPatch = $this->actingAs($this->admin)->patchJson(route('admin.roles.permissions.update', $this->agentRole), [
            'permissions' => ['not_a_real_permission'],
        ]);
        $invalidPermPatch->assertStatus(422)->assertJsonValidationErrors(['permissions.0']);

        // 4. Invalid permission name in matrix update
        $invalidMatrix = $this->actingAs($this->admin)->post(route('admin.matrix.update'), [
            'matrix' => [
                'agent' => ['fake_permission_xyz'],
            ],
        ]);
        $invalidMatrix->assertSessionHasErrors(['matrix.agent']);
    }

    public function test_audit_logs_record_admin_actions_and_forbidden_attempts(): void
    {
        // Clean log file or note current size
        $logPath = storage_path('logs/security.log');

        // Perform an admin action
        $this->actingAs($this->admin)->post(route('admin.roles.store'), [
            'name' => 'audited-role',
        ]);

        // Assert recorded in database table
        $this->assertDatabaseHas('admin_audit_logs', [
            'action' => 'create_role',
            'target_type' => 'role',
            'user_email' => $this->admin->email,
        ]);

        // Assert recorded in storage/logs/security.log
        $this->assertTrue(File::exists($logPath));
        $logContent = File::get($logPath);
        $this->assertStringContainsString('Admin action: create_role', $logContent);
        $this->assertStringContainsString($this->admin->email, $logContent);

        // Trigger a 403 attempt on /admin by non-admin
        $this->actingAs($this->normalUser)->get(route('admin.dashboard'));
        $logContent = File::get($logPath);
        $this->assertStringContainsString('Forbidden access attempt on admin route', $logContent);
    }
}
