<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Region extends Model
{
    use LogsModelActivity;

    protected $table = 'regiones';

    protected $fillable = ['nombre', 'empresa_id'];

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
        static::deleting(function (self $region): void {
            if ($region->tieneColaboradoresAsociados()) {
                throw ValidationException::withMessages([
                    'region' => 'No se puede eliminar porque tiene colaboradores asociados.',
                ]);
            }
        });
    }
}
