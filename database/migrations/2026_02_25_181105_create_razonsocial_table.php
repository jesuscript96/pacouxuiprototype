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
        Schema::create('razones_sociales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('rfc');
            $table->string('cp');
            $table->string('calle');
            $table->string('numero_exterior');
            $table->string('numero_interior')->nullable();
            $table->string('colonia');
            $table->string('alcaldia');
            $table->string('estado');
            $table->string('registro_patronal')->nullable();
            $table->timestamps();
        });

        Schema::create('empresas_razones_sociales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('razon_social_id')->constrained('razones_sociales');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('razones_sociales');
        Schema::dropIfExists('empresas_razones_sociales');
    }
};
