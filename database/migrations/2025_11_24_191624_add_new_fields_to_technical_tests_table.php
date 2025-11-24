<?php

declare (strict_types= 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('technical_tests', function (Blueprint $table) {
            $table->enum('difficulty_level', ['easy', 'medium', 'hard', 'expert'])->after('language');
            $table->integer('duration')->after('difficulty_level');
            $table->enum('state', ['draft', 'published', 'archived'])->default('draft')->after('duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technical_tests', function (Blueprint $table) {
            $table->dropColumn(['difficulty_level', 'duration', 'state']);
        });
    }
};
