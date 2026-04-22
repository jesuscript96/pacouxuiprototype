<?php

namespace App\Filament\Resources\EstadoAnimoCaracteristicas\Pages;

use App\Filament\Resources\EstadoAnimoCaracteristicas\EstadoAnimoCaracteristicaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEstadoAnimoCaracteristica extends CreateRecord
{
    protected static string $resource = EstadoAnimoCaracteristicaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl(parameters: $this->getRedirectUrlParameters());
    }
}
