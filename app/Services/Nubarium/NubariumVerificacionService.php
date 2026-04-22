<?php

declare(strict_types=1);

namespace App\Services\Nubarium;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente HTTP para verificaciones vía Nubarium (INE, CSF, IMSS, OCR).
 *
 * Legacy: app/Traits/Validations/INETrait.php, CSFTrait.php
 * Auth: HTTP Basic con config('services.nubarium.user') / password
 */
class NubariumVerificacionService
{
    private string $user;

    private string $password;

    private string $ocrUrl;

    private string $ineUrl;

    private string $csfUrl;

    private string $imssUrl;

    public function __construct()
    {
        $this->user = (string) config('services.nubarium.user');
        $this->password = (string) config('services.nubarium.password');
        $this->ocrUrl = (string) config('services.nubarium.ocr_url');
        $this->ineUrl = (string) config('services.nubarium.ine_url');
        $this->csfUrl = (string) config('services.nubarium.csf_url');
        $this->imssUrl = (string) config('services.nubarium.imss_url');
    }

    public function estaConfigurado(): bool
    {
        return ! empty($this->user) && ! empty($this->password);
    }

    /**
     * BL: OCR de INE — extrae datos de la imagen de la credencial.
     * Legacy: POST ocr_url/obtener_datos_id con { id: base64_frente, idReverso: base64_reverso }
     *
     * @return array{success: bool, datos?: array<string, mixed>, error?: string}
     */
    public function ocrIne(string $imagenFrenteBase64, ?string $imagenReversoBase64 = null): array
    {
        if (! $this->estaConfigurado() || empty($this->ocrUrl)) {
            return ['success' => false, 'error' => 'Nubarium OCR no configurado'];
        }

        $body = ['id' => $imagenFrenteBase64];
        if ($imagenReversoBase64) {
            $body['idReverso'] = $imagenReversoBase64;
        }

        return $this->postNubarium("{$this->ocrUrl}/obtener_datos_id", $body, 'ocrIne');
    }

    /**
     * BL: Validar INE contra el padrón del INE.
     * Legacy: POST ine_url/valida_ine con campos según subTipo de credencial.
     *
     * SubTipos:
     *   C: ocr, claveElector, numeroEmision (últimos 2 de registro)
     *   D: ocr, cic
     *   E,F,G,H: cic, identificadorCiudadano
     *
     * @param  array{subTipo?: string, ocr?: string, claveElector?: string, registro?: string, cic?: string, identificadorCiudadano?: string}  $datos
     * @return array{success: bool, valido?: bool, datos?: array<string, mixed>, error?: string}
     */
    public function validarIne(array $datos): array
    {
        if (! $this->estaConfigurado() || empty($this->ineUrl)) {
            return ['success' => false, 'error' => 'Nubarium INE no configurado'];
        }

        $subTipo = $datos['subTipo'] ?? 'C';
        $body = [];

        if (in_array($subTipo, ['C', 'D'])) {
            $body['ocr'] = $datos['ocr'] ?? '';
        }

        if ($subTipo === 'C') {
            $body['claveElector'] = $datos['claveElector'] ?? '';
            $body['numeroEmision'] = substr($datos['registro'] ?? '', -2);
        }

        if (in_array($subTipo, ['D', 'E', 'F', 'G', 'H'])) {
            $body['cic'] = $datos['cic'] ?? '';
        }

        if (in_array($subTipo, ['E', 'F', 'G', 'H'])) {
            $body['identificadorCiudadano'] = $datos['identificadorCiudadano'] ?? '';
        }

        $resultado = $this->postNubarium("{$this->ineUrl}/valida_ine", $body, 'validarIne');

        if (! $resultado['success']) {
            return $resultado;
        }

        $respuesta = $resultado['datos'] ?? [];

        return [
            'success' => true,
            'valido' => ($respuesta['estatus'] ?? '') !== 'ERROR',
            'codigoValidacion' => $respuesta['codigoValidacion'] ?? null,
            'datos' => $respuesta,
        ];
    }

