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

    /*public function test_authenticated_user_can_search_resources(): void
    {
        $this->authenticateUserWithRole('student');
        
        Resource::factory()->create(['title' => 'Laravel Tutorial']);
        Resource::factory()->create(['title' => 'React Guide']);

        $response = $this->getJson(route('resources.index', ['search' => 'Laravel']));

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_authenticated_user_can_list_all_resources(): void
    {
        $this->authenticateUserWithRole('student');
        
        Resource::factory()->count(5)->create();

        $response = $this->getJson(route('resources.index'));

        $response->assertStatus(200)
            ->assertJsonCount(5);
    }

    public function test_authenticated_user_can_view_single_resource(): void
    {
        $this->authenticateUserWithRole('student');
        
        $resource = Resource::factory()->create();

        $response = $this->getJson(route('resources.show', $resource->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $resource->id,
                'title' => $resource->title,
            ]);
    }

    public function test_unauthenticated_user_cannot_search_resources(): void
    {
        $response = $this->getJson(route('resources.index', ['search' => 'Laravel']));

        $response->assertStatus(401);
    }*/
}