<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\LanguageEnum;

class TechnicalTestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'language' => $this->faker->randomElement(LanguageEnum::values()),
            'description' => $this->faker->paragraph(),
            'tags' => $this->faker->randomElements(['backend', 'frontend', 'database', 'testing'], 2),
        ];
    }
        
}