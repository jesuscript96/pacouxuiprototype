<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retenciones_nomina', function (Blueprint $table): void {
            $table->id();
            $table->string('periodicidad_pago')->nullable();
            $table->string('estado')->nullable();
            $table->foreignId('cuenta_por_cobrar_id')->constrained('cuentas_por_cobrar')->cascadeOnDelete();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retenciones_nomina');
    }
};
