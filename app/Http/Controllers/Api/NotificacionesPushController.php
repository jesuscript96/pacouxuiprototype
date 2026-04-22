<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListNotificacionesPushRequest;
use App\Http\Resources\NotificacionPushResource;
use App\Models\NotificacionPushDestinatario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificacionesPushController extends Controller
{
    /**
     * GET /api/notificaciones-push
     */
    public function index(ListNotificacionesPushRequest $request): AnonymousResourceCollection
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Usuario no autenticado');
        }

        /** @var array{estado?: string, per_page?: int|string} $data */
        $data = $request->validated();
        $estado = $data['estado'] ?? 'todas';
        $perPage = (int) ($data['per_page'] ?? 20);

        $query = NotificacionPushDestinatario::query()
            ->where('user_id', $user->id)
            ->where('enviado', true)
            ->with(['notificacionPush' => function ($q): void {
                $q->select([
                    'id',
                    'titulo',
                    'mensaje',
                    'url',
                    'data',
                    'enviada_at',
                ]);
            }])
            ->orderByDesc('created_at');

        if ($estado === 'no_leidas') {
            $query->where('estado_lectura', 'NO_LEIDA');
        } elseif ($estado === 'leidas') {
            $query->where('estado_lectura', 'LEIDA');
        }

        $destinatarios = $query->paginate($perPage);

        return NotificacionPushResource::collection($destinatarios);
    }

    /**
     * GET /api/notificaciones-push/no-leidas/count
     */
    public function countNoLeidas(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['count' => 0]);
        }

        $count = NotificacionPushDestinatario::query()
            ->where('user_id', $user->id)
            ->where('enviado', true)
            ->where('estado_lectura', 'NO_LEIDA')
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * GET /api/notificaciones-push/{id}
     */
    public function show(Request $request, int $id): NotificacionPushResource
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Usuario no autenticado');
        }

        $destinatario = NotificacionPushDestinatario::query()
            ->where('user_id', $user->id)
            ->where('notificacion_push_id', $id)
            ->where('enviado', true)
            ->with('notificacionPush')
            ->firstOrFail();

        return new NotificacionPushResource($destinatario);
    }

    /**
     * POST /api/notificaciones-push/{id}/leer
     */
    public function marcarComoLeida(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Usuario no autenticado');
        }

        $destinatario = NotificacionPushDestinatario::query()
            ->where('user_id', $user->id)
            ->where('notificacion_push_id', $id)
            ->firstOrFail();

        if ($destinatario->estado_lectura !== 'LEIDA') {
            $destinatario->marcarComoLeida();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como leída',
        ]);
    }

    /**
     * POST /api/notificaciones-push/leer-todas
     */
    public function marcarTodasComoLeidas(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Usuario no autenticado');
        }

        $actualizadas = NotificacionPushDestinatario::query()
            ->where('user_id', $user->id)
            ->where('enviado', true)
            ->where('estado_lectura', 'NO_LEIDA')
            ->update([
                'estado_lectura' => 'LEIDA',
                'leida_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => "Se marcaron {$actualizadas} notificaciones como leídas",
            'count' => $actualizadas,
        ]);
    }
}
