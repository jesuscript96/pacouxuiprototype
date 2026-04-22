<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Una fila por (user_id, empresa_id); sin columna tipo.
     */
    public function up(): void
    {
        $keepIds = DB::table('empresa_user')
            ->selectRaw('MIN(id) as id')
            ->groupBy('user_id', 'empresa_id')
            ->pluck('id');

        if ($keepIds->isNotEmpty()) {
            DB::table('empresa_user')
                ->whereNotIn('id', $keepIds->all())
                ->delete();
        }

        // BL: la FK sobre user_id usa el índice único ternario; hay que crear antes un índice/unique con prefijo user_id.
        Schema::table('empresa_user', function (Blueprint $table): void {
            $table->unique(['user_id', 'empresa_id'], 'empresa_user_user_id_empresa_id_unique');
        });

        Schema::table('empresa_user', function (Blueprint $table): void {
            $table->dropUnique('empresa_user_user_id_empresa_id_tipo_unique');
            $table->dropColumn('tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresa_user', function (Blueprint $table): void {
            $table->index('user_id', 'empresa_user_user_id_index_down');
        });

        Schema::table('empresa_user', function (Blueprint $table): void {
            $table->dropUnique('empresa_user_user_id_empresa_id_unique');
            $table->string('tipo')->nullable()->after('user_id');
            $table->unique(['user_id', 'empresa_id', 'tipo'], 'empresa_user_user_id_empresa_id_tipo_unique');
        });

        Schema::table('empresa_user', function (Blueprint $table): void {
            $table->dropIndex('empresa_user_user_id_index_down');
        });
    }
};
