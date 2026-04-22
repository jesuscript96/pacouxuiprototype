<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Subindustria extends Model
{
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'sub_industrias';

    protected $fillable = ['nombre', 'industria_id'];

    public function industria(): BelongsTo
    {
        return $this->belongsTo(Industria::class);
    }

    public function empresas(): HasMany
    {
        return $this->hasMany(Empresa::class, 'sub_industria_id');
    }

    /**
     * Empresas activas (no eliminadas con soft delete) con esta sub-industria asignada.
     */
    public function tieneEmpresasAsignadas(): bool
    {
        return $this->empresas()->exists();
    }

    // LOGS
    protected static function booted(): void
    {
        static::deleting(function (Subindustria $subindustria): void {
            if ($subindustria->tieneEmpresasAsignadas()) {
                throw ValidationException::withMessages([
                    'subindustria' => 'No se puede eliminar la sub-industria mientras esté asignada a una empresa.',
                ]);
            }
        });

        static::deleted(function ($subindustria) {

            Log::create([
                'accion' => 'Borrado de subindustria: '.$subindustria->nombre.' por usuario: '.auth()->user()->name.' (ID: '.auth()->user()->id.')',
                'fecha' => now(),
                'user_id' => auth()->user()->id,
                // 'empresa_id' => auth()->user()->empresa_id,
            ]);
        });

        static::created(function ($subindustria) {
            Log::create([
                'accion' => 'Creacion de subindustria: '.$subindustria->nombre.' por usuario: '.auth()->user()->name.' (ID: '.auth()->user()->id.')',
                'fecha' => now(),
                'user_id' => auth()->user()->id,
                // 'empresa_id' => auth()->user()->empresa_id,
            ]);
        });

        static::updated(function ($subindustria) {
            Log::create([
                'accion' => 'Actualizacion de subindustria: '.$subindustria->nombre.' por usuario: '.auth()->user()->name.' (ID: '.auth()->user()->id.')',
                'fecha' => now(),
                'user_id' => auth()->user()->id,
                // 'empresa_id' => auth()->user()->empresa_id,
            ]);
        });
    }
}
