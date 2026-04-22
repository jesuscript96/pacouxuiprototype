<?php

namespace App\Filament\Cliente\Resources\DepartamentosGenerales\Pages;

use App\Filament\Cliente\Resources\DepartamentosGenerales\DepartamentoGeneralResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartamentoGeneral extends CreateRecord
{
    protected static string $resource = DepartamentoGeneralResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl(parameters: $this->getRedirectUrlParameters());
    }
}
