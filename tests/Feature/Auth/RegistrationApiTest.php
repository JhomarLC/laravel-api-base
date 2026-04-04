<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\VerifyEmailOtpnotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * @priority_high — maps to tests/Bdd/auth_registration.feature
 */
class RegistrationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_valid_payload_returns_201_without_token_and_sends_otp(): void
    {
        Notification::fake();

        $payload = [
            'name' => 'Jane Doe',
            'email' => 'newuser@example.com',
            'password' => 'SecurePass1',
            'password_confirmation' => 'SecurePass1',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Registration successful. Please verify your email.',
            ])
            ->assertJsonPath('data.user.email', 'newuser@example.com')
            ->assertJsonMissingPath('data.token');

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
        $this->assertDatabaseHas('email_verification_otps', ['email' => 'newuser@example.com']);

        $user = User::where('email', 'newuser@example.com')->first();
        Notification::assertSentTo($user, VerifyEmailOtpnotification::class);
    }

    public function test_register_duplicate_email_returns_422(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Other',
            'email' => 'taken@example.com',
            'password' => 'SecurePass1',
            'password_confirmation' => 'SecurePass1',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_password_shorter_than_eight_returns_422(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Jane',
            'email' => 'shortpw@example.com',
            'password' => 'Short1',
            'password_confirmation' => 'Short1',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_password_confirmation_mismatch_returns_422(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Jane',
            'email' => 'mismatch@example.com',
            'password' => 'SecurePass1',
            'password_confirmation' => 'SecurePass2',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
