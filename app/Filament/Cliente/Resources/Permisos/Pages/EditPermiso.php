<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\Permisos\Pages;

use App\Filament\Cliente\Resources\Permisos\PermisoResource;
use App\Filament\Cliente\Resources\Permisos\Schemas\PermisoForm;
use App\Filament\Cliente\Resources\Permisos\Support\PermisoFormHydrator;
use App\Models\TipoSolicitud;
use App\Services\TipoSolicitudPersistService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

/**
 * Edición de permiso ({@see \App\Models\TipoSolicitud}).
 */
class EditPermiso extends EditRecord
{
    protected static string $resource = PermisoResource::class;

    public function form(Schema $schema): Schema
    {
        /** @var TipoSolicitud $record */
        $record = $this->record;

        return PermisoForm::configure($schema, (int) $record->id);
    }

    protected function getRedirectUrl(): string
    {
        return PermisoResource::getUrl('index');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var TipoSolicitud $record */
        $record = $this->getRecord();

        return PermisoFormHydrator::mergeRelaciones($record, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $tenant = Filament::getTenant();
        abort_if($tenant === null, 403);
        abort_unless($record instanceof TipoSolicitud, 404);

        return app(TipoSolicitudPersistService::class)->actualizar($record, $data, (int) $tenant->id);
    }

    protected function afterSave(): void
    {
        $this->record->refresh();
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Permiso actualizado correctamente.';
    }

    /**
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
}
