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

class ListProjectsStoreTest extends TestCase
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

    public function test_method_store_successfully(): void
    {

        Sanctum::actingAs($this->userOne);

        $response = $this->postJson('/api/codeconnect/', [
            'title' => 'Proyecto Beta',
            'time_duration' => '1 mes',
            'language_backend' => LanguageEnum::Python->value,
            'language_frontend' => LanguageEnum::JavaScript->value,
        ]);

        $response->assertJsonFragment([
            'success' => true,
            'message' => 'Project created successfully',
        ]);

        $response->assertStatus(200);
    }

    public function test_method_datas_not_valid_language(): void
    {
        
        Sanctum::actingAs($this->userOne);

        $response = $this->postJson('/api/codeconnect/', [
            'title' => 'project invalid',
            'time_duration' => '1 month',
            'language_backend' => 'pokemon',
            'language_frontend' => LanguageEnum::JavaScript->value,
        ]);

        $response->assertJsonFragment([
            'success' => false,
            'message' => 'Invalid Backend language',
        ]);

        $response->assertStatus(400);
    }

    public function test_method_datas_error_required(): void
    {

        Sanctum::actingAs($this->userOne);

        $response = $this->postJson('/api/codeconnect/', [
            'title' => 'project invalid',
            'time_duration' => '',
            'language_backend' => LanguageEnum::Python->value,
            'language_frontend' => LanguageEnum::JavaScript->value,
        ]);
        $response->assertStatus(422);
    }

    public function test_store_requires_authentication(): void
    {

        $response = $this->postJson('/api/codeconnect/', [
            'title' => 'Proyecto Gamma',
            'time_duration' => '1 mes',
            'language_backend' => LanguageEnum::PHP->value,
            'language_frontend' => LanguageEnum::JavaScript->value,
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }
}
