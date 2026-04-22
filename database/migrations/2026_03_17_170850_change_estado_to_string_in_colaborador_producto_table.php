<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * estado era boolean; el código usa 'ACTIVO'/'INACTIVO'. Cambiar a string.
     */
    public function up(): void
    {
        Schema::table('colaborador_producto', function (Blueprint $table) {
            $table->string('estado')->default('ACTIVO')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colaborador_producto', function (Blueprint $table) {
            $table->boolean('estado')->default(true)->change();
        });
    }
};
