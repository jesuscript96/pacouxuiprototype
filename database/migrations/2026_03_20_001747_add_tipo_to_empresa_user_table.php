<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FASE 1: naturaleza de la membresía en el pivot empresa–user (nullable hasta backfill).
     */
    public function up(): void
    {
        Schema::table('empresa_user', function (Blueprint $table) {
            $table->string('tipo')->nullable()->after('user_id');
            $table->unique(['user_id', 'empresa_id', 'tipo'], 'empresa_user_user_id_empresa_id_tipo_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresa_user', function (Blueprint $table) {
            $table->dropUnique('empresa_user_user_id_empresa_id_tipo_unique');
            $table->dropColumn('tipo');
        });
    }
};
