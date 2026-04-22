<?php

namespace App\Filament\Resources\Subindustrias\Pages;

use App\Filament\Resources\Subindustrias\SubindustriaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubindustria extends CreateRecord
{
    protected static string $resource = SubindustriaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl(parameters: $this->getRedirectUrlParameters());
    }
}
