<?php

namespace App\Filament\Cliente\Resources\Departamentos\Pages;

use App\Filament\Cliente\Pages\Catalogos\CatalogosPage;
use App\Filament\Cliente\Resources\Departamentos\DepartamentoResource;
use App\Filament\Cliente\Resources\Departamentos\Tables\DepartamentosTable;
use App\Filament\Support\CatalogAdminListRecords;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;

class ListDepartamentos extends CatalogAdminListRecords
{
    protected static string $resource = DepartamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->makeCatalogCreateAction(),
            DepartamentosTable::accionExportarExcel(),
            Action::make('departamentos_generales')
                ->label('Tipos generales')
                ->icon('heroicon-o-rectangle-stack')
                ->color('gray')
                ->url(CatalogosPage::getUrl().'?tab=departamentos_generales'),
        ];
    }

    protected function configureCatalogCreateAction(CreateAction $action): CreateAction
    {
        return $action;
    }
}
