<?php

declare (strict_types= 1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListProjects>
 */
class ListProjectsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'title' => $this->faker->sentence(3),
            'time_duration' => $this->faker->word(),
            'language_Backend' => $this->faker->randomElement(['PHP', 'JavaScript', 'Python', 'Ruby', 'Java', 'C#', 'Go', 'Other']),
            'language_Frontend' => $this->faker->randomElement(['JavaScript', 'TypeScript', 'HTML', 'CSS', 'Other']),
        ];
    }
}
