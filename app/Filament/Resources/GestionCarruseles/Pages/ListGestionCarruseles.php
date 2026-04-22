<?php

namespace App\Filament\Resources\GestionCarruseles\Pages;

use App\Filament\Resources\GestionCarruseles\GestionCarruselesResource;
use Filament\Resources\Pages\ListRecords;

class ListGestionCarruseles extends ListRecords
{
    protected static string $resource = GestionCarruselesResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
