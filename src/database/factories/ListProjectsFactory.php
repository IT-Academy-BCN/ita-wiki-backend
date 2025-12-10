<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LanguageEnum;
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
        $languages = LanguageEnum::values();

        return [
            'title' => $this->faker->sentence(3),
            'time_duration' => $this->faker->word(),
            'language_backend' => $this->faker->randomElement($languages),
            'language_frontend' => $this->faker->randomElement($languages),
        ];
    }
}
