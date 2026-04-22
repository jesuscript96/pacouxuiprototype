<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_corporativos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('carpeta_id')
                ->constrained('carpetas')
                ->cascadeOnDelete();
            $table->string('subcarpeta', 191)->nullable();
            $table->string('nombre_documento', 191);
            $table->dateTime('fecha_carga');
            $table->dateTime('primera_visualizacion')->nullable();
            $table->dateTime('ultima_visualizacion')->nullable();
            $table->timestamps();

            $table->index(['carpeta_id', 'nombre_documento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_corporativos');
    }
};
