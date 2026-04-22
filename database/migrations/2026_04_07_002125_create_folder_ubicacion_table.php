<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carpeta_ubicacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carpeta_id')
                ->constrained('carpetas')
                ->cascadeOnDelete();
            $table->foreignId('ubicacion_id')
                ->constrained('ubicaciones')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['carpeta_id', 'ubicacion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carpeta_ubicacion');
    }
};
