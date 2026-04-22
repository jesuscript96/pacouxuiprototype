<?php

namespace App\Filament\Resources\NotificacionesIncluidas\Pages;

use App\Filament\Resources\NotificacionesIncluidas\NotificacionesIncluidasResource;
use App\Filament\Support\CatalogAdminListRecords;

class ListNotificacionesIncluidas extends CatalogAdminListRecords
{
    protected static string $resource = NotificacionesIncluidasResource::class;
}
