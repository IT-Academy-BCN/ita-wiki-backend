<?php

declare (strict_types= 1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Resource;
use App\Models\Bookmark;

class BookmarkControllerTest extends TestCase
{
    protected $student;
    protected $resources;
    protected $bookmarks;

    public function setUp(): void
    {
        parent::setUp();

        $this->student = Role::factory()->create([
            'github_id' => 9871315,
            'role' => 'student'
        ]);

        $this->resources = Resource::factory(10)->create();

        $this->bookmarks = [
            Bookmark::create([
            'github_id' => $this->student->github_id,
            'resource_id' => $this->resources[0]->id]),
            Bookmark::create([
            'github_id' => $this->student->github_id,
            'resource_id' => $this->resources[1]->id])
        ];
    }

    public function testGetStudentBookmarks(): void
    {
        $response = $this->get('api/bookmarks/' . $this->student->github_id);
        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJson([
                ['github_id' => $this->student->github_id, 'resource_id' => $this->resources[0]->id],
                ['github_id' => $this->student->github_id, 'resource_id' => $this->resources[1]->id]
            ]);
    }

    public function testGetBookmarksForUnexistentRoleFails(): void {
        $nonExistentGithubId = 38928374;
        $response = $this->get('api/bookmarks/' . $nonExistentGithubId);
        $response->assertStatus(422);
    }

    public function testDestroyBookmark(): void
    {
        $initial_count = $this->bookmarks[1]->resource->bookmark_count;

        $response = $this->delete('api/bookmarks', [
            'github_id' => $this->student->github_id,
            'resource_id' => $this->bookmarks[1]->resource_id
        ]);
                
        $response->assertStatus(200)
            ->assertJson(['message' => 'Bookmark deleted successfully']);


        $this->assertDatabaseMissing('bookmarks', [
            'github_id' => $this->student->github_id,
            'resource_id' => $this->bookmarks[1]->resource_id
        ]);

        // Assert counter decremented by BookmarkObserver
        $this->assertEquals($initial_count - 1, $this->bookmarks[1]->resource->fresh()->bookmark_count);
    }

    public function testCreateBookmark(): void
    {
        $test_increment_resource = $this->resources[2];
        $initial_count = $test_increment_resource->bookmark_count;

        $response = $this->post('api/bookmarks', [
            'github_id' => $this->student->github_id,
            'resource_id' => $this->resources[2]->id
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'github_id' => $this->student->github_id,
                'resource_id' => $this->resources[2]->id,
            ]);

        $this->assertDatabaseHas('bookmarks', [
            'github_id' => $this->student->github_id,
            'resource_id' => $this->resources[2]->id
        ]);

        // Assert counter incremented by BookmarkObserver
        $this->assertEquals($initial_count + 1, $test_increment_resource->fresh()->bookmark_count);
    }

    public function testCreateBookmarkForNonexistentRoleFails(): void {
        $response = $this->post('api/bookmarks', [
            'github_id' => 9384758,
            'resource_id' => $this->resources[2]->id
        ]);
        $response->assertStatus(422);
    }

    public function testCreateBookmarkForNonexistentResourceFails(): void {
        $response = $this->post('api/bookmarks', [
            'github_id' => $this->student->github_id,
            'resource_id' => 447012
        ]);
        $response->assertStatus(422);
    }
}