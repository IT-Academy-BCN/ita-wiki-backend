<?php

namespace Tests\Feature;

use App\Models\TechnicalTest;
use Database\Factories\TechnicalTestFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicalTestIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        TechnicalTest::truncate();
    }

    public function test_can_get_technical_test_list()
    {
        TechnicalTest::factory(3)->create();

       $response = $this->get('/api/technicaltests');
       file_put_contents('storage/app/cc/technical_test.json', json_encode($response->json(), JSON_PRETTY_PRINT));
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
             ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'github_id',
                        'node_id',
                        'title',
                        'language',
                        'description',
                        'file_path',
                        'file_original_name',
                        'file_size',
                        'tags',
                        'bookmark_count',
                        'like_count',
                        'created_at',
                        'updated_at',
                        'deleted_at'
                    ]
                ],
                'filters' => [
                    'available_languages',
                    'applied_filters'
                ],
            ]);
    }

    public function test_can_filter_by_language(): void
    {
        TechnicalTest::factory()->create(['language' => 'PHP']);
        TechnicalTest::factory()->create(['language' => 'Python']);

        $response = $this->get('api/technicaltests?language=PHP');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_by_multiple_parameters(): void
    {
        TechnicalTest::factory()->create([
            'language' => 'PHP',
            'title' => 'PHP Advanced Test',
            'tags' => ['php', 'backend']
        ]);

        TechnicalTest::factory()->create([
            'language' => 'Python',
            'title' => 'Python Basic Test'
        ]);

        $response = $this->get('/api/technicaltests?language=PHP&search=Advanced');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_returns_empty_when_no_matches(): void
    {
        TechnicalTest::factory()->create(['language' => 'PHP']);

        $response = $this->get('api/technicaltests?language=JavaScript');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data')
            ->assertJson([
            'message' => 'No se han encontrado tests con esos criterios'
        ]);
    }

    public function test_returns_all_when_no_filters(): void
    {
        TechnicalTest::factory(5)->create();

        $response = $this->get('api/technicaltests');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }
        
}