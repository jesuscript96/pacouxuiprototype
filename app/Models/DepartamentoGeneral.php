<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class DepartamentoGeneral extends Model
{
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'departamentos_generales';

    protected $fillable = ['nombre'];

    public function departamentos(): HasMany
    {
        return $this->hasMany(Departamento::class, 'departamento_general_id');
    }

    public function tieneDepartamentosAsociados(): bool
    {
        return $this->departamentos()->withTrashed()->exists();
    }

    protected static function booted(): void
    {
        static::deleting(function (self $departamentoGeneral): void {
            if ($departamentoGeneral->tieneDepartamentosAsociados()) {
                throw ValidationException::withMessages([
                    'departamento_general' => 'No se puede eliminar porque tiene departamentos asociados.',
                ]);
            }
        });
    }
}
