<?php

declare(strict_types=1);

namespace Tests\Feature\UserTests;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerNegativeTest extends TestCase
{
    use RefreshDatabase;
    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->assignRole('student');
        
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

    }

    public function test_not_admin_cannot_update_roles()
    {
        $this->actingAs($this->user, 'api');

    $response = $this->putJson("/api/users/{$this->user->id}/update-role", ['role' => 'admin']);
        $response->assertStatus(403)
                 ->assertJson([
                    'error' => 'Forbidden',
                 ]);
     
    }

    public function test_not_user_update_roles()
    {
    $response = $this->putJson("/api/users/{$this->user->id}/update-role", ['role' => 'admin']);
        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Unauthenticated.'
                 ]);
        
    }

    public function test_not_role_existent_cannot_be_assigned()
    {
        $this->actingAs($this->admin, 'api');

    $response = $this->putJson("/api/users/{$this->user->id}/update-role", ['role' => 'invalid_role']);
        $response->assertStatus(422)
                 ->assertJson([
                     'message' => 'The selected role is invalid.'
                 ])
                 ->assertJsonStructure([
                     'errors' => ['role']
                 ]);
        
    }
    

   

}
      