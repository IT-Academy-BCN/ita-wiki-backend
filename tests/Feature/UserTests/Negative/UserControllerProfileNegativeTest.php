<?php

declare(strict_types=1);

namespace Tests\Feature\UserTests\Negative;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerProfileNegativeTest extends TestCase
{
    use RefreshDatabase;

    public function test_not_user_cannot_access_profile()
    {
        $response = $this->getJson('/api/profile');
        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Unauthenticated.'
                 ]);
    }
}
