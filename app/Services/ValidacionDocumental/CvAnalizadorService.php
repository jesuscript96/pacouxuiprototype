<?php

declare(strict_types=1);

namespace App\Services\ValidacionDocumental;

use App\Contracts\ValidadorDocumentoInterface;
use App\DTO\ResultadoValidacion;
use App\Models\CandidatoReclutamiento;
use App\Services\ArchivoService;
use App\Services\OpenAI\OpenAIService;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Analizador de CV con IA vía OpenAI (reemplaza CvAnalizadorStub).
 *
 * Legacy: app/Traits/Validations/CVTrait.php
 * Extracción de texto: smalot/pdfparser (mismo que el legacy)
 */
class CvAnalizadorService implements ValidadorDocumentoInterface
{
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
            Log::info('CvAnalizadorService: OpenAI no configurado, sin análisis', [
                'candidato_id' => $candidato->id,
            ]);

            return ResultadoValidacion::sinValidar();
        }

        $textoCv = $this->extraerTextoPdf($rutaArchivo);
        $textoBase = ! empty($textoCv)
            ? $textoCv
            : $this->construirContextoCandidato($candidato);

        $resultado = $this->openAI->chat(
            $this->systemPrompt(),
            $this->userPrompt($candidato, $textoBase),
        );

        if (! $resultado['success']) {
            Log::warning('CvAnalizadorService: Error de OpenAI', [
                'candidato_id' => $candidato->id,
                'error' => $resultado['error'] ?? 'desconocido',
            ]);

            return ResultadoValidacion::fallido($resultado['error'] ?? 'Error de OpenAI');
        }

        $analisis = $this->parsearRespuesta($resultado['content'] ?? '');

        if ($analisis === null) {
            return ResultadoValidacion::fallido('No se pudo parsear respuesta de OpenAI');
        }

        // BL: Legacy usa job_comparison.score (1-10)
        $score = (float) ($analisis['job_comparison']['score'] ?? 0);

        Log::info('CvAnalizadorService: CV analizado', [
            'candidato_id' => $candidato->id,
            'score' => $score,
            'compatibility' => $analisis['job_comparison']['compatibility'] ?? null,
        ]);

        return ResultadoValidacion::exitoso(
            datosExtraidos: $analisis,
            score: $score,
        );
    }

    public function tipoDocumento(): string
    {
        return 'cv';
    }

    /**
     * BL: Extrae texto del PDF usando smalot/pdfparser (legacy: CVTrait.php líneas 33-35).
     */
    private function extraerTextoPdf(string $rutaArchivo): ?string
    {
        try {
            $contenido = $this->archivoService->obtener($rutaArchivo);

            if ($contenido === null) {
                Log::info('CvAnalizadorService: Archivo no encontrado', ['ruta' => $rutaArchivo]);

                return null;
            }

            $parser = new PdfParser;
            $pdf = $parser->parseContent($contenido);
            $texto = $pdf->getText();

            if (empty(trim($texto))) {
                Log::info('CvAnalizadorService: PDF sin texto extraíble', ['ruta' => $rutaArchivo]);

                return null;
            }

            return trim($texto);

        } catch (\Exception $e) {
            Log::warning('CvAnalizadorService: Error extrayendo texto del PDF', [
                'ruta' => $rutaArchivo,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Fallback: construye contexto desde datos del formulario del candidato.
     */
    private function construirContextoCandidato(CandidatoReclutamiento $candidato): string
    {
        $partes = [];

        if ($candidato->nombre_completo) {
            $partes[] = "Nombre: {$candidato->nombre_completo}";
        }
        if ($candidato->email) {
            $partes[] = "Email: {$candidato->email}";
        }
        if ($candidato->telefono) {
            $partes[] = "Teléfono: {$candidato->telefono}";
        }

        $formulario = $candidato->valores_formulario ?? [];
        foreach ($formulario as $campo => $valor) {
            if (is_string($valor) && ! empty($valor)) {
                $partes[] = ucfirst(str_replace('_', ' ', $campo)).': '.$valor;
            }
        }

        return implode("\n", $partes);
    }

    /**
     * BL: System prompt del legacy (CVTrait.php línea 75).
     */
    private function systemPrompt(): string
    {
        return 'Eres un experto en recursos humanos y análisis de perfiles profesionales. Tu tarea es leer currículums vitae (CVs) y generar un resumen estructurado que sea útil para reclutadores y tomadores de decisiones. Sé claro, objetivo y detallado. Si encuentras contenido HTML en los requisitos o aptitudes, extrae únicamente el texto relevante ignorando las etiquetas.';
    }

    /**
     * BL: User prompt del legacy (CVTrait.php líneas 78-122).
     * Estructura de 10 campos con job_comparison.
     */
    private function userPrompt(CandidatoReclutamiento $candidato, string $textoPerfil): string
    {
        $vacante = $candidato->vacante;
        $requisitos = $vacante?->requisitos ?: '[No se proporcionó requisitos de vacante]';
        $aptitudes = $vacante?->aptitudes ?: '[No se proporcionó aptitudes de vacante]';
        $puesto = $vacante?->puesto ?: '[No se proporcionó descripción de vacante]';

        $instrucciones = <<<'INSTRUCCIONES'
A continuación te proporciono el texto extraído de un CV y la descripción de la vacante. Quiero que me devuelvas un análisis estructurado con los siguientes bloques en JSON:

1. profile_summary: Resumen general del perfil (2-3 líneas).
2. strengths: Lista de 3 a 5 fortalezas clave del candidato.
3. weaknesses: Áreas de mejora o posibles brechas (si las hay).
4. hard_skills: Lista de habilidades técnicas.
5. soft_skills: Lista de habilidades blandas.
6. highlight_experience: Lista de experiencias laborales destacadas (empresa, rol, duración, logros clave).
7. education: Nivel educativo, instituciones y fechas si están disponibles.
8. languages: Idiomas con nivel estimado.
9. recommendations: Recomendaciones de desarrollo profesional.
10. job_comparison: Comparación estructurada contra la vacante proporcionada. Incluye:
  - por cada requisito clave de la vacante, si el perfil cumple ✅, parcialmente ⚠ o no cumple ❌
  - un comentario por criterio
  - puntuación general del ajuste (de 1 a 10)
  - resumen del nivel de compatibilidad (Alto, Medio, Bajo)

Formato de respuesta esperado: JSON estructurado con cada uno de los 10 campos anteriores.
Debes responder *únicamente con un objeto JSON válido*, con los siguientes 10 campos obligatorios y exactamente estos nombres:
{
"profile_summary": "",
"strengths": [],
"weaknesses": [],
"hard_skills": [],
"soft_skills": [],
"highlight_experience": [],
"education": [],
"languages": [],
"recommendations": [],
"job_comparison": {
    "requirements": [
        {"requirement": "", "status": "✅/⚠/❌", "comment": ""}
    ],
    "score": 0,
    "compatibility": ""
}
}
- Si algún campo no aplica, devuélvelo vacío ("" o []).
- No añadas texto fuera del JSON.
- Mantén el mismo orden de los campos.
INSTRUCCIONES;

        return $instrucciones."\n\n"
            ."Texto del CV:\n\n---\n".$textoPerfil."\n---\n\n"
            ."Texto de la descripción de la vacante:\n\n---\n".$puesto."\n---\n\n"
            ."Texto de los requisitos de la vacante:\n\n---\n".$requisitos."\n---\n\n"
            ."Texto de las aptitudes de la vacante:\n\n---\n".$aptitudes."\n---";
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parsearRespuesta(string $content): ?array
    {
        $content = preg_replace('/```json\s*/', '', $content) ?? $content;
        $content = preg_replace('/```\s*/', '', $content) ?? $content;
        $content = trim($content);

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('CvAnalizadorService: Respuesta no es JSON válido', [
                'error' => json_last_error_msg(),
            ]);

            return null;
        }

        return $data;
    }
}
