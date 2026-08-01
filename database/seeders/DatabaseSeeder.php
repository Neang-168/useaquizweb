<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $adminRole = Role::where('name', 'Admin')->first();

        User::factory()->create([
            'username' => 'super.admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'role_id' => $superAdminRole?->id,
        ]);

        User::factory()->create([
            'username' => 'admin.user',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'first_name' => 'Admin',
            'last_name' => 'User',
            'role_id' => $adminRole?->id,
        ]);
    }
}
