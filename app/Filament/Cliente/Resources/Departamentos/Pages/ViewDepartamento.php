<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\Departamentos\Pages;

use App\Filament\Cliente\Resources\Departamentos\DepartamentoResource;
use App\Models\Departamento;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewDepartamento extends ViewRecord
{
    protected static string $resource = DepartamentoResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var Departamento $record */
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
