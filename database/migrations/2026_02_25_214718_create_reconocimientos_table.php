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
        Schema::create('reconocimientos', function (Blueprint $table) { // MIGRAR ESTE CATALOGO ****************
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->boolean('es_enviable');
            $table->boolean('es_exclusivo');
            $table->integer('menciones_necesarias');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconocimientos');
    }
};
