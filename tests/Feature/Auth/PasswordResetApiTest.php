<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * @priority_high — maps to tests/Bdd/auth_password_reset.feature
 */
class PasswordResetApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_for_existing_user_returns_200_and_sends_reset_notification(): void
    {
        Mail::fake();
        Notification::fake();

        $user = User::factory()->create(['email' => 'forgot@example.com']);

        $response = $this->postJson('/api/auth/password/forgot', [
            'email' => 'forgot@example.com',
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'If the account exists, a password reset link has been sent to your email.',
            ]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_reset_password_with_valid_token_succeeds_and_allows_login_with_new_password(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'resetok@example.com',
            'password' => Hash::make('OldPassword1'),
        ]);

        $this->postJson('/api/auth/password/forgot', [
            'email' => $user->email,
        ])->assertOk();

        $plainToken = Notification::sent($user, ResetPasswordNotification::class)->first()->token;

        $response = $this->postJson('/api/auth/password/reset', [
            'email' => $user->email,
            'token' => $plainToken,
            'password' => 'NewPassword2',
            'password_confirmation' => 'NewPassword2',
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Password has been reset successfully.',
            ]);

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'NewPassword2',
        ])->assertOk()
            ->assertJsonPath('data.user.email', $user->email);
    }

    public function test_reset_password_revokes_existing_sanctum_tokens(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'tokrev@example.com',
            'password' => Hash::make('Password1'),
        ]);

        $plainAccessToken = $user->createToken('api-token')->plainTextToken;

        $this->postJson('/api/auth/password/forgot', [
            'email' => $user->email,
        ])->assertOk();

        $plainResetToken = Notification::sent($user, ResetPasswordNotification::class)->first()->token;

        $this->postJson('/api/auth/password/reset', [
            'email' => $user->email,
            'token' => $plainResetToken,
            'password' => 'NewPassword2',
            'password_confirmation' => 'NewPassword2',
        ])->assertOk();

        $this->withToken($plainAccessToken)
            ->getJson('/api/auth/me')
            ->assertUnauthorized();
    }
}
