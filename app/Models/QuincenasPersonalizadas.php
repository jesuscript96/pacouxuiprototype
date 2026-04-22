<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuincenasPersonalizadas extends Model
{
    protected $table = 'quincenas_personalizadas';

    protected $fillable = ['empresa_id', 'dia_inicio', 'dia_fin'];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
