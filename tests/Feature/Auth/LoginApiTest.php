<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * @priority_high — maps to tests/Bdd/auth_login.feature
 */
class LoginApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_verified_user_correct_credentials_returns_token(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('Password1'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'login@example.com',
            'password' => 'Password1',
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Login successful',
            ])
            ->assertJsonPath('data.user.email', 'login@example.com');

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_login_wrong_password_returns_401(): void
    {
        User::factory()->create([
            'email' => 'badpass@example.com',
            'password' => Hash::make('Password1'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'badpass@example.com',
            'password' => 'WrongPass1',
        ]);

        $response->assertUnauthorized()
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);
    }

    public function test_login_unknown_email_returns_401(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'missing@example.com',
            'password' => 'Password1',
        ]);

        $response->assertUnauthorized()
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);
    }

    public function test_login_unverified_email_returns_403(): void
    {
        User::factory()->unverified()->create([
            'email' => 'unverified@example.com',
            'password' => Hash::make('Password1'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'unverified@example.com',
            'password' => 'Password1',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Please verify your email before logging in.',
            ]);
    }
}
