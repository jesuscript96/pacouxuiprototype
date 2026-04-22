<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidatos_reclutamiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacante_id')->constrained('vacantes')->cascadeOnDelete();
            $table->string('estatus')->default('Sin atender');
            $table->json('valores_formulario')->nullable();
            $table->json('archivos')->nullable();
            $table->string('curp', 18)->nullable();
            $table->string('nombre_completo')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->decimal('evaluacion_cv', 3, 1)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('curp');
            $table->index('estatus');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidatos_reclutamiento');
    }
};
