<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\RoleNode;
use App\Models\ResourceNode;
use App\Models\BookmarkNode;

class BookmarkNodeControllerTest extends TestCase
{
    protected $student;
    protected $resources;
    protected $bookmarks;

    public function setUp(): void
    {
        parent::setUp();

        $this->student = RoleNode::factory()->create([
            'node_id' => 'MDQ6VXNlcj9871315=',
            'role' => 'student'
        ]);

        $this->resources = ResourceNode::factory(10)->create();

        $this->bookmarks = [
            BookmarkNode::firstOrCreate([
                'node_id' => $this->student->node_id,
                'resource_node_id' => $this->resources[0]->id
            ]),
            BookmarkNode::firstOrCreate([
                'node_id' => $this->student->node_id,
                'resource_node_id' => $this->resources[1]->id
            ])
        ];
    }

    public function testGetStudentBookmarksNode(): void
    {
        $response = $this->get('api/bookmarks-node/' . $this->student->node_id);
        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJson([
                ['node_id' => $this->student->node_id, 'resource_node_id' => $this->resources[0]->id],
                ['node_id' => $this->student->node_id, 'resource_node_id' => $this->resources[1]->id]
            ]);
    }

    public function testGetBookmarksNodeForUnexistentRoleFails(): void
    {
        $nonExistentNodeId = 'MDQ6VXNlcj38928374=';
        $response = $this->get('api/bookmarks-node/' . $nonExistentNodeId);
        $response->assertStatus(422);
    }

    public function testDestroyBookmarkNode(): void
    {
        $initial_count = $this->bookmarks[1]->resourceNode->bookmark_count;

        $response = $this->delete('api/bookmarks-node', [
            'node_id' => $this->student->node_id,
            'resource_node_id' => $this->bookmarks[1]->resource_node_id
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Bookmark deleted successfully']);

        $this->assertDatabaseMissing('bookmarks_node', [
            'node_id' => $this->student->node_id,
            'resource_node_id' => $this->bookmarks[1]->resource_node_id
        ]);

        // Assert counter decremented by BookmarkNodeObserver
        $this->assertEquals($initial_count - 1, $this->bookmarks[1]->resourceNode->fresh()->bookmark_count);
    }

    public function testCreateBookmarkNode(): void
    {
        $test_increment_resource = $this->resources[2];
        $initial_count = $test_increment_resource->bookmark_count;

        $response = $this->post('api/bookmarks-node', [
            'node_id' => $this->student->node_id,
            'resource_node_id' => $this->resources[2]->id
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'node_id' => $this->student->node_id,
                'resource_node_id' => $this->resources[2]->id,
            ]);

        $this->assertDatabaseHas('bookmarks_node', [
            'node_id' => $this->student->node_id,
            'resource_node_id' => $this->resources[2]->id
        ]);

        // Assert counter incremented by BookmarkNodeObserver
        $this->assertEquals($initial_count + 1, $test_increment_resource->fresh()->bookmark_count);
    }

    public function testCreateBookmarkNodeForNonexistentRoleFails(): void
    {
        $response = $this->post('api/bookmarks-node', [
            'node_id' => 'MDQ6VXNlcj9384758=',
            'resource_node_id' => $this->resources[2]->id
        ]);
        $response->assertStatus(422);
    }

    public function testCreateBookmarkNodeForNonexistentResourceFails(): void
    {
        $response = $this->post('api/bookmarks-node', [
            'node_id' => $this->student->node_id,
            'resource_node_id' => 447012
        ]);
        $response->assertStatus(422);
    }
}