    /**
     * BL: Validar Constancia de Situación Fiscal.
     * Legacy: POST csf_url/consultar_cif con { tipo: 'pdf'|'imagen', documento: base64 }
     *
     * @return array{success: bool, datos?: array<string, mixed>, error?: string}
     */
    public function validarCsf(string $archivoBase64, string $formato = 'pdf'): array
    {
        if (! $this->estaConfigurado() || empty($this->csfUrl)) {
            return ['success' => false, 'error' => 'Nubarium CSF no configurado'];
        }

        return $this->postNubarium("{$this->csfUrl}/consultar_cif", [
            'tipo' => $formato,
            'documento' => $archivoBase64,
        ], 'validarCsf');
    }

    /**
     * BL: Obtener NSS por CURP vía IMSS.
     * Legacy: POST nubarium_imss/obtener_nss con { curp, url }
     * Auth: user 'paco' + nubarium password (no nubarium_user)
     *
     * @return array{success: bool, datos?: array<string, mixed>, error?: string}
     */
    public function obtenerNss(string $curp, ?string $webhookUrl = null): array
    {
        if (empty($this->password) || empty($this->imssUrl)) {
            return ['success' => false, 'error' => 'Nubarium IMSS no configurado'];
        }

        return $this->postNubariumImss("{$this->imssUrl}/obtener_nss", [
            'curp' => $curp,
            'url' => $webhookUrl ?? '',
        ], 'obtenerNss');
    }

    /**
     * BL: Obtener historial laboral IMSS por CURP y NSS.
     * Legacy: POST nubarium_imss/obtener_historial con { curp, nss, url }
     * Auth: user 'paco' + nubarium password
     *
     * @return array{success: bool, datos?: array<string, mixed>, error?: string}
     */
    public function obtenerHistorialImss(string $curp, string $nss, ?string $webhookUrl = null): array
    {
        if (empty($this->password) || empty($this->imssUrl)) {
            return ['success' => false, 'error' => 'Nubarium IMSS no configurado'];
        }

        return $this->postNubariumImss("{$this->imssUrl}/obtener_historial", [
            'curp' => $curp,
            'nss' => $nss,
            'url' => $webhookUrl ?? '',
        ], 'obtenerHistorialImss');
    }

    /**
     * BL: IMSS usa auth con user 'paco' hardcodeado (legacy: VerificationNotificationController).
     *
     * @param  array<string, mixed>  $body
     * @return array{success: bool, datos?: array<string, mixed>, error?: string}
     */
    private function postNubariumImss(string $url, array $body, string $operacion): array
    {
        try {
            $response = Http::withBasicAuth('paco', $this->password)
                ->timeout(60)
                ->post($url, $body);

            if (! $response->successful()) {
                Log::error("NubariumVerificacionService: Error en {$operacion}", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return ['success' => false, 'error' => "Error en {$operacion}: ".$response->status()];
            }

            return [
                'success' => true,
                'datos' => $response->json(),
            ];

        } catch (\Exception $e) {
            Log::error("NubariumVerificacionService: Excepción en {$operacion}", [
                'message' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Error de conexión con Nubarium'];
        }
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array{success: bool, datos?: array<string, mixed>, error?: string}
     */
    private function postNubarium(string $url, array $body, string $operacion): array
    {
        try {
            $response = Http::withBasicAuth($this->user, $this->password)
                ->timeout(60)
                ->post($url, $body);

            if (! $response->successful()) {
                Log::error("NubariumVerificacionService: Error en {$operacion}", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return ['success' => false, 'error' => "Error en {$operacion}: ".$response->status()];
            }

            return [
                'success' => true,
                'datos' => $response->json(),
            ];

        } catch (\Exception $e) {
            Log::error("NubariumVerificacionService: Excepción en {$operacion}", [
                'message' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Error de conexión con Nubarium'];
        }
    }
}
