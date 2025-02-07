<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('github_id')->primary();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
