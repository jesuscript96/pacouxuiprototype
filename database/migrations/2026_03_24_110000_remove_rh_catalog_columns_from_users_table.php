<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['departamento_id']);
            $table->dropForeign(['puesto_id']);
            $table->dropForeign(['ubicacion_id']);
            $table->dropForeign(['area_id']);
            $table->dropForeign(['region_id']);
            $table->dropForeign(['centro_pago_id']);
            $table->dropForeign(['razon_social_id']);

            $table->dropColumn([
                'departamento_id',
                'puesto_id',
                'ubicacion_id',
                'area_id',
                'region_id',
                'centro_pago_id',
                'razon_social_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->nullOnDelete();
            $table->foreignId('puesto_id')->nullable()->constrained('puestos')->nullOnDelete();
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regiones')->nullOnDelete();
            $table->foreignId('centro_pago_id')->nullable()->constrained('centros_pagos')->nullOnDelete();
            $table->foreignId('razon_social_id')->nullable()->constrained('razones_sociales')->nullOnDelete();
        });
    }
};
