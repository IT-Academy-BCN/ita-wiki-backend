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
            'language_Backend' => 'PHP',
            'language_Frontend' => 'JavaScript',
        ]);
            
        $this->contributorOne = ContributorListProject::factory()->create([
            'user_id' => $this->userOne->id,
            'programmingRole' => 'Backend Developer',
            'list_project_id' => $this->projectOne->id,
        ]);
     }

   

    public function test_method_store_successfully():void{
        $response = $this->post('/api/listsProject/', [
            'title' => 'Proyecto Beta',
            'time_duration' => '1 mes',
            'language_Backend' => 'Python',
            'language_Frontend' => 'JavaScript'
        ]);
        $response->assertJsonFragment([
            'success' => true,
            'message' => 'Project created successfully',
        ]);
    }

    public function test_method_datas_not_valid_language():void{
        $response = $this->post('/api/listsProject/',[
            'title' => 'project invalid',
            'time_duration' => '1 month',
            'language_Backend' => 'pokemon',
            'language_Frontend' => 'JavaScript'
        ]);
            $response->assertJsonFragment([
                'success' => false,
                'message' => 'Invalid Backend language',
        ]);
    }

    public function test_method_datas_error_required():void{
        $response = $this->post('/api/listsProject/',[
            'title' => 'project invalid',
            'time_duration' => '',
            'language_Backend' => 'Python',
            'language_Frontend' => ''
        ]);
            $response->assertJsonFragment([
                'message' => 'The time duration field is required. (and 1 more error)',
        ]);
        
    }

}
