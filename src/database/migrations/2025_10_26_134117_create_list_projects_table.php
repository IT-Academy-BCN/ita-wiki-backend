<?php

declare (strict_types= 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\LanguageEnum;

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
            $table->enum('language_backend', LanguageEnum::values())->nullable();
            $table->enum('language_frontend', LanguageEnum::values())->nullable();
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
