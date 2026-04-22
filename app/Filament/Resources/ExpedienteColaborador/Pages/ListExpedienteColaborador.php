<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExpedienteColaborador\Pages;

use App\Filament\Resources\ExpedienteColaborador\ExpedienteColaboradorResource;
use Filament\Resources\Pages\ListRecords;

class ListExpedienteColaborador extends ListRecords
{
    protected static string $resource = ExpedienteColaboradorResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
