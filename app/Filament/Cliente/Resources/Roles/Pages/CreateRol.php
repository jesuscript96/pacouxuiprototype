<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\Roles\Pages;

use App\Filament\Cliente\Resources\Roles\Concerns\SyncsGroupedRolePermissions;
use App\Filament\Cliente\Resources\Roles\RolResource;
use App\Models\SpatieRole;
use Filament\Resources\Pages\CreateRecord;

class CreateRol extends CreateRecord
{
    use SyncsGroupedRolePermissions;

    protected static string $resource = RolResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->stripGroupedPermissionFields($data);
    }

    protected function afterCreate(): void
    {
        /** @var SpatieRole $record */
        $record = $this->record;
        $this->syncPermissionsToRecord($record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
