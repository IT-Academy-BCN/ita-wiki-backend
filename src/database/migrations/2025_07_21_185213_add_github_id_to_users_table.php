<?php

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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'github_id')) {
                $table->bigInteger('github_id')->unsigned()->unique();
            }

            if (!Schema::hasColumn('users', 'github_user_name')) {
                $table->string('github_user_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'github_id')) {
                $table->dropColumn('github_id');
            }

            if (Schema::hasColumn('users', 'github_user_name')) {
                $table->dropColumn('github_user_name');
            }
        });
    }
};
