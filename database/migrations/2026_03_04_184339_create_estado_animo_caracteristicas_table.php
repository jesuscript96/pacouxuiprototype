<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * CORE: catálogo estado de ánimo (características) para el panel admin.
     */
    public function up(): void
    {
        Schema::create('estado_animo_caracteristicas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->enum('lista_inicial', ['normal', 'bad', 'very_bad', 'well', 'very_well'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estado_animo_caracteristicas');
    }
};
