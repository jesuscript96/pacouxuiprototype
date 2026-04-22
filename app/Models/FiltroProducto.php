<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiltroProducto extends Model
{
    protected $table = 'filtros_productos';

    protected $fillable = [
        'empresa_id',
        'producto_id',
        'area_id',
        'departamento_id',
        'ubicacion_id',
        'puesto_id',
        'region_id',
        'generos',
        'meses',
        'edad_desde',
        'edad_hasta',
        'mes_desde',
        'mes_hasta',
        'razon',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
