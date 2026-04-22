<?php

declare(strict_types=1);

namespace App\Filament\Resources\Shield\Pages;

use App\Filament\Resources\Shield\RoleResource;
use App\Services\RolService;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreateRole extends \BezhanSalleh\FilamentShield\Resources\Roles\Pages\CreateRole
{
    protected static string $resource = RoleResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $permissions = collect($this->permissions ?? [])
            ->map(fn (mixed $p): string => is_string($p) ? $p : (string) ($p->name ?? $p))
            ->values()
            ->all();

        return app(RolService::class)->create($data, $permissions);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
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

        $this->permissions = collect($data)
            ->filter(function (mixed $permission, string $key) use ($nonPermissionKeys, $data): bool {
                if (in_array($key, $nonPermissionKeys)) {
                    return false;
                }
                $isClienteKey = str_contains($key, 'Filament\\Cliente\\');
                $isAsignable = (bool) ($data['is_asignable'] ?? false);

                return $isAsignable ? $isClienteKey : ! $isClienteKey;
            })
            ->values()
            ->flatten()
            ->unique();

        $keys = ['name', 'guard_name', 'display_name', 'description', 'company_id'];
        if (Utils::isTenancyEnabled() && Arr::has($data, Utils::getTenantModelForeignKey()) && filled($data[Utils::getTenantModelForeignKey()])) {
            $keys[] = Utils::getTenantModelForeignKey();
        }

        return Arr::only(array_merge($data, ['company_id' => $data['company_id'] ?? null]), $keys);
    }
}
