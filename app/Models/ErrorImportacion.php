<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErrorImportacion extends Model
{
    protected $table = 'errores_importacion';

    protected $fillable = [
        'importacion_id',
        'fila',
        'columna',
        'valor_enviado',
        'mensaje_error',
    ];

    public function importacion(): BelongsTo
    {
        return $this->belongsTo(Importacion::class);
    }
}
