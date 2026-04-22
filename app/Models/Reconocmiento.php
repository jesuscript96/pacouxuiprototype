<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use App\Services\ArchivoService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Reconocmiento extends Model
{
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'reconocimientos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'es_enviable',
        'es_exclusivo',
        'menciones_necesarias',
        'imagen_inicial',
        'imagen_final',
    ];

    /**
     * Empresas asignadas a este reconocimiento vía pivot.
     * El pivot incluye configuración por empresa: es_enviable y menciones_necesarias.
     */
    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'empresas_reconocimientos', 'reconocimiento_id', 'empresa_id')
            ->withPivot(['es_enviable', 'menciones_necesarias']);
    }

    /**
     * BL: No eliminar reconocimientos vinculados a empresas en el pivot.
     */
    public function tieneEmpresasAsignadas(): bool
    {
        return $this->empresas()->exists();
    }

    /**
     * Genera la URL de la imagen inicial almacenada en Wasabi/S3 o disco local.
     * Retorna null si no tiene imagen asignada.
     */
    public function imagenInicialUrl(): ?string
    {
        return $this->generarUrlImagen($this->imagen_inicial);
    }

    /**
     * Genera la URL de la imagen final almacenada en Wasabi/S3 o disco local.
     * Retorna null si no tiene imagen asignada.
     */
    public function imagenFinalUrl(): ?string
    {
        return $this->generarUrlImagen($this->imagen_final);
    }

    /**
     * Genera una URL firmada (S3) o asset (local) para una ruta de imagen.
     */
    private function generarUrlImagen(?string $ruta): ?string
    {
        if (blank($ruta)) {
            return null;
        }

        $archivoService = app(ArchivoService::class);
        $discoNombre = $archivoService->nombreDisco();

        if ($discoNombre === 's3') {
            return $archivoService->disco()->temporaryUrl($ruta, now()->addMinutes(60));
        }

        return asset($ruta);
    }

    protected static function booted(): void
    {
        static::deleting(function (self $reconocmiento): void {
            if ($reconocmiento->tieneEmpresasAsignadas()) {
                throw ValidationException::withMessages([
                    'reconocimiento' => 'No se puede eliminar el reconocimiento porque tiene empresas asignadas.',
                ]);
            }
        });

        static::deleted(function (Reconocmiento $reconocimiento) {
            $user = auth()->user();
            if ($user) {
                Log::create([
                    'accion' => 'Borrado de reconocimiento: '.$reconocimiento->nombre.' por usuario: '.$user->name.' (ID: '.$user->id.')',
                    'fecha' => now(),
                    'user_id' => $user->id,
                ]);
            }
        });

        static::created(function (Reconocmiento $reconocimiento) {
            $user = auth()->user();
            if ($user) {
                Log::create([
                    'accion' => 'Creación de reconocimiento: '.$reconocimiento->nombre.' por usuario: '.$user->name.' (ID: '.$user->id.')',
                    'fecha' => now(),
                    'user_id' => $user->id,
                ]);
            }
        });

        static::updated(function (Reconocmiento $reconocimiento) {
            $user = auth()->user();
            if ($user) {
                Log::create([
                    'accion' => 'Actualización de reconocimiento: '.$reconocimiento->nombre.' por usuario: '.$user->name.' (ID: '.$user->id.')',
                    'fecha' => now(),
                    'user_id' => $user->id,
                ]);
            }
        });
    }
}
