<?php

declare(strict_types = 1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;


class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {   
        parent::setUp();  
        User::truncate();
    }

    protected function validUserData($overrides = [])
    {
        return array_merge([
            'name' => 'Testing User',
            'github_id' => 111111111,
            'github_user_name' => 'testing github name',
            'email' => 'testing@example.com',
        //    'password' => 'password123',
        //    'password_confirmation' => 'password123',
        ], $overrides);
    }
/*
    public function testRegisterCreatesUserWithCorrectData()
    {
        $data = $this->validUserData();

        $response = $this->postJson('/api/register', $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'message' => 'User created successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => $data['name'],
            'github_id' => $data['github_id'],
            'github_user_name' => $data['github_user_name'],
            'email' => $data['email'],
        ]);

        $user = User::where('email', $data['email'])->first();
        $this->assertTrue(Hash::check($data['password'], $user->password));
    }

    public function test_register_requires_all_fields()
    {
        $response = $this->postJson('/api/register', []);

     // dump($response->getStatusCode());
     // dump($response->json());    

        $response->assertStatus(422);
        $data = $response->json();

        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('email', $data);
       // $this->assertArrayHasKey('password', $data);
        $this->assertArrayHasKey('github_id', $data);
        $this->assertArrayHasKey('github_user_name', $data);
        
        $this->assertEquals(['The name field is required.'], $data['name']);
        $this->assertEquals(['The email field is required.'], $data['email']);
      //  $this->assertEquals(['The password field is required.'], $data['password']);
        $this->assertEquals(['The github_id field is required.'], $data['github_id']);
        $this->assertEquals(['The github_user_name field is required.'], $data['github_user_name']);       
    }

    public function test_register_fails_with_duplicate_email()
    {
        User::factory()->create(['email' => 'testing@example.com']);
        $data = $this->validUserData();

        $response = $this->postJson('/api/register', $data);
                
        $response->assertStatus(422);
        $data = $response->json();
        
        $this->assertArrayHasKey('email', $data);
        $this->assertEquals(['The email has already been taken.'], $data['email']);

    }

    public function test_register_fails_with_password_confirmation_mismatch()
    {
        $data = $this->validUserData(['password_confirmation' => 'wrongpassword']);
        $response = $this->postJson('/api/register', $data);

        $response->assertStatus(422);
        $data = $response->json();
        
        $this->assertArrayHasKey('password', $data);
        $this->assertEquals(['The password field confirmation does not match.'], $data['password']);            
    }
*/
}