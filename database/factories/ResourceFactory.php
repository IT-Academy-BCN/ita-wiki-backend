<?php

declare (strict_types= 1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resource>
 */
class ResourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $student = User::role('student')->inRandomOrder()->first() 
            ?? User::factory()->create()->assignRole('student');

        return [
            'github_id' => $student->github_id,
            'title' => fake()->sentence(),
            'description' => fake()->text(200),
            'url' => fake()->url(),
            'category' => fake()->randomElement([
                'Node', 'React', 'Angular', 'JavaScript', 'Java', 'Fullstack PHP', 'Data Science', 'BBDD'
            ]),
            'type' => fake()->randomElement(['Video', 'Cursos', 'Blog']),
            'tags' => fake()->randomElements([
                'docker', 'kubernetes', 'git', 'github', 'sql', 'mongodb', 'aws', 'azure'
            ], rand(1, 3)),
            'bookmark_count' => 0,
            'like_count' => 0,
        ];
    }
}