<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class AreaGeneral extends Model
{
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'areas_generales';

    protected $fillable = ['nombre', 'empresa_id'];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class, 'area_general_id');
    }

    /**
     * Puestos que usan esta área general vía {@see Puesto::$fillable} area_general_id.
     */
    public function puestosConEstaAreaGeneral(): HasMany
    {
        return $this->hasMany(Puesto::class, 'area_general_id');
    }

    public function tieneAsignacionesEnCatalogosEmpresa(): bool
    {
        return $this->areas()->withTrashed()->exists()
            || $this->puestosConEstaAreaGeneral()->withTrashed()->exists();
    }

    protected static function booted(): void
    {
        static::deleting(function (self $areaGeneral): void {
            if ($areaGeneral->tieneAsignacionesEnCatalogosEmpresa()) {
                throw ValidationException::withMessages([
                    'area_general' => 'No se puede eliminar porque está asignada en áreas o puestos de la empresa.',
                ]);
            }
        });
    }
}
