<?php

declare (strict_types= 1);

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContributorListProject>
 */
class ContributorListProjectFactory extends Factory
{
    use HasFactory;

    public function definition(): array
    
    {
        $user = User::factory()->create();

        return [
            'user_id' => $user->id,
            'programmingRole' => $this->faker->randomElement(['Backend', 'Frontend']),
            'list_project_id' => $this->faker->numberBetween(1, 10),
        ];
    }
}
