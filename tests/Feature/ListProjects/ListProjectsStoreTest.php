<?php

declare (strict_types= 1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\ListProjects;
use App\Models\ContributorListProject;
use App\Models\User;

class ListProjectsStoreTest extends TestCase
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

   

    public function test_method_store_endpoint():void{
        $response = $this->post('/api/listsProject/', [
            'title' => 'Proyecto Beta',
            'time_duration' => '1 mes',
            'lenguage_Backend' => 'Python',
            'lenguage_Frontend' => 'JavaScript'
        ]);
        $response->assertJsonFragment([
            'success' => true,
            'message' => 'Project created successfully',
        ]);
    }

    public function test_method_store_data():void{        
        $response = $this->post('/api/listsProject/', [
            'title' => 'Proyecto Beta',
            'time_duration' => '1 mes',
            'lenguage_Backend' => 'Python',
            'lenguage_Frontend' => 'JavaScript'
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'title' => 'Proyecto Beta',
            'time_duration' => '1 mes',
            'lenguage_Backend' => 'Python',
            'lenguage_Frontend' => 'JavaScript'
        ]);
        
    }

}

