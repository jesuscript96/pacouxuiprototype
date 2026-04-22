<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * BL: Migra notificacion_push_destinatarios de colaborador_id a user_id.
 * Las notificaciones push van a dispositivos, y solo users tienen dispositivos.
 * El sistema ya filtraba whereHas('user') — este cambio elimina el rodeo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notificacion_push_destinatarios', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('notificacion_push_id')
                ->constrained('users')
                ->cascadeOnDelete();
        });

        DB::statement('
            UPDATE notificacion_push_destinatarios npd
            INNER JOIN users u ON u.colaborador_id = npd.colaborador_id
            SET npd.user_id = u.id
            WHERE npd.user_id IS NULL
        ');

        DB::table('notificacion_push_destinatarios')
            ->whereNull('user_id')
            ->delete();

        // MySQL usa el unique index compuesto para la FK de notificacion_push_id.
        // Crear índice simple primero para liberar el unique.
        Schema::table('notificacion_push_destinatarios', function (Blueprint $table) {
            $table->index('notificacion_push_id', 'npd_notificacion_push_id_idx');
        });

        DB::statement('ALTER TABLE notificacion_push_destinatarios DROP FOREIGN KEY notificacion_push_destinatarios_colaborador_id_foreign');
        DB::statement('ALTER TABLE notificacion_push_destinatarios DROP INDEX npd_notificacion_colaborador_unique');

        Schema::table('notificacion_push_destinatarios', function (Blueprint $table) {
            $table->dropColumn('colaborador_id');
        });

        Schema::table('notificacion_push_destinatarios', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->unique(['notificacion_push_id', 'user_id'], 'npd_notificacion_user_unique');
        });

        // El índice temporal ya no es necesario — el nuevo unique cubre notificacion_push_id.
        Schema::table('notificacion_push_destinatarios', function (Blueprint $table) {
            $table->dropIndex('npd_notificacion_push_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('notificacion_push_destinatarios', function (Blueprint $table) {
            $table->dropUnique('npd_notificacion_user_unique');
        });

        Schema::table('notificacion_push_destinatarios', function (Blueprint $table) {
            $table->foreignId('colaborador_id')
                ->nullable()
                ->after('notificacion_push_id')
                ->constrained('colaboradores')
                ->cascadeOnDelete();
        });

        DB::statement('
            UPDATE notificacion_push_destinatarios npd
            INNER JOIN users u ON u.id = npd.user_id
            SET npd.colaborador_id = u.colaborador_id
            WHERE npd.colaborador_id IS NULL AND u.colaborador_id IS NOT NULL
        ');

        Schema::table('notificacion_push_destinatarios', function (Blueprint $table) {
            $table->unique(['notificacion_push_id', 'colaborador_id'], 'npd_notificacion_colaborador_unique');
        });

        DB::statement('ALTER TABLE notificacion_push_destinatarios DROP FOREIGN KEY notificacion_push_destinatarios_user_id_foreign');

        Schema::table('notificacion_push_destinatarios', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
