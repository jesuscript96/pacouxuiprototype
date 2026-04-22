<?php

namespace App\Filament\Resources\NotificacionesIncluidas\Pages;

use App\Filament\Resources\NotificacionesIncluidas\NotificacionesIncluidasResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditNotificacionesIncluidas extends EditRecord
{
    protected static string $resource = NotificacionesIncluidasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
