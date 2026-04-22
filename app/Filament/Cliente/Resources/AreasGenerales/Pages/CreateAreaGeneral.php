<?php

namespace App\Filament\Cliente\Resources\AreasGenerales\Pages;

use App\Filament\Cliente\Resources\AreasGenerales\AreaGeneralResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAreaGeneral extends CreateRecord
{
    protected static string $resource = AreaGeneralResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl(parameters: $this->getRedirectUrlParameters());
    }
}
