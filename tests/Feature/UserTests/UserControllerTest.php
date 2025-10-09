<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;


class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_endpoint_roleUpdate()
    {
        $UserAdmin = User::factory()->create();
        $UserAdmin->assignRole('admin');

        $userUpdate = User::factory()->create();
        $userUpdate->assignRole('student');

        $this->actingAs($UserAdmin, 'api');
        $response = $this->put("/api/users/{$userUpdate->id}/update-role", ['role' => 'admin']);

        $this->assertTrue($userUpdate->hasRole('admin'));
        $this->assertFalse($userUpdate->hasRole('student'));

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Role updated successfully',
                    'user'=> $userUpdate->id
                ]);

    }

    public function test_endpoint_profile()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $response = $this->get('/api/profile');
        $response->assertStatus(200);
    }

    public function test_endpoint_index()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $response = $this->get('/api/users');
        $response->assertStatus(200);
    }

    public function test_endpoint_destroy()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $response = $this->delete("/api/users/{$user->id}");
        $response->assertStatus(200);
    }
}
