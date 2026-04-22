<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * CORE: Rafa (razones encuesta salida).
     */
    public function up(): void
    {
        Schema::create('razones_encuesta_salida', function (Blueprint $table) {
            $table->id();
            $table->string('razon');
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('razones_encuesta_salida');
    }
};
