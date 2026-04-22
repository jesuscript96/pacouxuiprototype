<?php

declare(strict_types=1);

namespace App\Services\Palenca;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente HTTP para la API de Palenca (Buró de Ingresos).
 *
 * Legacy: app/Jobs/EmployeeHistories/EmployeeHistoryJob.php
 * Auth: x-api-key header con config('services.palenca.key')
 */
class PalencaService
{
    private string $baseUrl;

    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.palenca.url'), '/');
        $this->apiKey = (string) config('services.palenca.key');
    }

    public function estaConfigurado(): bool
    {
        return ! empty($this->baseUrl) && ! empty($this->apiKey);
    }

    /**
     * BL: Crea un consent para consultar historial laboral.
     * Legacy: POST /consents con identifier, ip_address, privacy_notice_url
     *
     * @return array{success: bool, consent_id?: string|null, error?: string}
     */
    public function crearConsent(string $curp, ?string $ipAddress = null): array
    {
        return $this->post('/consents', [
            'identifier' => $curp,
            'ip_address' => $ipAddress ?? request()?->ip() ?? '127.0.0.1',
            'privacy_notice_url' => 'https://login.paco.app/politicas-paco/Aviso-de-Privacidad.docx',
        ], 'crearConsent', 'consent_id', 'id');
    }

    /**
     * BL: Crea una verificación de historial laboral.
     * Legacy: POST /verifications con solo identifier (CURP).
     *
     * @return array{success: bool, verification_id?: string|null, status?: string|null, error?: string}
     */
    public function crearVerification(string $curp): array
    {
        return $this->post('/verifications', [
            'identifier' => $curp,
        ], 'crearVerification', 'verification_id', 'id');
    }

    /**
     * BL: Obtiene el perfil IMSS de un identifier (CURP).
     * Legacy: GET /profile/{identifier}
     * Campos: personal_info.first_name, personal_info.last_name, personal_info.nss, employment_status
     *
     * @return array{success: bool, nss?: string|null, nombre_imss?: string|null, estatus_laboral?: string|null, datos_completos?: array<string, mixed>|null, error?: string}
     */
    public function obtenerPerfil(string $identifier): array
    {
        if (! $this->estaConfigurado()) {
            return ['success' => false, 'error' => 'Palenca no configurado'];
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'accept' => 'application/json',
            ])
                ->timeout(30)
                ->get("{$this->baseUrl}/profile/{$identifier}");

            if (! $response->successful()) {
                Log::error('PalencaService: Error en obtenerPerfil', [
                    'status' => $response->status(),
                    'identifier' => $identifier,
                ]);

                return ['success' => false, 'error' => 'Error al obtener perfil: '.$response->status()];
            }

            $data = $response->json();
            $personalInfo = $data['personal_info'] ?? [];

            return [
                'success' => true,
                'nss' => $personalInfo['nss'] ?? null,
                'nombre_imss' => trim(($personalInfo['first_name'] ?? '').' '.($personalInfo['last_name'] ?? '')) ?: null,
                'estatus_laboral' => $data['employment_status'] ?? null,
                'datos_completos' => $data,
            ];

        } catch (\Exception $e) {
            Log::error('PalencaService: Excepción en obtenerPerfil', ['message' => $e->getMessage()]);

            return ['success' => false, 'error' => 'Error de conexión con Palenca'];
        }
    }

    /**
     * BL: Obtiene historial de empleos de un identifier (CURP).
     * Legacy: GET /employments/{identifier}
     * Campos: semanas_cotizadas, employment_history[] con start_date, end_date, employer, etc.
     *
     * @return array{success: bool, semanas_cotizadas?: int|null, empleos?: list<array<string, mixed>>, error?: string}
     */
    public function obtenerEmpleos(string $identifier): array
    {
        if (! $this->estaConfigurado()) {
            return ['success' => false, 'error' => 'Palenca no configurado'];
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'accept' => 'application/json',
            ])
                ->timeout(30)
                ->get("{$this->baseUrl}/employments/{$identifier}");

            if (! $response->successful()) {
                Log::error('PalencaService: Error en obtenerEmpleos', [
                    'status' => $response->status(),
                    'identifier' => $identifier,
                ]);

                return ['success' => false, 'error' => 'Error al obtener empleos: '.$response->status()];
            }

            $data = $response->json();

            return [
                'success' => true,
                'semanas_cotizadas' => isset($data['semanas_cotizadas']) ? (int) $data['semanas_cotizadas'] : null,
                'empleos' => $data['employment_history'] ?? [],
            ];

        } catch (\Exception $e) {
            Log::error('PalencaService: Excepción en obtenerEmpleos', ['message' => $e->getMessage()]);

            return ['success' => false, 'error' => 'Error de conexión con Palenca'];
        }
    }

    /**
     * Helper para POST requests a Palenca.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    private function post(string $path, array $body, string $operacion, string $keyRetorno, string $keyRespuesta): array
    {
        if (! $this->estaConfigurado()) {
            return ['success' => false, 'error' => 'Palenca no configurado'];
        }

        try {
            $response = Http::withHeaders(['x-api-key' => $this->apiKey])
                ->timeout(30)
                ->post("{$this->baseUrl}{$path}", $body);

            if (! $response->successful()) {
                Log::error("PalencaService: Error en {$operacion}", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return ['success' => false, 'error' => "Error en {$operacion}: ".$response->status()];
            }

            $data = $response->json();

            return [
                'success' => true,
                $keyRetorno => $data[$keyRespuesta] ?? $data[$keyRetorno] ?? null,
                'status' => $data['status'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error("PalencaService: Excepción en {$operacion}", ['message' => $e->getMessage()]);

            return ['success' => false, 'error' => 'Error de conexión con Palenca'];
        }
    }
}
