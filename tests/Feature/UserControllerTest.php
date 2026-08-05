<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_and_create_users(): void
    {
        $permission = Permission::create(['name' => 'manage_users', 'module' => 'users']);
        $role = Role::create(['name' => 'Admin', 'description' => 'Administrator']);
        $role->permissions()->sync([$permission->id]);

        $admin = User::factory()->create([
            'username' => 'admin.user',
            'email' => 'admin@example.com',
            'role_id' => $role->id,
            'status' => true,
        ]);

        $this->actingAs($admin, 'sanctum');

        $listResponse = $this->getJson('/api/users');

        $listResponse->assertOk()
            ->assertJsonStructure([
                'data',
                'current_page',
                'per_page',
                'total',
            ]);

        $createResponse = $this->postJson('/api/users', [
            'username' => 'new.user',
            'email' => 'new@example.com',
            'password' => 'secret123',
            'first_name' => 'New',
            'last_name' => 'User',
            'role_id' => $role->id,
            'status' => true,
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('user.email', 'new@example.com');

        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'username' => 'new.user',
        ]);

        $this->assertTrue(Hash::check('secret123', User::where('email', 'new@example.com')->first()->password));
    }
}
