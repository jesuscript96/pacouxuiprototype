<?php

declare(strict_types=1);

namespace App\Services\ValidacionDocumental;

use App\Contracts\ValidadorDocumentoInterface;
use App\DTO\ResultadoValidacion;
use App\Models\CandidatoReclutamiento;
use App\Services\ArchivoService;
use App\Services\Nubarium\NubariumVerificacionService;
use Illuminate\Support\Facades\Log;

/**
 * Validador de INE vía Nubarium OCR + validación INE (reemplaza IneValidadorStub).
 *
 * Legacy: app/Traits/Validations/INETrait.php
 */
class IneValidadorService implements ValidadorDocumentoInterface
{
    public function __construct(
        private NubariumVerificacionService $nubarium,
        private ArchivoService $archivoService,
    ) {}

    public function validar(
        CandidatoReclutamiento $candidato,
        string $nombreCampo,
        string $rutaArchivo,
    ): ResultadoValidacion {
        if (! $this->nubarium->estaConfigurado()) {
            Log::info('IneValidadorService: Nubarium no configurado', [
                'candidato_id' => $candidato->id,
            ]);

            return ResultadoValidacion::sinValidar();
        }

        $contenido = $this->archivoService->obtener($rutaArchivo);

        if ($contenido === null) {
            return ResultadoValidacion::fallido('No se pudo obtener archivo de INE');
        }

        $imagenBase64 = base64_encode($contenido);

        // BL: Paso 1 — OCR para extraer datos de la credencial
        $ocrResult = $this->nubarium->ocrIne($imagenBase64);

        if (! $ocrResult['success']) {
            Log::warning('IneValidadorService: Error en OCR', [
                'candidato_id' => $candidato->id,
                'error' => $ocrResult['error'] ?? 'desconocido',
            ]);

            return ResultadoValidacion::fallido($ocrResult['error'] ?? 'Error en OCR de INE');
        }

        $datosOcr = $ocrResult['datos'] ?? [];

        // BL: Legacy verifica curp, nombres, primerApellido, segundoApellido, codigoValidacion
        if (isset($datosOcr['estatus']) && $datosOcr['estatus'] === 'ERROR') {
            return ResultadoValidacion::fallido($datosOcr['mensaje'] ?? 'Error en OCR INE');
        }

        $claveElector = $datosOcr['claveElector'] ?? $datosOcr['clave_elector'] ?? null;
        $ocr = $datosOcr['ocr'] ?? '';

        if (empty($claveElector) && empty($datosOcr['cic'] ?? null)) {
            return ResultadoValidacion::documentoValido(
                datosCoinciden: false,
                datosExtraidos: $datosOcr,
            );
        }

        // BL: Paso 2 — Validar INE contra padrón (usando subTipo C como default)
        $validacion = $this->nubarium->validarIne([
            'subTipo' => 'C',
            'ocr' => $ocr,
            'claveElector' => $claveElector ?? '',
            'registro' => $datosOcr['registro'] ?? $datosOcr['numeroEmision'] ?? '',
        ]);

        if (! $validacion['success']) {
            return ResultadoValidacion::fallido($validacion['error'] ?? 'Error validando INE');
        }

        $esValido = $validacion['valido'] ?? false;
        $datosCompletos = array_merge($datosOcr, $validacion['datos'] ?? []);

        Log::info('IneValidadorService: INE validada', [
            'candidato_id' => $candidato->id,
            'valido' => $esValido,
        ]);

        return ResultadoValidacion::documentoValido(
            datosCoinciden: $esValido,
            datosExtraidos: $datosCompletos,
        );
    }

    public function tipoDocumento(): string
    {
        return 'ine';
    }
}
