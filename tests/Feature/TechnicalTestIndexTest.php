<?php

namespace Tests\Feature;

use App\Models\TechnicalTest;
use App\Models\BookmarkNode;
use Database\Factories\TechnicalTestFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Enums\LanguageEnum;


class TechnicalTestIndexTest extends TestCase
{
    use RefreshDatabase;


    protected function setUp(): void
    {
        parent::setUp();
        TechnicalTest::truncate();
        BookmarkNode::truncate();
    }

    public function test_can_get_technical_test_list_with_correct_structure()
    {
        TechnicalTest::factory(3)->create();

       $response = $this->get('/api/technical-tests');  
       
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

    public function test_index_returns_correct_values(): void
    {
        TechnicalTest::factory()->create([
            'title' => 'Test PHP',
            'language' => LanguageEnum::PHP->value,
            'description' => 'Test de PHP.',
        ]);

        TechnicalTest::factory()->create([
            'title' => 'Test Python',
            'language' => LanguageEnum::Python->value,
            'description' => 'Test de Python.',
        ]);

        $response = $this->get('/api/technical-tests');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'title' => 'Test PHP',
                'language' => LanguageEnum::PHP->value,
                'description' => 'Test de PHP.',
            ])
            ->assertJsonFragment([               
                'title' => 'Test Python',
                'language' => LanguageEnum::Python->value,
                'description' => 'Test de Python.',
            ]);
    }

    public function test_can_filter_by_language(): void
    {
        TechnicalTest::factory()->create([
                'title' => 'Test PHP',
                'language' => LanguageEnum::PHP->value,
                'description' => 'Test de PHP.'
        ]);
        TechnicalTest::factory()->create([
            'title' => 'Test Python',
            'language' => LanguageEnum::Python->value,
            'description' => 'Test de Python.',
        ]);

        $response = $this->get('api/technical-tests?language=' . LanguageEnum::PHP->value);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'title' => 'Test PHP',
                'language' => LanguageEnum::PHP->value,
                'description' => 'Test de PHP.',
            ]);  
    }

    public function test_can_filter_by_multiple_parameters(): void
    {
        TechnicalTest::factory()->create([
            'language' => LanguageEnum::PHP->value,
            'title' => 'PHP Advanced Test',
        ]);

        TechnicalTest::factory()->create([
            'language' => LanguageEnum::Python->value,
            'title' => 'Python Basic Test'
        ]);

        $response = $this->get('/api/technical-tests?language=' . LanguageEnum::PHP->value . '&search=Advanced');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'PHP Advanced Test']);
    }

    public function test_returns_empty_when_no_matches(): void
    {
        TechnicalTest::factory()->create(['language' => LanguageEnum::PHP->value]);

        $response = $this->get('api/technical-tests?language=' . LanguageEnum::JavaScript->value);


        $response->assertStatus(200)
            ->assertJsonCount(0, 'data')
            ->assertJson([
            'message' => 'No se han encontrado tests con esos criterios'
        ]);
    }

    public function test_returns_all_when_no_filters(): void
    {
        TechnicalTest::factory(5)->create();

        $response = $this->get('api/technical-tests');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }
    
    // No happy path tests
    public function test_rejects_invalid_language(): void
    {
        $invalidLanguage = 'InvalidLanguage';
        $this->assertFalse(in_array($invalidLanguage, array_column(LanguageEnum::cases(), 'value')));


        $response = $this->get('api/technical-tests?language=' . $invalidLanguage);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'language' => ['El lenguaje seleccionado no es válido.']
        ]);
    }

    
    public function test_rejects_extremely_long_search_string(): void
    {
        $longString = str_repeat('a', 1000);
        
        $response = $this->get("api/technical-tests?search={$longString}");

        $response->assertStatus(422);
    }

    public function test_handles_special_characters_in_search(): void
    {
        TechnicalTest::factory()->create(['title' => 'Test with special chars: @#$%']);
        
        $response = $this->get('api/technical-tests?search=@#$%');

        $response->assertStatus(200);
    }

}