<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\NotificacionPush;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ObtenerDestinatariosPushInterface
{
    /**
     * @return Collection<int, string>
     */
    public function obtenerTokens(NotificacionPush $notificacion): Collection;

    public function contarDestinatarios(NotificacionPush $notificacion): int;

    /**
     * @return Collection<int, \App\Models\Colaborador>
     */
    public function obtenerColaboradoresFiltrados(NotificacionPush $notificacion): Collection;

    public function obtenerColaboradoresPaginados(
        NotificacionPush $notificacion,
        int $perPage = 50,
        ?string $busqueda = null
    ): LengthAwarePaginator;
}
