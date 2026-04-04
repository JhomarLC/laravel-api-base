<?php

namespace Tests\Feature\Auth;

use App\Models\EmailVerificationOtp;
use App\Models\User;
use App\Notifications\VerifyEmailOtpnotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * @priority_high — maps to tests/Bdd/auth_email_verification.feature
 */
class EmailVerificationApiTest extends TestCase
{
    use RefreshDatabase;

    private function otpFromRegistration(string $email): string
    {
        Notification::fake();

        $this->postJson('/api/auth/register', [
            'name' => 'Jane',
            'email' => $email,
            'password' => 'SecurePass1',
            'password_confirmation' => 'SecurePass1',
        ])->assertCreated();

        $user = User::where('email', $email)->firstOrFail();

        $sent = Notification::sent($user, VerifyEmailOtpnotification::class);
        $this->assertCount(1, $sent);

        return $sent->first()->otp;
    }

    public function test_verify_email_with_correct_otp_returns_200(): void
    {
        $email = 'verify@example.com';
        $otp = $this->otpFromRegistration($email);

        $response = $this->postJson('/api/auth/email/verify', [
            'email' => $email,
            'otp' => $otp,
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Email verified successfully.',
            ])
            ->assertJsonPath('data.user.has_verified_email', true);

        $this->assertDatabaseMissing('email_verification_otps', ['email' => $email]);
        $this->assertNotNull(User::where('email', $email)->first()->email_verified_at);
    }

    public function test_verify_with_wrong_otp_returns_400(): void
    {
        $email = 'wrongotp@example.com';
        $this->otpFromRegistration($email);

        $response = $this->postJson('/api/auth/email/verify', [
            'email' => $email,
            'otp' => '000000',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid or expired verification code.',
            ]);
    }

    public function test_verify_with_expired_otp_returns_400(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'expired@example.com',
            'password' => Hash::make('Password1'),
        ]);

        EmailVerificationOtp::create([
            'email' => $user->email,
            'otp_hash' => Hash::make('123456'),
            'expires_at' => now()->subMinute(),
        ]);

        $response = $this->postJson('/api/auth/email/verify', [
            'email' => $user->email,
            'otp' => '123456',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid or expired verification code.',
            ]);
    }

    public function test_verify_when_already_verified_returns_400(): void
    {
        $user = User::factory()->create([
            'email' => 'already@example.com',
        ]);

        $response = $this->postJson('/api/auth/email/verify', [
            'email' => $user->email,
            'otp' => '123456',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid or expired verification code.',
            ]);
    }

    public function test_resend_for_unverified_user_refreshes_otp_hash_and_sends_notification(): void
    {
        Notification::fake();

        $this->postJson('/api/auth/register', [
            'name' => 'Jane',
            'email' => 'resend@example.com',
            'password' => 'SecurePass1',
            'password_confirmation' => 'SecurePass1',
        ])->assertCreated();

        $firstHash = EmailVerificationOtp::where('email', 'resend@example.com')->value('otp_hash');

        $this->postJson('/api/auth/email/resend', [
            'email' => 'resend@example.com',
        ])->assertOk()
            ->assertJson([
                'message' => 'If the account exists and is not yet verified, a verification code has been sent.',
            ]);

        $secondHash = EmailVerificationOtp::where('email', 'resend@example.com')->value('otp_hash');
        $this->assertNotSame($firstHash, $secondHash);

        $user = User::where('email', 'resend@example.com')->firstOrFail();
        Notification::assertSentToTimes($user, VerifyEmailOtpnotification::class, 2);
    }
}
