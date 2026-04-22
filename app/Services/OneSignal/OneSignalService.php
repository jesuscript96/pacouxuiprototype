<?php

declare(strict_types=1);

namespace App\Services\OneSignal;

use App\Models\Empresa;
use Berkayk\OneSignal\OneSignalClient;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class OneSignalService
{
    protected ?OneSignalClient $client = null;

    protected ?Empresa $empresa = null;

    protected bool $modoSimulacion = false;

    public function paraEmpresa(Empresa $empresa): self
    {
        $this->empresa = $empresa;
        $this->client = null;

        return $this;
    }

    public function simular(bool $simular = true): self
    {
        $this->modoSimulacion = $simular;

        return $this;
    }

    protected function getClient(): ?OneSignalClient
    {
        if ($this->client instanceof OneSignalClient) {
            return $this->client;
        }

        if (! $this->empresa instanceof Empresa) {
            Log::warning('OneSignalService: No se ha configurado una empresa');

            return null;
        }

        $credentials = $this->empresa->getOneSignalCredentials();

        if ($credentials === null) {
            Log::warning('OneSignalService: Empresa sin credenciales OneSignal', [
                'empresa_id' => $this->empresa->id,
                'empresa_nombre' => $this->empresa->nombre,
            ]);

            return null;
        }

        $this->client = new OneSignalClient(
            $credentials['app_id'],
            $credentials['rest_api_key'],
            null,
            (int) config('onesignal.guzzle_client_timeout', 0),
            config('onesignal.rest_api_url'),
        );

        return $this->client;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $opciones
     * @return array<string, mixed>|null
     */
    public function enviarATokens(
        array $playerIds,
        string $titulo,
        string $mensaje,
        array $data = [],
        array $opciones = []
    ): ?array {
        if ($playerIds === []) {
            Log::info('OneSignalService: No hay tokens para enviar');

            return null;
        }

        if ($this->modoSimulacion) {
            Log::info('OneSignalService [SIMULACIÓN]: Envío de notificación', [
                'empresa_id' => $this->empresa?->id,
                'tokens_count' => count($playerIds),
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'data' => $data,
            ]);

            return [
                'id' => 'simulated-'.uniqid(),
                'recipients' => count($playerIds),
                'simulado' => true,
            ];
        }

        $client = $this->getClient();

        if (! $client instanceof OneSignalClient) {
            return null;
        }

        try {
            $params = $this->buildParams($playerIds, $titulo, $mensaje, $data, $opciones);
            $response = $client->sendNotificationCustom($params);
            $decoded = $this->decodeResponse($response);

            Log::info('OneSignalService: Notificación enviada', [
                'empresa_id' => $this->empresa?->id,
                'tokens_count' => count($playerIds),
                'response' => $decoded,
            ]);

            return $decoded;
        } catch (Throwable $e) {
            Log::error('OneSignalService: Error al enviar notificación', [
                'empresa_id' => $this->empresa?->id,
                'error' => $e->getMessage(),
                'tokens_count' => count($playerIds),
            ]);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $opciones
     * @return list<array{chunk: int, tokens: int, response: array<string, mixed>|null}>
     */
    public function enviarMasivo(
        array $playerIds,
        string $titulo,
        string $mensaje,
        array $data = [],
        array $opciones = []
    ): array {
        $chunks = array_chunk($playerIds, 2000);
        $resultados = [];

        foreach ($chunks as $index => $chunk) {
            $resultado = $this->enviarATokens($chunk, $titulo, $mensaje, $data, $opciones);
            $resultados[] = [
                'chunk' => $index + 1,
                'tokens' => count($chunk),
                'response' => $resultado,
            ];
        }

        return $resultados;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $opciones
     * @return array<string, mixed>
     */
    protected function buildParams(
        array $playerIds,
        string $titulo,
        string $mensaje,
        array $data,
        array $opciones
    ): array {
        $credentials = $this->empresa?->getOneSignalCredentials() ?? [];

        $params = [
            'app_id' => $credentials['app_id'] ?? '',
            'include_player_ids' => $playerIds,
            'headings' => ['en' => $titulo],
            'contents' => ['en' => $mensaje],
            'data' => $data,
            'ios_badgeType' => 'Increase',
            'ios_badgeCount' => 1,
            'ios_sound' => 'sound.wav',
            'priority' => 10,
            'large_icon' => 'ic_stat_onesignal_default',
        ];

        if (! empty($credentials['android_channel_id'])) {
            $params['android_channel_id'] = $credentials['android_channel_id'];
        }

        if (! empty($opciones['url'])) {
            $params['url'] = $opciones['url'];
        }

        if (! empty($opciones['buttons'])) {
            $params['buttons'] = $opciones['buttons'];
        }

        if (! empty($opciones['send_after'])) {
            $params['send_after'] = $opciones['send_after'];
        }

        return $params;
    }

    public function estaConfigurado(): bool
    {
        if (! $this->empresa instanceof Empresa) {
            return false;
        }

        return $this->empresa->getOneSignalCredentials() !== null;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function decodeResponse(mixed $response): ?array
    {
        if ($response instanceof ResponseInterface) {
            $body = (string) $response->getBody();
            $decoded = json_decode($body, true);

            return is_array($decoded) ? $decoded : null;
        }

        if (is_array($response)) {
            return $response;
        }

        return null;
    }
}
