<?php
declare(strict_types=1);

namespace Tests\Feature\TagTests;

use Tests\TestCase;
use App\Models\Tag;
use App\Models\User;
use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected Resource $nodeResource;
    protected Resource $reactResource;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->student = User::factory()->create();
        $this->student->assignRole('student');

        Tag::firstOrCreate(['name' => 'nodejs']);
        Tag::firstOrCreate(['name' => 'react']);
        Tag::firstOrCreate(['name' => 'typescript']);

        $this->nodeResource = Resource::factory()->create([
            'github_id' => $this->student->github_id,
            'tags' => ['docker', 'kubernetes', 'nodejs'],
            'category' => 'Node'
        ]);

        $this->reactResource = Resource::factory()->create([
            'github_id' => $this->student->github_id,
            'tags' => ['docker', 'react', 'typescript'],
            'category' => 'React'
        ]);
    }

    public function test_can_get_all_tags(): void
    {
        $response = $this->getJson('/api/tags');

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data']);

        $tagNames = collect($response->json('data'))->pluck('name')->toArray();
        
        $this->assertContains('docker', $tagNames);
        $this->assertContains('kubernetes', $tagNames);
        $this->assertContains('nodejs', $tagNames);
        $this->assertContains('react', $tagNames);
    }

    public function test_can_get_tag_frequencies(): void
    {
        $response = $this->getJson('/api/tags/frequency');

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data']);

        $frequencies = $response->json('data');

        $this->assertEquals(2, $frequencies['docker']);
        
        $this->assertEquals(1, $frequencies['kubernetes']);
        $this->assertEquals(1, $frequencies['nodejs']);
        $this->assertEquals(1, $frequencies['react']);
        $this->assertEquals(1, $frequencies['typescript']);
    }

    public function test_can_get_category_tag_frequencies(): void
    {
        $response = $this->getJson('/api/tags/category-frequency');

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data']);

        $data = $response->json('data');

       
        $this->assertArrayHasKey('Node', $data);
        $this->assertEquals(1, $data['Node']['docker']);
        $this->assertEquals(1, $data['Node']['kubernetes']);
        $this->assertEquals(1, $data['Node']['nodejs']);
        $this->assertArrayNotHasKey('react', $data['Node']);

        
        $this->assertArrayHasKey('React', $data);
        $this->assertEquals(1, $data['React']['docker']);
        $this->assertEquals(1, $data['React']['react']);
        $this->assertEquals(1, $data['React']['typescript']);
        $this->assertArrayNotHasKey('kubernetes', $data['React']);
    }

    public function test_can_get_tags_grouped_by_category(): void
    {
        $response = $this->getJson('/api/tags/by-category');

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data']);

        $data = $response->json('data');

       
        $this->assertArrayHasKey('Node', $data);
        $this->assertArrayHasKey('React', $data);
        $this->assertCount(3, $data['Node']);
        $this->assertCount(3, $data['React']);

        
        $dockerId = Tag::where('name', 'docker')->value('id');
        $this->assertContains($dockerId, $data['Node']);
        $this->assertContains($dockerId, $data['React']);

        $reactId = Tag::where('name', 'react')->value('id');
        $this->assertContains($reactId, $data['React']);
        $this->assertNotContains($reactId, $data['Node']);
    }

    public function test_endpoints_return_empty_when_no_resources(): void
    {
        Resource::query()->delete();

        
        $this->getJson('/api/tags')
            ->assertStatus(200)
            ->assertJsonCount(53, 'data'); 

        $this->getJson('/api/tags/frequency')
            ->assertStatus(200)
            ->assertJson(['data' => []]);

        $this->getJson('/api/tags/category-frequency')
            ->assertStatus(200)
            ->assertJson(['data' => []]);

        $this->getJson('/api/tags/by-category')
            ->assertStatus(200)
            ->assertJson(['data' => []]);
    }
}
