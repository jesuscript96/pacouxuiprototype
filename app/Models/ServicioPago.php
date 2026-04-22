<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Detalle de transacciones tipo «PAGO DE SERVICIO» (reportes internos).
 */
class ServicioPago extends Model
{
    protected $table = 'servicios_pago';

    protected $fillable = [
        'id_producto_externo',
        'id_cuenta_externo',
        'modo_pago',
        'referencia_extra_cuenta',
        'transaccion_id',
        'cuenta_bancaria_id',
        'nombre_producto',
        'tipo',
        'centro_costo',
        'codigo_operacion',
        'latitud',
        'longitud',
    ];

    public function transaccion(): BelongsTo
    {
        return $this->belongsTo(Transaccion::class, 'transaccion_id');
    }

    public function cuentaBancaria(): BelongsTo
    {
        return $this->belongsTo(CuentaBancaria::class, 'cuenta_bancaria_id');
    }
}
