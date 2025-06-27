<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TagNode;
use App\Models\RoleNode;
use App\Models\ResourceNode;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TagNodeControllerTest extends TestCase
{
    //use RefreshDatabase;
    public function setUp(): void
    {
        parent::setUp();

        $tags = [
            'tag-one',
            'tag-two',
            'tag-three'
        ];

        foreach ($tags as $tag) {
            TagNode::create([
                'name' => $tag
            ]);
        }

        RoleNode::factory()->create([
            'node_id' => 'NODEID987',
            'role' => 'student'
        ]);

        ResourceNode::factory()->create([
            'node_id' => 'NODEID987',
            'tags'    => ['tag-one']
        ]);

        ResourceNode::factory()->create([
        'node_id' => 'NODEID987',
        'tags'    => ['tag-one', 'tag-two']
        ]);

        ResourceNode::factory()->create([
            'node_id' => 'NODEID987',
            'tags'      => ['tag-one', 'tag-two', 'tag-three']
        ]);
      
    }
    
    public function testCanGetTagsList(): void
    {
        $response = $this->get(route('tags-node'));
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'tag-one']);
        $response->assertJsonFragment(['name' => 'tag-two']);
        $response->assertJsonFragment(['name' => 'tag-three']);
    }

    public function testCanGetTagsFrequency(): void
    {
        $response = $this->get(route('tags-node.frequency'));
        $response->assertStatus(200);
    }

    public function testCanGetCategoryTagsFrequency(): void
    {
        $response = $this->get(route('category.tags-node.frequency'));
        $response->assertStatus(200);
    }

      public function testCanGetCategoryTagsId(): void
    {
        $response = $this->get('/api/tags-node/by-category');
        $response->assertStatus(200);
    }
}
