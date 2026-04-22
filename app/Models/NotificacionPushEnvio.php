<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacionPushEnvio extends Model
{
    protected $table = 'notificacion_push_envios';

    protected $fillable = [
        'notificacion_push_id',
        'chunk_numero',
        'tokens_enviados',
        'onesignal_notification_id',
        'onesignal_response',
        'estado',
        'error_mensaje',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'onesignal_response' => 'array',
        ];
    }

    public function notificacionPush(): BelongsTo
    {
        return $this->belongsTo(NotificacionPush::class);
    }

    /**
     * @param  array<string, mixed>  $response
     */
    public function marcarComoEnviado(string $notificationId, array $response): void
    {
        $this->update([
            'estado' => 'enviado',
            'onesignal_notification_id' => $notificationId,
            'onesignal_response' => $response,
        ]);
    }

    public function marcarComoFallido(string $error): void
    {
        $this->update([
            'estado' => 'fallido',
            'error_mensaje' => $error,
        ]);
    }
}
