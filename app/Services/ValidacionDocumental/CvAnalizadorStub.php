<?php

namespace App\Services\ValidacionDocumental;

use App\Contracts\ValidadorDocumentoInterface;
use App\DTO\ResultadoValidacion;
use App\Models\CandidatoReclutamiento;
use Illuminate\Support\Facades\Log;

/**
 * STUB: Analizador de CV con IA vía OpenAI.
 *
 * TODO: Implementar cuando se tenga API key de OpenAI.
 * Legacy: app/Traits/Validations/CVTrait.php
 * Dependencia futura: smalot/pdfparser para extraer texto del PDF
 *
 * Flujo real:
 * 1. Descargar PDF de S3
 * 2. Extraer texto con smalot/pdfparser
 * 3. POST config('services.openai.base_url') . "chat/completions"
 *    - Modelo: gpt-4o
 *    - Prompt: texto del CV + descripción/requisitos de la vacante
 *    - Response: JSON con score (0-10), fortalezas, debilidades, resumen
 * 4. Actualizar candidato.evaluacion_cv con el score
 */
class CvAnalizadorStub implements ValidadorDocumentoInterface
{
    public function validar(
        CandidatoReclutamiento $candidato,
        string $nombreCampo,
        string $rutaArchivo,
    ): ResultadoValidacion {
        Log::info('CvAnalizadorStub: Análisis de CV pendiente de implementación', [
            'candidato_id' => $candidato->id,
            'campo' => $nombreCampo,
        ]);

        return ResultadoValidacion::sinValidar();
    }

    public function tipoDocumento(): string
    {
        return 'cv';
    }
}
