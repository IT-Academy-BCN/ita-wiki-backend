<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Resource;
use App\Models\User;
use App\Models\Tag;

class ResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ✅ Create resources with tags
        $users = User::all();
        $validTags = Tag::all()->pluck('name')->toArray();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No users found. Run UserSeeder first.');
            return;
        }
        
        if (empty($validTags)) {
            $this->command->warn('⚠️ No tags found. Run TagSeeder first.');
            return;
        }

        foreach (range(1, 20) as $index) {
            $user = $users->random();
            
            Resource::create([
                'github_id' => $user->github_id,
                'title' => fake()->sentence(4),
                'description' => fake()->paragraph(),
                'url' => fake()->url(),
                'category' => fake()->randomElement(['Node', 'React', 'Angular', 'JavaScript', 'Java', 'Fullstack PHP', 'Data Science', 'BBDD']),
                'tags' => fake()->randomElements($validTags, fake()->numberBetween(1, 5)), // ✅ Always add tags
                'type' => fake()->randomElement(['Video', 'Cursos', 'Blog']),
            ]);
        }
        
        $this->command->info('✅ Created 20 resources with tags');
    }
}
