<?php

namespace App\Filament\Resources\NotificacionesIncluidas\Pages;

use App\Filament\Resources\NotificacionesIncluidas\NotificacionesIncluidasResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNotificacionesIncluidas extends CreateRecord
{
    protected static string $resource = NotificacionesIncluidasResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl(parameters: $this->getRedirectUrlParameters());
    }
}
