<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\ListProjects;
use App\Models\ContributorListProject;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use App\Enums\LanguageEnum;

class ListProjectsDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected $projectOne;
    protected $contributorOne;
    protected $userOne;

    public function setUp(): void
    {
        parent::setUp();

        $this->userOne = User::factory()->create(['id' => 1]);

        $this->projectOne = ListProjects::factory()->create([
            'id' => 1,
            'title' => 'Project Alpha',
            'time_duration' => '1 month',
            'language_backend' => LanguageEnum::PHP->value,
            'language_frontend' => LanguageEnum::JavaScript->value,
        ]);

        $this->contributorOne = ContributorListProject::factory()->create([
            'user_id' => $this->userOne->id,
            'programming_role' => 'Backend Developer',
            'list_project_id' => $this->projectOne->id,
        ]);
    }



    public function test_delete_existing_project_successfully(): void
    {

        Sanctum::actingAs($this->userOne);
        $response = $this->delete("/api/codeconnect/{$this->projectOne->id}");

        $response->assertJsonFragment([
            'success' => true,
            'message' => 'Project deleted successfully',
        ]);
        $response->assertStatus(200);
    }

    public function test_delete_nonexistent_project_returns_404(): void
    {
        Sanctum::actingAs($this->userOne);
        $response = $this->delete("/api/codeconnect/999");

        $response->assertJsonFragment([
            'success' => false,
            'message' => 'Project not found',
        ]);
        $response->assertStatus(404);
    }

    public function test_delete_requires_authentication(): void
    {
        $response = $this->deleteJson("/api/codeconnect/{$this->projectOne->id}");

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }
}
