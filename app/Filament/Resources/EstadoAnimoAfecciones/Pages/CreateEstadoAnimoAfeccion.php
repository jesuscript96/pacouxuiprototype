<?php

namespace App\Filament\Resources\EstadoAnimoAfecciones\Pages;

use App\Filament\Resources\EstadoAnimoAfecciones\EstadoAnimoAfeccionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEstadoAnimoAfeccion extends CreateRecord
{
    protected static string $resource = EstadoAnimoAfeccionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl(parameters: $this->getRedirectUrlParameters());
    }
}
