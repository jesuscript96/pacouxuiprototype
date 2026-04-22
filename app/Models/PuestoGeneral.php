<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class PuestoGeneral extends Model
{
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'puestos_generales';

    protected $fillable = ['nombre', 'empresa_id'];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function puestos(): HasMany
    {
        return $this->hasMany(Puesto::class, 'puesto_general_id');
    }

    public function tienePuestosAsociados(): bool
    {
        return $this->puestos()->withTrashed()->exists();
    }

    protected static function booted(): void
    {
        static::deleting(function (self $puestoGeneral): void {
            if ($puestoGeneral->tienePuestosAsociados()) {
                throw ValidationException::withMessages([
                    'puesto_general' => 'No se puede eliminar porque tiene puestos asociados.',
                ]);
            }
        });
    }
}
