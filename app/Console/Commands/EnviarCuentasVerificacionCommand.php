<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\VerificacionCuentas\VerificacionCuentaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EnviarCuentasVerificacionCommand extends Command
{
    protected $signature = 'verificacion:enviar-pendientes
        {--dry-run : Simula el envío sin marcar cuentas como enviadas}
        {--limit= : Límite de cuentas a procesar (default: 100)}';

    protected $description = 'Envía cuentas bancarias pendientes de verificación a STP';

    public function handle(VerificacionCuentaService $service): int
    {
        $dryRun = $this->option('dry-run');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        $this->info('Iniciando envío de cuentas a verificación...');

        if ($dryRun) {
            $this->warn('Modo dry-run activado. No se marcarán cuentas como enviadas.');
        }

        try {
            $cuentas = $service->obtenerCuentasPendientesDeEnvio();

            if ($limit !== null && $limit < $cuentas->count()) {
                $cuentas = $cuentas->take($limit);
                $this->info("Limitando a {$limit} cuentas.");
            }

            if ($cuentas->isEmpty()) {
                $this->info('No hay cuentas pendientes de envío.');
                $this->info('Posibles razones:');
                $this->line('  - No hay cuentas sin verificar');
                $this->line('  - Las cuentas ya fueron enviadas');
                $this->line('  - Es hora de bloqueo (18:00-18:59 hora Guatemala)');
                $this->line('  - Los colaboradores no tienen antigüedad mínima (3 meses)');

                return self::SUCCESS;
            }

            $this->info("Encontradas {$cuentas->count()} cuentas pendientes de envío.");

            $this->table(
                ['ID', 'Número', 'Colaborador', 'Banco', 'Creada'],
                $cuentas->map(fn ($cuenta): array => [
                    $cuenta->id,
                    $cuenta->numero,
                    $cuenta->colaborador?->nombre_completo ?? 'N/A',
                    $cuenta->banco?->nombre ?? 'N/A',
                    $cuenta->created_at->format('d/m/Y'),
                ])->toArray()
            );

            $payload = $service->prepararPayloadSTP($cuentas);

            $this->info('Payload preparado para STP:');
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            if ($dryRun) {
                $this->warn("Dry-run: {$cuentas->count()} cuentas se habrían marcado como enviadas.");

                return self::SUCCESS;
            }

            $service->marcarComoEnviadas($cuentas);
            $this->info("{$cuentas->count()} cuentas marcadas como enviadas.");

            Log::info('Cuentas enviadas a verificación', [
                'cantidad' => $cuentas->count(),
                'ids' => $cuentas->pluck('id')->toArray(),
            ]);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error al procesar cuentas: '.$e->getMessage());

            Log::error('Error en envío de cuentas a verificación', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }
}
