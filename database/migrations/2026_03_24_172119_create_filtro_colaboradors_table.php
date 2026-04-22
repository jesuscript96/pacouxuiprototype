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
        Schema::create('filtros_colaboradores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullable();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullable();
            $table->foreignId('region_id')->nullable()->constrained('regiones')->nullable();
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones')->nullable();
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->nullable();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullable();
            $table->foreignId('puesto_id')->nullable()->constrained('puestos')->nullable();
            $table->text('meses')->nullable();
            $table->text('generos')->nullable();
            $table->integer('edad_desde')->nullable();
            $table->integer('edad_hasta')->nullable();
            $table->integer('mes_desde')->nullable();
            $table->integer('mes_hasta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filtros_colaboradores');
    }
};
