<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_require_authentication_and_the_admin_role(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));

        $this->seed();

        $user = User::factory()->create();
        $user->assignRole('user');

        $this->actingAs($user)->get('/admin')->assertForbidden();

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('Admin Dashboard')
            ->assertSee(route('admin.dashboard'))
            ->assertDontSee(route('agent.dashboard'))
            ->assertSee('Role: admin');
    }

    public function test_agent_and_user_dashboards_render_their_own_headings(): void
    {
        $this->seed();

        $agent = User::factory()->create();
        $agent->assignRole('agent');

        $this->actingAs($agent)->get('/agent')
            ->assertOk()
            ->assertSee('Agent Dashboard')
            ->assertSee(route('agent.dashboard'))
            ->assertDontSee(route('admin.dashboard'))
            ->assertSee('Role: agent');

        $user = User::factory()->create();
        $user->assignRole('user');

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('User Dashboard')
            ->assertSee(route('dashboard'))
            ->assertDontSee(route('admin.dashboard'))
            ->assertDontSee(route('agent.dashboard'))
            ->assertSee('Role: user');
    }

    public function test_database_seeder_assigns_roles_to_test_users(): void
    {
        $this->seed();

        $this->assertTrue(User::where('email', 'admin@example.com')->firstOrFail()->hasRole('admin'));
        $this->assertTrue(User::where('email', 'agent@example.com')->firstOrFail()->hasRole('agent'));
        $this->assertTrue(User::where('email', 'test@example.com')->firstOrFail()->hasRole('user'));
    }
}
