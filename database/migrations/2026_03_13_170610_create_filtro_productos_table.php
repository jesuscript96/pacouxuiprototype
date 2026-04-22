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
        Schema::create('filtros_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('area_id')->nullable()->constrained('areas');
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos');
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones');
            $table->foreignId('puesto_id')->nullable()->constrained('puestos');
            $table->foreignId('region_id')->nullable()->constrained('regiones');
            $table->string('generos')->nullable();
            $table->string('meses')->nullable();
            $table->integer('edad_desde')->nullable();
            $table->integer('edad_hasta')->nullable();
            $table->integer('mes_desde')->nullable();
            $table->integer('mes_hasta')->nullable();
            $table->string('razon')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filtros_productos');
    }
};
