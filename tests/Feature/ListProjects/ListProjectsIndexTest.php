<?php

declare (strict_types= 1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\ListProjects;
use App\Models\ContributorListProject;
use App\Models\User;

class ListProjectsIndexTest extends TestCase
{
     use RefreshDatabase;

        protected $projectOne;
        protected $projectTwo;
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

        $this->projectTwo = ListProjects::factory()->create([
            'id' => 2,
            'title' => 'Project Beta',
            'time_duration' => '2 months',
            'language_Backend' => 'Python',
            'language_Frontend' => 'HTML',
        ]);

        ListProjects::factory(3)->create();
            
        $this->contributorOne = ContributorListProject::factory()->create([
            'user_id' => $this->userOne->id,
            'programmingRole' => 'Backend Developer',
            'list_project_id' => $this->projectOne->id,
        ]);
     }


    public function test_method_index_endpoint():void{
        $response = $this->get('/api/listsProject');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => true,
        ]);
   }

    public function test_method_count_projects():void {
        $response = $this->get('/api/listsProject');
        $response->assertJsonCount(5, 'data');
        $response->assertStatus(200);
    }
    
    public function test_index_returns_successfully():void{
        $response = $this->get('/api/listsProject');
        $response->assertJsonFragment([
            'title' => $this->projectOne->title,
            'time_duration' => $this->projectOne->time_duration,
            'language_Backend' => $this->projectOne->language_Backend,
            'language_Frontend' => $this->projectOne->language_Frontend,
            'contributors' => [
                [
                    'name' => $this->contributorOne->user->name,
                    'programmingRole' => $this->contributorOne->programmingRole,
                ]
            ],
            'title' => $this->projectTwo->title,
            'time_duration' => $this->projectTwo->time_duration,
            'language_Backend' => $this->projectTwo->language_Backend,
            'language_Frontend' => $this->projectTwo->language_Frontend,
            'contributors' => [],
          
          
        ]);
    }



    
}

