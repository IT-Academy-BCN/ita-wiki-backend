<?php

declare (strict_types= 1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\ListProjects;
use App\Models\ContributorListProject;
use App\Models\User;

class ListProjectsShowTest extends TestCase
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
            'lenguage_Backend' => 'PHP',
            'lenguage_Frontend' => 'JavaScript',
        ]);
            
        $this->contributorOne = ContributorListProject::factory()->create([
            'user_id' => $this->userOne->id,
            'roleProgramming' => 'Backend Developer',
            'list_project_id' => $this->projectOne->id,
        ]);
     }

    

    public function test_method_show_endpoint(): void {
        $response = $this->get("/api/listsProject/{$this->projectOne->id}");
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Project retrieved successfully'
        ]);
    }
     public function test_method_show_data():void{
        $response = $this->get("/api/listsProject/{$this->projectOne->id}");
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'title' => $this->projectOne->title,
            'time_duration' => $this->projectOne->time_duration,
            'lenguage_Backend' => $this->projectOne->lenguage_Backend,
            'lenguage_Frontend' => $this->projectOne->lenguage_Frontend,
            'contributors' => [
                [
                    'name' => $this->contributorOne->user->name,
                    'roleProgramming' => $this->contributorOne->roleProgramming,
                ]
            ],
        ]);
    }

    public function test_method_show_data_contributors():void{
        $response = $this->get("/api/listsProject/{$this->projectOne->id}");
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'contributors' => [
                [
                    'name' => $this->contributorOne->user->name,
                    'roleProgramming' => $this->contributorOne->roleProgramming,
                ]
            ],
        ]);
    }

    public function test_method_show_not_found(): void{
        $response = $this->get('/api/listsProject/999');
        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Project not found'
        ]);
    }

   

}
