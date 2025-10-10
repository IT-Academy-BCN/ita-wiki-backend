<?php
declare(strict_types=1);

namespace Tests\Feature\UserTests\Positiv;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class UserControllerUpdateRoleTest extends TestCase
{
    use RefreshDatabase;
    protected User $user;
    protected User $admin;
    protected User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->assignRole('student');
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        $this->superadmin = User::factory()->create();
        $this->superadmin->assignRole('superadmin');
    }

    public function test_endpoint_roleUpdate_direction():void
    {
        $this->actingAs($this->admin, 'api');
        $response = $this->put("/api/users/{$this->user->id}/update-role", ['role' => 'admin']);
        $response->assertStatus(200);
    }

    public function test_endpoint_roleUpdate_direction_superadmin():void
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
}
