<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Importacion extends Model
{
    public const TIPO_ALTA_MASIVA = 'ALTA_MASIVA';

    public const TIPO_EDICION_MASIVA = 'EDICION_MASIVA';

    public const TIPO_BAJA_MASIVA = 'BAJA_MASIVA';

    public const TIPO_CARGA_SUA = 'CARGA_SUA';

    public const ESTADO_PENDIENTE = 'PENDIENTE';

    public const ESTADO_PROCESANDO = 'PROCESANDO';

    public const ESTADO_COMPLETADA = 'COMPLETADA';

    public const ESTADO_CON_ERRORES = 'CON_ERRORES';

    public const ESTADO_FALLIDA = 'FALLIDA';

    protected $table = 'importaciones';

    protected $fillable = [
        'empresa_id',
        'usuario_id',
        'tipo',
        'archivo_original',
        'total_filas',
        'filas_procesadas',
        'filas_exitosas',
        'filas_con_error',
        'estado',
        'archivo_errores',
        'iniciado_en',
        'completado_en',
    ];

    protected function casts(): array
    {
        return [
            'iniciado_en' => 'datetime',
            'completado_en' => 'datetime',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function errores(): HasMany
    {
        return $this->hasMany(ErrorImportacion::class, 'importacion_id');
    }
}
