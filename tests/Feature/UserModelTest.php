<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanBeCreatedWithGithubId(): void
    {
        $user = User::factory()->create([
            'github_id' => '123456789'
        ]);

        $this->assertDatabaseHas('users', [
            'github_id' => '123456789',
            'email' => $user->email
        ]);
    }

    public function testGithubIdIsAccessible(): void
    {
        $githubId = '987654321';
        $user = User::factory()->create(['github_id' => $githubId]);

        $this->assertEquals($githubId, $user->github_id);
    }

    public function testGithubNameIsAccessible(): void
    {
        $githubUserName = 'Cristina';
        $user = User::factory()->create(['github_user_name' =>$githubUserName]);

        $this->assertEquals($githubUserName, $user->github_user_name);
    }


}