<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TechnicalTest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Database\Seeders\RoleNodeSeeder;
use Database\Seeders\TechnicalTestSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,        RoleNodeSeeder::class,      // for node_id transition
            TagSeeder::class,         TagNodeSeeder::class,       //for node_id transition
            ResourceSeeder::class,    ResourceNodeSeeder::class,  // for node_id transition
            BookmarkSeeder::class,    BookmarkNodeSeeder::class,  // for node_id transition
            LikeSeeder::class,
            TechnicalTestSeeder::class,
        ]);
    
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}