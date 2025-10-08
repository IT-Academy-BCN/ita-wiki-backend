<?php
// filepath: c:\xampp\htdocs\Progetto ITA\ita-wiki-backend\tests\Feature\MiddlewareTests\SpatieMiddlewareTest.php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use Tests\TestCase;
use App\Models\User;
use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SpatieMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    // ⚠️ THESE TESTS REQUIRE ROLES FROM SEEDER
    // Will pass after RolesAndPermissionsSeeder is implemented in next PR

    public function test_middleware_blocks_request_without_authentication(): void
    {
        $this->markTestSkipped('Waiting for RolesAndPermissionsSeeder in next PR');
        
        $response = $this->postJson('/api/resources', [
            'title' => 'Test',
            'description' => 'Test description',
            'url' => 'https://example.com',
            'category' => 'Node',
            'type' => 'Video',
        ]);
        
        $response->assertStatus(401);
    }

    public function test_middleware_allows_authenticated_request(): void
    {
        $this->markTestSkipped('Waiting for RolesAndPermissionsSeeder in next PR');
        
        $user = User::factory()->create(['github_id' => '123']);
        $user->assignRole('student');
        
        $this->actingAs($user, 'api');
        
        $response = $this->postJson('/api/resources', [
            'title' => 'Test Resource',
            'description' => 'Test description',
            'url' => 'https://example.com',
            'category' => 'Node',
            'type' => 'Video',
            'github_id' => '123',
        ]);
        
        $response->assertStatus(201);
    }

    public function test_middleware_enforces_ownership(): void
    {
        $this->markTestSkipped('Waiting for RolesAndPermissionsSeeder in next PR');
        
        $owner = User::factory()->create(['github_id' => '111']);
        $owner->assignRole('student');
        
        $otherUser = User::factory()->create(['github_id' => '222']);
        $otherUser->assignRole('student');
        
        $resource = Resource::factory()->create(['github_id' => '111']);
        
        $this->actingAs($otherUser, 'api');
        
        $response = $this->deleteJson("/api/resources/{$resource->id}");
        
        $response->assertStatus(403);
    }
}