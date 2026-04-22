<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * CORE: catálogo de felicitaciones para el panel admin.
     */
    public function up(): void
    {
        Schema::create('felicitaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('user_id')->constrained('users')->nullable();
            $table->string('titulo');
            $table->string('tipo');
            $table->text('mensaje');
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos');
            $table->boolean('requiere_respuesta')->default(false);
            $table->string('tipo_respuesta')->nullable();
            $table->boolean('es_urgente')->default(false);
            $table->string('logo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('felicitaciones');
    }
};
