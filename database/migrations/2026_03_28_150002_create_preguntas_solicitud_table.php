<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preguntas_solicitud', function (Blueprint $table): void {
            $table->id();
            $table->string('tipo');
            $table->string('titulo');
            $table->string('subtitulo', 300)->nullable();
            $table->string('imagen')->nullable();
            $table->integer('min_respuestas');
            $table->integer('max_respuestas');
            $table->integer('numero');
            $table->foreignId('tipo_solicitud_id')->constrained('tipos_solicitud')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preguntas_solicitud');
    }
};
