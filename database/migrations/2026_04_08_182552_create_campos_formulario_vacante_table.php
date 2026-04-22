<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campos_formulario_vacante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacante_id')->constrained('vacantes')->cascadeOnDelete();
            $table->string('tipo');
            $table->string('etiqueta');
            $table->string('nombre');
            $table->boolean('requerido')->default(false);
            $table->string('placeholder')->nullable();
            $table->string('tipos_archivo')->nullable();
            $table->unsignedInteger('longitud_minima')->nullable();
            $table->unsignedInteger('longitud_maxima')->nullable();
            $table->json('opciones')->nullable();
            $table->boolean('es_dependiente')->default(false);
            $table->string('campo_padre')->nullable();
            $table->string('valor_activador')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campos_formulario_vacante');
    }
};
