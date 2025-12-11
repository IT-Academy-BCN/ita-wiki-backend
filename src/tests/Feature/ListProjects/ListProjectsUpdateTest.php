<?php

declare (strict_types= 1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\ListProjects;
use App\Models\ContributorListProject;
use App\Models\User;

class ListProjectsUpdateTest extends TestCase
{
     use RefreshDatabase;

        protected $projectOne;
        protected $contributorOne;

    public function setUp(): void
    {
        parent::setUp();

        $this->userOne = User::factory()->create(['id' => 1]);

        $this->projectOne = ListProjects::factory()->create([
            'id' => 1,
            'title' => 'Project Alpha',
            'time_duration' => '1 month',
            'language_backend' => 'PHP',
            'language_frontend' => 'JavaScript',
        ]);
            
        $this->contributorOne = ContributorListProject::factory()->create([
            'user_id' => $this->userOne->id,
            'programming_role' => 'Backend Developer',
            'list_project_id' => $this->projectOne->id,
        ]);
    }


    public function test_method_update_successfully():void{
        $response = $this->putJson('/api/codeconnect/1', [
            'title' => 'Project Alpha',
            'time_duration' => '2 months',
            'language_backend' => 'PHP',
            'language_frontend' => 'TypeScript'
        ]);
        $response->assertJsonFragment([
            'success' => true,
            'message' => 'Project updated successfully',
        ]);
        $response->assertStatus(200);
    }

    public function test_method_update_error_404():void{
        $response = $this->putJson('/api/codeconnect/999', [
            'title' => 'Non-existent Project',
            'time_duration' => '3 months',
            'language_backend' => 'Python',
            'language_frontend' => 'React'
        ]);
        $response->assertStatus(404);
        $response->assertJsonFragment([
            'success' => false,
            'message' => 'Project not found',
        ]);

    }

     public function test_method_datas_not_valid_language():void{
        $response = $this->postJson('/api/codeconnect/',[
            'title' => 'project invalid',
            'time_duration' => '1 month',
            'language_backend' => 'pokemon',
            'language_frontend' => 'JavaScript'
        ]);
            $response->assertJsonFragment([
                'success' => false,
                'message' => 'Invalid Backend language',
        ]);
        $response->assertStatus(400);
    }

    public function test_method_datas_error_required():void{
        $response = $this->postJson('/api/codeconnect/',[
            'title' => 'project invalid',
            'time_duration' => '',
            'language_backend' => 'Python',
            'language_frontend' => 'JavaScript'
        ]);
        $response->assertStatus(422);
    }


}

