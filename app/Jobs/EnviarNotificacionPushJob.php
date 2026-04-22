<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\EstadoNotificacionPush;
use App\Models\NotificacionPush;
use App\Models\NotificacionPushDestinatario;
use App\Models\NotificacionPushEnvio;
use App\Services\OneSignal\OneSignalService;
use App\Services\Pumble\PumbleNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class EnviarNotificacionPushJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [30, 60, 120];

    public int $timeout = 300;

    public function __construct(
        public NotificacionPush $notificacion
    ) {
        $this->onQueue('notificaciones');
    }

    public function handle(OneSignalService $oneSignalService): void
    {
        $notificacion = $this->notificacion->fresh();

        if (! $notificacion || ! $notificacion->puedeProcesarEnvioPorJob()) {
            Log::warning('EnviarNotificacionPushJob: Notificación no procesable', [
                'notificacion_id' => $this->notificacion->id,
                'estado' => $notificacion?->estado?->value,
            ]);

            return;
        }

        if ($notificacion->estado !== EstadoNotificacionPush::ENVIANDO) {
            $notificacion->marcarComoEnviando();
        }

        $empresa = $notificacion->empresa;

        if ($empresa?->getOneSignalCredentials() === null) {
            Log::error('EnviarNotificacionPushJob: Empresa sin OneSignal configurado', [
                'notificacion_id' => $notificacion->id,
                'empresa_id' => $notificacion->empresa_id,
            ]);
            $notificacion->marcarComoFallida();

            return;
        }

        $oneSignalService->paraEmpresa($empresa);

        $destinatariosPendientes = $notificacion->destinatarios()
            ->where('enviado', false)
            ->get();

        if ($destinatariosPendientes->isEmpty()) {
            Log::info('EnviarNotificacionPushJob: No hay destinatarios pendientes', [
                'notificacion_id' => $notificacion->id,
            ]);
            $notificacion->marcarComoEnviada(0, 0);

            $this->intentarNotificarPumble();

            return;
        }

        Log::info('EnviarNotificacionPushJob: Iniciando envío', [
            'notificacion_id' => $notificacion->id,
            'destinatarios_pendientes' => $destinatariosPendientes->count(),
        ]);

        $tokensMap = $this->obtenerTokensDeDestinatarios($destinatariosPendientes);

        if ($tokensMap->isEmpty()) {
            Log::warning('EnviarNotificacionPushJob: No hay tokens disponibles', [
                'notificacion_id' => $notificacion->id,
            ]);
            $notificacion->marcarComoFallida();

            $this->intentarNotificarPumble();

            return;
        }

        $chunkSize = 2000;
        $chunks = $tokensMap->chunk($chunkSize);
        $chunkNumero = 0;
        $totalEnviados = 0;
        $totalFallidos = 0;

        foreach ($chunks as $chunk) {
            $chunkNumero++;
            $tokens = $chunk->pluck('token')->all();
            $destinatarioIds = $chunk->pluck('destinatario_id')->all();

            $envio = NotificacionPushEnvio::query()->create([
                'notificacion_push_id' => $notificacion->id,
                'chunk_numero' => $chunkNumero,
                'tokens_enviados' => count($tokens),
                'estado' => 'pendiente',
            ]);

            try {
                $response = $oneSignalService->enviarATokens(
                    $tokens,
                    $notificacion->titulo,
                    $notificacion->mensaje,
                    $this->buildData($notificacion),
                    $this->buildOpciones($notificacion)
                );

                if ($response !== null) {
                    $envio->marcarComoEnviado(
                        (string) ($response['id'] ?? 'unknown'),
                        $response
                    );

                    $recipients = (int) ($response['recipients'] ?? count($tokens));
                    $totalEnviados += $recipients;

                    NotificacionPushDestinatario::query()
                        ->whereIn('id', $destinatarioIds)
                        ->update([
                            'enviado' => true,
                            'enviado_at' => now(),
                        ]);
                } else {
                    $envio->marcarComoFallido('Respuesta vacía de OneSignal');
                    $totalFallidos += count($tokens);
                }

                Log::info('EnviarNotificacionPushJob: Chunk enviado', [
                    'notificacion_id' => $notificacion->id,
                    'chunk' => $chunkNumero,
                    'tokens' => count($tokens),
                ]);
            } catch (Throwable $e) {
                $envio->marcarComoFallido($e->getMessage());
                $totalFallidos += count($tokens);

                Log::error('EnviarNotificacionPushJob: Error en chunk', [
                    'notificacion_id' => $notificacion->id,
                    'chunk' => $chunkNumero,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($totalEnviados > 0) {
            $notificacion->marcarComoEnviada($totalEnviados, $totalFallidos);
        } else {
            $notificacion->marcarComoFallida();
        }

        Log::info('EnviarNotificacionPushJob: Envío completado', [
            'notificacion_id' => $notificacion->id,
            'total_enviados' => $totalEnviados,
            'total_fallidos' => $totalFallidos,
        ]);

        $this->intentarNotificarPumble();
    }

    private function intentarNotificarPumble(): void
    {
        if (! config('services.pumble.enabled')) {
            return;
        }

        try {
            $record = $this->notificacion->fresh();
            if ($record !== null) {
                app(PumbleNotificationService::class)->enviarNotificacionPush($record);
            }
        } catch (Throwable $e) {
            Log::warning('Error enviando a Pumble: '.$e->getMessage());
        }
    }

    /**
     * @param  Collection<int, NotificacionPushDestinatario>  $destinatarios
     * @return Collection<int, array{destinatario_id: int, token: string}>
     */
    protected function obtenerTokensDeDestinatarios(Collection $destinatarios): Collection
    {
        return $destinatarios->map(function (NotificacionPushDestinatario $dest): ?array {
            if ($dest->user_id === null) {
                return null;
            }

            return [
                'destinatario_id' => $dest->id,
                'token' => "placeholder-user-{$dest->user_id}",
            ];
        })->filter()->values();
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildData(NotificacionPush $notificacion): array
    {
        $data = [
            'type' => 'NOTIFICACION_CUSTOM',
            'notificacion_id' => $notificacion->id,
        ];

        if (! empty($notificacion->data)) {
            /** @var array<string, mixed> $extra */
            $extra = $notificacion->data;

            $data = array_merge($data, $extra);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildOpciones(NotificacionPush $notificacion): array
    {
        $opciones = [];

        if (! empty($notificacion->url)) {
            $opciones['url'] = $notificacion->url;
        }

        return $opciones;
    }

    public function failed(Throwable $exception): void
    {
        Log::error('EnviarNotificacionPushJob: Job falló', [
            'notificacion_id' => $this->notificacion->id,
            'error' => $exception->getMessage(),
        ]);

        $this->notificacion->fresh()?->marcarComoFallida();
    }
}
