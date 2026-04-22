<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class EstadoAnimoAfeccion extends Model
{
    use LogsModelActivity;

    protected $table = 'estado_animo_afecciones';

    protected $fillable = ['nombre'];
}
