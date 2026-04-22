<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use App\Services\ArchivoService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Felicitacion extends Model
{
    use LogsModelActivity;

    protected $table = 'felicitaciones';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'titulo',
        'tipo',
        'mensaje',
        'departamento_id',
        'requiere_respuesta',
        'tipo_respuesta',
        'es_urgente',
        'logo',
    ];

    /**
     * Empresa a la que pertenece esta felicitación.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Usuario remitente de la felicitación (opcional).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Departamento al que va dirigida la felicitación (opcional).
     */
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    /**
     * Genera la URL del logo almacenado en Wasabi/S3 o disco local.
     * Retorna la URL del logo por defecto si no tiene uno asignado.
     */
    public function logoUrl(): string
    {
        if (blank($this->logo)) {
            return asset('img/felicitaciones/logo.png');
        }

        $archivoService = app(ArchivoService::class);
        $discoNombre = $archivoService->nombreDisco();

        if ($discoNombre === 's3') {
            return $archivoService->disco()->temporaryUrl($this->logo, now()->addMinutes(60));
        }

        return asset($this->logo);
    }
}
