<?php

declare(strict_types=1);

namespace App\Filament\Resources\Industrias\Pages;

use App\Filament\Resources\Industrias\IndustriaResource;
use App\Models\Industria;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewIndustria extends ViewRecord
{
    protected static string $resource = IndustriaResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var Industria $record */
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
