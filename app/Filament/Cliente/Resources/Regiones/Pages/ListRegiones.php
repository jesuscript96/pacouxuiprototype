<?php

namespace App\Filament\Cliente\Resources\Regiones\Pages;

use App\Filament\Cliente\Resources\Regiones\RegionResource;
use App\Filament\Cliente\Resources\Regiones\Tables\RegionesTable;
use App\Filament\Support\CatalogAdminListRecords;
use Filament\Actions\CreateAction;

class ListRegiones extends CatalogAdminListRecords
{
    protected static string $resource = RegionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->makeCatalogCreateAction(),
            RegionesTable::accionExportarExcel(),
        ];
    }

    protected function configureCatalogCreateAction(CreateAction $action): CreateAction
    {
        return $action;
    }
}
