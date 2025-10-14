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
            ->assertJsonStructure(['message', 'data'])
            ->assertJson(['message' => 'Tags retrieved successfully']);

        $tags = $response->json('data');

        $this->assertIsArray($tags);
        $this->assertNotEmpty($tags, "Tags list should not be empty");
        
        $tagNames = collect($tags)->pluck('name')->toArray();
        
        $this->assertContains('docker', $tagNames, "Should contain 'docker' tag from seeder");
        $this->assertContains('kubernetes', $tagNames, "Should contain 'kubernetes' tag from seeder");
        $this->assertContains('orm', $tagNames, "Should contain 'orm' tag from seeder");
        $this->assertContains('testing', $tagNames, "Should contain 'testing' tag from seeder");
        
        $this->assertContains('nodejs', $tagNames, "Should contain 'nodejs' tag created in test");
        $this->assertContains('react', $tagNames, "Should contain 'react' tag created in test");
        $this->assertContains('typescript', $tagNames, "Should contain 'typescript' tag created in test");
    }

    /**
     * Test that tag frequencies show correct usage count
     */
    public function test_can_get_tag_frequencies(): void
    {
        $response = $this->getJson('/api/tags/frequency');

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data'])
            ->assertJson(['message' => 'Tag frequencies retrieved successfully']);

        $frequencies = $response->json('data');

        $this->assertIsArray($frequencies);
        $this->assertNotEmpty($frequencies, "Tag frequencies should not be empty");

        $this->assertArrayHasKey('docker', $frequencies);
        $this->assertEquals(2, $frequencies['docker'], 
            "Expected 'docker' tag to appear 2 times (in both Node and React resources)");

        $this->assertArrayHasKey('kubernetes', $frequencies);
        $this->assertEquals(1, $frequencies['kubernetes'],
            "Expected 'kubernetes' tag to appear 1 time (only in Node resource)");

        $this->assertArrayHasKey('nodejs', $frequencies);
        $this->assertEquals(1, $frequencies['nodejs'],
            "Expected 'nodejs' tag to appear 1 time (only in Node resource)");

        $this->assertArrayHasKey('react', $frequencies);
        $this->assertEquals(1, $frequencies['react'],
            "Expected 'react' tag to appear 1 time (only in React resource)");

        $this->assertArrayHasKey('typescript', $frequencies);
        $this->assertEquals(1, $frequencies['typescript'],
            "Expected 'typescript' tag to appear 1 time (only in React resource)");
    }

    /**
     * Test that category tag frequencies are grouped correctly
     */
    public function test_can_get_category_tag_frequencies(): void
    {
        $response = $this->getJson('/api/tags/category-frequency');

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data'])
            ->assertJson(['message' => 'Category tag frequencies retrieved successfully']);

        $categoryFrequencies = $response->json('data');

        $this->assertIsArray($categoryFrequencies);
        $this->assertNotEmpty($categoryFrequencies, "Category tag frequencies should not be empty");
        
        $this->assertArrayHasKey('Node', $categoryFrequencies);
        $nodeTags = $categoryFrequencies['Node'];
        
        $this->assertArrayHasKey('docker', $nodeTags);
        $this->assertEquals(1, $nodeTags['docker'],
            "Expected 'docker' to appear 1 time in Node category");
        
        $this->assertArrayHasKey('kubernetes', $nodeTags);
        $this->assertEquals(1, $nodeTags['kubernetes'],
            "Expected 'kubernetes' to appear 1 time in Node category");
        
        $this->assertArrayHasKey('nodejs', $nodeTags);
        $this->assertEquals(1, $nodeTags['nodejs'],
            "Expected 'nodejs' to appear 1 time in Node category");

        $this->assertArrayHasKey('React', $categoryFrequencies);
        $reactTags = $categoryFrequencies['React'];
        
        $this->assertArrayHasKey('docker', $reactTags);
        $this->assertEquals(1, $reactTags['docker'],
            "Expected 'docker' to appear 1 time in React category");
        
        $this->assertArrayHasKey('react', $reactTags);
        $this->assertEquals(1, $reactTags['react'],
            "Expected 'react' to appear 1 time in React category");
        
        $this->assertArrayHasKey('typescript', $reactTags);
        $this->assertEquals(1, $reactTags['typescript'],
            "Expected 'typescript' to appear 1 time in React category");

        $this->assertArrayNotHasKey('kubernetes', $reactTags,
            "Expected 'kubernetes' to NOT appear in React category (Node only)");
        $this->assertArrayNotHasKey('nodejs', $reactTags,
            "Expected 'nodejs' to NOT appear in React category (Node only)");
        
        $this->assertArrayNotHasKey('react', $nodeTags,
            "Expected 'react' to NOT appear in Node category (React only)");
        $this->assertArrayNotHasKey('typescript', $nodeTags,
            "Expected 'typescript' to NOT appear in Node category (React only)");
    }

    /**
     * Test that tags are correctly grouped by category with Tag IDs
     */
    public function test_can_get_tags_grouped_by_category(): void
    {
        $response = $this->getJson('/api/tags/by-category');

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data'])
            ->assertJson(['message' => 'Category tags retrieved successfully']);

        $categoryTags = $response->json('data');

        $this->assertIsArray($categoryTags);
        $this->assertNotEmpty($categoryTags, "Category tags should not be empty");

        $this->assertArrayHasKey('Node', $categoryTags);
        $nodeTagIds = $categoryTags['Node'];
        
        $this->assertIsArray($nodeTagIds);
        $this->assertNotEmpty($nodeTagIds, "Node category should have tag IDs");
        $this->assertCount(3, $nodeTagIds, "Node category should have 3 tags (docker, kubernetes, nodejs)");
        
        foreach ($nodeTagIds as $tagId) {
            $this->assertIsInt($tagId, "Tag ID should be an integer");
        }

        $this->assertArrayHasKey('React', $categoryTags);
        $reactTagIds = $categoryTags['React'];
        
        $this->assertIsArray($reactTagIds);
        $this->assertNotEmpty($reactTagIds, "React category should have tag IDs");
        $this->assertCount(3, $reactTagIds, "React category should have 3 tags (docker, react, typescript)");
        
        foreach ($reactTagIds as $tagId) {
            $this->assertIsInt($tagId, "Tag ID should be an integer");
        }

        $dockerTag = Tag::where('name', 'docker')->first();
        $this->assertNotNull($dockerTag, "Docker tag should exist in database");
        
        $this->assertContains($dockerTag->id, $nodeTagIds,
            "Docker tag ID should be in Node category");
        $this->assertContains($dockerTag->id, $reactTagIds,
            "Docker tag ID should be in React category");

        $kubernetesTag = Tag::where('name', 'kubernetes')->first();
        $this->assertNotNull($kubernetesTag, "Kubernetes tag should exist in database");
        $this->assertContains($kubernetesTag->id, $nodeTagIds,
            "Kubernetes tag ID should be in Node category");
        $this->assertNotContains($kubernetesTag->id, $reactTagIds,
            "Kubernetes tag ID should NOT be in React category");

        $nodejsTag = Tag::where('name', 'nodejs')->first();
        $this->assertNotNull($nodejsTag, "Nodejs tag should exist in database");
        $this->assertContains($nodejsTag->id, $nodeTagIds,
            "Nodejs tag ID should be in Node category");
        $this->assertNotContains($nodejsTag->id, $reactTagIds,
            "Nodejs tag ID should NOT be in React category");

        $reactTag = Tag::where('name', 'react')->first();
        $this->assertNotNull($reactTag, "React tag should exist in database");
        $this->assertContains($reactTag->id, $reactTagIds,
            "React tag ID should be in React category");
        $this->assertNotContains($reactTag->id, $nodeTagIds,
            "React tag ID should NOT be in Node category");

        $typescriptTag = Tag::where('name', 'typescript')->first();
        $this->assertNotNull($typescriptTag, "Typescript tag should exist in database");
        $this->assertContains($typescriptTag->id, $reactTagIds,
            "Typescript tag ID should be in React category");
        $this->assertNotContains($typescriptTag->id, $nodeTagIds,
            "Typescript tag ID should NOT be in Node category");
    }

    /**
     * Test that endpoints handle empty resources gracefully
     */
    public function test_endpoints_return_empty_when_no_resources(): void
    {
        Resource::query()->delete();

        $response = $this->getJson('/api/tags');
        $response->assertStatus(200);
        $tags = $response->json('data');
        $this->assertIsArray($tags);
        $this->assertNotEmpty($tags, "Tags from TagSeeder should still exist even without resources");

        $response = $this->getJson('/api/tags/frequency');
        $response->assertStatus(200);
        $frequencies = $response->json('data');
        $this->assertIsArray($frequencies);
        $this->assertEmpty($frequencies, "Tag frequencies should be empty when no resources exist");

        $response = $this->getJson('/api/tags/category-frequency');
        $response->assertStatus(200);
        $categoryFrequencies = $response->json('data');
        $this->assertIsArray($categoryFrequencies);
        $this->assertEmpty($categoryFrequencies, "Category tag frequencies should be empty when no resources exist");

        $response = $this->getJson('/api/tags/by-category');
        $response->assertStatus(200);
        $categoryTags = $response->json('data');
        $this->assertIsArray($categoryTags);
        $this->assertEmpty($categoryTags, "Category tags should be empty when no resources exist");
    }
}
