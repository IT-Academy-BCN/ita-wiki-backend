<?php

declare(strict_types=1);

namespace Tests\Feature\AuthTests;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_seeder_creates_user_with_github_credentials(): void{

        $testUser = User::factory()->create([
            'github_id' => '12345678',
            'github_user_name' => 'test_user',
            'name' => 'test_user',
        ]);

        $this->assertDatabaseHas('users', [
            'github_id' => '12345678',
            'github_user_name' => 'test_user',
            'name' => 'test_user',
        ]);

        $this->assertEquals('12345678', $testUser->github_id);
        $this->assertEquals('test_user', $testUser->github_user_name);
        $this->assertEquals('test_user', $testUser->name);
    }

    public function test_user_can_generate_sanctum_token(): void{
        
        $user = User::factory()->create([
            'github_id' => '12345678',
            'github_user_name' => 'test_user',
            'name' => 'test_user',
        ]);

        $token = $user->createToken('Personal Access Token')->plainTextToken;

        $this->assertNotNull($token);
        $this->assertStringStartsWith('1|', $token);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => 'App\Models\User',
            'name' => 'Personal Access Token',
        ]);
    }

    public function test_sanctum_token_authenticates_user(): void{

        $user = User::factory()->create([
            'github_id' => '12345678',
            'github_user_name' => 'test_user',
            'name' => 'test_user',
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'github_id' => '12345678',
                    'github_user_name' => 'test_user',
                    'name' => 'test_user',
                ]
            ]);
    }

    public function test_auth_me_endpoint_requires_authentication(): void{

        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    public function test_auth_github_user_endpoint_requires_authentication(): void{

        $response = $this->getJson('/api/auth/github/user');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    public function test_authenticated_user_can_access_github_user_endpoint(): void{

        $user = User::factory()->create([
            'github_id' => '12345678',
            'github_user_name' => 'test_user',
            'name' => 'test_user',
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/auth/github/user');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'github_id' => '12345678',
                    'github_user_name' => 'test_user',
                    'name' => 'test_user',
                ]
            ]);
    }

    public function test_logout_deletes_current_token(): void{

        $user = User::factory()->create([
            'github_id' => '12345678',
            'github_user_name' => 'test_user',
            'name' => 'test_user',
        ]);

        $token = $user->createToken('auth_token');
        $tokenId = $token->accessToken->id;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Session closed successfully'
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }

    public function test_user_factory_generates_valid_github_credentials(): void{

        $user = User::factory()->create();

        $this->assertNotNull($user->github_id);
        $this->assertNotNull($user->github_user_name);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertIsNumeric($user->github_id);
        $this->assertGreaterThanOrEqual(10000000, (int)$user->github_id);
        $this->assertLessThanOrEqual(99999999, (int)$user->github_id);
    }

    public function test_multiple_users_can_have_tokens(): void{

        $user1 = User::factory()->create(['github_id' => '11111111']);
        $user2 = User::factory()->create(['github_id' => '22222222']);

        $token1 = $user1->createToken('token1')->plainTextToken;
        $token2 = $user2->createToken('token2')->plainTextToken;

        $this->assertNotNull($token1);
        $this->assertNotNull($token2);
        $this->assertNotEquals($token1, $token2);

        $this->assertDatabaseCount('personal_access_tokens', 2);
    }

    public function test_user_can_have_multiple_tokens(): void
    {
        $user = User::factory()->create(['github_id' => '12345678']);

        $token1 = $user->createToken('web')->plainTextToken;
        $token2 = $user->createToken('mobile')->plainTextToken;
        $token3 = $user->createToken('api')->plainTextToken;

        $this->assertNotNull($token1);
        $this->assertNotNull($token2);
        $this->assertNotNull($token3);

        $this->assertDatabaseCount('personal_access_tokens', 3);
        $this->assertEquals(3, $user->tokens()->count());
    }
}
