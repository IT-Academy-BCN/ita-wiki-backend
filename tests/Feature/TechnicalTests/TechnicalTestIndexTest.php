<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\TechnicalTest;
use Database\Factories\TechnicalTestFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Enums\LanguageEnum;
use App\Enums\DifficultyLevelEnum;
use App\Enums\TechnicalTestStatusEnum;

class TechnicalTestIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        TechnicalTest::truncate();
    }

    public function testCanGetTechnicalTestListWithCorrectStructure(): void
    {
        TechnicalTest::factory(3)->create();

        $response = $this->get(route('technical-tests.index'));;

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'github_id',
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

    public function testIndexReturnsCorrectValues(): void
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

        $response = $this->get(route('technical-tests.index'));

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

    public function testCanFilterByLanguage(): void
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

        $response = $this->get(route('technical-tests.index', ['language' => LanguageEnum::PHP->value]));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'title' => 'Test PHP',
                'language' => LanguageEnum::PHP->value,
                'description' => 'Test de PHP.',
            ]);  
    }

    public function testCanFilterByMultipleParameters(): void
    {
        TechnicalTest::factory()->create([
            'language' => LanguageEnum::PHP->value,
            'title' => 'PHP Advanced Test',
        ]);

        TechnicalTest::factory()->create([
            'language' => LanguageEnum::Python->value,
            'title' => 'Python Basic Test'
        ]);

        $response = $this->get(route('technical-tests.index', ['language' => LanguageEnum::PHP->value, 'search' => 'Advanced']));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'PHP Advanced Test']);
    }

    public function testReturnsEmptyWhenNoMatches(): void
    {
        TechnicalTest::factory()->create(['language' => LanguageEnum::PHP->value]);

        $response = $this->get(route('technical-tests.index', ['language' => LanguageEnum::JavaScript->value]));


        $response->assertStatus(200)
            ->assertJsonCount(0, 'data')
            ->assertJson([
            'message' => 'No se han encontrado tests con esos criterios'
        ]);
    }

    public function testReturnsAllWhenNoFilters(): void
    {
        TechnicalTest::factory(5)->create();

        $response = $this->get(route('technical-tests.index'));

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }
    
   
    public function testRejectsInvalidLanguage(): void
    {
        $invalidLanguage = 'InvalidLanguage';
        $this->assertFalse(in_array($invalidLanguage, array_column(LanguageEnum::cases(), 'value')));

        $response = $this->getJson(route('technical-tests.index', ['language' => $invalidLanguage]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['language']);
    }

    public function testRejectsExtremelyLongSearchString(): void
    {
        $longString = str_repeat('a', 1000);

        $response = $this->getJson(route('technical-tests.index', ['search' => $longString]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['search']);
    }

    public function testHandlesSpecialCharactersInSearch(): void
    {
        TechnicalTest::factory()->create(['title' => 'Test with special chars: @#$%']);
        
        $response = $this->get(route('technical-tests.index', ['search' => '@#$%']));

        $response->assertStatus(200);
    }

    public function test_can_filter_by_difficulty_level(): void
    {
        TechnicalTest::factory()->create([
            'difficulty_level' => DifficultyLevelEnum::Easy->value,
        ]);
        TechnicalTest::factory()->create([
            'difficulty_level' => DifficultyLevelEnum::Hard->value,
        ]);
        TechnicalTest::factory()->create([
            'difficulty_level' => DifficultyLevelEnum::Hard->value,
        ]);

        $response = $this->get(route('technical-tests.index', ['difficulty_level' => 'hard']));

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_by_state(): void
    {
        TechnicalTest::factory()->create([
            'state' => TechnicalTestStatusEnum::Draft->value,
        ]);
        TechnicalTest::factory()->create([
            'state' => TechnicalTestStatusEnum::Published->value,
        ]);
        TechnicalTest::factory()->create([
            'state' => TechnicalTestStatusEnum::Published->value,
        ]);

        $response = $this->get(route('technical-tests.index', ['state' => 'published']));

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data');
    }

    public function test_index_returns_available_filters(): void
    {
        $response = $this->get(route('technical-tests.index'));

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'filters' => [
                         'available_languages',
                         'available_difficulty_levels',
                         'available_states',
                         'applied_filters',
                     ],
                 ]);
    }

    public function test_index_includes_exercises_in_response(): void
    {
        $technicalTest = TechnicalTest::factory()->create();
        $technicalTest->exercises()->create([
            'title' => 'Exercise 1',
            'order' => 1,
        ]);

        $response = $this->get(route('technical-tests.index'));

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'exercises',
                         ],
                     ],
                 ])
                 ->assertJsonCount(1, 'data.0.exercises');
    }

}
