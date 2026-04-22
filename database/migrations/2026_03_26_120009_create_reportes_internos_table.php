<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla ancla para permisos Shield del módulo (sin datos de negocio obligatorios).
     */
    public function up(): void
    {
        Schema::create('reportes_internos', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_internos');
    }
};
