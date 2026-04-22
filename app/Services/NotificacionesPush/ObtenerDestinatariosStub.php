<?php

declare(strict_types=1);

namespace App\Services\NotificacionesPush;

use App\Contracts\ObtenerDestinatariosPushInterface;
use App\Models\NotificacionPush;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Implementación temporal que simula obtener tokens de dispositivo.
 *
 * TODO: Reemplazar por implementación real cuando exista la tabla de tokens (app RN + Expo).
 */
class ObtenerDestinatariosStub implements ObtenerDestinatariosPushInterface
{
    /**
     * @return Collection<int, string>
     */
    public function obtenerTokens(NotificacionPush $notificacion): Collection
    {
        Log::info('ObtenerDestinatariosStub: Generando tokens simulados', [
            'notificacion_id' => $notificacion->id,
            'empresa_id' => $notificacion->empresa_id,
            'filtros' => $notificacion->filtros,
        ]);

        $cantidad = $this->contarDestinatarios($notificacion);

        return collect(range(1, $cantidad))
            ->map(fn (int $i): string => "simulated-token-{$notificacion->id}-{$i}");
    }

    public function contarDestinatarios(NotificacionPush $notificacion): int
    {
        $filtros = $notificacion->getFiltrosConDefaults();
        $tieneAlgunFiltro = collect($filtros)
            ->filter(fn (mixed $v): bool => ! empty($v))
            ->isNotEmpty();

        return $tieneAlgunFiltro ? 50 : 150;
    }

    /**
     * @return Collection<int, \App\Models\Colaborador>
     */
    public function obtenerColaboradoresFiltrados(NotificacionPush $notificacion): Collection
    {
        Log::info('ObtenerDestinatariosStub: obtenerColaboradoresFiltrados llamado (stub)');

        return collect();
    }

    public function obtenerColaboradoresPaginados(
        NotificacionPush $notificacion,
        int $perPage = 50,
        ?string $busqueda = null
    ): LengthAwarePaginator {
        Log::info('ObtenerDestinatariosStub: obtenerColaboradoresPaginados llamado (stub)');

        return new Paginator([], 0, $perPage, 1);
    }
}
