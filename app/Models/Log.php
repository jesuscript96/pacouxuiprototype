<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';

    protected $fillable = ['accion', 'fecha', 'user_id', 'empresa_id'];
}
