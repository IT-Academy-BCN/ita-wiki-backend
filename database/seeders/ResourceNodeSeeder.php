<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ResourceNode;
use Illuminate\Database\Seeder;

class ResourceNodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ResourceNode::factory()->count(20)->create();
    }
}
