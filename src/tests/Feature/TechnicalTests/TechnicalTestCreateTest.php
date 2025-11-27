<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Enums\LanguageEnum;
use App\Models\User;
use App\Models\TechnicalTest;

class TechnicalTestCreateTest extends TestCase
{
    use RefreshDatabase;

 

    public function test_can_create_technical_test_with_required_fields_only()
    {
   
        
        //$user = $this->authenticateUserWithRole('mentor');
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

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

    public function test_can_create_technical_test_with_all_fields()
    {
        //$user = $this->authenticateUserWithRole('mentor');
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

   
    public function test_title_is_required()
    {
        //$this->authenticateUserWithRole('mentor');
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $data = [
            'language' => LanguageEnum::PHP->value,
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title']);
    }

    public function test_language_is_required()
    {
        //$this->authenticateUserWithRole('mentor');
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $data = [
            'title' => 'Examen sin lenguaje',
        ];

        $response = $this->postJson(route('technical-tests.store'), $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['language']);
    }

    public function test_title_must_be_between_5_and_255_characters()
    {
       // $this->authenticateUserWithRole('mentor');
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

    public function test_language_must_be_valid_enum()
    {
       // $this->authenticateUserWithRole('mentor');
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
}