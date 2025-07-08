<?php

namespace Tests\Feature;

use App\Models\TechnicalTest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicalTestIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_technical_test_list()
    {
        TechnicalTest::truncate();

        TechnicalTest::factory(3)->create();

        $response = $this->get('/api/technicaltests');
       
        //dd($response->json());

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    
}