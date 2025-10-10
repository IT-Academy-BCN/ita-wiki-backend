<?php
declare(strict_types=1);

namespace Tests\Feature\UserTests\Positiv;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class UserControllerIndexTest extends TestCase
{
    use RefreshDatabase;
    protected User $admin;
    protected User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        $this->superadmin = User::factory()->create();
        $this->superadmin->assignRole('superadmin');
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
}
