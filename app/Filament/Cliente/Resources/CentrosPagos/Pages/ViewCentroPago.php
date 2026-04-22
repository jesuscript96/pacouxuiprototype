<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\CentrosPagos\Pages;

use App\Filament\Cliente\Resources\CentrosPagos\CentroPagoResource;
use App\Models\CentroPago;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewCentroPago extends ViewRecord
{
    protected static string $resource = CentroPagoResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var CentroPago $record */
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
