<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensajes_candidato', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidato_id')->constrained('candidatos_reclutamiento')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->longText('comentario');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes_candidato');
    }
};
