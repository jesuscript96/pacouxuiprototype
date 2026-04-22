<?php

namespace App\Filament\Resources\Felicitaciones\Pages;

use App\Filament\Resources\Felicitaciones\FelicitacionResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Página de creación de felicitación.
 *
 * Tras guardar, redirige al listado de felicitaciones.
 */
class CreateFelicitacion extends CreateRecord
{
    protected static string $resource = FelicitacionResource::class;

    /**
     * Redirige al listado tras crear exitosamente.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl(parameters: $this->getRedirectUrlParameters());
    }
}
