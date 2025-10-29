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
            'language_Backend' => 'PHP',
            'language_Frontend' => 'JavaScript',
        ]);
            
        $this->contributorOne = ContributorListProject::factory()->create([
            'user_id' => $this->userOne->id,
            'programmingRole' => 'Backend Developer',
            'list_project_id' => $this->projectOne->id,
        ]);
    }

    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

   
    public function test_method_update_endpoint():void{
        $response = $this->put('/api/listsProject/1', [
            'title' => 'Project Alpha',
            'time_duration' => '2 months',
            'language_Backend' => 'PHP',
            'language_Frontend' => 'TypeScript'
        ]);
        $response->assertJsonFragment([
            'success' => true,
            'message' => 'Project updated successfully',
        ]);
    }

    public function test_method_update_not_found():void{
        $response = $this->put('/api/listsProject/999', [
            'title' => 'Non-existent Project',
            'time_duration' => '3 months',
            'language_Backend' => 'Ruby',
            'language_Frontend' => 'Elm'
        ]);
        $response->assertStatus(404);
        $response->assertJsonFragment([
            'success' => false,
            'message' => 'Project not found',
        ]);

    }

    public function test_method_update_data():void{
        $response = $this->put("/api/listsProject/{$this->projectOne->id}", [
            'title' => 'Project Alpha Updated',
            'time_duration' => '2 months',
            'language_Backend' => 'PHP',
            'language_Frontend' => 'TypeScript'
        ]);
        $response->assertJsonFragment([
            'success' => true,
            'message' => 'Project updated successfully',
        ]);
    }

}

