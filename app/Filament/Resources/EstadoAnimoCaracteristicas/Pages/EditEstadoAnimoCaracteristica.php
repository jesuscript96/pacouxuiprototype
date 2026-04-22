<?php

namespace App\Filament\Resources\EstadoAnimoCaracteristicas\Pages;

use App\Filament\Resources\EstadoAnimoCaracteristicas\EstadoAnimoCaracteristicaResource;
use Filament\Resources\Pages\EditRecord;

class EditEstadoAnimoCaracteristica extends EditRecord
{
    protected static string $resource = EstadoAnimoCaracteristicaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
