<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bajas_colaboradores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('colaborador_id')->constrained('colaboradores')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();

            $table->date('fecha_baja');
            $table->string('motivo');
            $table->text('comentarios')->nullable();
            $table->enum('estado', ['PROGRAMADA', 'EJECUTADA', 'CANCELADA'])->default('PROGRAMADA');

            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('puesto_id')->nullable()->constrained('puestos')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regiones')->nullOnDelete();
            $table->foreignId('centro_pago_id')->nullable()->constrained('centros_pagos')->nullOnDelete();
            $table->foreignId('razon_social_id')->nullable()->constrained('razones_sociales')->nullOnDelete();

            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ejecutada_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['colaborador_id', 'estado']);
            $table->index(['empresa_id', 'estado']);
            $table->index(['fecha_baja', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bajas_colaboradores');
    }
};
