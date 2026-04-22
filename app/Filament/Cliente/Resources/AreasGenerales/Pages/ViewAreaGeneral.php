<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\AreasGenerales\Pages;

use App\Filament\Cliente\Resources\AreasGenerales\AreaGeneralResource;
use App\Models\AreaGeneral;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewAreaGeneral extends ViewRecord
{
    protected static string $resource = AreaGeneralResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var AreaGeneral $record */
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
