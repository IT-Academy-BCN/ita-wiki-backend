<?php
declare (strict_types= 1);

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\Role;
use App\Models\Tag;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $tags = [
            'tag-one',
            'tag-two',
            'tag-three'
        ];

        foreach ($tags as $tag) {
            Tag::create([
                'name' => $tag
            ]);
        }

        Role::factory()->create([
            'github_id' => 9871315,
            'role' => 'student'
        ]);

        Resource::factory()->create([
            'github_id' => 9871315,
            'tags' => ['tag-one']
        ]);

        Resource::factory()->create([
            'github_id' => 9871315,
            'tags' => ['tag-one', 'tag-two']
        ]);

        Resource::factory()->create([
            'github_id' => 9871315,
            'tags' => ['tag-one', 'tag-two', 'tag-three']
        ]);
      
    }
    
    public function testCanGetTagsList(): void
    {
        $response = $this->get(route('tags'));
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'tag-one']);
        $response->assertJsonFragment(['name' => 'tag-two']);
        $response->assertJsonFragment(['name' => 'tag-three']);
    }

    public function testCanGetTagsFrequency(): void
    {
        $response = $this->get(route('tags.frequency'));
        $response->assertStatus(200);
    }

    public function testCanGetCategoryTagsFrequency(): void
    {
        $response = $this->get(route('category.tags.frequency'));
        $response->assertStatus(200);
    }

      public function testCanGetCategoryTagsId(): void
    {
        $response = $this->get('/api/tags/by-category');
        $response->assertStatus(200);
    }
}
