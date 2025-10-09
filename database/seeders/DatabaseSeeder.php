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
            RoleSeeder::class,              // 1. Create roles
            PermissionSeeder::class,        // 2. Create permissions
            RolePermissionSeeder::class,    // 3. Assign permissions to roles
            
            UserSeeder::class,              // 4. Create users (needs roles)
            OldRoleSeeder::class,           // 5. To deprecate
            TagSeeder::class,               // 6. Create tags
            ResourceSeeder::class,          // 7. Create resources
            BookmarkSeeder::class,          // 8. Create bookmarks
            LikeSeeder::class,              // 9. Create likes
            TechnicalTestSeeder::class,     // 10. Create technical tests
        ]);
    }
}