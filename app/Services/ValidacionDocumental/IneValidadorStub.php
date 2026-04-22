<?php

namespace App\Services\ValidacionDocumental;

use App\Contracts\ValidadorDocumentoInterface;
use App\DTO\ResultadoValidacion;
use App\Models\CandidatoReclutamiento;
use Illuminate\Support\Facades\Log;

/**
 * STUB: Validador de INE vía Nubarium.
 *
 * TODO: Implementar cuando se tengan credenciales de Nubarium.
 * Legacy: app/Traits/Validations/INETrait.php
 *
 * Flujo real:
 * 1. Obtener archivo de S3 → base64
 * 2. POST config('services.nubarium.ocr_url') . "/obtener_datos_id"
 *    - Auth: HTTP Basic (nubarium_user / nubarium_password)
 *    - Body: { id: base64_frente, idReverso: base64_reverso }
 * 3. POST config('services.nubarium.ine_url') . "/valida_ine"
 *    - Body varía por subTipo (C/D/E/F/G/H): ocr, claveElector, cic, identificadorCiudadano
 * 4. Comparar datos extraídos con datos del formulario
 * 5. (Opcional) Biometría facial via /reconocimiento_facial
 */
class IneValidadorStub implements ValidadorDocumentoInterface
{
    public function validar(
        CandidatoReclutamiento $candidato,
        string $nombreCampo,
        string $rutaArchivo,
    ): ResultadoValidacion {
        Log::info('IneValidadorStub: Validación de INE pendiente de implementación', [
            'candidato_id' => $candidato->id,
            'campo' => $nombreCampo,
            'ruta' => $rutaArchivo,
        ]);

        return ResultadoValidacion::sinValidar();
    }

    public function tipoDocumento(): string
    {
        return 'ine';
    }
}
