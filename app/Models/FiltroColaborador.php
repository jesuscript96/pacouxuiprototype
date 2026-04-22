<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiltroColaborador extends Model
{
    protected $table = 'filtros_colaboradores';

    protected $fillable = [
        'nombre',
        'empresa_id',
        'user_id',
        'region_id',
        'ubicacion_id',
        'departamento_id',
        'area_id',
        'puesto_id',
        'meses',
        'generos',
        'edad_desde',
        'edad_hasta',
        'mes_desde',
        'mes_hasta',
    ];

    protected function casts(): array
    {
        return [
            'meses' => 'array',
            'generos' => 'array',
            'edad_desde' => 'integer',
            'edad_hasta' => 'integer',
            'mes_desde' => 'integer',
            'mes_hasta' => 'integer',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class);
    }
}
