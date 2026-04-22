<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrecuenciaNotificaciones extends Model
{
    protected $table = 'frecuencia_notificaciones';

    protected $fillable = ['empresa_id', 'dias', 'tipo', 'siguiente_fecha'];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
