<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\HistorialLaboralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PalencaWebhookController extends Controller
{
    public function __construct(
        private HistorialLaboralService $historialService,
    ) {}

    /**
     * POST /api/webhooks/palenca
     *
     * BL: Palenca envía un webhook cuando la verificación de historial
     * laboral está completa. Auth vía HTTP Basic configurado en Palenca dashboard.
     */
    public function handle(Request $request): JsonResponse
    {
        if (! $this->validarAutenticacion($request)) {
            Log::warning('PalencaWebhook: Autenticación fallida', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = $request->all();
        $event = $payload['event'] ?? $payload['type'] ?? 'unknown';
        $verificationId = $payload['verification_id']
            ?? $payload['data']['verification_id']
            ?? null;

        Log::info('PalencaWebhook: Evento recibido', [
            'event' => $event,
            'verification_id' => $verificationId,
        ]);

        if (! $verificationId) {
            return response()->json(['error' => 'verification_id requerido'], 400);
        }

        if (in_array($event, ['verification.completed', 'completed', 'success'])) {
            $ok = $this->historialService->procesarWebhook($verificationId, $payload);

            return $ok
                ? response()->json(['status' => 'processed'])
                : response()->json(['error' => 'Error procesando verificación'], 422);
        }

        Log::info('PalencaWebhook: Evento no procesado', ['event' => $event]);

        return response()->json(['status' => 'acknowledged']);
    }

    private function validarAutenticacion(Request $request): bool
    {
        $expectedUser = config('services.palenca.webhook_user');
        $expectedPassword = config('services.palenca.webhook_password');

        if (empty($expectedUser) || empty($expectedPassword)) {
            return true;
        }

        $authHeader = $request->header('Authorization');

        if (! $authHeader || ! str_starts_with($authHeader, 'Basic ')) {
            return false;
        }

        $decoded = base64_decode(substr($authHeader, 6), true);
        if ($decoded === false) {
            return false;
        }

        $parts = explode(':', $decoded, 2);
        $user = $parts[0] ?? '';
        $password = $parts[1] ?? '';

        return hash_equals($expectedUser, $user)
            && hash_equals($expectedPassword, $password);
    }
}
