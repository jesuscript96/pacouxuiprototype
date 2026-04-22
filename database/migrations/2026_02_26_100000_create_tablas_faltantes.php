<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * DEPRECADA / NO-OP: Las tablas que creaba esta migración (bancos, departamentos,
     * puestos, ubicaciones, regiones, centros_pagos) ya existen o se crean en otras migraciones:
     * - bancos: 2026_03_04_171552_create_bancos_table.php
     * - departamentos: 2026_03_05_004220_create_departamentos_table.php
     * - puestos: 2026_02_26_099998_create_puestos_table.php
     * - ubicaciones: 2026_03_10_190906_create_ubicaciones_table.php
     * - regiones: 2026_03_11_181350_create_regions_table.php
     * - centros_pagos: 2026_03_11_153115_create_centro_pagos_table.php
     *
     * Se mantiene el archivo para no romper el historial de migraciones en entornos
     * donde esta migración ya fue ejecutada.
     */
    public function up(): void
    {
        // No-op: las tablas ya existen o se crean en otras migraciones.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: no eliminar tablas que pueden ser usadas por otras migraciones o datos.
    }
};
