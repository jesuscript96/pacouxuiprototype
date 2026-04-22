<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionRetencionNomina extends Model
{
    protected $table = 'configuracion_retencion_nominas';

    protected $casts = [
        'emails' => 'array',
    ];

    protected $fillable = [
        'fecha',
        'dias',
        'dia_semana',
        'emails',
        'periodicidad_pago',
        'empresa_id',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
