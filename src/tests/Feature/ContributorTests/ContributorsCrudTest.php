<?php

declare(strict_types=1);

namespace Tests\Feature\ContributorTests;

use Tests\TestCase;
use App\Models\User;
use App\Models\ListProjects;
use App\Models\ContributorListProject;
use App\Enums\ContributorStatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContributorsCrudTest extends TestCase
{
    use RefreshDatabase;

    private ListProjects $project;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->project = ListProjects::factory()->create();
        $this->user = User::factory()->create();
    }

    // ========== GET TESTS ==========

    public function test_it_can_list_all_contributors_of_a_project(): void
    {
        $contributor1 = ContributorListProject::factory()->create([
            'list_project_id' => $this->project->id,
            'status' => ContributorStatusEnum::Accepted->value,
        ]);

        $contributor2 = ContributorListProject::factory()->create([
            'list_project_id' => $this->project->id,
            'status' => ContributorStatusEnum::Pending->value,
        ]);

        $response = $this->getJson("/api/codeconnect/{$this->project->id}/contributors");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Contributors retrieved successfully',
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_it_returns_empty_array_when_project_has_no_contributors(): void
    {
        $response = $this->getJson("/api/codeconnect/{$this->project->id}/contributors");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [],
                'message' => 'Contributors retrieved successfully',
            ]);
    }

    public function test_get_contributors_returns_404_when_project_does_not_exist(): void
    {
        $response = $this->getJson("/api/codeconnect/99999/contributors");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Project not found',
            ]);
    }

    // ========== POST TESTS ==========

    public function test_it_can_create_a_new_contributor_request(): void
    {
        $response = $this->postJson("/api/codeconnect/{$this->project->id}/contributors", [
            'user_id' => $this->user->id,
            'programming_role' => 'Frontend Developer',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Contributor request created successfully',
                'data' => [
                    'user_id' => $this->user->id,
                    'programming_role' => 'Frontend Developer',
                    'list_project_id' => $this->project->id,
                    'status' => ContributorStatusEnum::Pending->value,
                ],
            ]);

        $this->assertDatabaseHas('contributors_list_project', [
            'user_id' => $this->user->id,
            'list_project_id' => $this->project->id,
            'status' => ContributorStatusEnum::Pending->value,
        ]);
    }

    public function test_post_contributor_returns_404_when_project_does_not_exist(): void
    {
        $response = $this->postJson("/api/codeconnect/99999/contributors", [
            'user_id' => $this->user->id,
            'programming_role' => 'Backend Developer',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Project not found',
            ]);
    }

    public function test_it_returns_400_when_user_is_already_a_contributor(): void
    {
        ContributorListProject::factory()->create([
            'user_id' => $this->user->id,
            'list_project_id' => $this->project->id,
        ]);

        $response = $this->postJson("/api/codeconnect/{$this->project->id}/contributors", [
            'user_id' => $this->user->id,
            'programming_role' => 'Fullstack Developer',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'User is already a contributor for this project',
            ]);
    }

    public function test_it_returns_422_when_user_id_does_not_exist(): void
    {
        $response = $this->postJson("/api/codeconnect/{$this->project->id}/contributors", [
            'user_id' => 99999,
            'programming_role' => 'Backend Developer',
        ]);

        $response->assertStatus(422);
    }

    public function test_it_returns_422_when_programming_role_is_invalid(): void
    {
        $response = $this->postJson("/api/codeconnect/{$this->project->id}/contributors", [
            'user_id' => $this->user->id,
            'programming_role' => 'Invalid Role',
        ]);

        $response->assertStatus(422);
    }

    public function test_it_returns_422_when_required_fields_are_missing(): void
    {
        $response = $this->postJson("/api/codeconnect/{$this->project->id}/contributors", []);

        $response->assertStatus(422);
    }

    // ========== DELETE TESTS ==========

    public function test_it_can_delete_a_contributor_from_project(): void
    {
        $contributor = ContributorListProject::factory()->create([
            'list_project_id' => $this->project->id,
        ]);

        $response = $this->deleteJson("/api/codeconnect/{$this->project->id}/contributors/{$contributor->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Contributor removed successfully',
            ]);

        $this->assertDatabaseMissing('contributors_list_project', [
            'id' => $contributor->id,
        ]);
    }

    public function test_delete_returns_404_when_contributor_does_not_exist(): void
    {
        $response = $this->deleteJson("/api/codeconnect/{$this->project->id}/contributors/99999");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Contributor not found',
            ]);
    }

    public function test_delete_returns_404_when_contributor_does_not_belong_to_project(): void
    {
        $otherProject = ListProjects::factory()->create();
        $contributor = ContributorListProject::factory()->create([
            'list_project_id' => $otherProject->id,
        ]);

        $response = $this->deleteJson("/api/codeconnect/{$this->project->id}/contributors/{$contributor->id}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Contributor not found',
            ]);
    }
}
