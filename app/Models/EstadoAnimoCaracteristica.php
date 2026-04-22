<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class EstadoAnimoCaracteristica extends Model
{
    use LogsModelActivity;

    protected $table = 'estado_animo_caracteristicas';

    protected $fillable = ['nombre', 'lista_inicial'];
}
