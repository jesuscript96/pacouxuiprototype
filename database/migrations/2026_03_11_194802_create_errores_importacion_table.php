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
        Schema::create('errores_importacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('importacion_id')->constrained('importaciones')->cascadeOnDelete();
            $table->integer('fila');
            $table->string('columna')->nullable();
            $table->text('valor_enviado')->nullable();
            $table->string('mensaje_error');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('errores_importacion');
    }
};
