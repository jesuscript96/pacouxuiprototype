<?php

namespace App\Filament\Cliente\Resources\Vacantes\Pages;

use App\Filament\Cliente\Resources\Vacantes\VacanteResource;
use App\Models\Vacante;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewVacante extends ViewRecord
{
    protected static string $resource = VacanteResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var Vacante $record */
        $record = $this->record;

        return $record->puesto;
    }

    protected function getHeaderActions(): array
    {
        /** @var Vacante $record */
        $record = $this->record;

        return [
            Action::make('copiarUrl')
                ->label('Copiar URL pública')
                ->icon('heroicon-o-link')
                ->color('gray')
                ->alpineClickHandler("navigator.clipboard.writeText('".$record->urlPublica()."').then(() => { new FilamentNotification().title('URL copiada al portapapeles').body('".$record->urlPublica()."').success().send() })"),

            EditAction::make(),
        ];
    }
}
