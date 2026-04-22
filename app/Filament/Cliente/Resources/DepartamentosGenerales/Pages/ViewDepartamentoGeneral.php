<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\DepartamentosGenerales\Pages;

use App\Filament\Cliente\Resources\DepartamentosGenerales\DepartamentoGeneralResource;
use App\Models\DepartamentoGeneral;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewDepartamentoGeneral extends ViewRecord
{
    protected static string $resource = DepartamentoGeneralResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var DepartamentoGeneral $record */
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
