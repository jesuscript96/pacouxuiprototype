<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuentaNomina extends Model
{
    use HasFactory;

    protected $table = 'cuentas_nomina';

    protected $fillable = ['user_id', 'colaborador_id', 'banco_id', 'numero_cuenta', 'tipo_cuenta', 'estado'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class);
    }

    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class);
    }
}
