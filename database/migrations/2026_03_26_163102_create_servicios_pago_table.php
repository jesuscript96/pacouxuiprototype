<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicios_pago', function (Blueprint $table): void {
            $table->id();
            $table->string('id_producto_externo');
            $table->string('id_cuenta_externo');
            $table->string('modo_pago');
            $table->string('referencia_extra_cuenta')->nullable();
            $table->foreignId('transaccion_id')->nullable()->constrained('transacciones')->nullOnDelete();
            $table->foreignId('cuenta_bancaria_id')->nullable()->constrained('cuentas_bancarias')->nullOnDelete();
            $table->string('nombre_producto');
            $table->string('tipo')->nullable();
            $table->string('centro_costo')->nullable();
            $table->string('codigo_operacion')->nullable();
            $table->string('latitud')->nullable();
            $table->string('longitud')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios_pago');
    }
};
