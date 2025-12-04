<?php

declare (strict_types= 1);

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Enums\ContributorStatusEnum;
use App\Models\ListProjects;

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
            'programming_role' => $this->faker->randomElement(['Frontend Developer', 'Backend Developer', 'Fullstack Developer', 'Other']),
            'list_project_id' => ListProjects::factory(),
            'status' => ContributorStatusEnum::Pending->value,
        ];
    }
}
