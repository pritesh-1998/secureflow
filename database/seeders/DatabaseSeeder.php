<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $testUsers = [
            ['name' => 'Test Admin', 'email' => 'admin@example.com', 'role' => 'admin'],
            ['name' => 'Test Agent', 'email' => 'agent@example.com', 'role' => 'agent'],
            ['name' => 'Test User', 'email' => 'test@example.com', 'role' => 'user'],
        ];

        foreach ($testUsers as $testUser) {
            $user = User::firstOrCreate(
                ['email' => $testUser['email']],
                [
                    'name' => $testUser['name'],
                    'password' => 'password',
                ],
            );

            $user->syncRoles($testUser['role']);
        }
    }
}
