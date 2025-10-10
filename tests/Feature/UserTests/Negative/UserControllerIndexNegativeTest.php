<?php

declare(strict_types=1);

namespace Tests\Feature\UserTests\Negative;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class UserControllerIndexNegativeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $student;
    protected User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->student = User::factory()->create();
        $this->student->assignRole('student');

        $this->superadmin = User::factory()->create();
        $this->superadmin->assignRole('superadmin');
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
