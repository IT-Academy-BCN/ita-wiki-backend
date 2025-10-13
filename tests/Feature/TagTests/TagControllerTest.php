<?php
declare(strict_types=1);

namespace Tests\Feature\TagTests;

use Tests\TestCase;
use App\Models\User;
use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // ✅ Create a student user for resources
        $student = User::factory()->create();
        $student->assignRole('student');

        // Create resources with tags
        Resource::factory()->create([
            'github_id' => $student->github_id,
            'tags' => ['docker', 'kubernetes'],
            'category' => 'Node'
        ]);

        Resource::factory()->create([
            'github_id' => $student->github_id,
            'tags' => ['docker', 'git'],
            'category' => 'React'
        ]);
    }

    public function test_can_get_all_tags(): void
    {
        $response = $this->getJson('/api/tags');

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data']);
    }

    public function test_can_get_tag_frequencies(): void
    {
        $response = $this->getJson('/api/tags/frequency');

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data']);
    }

    public function test_can_get_category_tag_frequencies(): void
    {
        $response = $this->getJson('/api/tags/category-frequency');

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data']);
    }

    public function test_can_get_tags_grouped_by_category(): void
    {
        $response = $this->getJson('/api/tags/by-category');

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data']);
    }
}
