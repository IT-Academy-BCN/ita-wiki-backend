<?php

declare (strict_types= 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * foreignId github_id references id on github table
     * string role
     * foreignId list_project_id references id on list_projects table
     */

    public function up(): void
    {
        Schema::create('contributors_list_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('github_id')->constrained('github')->onDelete('cascade');
            $table->string('role');
            $table->foreignId('list_project_id')->constrained('list_projects')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributors_list_project');
    }
};
