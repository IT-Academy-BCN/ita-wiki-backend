<?php

declare(strict_types=1);

namespace Tests\Feature\UserTests;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_endpoint_profile(){
    $user = User::factory()->create();
    $this->actingAs($user, 'api');

    $response = $this->get('/api/user/profile');
    $response->assertStatus(200);
    }
    
    public function test_endpoint_index(){
    $user = User::factory()->create();
    $this->actingAs($user, 'api');

    $response = $this->get('/api/users');
    $response->assertStatus(200);
    }
    
    public function test_endpoint_destroy(){
    $user = User::factory()->create();
    $this->actingAs($user, 'api');

    $response = $this->delete('/api/users/' . $user->id . '/destroy');
    $response->assertStatus(200);
    }
    
    public function test_endpoint_updateRole(){
    $user = User::factory()->create();
    $this->actingAs($user, 'api');
    
    $response = $this->put('/api/users/' . $user->id . '/updateRole');
    $response->assertStatus(200);
    }


}