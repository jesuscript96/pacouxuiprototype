<?php

namespace App\Filament\Cliente\Resources\Puestos\Pages;

use App\Filament\Cliente\Pages\Catalogos\CatalogosPage;
use App\Filament\Cliente\Resources\Puestos\PuestoResource;
use App\Filament\Cliente\Resources\Puestos\Tables\PuestosTable;
use App\Filament\Support\CatalogAdminListRecords;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;

class ListPuestos extends CatalogAdminListRecords
{
    protected static string $resource = PuestoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->makeCatalogCreateAction(),
            PuestosTable::accionExportarExcel(),
            Action::make('puestos_generales')
                ->label('Puestos generales')
                ->icon('heroicon-o-rectangle-stack')
                ->color('gray')
                ->url(CatalogosPage::getUrl().'?tab=puestos_generales'),
        ];
    }

    protected function configureCatalogCreateAction(CreateAction $action): CreateAction
    {
        return $action;
    }
}
