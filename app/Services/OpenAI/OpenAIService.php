<?php

declare(strict_types=1);

namespace App\Services\OpenAI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    private string $baseUrl;

    private string $apiKey;

    private string $model;

    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.openai.base_url'), '/');
        $this->apiKey = (string) config('services.openai.key');
        $this->model = (string) config('services.openai.model', 'gpt-4o');
        $this->timeout = (int) config('services.openai.timeout', 60);
    }

    public function estaConfigurado(): bool
    {
        return ! empty($this->apiKey) && ! empty($this->baseUrl);
    }

    /**
     * Envía un prompt a OpenAI Chat Completions y retorna la respuesta.
     *
     * @return array{success: bool, content?: string|null, usage?: array<string, mixed>|null, error?: string}
     */
    public function chat(string $systemPrompt, string $userMessage, ?string $model = null): array
    {
        if (! $this->estaConfigurado()) {
            return [
                'success' => false,
                'error' => 'OpenAI no está configurado',
            ];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $model ?? $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                    'temperature' => 0.3,
                ]);

            if (! $response->successful()) {
                Log::error('OpenAIService: Error en respuesta', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'Error de OpenAI: '.$response->status(),
                ];
            }

            $data = $response->json();

            return [
                'success' => true,
                'content' => $data['choices'][0]['message']['content'] ?? null,
                'usage' => $data['usage'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('OpenAIService: Excepción', ['message' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => 'Error de conexión con OpenAI',
            ];
        }
    }

    /**
     * BL: Envía solicitud multimodal (texto + imagen) a la API Responses de OpenAI.
     * Legacy: POST {base_url}/responses con modelo gpt-4.1 y input_image.
     * Usado para comprobantes de domicilio.
     *
     * @return array{success: bool, content?: string|null, error?: string}
     */
    public function analizarImagen(string $prompt, string $imageUrl, ?string $model = null): array
    {
        if (! $this->estaConfigurado()) {
            return [
                'success' => false,
                'error' => 'OpenAI no está configurado',
            ];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/responses", [
                    'model' => $model ?? 'gpt-4.1',
                    'input' => [
                        [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'input_text', 'text' => $prompt],
                                ['type' => 'input_image', 'image_url' => $imageUrl],
                            ],
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('OpenAIService: Error en analizarImagen', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'Error de OpenAI: '.$response->status(),
                ];
            }

            $data = $response->json();

            return [
                'success' => true,
                'content' => $data['output'][0]['content'][0]['text'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('OpenAIService: Excepción en analizarImagen', ['message' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => 'Error de conexión con OpenAI',
            ];
        }
    }
}
