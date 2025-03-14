<?php
declare(strict_types = 1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Role;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        do {
            $githubId = $this->faker->numberBetween(1, 10000000);
        } while (Role::where('github_id', $githubId)->exists());
        return [
            'github_id' => $githubId,
            'role' => $this->faker->randomElement(['admin', 'mentor', 'student']),
        ];
    }
}
