<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas_bancarias', function (Blueprint $table): void {
            $table->id();
            $table->string('numero');
            $table->string('tipo')->nullable();
            $table->string('alias')->nullable();
            $table->string('estado')->nullable();
            $table->foreignId('banco_id')->constrained('bancos');
            $table->foreignId('user_id')->constrained('users');
            $table->boolean('es_nomina')->default(false);
            $table->string('envio_verificacion')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_bancarias');
    }
};
