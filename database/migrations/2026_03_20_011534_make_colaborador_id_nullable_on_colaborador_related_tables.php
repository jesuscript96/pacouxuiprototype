<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FASE 5: permitir filas solo con user_id mientras coexisten colaborador_id (legacy).
     *
     * @var list<string>
     */
    private const TABLAS = [
        'beneficiarios_colaborador',
        'cuentas_nomina',
        'historial_departamentos',
        'historial_areas',
        'historial_puestos',
        'historial_ubicaciones',
        'historial_regiones',
        'historial_razones_sociales',
        'historial_periodicidades_pago',
    ];

    public function up(): void
    {
        foreach (self::TABLAS as $tabla) {
            if (! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, 'colaborador_id')) {
                continue;
            }

            $this->dropForeignKeyIfExists($tabla, 'colaborador_id');

            Schema::table($tabla, function (Blueprint $table) {
                $table->unsignedBigInteger('colaborador_id')->nullable()->change();
            });

            if (! $this->foreignKeyExistsOnColumn($tabla, 'colaborador_id')) {
                Schema::table($tabla, function (Blueprint $table) {
                    $table->foreign('colaborador_id')->references('id')->on('colaboradores')->nullOnDelete();
                });
            }
        }

        if (! Schema::hasTable('colaborador_producto')) {
            return;
        }

        $this->dropForeignKeyIfExists('colaborador_producto', 'colaborador_id');
        $this->dropForeignKeyIfExists('colaborador_producto', 'producto_id');

        // BL: Aiven / hosts con sql_require_primary_key=ON necesitan relajar la sesión entre pasos.
        // Sail/usuarios sin SESSION_VARIABLES_ADMIN fallan en SET SESSION; en local suele no ser necesario.
        $relaxedRequirePk = false;
        try {
            DB::statement('SET SESSION sql_require_primary_key = 0');
            $relaxedRequirePk = true;
        } catch (\Throwable) {
        }

        try {
            if (! Schema::hasColumn('colaborador_producto', 'id')) {
                Schema::table('colaborador_producto', function (Blueprint $table) {
                    $table->dropPrimary();
                });
                Schema::table('colaborador_producto', function (Blueprint $table) {
                    $table->id()->first();
                });
            }

            if (Schema::hasColumn('colaborador_producto', 'colaborador_id')) {
                Schema::table('colaborador_producto', function (Blueprint $table) {
                    $table->unsignedBigInteger('colaborador_id')->nullable()->change();
                });
            }

            if (! $this->foreignKeyExistsOnColumn('colaborador_producto', 'colaborador_id')) {
                Schema::table('colaborador_producto', function (Blueprint $table) {
                    $table->foreign('colaborador_id')->references('id')->on('colaboradores')->nullOnDelete();
                });
            }
            if (! $this->foreignKeyExistsOnColumn('colaborador_producto', 'producto_id')) {
                Schema::table('colaborador_producto', function (Blueprint $table) {
                    $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
                });
            }
        } finally {
            if ($relaxedRequirePk) {
                try {
                    DB::statement('SET SESSION sql_require_primary_key = 1');
                } catch (\Throwable) {
                }
            }
        }
    }

    public function down(): void
    {
        // BL: no revertir de forma segura si existen filas con colaborador_id NULL o pivot con id autonumérico.
    }

    /**
     * @return list<string>
     */
    private function findForeignKeyNames(string $table, string $column): array
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $database = Schema::getConnection()->getDatabaseName();
            $rows = DB::select(
                <<<'SQL'
                SELECT kcu.CONSTRAINT_NAME AS name
                FROM information_schema.KEY_COLUMN_USAGE kcu
                INNER JOIN information_schema.TABLE_CONSTRAINTS tc
                    ON kcu.CONSTRAINT_NAME = tc.CONSTRAINT_NAME
                    AND kcu.TABLE_SCHEMA = tc.TABLE_SCHEMA
                WHERE tc.CONSTRAINT_TYPE = 'FOREIGN KEY'
                    AND kcu.TABLE_SCHEMA = ?
                    AND kcu.TABLE_NAME = ?
                    AND kcu.COLUMN_NAME = ?
                    AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
                SQL
                ,
                [$database, $table, $column]
            );

            return collect($rows)->pluck('name')->unique()->values()->all();
        }

        return [];
    }

    private function foreignKeyExistsOnColumn(string $table, string $column): bool
    {
        return $this->findForeignKeyNames($table, $column) !== [];
    }

    private function dropForeignKeyIfExists(string $table, string $column): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            foreach ($this->findForeignKeyNames($table, $column) as $constraintName) {
                $t = str_replace('`', '``', $table);
                $c = str_replace('`', '``', $constraintName);
                try {
                    DB::statement("ALTER TABLE `{$t}` DROP FOREIGN KEY `{$c}`");
                } catch (\Throwable) {
                    // FK ya eliminada o nombre distinto en caché de esquema
                }
            }

            return;
        }

        try {
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->dropForeign([$column]);
            });
        } catch (\Throwable) {
            // FK inexistente o nombre no convencional
        }
    }
};
