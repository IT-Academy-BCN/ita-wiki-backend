<?php

declare(strict_types=1);

namespace Tests\Feature\ResourceTests;

use Tests\TestCase;
use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResourceTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_public_user_can_get_resources_list(): void
    {
        Resource::factory()->count(10)->create();

        $response = $this->getJson(route('resources.index'));

        $response->assertStatus(200)
            ->assertJsonCount(10);
    }

    public function test_public_user_gets_empty_list_when_no_resources_exist(): void
    {
        $response = $this->getJson(route('resources.index'));

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }
}
