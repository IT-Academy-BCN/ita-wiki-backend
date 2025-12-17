<?php

namespace Tests\Feature\Exercises;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\TechnicalTest;
use App\Models\Exercise;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class ExerciseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Authenticate user for all tests in this class
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    public function test_can_create_exercise_with_required_fields()
    {
        $technicalTest = TechnicalTest::factory()->create();

        $data = [
            'technical_test_id' => $technicalTest->id,
            'title' => 'Crear clase Person'
        ];

        $response = $this->postJson(route('exercises.store'), $data);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         'id',
                         'technical_test_id',
                         'title',
                         'created_at',
                         'updated_at'
                     ]
                 ])
                 ->assertJsonFragment([
                     'title' => 'Crear clase Person'
                 ]);

        $this->assertDatabaseHas('exercises', [
            'technical_test_id' => $technicalTest->id,
            'title' => 'Crear clase Person'
        ]);
    }

    public function test_can_create_exercise_with_all_fields()
    {
        $technicalTest = TechnicalTest::factory()->create();

        $data = [
            'technical_test_id' => $technicalTest->id,
            'title' => 'Implementar herencia',
            'description' => 'Crie uma classe Student que herda de Person',
            'order' => 2
        ];

        $response = $this->postJson(route('exercises.store'), $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('exercises', [
            'technical_test_id' => $technicalTest->id,
            'title' => 'Implementar herencia',
            'description' => 'Crie uma classe Student que herda de Person',
            'order' => 2
        ]);
    }

    public function test_can_list_all_exercises()
    {
        $technicalTest = TechnicalTest::factory()->create();
        Exercise::factory()->count(3)->create(['technical_test_id' => $technicalTest->id]);

        $response = $this->getJson(route('exercises.index'));

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    public function test_can_filter_exercises_by_technical_test()
    {
        $test1 = TechnicalTest::factory()->create();
        $test2 = TechnicalTest::factory()->create();

        Exercise::factory()->count(2)->create(['technical_test_id' => $test1->id]);
        Exercise::factory()->count(3)->create(['technical_test_id' => $test2->id]);

        $response = $this->getJson(route('exercises.index', ['technical_test_id' => $test1->id]));

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data');
    }

    public function test_can_show_exercise()
    {
        $exercise = Exercise::factory()->create();

        $response = $this->getJson(route('exercises.show', $exercise->id));

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'title' => $exercise->title
                 ]);
    }

    public function test_can_update_exercise()
    {
        $exercise = Exercise::factory()->create();

        $data = [
            'title' => 'Título atualizado',
            'is_completed' => true
        ];

        $response = $this->putJson(route('exercises.update', $exercise->id), $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('exercises', [
            'id' => $exercise->id,
            'title' => 'Título atualizado',
            'is_completed' => true
        ]);
    }

    public function test_can_delete_exercise()
    {
        $exercise = Exercise::factory()->create();

        $response = $this->deleteJson(route('exercises.destroy', $exercise->id));

        $response->assertStatus(200);

        $this->assertDatabaseMissing('exercises', [
            'id' => $exercise->id
        ]);
    }

    public function test_cannot_create_exercise_without_title()
    {
        $technicalTest = TechnicalTest::factory()->create();

        $data = [
            'technical_test_id' => $technicalTest->id
        ];

        $response = $this->postJson(route('exercises.store'), $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title']);
    }

    public function test_cannot_create_exercise_with_invalid_technical_test()
    {
        $data = [
            'technical_test_id' => 99999,
            'title' => 'Test'
        ];

        $response = $this->postJson(route('exercises.store'), $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['technical_test_id']);
    }

    public function test_title_must_be_at_least_3_characters()
    {
        $technicalTest = TechnicalTest::factory()->create();

        $data = [
            'technical_test_id' => $technicalTest->id,
            'title' => 'AB'
        ];

        $response = $this->postJson(route('exercises.store'), $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title']);
    }
}
