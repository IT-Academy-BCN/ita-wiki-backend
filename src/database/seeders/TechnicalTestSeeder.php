<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TechnicalTest;

class TechnicalTestSeeder extends Seeder
{
    public function run(): void
    {
        TechnicalTest::factory(20)->create();
    }
}