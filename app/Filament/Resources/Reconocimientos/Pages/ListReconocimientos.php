<?php

namespace App\Filament\Resources\Reconocimientos\Pages;

use App\Filament\Resources\Reconocimientos\ReconocimientosResource;
use App\Filament\Support\CatalogAdminListRecords;
use App\Filament\Support\ReconocimientoFormActions;
use App\Models\Reconocmiento;
use Filament\Actions\CreateAction;

class ListReconocimientos extends CatalogAdminListRecords
{
    protected static string $resource = ReconocimientosResource::class;

    protected function configureCatalogCreateAction(CreateAction $action): CreateAction
    {
        return $action->after(function (CreateAction $mounted): void {
            $record = $mounted->getRecord();
            if (! $record instanceof Reconocmiento) {
                return;
            }
            ReconocimientoFormActions::syncEmpresasPivot($record, $mounted->getData());
        });
    }
}
