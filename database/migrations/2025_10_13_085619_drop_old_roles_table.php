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
        Schema::dropIfExists('old_roles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate table for rollback
        Schema::create('old_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('github_id')->unique();
            $table->enum('role', ['superadmin', 'admin', 'mentor', 'student'])->default('student');
            $table->timestamps();
        });
    }
};
