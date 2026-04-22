<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\EstadoNotificacionPush;
use App\Jobs\EnviarNotificacionPushJob;
use App\Models\NotificacionPush;
use App\Services\NotificacionesPush\ResolverDestinatariosService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcesarNotificacionesPushProgramadasCommand extends Command
{
    protected $signature = 'notificaciones:enviar-programadas
                            {--limit=50 : Máximo de notificaciones a procesar por ejecución}
                            {--dry-run : Simular sin despachar jobs}';

    protected $description = 'Procesa y envía las notificaciones push programadas cuya fecha ya pasó';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Buscando notificaciones push programadas...');

        if ($dryRun) {
            $notificaciones = NotificacionPush::query()
                ->pendientesDeEnvio()
                ->orderBy('programada_para')
                ->limit($limit)
                ->get();

            if ($notificaciones->isEmpty()) {
                $this->info('No hay notificaciones programadas pendientes.');

                return self::SUCCESS;
            }

            $this->info("Encontradas: {$notificaciones->count()} notificaciones (dry-run).");
            foreach ($notificaciones as $notificacion) {
                $this->line("  [DRY-RUN] ID {$notificacion->id}: {$notificacion->titulo}");
            }

            return self::SUCCESS;
        }

        $toDispatch = [];
        $fallidas = 0;

        DB::transaction(function () use ($limit, &$toDispatch, &$fallidas): void {
            $notificaciones = NotificacionPush::query()
                ->with('empresa')
                ->pendientesDeEnvio()
                ->orderBy('programada_para')
                ->limit($limit)
                ->lockForUpdate()
                ->get();

            if ($notificaciones->isEmpty()) {
                return;
            }

            foreach ($notificaciones as $notificacion) {
                try {
                    $notificacion->marcarComoEnviando();
                    $notificacion->refresh();

                    $empresa = $notificacion->empresa;
                    if ($empresa?->getOneSignalCredentials() === null) {
                        Log::warning('NotificacionesProgramadas: Empresa sin OneSignal configurado', [
                            'notificacion_id' => $notificacion->id,
                            'empresa_id' => $notificacion->empresa_id,
                        ]);
                        $notificacion->marcarComoFallida();
                        $fallidas++;
                        $this->warn("  ID {$notificacion->id}: Empresa sin OneSignal configurado");

                        continue;
                    }

                    $resolverService = app(ResolverDestinatariosService::class);
                    $totalDestinatarios = $resolverService->recalcularDestinatarios($notificacion);

                    if ($totalDestinatarios === 0) {
                        Log::warning('NotificacionesProgramadas: Sin destinatarios después de recalcular', [
                            'notificacion_id' => $notificacion->id,
                        ]);
                        $notificacion->marcarComoFallida();
                        $fallidas++;
                        $this->warn("  ID {$notificacion->id}: Sin destinatarios tras recalcular");

                        continue;
                    }

                    $toDispatch[] = $notificacion->id;
                } catch (Throwable $e) {
                    $fallidas++;
                    $this->error("  ID {$notificacion->id}: Error - {$e->getMessage()}");

                    Log::error('NotificacionesProgramadas: Error al preparar envío', [
                        'notificacion_id' => $notificacion->id,
                        'error' => $e->getMessage(),
                    ]);

                    $notificacion->refresh();
                    if ($notificacion->estado === EstadoNotificacionPush::ENVIANDO) {
                        $notificacion->update(['estado' => EstadoNotificacionPush::PROGRAMADA]);
                    }
                }
            }
        });

        if ($toDispatch === [] && $fallidas === 0) {
            $this->info('No hay notificaciones programadas pendientes.');

            return self::SUCCESS;
        }

        $procesadas = 0;
        foreach ($toDispatch as $id) {
            $modelo = NotificacionPush::query()->find($id);
            if ($modelo === null) {
                continue;
            }

            EnviarNotificacionPushJob::dispatch($modelo);
            $procesadas++;

            $this->line("  ID {$id}: Despachada a cola");

            Log::info('NotificacionesProgramadas: Job despachado', [
                'notificacion_id' => $id,
                'empresa_id' => $modelo->empresa_id,
                'titulo' => $modelo->titulo,
            ]);
        }

        $this->newLine();
        $this->info("Resumen: {$procesadas} despachadas a cola, {$fallidas} fallidas al preparar.");

        return $fallidas > 0 ? self::FAILURE : self::SUCCESS;
    }
}
