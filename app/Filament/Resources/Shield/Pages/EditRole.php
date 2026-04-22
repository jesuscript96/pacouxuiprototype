<?php

declare(strict_types=1);

namespace App\Filament\Resources\Shield\Pages;

use App\Filament\Resources\Shield\RoleResource;
use App\Services\RolService;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditRole extends \BezhanSalleh\FilamentShield\Resources\Roles\Pages\EditRole
{
    protected static string $resource = RoleResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);
        $data['is_asignable'] = filled($data['company_id'] ?? null);
        $data['role_name_edit'] = RoleResource::roleNameInputForFill($data);
        $cid = RoleResource::companyIdToInt($data['company_id'] ?? null);
        if ($cid !== null) {
            $data['display_name'] = RoleResource::displayNameSuffixForEdit(
                $cid,
                (string) ($data['display_name'] ?? '')
            );
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $permissions = collect($this->permissions ?? [])
            ->map(fn (mixed $p): string => is_string($p) ? $p : (string) ($p->name ?? $p))
            ->values()
            ->all();

        return app(RolService::class)->update($record, $data, $permissions);
    }

    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();
        $isSuperAdmin = $user && $user->hasRole(Utils::getSuperAdminName());

        if (! array_key_exists('is_asignable', $data) && filled($data['company_id'] ?? null)) {
            $data['is_asignable'] = true;
        }

        $data = RoleResource::mergeRoleNameFromFormData($data);
        $data = RoleResource::mergeDisplayNameForStorage($data);

        $nonPermissionKeys = [
            'name',
            'guard_name',
            'select_all',
            'display_name',
            'description',
            'company_id',
            'is_asignable',
            'role_name_edit',
            Utils::getTenantModelForeignKey(),
        ];

        $isAsignable = (bool) ($data['is_asignable'] ?? false);

        $fromForm = collect($data)
            ->filter(function (mixed $permission, string $key) use ($nonPermissionKeys, $isAsignable): bool {
                if (in_array($key, $nonPermissionKeys)) {
                    return false;
                }
                $isClienteKey = str_contains($key, 'Filament\\Cliente\\');

                return $isAsignable ? $isClienteKey : ! $isClienteKey;
            })
            ->values()
            ->flatten()
            ->unique();

        // BL: Al guardar no sobrescribir los permisos del otro panel. Con "Asignar a empresa" ON
        // solo vienen en el form los de recursos Cliente; los custom (Upload, Import, etc.) se
        // pierden. Con toggle OFF solo vienen Admin; los de recursos Cliente se pierden.
        $existingPermissionNames = $this->record->permissions->pluck('name');
        if ($isAsignable) {
            $customNames = array_keys(config('filament-shield.custom_permissions', []));
            $toKeep = $existingPermissionNames->intersect($customNames);
        } else {
            $toKeep = $existingPermissionNames->intersect(RoleResource::getClientePanelPermissionNames());
        }

        $this->permissions = $fromForm->merge($toKeep)->unique()->values();

        $keys = ['name', 'guard_name', 'display_name', 'description', 'company_id'];
        if (Utils::isTenancyEnabled() && Arr::has($data, Utils::getTenantModelForeignKey()) && filled($data[Utils::getTenantModelForeignKey()])) {
            $keys[] = Utils::getTenantModelForeignKey();
        }

        return Arr::only(array_merge($data, ['company_id' => $data['company_id'] ?? null]), $keys);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
