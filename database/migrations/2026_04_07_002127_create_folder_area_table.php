<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carpeta_area', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carpeta_id')
                ->constrained('carpetas')
                ->cascadeOnDelete();
            $table->foreignId('area_id')
                ->constrained('areas')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['carpeta_id', 'area_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carpeta_area');
    }
};
