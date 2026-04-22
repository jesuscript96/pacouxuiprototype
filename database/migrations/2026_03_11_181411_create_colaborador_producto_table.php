<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Pivote colaborador-producto. Primary compuesta.
     */
    public function up(): void
    {
        Schema::create('colaborador_producto', function (Blueprint $table) {
            $table->foreignId('colaborador_id')->constrained('colaboradores')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->primary(['colaborador_id', 'producto_id']);
            $table->boolean('estado')->default(true);
            $table->text('razon')->nullable();
            $table->string('tipo_cambio')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colaborador_producto');
    }
};
