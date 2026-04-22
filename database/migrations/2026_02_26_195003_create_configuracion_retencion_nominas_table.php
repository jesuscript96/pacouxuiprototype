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
        Schema::create('configuracion_retencion_nominas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas'); // company_id
            $table->date('fecha')->nullable(); // date
            $table->integer('dias')->nullable(); // days
            $table->integer('dia_semana')->nullable(); // weekday
            $table->text('emails')->nullable(); // emails
            $table->enum('periodicidad_pago', ['SEMANAL', 'CATORCENAL', 'QUINCENAL', 'MENSUAL']); // payment_periodicity
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_retencion_nominas');
    }
};
