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
 * Validador de Constancia de Situación Fiscal vía Nubarium (reemplaza CsfValidadorStub).
 *
 * Legacy: app/Traits/Validations/CSFTrait.php
 */
class CsfValidadorService implements ValidadorDocumentoInterface
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
            Log::info('CsfValidadorService: Nubarium no configurado', [
                'candidato_id' => $candidato->id,
            ]);

            return ResultadoValidacion::sinValidar();
        }

        $contenido = $this->archivoService->obtener($rutaArchivo);

        if ($contenido === null) {
            return ResultadoValidacion::fallido('No se pudo obtener archivo CSF');
        }

        $formato = str_ends_with(strtolower($rutaArchivo), '.pdf') ? 'pdf' : 'imagen';
        $archivoBase64 = base64_encode($contenido);

        $resultado = $this->nubarium->validarCsf($archivoBase64, $formato);

        if (! $resultado['success']) {
            Log::warning('CsfValidadorService: Error en validación', [
                'candidato_id' => $candidato->id,
                'error' => $resultado['error'] ?? 'desconocido',
            ]);

            return ResultadoValidacion::fallido($resultado['error'] ?? 'Error validando CSF');
        }

        $datos = $resultado['datos'] ?? [];
        $rfcExtraido = $datos['rfc'] ?? $datos['RFC'] ?? null;
        $nombreExtraido = $datos['nombre'] ?? $datos['razonSocial'] ?? null;

        // BL: Comparar RFC extraído con datos del formulario del candidato
        $formulario = $candidato->valores_formulario ?? [];
        $rfcCandidato = $formulario['rfc'] ?? $candidato->curp ?? null;

        $coincide = $rfcExtraido !== null
            && $rfcCandidato !== null
            && strtoupper(trim($rfcExtraido)) === strtoupper(trim($rfcCandidato));

        Log::info('CsfValidadorService: CSF validada', [
            'candidato_id' => $candidato->id,
            'rfc_coincide' => $coincide,
        ]);

        return ResultadoValidacion::documentoValido(
            datosCoinciden: $coincide,
            datosExtraidos: $datos,
        );
    }

    public function tipoDocumento(): string
    {
        return 'csf';
    }
}
