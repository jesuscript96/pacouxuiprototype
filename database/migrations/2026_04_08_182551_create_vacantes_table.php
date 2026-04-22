<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('puesto');
            $table->longText('requisitos');
            $table->longText('aptitudes');
            $table->longText('prestaciones');
            $table->string('slug');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacantes');
    }
};
