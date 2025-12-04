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
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('github_id')->unsigned();
            $table->foreign('github_id')
                ->references('github_id')
                ->on('roles')
                ->onUpdate('cascade') // Updates if github_id is modified in roles
                ->onDelete('restrict'); // Stays if github_id is destroyed in roles
            $table->bigInteger('resource_id')->unsigned();
            $table->foreign('resource_id')
                ->references('id')
                ->on('resources')
                ->onUpdate('cascade') // Updates if resource_id is modified in resources
                ->onDelete('restrict'); // Stays if resource_id is destroyed in resources
            $table->timestamps();

            $table->unique(['github_id', 'resource_id']); // Prevent duplicate likes/dislikes per user-resource pair
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
