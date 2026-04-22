<?php

declare(strict_types=1);

namespace App\Filament\Resources\EstadoAnimoCaracteristicas\Pages;

use App\Filament\Resources\EstadoAnimoCaracteristicas\EstadoAnimoCaracteristicaResource;
use App\Models\EstadoAnimoCaracteristica;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewEstadoAnimoCaracteristica extends ViewRecord
{
    protected static string $resource = EstadoAnimoCaracteristicaResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var EstadoAnimoCaracteristica $record */
        $record = $this->record;

        return $record->nombre;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
