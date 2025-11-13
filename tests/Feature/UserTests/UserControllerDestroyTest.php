<?php
declare(strict_types=1);

namespace Tests\Feature\UserTests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

// class UserControllerDestroyTest extends TestCase
// {
//     use RefreshDatabase;
//     protected User $user;
//     protected User $admin;
//     protected User $superadmin;
//     protected User $student;

//     protected function setUp(): void
//     {
//         parent::setUp();
//         $this->user = User::factory()->create();
//         $this->user->assignRole('student');
//         $this->admin = User::factory()->create();
//         $this->admin->assignRole('admin');
//         $this->superadmin = User::factory()->create();
//         $this->superadmin->assignRole('superadmin');
//         $this->student = User::factory()->create();
//         $this->student->assignRole('student');
//     }

//     public function test_endpoint_destroy_direction(): void
//     {
//         $this->actingAs($this->admin, 'api');
//         $response = $this->delete("/api/users/{$this->user->id}");
//         $response->assertStatus(200);
//     }

//     public function test_not_admin_or_superadmin_cannot_delete_user(): void
//     {
//         $this->actingAs($this->student, 'api');
//         $response = $this->delete("/api/users/{$this->student->id}");
//         $response->assertStatus(403)
//                  ->assertJson([
//                      'error' => 'Forbidden',
//                  ]);
//     }
    
//     public function test_user_not_found():void{
//         $this->actingAs($this->admin, 'api');
//         $response = $this->delete("/api/users/9999");
//         $response->assertStatus(404)
//                  ->assertJson([
//                      'message' => 'User not found.',
//                  ]);
//     }
// }