<?php

declare (strict_types= 1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Role;
use App\Models\Resource;
use App\Models\Bookmark;

class BookmarkControllerTest extends TestCase
{
    use RefreshDatabase;
    protected $student;
    protected $anonymous;
    protected $resources;
    protected $bookmarks;

    public function setUp(): void
    {
        parent::setUp();

        $this->student = Role::factory()->create([
            'github_id' => 9871315,
            'role' => 'student'
        ]);
        $this->anonymous = Role::factory()->create([
            'github_id' => 9861725,
            'role' => 'anonymous'
        ]);

        $this->resources = Resource::factory(3)->create([
            'github_id' => $this->student->github_id
        ]);

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
        $response = $this->get('api/bookmarks/?github_id=' . $this->student->github_id);
        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJson([
                ['github_id' => 9871315, 'resource_id' => $this->resources[0]->id],
                ['github_id' => 9871315, 'resource_id' => $this->resources[1]->id]
            ]);
    }

    public function testDestroyBookmark(): void
    {
        $response = $this->post('api/bookmarks', [
            'github_id' => $this->student->github_id,
            'resource_id' => $this->bookmarks[1]->resource_id
        ]);
                
        $response->assertStatus(200)->assertJsonCount(1);

        $this->assertDatabaseMissing('bookmarks', [
            'github_id' => $this->student->github_id,
            'resource_id' => $this->bookmarks[1]->resource_id
        ]);
    }

    public function testCreateBookmark(): void
    {
        $response = $this->post('api/bookmarks', [
            'github_id' => $this->student->github_id,
            'resource_id' => $this->resources[2]->id
        ]);

        $response->assertStatus(200)->assertJsonCount(3);

        $this->assertDatabaseHas('bookmarks', [
            'github_id' => $this->student->github_id,
            'resource_id' => $this->resources[2]->id
        ]);
    }

    public function testCreateAnonymousBookmarkFails() : void {
        $response = $this->post('api/bookmarks', [
            'github_id' => $this->anonymous->github_id,
            'resource_id' => 2]);
        $response->assertStatus(422);
    }

    public function testGetAnonymousBookmarksFails(): void
    {
        $response = $this->get('api/bookmarks/?github_id=' . $this->anonymous->github_id);
        $response->assertStatus(422);
    }
}