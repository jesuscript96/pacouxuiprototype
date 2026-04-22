<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transacciones', function (Blueprint $table): void {
            $table->id();
            $table->dateTime('fecha');
            $table->string('tipo');
            $table->decimal('monto', 14, 2)->default(0);
            $table->decimal('comision', 14, 2)->default(0);
            $table->foreignId('estado_cuenta_id')->constrained('estados_cuenta')->cascadeOnDelete();
            $table->string('estado')->nullable();
            $table->string('tipo_pago')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transacciones');
    }
};
