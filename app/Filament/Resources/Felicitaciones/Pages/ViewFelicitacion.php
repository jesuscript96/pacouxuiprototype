<?php

declare(strict_types=1);

namespace App\Filament\Resources\Felicitaciones\Pages;

use App\Filament\Resources\Felicitaciones\FelicitacionResource;
use App\Models\Felicitacion;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Página de vista de felicitación.
 *
 * Muestra el detalle de la felicitación en modo de solo lectura
 * con opción de ir a editar desde el header.
 */
class ViewFelicitacion extends ViewRecord
{
    protected static string $resource = FelicitacionResource::class;

    /**
     * Título de la página: muestra el título de la felicitación.
     */
    public function getTitle(): string|Htmlable
    {
        /** @var Felicitacion $record */
        $record = $this->record;

        return $record->titulo;
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
