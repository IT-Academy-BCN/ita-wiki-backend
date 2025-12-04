<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TechnicalTest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Database\Seeders\UserSeeder;
use Database\Seeders\TagSeeder;
use Database\Seeders\ResourceSeeder;
use Database\Seeders\BookmarkSeeder;
use Database\Seeders\LikeSeeder;
use Database\Seeders\TechnicalTestSeeder;
use Database\Seeders\ListProjectsSeeder;
use Database\Seeders\ContributorListProjectSeeder;

class DatabaseSeeder extends Seeder
{
    
    public function run(): void
    {
        $this->call([
            //RoleSeeder::class,              
            //PermissionSeeder::class,       
            //RolePermissionSeeder::class,    
            UserSeeder::class,              
            TagSeeder::class,               
            ResourceSeeder::class,         
            //BookmarkSeeder::class,         
            //LikeSeeder::class,              
            TechnicalTestSeeder::class,
            ListProjectsSeeder::class,
            ContributorListProjectSeeder::class,
        ]);
    }
}