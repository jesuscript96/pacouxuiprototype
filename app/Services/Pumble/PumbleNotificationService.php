<?php

declare(strict_types=1);

namespace App\Services\Pumble;

use App\Models\Colaborador;
use App\Models\NotificacionPush;
use App\Models\NotificacionPushDestinatario;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class PumbleNotificationService
{
    private const MAX_VISIBLE_DESTINATARIOS = 20;

    private const MAX_PAYLOAD_CHARS = 10000;

    public function enviarNotificacionPush(NotificacionPush $notificacion): bool
    {
        if (! config('services.pumble.enabled')) {
            return false;
        }

        $webhookUrl = trim((string) config('services.pumble.webhook_url'));
        if ($webhookUrl === '') {
            Log::warning('PumbleNotificationService: Webhook URL vacía');

            return false;
        }

        try {
            $notificacion->loadMissing(['empresa', 'destinatarios.user.colaborador']);

            $payload = $this->ajustarPayloadAlLimite($notificacion);

            $response = Http::timeout(15)
                ->acceptJson()
                ->asJson()
                ->post($webhookUrl, $payload);

            if (! $response->successful()) {
                Log::warning('PumbleNotificationService: Respuesta no exitosa', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (Throwable $e) {
            Log::warning('PumbleNotificationService: Error al enviar', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Expuesto para pruebas: mismo cuerpo que se enviaría (sin POST).
     *
     * @return array<string, mixed>
     */
    public function construirPayloadParaPruebas(NotificacionPush $notificacion, int $maxVisibles = self::MAX_VISIBLE_DESTINATARIOS): array
    {
        $notificacion->loadMissing(['empresa', 'destinatarios.user.colaborador']);

        return $this->construirPayload($notificacion, $maxVisibles, null);
    }

    /**
     * @return array<string, mixed>
     */
    private function ajustarPayloadAlLimite(NotificacionPush $notificacion): array
    {
        $maxMensaje = null;

        for ($maxVisibles = self::MAX_VISIBLE_DESTINATARIOS; $maxVisibles >= 0; $maxVisibles--) {
            $payload = $this->construirPayload($notificacion, $maxVisibles, $maxMensaje);
            if ($this->longitudJson($payload) <= self::MAX_PAYLOAD_CHARS) {
                return $payload;
            }
        }

        for ($len = 4000; $len >= 100; $len -= 200) {
            $maxMensaje = $len;
            $payload = $this->construirPayload($notificacion, 0, $maxMensaje);
            if ($this->longitudJson($payload) <= self::MAX_PAYLOAD_CHARS) {
                return $payload;
            }
        }

        return $this->construirPayloadEsencial($notificacion);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function longitudJson(array $payload): int
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);

        return $json === false ? self::MAX_PAYLOAD_CHARS + 1 : strlen($json);
    }

    /**
     * @return array<string, mixed>
     */
    private function construirPayload(NotificacionPush $notificacion, int $maxVisibles, ?int $maxMensajeChars): array
    {
        $empresaNombre = $notificacion->empresa?->nombre ?? 'N/D';
        $fechaEnvio = $this->resolverFechaEnvio($notificacion);
        $titulo = $notificacion->titulo;
        $mensaje = $maxMensajeChars !== null
            ? $this->truncarTexto($notificacion->mensaje, $maxMensajeChars)
            : $notificacion->mensaje;

        $titleLink = $this->resolverTitleLink($notificacion);

        $destinatarios = $notificacion->destinatarios->sortBy(fn ($d) => $d->user_id);
        $total = $destinatarios->count();
        $lineasDestinatarios = $this->formatearLineasDestinatarios($destinatarios, $maxVisibles, $total);

        return [
            'text' => "📢 **Nueva Notificación Push Enviada**\n\n*Empresa:* {$empresaNombre}\n*Fecha:* {$fechaEnvio}",
            'attachments' => [
                [
                    'title' => $titulo,
                    'title_link' => $titleLink,
                    'text' => $mensaje,
                    'color' => '#2563eb',
                    'footer' => "Notificación Push #{$notificacion->id}",
                ],
                [
                    'pretext' => "📋 **Destinatarios ({$total} colaboradores)**",
                    'text' => $lineasDestinatarios,
                    'color' => '#10b981',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function construirPayloadEsencial(NotificacionPush $notificacion): array
    {
        $empresaNombre = $notificacion->empresa?->nombre ?? 'N/D';
        $fechaEnvio = $this->resolverFechaEnvio($notificacion);
        $total = $notificacion->destinatarios->count();

        return [
            'text' => "📢 **Nueva Notificación Push Enviada**\n\n*Empresa:* {$empresaNombre}\n*Fecha:* {$fechaEnvio}\n*Destinatarios:* {$total}\n*(Payload recortado por límite de 10.000 caracteres)*",
            'attachments' => [
                [
                    'title' => $this->truncarTexto($notificacion->titulo, 200),
                    'title_link' => $this->resolverTitleLink($notificacion),
                    'text' => $this->truncarTexto($notificacion->mensaje, 500),
                    'color' => '#2563eb',
                    'footer' => "Notificación Push #{$notificacion->id}",
                ],
            ],
        ];
    }

    private function resolverFechaEnvio(NotificacionPush $notificacion): string
    {
        $tz = config('app.timezone') ?? 'UTC';
        $fecha = $notificacion->enviada_at ?? now();

        return $fecha->timezone($tz)->format('d/m/Y H:i');
    }

    private function resolverTitleLink(NotificacionPush $notificacion): string
    {
        $url = $notificacion->url;
        if (is_string($url) && filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        return rtrim((string) config('app.url'), '/');
    }

    /**
     * @param  Collection<int, NotificacionPushDestinatario>  $destinatarios
     */
    private function formatearLineasDestinatarios(Collection $destinatarios, int $maxVisibles, int $total): string
    {
        if ($total === 0) {
            return '_Sin destinatarios registrados para esta notificación._';
        }

        $items = $destinatarios->take($maxVisibles);
        $lineas = $items->map(function ($dest): string {
            $col = $dest->colaborador;
            if (! $col instanceof Colaborador) {
                return '• (colaborador no disponible)';
            }

            $nombre = $this->nombreCompletoColaborador($col);
            $email = $this->emailColaborador($col);

            return "• {$nombre} ({$email})";
        })->implode("\n");

        $restantes = $total - $items->count();
        if ($restantes > 0) {
            $lineas .= "\n\n_…y {$restantes} más_";
        }

        return $lineas;
    }

    private function nombreCompletoColaborador(Colaborador $colaborador): string
    {
        $partes = array_filter([
            $colaborador->nombre,
            $colaborador->apellido_paterno,
            $colaborador->apellido_materno,
        ], fn (?string $p): bool => $p !== null && $p !== '');

        $nombre = trim(implode(' ', $partes));

        return $nombre !== '' ? $nombre : 'Sin nombre';
    }

    private function emailColaborador(Colaborador $colaborador): string
    {
        $email = $colaborador->user?->email ?? $colaborador->email;

        return is_string($email) && $email !== '' ? $email : 'sin-correo';
    }

    private function truncarTexto(string $texto, int $maxChars): string
    {
        if (mb_strlen($texto) <= $maxChars) {
            return $texto;
        }

        return mb_substr($texto, 0, max(0, $maxChars - 3)).'...';
    }
}
