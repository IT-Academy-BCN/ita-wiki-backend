<?php

declare(strict_types=1);

namespace Tests\Feature\UserTests;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerNegativeTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_update_roles()
    {
        $UsernotAdmin = User::factory()->create();
        $UsernotAdmin->assignRole('student');
        $this->actingAs($UsernotAdmin, 'api');

        $response = $this->put("/api/users/{$UsernotAdmin->id}/update-role", ['role' => 'admin']);
        $response->assertStatus(403)
                 ->assertJson([
                     'message' => 'not authorized'
                 ]);
    }

   

}
      