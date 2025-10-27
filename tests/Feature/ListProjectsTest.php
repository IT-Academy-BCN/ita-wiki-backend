<?php

declare (strict_types= 1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\ListProjects;

class ListProjectsTest extends TestCase
{
   
    use RefreshDatabase;

     protected $projectOne;

     public function setUp(): void
     {
         parent::setUp();
         $this->projectOne = ListProjects::factory()->create([
                'id' => 1,
                'title' => 'Project Alpha',
                'time_duration' => '1 month',
                'lenguage_Backend' => 'PHP',
                'lenguage_Frontend' => 'JavaScript',
         ]);
     }

    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_method_show_devolve_dates(): void {
        $response = $this->get('/api/listsProject/1');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => 1,
                'title' => 'Project Alpha',
                'time_duration' => '1 month',
                'lenguage_Backend' => 'PHP',
                'lenguage_Frontend' => 'JavaScript',
            ],
            'message' => 'Project retrieved successfully'
        ]);
    }

    public function test_method_index_endpoint():void{
        $response = $this->get('/api/listsProject/');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => ['All projects list here...'],
            'message' => 'List of projects retrieved successfully'
        ]);
    }

    public function test_method_store_endpoint():void{
        $response = $this->post('/api/listsProject/');
        $response->assertJson([
            'success' => true,
            'message' => 'Project created successfully'
        ]);
    }

    public function test_method_update_endpoint():void{
        $response =$this->put('/api/listsProject/1');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Project updated successfully'
        ]);

    }

    public function test_method_delete_endpoint():void{
        $response =$this->delete('api/listsProject/1');
        $response->assertStatus(200);
        $response->assertJson([
            'success' =>true,
            'message' => 'Project deleted successfully',
        ]);

    }

}
