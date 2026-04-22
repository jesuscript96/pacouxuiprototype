<?php

declare(strict_types=1);

namespace App\Actions\NotificacionesPush;

use App\Jobs\EnviarNotificacionPushJob;
use App\Models\NotificacionPush;
use App\Services\NotificacionesPush\ResolverDestinatariosService;
use DateTimeInterface;

class EnviarNotificacionPushAction
{
    public function __construct(
        protected ResolverDestinatariosService $resolverDestinatarios
    ) {}

    /**
     * @return array{success: bool, message: string, destinatarios?: int}
     */
    public function enviarAhora(NotificacionPush $notificacion): array
    {
        if (! $notificacion->puedeEnviarse()) {
            return [
                'success' => false,
                'message' => "La notificación no puede enviarse (estado: {$notificacion->estado->getLabel()})",
            ];
        }

        $totalDestinatarios = $notificacion->cantidadDestinatarios();

        if ($totalDestinatarios === 0) {
            $totalDestinatarios = $this->resolverDestinatarios->persistirDestinatarios($notificacion);
        }

        if ($totalDestinatarios === 0) {
            return [
                'success' => false,
                'message' => 'No hay destinatarios para esta notificación.',
            ];
        }

        $empresa = $notificacion->empresa;

        if ($empresa?->getOneSignalCredentials() === null) {
            return [
                'success' => false,
                'message' => 'La empresa no tiene OneSignal configurado.',
            ];
        }

        EnviarNotificacionPushJob::dispatch($notificacion);

        return [
            'success' => true,
            'message' => 'Notificación enviada a la cola de procesamiento.',
            'destinatarios' => $totalDestinatarios,
        ];
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function programar(NotificacionPush $notificacion, DateTimeInterface $fecha): array
    {
        if (! $notificacion->puedeEnviarse()) {
            return [
                'success' => false,
                'message' => "La notificación no puede programarse (estado: {$notificacion->estado->getLabel()})",
            ];
        }

        if ($fecha <= now()) {
            return [
                'success' => false,
                'message' => 'La fecha de programación debe ser en el futuro',
            ];
        }

        $notificacion->programarPara($fecha);

        return [
            'success' => true,
            'message' => 'Notificación programada para '.$fecha->format('d/m/Y H:i'),
        ];
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function cancelar(NotificacionPush $notificacion): array
    {
        if (! $notificacion->esCancelable()) {
            return [
                'success' => false,
                'message' => "La notificación no puede cancelarse (estado: {$notificacion->estado->getLabel()})",
            ];
        }

        $notificacion->cancelar();

        return [
            'success' => true,
            'message' => 'Notificación cancelada',
        ];
    }
}
