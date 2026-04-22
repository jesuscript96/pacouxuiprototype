<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\Areas\Pages;

use App\Filament\Cliente\Resources\Areas\AreaResource;
use App\Models\Area;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewArea extends ViewRecord
{
    protected static string $resource = AreaResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var Area $record */
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
