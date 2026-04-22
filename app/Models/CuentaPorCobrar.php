<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CuentaPorCobrar extends Model
{
    protected $table = 'cuentas_por_cobrar';

    protected $fillable = [
        'estado',
        'debe',
        'estado_cuenta_id',
        'cuenta_bancaria_id',
        'empresa_id',
        'user_id',
        'fecha_pago',
        'fecha_confirmacion_pago',
        'comentarios',
        'tipo_confirmacion_pago',
        'comisiones_bancarias',
        'periodicidad_pago',
        'ubicacion_id',
        'puesto_id',
        'departamento_id',
        'area_id',
        'parent_id',
        'centro_costo',
    ];

    protected function casts(): array
    {
        return [
            'debe' => 'decimal:2',
            'fecha_pago' => 'datetime',
            'fecha_confirmacion_pago' => 'datetime',
            'comisiones_bancarias' => 'decimal:2',
        ];
    }

    public function estadoCuenta(): BelongsTo
    {
        return $this->belongsTo(EstadoCuenta::class, 'estado_cuenta_id');
    }

    public function cuentaBancaria(): BelongsTo
    {
        return $this->belongsTo(CuentaBancaria::class, 'cuenta_bancaria_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class, 'puesto_id');
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function hijos(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function intentosCobro(): HasMany
    {
        return $this->hasMany(IntentoCobro::class, 'cuenta_por_cobrar_id');
    }

    public function transaccionesExcluidas(): HasMany
    {
        return $this->hasMany(TransaccionExcluida::class, 'cuenta_por_cobrar_id');
    }

    public function penalizacionesExclusivas(): HasMany
    {
        return $this->hasMany(PenalizacionExclusiva::class, 'cuenta_por_cobrar_id');
    }

    public function retencionNomina(): HasOne
    {
        return $this->hasOne(RetencionNomina::class, 'cuenta_por_cobrar_id');
    }
}
