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
        Schema::create('ubicaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('cp');
            $table->boolean('mostrar_modal_calendly')->default(true);
            $table->string('registro_patronal_sucursal')->nullable();
            $table->string('direccion_imss')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('razones_sociales_ubicaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('razon_social_id')->constrained('razones_sociales');
            $table->foreignId('ubicacion_id')->constrained('ubicaciones');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('razones_sociales_ubicaciones');
        Schema::dropIfExists('ubicaciones');
    }
};
