<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaccionExcluida extends Model
{
    protected $table = 'transacciones_excluidas';

    protected $fillable = ['transaccion_id', 'cuenta_por_cobrar_id'];

    public function transaccion(): BelongsTo
    {
        return $this->belongsTo(Transaccion::class, 'transaccion_id');
    }

    public function cuentaPorCobrar(): BelongsTo
    {
        return $this->belongsTo(CuentaPorCobrar::class, 'cuenta_por_cobrar_id');
    }
}
