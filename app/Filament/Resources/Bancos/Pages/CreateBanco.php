<?php

namespace App\Filament\Resources\Bancos\Pages;

use App\Filament\Resources\Bancos\BancoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBanco extends CreateRecord
{
    protected static string $resource = BancoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl(parameters: $this->getRedirectUrlParameters());
    }
}
