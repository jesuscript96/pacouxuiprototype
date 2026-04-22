<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\Regiones\Pages;

use App\Filament\Cliente\Resources\Regiones\RegionResource;
use App\Models\Region;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewRegion extends ViewRecord
{
    protected static string $resource = RegionResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var Region $record */
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
