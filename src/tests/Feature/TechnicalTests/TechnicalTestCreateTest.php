<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Enums\LanguageEnum;
use App\Enums\DifficultyLevelEnum;
use App\Enums\TechnicalTestStatusEnum;
use App\Models\User;
use App\Models\TechnicalTest;
use Laravel\Sanctum\Sanctum;

class TechnicalTestCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Authenticate user for all tests in this class
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    public function test_can_create_technical_test_with_required_fields_only(): void
    {
        $githubId = 123456;

        $data = [
            'title' => 'Examen PHP Básico',
            'language' => LanguageEnum::PHP->value,
            'github_id' => $githubId,
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         'id',
                         'title',
                         'language',
                         'description',
                         'created_at',
                         'updated_at'
                     ]
                 ]);

        $this->assertDatabaseHas('technical_tests', [
            'title' => 'Examen PHP Básico',
            'language' => LanguageEnum::PHP->value,
            'description' => null,
            'github_id' => $githubId,
        ]);
    }

    public function test_can_create_technical_test_with_all_fields(): void
    {
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $data = [
            'title' => 'Examen Completo JavaScript',
            'language' => LanguageEnum::JavaScript->value,
            'description' => 'Descripción detallada del examen',
            'tags' => ['javascript', 'frontend', 'react'],
            'github_id' => $githubId,
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('technical_tests', [
            'title' => 'Examen Completo JavaScript',
            'language' => LanguageEnum::JavaScript->value,
            'description' => 'Descripción detallada del examen',
            'github_id' => $githubId,
        ]);
    }

    public function test_title_is_required(): void
    {
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $data = [
            'language' => LanguageEnum::PHP->value,
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title']);
    }

    public function test_language_is_required(): void
    {
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $data = [
            'title' => 'Examen sin lenguaje',
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['language']);
    }

    public function test_title_must_be_between_5_and_255_characters(): void
    {
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $response = $this->postJson(route('technical-tests.store'), [
            'title' => 'abc',
            'language' => LanguageEnum::PHP->value,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title']);

        $response = $this->postJson(route('technical-tests.store'), [
            'title' => str_repeat('a', 256),
            'language' => LanguageEnum::PHP->value,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title']);
    }

    public function test_language_must_be_valid_enum(): void
    {
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $data = [
            'title' => 'Examen con lenguaje inválido',
            'language' => 'InvalidLanguage',
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['language']);
    }

    public function test_can_create_technical_test_with_exercises(): void
    {
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $data = [
            'title' => 'Test con Exercises',
            'language' => LanguageEnum::PHP->value,
            'difficulty_level' => DifficultyLevelEnum::Medium->value,
            'duration' => 120,
            'state' => TechnicalTestStatusEnum::Draft->value,
            'github_id' => $githubId,
            'exercises' => [
                ['title' => 'Exercise 1', 'description' => 'Description 1'],
                ['title' => 'Exercise 2', 'description' => 'Description 2'],
                ['title' => 'Exercise 3'],
            ],
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);

        $response->assertStatus(201)
                 ->assertJsonPath('data.exercises.0.title', 'Exercise 1')
                 ->assertJsonPath('data.exercises.0.order', 1)
                 ->assertJsonPath('data.exercises.0.is_completed', false)
                 ->assertJsonPath('data.exercises.1.title', 'Exercise 2')
                 ->assertJsonPath('data.exercises.1.order', 2)
                 ->assertJsonPath('data.exercises.2.title', 'Exercise 3')
                 ->assertJsonPath('data.exercises.2.order', 3)
                 ->assertJsonPath('data.exercises.2.description', null);

        $this->assertDatabaseHas('exercises', [
            'title' => 'Exercise 1',
            'order' => 1,
            'is_completed' => false,
        ]);

        $this->assertCount(3, TechnicalTest::find($response->json('data.id'))->exercises);
    }

    public function test_state_defaults_to_draft_when_not_provided(): void
    {
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $data = [
            'title' => 'Test Default State',
            'language' => LanguageEnum::PHP->value,
            'github_id' => $githubId,
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);

        $response->assertStatus(201)
                 ->assertJsonPath('data.state', 'draft');
    }

    public function test_duration_must_be_at_least_1_minute(): void
    {
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $data = [
            'title' => 'Test Duration Min',
            'language' => LanguageEnum::PHP->value,
            'duration' => 0,
            'github_id' => $githubId,
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['duration']);
    }

    public function test_duration_must_not_exceed_480_minutes(): void
    {
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $data = [
            'title' => 'Test Duration Max',
            'language' => LanguageEnum::PHP->value,
            'duration' => 481,
            'github_id' => $githubId,
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['duration']);
    }

    public function test_difficulty_level_must_be_valid_enum(): void
    {
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $data = [
            'title' => 'Test Invalid Difficulty',
            'language' => LanguageEnum::PHP->value,
            'difficulty_level' => 'invalid',
            'github_id' => $githubId,
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['difficulty_level']);
    }

    public function test_state_must_be_valid_enum(): void
    {
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $data = [
            'title' => 'Test Invalid State',
            'language' => LanguageEnum::PHP->value,
            'state' => 'invalid',
            'github_id' => $githubId,
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['state']);
    }

    public function test_exercises_cannot_exceed_20_items(): void
    {
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $exercises = [];
        for ($i = 1; $i <= 21; $i++) {
            $exercises[] = ['title' => "Exercise $i"];
        }

        $data = [
            'title' => 'Test Max Exercises',
            'language' => LanguageEnum::PHP->value,
            'github_id' => $githubId,
            'exercises' => $exercises,
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['exercises']);
    }

    public function test_exercise_title_is_required(): void
    {
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $data = [
            'title' => 'Test Exercise Title Required',
            'language' => LanguageEnum::PHP->value,
            'github_id' => $githubId,
            'exercises' => [
                ['description' => 'Missing title'],
            ],
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['exercises.0.title']);
    }
}