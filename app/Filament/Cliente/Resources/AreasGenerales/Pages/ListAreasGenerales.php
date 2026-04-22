<?php

namespace App\Filament\Cliente\Resources\AreasGenerales\Pages;

use App\Filament\Cliente\Resources\AreasGenerales\AreaGeneralResource;
use App\Filament\Support\CatalogAdminListRecords;
use Filament\Actions\CreateAction;

class ListAreasGenerales extends CatalogAdminListRecords
{
    protected static string $resource = AreaGeneralResource::class;

    protected function configureCatalogCreateAction(CreateAction $action): CreateAction
    {
        return $action;
    }
}
