<?php

namespace App\Filament\Cliente\Resources\Departamentos\Pages;

use App\Filament\Cliente\Resources\Departamentos\DepartamentoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartamento extends CreateRecord
{
    protected static string $resource = DepartamentoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl(parameters: $this->getRedirectUrlParameters());
    }
}
