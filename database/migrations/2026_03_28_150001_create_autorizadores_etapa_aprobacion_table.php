<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('autorizadores_etapa_aprobacion', function (Blueprint $table): void {
            $table->id();
            $table->enum('nivel', ['1', '2', '3', '4'])->nullable();
            $table->foreignId('etapa_flujo_aprobacion_id')->constrained('etapas_flujo_aprobacion')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autorizadores_etapa_aprobacion');
    }
};
