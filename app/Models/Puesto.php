<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Puesto extends Model
{
    use LogsModelActivity;
    use SoftDeletes;

    protected $fillable = ['nombre', 'empresa_id', 'puesto_general_id', 'ocupacion_id', 'area_general_id'];

    public function puestoGeneral(): BelongsTo
    {
        return $this->belongsTo(PuestoGeneral::class);
    }

    public function ocupacion(): BelongsTo
    {
        return $this->belongsTo(Ocupacion::class);
    }

    public function areaGeneral(): BelongsTo
    {
        return $this->belongsTo(AreaGeneral::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function colaboradores(): HasMany
    {
        return $this->hasMany(Colaborador::class);
    }

    public function tieneColaboradoresAsociados(): bool
    {
        return $this->colaboradores()->withTrashed()->exists();
    }

    protected static function booted(): void
    {
        static::deleting(function (self $puesto): void {
            if ($puesto->tieneColaboradoresAsociados()) {
                throw ValidationException::withMessages([
                    'puesto' => 'No se puede eliminar porque tiene colaboradores asociados.',
                ]);
            }
        });
    }
}
