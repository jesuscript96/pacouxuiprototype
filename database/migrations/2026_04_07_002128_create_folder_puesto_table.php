<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carpeta_puesto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carpeta_id')
                ->constrained('carpetas')
                ->cascadeOnDelete();
            $table->foreignId('puesto_id')
                ->constrained('puestos')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['carpeta_id', 'puesto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carpeta_puesto');
    }
};
