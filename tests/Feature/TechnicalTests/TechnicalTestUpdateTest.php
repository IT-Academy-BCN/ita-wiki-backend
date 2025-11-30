<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Enums\LanguageEnum;
use App\Enums\DifficultyLevelEnum;
use App\Enums\TechnicalTestStatusEnum;
use App\Models\User;
use App\Models\TechnicalTest;
use App\Models\Exercise;

class TechnicalTestUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_technical_test_with_new_exercises()
    {
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $technicalTest = TechnicalTest::factory()->create([
            'github_id' => $githubId,
            'language' => LanguageEnum::PHP->value,
        ]);

        Exercise::factory()->count(2)->create([
            'technical_test_id' => $technicalTest->id,
        ]);

        $data = [
            'title' => $technicalTest->title,
            'language' => LanguageEnum::PHP->value,
            'exercises' => [
                ['title' => 'New Exercise 1'],
                ['title' => 'New Exercise 2'],
                ['title' => 'New Exercise 3'],
            ],
        ];

        $response = $this->putJson(route('technical-tests.update', $technicalTest), $data);

        $response->assertStatus(200);

        $technicalTest->refresh();
        $this->assertCount(3, $technicalTest->exercises);
        $this->assertEquals('New Exercise 1', $technicalTest->exercises[0]->title);
    }

    public function test_updating_without_exercises_field_preserves_existing_exercises()
    {
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $technicalTest = TechnicalTest::factory()->create([
            'github_id' => $githubId,
            'language' => LanguageEnum::PHP->value,
        ]);

        $existingExercises = Exercise::factory()->count(2)->create([
            'technical_test_id' => $technicalTest->id,
        ]);

        $data = [
            'title' => 'Updated Title',
            'language' => LanguageEnum::PHP->value,
        ];

        $response = $this->putJson(route('technical-tests.update', $technicalTest), $data);

        $response->assertStatus(200);

        $technicalTest->refresh();
        $this->assertCount(2, $technicalTest->exercises);
        $this->assertEquals($existingExercises[0]->id, $technicalTest->exercises[0]->id);
    }

    public function test_updating_with_empty_exercises_array_deletes_all_exercises()
    {
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $technicalTest = TechnicalTest::factory()->create([
            'github_id' => $githubId,
            'language' => LanguageEnum::PHP->value,
        ]);

        Exercise::factory()->count(3)->create([
            'technical_test_id' => $technicalTest->id,
        ]);

        $data = [
            'title' => $technicalTest->title,
            'language' => LanguageEnum::PHP->value,
            'exercises' => [],
        ];

        $response = $this->putJson(route('technical-tests.update', $technicalTest), $data);

        $response->assertStatus(200);

        $technicalTest->refresh();
        $this->assertCount(0, $technicalTest->exercises);
    }

    public function test_can_update_difficulty_level_and_duration()
    {
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $technicalTest = TechnicalTest::factory()->create([
            'github_id' => $githubId,
            'language' => LanguageEnum::PHP->value,
            'difficulty_level' => DifficultyLevelEnum::Easy->value,
            'duration' => 60,
        ]);

        $data = [
            'title' => $technicalTest->title,
            'language' => LanguageEnum::PHP->value,
            'difficulty_level' => DifficultyLevelEnum::Hard->value,
            'duration' => 240,
        ];

        $response = $this->putJson(route('technical-tests.update', $technicalTest), $data);

        $response->assertStatus(200)
                 ->assertJsonPath('data.difficulty_level', 'hard')
                 ->assertJsonPath('data.duration', 240);

        $this->assertDatabaseHas('technical_tests', [
            'id' => $technicalTest->id,
            'difficulty_level' => 'hard',
            'duration' => 240,
        ]);
    }

    public function test_can_update_state()
    {
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $technicalTest = TechnicalTest::factory()->create([
            'github_id' => $githubId,
            'state' => TechnicalTestStatusEnum::Draft->value,
        ]);

        $data = [
            'title' => $technicalTest->title,
            'language' => $technicalTest->language,
            'state' => TechnicalTestStatusEnum::Published->value,
        ];

        $response = $this->putJson(route('technical-tests.update', $technicalTest), $data);

        $response->assertStatus(200)
                 ->assertJsonPath('data.state', 'published');

        $this->assertDatabaseHas('technical_tests', [
            'id' => $technicalTest->id,
            'state' => 'published',
        ]);
    }
}
