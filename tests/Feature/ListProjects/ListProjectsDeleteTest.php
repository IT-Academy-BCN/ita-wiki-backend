<?php

declare (strict_types= 1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\ListProjects;
use App\Models\ContributorListProject;
use App\Models\User;

class ListProjectsDeleteTest extends TestCase
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

  

    public function test_delete_existing_project_successfully():void{
        $response = $this->delete("/api/listsProject/{$this->projectOne->id}");
  
        $response->assertJsonFragment([
            'success' => true,
            'message' => 'Project deleted successfully',
        ]);
    }

    public function test_delete_nonexistent_project_returns_404():void{
        $response = $this->delete("/api/listsProject/999");
    
        $response->assertJsonFragment([
            'success' => false,
            'message' => 'Project not found',
        ]);
    }


}

