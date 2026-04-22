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
        Schema::create('comisiones_rangos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('tipo_comision');
            $table->decimal('precio_desde', 10, 2)->nullable();
            $table->decimal('precio_hasta', 10, 2)->nullable();
            $table->decimal('cantidad_fija', 10, 2)->nullable();
            $table->decimal('porcentaje', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comisiones_rangos');
    }
};
