<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('importaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipo');
            $table->string('archivo_original');
            $table->integer('total_filas')->default(0);
            $table->integer('filas_procesadas')->default(0);
            $table->integer('filas_exitosas')->default(0);
            $table->integer('filas_con_error')->default(0);
            $table->string('estado')->default('PENDIENTE');
            $table->string('archivo_errores')->nullable();
            $table->timestamp('iniciado_en')->nullable();
            $table->timestamp('completado_en')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('importaciones');
    }
};
