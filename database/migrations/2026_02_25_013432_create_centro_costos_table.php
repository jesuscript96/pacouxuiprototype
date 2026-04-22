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
        Schema::create('centro_de_costos', function (Blueprint $table) {
            $table->id();
            $table->string('servicio')->nullable();
            $table->string('nombre')->nullable();
            $table->string('cuenta_bancaria')->nullable();
            $table->string('terminal_id_tae')->nullable();
            $table->string('terminal_id_ps')->nullable();
            $table->string('clerk_id_tae')->nullable();
            $table->string('clerk_id_ps')->nullable();
            $table->string('key_id')->nullable();
            $table->string('secret_key')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centro_de_costos');
    }
};
