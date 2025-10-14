<?php
declare(strict_types=1);

namespace Tests\Feature\UserTests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class UserControllerIndexTest extends TestCase
{
    use RefreshDatabase;
    protected User $admin;
    protected User $superadmin;
    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        $this->superadmin = User::factory()->create();
        $this->superadmin->assignRole('superadmin');
        $this->student = User::factory()->create();
        $this->student->assignRole('student');
    }

    public function test_endpoint_index_direction(): void
    {
        $this->actingAs($this->admin, 'api');
        $response = $this->get('/api/users');
        $response->assertStatus(200);
    }

    public function test_endpoint_index_direction_superadmin(): void
    {
        $this->actingAs($this->superadmin, 'api');
        $response = $this->get('/api/users');
        $response->assertStatus(200)
                   ->assertJson([
                     'message' => 'Users retrieved successfully',
                 ]);
    }

    public function test_not_admin_or_superadmin_cannot_access_index(): void
    {
        $this->actingAs($this->student, 'api');
        $response = $this->get('/api/users');
        $response->assertStatus(403)
                 ->assertJson([
                     'error' => 'Forbidden',
                 ]);
    }
}