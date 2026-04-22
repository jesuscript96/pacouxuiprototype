<?php

namespace App\Filament\Resources\Felicitaciones\Pages;

use App\Filament\Resources\Felicitaciones\FelicitacionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

/**
 * Página de edición de felicitación.
 *
 * Incluye acción de eliminar en el header y redirige
 * al listado tras guardar cambios.
 */
class EditFelicitacion extends EditRecord
{
    protected static string $resource = FelicitacionResource::class;

    /**
     * Acciones del header: botón de eliminar.
     *
     * @return array<\Filament\Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Redirige al listado tras editar exitosamente.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
