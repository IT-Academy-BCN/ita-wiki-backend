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
        
    // No happy path tests
    public function test_rejects_invalid_language(): void
    {
        $response = $this->get('api/technicaltests?language=InvalidLanguage');

        //file_put_contents('storage/app/cc/technical_test_rejects_invalid_language.json', json_encode($response->json(), JSON_PRETTY_PRINT));
        
        //dd($response->json());

        $response->assertStatus(422);
        $response->assertJsonFragment([
                'language' => ['El lenguaje seleccionado no es válido.']
        ]);
    }

    public function test_rejects_malitious_sql_injection_attemps(): void
    {
        $response = $this->get("api/technicaltests?search=' OR 1=1--");

        $response->assertStatus(200);
    }

    public function test_handles_extremely_long_search_string(): void
    {
        $longString = str_repeat('a', 1000);
        
        $response = $this->get("api/technicaltests?search={$longString}");

        $response->assertStatus(422);
    }

    public function test_handles_special_characters_in_search(): void
    {
        TechnicalTest::factory()->create(['title' => 'Test with special chars: @#$%']);
        
        $response = $this->get('api/technicaltests?search=@#$%');

        $response->assertStatus(200);
    }

    public function test_rejects_invalid_date_formats(): void
{
    $response = $this->get('api/technicaltests?date_from=invalid-date');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['date_from']);
}

public function test_rejects_date_from_after_date_to(): void
{
    $response = $this->get('api/technicaltests?date_from=2024-12-31&date_to=2024-01-01');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['date_from']);
}

    public function test_rejects_html_injection_in_search(): void
    {
        $response = $this->get('api/technicaltests?search=<script>alert("xss")</script>');

        $response->assertStatus(422);
    
    }

    public function test_rejects_empty_string_parameters(): void
    {
        $response = $this->get('api/technicaltests?search=&language=&description=');

        $response->assertStatus(422);
    }

    public function test_rejects_invalid_json_in_tag_filter(): void
    {
        $response = $this->get('api/technicaltests?tag=invalid-json');

        $response->assertStatus(422);
    }

}