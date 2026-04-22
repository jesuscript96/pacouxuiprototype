<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class NotificacionesIncluidas extends Model
{
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'notificaciones_incluidas';

    protected $fillable = ['nombre', 'descripcion'];

    public const CACHE_KEY_ALL = 'notificaciones_incluidas_all';

    public const CACHE_TTL_SECONDS = 3600;

    /**
     * Colección de notificaciones para formularios, cacheadas para evitar consultas repetidas.
     *
     * @return Collection<int, NotificacionesIncluidas>
     */
    public static function cachedAll(): Collection
    {
        return Cache::remember(
            self::CACHE_KEY_ALL,
            self::CACHE_TTL_SECONDS,
            fn (): Collection => self::query()->orderBy('nombre')->get()
        );
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_ALL);
    }

    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'empresas_notificaciones_incluidas', 'notificacion_incluida_id', 'empresa_id');
    }

    /**
     * Empresas activas (no eliminadas con soft delete) con esta notificación incluida asignada.
     */
    public function tieneEmpresasAsignadas(): bool
    {
        return $this->empresas()->exists();
    }

    protected static function booted(): void
    {
        static::deleting(function (NotificacionesIncluidas $notificacionIncluida): void {
            if ($notificacionIncluida->tieneEmpresasAsignadas()) {
                throw ValidationException::withMessages([
                    'notificaciones_incluidas' => 'No se puede eliminar la notificación incluida mientras esté asignada a una empresa.',
                ]);
            }
        });

        static::created(fn () => self::clearCache());
        static::updated(fn () => self::clearCache());
        static::deleted(fn () => self::clearCache());
    }
}
