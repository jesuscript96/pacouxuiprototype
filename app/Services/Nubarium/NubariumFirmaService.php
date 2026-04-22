<?php

namespace App\Services\Nubarium;

use App\Models\CartaSua;
use App\Models\Empresa;
use App\Services\ArchivoService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NubariumFirmaService
{
    private string $url;

    private string $user;

    private string $password;

    public function __construct()
    {
        $this->url = (string) config('services.nubarium.nom151_url', '');
        $this->user = (string) config('services.nubarium.user', '');
        $this->password = (string) config('services.nubarium.password', '');
    }

    public function estaConfigurado(): bool
    {
        return ! empty($this->url) && ! empty($this->user) && ! empty($this->password);
    }

    public function empresaTieneFirma(Empresa $empresa): bool
    {
        return (bool) $empresa->tiene_firma_nubarium;
    }

    /**
     * BL: Firma una carta SUA con NOM-151 vía Nubarium.
     * Si la empresa no tiene firma Nubarium habilitada, retorna success sin datos NOM-151.
     *
     * El payload sigue la estructura del legacy: pdf base64 + firmantes[].
     *
     * @return array{success: bool, nom151?: string|null, hash?: string|null, codigo_validacion?: string|null, pdf_firmado?: string|null, error?: string}
     */
    public function firmarCartaSua(CartaSua $carta, string $imagenFirmaBase64): array
    {
        if (! $this->estaConfigurado()) {
            Log::warning('NubariumFirmaService: Servicio no configurado');

            return [
                'success' => false,
                'error' => 'Servicio de firma no configurado',
            ];
        }

        $empresa = $carta->empresa;

        if (! $this->empresaTieneFirma($empresa)) {
            return [
                'success' => true,
                'nom151' => null,
                'hash' => null,
                'codigo_validacion' => null,
                'pdf_firmado' => null,
            ];
        }

        $pdfBase64 = $this->obtenerPdfBase64($carta);

        if ($pdfBase64 === null) {
            return [
                'success' => false,
                'error' => 'No se pudo obtener el PDF de la carta',
            ];
        }

        try {
            $colaborador = $carta->colaborador;
            $email = $colaborador->user?->email ?? 'None';

            // BL: Posición de firma — legacy usa [200, 50, 410, 90, página].
            // Cartas SUA son de 1 página, posición en página 0.
            $posicionFirma = [200, 50, 410, 90, 0];

            $payload = [
                'pdf' => $pdfBase64,
                'firmantes' => [
                    [
                        'nombreCompleto' => $colaborador->nombre_completo ?? $colaborador->nombre,
                        'correoElectronico' => $email,
                        'firma' => [
                            'imagen' => $imagenFirmaBase64,
                            'ubicacion' => $posicionFirma,
                        ],
                    ],
                ],
                'obtenerNOM' => true,
            ];

            $response = Http::withBasicAuth($this->user, $this->password)
                ->timeout(30)
                ->post($this->url, $payload);

            if (! $response->successful()) {
                Log::error('NubariumFirmaService: Error en respuesta', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'carta_id' => $carta->id,
                ]);

                return [
                    'success' => false,
                    'error' => 'Error al obtener firma NOM-151: '.$response->status(),
                ];
            }

            $data = $response->json();

            if (($data['estatus'] ?? null) !== 'OK') {
                Log::error('NubariumFirmaService: Estatus no OK', [
                    'estatus' => $data['estatus'] ?? 'desconocido',
                    'mensaje' => $data['mensaje'] ?? '',
                    'carta_id' => $carta->id,
                ]);

                return [
                    'success' => false,
                    'error' => $data['mensaje'] ?? 'Error desconocido de Nubarium',
                ];
            }

            return [
                'success' => true,
                'nom151' => $data['nom151'] ?? null,
                'hash' => $data['hash'] ?? null,
                'codigo_validacion' => $data['codigoValidacion'] ?? null,
                'pdf_firmado' => $data['pdfFirmado'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('NubariumFirmaService: Excepción', [
                'message' => $e->getMessage(),
                'carta_id' => $carta->id,
            ]);

            return [
                'success' => false,
                'error' => 'Error de conexión con servicio de firma',
            ];
        }
    }

    /**
     * Obtiene el contenido del PDF en base64 desde el storage.
     */
    private function obtenerPdfBase64(CartaSua $carta): ?string
    {
        if (! $carta->pdf_path) {
            return null;
        }

        $archivoService = app(ArchivoService::class);
        $contenido = $archivoService->obtener($carta->pdf_path);

        if ($contenido === null) {
            return null;
        }

        return base64_encode($contenido);
    }
}
