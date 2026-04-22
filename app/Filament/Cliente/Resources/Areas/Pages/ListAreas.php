<?php

namespace App\Filament\Cliente\Resources\Areas\Pages;

use App\Filament\Cliente\Pages\Catalogos\CatalogosPage;
use App\Filament\Cliente\Resources\Areas\AreaResource;
use App\Filament\Cliente\Resources\Areas\Tables\AreasTable;
use App\Filament\Support\CatalogAdminListRecords;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;

class ListAreas extends CatalogAdminListRecords
{
    protected static string $resource = AreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->makeCatalogCreateAction(),
            AreasTable::accionExportarExcel(),
            Action::make('areas_generales')
                ->label('Áreas generales')
                ->icon('heroicon-o-rectangle-stack')
                ->color('gray')
                ->url(CatalogosPage::getUrl().'?tab=areas_generales'),
        ];
    }

    protected function configureCatalogCreateAction(CreateAction $action): CreateAction
    {
        return $action;
    }
}
