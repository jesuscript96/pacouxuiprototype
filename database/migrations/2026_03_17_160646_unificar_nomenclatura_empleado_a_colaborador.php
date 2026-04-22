<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unifica nomenclatura empleado → colaborador sin pérdida de datos.
     * - users: empleado_id → colaborador_id (FK a colaboradores)
     * - colaboradores: numero_empleado → numero_colaborador
     * - spatie_roles: rol name 'empleado' → 'colaborador'
     * - permissions: UploadArchivoEmpleado → UploadArchivoColaborador, ViewBajaEmpleado → ViewBajaColaborador
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'empleado_id')) {
            $fkName = DB::selectOne("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'users'
                  AND COLUMN_NAME = 'empleado_id'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            if ($fkName !== null) {
                Schema::table('users', function (Blueprint $table) use ($fkName) {
                    $table->dropForeign($fkName->CONSTRAINT_NAME);
                });
            }
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('empleado_id', 'colaborador_id');
            });
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('colaborador_id')
                    ->references('id')
                    ->on('colaboradores')
                    ->nullOnDelete();
            });
        } elseif (Schema::hasColumn('users', 'colaborador_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('colaborador_id')
                    ->references('id')
                    ->on('colaboradores')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasColumn('colaboradores', 'numero_empleado')) {
            Schema::table('colaboradores', function (Blueprint $table) {
                $table->renameColumn('numero_empleado', 'numero_colaborador');
            });
        }

        DB::table('spatie_roles')->where('name', 'empleado')->update(['name' => 'colaborador']);

        DB::table('permissions')->where('name', 'UploadArchivoEmpleado')->update(['name' => 'UploadArchivoColaborador']);
        DB::table('permissions')->where('name', 'ViewBajaEmpleado')->update(['name' => 'ViewBajaColaborador']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->where('name', 'UploadArchivoColaborador')->update(['name' => 'UploadArchivoEmpleado']);
        DB::table('permissions')->where('name', 'ViewBajaColaborador')->update(['name' => 'ViewBajaEmpleado']);

        DB::table('spatie_roles')->where('name', 'colaborador')->update(['name' => 'empleado']);

        if (Schema::hasColumn('colaboradores', 'numero_colaborador')) {
            Schema::table('colaboradores', function (Blueprint $table) {
                $table->renameColumn('numero_colaborador', 'numero_empleado');
            });
        }

        if (Schema::hasColumn('users', 'colaborador_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['colaborador_id']);
            });
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('colaborador_id', 'empleado_id');
            });
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('empleado_id')->references('id')->on('colaboradores')->onDelete('set null');
            });
        }
    }
};
