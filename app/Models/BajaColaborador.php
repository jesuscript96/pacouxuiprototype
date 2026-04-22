<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @use HasFactory<\Database\Factories\BajaColaboradorFactory>
 */
class BajaColaborador extends Model
{
    use HasFactory;
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'bajas_colaboradores';

    public const MOTIVO_ABANDONO = 'ABANDONO';

    public const MOTIVO_DESPIDO = 'DESPIDO';

    public const MOTIVO_FALLECIMIENTO = 'FALLECIMIENTO';

    public const MOTIVO_RENUNCIA = 'RENUNCIA';

    public const MOTIVO_TERMINO_CONTRATO = 'TERMINO_CONTRATO';

    public const ESTADO_PROGRAMADA = 'PROGRAMADA';

    public const ESTADO_EJECUTADA = 'EJECUTADA';

    public const ESTADO_CANCELADA = 'CANCELADA';

    protected $fillable = [
        'colaborador_id',
        'user_id',
        'empresa_id',
        'fecha_baja',
        'motivo',
        'comentarios',
        'estado',
        'ubicacion_id',
        'departamento_id',
        'area_id',
        'puesto_id',
        'region_id',
        'centro_pago_id',
        'razon_social_id',
        'registrado_por',
        'ejecutada_at',
    ];

    protected function casts(): array
    {
        return [
            'fecha_baja' => 'date',
            'ejecutada_at' => 'datetime',
        ];
    }

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function centroPago(): BelongsTo
    {
        return $this->belongsTo(CentroPago::class, 'centro_pago_id');
    }

    public function razonSocial(): BelongsTo
    {
        return $this->belongsTo(Razonsocial::class, 'razon_social_id');
    }

    public function reingreso(): HasOne
    {
        return $this->hasOne(ReingresoColaborador::class, 'baja_colaborador_id');
    }

    public function tieneReingreso(): bool
    {
        return $this->reingreso()->exists();
    }

    public function puedeReingresar(): bool
    {
        return $this->estaEjecutada()
            && ! $this->trashed()
            && ! $this->tieneReingreso();
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeProgramadas(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_PROGRAMADA);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeEjecutadas(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_EJECUTADA);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeVencidas(Builder $query): Builder
    {
        return $query->programadas()
            ->whereDate('fecha_baja', '<=', now()->toDateString());
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeDeEmpresa(Builder $query, int $empresaId): Builder
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function esProgramada(): bool
    {
        return $this->estado === self::ESTADO_PROGRAMADA;
    }

    public function estaEjecutada(): bool
    {
        return $this->estado === self::ESTADO_EJECUTADA;
    }

    public function estaCancelada(): bool
    {
        return $this->estado === self::ESTADO_CANCELADA;
    }

    public function esFechaFutura(): bool
    {
        return $this->fecha_baja->isFuture();
    }

    /**
     * @return array<string, string>
     */
    public static function motivosDisponibles(): array
    {
        return [
            self::MOTIVO_ABANDONO => 'Abandono',
            self::MOTIVO_DESPIDO => 'Despido',
            self::MOTIVO_FALLECIMIENTO => 'Fallecimiento',
            self::MOTIVO_RENUNCIA => 'Renuncia',
            self::MOTIVO_TERMINO_CONTRATO => 'Término de contrato',
        ];
    }
}
