<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TechnicalTestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'language' => $this->faker->randomElement(['PHP', 'JavaScript', 'Java', 'React', 'TypeScript', 'Python', 'SQL']),
            'description' => $this->faker->paragraph(),
            'tags' => $this->faker->randomElements(['backend', 'frontend', 'database', 'testing'], 2),
        ];
    }
        
}