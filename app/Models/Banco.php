<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Banco extends Model
{
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'bancos';

    protected $fillable = ['nombre', 'codigo', 'comision'];

    public function cuentasNomina(): HasMany
    {
        return $this->hasMany(CuentaNomina::class, 'banco_id');
    }

    public function cuentasBancarias(): HasMany
    {
        return $this->hasMany(CuentaBancaria::class, 'banco_id');
    }

    /**
     * No hay pivot `empresas_bancos`: el vínculo con empresas es indirecto vía cuentas de nómina
     * (colaborador con empresa) o cuentas bancarias de usuarios con empresa asignada.
     */
    public function tieneEmpresasAsignadas(): bool
    {
        $tieneNominaDeEmpresa = $this->cuentasNomina()
            ->whereHas('colaborador', function ($query): void {
                $query->withTrashed()->whereNotNull('empresa_id');
            })
            ->exists();

        if ($tieneNominaDeEmpresa) {
            return true;
        }

        return $this->cuentasBancarias()
            ->whereHas('user', function ($query): void {
                $query->withTrashed()->whereNotNull('empresa_id');
            })
            ->exists();
    }

    protected static function booted(): void
    {
        static::deleting(function (Banco $banco): void {
            if ($banco->tieneEmpresasAsignadas()) {
                throw ValidationException::withMessages([
                    'banco' => 'No se puede eliminar el banco mientras esté asignado a una empresa.',
                ]);
            }
        });
    }
}
