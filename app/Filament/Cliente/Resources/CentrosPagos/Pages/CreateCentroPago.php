<?php

namespace App\Filament\Cliente\Resources\CentrosPagos\Pages;

use App\Filament\Cliente\Resources\CentrosPagos\CentroPagoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCentroPago extends CreateRecord
{
    protected static string $resource = CentroPagoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl(parameters: $this->getRedirectUrlParameters());
    }
}
