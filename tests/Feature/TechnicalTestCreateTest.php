<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Enums\LanguageEnum;

class TechnicalTestCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_technical_test_with_required_fields_only()
    {
        $data = [
            'title' => 'Examen PHP Básico',
            'language' => LanguageEnum::PHP->value,
        ];

        $response = $this->postJson('/api/technicaltests', $data);

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
        ]);
    }

    public function test_can_create_technical_test_with_all_fields()
    {
        $data = [
            'title' => 'Examen Completo JavaScript',
            'language' => LanguageEnum::JavaScript->value,
            'description' => 'Descripción detallada del examen',
            'tags' => ['javascript', 'frontend', 'react'],
        ];

        $response = $this->postJson('/api/technicaltests', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('technical_tests', [
            'title' => 'Examen Completo JavaScript',
            'language' => LanguageEnum::JavaScript->value,
            'description' => 'Descripción detallada del examen',
        ]);
    }

    public function test_title_is_required()
    {
        $data = [
            'language' => LanguageEnum::PHP->value,
        ];

        $response = $this->postJson('/api/technicaltests', $data);

        $response->assertStatus(422)
                 ->assertJsonStructure(['title']);
    }

    public function test_language_is_required()
    {
        $data = [
            'title' => 'Examen sin lenguaje',
        ];

        $response = $this->postJson('/api/technicaltests', $data);

        $response->assertStatus(422)
                 ->assertJsonStructure(['language']);
    }

    public function test_title_must_be_between_5_and_255_characters()
    {
        // Título muy corto
        $response = $this->postJson('/api/technicaltests', [
            'title' => 'abc',
            'language' => LanguageEnum::PHP->value,
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['title']);

        // Título muy largo
        $response = $this->postJson('/api/technicaltests', [
            'title' => str_repeat('a', 256),
            'language' => LanguageEnum::PHP->value,
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['title']);
    }

    public function test_language_must_be_valid_enum()
    {
        $data = [
            'title' => 'Examen con lenguaje inválido',
            'language' => 'InvalidLanguage',
        ];

        $response = $this->postJson('/api/technicaltests', $data);

        $response->assertStatus(422)
                 ->assertJsonStructure(['language']);
    }
}