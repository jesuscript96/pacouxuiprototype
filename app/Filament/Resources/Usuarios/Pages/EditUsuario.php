<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Pages;

use App\Filament\Resources\Usuarios\UsuarioResource;
use App\Services\UsuarioService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUsuario extends EditRecord
{
    protected static string $resource = UsuarioResource::class;

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Usuario actualizado correctamente';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return UsuarioResource::mutateRecordDataBeforeFillForModal($this->record, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return UsuarioResource::mutateFormDataBeforeSaveForModal($data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UsuarioService::class)->update($record, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
