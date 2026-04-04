<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * @priority_high — maps to tests/Bdd/auth_session.feature
 */
class SessionApiTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedUserWithToken(): array
    {
        $user = User::factory()->create([
            'email' => 'me@example.com',
            'password' => Hash::make('Password1'),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return [$user, $token];
    }

    public function test_me_with_valid_bearer_returns_user_resource(): void
    {
        [$user, $token] = $this->verifiedUserWithToken();

        $response = $this->withToken($token)->getJson('/api/auth/me');

        $response->assertOk()
            ->assertJson([
                'message' => 'User authenticated',
            ])
            ->assertJsonPath('data.user.email', $user->email)
            ->assertJsonPath('data.user.has_verified_email', true);

        $this->assertNotNull($response->json('data.user.email_verified_at'));
    }

    public function test_me_without_token_returns_401(): void
    {
        $this->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_me_with_invalid_bearer_token_returns_401(): void
    {
        $this->withToken('invalid-token')
            ->getJson('/api/auth/me')
            ->assertUnauthorized();
    }
}
