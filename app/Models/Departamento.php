<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Departamento extends Model
{
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'departamentos';

    protected $fillable = ['nombre', 'empresa_id', 'departamento_general_id'];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function departamentoGeneral(): BelongsTo
    {
        return $this->belongsTo(DepartamentoGeneral::class, 'departamento_general_id');
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
        static::deleting(function (self $departamento): void {
            if ($departamento->tieneColaboradoresAsociados()) {
                throw ValidationException::withMessages([
                    'departamento' => 'No se puede eliminar porque tiene colaboradores asociados.',
                ]);
            }
        });
    }
}
