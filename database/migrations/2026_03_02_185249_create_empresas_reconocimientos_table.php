<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * CORE: pivot empresa–reconocimiento (tabla reconocimientos de Rafa). Create directo.
     */
    public function up(): void
    {
        Schema::create('empresas_reconocimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('reconocimiento_id')->constrained('reconocimientos');
            $table->boolean('es_enviable')->default(false);
            $table->integer('menciones_necesarias')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas_reconocimientos');
    }
};
