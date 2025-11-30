<?php

namespace Database\Factories;

use App\Models\TechnicalTest;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExerciseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'technical_test_id' => TechnicalTest::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'order' => $this->faker->numberBetween(1, 10),
            'is_completed' => false,
        ];
    }
}
