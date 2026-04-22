<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoCuenta extends Model
{
    protected $table = 'estados_cuenta';

    protected $fillable = [
        'desde',
        'hasta',
        'saldo',
        'saldo_sin_comision',
        'estado',
        'periodicidad_pago',
        'user_id',
        'tipo_comision',
        'monto_comision',
    ];

    protected function casts(): array
    {
        return [
            'desde' => 'datetime',
            'hasta' => 'datetime',
            'saldo' => 'decimal:2',
            'saldo_sin_comision' => 'decimal:2',
            'monto_comision' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transacciones(): HasMany
    {
        return $this->hasMany(Transaccion::class, 'estado_cuenta_id');
    }

    public function cuentasPorCobrar(): HasMany
    {
        return $this->hasMany(CuentaPorCobrar::class, 'estado_cuenta_id');
    }
}
