<?php

namespace App\Services\ValidacionDocumental;

use App\Contracts\ValidadorDocumentoInterface;
use App\DTO\ResultadoValidacion;
use App\Models\CandidatoReclutamiento;
use Illuminate\Support\Facades\Log;

/**
 * STUB: Validador de Constancia de Situación Fiscal vía Nubarium.
 *
 * TODO: Implementar cuando se tengan credenciales de Nubarium.
 * Legacy: app/Traits/Validations/CSFTrait.php
 *
 * Flujo real:
 * 1. Obtener archivo de S3 → base64
 * 2. POST config('services.nubarium.csf_url') . "/consultar_cif"
 *    - Auth: HTTP Basic (nubarium_user / nubarium_password)
 *    - Body: { pdf: base64 } o { imagen: base64 }
 * 3. Comparar RFC/nombre extraído con datos del formulario
 */
class CsfValidadorStub implements ValidadorDocumentoInterface
{
    public function validar(
        CandidatoReclutamiento $candidato,
        string $nombreCampo,
        string $rutaArchivo,
    ): ResultadoValidacion {
        Log::info('CsfValidadorStub: Validación de CSF pendiente de implementación', [
            'candidato_id' => $candidato->id,
            'campo' => $nombreCampo,
        ]);

        return ResultadoValidacion::sinValidar();
    }

    public function tipoDocumento(): string
    {
        return 'csf';
    }
}
