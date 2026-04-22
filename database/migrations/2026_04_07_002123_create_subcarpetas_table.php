<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subcarpetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carpeta_id')
                ->constrained('carpetas')
                ->cascadeOnDelete();
            $table->string('nombre', 191);
            $table->string('url', 191);
            $table->string('tipo', 191)->default('documentos_corporativos');
            $table->timestamps();

            $table->index(['carpeta_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subcarpetas');
    }
};
