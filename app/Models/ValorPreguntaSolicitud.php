<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ValorPreguntaSolicitud extends Model
{
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'valores_pregunta_solicitud';

    protected $fillable = [
        'titulo',
        'indice',
        'respuesta_personalizada',
        'pregunta_solicitud_id',
    ];

    /**
     * @return BelongsTo<PreguntaSolicitud, $this>
     */
    public function preguntaSolicitud(): BelongsTo
    {
        return $this->belongsTo(PreguntaSolicitud::class, 'pregunta_solicitud_id');
    }

    protected function casts(): array
    {
        return [
            'pregunta_solicitud_id' => 'integer',
            'indice' => 'integer',
            'respuesta_personalizada' => 'boolean',
        ];
    }
}
