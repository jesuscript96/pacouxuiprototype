<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EtapaFlujoAprobacion extends Model
{
    protected $table = 'etapas_flujo_aprobacion';

    protected $fillable = [
        'etapa',
        'nivel_autorizacion',
        'tipo_solicitud_id',
    ];

    /**
     * @return BelongsTo<TipoSolicitud, $this>
     */
    public function tipoSolicitud(): BelongsTo
    {
        return $this->belongsTo(TipoSolicitud::class, 'tipo_solicitud_id');
    }

    /**
     * @return HasMany<AutorizadorEtapaAprobacion, $this>
     */
    public function autorizadoresEtapaAprobacion(): HasMany
    {
        return $this->hasMany(AutorizadorEtapaAprobacion::class, 'etapa_flujo_aprobacion_id');
    }

    protected function casts(): array
    {
        return [
            'etapa' => 'integer',
            'tipo_solicitud_id' => 'integer',
        ];
    }
}
