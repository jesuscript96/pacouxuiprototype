<?php

namespace App\Filament\Resources\Reconocimientos\Pages;

use App\Filament\Resources\Reconocimientos\ReconocimientosResource;
use App\Filament\Support\ReconocimientoFormActions;
use App\Models\Reconocmiento;
use Filament\Resources\Pages\CreateRecord;

/**
 * Página de creación de reconocimiento.
 *
 * Tras guardar el registro, sincroniza el pivot con las empresas
 * asignadas. Si no es exclusivo, se asigna a TODAS las empresas.
 */
class CreateReconocimientos extends CreateRecord
{
    protected static string $resource = ReconocimientosResource::class;

    /**
     * Redirige al listado tras crear exitosamente.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl(parameters: $this->getRedirectUrlParameters());
    }

    /**
     * Post-creación: sincroniza el pivot empresas_reconocimientos.
     * Las imágenes ya fueron guardadas en Wasabi/S3 por el FileUpload
     * y sus rutas persistidas en la BD automáticamente.
     */
    protected function afterCreate(): void
    {
        /** @var Reconocmiento $record */
        $record = $this->record;

        ReconocimientoFormActions::syncEmpresasPivot($record, $this->data);
    }
}
