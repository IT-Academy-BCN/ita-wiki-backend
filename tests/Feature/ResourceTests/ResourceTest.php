<?php

declare(strict_types=1);

namespace Tests\Feature\ResourceTests;

use Tests\TestCase;
use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResourceTest extends TestCase
{
    use RefreshDatabase;

   /* public function test_authenticated_user_can_get_resources_list(): void
    {
        $this->authenticateUserWithRole('student');

        Resource::factory()->count(10)->create();

        $response = $this->getJson(route('resources.index'));

        $response->assertStatus(200)
            ->assertJsonCount(10);
    }

    public function test_unauthenticated_user_cannot_get_resources_list(): void
    {
        $response = $this->getJson(route('resources.index'));

        $response->assertStatus(401);
    }*/
}