<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technical_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('github_id')->nullable()->comment('Usuario creador (FK → roles.github_id)');
            $table->string('title')->comment('Título del examen');
            $table->enum('language', ['PHP', 'JavaScript', 'Java', 'React', 'TypeScript', 'Python', 'SQL'])->comment('Lenguaje de programación');
            $table->text('description')->nullable()->comment('Descripción detallada (opcional)');
            $table->string('file_path', 500)->nullable()->comment('Ruta del archivo PDF (opcional - borrador)');
            $table->string('file_original_name')->nullable()->comment('Nombre original del archivo (opcional)');
            $table->integer('file_size')->nullable()->comment('Tamaño en bytes (opcional)');
            $table->json('tags')->nullable()->comment('Array de strings: ["php", "laravel"]');
            $table->integer('bookmark_count')->default(0)->comment('Contador automático');
            $table->integer('like_count')->default(0)->comment('Contador automático');
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('github_id');
            $table->index('language');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technical_tests');
    }
};