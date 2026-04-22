<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Comando temporal para verificar que las migraciones cargadas en la BD
 * coinciden con los archivos y que las tablas esperadas existen.
 *
 * Uso: php artisan migrate:verify
 */
class MigrateVerifyCommand extends Command
{
    /**
     * Nombres inferidos del archivo de migración que no coinciden con la tabla real.
     * Clave = nombre inferido (create_X_table → X), valor = tablas reales en la BD.
     *
     * @var array<string, array<int, string>>
     */
    private const INFERRED_TO_REAL_TABLES = [
        'centro_costos' => ['centro_de_costos'],
        'razonsocial' => ['razones_sociales', 'empresas_razones_sociales'],
        'configuracion_apps' => ['configuracion_app'],
        'comision_rangos' => ['comisiones_rangos'],
        'tema_voz_colaboradores' => ['temas_voz_colaboradores', 'empresas_temas_voz_colaboradores'],
        'razon_encuesta_salidas' => ['razones_encuesta_salida'],
        'alias_tipo_transaccions' => ['alias_tipo_transacciones'],
        'puestos_ubicaciones_regiones_centros_pago' => ['departamentos', 'puestos', 'ubicaciones', 'regiones', 'centros_pago'],
        'departamento_generals' => ['departamentos_generales'],
    ];

    /**
     * Columnas críticas que deben existir tras las migraciones (verificación 1 a 1).
     * Solo se comprueban si la tabla existe en la BD.
     *
     * @var array<string, array<int, string>>
     */
    private const EXPECTED_COLUMNS_BY_TABLE = [
        'users' => ['workos_id', 'avatar', 'ver_reportes', 'usuario_tableau', 'recibir_boletin', 'departamento_id', 'puesto_id'],
        'spatie_roles' => ['company_id', 'display_name', 'description'],
        'bancos' => ['comision', 'deleted_at'],
    ];

    protected $signature = 'migrate:verify
                            {--json : Salida en JSON para scripting}
                            {--strict : Fallar si hay pendientes, tablas o columnas faltantes}';

    protected $description = 'Compara migraciones (archivos vs BD), verifica tablas y columnas esperadas.';

    public function handle(): int
    {
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->error('No se pudo conectar a la BD: '.$e->getMessage());

            return self::FAILURE;
        }

        $path = database_path('migrations');
        $files = is_dir($path)
            ? collect(scandir($path))->filter(fn ($f) => str_ends_with($f, '.php'))->values()->all()
            : [];
        $migrationNamesFromFiles = collect($files)->map(fn ($f) => str_replace('.php', '', $f))->sort()->values()->all();

        $runMigrations = DB::table('migrations')->orderBy('batch')->orderBy('migration')->pluck('migration', 'id')->all();
        $runNames = array_values($runMigrations);

        $pending = array_diff($migrationNamesFromFiles, $runNames);
        $orphans = array_diff($runNames, $migrationNamesFromFiles);

        $dbName = config('database.connections.'.config('database.default').'.database');
        $tablesInDb = $this->getTableNames($dbName);

        $expectedTablesFromMigrations = $this->inferExpectedTables($runNames);
        $tablesMissing = [];
        foreach ($expectedTablesFromMigrations as $table) {
            if (! in_array($table, $tablesInDb, true)) {
                $tablesMissing[] = $table;
            }
        }

        $columnsResult = $this->verifyExpectedColumns($tablesInDb);
        $columnsMissing = $columnsResult['columns_missing'];
        $columnsOk = count($columnsMissing) === 0;

