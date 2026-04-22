<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\Puestos\Pages;

use App\Filament\Cliente\Resources\Puestos\PuestoResource;
use App\Models\Puesto;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewPuesto extends ViewRecord
{
    protected static string $resource = PuestoResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var Puesto $record */
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
