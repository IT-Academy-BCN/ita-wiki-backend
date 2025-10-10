<?php
declare(strict_types=1);

namespace Tests\Feature\UserTests\Positiv;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class UserControllerDestroyTest extends TestCase
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

    public function test_endpoint_destroy_direction(): void
    {
        $this->actingAs($this->admin, 'api');
        $response = $this->delete("/api/users/{$this->user->id}");
        $response->assertStatus(200);
    }
}
