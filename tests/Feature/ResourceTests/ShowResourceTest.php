<?php

declare(strict_types=1);

namespace Tests\Feature\ResourceTests;

use Tests\TestCase;
use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShowResourceTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_public_user_can_search_resources(): void
    {
        Resource::factory()->create(['title' => 'Laravel Tutorial']);
        Resource::factory()->create(['title' => 'React Guide']);

        $response = $this->getJson(route('resources.index', ['search' => 'Laravel']));

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_public_user_can_list_all_resources(): void
    {
        Resource::factory()->count(5)->create();

        $response = $this->getJson(route('resources.index'));

        $response->assertStatus(200)
            ->assertJsonCount(5);
    }

    public function test_public_user_can_view_single_resource(): void
    {
        $resource = Resource::factory()->create();

        $response = $this->getJson(route('resources.show', $resource->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $resource->id,
                'title' => $resource->title,
            ]);
    }

    public function test_returns_404_when_resource_not_found(): void
    {
        $response = $this->getJson(route('resources.show', 999));

        $response->assertStatus(404);
    }
}
