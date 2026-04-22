<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComisionRango extends Model
{
    protected $table = 'comisiones_rangos';

    protected $fillable = [
        'empresa_id',
        'tipo_comision',
        'precio_desde',
        'precio_hasta',
        'cantidad_fija',
        'porcentaje',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
