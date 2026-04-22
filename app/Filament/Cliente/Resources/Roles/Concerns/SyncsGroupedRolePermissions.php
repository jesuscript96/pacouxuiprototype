<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\Roles\Concerns;

use App\Models\SpatieRole;
use Spatie\Permission\Models\Permission;

trait SyncsGroupedRolePermissions
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function stripGroupedPermissionFields(array $data): array
    {
        foreach (array_keys($data) as $key) {
            if (is_string($key) && str_starts_with($key, 'permisos_')) {
                unset($data[$key]);
            }
        }
        unset($data['permissions']);

        return $data;
    }

    /**
     * @return list<int>
     */
    protected function groupedPermissionIdsFromFormState(): array
    {
        $state = $this->form->getState();
        $ids = [];
        foreach ($state as $key => $value) {
            if (is_string($key) && str_starts_with($key, 'permisos_') && is_array($value)) {
                foreach ($value as $v) {
                    $ids[] = (int) $v;
                }
            }
        }

        return array_values(array_unique(array_filter($ids)));
    }

    protected function syncPermissionsToRecord(SpatieRole $record): void
    {
        $ids = $this->groupedPermissionIdsFromFormState();
        $permissions = $ids === []
            ? collect()
            : Permission::query()->whereIn('id', $ids)->get();

        $record->syncPermissions($permissions);
    }
}
