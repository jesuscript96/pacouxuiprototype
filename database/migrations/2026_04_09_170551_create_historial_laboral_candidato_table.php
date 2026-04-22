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
        Schema::create('historial_laboral_candidato', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidato_id')->constrained('candidatos_reclutamiento')->cascadeOnDelete();
            $table->string('curp', 18);

            $table->string('consent_id')->nullable();
            $table->string('verification_id')->nullable();
            $table->string('account_status')->nullable();
            $table->string('failed_reason')->nullable();

            $table->string('nss')->nullable();
            $table->string('nombre_imss')->nullable();
            $table->integer('semanas_cotizadas')->nullable();
            $table->string('estatus_laboral')->nullable();
            $table->string('empresa_actual')->nullable();

            $table->json('empleos')->nullable();

            $table->timestamp('ultima_actualizacion')->nullable();
            $table->timestamps();

            $table->index('curp');
            $table->index('account_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_laboral_candidato');
    }
};
