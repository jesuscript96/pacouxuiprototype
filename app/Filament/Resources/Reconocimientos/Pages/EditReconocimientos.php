<?php

namespace App\Filament\Resources\Reconocimientos\Pages;

use App\Filament\Resources\Reconocimientos\ReconocimientosResource;
use App\Filament\Support\ReconocimientoFormActions;
use App\Models\Reconocmiento;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

/**
 * Página de edición de reconocimiento.
 *
 * Al cargar, llena el selector de empresas desde el pivot.
 * Al guardar, sincroniza el pivot y las imágenes se manejan
 * automáticamente por FileUpload + ArchivoService.
 */
class EditReconocimientos extends EditRecord
{
    protected static string $resource = ReconocimientosResource::class;

    /**
     * Acciones del header: eliminar, forzar eliminación, restaurar.
     *
     * @return array<\Filament\Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * Pre-llenado del formulario al editar.
     *
     * Carga las empresas asignadas desde el pivot para poblar el
     * selector múltiple. Las imágenes se cargan automáticamente
     * desde las columnas imagen_inicial/imagen_final en la BD.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function mutateFormDataBeforeFill(array $data): array
    {
        return ReconocimientoFormActions::mutateFillWithEmpresas($this->record, $data);
    }

    /**
     * Post-guardado: sincroniza el pivot empresas_reconocimientos.
     * Las imágenes ya fueron guardadas en Wasabi/S3 por el FileUpload
     * y sus rutas actualizadas en la BD automáticamente.
     */
    protected function afterSave(): void
    {
        /** @var Reconocmiento $record */
        $record = $this->record;
        $data = $this->form->getState();

        ReconocimientoFormActions::syncEmpresasPivot($record, $data);
    }

    /**
     * Redirige al listado tras editar exitosamente.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
