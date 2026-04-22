<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estados_cuenta', function (Blueprint $table): void {
            $table->id();
            $table->dateTime('desde');
            $table->dateTime('hasta');
            $table->decimal('saldo', 14, 2)->default(0);
            $table->decimal('saldo_sin_comision', 14, 2)->default(0);
            $table->string('estado');
            $table->string('periodicidad_pago')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->string('tipo_comision')->nullable();
            $table->decimal('monto_comision', 14, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estados_cuenta');
    }
};
