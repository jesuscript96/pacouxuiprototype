<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BL: En greenfield, users nunca tuvo empleado_id; la migración de unificación solo renombra si existía.
     * Para el vínculo 1:1 users ↔ colaboradores hace falta la columna explícita.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users') || Schema::hasColumn('users', 'colaborador_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('colaborador_id')
                ->nullable()
                ->after('empresa_id')
                ->constrained('colaboradores')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'colaborador_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['colaborador_id']);
            $table->dropColumn('colaborador_id');
        });
    }
};
