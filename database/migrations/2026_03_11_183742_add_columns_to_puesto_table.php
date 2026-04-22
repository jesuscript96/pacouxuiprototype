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
        Schema::table('puestos', function (Blueprint $table) {
            $table->foreignId('puesto_general_id')->constrained('puestos_generales');
            $table->foreignId('ocupacion_id')->constrained('ocupaciones');
            $table->foreignId('area_general_id')->constrained('areas_generales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('puestos', function (Blueprint $table) {
            $table->dropForeign(['puesto_general_id']);
            $table->dropForeign(['ocupacion_id']);
            $table->dropForeign(['area_general_id']);
        });
    }
};
