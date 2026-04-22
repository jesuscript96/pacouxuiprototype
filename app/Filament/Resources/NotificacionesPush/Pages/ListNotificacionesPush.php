<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificacionesPush\Pages;

use App\Filament\Resources\NotificacionesPush\NotificacionPushResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNotificacionesPush extends ListRecords
{
    protected static string $resource = NotificacionPushResource::class;

    /**
     * BL: Formulario extenso (envío, filtros, destinatarios); se mantiene en página dedicada.
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
