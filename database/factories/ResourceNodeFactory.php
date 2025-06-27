<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RoleNode;
use App\Models\TagNode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ResourceNode>
 */
class ResourceNodeFactory extends Factory
{
    public function definition(): array
    {
        // Pick a random student node_id
        $roleNode = RoleNode::where('role', 'student')->inRandomOrder()->first();

        // If no student exists, create one (to avoid null errors in seeding)
        if (!$roleNode) {
            $roleNode = RoleNode::factory()->create(['role' => 'student']);
        }

        // Get all valid tag names (or fallback to some defaults)
        $validTags = TagNode::all()->pluck('name')->toArray();

        if (empty($validTags)) {
            $validTags = ['kubernetes', 'sql', 'azure', 'docker', 'laravel'];
        }

        $createdAtDate = $this->faker->dateTimeBetween('-2 years', 'now');

        $updatedAtDate = $this->faker->boolean(50)
            ? $createdAtDate
            : $this->faker->dateTimeBetween($createdAtDate, 'now');

        return [
            'node_id' => $roleNode->node_id,
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->sentence(6),
            'url' => $this->faker->url(),
            'category' => $this->faker->randomElement([
                'Node', 'React', 'Angular', 'JavaScript', 'Java', 'Fullstack PHP', 'Data Science', 'BBDD'
            ]),
            
            'tags' => $this->faker->randomElements($validTags, $this->faker->numberBetween(1, 5)),
            'type' => $this->faker->randomElement(['Video', 'Cursos', 'Blog']),
            'bookmark_count' => 0,
            'like_count' => 0,
            'created_at' => $createdAtDate,
            'updated_at' => $updatedAtDate,
        ];
    }
}
