<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DevDbReportCommand extends Command
{
    protected $signature = 'dev:db-report {--json=storage/app/dev_db_analyze.json : Ruta al JSON de análisis}';

    protected $description = 'Genera docs/base-datos/reporte-compatibilidad-rafa.md a partir del JSON de dev:db-analyze.';

    private const TABLAS_RAFA_ESPERADAS = [
        'empresas',
        'departamentos',
        'puestos',
        'bancos',
        'temas_voz',
        'productos',
    ];

    public function handle(): int
    {
        $path = base_path($this->option('json'));
        if (! File::exists($path)) {
            $this->error('No existe el archivo de análisis. Ejecuta antes: php artisan dev:db-analyze');
            $this->line('Ruta esperada: '.$path);

            return self::FAILURE;
        }

        $data = json_decode(File::get($path), true);
        if (! $data || ! isset($data['tables'])) {
            $this->error('JSON inválido o sin clave "tables".');

            return self::FAILURE;
        }

        $report = $this->buildReport($data);
        $outPath = base_path('docs/base-datos/reporte-compatibilidad-rafa.md');
        File::put($outPath, $report);
        $this->info('Reporte generado: '.$outPath);

        return self::SUCCESS;
    }

    private function buildReport(array $data): string
    {
        $db = $data['database'] ?? 'N/A';
        $tables = $data['tables'] ?? [];
        $tablasRafa = self::TABLAS_RAFA_ESPERADAS;

        $lines = [
            '# Reporte de compatibilidad: BD dev vs Fase 1 (tablas de Rafa)',
            '# Esto es con el fin de generar un reporte actual de la base de datos paco_dev_db',
            '',
            'Generado a partir de `dev_db_analyze.json` (BD: **'.$db.'**).',
            '',
            '---',
            '',
            '## 1. Tablas existentes en dev',
            '',
            'Total: **'.count($tables).'** tablas.',
            '',
            '| Tabla | Columnas | FKs | Índices |',
            '|-------|----------|-----|---------|',
        ];

        foreach ($tables as $name => $info) {
            $cols = count($info['columns'] ?? []);
            $fks = count($info['foreign_keys'] ?? []);
            $idx = count($info['indexes'] ?? []);
            $lines[] = "| {$name} | {$cols} | {$fks} | {$idx} |";
        }

        $lines[] = '';
        $lines[] = '---';
        $lines[] = '';
        $lines[] = '## 2. Tablas de Rafa requeridas por Fase 1';
        $lines[] = '';
        $lines[] = 'Nuestras migraciones referencian las siguientes tablas. Deben existir y tener columna `id` (BIGINT UNSIGNED) para las FK.';
        $lines[] = '';

        foreach ($tablasRafa as $tabla) {
            $lines[] = '### '.$tabla;
            $lines[] = '';

            if (! isset($tables[$tabla])) {
                $lines[] = '**Estado:** NO existe en la BD de dev. Las migraciones de Fase 1 que referencian esta tabla fallarán hasta que Rafa la cree.';
                $lines[] = '';
                $lines[] = '**Acción:** Esperar a que Rafa suba el código o crear la tabla en dev con al menos `id` (bigint unsigned, PK).';
                $lines[] = '';
                continue;
            }

            $info = $tables[$tabla];
            $lines[] = '**Estado:** Existe.';
            $lines[] = '';

            $lines[] = '**Columnas (DESCRIBE):**';
            $lines[] = '';
            $lines[] = '| Campo | Tipo | Null | Key | Default | Extra |';
            $lines[] = '|-------|------|------|-----|---------|-------|';

            foreach ($info['columns'] as $c) {
                $lines[] = '| '.$c['field'].' | '.$c['type'].' | '.$c['null'].' | '.($c['key'] ?: '-').' | '.($c['default'] ?? 'NULL').' | '.($c['extra'] ?? '-').' |';
            }

            $lines[] = '';

            if (! empty($info['foreign_keys'])) {
                $lines[] = '**Foreign keys (salientes):**';
                $lines[] = '';
                foreach ($info['foreign_keys'] as $fk) {
                    $lines[] = '- `'.$fk['column'].'` → '.$fk['referenced_table'].'.'.$fk['referenced_column'];
                }
                $lines[] = '';
            }

            if (! empty($info['indexes'])) {
                $lines[] = '**Índices:**';
                $lines[] = '';
                foreach ($info['indexes'] as $i) {
                    $lines[] = '- '.$i['name'].' ('.$i['column'].')'.($i['unique'] ? ' UNIQUE' : '');
                }
                $lines[] = '';
            }

            $idCol = null;
            foreach ($info['columns'] as $c) {
                if ($c['field'] === 'id' && (str_contains(strtolower($c['type']), 'int'))) {
                    $idCol = $c;
                    break;
                }
            }
            if ($idCol) {
                $lines[] = '**Compatibilidad FK Fase 1:** La columna `id` existe (tipo: '.$idCol['type'].'). Nuestras migraciones usan `foreignId(...)->constrained(\''.$tabla.'\')` → correcto.';
            } else {
                $lines[] = '**Compatibilidad FK Fase 1:** No se encontró columna `id` numérica. Revisar si la PK tiene otro nombre o tipo.';
            }

            $lines[] = '';
        }

        $lines[] = '---';
        $lines[] = '';
        $lines[] = '## 3. Resumen de compatibilidad';
        $lines[] = '';

        $faltan = array_diff($tablasRafa, array_keys($tables));
        if (count($faltan) === 0) {
            $lines[] = '- Todas las tablas de Rafa requeridas por Fase 1 **existen** en dev.';
        } else {
            $lines[] = '- **Tablas faltantes en dev:** '.implode(', ', $faltan).'.';
            $lines[] = '- Ejecutar las migraciones de Fase 1 en esta BD fallará hasta que existan.';
        }

        $lines[] = '';
        $lines[] = '---';
        $lines[] = '';
        $lines[] = '*Reporte generado por `php artisan dev:db-report`. Actualizar tras volver a ejecutar `dev:db-analyze` si Rafa cambia la BD.*';

        return implode("\n", $lines);
    }
}
