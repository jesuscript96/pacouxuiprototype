<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\CategoriasSolicitud\Pages;

use App\Filament\Cliente\Resources\CategoriasSolicitud\CategoriaSolicitudResource;
use App\Filament\Support\CatalogAdminListRecords;
use Filament\Actions\CreateAction;

class ListCategoriasSolicitud extends CatalogAdminListRecords
{
    protected static string $resource = CategoriaSolicitudResource::class;

    protected function configureCatalogCreateAction(CreateAction $action): CreateAction
    {
        return $action;
    }
}
