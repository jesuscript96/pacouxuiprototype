<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Area extends Model
{
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'areas';

    protected $fillable = ['nombre', 'area_general_id', 'empresa_id'];

    public function areaGeneral(): BelongsTo
    {
        return $this->belongsTo(AreaGeneral::class, 'area_general_id');
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
        static::deleting(function (self $area): void {
            if ($area->tieneColaboradoresAsociados()) {
                throw ValidationException::withMessages([
                    'area' => 'No se puede eliminar porque tiene colaboradores asociados.',
                ]);
            }
        });
    }
}
