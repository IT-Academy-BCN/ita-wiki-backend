<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Enums\LanguageEnum;

class TechnicalTestCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_technical_test_with_required_fields_only()
    {
   
        
        $user = $this->authenticateUserWithRole('mentor');

        $data = [
            'title' => 'Examen PHP Básico',
            'language' => LanguageEnum::PHP->value,
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
            'github_id' => $user->github_id,
        ]);
    }

    public function test_can_create_technical_test_with_all_fields()
    {
        $user = $this->authenticateUserWithRole('mentor');

        $data = [
            'title' => 'Examen Completo JavaScript',
            'language' => LanguageEnum::JavaScript->value,
            'description' => 'Descripción detallada del examen',
            'tags' => ['javascript', 'frontend', 'react'],
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('technical_tests', [
            'title' => 'Examen Completo JavaScript',
            'language' => LanguageEnum::JavaScript->value,
            'description' => 'Descripción detallada del examen',
            'github_id' => $user->github_id,
        ]);
    }

    public function test_student_cannot_create_technical_test(): void
    {
        $this->authenticateUserWithRole('student');

        $data = [
            'title' => 'Examen PHP Básico',
            'language' => LanguageEnum::PHP->value,
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_create_technical_test(): void
    {
        $data = [
            'title' => 'Examen PHP Básico',
            'language' => LanguageEnum::PHP->value,
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);
        $response->assertStatus(401);
    }

    public function test_title_is_required()
    {
        $this->authenticateUserWithRole('mentor');

        $data = [
            'language' => LanguageEnum::PHP->value,
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title']);
    }

    public function test_language_is_required()
    {
        $this->authenticateUserWithRole('mentor');

        $data = [
            'title' => 'Examen sin lenguaje',
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['language']);
    }

    public function test_title_must_be_between_5_and_255_characters()
    {
        $this->authenticateUserWithRole('mentor');

        // Título muy corto
        $response = $this->postJson(route('technical-tests.store'), [
            'title' => 'abc',
            'language' => LanguageEnum::PHP->value,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title']);

        // Título muy largo
        $response = $this->postJson(route('technical-tests.store'), [
            'title' => str_repeat('a', 256),
            'language' => LanguageEnum::PHP->value,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title']);
    }

    public function test_language_must_be_valid_enum()
    {
        $this->authenticateUserWithRole('mentor');

        $data = [
            'title' => 'Examen con lenguaje inválido',
            'language' => 'InvalidLanguage',
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['language']);
    }
}