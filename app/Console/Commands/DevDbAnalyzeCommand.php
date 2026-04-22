<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DevDbAnalyzeCommand extends Command
{
    protected $signature = 'dev:db-analyze {--output= : Ruta del archivo JSON de salida}';

    protected $description = 'Analiza la estructura de la BD de dev (tablas, columnas, FKs, índices) para reporte de compatibilidad.';

    public function handle(): int
    {
        $db = config('database.connections.mysql.database');

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->error('No se pudo conectar a la BD. ¿Tienes el túnel SSH abierto?');
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $tables = DB::select("SHOW TABLES");
        $key = 'Tables_in_'.$db;
        $tableNames = array_map(fn ($row) => $row->{$key}, $tables);

        $result = [
            'database' => $db,
            'tables' => [],
        ];

        foreach ($tableNames as $table) {
            $columns = DB::select("SHOW FULL COLUMNS FROM `{$table}`");
            $indexes = DB::select("SHOW INDEX FROM `{$table}`");
            $createTable = DB::selectOne("SHOW CREATE TABLE `{$table}`");
            $createSql = $createTable->{'Create Table'} ?? null;

            $fks = [];
            if ($createSql && preg_match_all('/CONSTRAINT `([^`]+)` FOREIGN KEY/', $createSql, $m)) {
                foreach ($m[1] as $fkName) {
                    $fkInfo = DB::selectOne("
                        SELECT
                            COLUMN_NAME,
                            REFERENCED_TABLE_NAME,
                            REFERENCED_COLUMN_NAME
                        FROM information_schema.KEY_COLUMN_USAGE
                        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?
                    ", [$db, $table, $fkName]);
                    if ($fkInfo) {
                        $fks[] = [
                            'constraint' => $fkName,
                            'column' => $fkInfo->COLUMN_NAME,
                            'referenced_table' => $fkInfo->REFERENCED_TABLE_NAME,
                            'referenced_column' => $fkInfo->REFERENCED_COLUMN_NAME,
                        ];
                    }
                }
            }

            $result['tables'][$table] = [
                'columns' => array_map(fn ($c) => [
                    'field' => $c->Field,
                    'type' => $c->Type,
                    'null' => $c->Null,
                    'key' => $c->Key,
                    'default' => $c->Default,
                    'extra' => $c->Extra,
                ], $columns),
                'foreign_keys' => $fks,
                'indexes' => array_values(array_map(fn ($i) => [
                    'name' => $i->Key_name,
                    'column' => $i->Column_name,
                    'unique' => (bool) ($i->Non_unique == 0),
                ], $indexes)),
            ];
        }

        $outputPath = $this->option('output') ?? storage_path('app/dev_db_analyze.json');
        file_put_contents($outputPath, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info('Análisis guardado en: '.$outputPath);

        return self::SUCCESS;
    }
}
