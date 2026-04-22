<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\PuestosGenerales\Pages;

use App\Filament\Cliente\Resources\PuestosGenerales\PuestoGeneralResource;
use App\Models\PuestoGeneral;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewPuestoGeneral extends ViewRecord
{
    protected static string $resource = PuestoGeneralResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var PuestoGeneral $record */
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
