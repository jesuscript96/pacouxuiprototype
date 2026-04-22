<?php

declare(strict_types=1);

namespace App\Services\ValidacionDocumental;

use App\Contracts\ValidadorDocumentoInterface;
use App\DTO\ResultadoValidacion;
use App\Models\CandidatoReclutamiento;
use App\Services\ArchivoService;
use App\Services\OpenAI\OpenAIService;
use Illuminate\Support\Facades\Log;

/**
 * Validador de Comprobante de Domicilio vía OpenAI gpt-4.1 multimodal.
 *
 * Legacy: app/Traits/Validations/ComprobanteDomicilioTrait.php
 * Legacy: app/Repositories/OpenAi.php (getInfoImage)
 * Endpoint: POST {OPENAI_BASE_URL}/responses (API Responses, NO Chat Completions)
 */
class DomicilioValidadorService implements ValidadorDocumentoInterface
{
    private const MAX_DIAS_ANTIGUEDAD = 90;

    public function __construct(
        private OpenAIService $openAI,
        private ArchivoService $archivoService,
    ) {}

    public function validar(
        CandidatoReclutamiento $candidato,
        string $nombreCampo,
        string $rutaArchivo,
    ): ResultadoValidacion {
        if (! $this->openAI->estaConfigurado()) {
            Log::info('DomicilioValidadorService: OpenAI no configurado', [
                'candidato_id' => $candidato->id,
            ]);

            return ResultadoValidacion::sinValidar();
        }

        $imageUrl = $this->obtenerUrlImagen($rutaArchivo);

        if (empty($imageUrl)) {
            return ResultadoValidacion::fallido('No se pudo obtener URL del comprobante de domicilio');
        }

        $prompt = $this->construirPrompt();

        $resultado = $this->openAI->analizarImagen($prompt, $imageUrl, 'gpt-4.1');

        if (! $resultado['success']) {
            Log::warning('DomicilioValidadorService: Error de OpenAI', [
                'candidato_id' => $candidato->id,
                'error' => $resultado['error'] ?? 'desconocido',
            ]);

            return ResultadoValidacion::fallido($resultado['error'] ?? 'Error de OpenAI');
        }

        $datos = $this->parsearRespuesta($resultado['content'] ?? '');

        if (empty($datos)) {
            return ResultadoValidacion::documentoValido(
                datosCoinciden: false,
                datosExtraidos: ['raw_response' => $resultado['content']],
            );
        }

        $diasDiff = $this->calcularDiasAntiguedad($datos);
        $esReciente = $diasDiff === null || $diasDiff <= self::MAX_DIAS_ANTIGUEDAD;

        Log::info('DomicilioValidadorService: Comprobante analizado', [
            'candidato_id' => $candidato->id,
            'domicilio' => $datos['domicilio'] ?? null,
            'dias_antiguedad' => $diasDiff,
            'es_reciente' => $esReciente,
        ]);

        return ResultadoValidacion::documentoValido(
            datosCoinciden: $esReciente,
            datosExtraidos: array_merge($datos, [
                'dias_antiguedad' => $diasDiff,
                'max_dias_permitido' => self::MAX_DIAS_ANTIGUEDAD,
                'es_reciente' => $esReciente,
            ]),
        );
    }

    public function tipoDocumento(): string
    {
        return 'comprobante_domicilio';
    }

    /**
     * BL: Prompt exacto del legacy (ComprobanteDomicilioTrait.php línea 22).
     */
    private function construirPrompt(): string
    {
        $fechaActual = now()->format('d/m/y');

        return "Analiza este comprobante de domicilio y devuelveme en formato json sin la palabra json los siguientes datos: domicilio,fecha de emision issue_date,diferencia en dias de la fecha {$fechaActual} con la fecha de emision days_diff,monto total del recibo total_mount,tipo de comprobante de servicio type_receipt_service,nombre del titular del recibo name_receipt_holder,si es que la imagen es incorrecta devuelveme un json vacio";
    }

    /**
     * @return array<string, mixed>
     */
    private function parsearRespuesta(string $content): array
    {
        if (empty($content)) {
            return [];
        }

        $content = preg_replace('/^```json\s*/i', '', trim($content)) ?? $content;
        $content = preg_replace('/\s*```$/', '', $content) ?? $content;
        $content = preg_replace('/```[a-zA-Z]*\s*/', '', $content) ?? $content;
        $content = preg_replace('/\s*```/', '', $content) ?? $content;
        $content = trim($content);

        if ($content === '' || $content === '{}') {
            return [];
        }

        $datos = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('DomicilioValidadorService: JSON inválido', [
                'error' => json_last_error_msg(),
            ]);

            return [];
        }

        return is_array($datos) ? $datos : [];
    }

    private function calcularDiasAntiguedad(array $datos): ?int
    {
        $diasDiff = $datos['days_diff'] ?? null;

        if ($diasDiff !== null) {
            return abs((int) $diasDiff);
        }

        $issueDate = $datos['issue_date'] ?? null;
        if ($issueDate === null) {
            return null;
        }

        try {
            return (int) abs(now()->diffInDays(\Carbon\Carbon::parse($issueDate)));
        } catch (\Exception) {
            return null;
        }
    }

    private function obtenerUrlImagen(string $rutaArchivo): ?string
    {
        try {
            return $this->archivoService->url($rutaArchivo);
        } catch (\Exception $e) {
            Log::warning('DomicilioValidadorService: No se pudo obtener URL', [
                'ruta' => $rutaArchivo,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
