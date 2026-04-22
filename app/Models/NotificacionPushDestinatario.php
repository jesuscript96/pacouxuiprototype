<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacionPushDestinatario extends Model
{
    protected $table = 'notificacion_push_destinatarios';

    protected $fillable = [
        'notificacion_push_id',
        'user_id',
        'estado_lectura',
        'leida_at',
        'enviado',
        'onesignal_player_id',
        'enviado_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enviado' => 'boolean',
            'leida_at' => 'datetime',
            'enviado_at' => 'datetime',
        ];
    }

    public function notificacionPush(): BelongsTo
    {
        return $this->belongsTo(NotificacionPush::class, 'notificacion_push_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * BL: Accessor para obtener el colaborador vía user->colaborador.
     * Mantiene compatibilidad con código que espere ->colaborador.
     */
    public function getColaboradorAttribute(): ?Colaborador
    {
        return $this->user?->colaborador;
    }

    public function marcarComoLeida(): void
    {
        $this->update([
            'estado_lectura' => 'LEIDA',
            'leida_at' => now(),
        ]);
    }

    public function marcarComoEnviado(?string $playerId = null): void
    {
        $this->update([
            'enviado' => true,
            'onesignal_player_id' => $playerId,
            'enviado_at' => now(),
        ]);
    }

    public function estaLeida(): bool
    {
        return $this->estado_lectura === 'LEIDA';
    }

    public function estaEnviado(): bool
    {
        return $this->enviado;
    }

    /**
     * @param  Builder<NotificacionPushDestinatario>  $query
     * @return Builder<NotificacionPushDestinatario>
     */
    public function scopeNoLeidas(Builder $query): Builder
    {
        return $query->where('estado_lectura', 'NO_LEIDA');
    }

    /**
     * @param  Builder<NotificacionPushDestinatario>  $query
     * @return Builder<NotificacionPushDestinatario>
     */
    public function scopeLeidas(Builder $query): Builder
    {
        return $query->where('estado_lectura', 'LEIDA');
    }

    /**
     * @param  Builder<NotificacionPushDestinatario>  $query
     * @return Builder<NotificacionPushDestinatario>
     */
    public function scopeEnviados(Builder $query): Builder
    {
        return $query->where('enviado', true);
    }

    /**
     * @param  Builder<NotificacionPushDestinatario>  $query
     * @return Builder<NotificacionPushDestinatario>
     */
    public function scopePendientesDeEnvio(Builder $query): Builder
    {
        return $query->where('enviado', false);
    }

    /**
     * @param  Builder<NotificacionPushDestinatario>  $query
     * @return Builder<NotificacionPushDestinatario>
     */
    public function scopeDelUsuario(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * BL: Filtra por colaborador navegando vía user.colaborador_id.
     * Mantiene compatibilidad con código que filtre por colaborador.
     *
     * @param  Builder<NotificacionPushDestinatario>  $query
     * @return Builder<NotificacionPushDestinatario>
     */
    public function scopeDelColaborador(Builder $query, int $colaboradorId): Builder
    {
        return $query->whereHas('user', fn (Builder $q): Builder => $q->where('colaborador_id', $colaboradorId));
    }
}
