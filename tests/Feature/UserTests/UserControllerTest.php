<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;



class UserControllerTest extends TestCase
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

    // ========== AUTHENTICATED TESTS FOR ENDPOINTS ==========

    public function test_endpoint_roleUpdate_direction():void
    {
        $this->actingAs($this->admin, 'api');
        $response = $this->put("/api/users/{$this->user->id}/update-role", ['role' => 'admin']);
        $response->assertStatus(200);
    }

    public function test_endpoint_profile_direction(): void
    {
        $this->actingAs($this->user, 'api');
        $response = $this->get('/api/profile');
        $response->assertStatus(200)
                    ->assertJson([
                        'message' => 'User profile retrieved successfully',
                        'user' => [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                        'github_id' => $this->user->github_id,
                        'roles' => $this->user->roles->toArray()
                    ]
                    ]);
    }

    public function test_endpoint_index_direction(): void
    {
        $this->actingAs($this->admin, 'api');
        $response = $this->get('/api/users');
        $response->assertStatus(200);
    }

    public function test_endpoint_destroy_direction(): void
    {
        $this->actingAs($this->admin, 'api');
        $response = $this->delete("/api/users/{$this->user->id}");
        $response->assertStatus(200);
    }
}
