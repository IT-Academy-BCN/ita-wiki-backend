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
        Schema::create('list_projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('time_duration')->nullable();
            $table->enum('language_backend', ['PHP', 'JavaScript', 'Python', 'Ruby', 'Java', 'C#', 'Go', 'Other'])->nullable();
            $table->enum('language_frontend', ['JavaScript', 'TypeScript', 'HTML', 'CSS', 'Other'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_projects');
    }
};
