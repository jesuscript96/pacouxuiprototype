<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\Roles\Pages;

use App\Filament\Cliente\Resources\Roles\Concerns\SyncsGroupedRolePermissions;
use App\Filament\Cliente\Resources\Roles\RolResource;
use App\Filament\Cliente\Resources\Roles\Schemas\RolForm;
use App\Models\SpatieRole;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditRol extends EditRecord
{
    use SyncsGroupedRolePermissions;

    protected static string $resource = RolResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var SpatieRole $record */
        $record = $this->record;

        return array_merge($data, RolForm::hydrateGroupedPermissionFields($record));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->stripGroupedPermissionFields($data);
    }

    protected function afterSave(): void
    {
        /** @var SpatieRole $record */
        $record = $this->record;
        $this->syncPermissionsToRecord($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (SpatieRole $record, DeleteAction $action): void {
                    if ($record->users()->exists()) {
                        Notification::make()
                            ->title('No se puede eliminar el rol')
                            ->body('Tiene usuarios asignados.')
                            ->danger()
                            ->send();
                        $action->halt();
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
