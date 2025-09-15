<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

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

    public function testPasswordFieldHasBeenRemovedFromUsersTable(): void
    {
        
        $user = User::factory()->create();
        
        
        $this->assertArrayNotHasKey('password', $user->getAttributes());
        
        
        $this->assertNotContains('password', $user->getFillable());
        
        
        $this->assertNotContains('password', $user->getHidden());
        
        
        $columns = Schema::getColumnListing('users');
        $this->assertNotContains('password', $columns);
    }

    public function testCannotCreateUserWithPasswordField(): void
    {
       
        $userData = [
            'name' => 'Test User',
            'email' => 'test' . uniqid() . '@example.com',
            'github_id' => 123456789,
            'github_user_name' => 'testuser' . uniqid(),
            'password' => 'secretpassword' 
        ];

        $user = User::create($userData);
        
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Test User',
            'email' => $userData['email'],
            'github_id' => 123456789,
            'github_user_name' => $userData['github_user_name']
        ]);
        
        
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'password' => 'secretpassword'
        ]);
    }


}