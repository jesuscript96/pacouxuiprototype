<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\Permisos\Pages;

use App\Filament\Cliente\Resources\Permisos\PermisoResource;
use App\Filament\Support\CatalogAdminListRecords;
use Filament\Actions\CreateAction;

/**
 * Listado de permisos ({@see \App\Models\TipoSolicitud}) en el panel Cliente.
 */
class ListPermisos extends CatalogAdminListRecords
{
    protected static string $resource = PermisoResource::class;

    protected function configureCatalogCreateAction(CreateAction $action): CreateAction
    {
        return $action;
    }
}
