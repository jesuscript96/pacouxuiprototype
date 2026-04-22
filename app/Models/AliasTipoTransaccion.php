<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AliasTipoTransaccion extends Model
{
    protected $table = 'alias_tipo_transacciones';

    protected $fillable = ['empresa_id', 'tipo_transaccion', 'alias'];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
