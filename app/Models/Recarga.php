<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recarga extends Model
{
    protected $table = 'recargas';

    protected $fillable = [
        'id_producto_externo',
        'id_cuenta_externo',
        'transaccion_id',
        'cuenta_bancaria_id',
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
