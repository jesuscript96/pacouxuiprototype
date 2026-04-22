<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use App\Services\ArchivoService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreguntaSolicitud extends Model
{
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'preguntas_solicitud';

    protected $fillable = [
        'tipo',
        'titulo',
        'subtitulo',
        'imagen',
        'min_respuestas',
        'max_respuestas',
        'numero',
        'tipo_solicitud_id',
    ];

    /**
     * Tipo de solicitud al que pertenece esta pregunta.
     *
     * @return BelongsTo<TipoSolicitud, $this>
     */
    public function tipoSolicitud(): BelongsTo
    {
        return $this->belongsTo(TipoSolicitud::class, 'tipo_solicitud_id');
    }

    /**
     * Valores/opciones de respuesta de esta pregunta.
     *
     * @return HasMany<ValorPreguntaSolicitud, $this>
     */
    public function valores(): HasMany
    {
        return $this->hasMany(ValorPreguntaSolicitud::class, 'pregunta_solicitud_id');
    }

    /**
     * Genera la URL de la imagen almacenada en Wasabi/S3 o disco local.
     *
     * Maneja dos formatos de ruta:
     * - Nuevo (S3): ruta completa como `companies/5/tipos-solicitud/123/foto.png`
     * - Legacy: solo el filename como `foto.png` (se resuelve con rutaRelativaImagenes)
     */
    public function imagenUrl(): ?string
    {
        if (blank($this->imagen)) {
            return null;
        }

        $archivoService = app(ArchivoService::class);
        $discoNombre = $archivoService->nombreDisco();

        $ruta = str_contains($this->imagen, '/')
            ? $this->imagen
            : TipoSolicitud::rutaRelativaImagenes((int) $this->tipo_solicitud_id).'/'.$this->imagen;

        if ($discoNombre === 's3') {
            return $archivoService->disco()->temporaryUrl($ruta, now()->addMinutes(60));
        }

        return asset($ruta);
    }

    protected function casts(): array
    {
        return [
            'tipo_solicitud_id' => 'integer',
            'min_respuestas' => 'integer',
            'max_respuestas' => 'integer',
            'numero' => 'integer',
        ];
    }
}
