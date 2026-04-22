<?php

declare(strict_types=1);

namespace App\Services\NotificacionesPush;

use App\Contracts\ObtenerDestinatariosPushInterface;
use App\Models\Colaborador;
use App\Models\NotificacionPush;
use App\Models\NotificacionPushDestinatario;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ResolverDestinatariosService
{
    public function __construct(
        protected ObtenerDestinatariosPushInterface $obtenerDestinatarios
    ) {}

    /**
     * Resuelve los IDs de colaboradores finales según filtros de catálogo y bloque destinatarios.
     *
     * BL: La selección/filtrado trabaja con colaborador IDs (catálogo HR),
     * la conversión a user_id ocurre en persistirDestinatarios().
     *
     * @return Collection<int, int>
     */
    public function resolverColaboradorIds(NotificacionPush $notificacion): Collection
    {
        $filtros = $notificacion->getFiltrosConDefaults();
        $configDestinatarios = $filtros['destinatarios'] ?? [];

        $selectAll = (bool) ($configDestinatarios['select_all'] ?? true);
        $manualActivation = (array) ($configDestinatarios['manual_activation'] ?? []);
        $manualDeactivation = (array) ($configDestinatarios['manual_deactivation'] ?? []);

        Log::debug('ResolverDestinatariosService: Resolviendo destinatarios', [
            'notificacion_id' => $notificacion->id,
            'select_all' => $selectAll,
            'manual_activation_count' => count($manualActivation),
            'manual_deactivation_count' => count($manualDeactivation),
        ]);

        if ($selectAll) {
            $colaboradoresFiltrados = $this->obtenerDestinatarios
                ->obtenerColaboradoresFiltrados($notificacion);

            $ids = $colaboradoresFiltrados->pluck('id');

            if ($manualDeactivation !== []) {
                $deactivate = collect($manualDeactivation)->map(fn (mixed $id): int => (int) $id);
                $ids = $ids->diff($deactivate);
            }

            return $ids->values();
        }

        $activationIds = collect($manualActivation)
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        if ($activationIds->isEmpty()) {
            return collect();
        }

        return Colaborador::query()
            ->where('empresa_id', $notificacion->empresa_id)
            ->whereIn('id', $activationIds)
            ->orderBy('id')
            ->pluck('id')
            ->values();
    }

    /**
     * Sincroniza el pivote con el conjunto resuelto (reemplazo completo).
     *
     * BL: Convierte colaborador IDs → user IDs para almacenar en la tabla pivot.
     * Solo colaboradores con usuario registrado reciben la notificación.
     */
    public function persistirDestinatarios(NotificacionPush $notificacion): int
    {
        $notificacion = $notificacion->fresh() ?? $notificacion;
        $colaboradorIds = $this->resolverColaboradorIds($notificacion);

        if ($colaboradorIds->isEmpty()) {
            Log::warning('ResolverDestinatariosService: No hay destinatarios para persistir', [
                'notificacion_id' => $notificacion->id,
            ]);

            return 0;
        }

        $userIds = User::query()
            ->whereIn('colaborador_id', $colaboradorIds->all())
            ->whereNotNull('colaborador_id')
            ->pluck('id');

        if ($userIds->isEmpty()) {
            Log::warning('ResolverDestinatariosService: Ningún colaborador tiene usuario asociado', [
                'notificacion_id' => $notificacion->id,
                'colaborador_ids_count' => $colaboradorIds->count(),
            ]);

            return 0;
        }

        $syncData = $userIds->mapWithKeys(fn (int $id): array => [
            $id => [
                'estado_lectura' => 'NO_LEIDA',
                'leida_at' => null,
                'enviado' => false,
                'onesignal_player_id' => null,
                'enviado_at' => null,
            ],
        ])->all();

        $notificacion->usersDestinatarios()->sync($syncData);

        $total = $userIds->count();
        $notificacion->update(['total_destinatarios' => $total]);

        Log::info('ResolverDestinatariosService: Destinatarios persistidos', [
            'notificacion_id' => $notificacion->id,
            'total' => $total,
        ]);

        return $total;
    }

    /**
     * Ajusta altas/bajas en el pivote preservando filas existentes.
     *
     * BL: Convierte colaborador IDs → user IDs para el delta.
     */
    public function recalcularDestinatarios(NotificacionPush $notificacion): int
    {
        $notificacion = $notificacion->fresh() ?? $notificacion;
        $nuevosColaboradorIds = $this->resolverColaboradorIds($notificacion)->unique()->values();

        $nuevosUserIds = User::query()
            ->whereIn('colaborador_id', $nuevosColaboradorIds->all())
            ->whereNotNull('colaborador_id')
            ->pluck('id')
            ->unique()
            ->values();

        $existentesUserIds = $notificacion->destinatarios()
            ->pluck('user_id')
            ->unique()
            ->values();

        $aAgregar = $nuevosUserIds->diff($existentesUserIds);
        $aEliminar = $existentesUserIds->diff($nuevosUserIds);

        if ($aEliminar->isNotEmpty()) {
            $notificacion->destinatarios()
                ->whereIn('user_id', $aEliminar->all())
                ->delete();
        }

        if ($aAgregar->isNotEmpty()) {
            $now = now();
            $registros = $aAgregar->map(fn (int $id): array => [
                'notificacion_push_id' => $notificacion->id,
                'user_id' => $id,
                'estado_lectura' => 'NO_LEIDA',
                'leida_at' => null,
                'enviado' => false,
                'onesignal_player_id' => null,
                'enviado_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            NotificacionPushDestinatario::query()->insert($registros);
        }

        $total = $notificacion->destinatarios()->count();
        $notificacion->update(['total_destinatarios' => $total]);

        Log::info('ResolverDestinatariosService: Destinatarios recalculados', [
            'notificacion_id' => $notificacion->id,
            'agregados' => $aAgregar->count(),
            'eliminados' => $aEliminar->count(),
            'total' => $total,
        ]);

        return $total;
    }
}