        $result = [
            'database' => $dbName,
            'migrations' => [
                'total_files' => count($migrationNamesFromFiles),
                'total_run' => count($runNames),
                'pending' => array_values($pending),
                'orphans' => array_values($orphans),
                'ok' => count($pending) === 0 && count($orphans) === 0,
            ],
            'schema' => [
                'tables_in_db' => count($tablesInDb),
                'expected_tables_checked' => count($expectedTablesFromMigrations),
                'tables_missing' => $tablesMissing,
                'schema_ok' => count($tablesMissing) === 0,
                'expected_columns_checked' => $columnsResult['columns_checked'],
                'columns_missing' => $columnsMissing,
                'columns_ok' => $columnsOk,
            ],
            'overall_ok' => count($pending) === 0 && count($orphans) === 0 && count($tablesMissing) === 0 && $columnsOk,
        ];

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return ($this->option('strict') && ! $result['overall_ok']) ? self::FAILURE : self::SUCCESS;
        }

        $this->printReport($result, $migrationNamesFromFiles, $runNames);

        if ($this->option('strict') && ! $result['overall_ok']) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function getTableNames(string $dbName): array
    {
        $driver = config('database.connections.'.config('database.default').'.driver');
        if ($driver === 'mysql') {
            $rows = DB::select('SHOW TABLES');
            $key = 'Tables_in_'.$dbName;

            return array_map(fn ($r) => $r->{$key}, $rows);
        }
        $tables = Schema::getTables();

        return array_map(fn ($t) => $t['name'] ?? $t->name ?? '', $tables);
    }

    /**
     * Infiere nombres de tabla a partir de nombres de migración (create_*_table, add_*_to_*_table).
     * Usa INFERRED_TO_REAL_TABLES cuando el nombre del archivo no coincide con la tabla real.
     *
     * @param  array<int, string>  $runNames
     * @return array<int, string>
     */
    private function inferExpectedTables(array $runNames): array
    {
        $tables = [];
        foreach ($runNames as $name) {
            if (preg_match('/create_(.+)_table$/', $name, $m)) {
                $part = $m[1];
                if (str_ends_with($part, '_tables')) {
                    continue;
                }
                if (isset(self::INFERRED_TO_REAL_TABLES[$part])) {
                    foreach (self::INFERRED_TO_REAL_TABLES[$part] as $real) {
                        $tables[] = $real;
                    }
                } else {
                    $tables[] = $part;
                }
            }
            if (preg_match('/add_.+_to_(.+)_table$/', $name, $m)) {
                $tables[] = $m[1];
            }
        }

        return array_values(array_unique($tables));
    }

    /**
     * Verifica columna por columna las tablas definidas en EXPECTED_COLUMNS_BY_TABLE.
     * Solo comprueba tablas que existan en la BD.
     *
     * @param  array<int, string>  $tablesInDb
     * @return array{columns_checked: int, columns_missing: array<int, string>}
     */
    private function verifyExpectedColumns(array $tablesInDb): array
    {
        $checked = 0;
        $missing = [];
        foreach (self::EXPECTED_COLUMNS_BY_TABLE as $table => $columns) {
            if (! in_array($table, $tablesInDb, true)) {
                continue;
            }
            foreach ($columns as $column) {
                $checked++;
                if (! Schema::hasColumn($table, $column)) {
                    $missing[] = "{$table}.{$column}";
                }
            }
        }

        return [
            'columns_checked' => $checked,
            'columns_missing' => $missing,
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<int, string>  $allFileNames
     * @param  array<int, string>  $runNames
     */
    private function printReport(array $result, array $allFileNames, array $runNames): void
    {
        $this->newLine();
        $this->info('--- Verificación: migraciones vs base de datos ('.($result['database'] ?? 'N/A').') ---');
        $this->newLine();

        $pending = $result['migrations']['pending'];
        $orphans = $result['migrations']['orphans'];
        $missing = $result['schema']['tables_missing'];

        if (count($pending) === 0 && count($orphans) === 0) {
            $this->info('Migraciones: todos los archivos están registrados en la BD ('.count($runNames).' ejecutadas).');
        } else {
            if (count($pending) > 0) {
                $this->warn('Migraciones PENDIENTES ('.count($pending).') — archivos que aún no se han ejecutado:');
                foreach (array_slice($pending, 0, 15) as $m) {
                    $this->line('  - '.$m);
                }
                if (count($pending) > 15) {
                    $this->line('  ... y '.(count($pending) - 15).' más.');
                }
                $this->newLine();
            }
            if (count($orphans) > 0) {
                $this->warn('Migraciones HUÉRFANAS ('.count($orphans).') — en la BD pero sin archivo (p. ej. renombradas):');
                foreach (array_slice($orphans, 0, 10) as $m) {
                    $this->line('  - '.$m);
                }
                if (count($orphans) > 10) {
                    $this->line('  ... y '.(count($orphans) - 10).' más.');
                }
                $this->newLine();
            }
        }

        if (count($missing) === 0) {
            $this->info('Esquema: las tablas inferidas de las migraciones ejecutadas existen en la BD.');
        } else {
            $this->warn('Esquema: tablas esperadas que NO están en la BD ('.count($missing).'):');
            foreach ($missing as $t) {
                $this->line('  - '.$t);
            }
            $this->line('  (Puede ser normal si la migración crea varias tablas con otro nombre.)');
            $this->newLine();
        }

        $this->line('Tablas en la BD: '.$result['schema']['tables_in_db']);
        $this->newLine();

        $columnsMissing = $result['schema']['columns_missing'] ?? [];
        $columnsChecked = $result['schema']['expected_columns_checked'] ?? 0;
        if ($columnsChecked > 0) {
            if (count($columnsMissing) === 0) {
                $this->info('Columnas: '.$columnsChecked.' comprobadas (tablas críticas); todas existen.');
            } else {
                $this->warn('Columnas: faltan '.count($columnsMissing).' de '.$columnsChecked.' esperadas:');
                foreach ($columnsMissing as $c) {
                    $this->line('  - '.$c);
                }
            }
            $this->newLine();
        }

        if ($result['overall_ok']) {
            $this->info('Resultado: OK — migraciones, tablas y columnas coherentes.');
        } else {
            $this->warn('Resultado: hay diferencias. Revisa pendientes, huérfanas, tablas o columnas faltantes arriba.');
        }
        $this->newLine();
    }
}
