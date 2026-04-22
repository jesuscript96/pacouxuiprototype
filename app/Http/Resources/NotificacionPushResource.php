<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\NotificacionPushDestinatario
 */
class NotificacionPushResource extends JsonResource
{
    /**
     * El payload incluye la clave `data`; sin forzar envoltorio, Laravel no aplica el wrapper `data` y rompe el formato estándar de la API.
     */
    public static bool $forceWrapping = true;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $notificacion = $this->notificacionPush;

        return [
            'id' => $notificacion->id,
            'titulo' => $notificacion->titulo,
            'mensaje' => $notificacion->mensaje,
            'url' => $notificacion->url,
            'data' => $notificacion->data,
            'enviada_at' => $notificacion->enviada_at?->toIso8601String(),
            'estado_lectura' => $this->estado_lectura,
            'leida' => $this->estado_lectura === 'LEIDA',
            'leida_at' => $this->leida_at?->toIso8601String(),
            'recibida_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
