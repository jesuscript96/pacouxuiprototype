<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriaSolicitud extends Model
{
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'categorias_solicitudes';

    protected $fillable = [
        'nombre',
        'empresa_id',
    ];

    protected function casts(): array
    {
        return [
            'empresa_id' => 'integer',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * BL: paridad con legacy RequestCategory::requests_type() — no borrar categoría si existen tipos.
     *
     * @return HasMany<TipoSolicitud, $this>
     */
    public function tiposSolicitud(): HasMany
    {
        return $this->hasMany(TipoSolicitud::class, 'categoria_solicitud_id');
    }

    public function tieneTiposSolicitudAsignados(): bool
    {
        return $this->tiposSolicitud()->exists();
    }

    /**
     * Categorías de catálogo global (solo lectura para clientes).
     */
    public function esCatalogoGlobal(): bool
    {
        return $this->empresa_id === null;
    }

    
}
