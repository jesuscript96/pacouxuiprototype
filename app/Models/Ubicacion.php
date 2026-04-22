<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Ubicacion extends Model
{
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'ubicaciones';

    protected $fillable = ['nombre', 'empresa_id', 'cp', 'mostrar_modal_calendly', 'registro_patronal_sucursal', 'direccion_imss'];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function razonesSociales(): BelongsToMany
    {
        return $this->belongsToMany(Razonsocial::class, 'razones_sociales_ubicaciones', 'ubicacion_id', 'razon_social_id')
            ->withTimestamps();
    }

    public function colaboradores(): HasMany
    {
        return $this->hasMany(Colaborador::class);
    }

    public function tieneColaboradoresAsociados(): bool
    {
        return $this->colaboradores()->withTrashed()->exists();
    }

    // LOGS
    protected static function booted(): void
    {
        static::deleting(function (self $ubicacion): void {
            if ($ubicacion->tieneColaboradoresAsociados()) {
                throw ValidationException::withMessages([
                    'ubicacion' => 'No se puede eliminar porque tiene colaboradores asociados.',
                ]);
            }
        });

        static::deleted(function ($ubicacion) {
            $user = auth()->user();
            if ($user) {
                \App\Models\Log::create([
                    'accion' => 'Borrado de ubicacion: '.$ubicacion->nombre.' por usuario: '.$user->name.' (ID: '.$user->id.')',
                    'fecha' => now(),
                    'user_id' => $user->id,
                ]);
            }
        });

        static::created(function ($ubicacion) {
            $user = auth()->user();
            if ($user) {
                \App\Models\Log::create([
                    'accion' => 'Creacion de ubicacion: '.$ubicacion->nombre.' por usuario: '.$user->name.' (ID: '.$user->id.')',
                    'fecha' => now(),
                    'user_id' => $user->id,
                ]);
            }
        });

        static::updated(function ($ubicacion) {
            $user = auth()->user();
            if ($user) {
                \App\Models\Log::create([
                    'accion' => 'Actualizacion de ubicacion: '.$ubicacion->nombre.' por usuario: '.$user->name.' (ID: '.$user->id.')',
                    'fecha' => now(),
                    'user_id' => $user->id,
                ]);
            }
        });
    }
}
