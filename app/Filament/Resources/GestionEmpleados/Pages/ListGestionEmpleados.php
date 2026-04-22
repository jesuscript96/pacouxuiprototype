<?php

declare(strict_types=1);

namespace App\Filament\Resources\GestionEmpleados\Pages;

use App\Filament\Resources\GestionEmpleados\GestionEmpleadosResource;
use Filament\Resources\Pages\ListRecords;

class ListGestionEmpleados extends ListRecords
{
    protected static string $resource = GestionEmpleadosResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
