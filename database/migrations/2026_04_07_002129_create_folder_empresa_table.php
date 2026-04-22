<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carpeta_empresa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carpeta_id')
                ->constrained('carpetas')
                ->cascadeOnDelete();
            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['carpeta_id', 'empresa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carpeta_empresa');
    }
};
