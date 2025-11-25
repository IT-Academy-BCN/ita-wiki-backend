<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Resource;
use App\Models\User;
use App\Models\Tag;
use Illuminate\Support\Str;

class ResourceSeeder extends Seeder
{
   
    public function run(): void
    {
        $users = User::all();
        $validTags = Tag::all()->pluck('name')->toArray();
        
        if ($users->isEmpty()) {
            $this->command->warn(' No users found. Run UserSeeder first.');
            return;
        }
        
        if (empty($validTags)) {
            $this->command->warn(' No tags found. Run TagSeeder first.');
            return;
        }

        foreach (range(1, 20) as $index) {
            $user = $users->random();
            
            Resource::create([
                'github_id' => $user->github_id,
                'title' => fake()->sentence(4),
                'description' => Str::limit(fake()->sentence(5), 200),
                'url' => fake()->url(),
                'category' => fake()->randomElement(['Node', 'React', 'Angular', 'JavaScript', 'Java', 'Fullstack PHP', 'Data Science', 'BBDD']),
                'tags' => fake()->randomElements($validTags, fake()->numberBetween(1, 5)), 
                'type' => fake()->randomElement(['Video', 'Cursos', 'Blog']),
            ]);
        }
        
        $this->command->info(' Created 20 resources with tags');
    }
}
