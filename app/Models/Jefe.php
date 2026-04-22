<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de códigos de jerarquía por colaborador (equivalente legacy `bosses` → `high_employee_id`).
 */
class Jefe extends Model
{
    /** @use HasFactory<\Database\Factories\JefeFactory> */
    use HasFactory;

    protected $table = 'jefes';

    protected $fillable = [
        'colaborador_id',
        'codigo_nivel_1',
        'codigo_nivel_2',
        'codigo_nivel_3',
        'codigo_nivel_4',
    ];

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class);
    }
}
