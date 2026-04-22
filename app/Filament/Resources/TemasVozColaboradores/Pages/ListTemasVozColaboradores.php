<?php

namespace App\Filament\Resources\TemasVozColaboradores\Pages;

use App\Filament\Resources\TemasVozColaboradores\TemasVozColaboradoresResource;
use Filament\Resources\Pages\ListRecords;

class ListTemasVozColaboradores extends ListRecords
{
    protected static string $resource = TemasVozColaboradoresResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
