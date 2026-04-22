<?php

namespace App\Filament\Cliente\Resources\DepartamentosGenerales\Pages;

use App\Filament\Cliente\Resources\DepartamentosGenerales\DepartamentoGeneralResource;
use App\Filament\Support\CatalogAdminListRecords;
use Filament\Actions\CreateAction;

class ListDepartamentosGenerales extends CatalogAdminListRecords
{
    protected static string $resource = DepartamentoGeneralResource::class;

    protected function configureCatalogCreateAction(CreateAction $action): CreateAction
    {
        return $action;
    }
}
