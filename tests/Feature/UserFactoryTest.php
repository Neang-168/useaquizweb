<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_can_create_a_user_with_the_current_schema(): void
    {
        $user = User::factory()->create([
            'username' => 'factory.user',
            'email' => 'factory@example.com',
            'first_name' => 'Factory',
            'last_name' => 'User',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'factory@example.com',
        ]);
    }
}
