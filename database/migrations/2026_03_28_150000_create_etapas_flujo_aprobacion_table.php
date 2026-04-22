<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etapas_flujo_aprobacion', function (Blueprint $table): void {
            $table->id();
            $table->unsignedTinyInteger('etapa');
            $table->enum('nivel_autorizacion', ['POR NOMBRE', 'JERARQUIA']);
            $table->foreignId('tipo_solicitud_id')->constrained('tipos_solicitud')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etapas_flujo_aprobacion');
    }
};
