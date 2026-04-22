<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valores_pregunta_solicitud', function (Blueprint $table): void {
            $table->id();
            $table->string('titulo', 300)->nullable();
            $table->integer('indice');
            $table->boolean('respuesta_personalizada')->default(false);
            $table->foreignId('pregunta_solicitud_id')->constrained('preguntas_solicitud')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valores_pregunta_solicitud');
    }
};
