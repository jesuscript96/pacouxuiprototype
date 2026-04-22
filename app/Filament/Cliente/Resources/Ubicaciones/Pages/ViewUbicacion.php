<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\Ubicaciones\Pages;

use App\Filament\Cliente\Resources\Ubicaciones\UbicacionResource;
use App\Models\Ubicacion;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewUbicacion extends ViewRecord
{
    protected static string $resource = UbicacionResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var Ubicacion $record */
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
