<?php

declare(strict_types=1);

namespace App\Filament\Resources\CentroCostos\Pages;

use App\Filament\Resources\CentroCostos\CentroCostoResource;
use App\Models\CentroCosto;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewCentroCosto extends ViewRecord
{
    protected static string $resource = CentroCostoResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var CentroCosto $record */
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
