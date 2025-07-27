<?php

declare (strict_types= 1);

namespace Database\Factories;

use App\Models\User;
use App\Models\OldRole;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

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
    {   /*Antiguo sistema de gestion de permisos. Elminar cuando Spatie esté implementado
        * 
            $role = OldRole::where('role', '=', 'student')
            ->inRandomOrder()
            ->first();
        */
        $validTags = Tag::all()->pluck('name')->toArray();

        $createdAtDate = $this->faker->dateTimeBetween('-2 years', 'now');

        $updatedAtDate = $this->faker->boolean(50)? $createdAtDate : $this->faker->dateTimeBetween($createdAtDate, 'now');

        return [
            'github_id' => User::factory()->create()->github_id,
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->sentence(6),
            'url' => $this->faker->url(),
            'category' => $this->faker->randomElement(['Node', 'React', 'Angular', 'JavaScript', 'Java', 'Fullstack PHP', 'Data Science', 'BBDD']),
            'tags' => $this->faker->randomElements($validTags, $this->faker->numberBetween(1, 5)),
            'type' => $this->faker->randomElement(['Video', 'Cursos', 'Blog']),
            'created_at' => $createdAtDate,
            'updated_at' => $updatedAtDate,
        ];
    }
}