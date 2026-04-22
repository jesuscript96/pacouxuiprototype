<?php

namespace App\Filament\Resources\EstadoAnimoAfecciones\Pages;

use App\Filament\Resources\EstadoAnimoAfecciones\EstadoAnimoAfeccionResource;
use Filament\Resources\Pages\EditRecord;

class EditEstadoAnimoAfeccion extends EditRecord
{
    protected static string $resource = EstadoAnimoAfeccionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
