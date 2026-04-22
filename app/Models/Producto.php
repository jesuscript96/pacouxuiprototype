<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class Producto extends Model
{
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'productos';

    protected $fillable = ['nombre', 'descripcion'];

    public const CACHE_KEY_OPTIONS_SELECT = 'productos_select_options';

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

    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'empresas_productos', 'producto_id', 'empresa_id');
    }

    /**
     * Empresas activas (no eliminadas con soft delete) con este producto asignado.
     */
    public function tieneEmpresasAsignadas(): bool
    {
        return $this->empresas()->exists();
    }

    // LOGS
    protected static function booted(): void
    {
        static::deleting(function (Producto $producto): void {
            if ($producto->tieneEmpresasAsignadas()) {
                throw ValidationException::withMessages([
                    'producto' => 'No se puede eliminar el producto mientras esté asignado a una empresa.',
                ]);
            }
        });

        static::created(fn () => self::clearOptionsCache());
        static::updated(fn () => self::clearOptionsCache());
        static::deleted(fn () => self::clearOptionsCache());

        static::deleted(function ($producto) {

            Log::create([
                'accion' => 'Borrado de producto: '.$producto->nombre.' por usuario: '.auth()->user()->name.' (ID: '.auth()->user()->id.')',
                'fecha' => now(),
                'user_id' => auth()->user()->id,
                // 'empresa_id' => auth()->user()->empresa_id,
            ]);
        });

        static::created(function ($producto) {
            Log::create([
                'accion' => 'Creacion de producto: '.$producto->nombre.' por usuario: '.auth()->user()->name.' (ID: '.auth()->user()->id.')',
                'fecha' => now(),
                'user_id' => auth()->user()->id,
                // 'empresa_id' => auth()->user()->empresa_id,
            ]);
        });

        static::updated(function ($producto) {
            Log::create([
                'accion' => 'Actualizacion de producto: '.$producto->nombre.' por usuario: '.auth()->user()->name.' (ID: '.auth()->user()->id.')',
                'fecha' => now(),
                'user_id' => auth()->user()->id,
                // 'empresa_id' => auth()->user()->empresa_id,
            ]);
        });
    }
}
