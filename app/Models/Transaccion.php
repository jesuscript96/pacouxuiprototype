<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaccion extends Model
{
    protected $table = 'transacciones';

    protected $fillable = [
        'fecha',
        'tipo',
        'monto',
        'comision',
        'estado_cuenta_id',
        'estado',
        'tipo_pago',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'monto' => 'decimal:2',
            'comision' => 'decimal:2',
        ];
    }

    public function estadoCuenta(): BelongsTo
    {
        return $this->belongsTo(EstadoCuenta::class, 'estado_cuenta_id');
    }

    public function transaccionExcluida(): HasOne
    {
        return $this->hasOne(TransaccionExcluida::class, 'transaccion_id');
    }

    public function penalizacionExclusiva(): HasOne
    {
        return $this->hasOne(PenalizacionExclusiva::class, 'transaccion_id');
    }

    public function adelantoNomina(): HasOne
    {
        return $this->hasOne(AdelantoNomina::class, 'transaccion_id');
    }

    public function recarga(): HasOne
    {
        return $this->hasOne(Recarga::class, 'transaccion_id');
    }

    public function servicioPago(): HasOne
    {
        return $this->hasOne(ServicioPago::class, 'transaccion_id');
    }
}
