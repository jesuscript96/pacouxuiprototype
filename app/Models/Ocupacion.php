<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ocupacion extends Model
{
    protected $table = 'ocupaciones';

    protected $fillable = ['descripcion'];

    public function puestos(): HasMany
    {
        return $this->hasMany(Puesto::class);
    }
}
