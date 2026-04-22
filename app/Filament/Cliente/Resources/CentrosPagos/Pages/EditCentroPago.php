<?php

namespace App\Filament\Cliente\Resources\CentrosPagos\Pages;

use App\Filament\Cliente\Resources\CentrosPagos\CentroPagoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCentroPago extends EditRecord
{
    protected static string $resource = CentroPagoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
