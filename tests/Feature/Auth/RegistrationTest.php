<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        // 1. Create roles first, the test database is empty
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        // 2. Register
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // 3. Check the result
        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->hasRole('user'));
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
