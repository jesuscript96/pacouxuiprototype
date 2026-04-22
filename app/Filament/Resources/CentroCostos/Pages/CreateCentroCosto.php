<?php

namespace App\Filament\Resources\CentroCostos\Pages;

use App\Filament\Resources\CentroCostos\CentroCostoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCentroCosto extends CreateRecord
{
    protected static string $resource = CentroCostoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl(parameters: $this->getRedirectUrlParameters());
    }
}
