<?php

namespace App\Filament\Cliente\Resources\Regiones\Pages;

use App\Filament\Cliente\Resources\Regiones\RegionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRegion extends CreateRecord
{
    protected static string $resource = RegionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl(parameters: $this->getRedirectUrlParameters());
    }
}
