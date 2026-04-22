<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reingresos_colaboradores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('baja_colaborador_id')
                ->constrained('bajas_colaboradores')
                ->cascadeOnDelete();

            $table->foreignId('colaborador_anterior_id')
                ->constrained('colaboradores')
                ->cascadeOnDelete();

            $table->foreignId('colaborador_nuevo_id')
                ->nullable()
                ->constrained('colaboradores')
                ->nullOnDelete();

            $table->foreignId('user_anterior_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('user_nuevo_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            $table->date('fecha_ingreso_anterior')->nullable();
            $table->date('fecha_ingreso_nuevo');

            $table->string('motivo_reingreso')->nullable();
            $table->text('comentarios')->nullable();

            $table->foreignId('registrado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['empresa_id']);
            $table->index(['baja_colaborador_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reingresos_colaboradores');
    }
};
