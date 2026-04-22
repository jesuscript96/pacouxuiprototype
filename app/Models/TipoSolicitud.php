<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoSolicitud extends Model
{
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'tipos_solicitud';

    /**
     * @deprecated Usar rutaImagenes() para nuevos uploads en S3.
     *             Se conserva solo para compatibilidad con datos legacy en disco local.
     */
    public static function rutaRelativaImagenes(int $tipoSolicitudId): string
    {
        return 'assets/requests_type/'.$tipoSolicitudId;
    }

    /**
     * Ruta estándar de imágenes de preguntas en Wasabi/S3.
     * Path: companies/{empresaId}/tipos-solicitud/{tipoSolicitudId}
     */
    public static function rutaImagenes(int $empresaId, int $tipoSolicitudId): string
    {
        return "companies/{$empresaId}/tipos-solicitud/{$tipoSolicitudId}";
    }

    protected $fillable = [
        'nombre',
        'estado',
        'rango_fechas',
        'vigencia_solicitud',
        'unidad_tiempo',
        'fecha_vigencia',
        'descripcion',
        'categoria_solicitud_id',
    ];

    /**
     * Categoría a la que pertenece este tipo de solicitud.
     *
     * @return BelongsTo<CategoriaSolicitud, $this>
     */
    public function categoriaSolicitud(): BelongsTo
    {
        return $this->belongsTo(CategoriaSolicitud::class, 'categoria_solicitud_id');
    }

    /**
     * Etapas del flujo de aprobación, ordenadas por número de etapa.
     *
     * @return HasMany<EtapaFlujoAprobacion, $this>
     */
    public function etapasFlujoAprobacion(): HasMany
    {
        return $this->hasMany(EtapaFlujoAprobacion::class, 'tipo_solicitud_id')->orderBy('etapa');
    }

    /**
     * Preguntas de la solicitud, ordenadas por número.
     *
     * @return HasMany<PreguntaSolicitud, $this>
     */
    public function preguntasSolicitud(): HasMany
    {
        return $this->hasMany(PreguntaSolicitud::class, 'tipo_solicitud_id')->orderBy('numero');
    }

    protected function casts(): array
    {
        return [
            'categoria_solicitud_id' => 'integer',
            'fecha_vigencia' => 'date',
        ];
    }
}
