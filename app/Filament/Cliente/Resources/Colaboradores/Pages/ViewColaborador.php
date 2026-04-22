<?php

namespace App\Filament\Cliente\Resources\Colaboradores\Pages;

use App\Filament\Cliente\Resources\Colaboradores\ColaboradorResource;
use App\Models\Colaborador;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewColaborador extends ViewRecord
{
    protected static string $resource = ColaboradorResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var Colaborador $record */
        $record = $this->record;

        return $record->nombre_completo;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
