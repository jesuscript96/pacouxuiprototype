<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutorizadorEtapaAprobacion extends Model
{
    protected $table = 'autorizadores_etapa_aprobacion';

    protected $fillable = [
        'nivel',
        'etapa_flujo_aprobacion_id',
        'usuario_id',
    ];

    /**
     * @return BelongsTo<EtapaFlujoAprobacion, $this>
     */
    public function etapaFlujoAprobacion(): BelongsTo
    {
        return $this->belongsTo(EtapaFlujoAprobacion::class, 'etapa_flujo_aprobacion_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    protected function casts(): array
    {
        return [
            'etapa_flujo_aprobacion_id' => 'integer',
            'usuario_id' => 'integer',
        ];
    }
}
