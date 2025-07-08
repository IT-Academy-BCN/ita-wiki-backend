<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TechnicalTestFactory extends Factory
{
    private const TECH_STACK = ['PHP', 'JavaScript', 'Java', 'React', 'TypeScript', 'Python', 'SQL'];

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'language' => $this->faker->randomElement(self::TECH_STACK),
            'description' => $this->faker->paragraph(),
            'tags' => $this->faker->randomElements(['backend', 'frontend', 'database', 'testing'], 2),
        ];
    }
        
}