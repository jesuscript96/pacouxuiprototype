<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdelantoNomina extends Model
{
    protected $table = 'adelantos_nomina';

    protected $fillable = [
        'transaccion_id',
        'cuenta_bancaria_id',
        'id_transferencia',
        'centro_costo',
        'clave_seguimiento',
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
