<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TechnicalTest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Database\Seeders\TechnicalTestSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            RoleSeeder::class,        
            TagSeeder::class,         
            ResourceSeeder::class,   
            BookmarkSeeder::class,    
            LikeSeeder::class,
            TechnicalTestSeeder::class,
        ]);
    
    }
}