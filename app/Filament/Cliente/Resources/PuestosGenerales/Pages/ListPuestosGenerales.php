<?php

namespace App\Filament\Cliente\Resources\PuestosGenerales\Pages;

use App\Filament\Cliente\Resources\PuestosGenerales\PuestoGeneralResource;
use App\Filament\Support\CatalogAdminListRecords;
use Filament\Actions\CreateAction;

class ListPuestosGenerales extends CatalogAdminListRecords
{
    protected static string $resource = PuestoGeneralResource::class;

    protected function configureCatalogCreateAction(CreateAction $action): CreateAction
    {
        return $action;
    }
}
