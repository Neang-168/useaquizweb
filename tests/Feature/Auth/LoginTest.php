<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_email_and_receive_a_token(): void
    {
        $user = User::create([
            'username' => 'jane.doe',
            'email' => 'jane@example.com',
            'password' => Hash::make('secret123'),
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'status' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'token',
                'user' => ['id', 'username', 'email'],
            ])
            ->assertJsonPath('user.email', $user->email);

        $this->assertNotEmpty($response->json('token'));
    }
}
