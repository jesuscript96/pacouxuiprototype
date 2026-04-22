<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class Industria extends Model
{
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'industrias';

    protected $fillable = ['nombre'];

    public const CACHE_KEY_OPTIONS_SELECT = 'industrias_select_options';

    public const CACHE_TTL_SECONDS = 3600;

    /**
     * Opciones id => nombre para selects, cacheadas para evitar consultas repetidas.
     *
     * @return array<int, string>
     */
    public static function cachedOptionsForSelect(): array
    {
        return Cache::remember(
            self::CACHE_KEY_OPTIONS_SELECT,
            self::CACHE_TTL_SECONDS,
            fn (): array => self::query()->orderBy('nombre')->pluck('nombre', 'id')->all()
        );
    }

    public static function clearOptionsCache(): void
    {
        Cache::forget(self::CACHE_KEY_OPTIONS_SELECT);
    }

    public function subIndustrias(): HasMany
    {
        return $this->hasMany(Subindustria::class, 'industria_id');
    }

    /**
     * Sub-industrias activas (no eliminadas con soft delete) vinculadas a esta industria.
     */
    public function tieneSubindustrias(): bool
    {
        return $this->subIndustrias()->exists();
    }

    public function empresas(): HasMany
    {
        return $this->hasMany(Empresa::class, 'industria_id');
    }

    /**
     * Empresas activas (no eliminadas con soft delete) con esta industria asignada.
     */
    public function tieneEmpresasAsignadas(): bool
    {
        return $this->empresas()->exists();
    }

    // LOGS
    protected static function booted(): void
    {
        static::deleting(function (Industria $industria): void {
            if ($industria->tieneSubindustrias()) {
                throw ValidationException::withMessages([
                    'industria' => 'No se puede eliminar la industria mientras tenga sub-industrias asignadas.',
                ]);
            }

            if ($industria->tieneEmpresasAsignadas()) {
                throw ValidationException::withMessages([
                    'industria' => 'No se puede eliminar la industria mientras esté asignada a una empresa.',
                ]);
            }
        });

        static::created(fn () => self::clearOptionsCache());
        static::updated(fn () => self::clearOptionsCache());
        static::deleted(fn () => self::clearOptionsCache());

        static::deleted(function ($industria) {

            Log::create([
                'accion' => 'Borrado de industria: '.$industria->nombre.' por usuario: '.auth()->user()->name.' (ID: '.auth()->user()->id.')',
                'fecha' => now(),
                'user_id' => auth()->user()->id,
                // 'empresa_id' => auth()->user()->empresa_id,
            ]);
        });

        static::created(function ($industria) {
            Log::create([
                'accion' => 'Creacion de industria: '.$industria->nombre.' por usuario: '.auth()->user()->name.' (ID: '.auth()->user()->id.')',
                'fecha' => now(),
                'user_id' => auth()->user()->id,
                // 'empresa_id' => auth()->user()->empresa_id,
            ]);
        });

        static::updated(function ($industria) {
            Log::create([
                'accion' => 'Actualizacion de industria: '.$industria->nombre.' por usuario: '.auth()->user()->name.' (ID: '.auth()->user()->id.')',
                'fecha' => now(),
                'user_id' => auth()->user()->id,
                // 'empresa_id' => auth()->user()->empresa_id,
            ]);
        });
    }
}
