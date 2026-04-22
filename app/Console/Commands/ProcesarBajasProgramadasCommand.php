<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BajaColaborador;
use App\Services\ColaboradorBajaService;
use Illuminate\Console\Command;
use Throwable;

class ProcesarBajasProgramadasCommand extends Command
{
    protected $signature = 'bajas:procesar-programadas
        {--dry-run : Mostrar qué bajas se procesarían sin ejecutarlas}';

    protected $description = 'Procesa las bajas de colaboradores programadas cuya fecha de baja ya venció';

    public function handle(ColaboradorBajaService $service): int
    {
        $this->info('Buscando bajas programadas vencidas...');

        $bajasVencidas = BajaColaborador::query()
            ->vencidas()
            ->with(['colaborador.user'])
            ->orderBy('id')
            ->get();

        if ($bajasVencidas->isEmpty()) {
            $this->info('No hay bajas programadas pendientes de ejecutar.');

            return self::SUCCESS;
        }

        $this->info("Encontradas {$bajasVencidas->count()} baja(s) programada(s) vencida(s).");

        if ($this->option('dry-run')) {
            $this->warn('Modo dry-run: no se ejecutarán las bajas.');
            $this->table(
                ['ID', 'Colaborador', 'Email', 'Fecha Baja', 'Motivo'],
                $bajasVencidas->map(fn (BajaColaborador $baja): array => [
                    $baja->id,
                    $baja->colaborador?->nombre_completo ?? 'N/A',
                    $baja->colaborador?->email ?? 'N/A',
                    $baja->fecha_baja->format('d/m/Y'),
                    $baja->motivo,
                ])->all()
            );

            return self::SUCCESS;
        }

        $procesadas = 0;
        $errores = 0;

        $this->withProgressBar($bajasVencidas, function (BajaColaborador $baja) use ($service, &$procesadas, &$errores): void {
            try {
                $service->ejecutarBaja($baja);
                $procesadas++;
            } catch (Throwable $e) {
                $errores++;
                $this->newLine();
                $this->error("Error procesando baja ID {$baja->id}: {$e->getMessage()}");
            }
        });

        $this->newLine(2);
        $this->info("Procesadas: {$procesadas}");

        if ($errores > 0) {
            $this->warn("Errores: {$errores}");

            return self::FAILURE;
        }

        $this->info('Proceso completado exitosamente.');

        return self::SUCCESS;
    }
}
