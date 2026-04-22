<?php

namespace App\Filament\Cliente\Resources\CentrosPagos\Pages;

use App\Filament\Cliente\Resources\CentrosPagos\CentroPagoResource;
use App\Filament\Support\CatalogAdminListRecords;
use Filament\Actions\CreateAction;

class ListCentrosPagos extends CatalogAdminListRecords
{
    protected static string $resource = CentroPagoResource::class;

    protected function configureCatalogCreateAction(CreateAction $action): CreateAction
    {
        return $action;
    }
}
