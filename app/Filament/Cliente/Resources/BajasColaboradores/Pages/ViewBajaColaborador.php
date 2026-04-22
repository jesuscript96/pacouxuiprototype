<?php

namespace App\Filament\Cliente\Resources\BajasColaboradores\Pages;

use App\Filament\Cliente\Resources\BajasColaboradores\BajaColaboradorResource;
use App\Models\BajaColaborador;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewBajaColaborador extends ViewRecord
{
    protected static string $resource = BajaColaboradorResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var BajaColaborador $record */
        $record = $this->record;

        return 'Baja — '.$record->colaborador?->nombre_completo;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (): bool => $this->record instanceof BajaColaborador && $this->record->esProgramada()),
        ];
    }
}
