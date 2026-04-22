<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Parte 3/4: FKs a catálogos (ALTER separado para reducir bloqueo en MySQL).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regiones')->nullOnDelete();
            $table->foreignId('centro_pago_id')->nullable()->constrained('centros_pagos')->nullOnDelete();
            $table->foreignId('razon_social_id')->nullable()->constrained('razones_sociales')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['ubicacion_id']);
            $table->dropForeign(['area_id']);
            $table->dropForeign(['region_id']);
            $table->dropForeign(['centro_pago_id']);
            $table->dropForeign(['razon_social_id']);

            $table->dropColumn([
                'ubicacion_id',
                'area_id',
                'region_id',
                'centro_pago_id',
                'razon_social_id',
            ]);
        });
    }
};
