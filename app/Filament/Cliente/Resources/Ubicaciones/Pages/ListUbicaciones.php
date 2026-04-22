<?php

namespace App\Filament\Cliente\Resources\Ubicaciones\Pages;

use App\Filament\Cliente\Resources\Ubicaciones\Tables\UbicacionesTable;
use App\Filament\Cliente\Resources\Ubicaciones\UbicacionResource;
use App\Filament\Support\CatalogAdminListRecords;
use Filament\Actions\CreateAction;

class ListUbicaciones extends CatalogAdminListRecords
{
    protected static string $resource = UbicacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->makeCatalogCreateAction(),
            UbicacionesTable::accionExportarExcel(),
        ];
    }

    protected function configureCatalogCreateAction(CreateAction $action): CreateAction
    {
        return $action;
    }
}
