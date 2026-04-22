<?php

namespace App\Filament\Cliente\Resources\PuestosGenerales\Pages;

use App\Filament\Cliente\Resources\PuestosGenerales\PuestoGeneralResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePuestoGeneral extends CreateRecord
{
    protected static string $resource = PuestoGeneralResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl(parameters: $this->getRedirectUrlParameters());
    }
}
