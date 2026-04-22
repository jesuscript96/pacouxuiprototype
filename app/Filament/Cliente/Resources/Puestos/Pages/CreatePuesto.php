<?php

namespace App\Filament\Cliente\Resources\Puestos\Pages;

use App\Filament\Cliente\Resources\Puestos\PuestoResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePuesto extends CreateRecord
{
    protected static string $resource = PuestoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl(parameters: $this->getRedirectUrlParameters());
    }
}
