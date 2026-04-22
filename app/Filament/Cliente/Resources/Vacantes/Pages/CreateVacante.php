<?php

namespace App\Filament\Cliente\Resources\Vacantes\Pages;

use App\Filament\Cliente\Resources\Vacantes\VacanteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVacante extends CreateRecord
{
    protected static string $resource = VacanteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['creado_por'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl(parameters: $this->getRedirectUrlParameters());
    }
}
