<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoNotificacionPush;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificacionPush extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'notificaciones_push';

    protected $fillable = [
        'empresa_id',
        'titulo',
        'mensaje',
        'url',
        'data',
        'filtros',
        'estado',
        'programada_para',
        'enviada_at',
        'total_destinatarios',
        'total_enviados',
        'total_fallidos',
        'creado_por',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'filtros' => 'array',
            'estado' => EstadoNotificacionPush::class,
            'programada_para' => 'datetime',
            'enviada_at' => 'datetime',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /**
     * @return HasMany<NotificacionPushEnvio, $this>
     */
    public function envios(): HasMany
    {
        return $this->hasMany(NotificacionPushEnvio::class);
    }

    /**
     * @return HasMany<NotificacionPushDestinatario, $this>
     */
    public function destinatarios(): HasMany
    {
        return $this->hasMany(NotificacionPushDestinatario::class, 'notificacion_push_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function usersDestinatarios(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'notificacion_push_destinatarios',
            'notificacion_push_id',
            'user_id'
        )->withPivot(['estado_lectura', 'leida_at', 'enviado', 'onesignal_player_id', 'enviado_at'])
            ->withTimestamps();
    }

    public function cantidadDestinatarios(): int
    {
        return $this->destinatarios()->count();
    }

    public function cantidadLeidas(): int
    {
        return $this->destinatarios()->where('estado_lectura', 'LEIDA')->count();
    }

    public function cantidadEnviados(): int
    {
        return $this->destinatarios()->where('enviado', true)->count();
    }

    /**
     * @param  Builder<NotificacionPush>  $query
     * @return Builder<NotificacionPush>
     */
    public function scopePendientesDeEnvio(Builder $query): Builder
    {
        return $query->where('estado', EstadoNotificacionPush::PROGRAMADA)
            ->where('programada_para', '<=', now());
    }

    /**
     * @param  Builder<NotificacionPush>  $query
     * @return Builder<NotificacionPush>
     */
    public function scopeBorradores(Builder $query): Builder
    {
        return $query->where('estado', EstadoNotificacionPush::BORRADOR);
    }

    /**
     * @param  Builder<NotificacionPush>  $query
     * @return Builder<NotificacionPush>
     */
    public function scopeEnviadas(Builder $query): Builder
    {
        return $query->where('estado', EstadoNotificacionPush::ENVIADA);
    }

    public function esEditable(): bool
    {
        return $this->estado->esEditable();
    }

    public function esCancelable(): bool
    {
        return $this->estado->esCancelable();
    }

    public function puedeEnviarse(): bool
    {
        return $this->estado->puedeEnviarse();
    }

    /**
     * Incluye ENVIANDO para el flujo programado: el comando marca ENVIANDO antes de despachar el job.
     */
    public function puedeProcesarEnvioPorJob(): bool
    {
        return in_array($this->estado, [
            EstadoNotificacionPush::BORRADOR,
            EstadoNotificacionPush::PROGRAMADA,
            EstadoNotificacionPush::ENVIANDO,
        ], true);
    }

    public function marcarComoEnviando(): void
    {
        $this->update(['estado' => EstadoNotificacionPush::ENVIANDO]);
    }

    public function marcarComoEnviada(int $enviados, int $fallidos = 0): void
    {
        $this->update([
            'estado' => EstadoNotificacionPush::ENVIADA,
            'enviada_at' => now(),
            'total_enviados' => $enviados,
            'total_fallidos' => $fallidos,
        ]);
    }

    public function marcarComoFallida(): void
    {
        $this->update(['estado' => EstadoNotificacionPush::FALLIDA]);
    }

    public function cancelar(): void
    {
        if ($this->esCancelable()) {
            $this->update(['estado' => EstadoNotificacionPush::CANCELADA]);
        }
    }

    public function programarPara(DateTimeInterface $fechaHora): void
    {
        $this->update([
            'estado' => EstadoNotificacionPush::PROGRAMADA,
            'programada_para' => $fechaHora,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFiltrosConDefaults(): array
    {
        $defaults = [
            'ubicaciones' => [],
            'areas' => [],
            'departamentos' => [],
            'puestos' => [],
            'generos' => [],
            'edad_minima' => null,
            'edad_maxima' => null,
            'antiguedad_minima_meses' => null,
            'antiguedad_maxima_meses' => null,
            'con_adeudos' => null,
            'cumpleaneros_mes' => null,
            'destinatarios' => [
                'select_all' => true,
                'manual_activation' => [],
                'manual_deactivation' => [],
            ],
        ];

        return array_merge($defaults, $this->filtros ?? []);
    }

    /**
     * Estadísticas de lectura basadas en filas del pivote de destinatarios.
     *
     * @return array{
     *     total_destinatarios: int,
     *     enviados: int,
     *     pendientes_envio: int,
     *     leidas: int,
     *     no_leidas: int,
     *     porcentaje_lectura: float
     * }
     */
    public function getEstadisticasLectura(): array
    {
        $total = $this->destinatarios()->count();
        $enviados = $this->destinatarios()->where('enviado', true)->count();
        $leidas = $this->destinatarios()->where('estado_lectura', 'LEIDA')->count();
        $noLeidas = $this->destinatarios()
            ->where('estado_lectura', 'NO_LEIDA')
            ->where('enviado', true)
            ->count();

        return [
            'total_destinatarios' => $total,
            'enviados' => $enviados,
            'pendientes_envio' => $total - $enviados,
            'leidas' => $leidas,
            'no_leidas' => $noLeidas,
            'porcentaje_lectura' => $enviados > 0 ? round(($leidas / $enviados) * 100, 1) : 0.0,
        ];
    }

    public function getPorcentajeLecturaAttribute(): float
    {
        $stats = $this->getEstadisticasLectura();

        return $stats['porcentaje_lectura'];
    }
}
