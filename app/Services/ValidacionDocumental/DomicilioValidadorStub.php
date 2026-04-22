<?php

namespace App\Services\ValidacionDocumental;

use App\Contracts\ValidadorDocumentoInterface;
use App\DTO\ResultadoValidacion;
use App\Models\CandidatoReclutamiento;
use Illuminate\Support\Facades\Log;

/**
 * STUB: Validador de Comprobante de Domicilio vía OpenAI.
 *
 * TODO: Implementar cuando se tenga API key de OpenAI.
 * Legacy: app/Traits/Validations/ComprobanteDomicilioTrait.php
 *
 * Flujo real:
 * 1. Obtener URL firmada del archivo en S3
 * 2. POST config('services.openai.base_url') . "responses"
 *    - Modelo: gpt-4.1 con input multimodal (imagen)
 *    - Prompt: extraer dirección, titular, periodo, tipo de servicio
 * 3. Comparar datos extraídos con datos del formulario
 */
class DomicilioValidadorStub implements ValidadorDocumentoInterface
{
    public function validar(
        CandidatoReclutamiento $candidato,
        string $nombreCampo,
        string $rutaArchivo,
    ): ResultadoValidacion {
        Log::info('DomicilioValidadorStub: Validación de domicilio pendiente de implementación', [
            'candidato_id' => $candidato->id,
            'campo' => $nombreCampo,
        ]);

        return ResultadoValidacion::sinValidar();
    }

    public function tipoDocumento(): string
    {
        return 'comprobante_domicilio';
    }
}
