<?php

declare (strict_types= 1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContributorListProject>
 */
class ContributorListProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create();

        return [
            'user_id' => $user->id,
            'roleProgramming' => $this->faker->randomElement(['Backend', 'Frontend']),
            'list_project_id' => $this->faker->numberBetween(1, 10),
        ];
    }
}
