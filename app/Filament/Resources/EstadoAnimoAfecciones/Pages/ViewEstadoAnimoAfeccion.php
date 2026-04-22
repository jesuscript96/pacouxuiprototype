<?php

declare(strict_types=1);

namespace App\Filament\Resources\EstadoAnimoAfecciones\Pages;

use App\Filament\Resources\EstadoAnimoAfecciones\EstadoAnimoAfeccionResource;
use App\Models\EstadoAnimoAfeccion;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewEstadoAnimoAfeccion extends ViewRecord
{
    protected static string $resource = EstadoAnimoAfeccionResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var EstadoAnimoAfeccion $record */
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
