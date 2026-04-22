<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\CargarDocumentos\Pages;

use App\Filament\Cliente\Resources\CargarDocumentos\CargarDocumentosResource;
use App\Filament\Cliente\Resources\CargarDocumentos\Schemas\CarpetaDocumentosWizard;
use App\Models\Empresa;
use App\Services\DocumentosCorporativosCarpetaService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateCargarDocumentos extends CreateRecord
{
    protected static string $resource = CargarDocumentosResource::class;

    protected static bool $canCreateAnother = false;

    public string $stagingId = '';

    public function mount(): void
    {
        $this->stagingId = (string) Str::uuid();
        parent::mount();
    }

    public function getTitle(): string
    {
        return 'Nueva carpeta de documentos corporativos';
    }

    public function form(Schema $schema): Schema
    {
        $tenant = Filament::getTenant();
        abort_unless($tenant instanceof Empresa, 403);

        return CarpetaDocumentosWizard::configure($schema, $tenant, $this->stagingId, null);
    }

    /**
     * BL: Captura errores de validación y de servicio externo (Wasabi/S3)
     * para mostrarlos como notificación visible al usuario.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $tenant = Filament::getTenant();
        abort_unless($tenant instanceof Empresa, 403);

        $data['staging_id'] = $this->stagingId;

        try {
            return app(DocumentosCorporativosCarpetaService::class)->crearDesdeWizard(
                $tenant,
                auth()->user(),
                $data
            );
        } catch (ValidationException $e) {
            Notification::make()
                ->title('Error de validación')
                ->body(collect($e->errors())->flatten()->implode(' '))
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error al crear la carpeta')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }
    }

    /**
     * BL: Solo se muestra Cancelar (el botón Crear vive dentro del Wizard, paso 2).
     */
    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return CargarDocumentosResource::getUrl('index', ['tenant' => Filament::getTenant()]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Carpeta creada correctamente.';
    }
}
