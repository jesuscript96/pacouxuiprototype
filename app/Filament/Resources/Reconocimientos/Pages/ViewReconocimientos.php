<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reconocimientos\Pages;

use App\Filament\Resources\Reconocimientos\ReconocimientosResource;
use App\Models\Reconocmiento;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Página de vista de reconocimiento.
 *
 * Muestra el detalle del reconocimiento en modo de solo lectura
 * con opción de ir a editar desde el header.
 */
class ViewReconocimientos extends ViewRecord
{
    protected static string $resource = ReconocimientosResource::class;

    /**
     * Título de la página: muestra el nombre del reconocimiento.
     */
    public function getTitle(): string|Htmlable
    {
        /** @var Reconocmiento $record */
        $record = $this->record;

        return $record->nombre;
    }

    /**
     * Acciones del header: botón de editar.
     *
     * @return array<\Filament\Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
