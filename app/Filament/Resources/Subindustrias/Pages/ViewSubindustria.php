<?php

declare(strict_types=1);

namespace App\Filament\Resources\Subindustrias\Pages;

use App\Filament\Resources\Subindustrias\SubindustriaResource;
use App\Models\Subindustria;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewSubindustria extends ViewRecord
{
    protected static string $resource = SubindustriaResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var Subindustria $record */
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
