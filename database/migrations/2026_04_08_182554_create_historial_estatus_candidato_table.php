<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_estatus_candidato', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidato_id')->constrained('candidatos_reclutamiento')->cascadeOnDelete();
            $table->string('estatus');
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin')->nullable();
            $table->string('duracion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_estatus_candidato');
    }
};
