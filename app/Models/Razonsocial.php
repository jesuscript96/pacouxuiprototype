<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Razonsocial extends Model
{
    protected $table = 'razones_sociales';

    protected $fillable = ['nombre', 'rfc', 'cp', 'calle', 'numero_exterior', 'numero_interior', 'colonia', 'alcaldia', 'estado', 'registro_patronal'];

    public function empresas()
    {
        return $this->belongsToMany(Empresa::class, 'empresas_razones_sociales', 'razon_social_id', 'empresa_id');
    }
}
