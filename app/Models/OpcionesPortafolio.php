<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpcionesPortafolio extends Model
{
    protected $table = 'opciones_portafolio';

    protected $fillable = ['opcion', 'nombre', 'empresa_id'];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
