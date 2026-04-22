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
        Schema::create('temas_voz_colaboradores', function (Blueprint $table) { // MIGRAR ESTE CATALOGO ****************
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->integer('exclusivo_para_empresa')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('empresas_temas_voz_colaboradores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('tema_voz_colaborador_id')->constrained('temas_voz_colaboradores');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas_temas_voz_colaboradores');
        Schema::dropIfExists('temas_voz_colaboradores');
    }
};
