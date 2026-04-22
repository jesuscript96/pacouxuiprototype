<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class VerificarTraduccionesCommand extends Command
{
    protected $signature = 'verificar:traducciones
                            {--output= : Ruta custom para el JSON (default: storage/app/traducciones_pendientes_YYYYMMDD_HHMMSS.json)}';

    protected $description = 'Escanea tablas y columnas de la BD, detecta nombres en inglés y genera un JSON con pendientes de traducir al español.';

    public function handle(): int
    {
        $scriptPath = database_path('scripts/verificar_traducciones.php');
        if (! File::exists($scriptPath)) {
            $this->error('No existe el script: '.$scriptPath);

            return self::FAILURE;
        }

        $outputOption = $this->option('output');
        if ($outputOption) {
            $GLOBALS['verificar_traducciones_output_file'] = base_path($outputOption);
        }

        $ret = require $scriptPath;

        $resultado = $ret['resultado'] ?? $ret;
        $outputFile = $ret['output_file'] ?? storage_path('app/traducciones_pendientes_'.date('Ymd_His').'.json');

        $this->info('Reporte generado: '.$outputFile);

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total tablas escaneadas', $resultado['total_tablas']],
                ['Tablas en inglés detectadas', count($resultado['tablas_ingles'])],
                ['Campos en inglés detectados', count($resultado['campos_ingles'])],
            ]
        );

        if (count($resultado['tablas_ingles']) > 0) {
            $this->warn('Tablas pendientes: '.implode(', ', $resultado['tablas_ingles']));
        }
        if (count($resultado['campos_ingles']) > 0) {
            $this->newLine();
            $this->line('Resumen por tabla (primeras 15):');
            $i = 0;
            foreach ($resultado['resumen'] as $tabla => $campos) {
                if ($i++ >= 15) {
                    $this->line('...');
                    break;
                }
                $this->line("  {$tabla}: ".count($campos).' campo(s) → '.implode(', ', array_column($campos, 'sugerencia')));
            }
        }

        return self::SUCCESS;
    }
}
