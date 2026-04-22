<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class CentroCosto extends Model
{
    use LogsModelActivity;

    protected $table = 'centro_de_costos';

    protected $fillable = [
        'servicio',
        'nombre',
        'cuenta_bancaria',
        'terminal_id_tae',
        'terminal_id_ps',
        'clerk_id_tae',
        'clerk_id_ps',
        'key_id',
        'secret_key',
    ];

    public const CACHE_KEY_PREFIX = 'centro_costo_options_';

    public const CACHE_TTL_SECONDS = 3600;

    /** Servicios usados en formularios (para invalidar cache). */
    public const SERVICIOS_FORM = ['BELVO', 'EMIDA', 'STP'];

    /**
     * Opciones id => nombre para selects por servicio, cacheadas.
     *
     * @return array<int, string>
     */
    public static function cachedOptionsForSelectByServicio(string $servicio): array
    {
        return Cache::remember(
            self::CACHE_KEY_PREFIX.$servicio,
            self::CACHE_TTL_SECONDS,
            fn (): array => self::query()
                ->where('servicio', $servicio)
                ->orderBy('nombre')
                ->pluck('nombre', 'id')
                ->all()
        );
    }

    public static function clearOptionsCache(): void
    {
        foreach (self::SERVICIOS_FORM as $servicio) {
            Cache::forget(self::CACHE_KEY_PREFIX.$servicio);
        }
    }

    /**
     * @return list<string>
     */
    protected function attributesExcludedFromActivityLog(): array
    {
        return ['secret_key'];
    }

    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'empresas_centros_costos', 'centro_costo_id', 'empresa_id');
    }

    /**
     * Empresas activas (no eliminadas con soft delete) con este centro de costos asignado.
     */
    public function tieneEmpresasAsignadas(): bool
    {
        return $this->empresas()->exists();
    }

    // LOGS
    protected static function booted(): void
    {
        static::deleting(function (CentroCosto $centroCosto): void {
            if ($centroCosto->tieneEmpresasAsignadas()) {
                throw ValidationException::withMessages([
                    'centro_costo' => 'No se puede eliminar el centro de costos mientras esté asignado a una empresa.',
                ]);
            }
        });

        static::created(fn () => self::clearOptionsCache());
        static::updated(fn () => self::clearOptionsCache());
        static::deleted(fn () => self::clearOptionsCache());

        static::deleted(function ($centroCosto) {
            Log::create([
                'accion' => 'Borrado de centro de costo: '.$centroCosto->nombre.' por usuario: '.auth()->user()->name.' (ID: '.auth()->user()->id.')',
                'fecha' => now(),
                'user_id' => auth()->user()->id,
                // 'empresa_id' => auth()->user()->empresa_id,
            ]);
        });

        static::created(function ($centroCosto) {
            Log::create([
                'accion' => 'Creacion de centro de costo: '.$centroCosto->nombre.' por usuario: '.auth()->user()->name.' (ID: '.auth()->user()->id.')',
                'fecha' => now(),
                'user_id' => auth()->user()->id,
                // 'empresa_id' => auth()->user()->empresa_id,
            ]);
        });

        static::updated(function ($centroCosto) {
            Log::create([
                'accion' => 'Actualizacion de centro de costo: '.$centroCosto->nombre.' por usuario: '.auth()->user()->name.' (ID: '.auth()->user()->id.')',
                'fecha' => now(),
                'user_id' => auth()->user()->id,
                // 'empresa_id' => auth()->user()->empresa_id,
            ]);
        });
    }
}
