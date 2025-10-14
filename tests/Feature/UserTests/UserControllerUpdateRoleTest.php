<?php
declare(strict_types=1);

namespace Tests\Feature\UserTests;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerUpdateRoleTest extends TestCase
{
    use RefreshDatabase;
    protected User $user;
    protected User $admin;
    protected User $superadmin;
    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->assignRole('student');
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        $this->superadmin = User::factory()->create();
        $this->superadmin->assignRole('superadmin');
        $this->student = User::factory()->create();
        $this->student->assignRole('student');
    }

    public function test_endpoint_roleUpdate_direction(): void
    {
        $this->actingAs($this->admin, 'api');
        $response = $this->put("/api/users/{$this->user->id}/update-role", ['role' => 'admin']);
        $response->assertStatus(200);
    }

    public function test_endpoint_roleUpdate_direction_superadmin(): void
    {
        $this->actingAs($this->superadmin, 'api');
        $response = $this->put("/api/users/{$this->user->id}/update-role", ['role' => 'admin']);
        $response->assertStatus(200);
    }

    public function test_role_is_updated(): void
    {
        $this->actingAs($this->admin, 'api');
        $response = $this->put("/api/users/{$this->user->id}/update-role", ['role' => 'admin']);
        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'User role updated successfully',
                 ]);
        $this->assertTrue($this->user->hasRole('admin'));
    }

    public function test_not_admin_cannot_update_roles()
    {
        $this->actingAs($this->student, 'api');
        $response = $this->putJson("/api/users/{$this->student->id}/update-role", ['role' => 'admin']);
        $response->assertStatus(403)
                 ->assertJson([
                    'error' => 'Forbidden',
                 ]);
    }

    public function test_not_user_update_roles()
    {
        $response = $this->putJson("/api/users/{$this->student->id}/update-role", ['role' => 'admin']);
        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Unauthenticated.'
                 ]);
    }

    public function test_not_role_existent_cannot_be_assigned()
    {
        $this->actingAs($this->admin, 'api');
        $response = $this->putJson("/api/users/{$this->student->id}/update-role", ['role' => 'invalid_role']);
        $response->assertStatus(422)
                 ->assertJson([
                     'message' => 'The selected role is invalid.'
                 ])
                 ->assertJsonStructure([
                     'errors' => ['role']
                 ]);
    }

    public function test_not_user()
    {
        $this->actingAs($this->admin, 'api');
        $response = $this->putJson("/api/users/999999/update-role", ['role' => 'student']);
        $response->assertStatus(404)
                 ->assertJson([
                     'message' => 'User not found.'
                 ]);
    }
}