<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Empresa;
use App\Models\SpatieRole;
use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UsuarioService
{
    /**
     * Crea un nuevo usuario con roles, pivot empresa_user (acceso panel por empresa) y JSON tipo.
     *
     * @param  array<string, mixed>  $data  Estado completo del formulario
     *
     * @throws ValidationException
     * @throws \Throwable
     */
    public function create(array $data): User
    {
        $data = $this->mergeNormalizedTipo($data);
        $this->validateEmpresaPrincipalCuandoAplica($data, null);
        $this->validateEmpresasCuandoAplica($data, null);
        $this->validateReportLimit($data, null);

        $mergedEmpresaIds = $this->mergedEmpresaPivotIds($data, null);
        $this->validarRolesPorEmpresas($data['roles'] ?? [], $mergedEmpresaIds);
        $principalId = $this->resolvePrincipalEmpresaIdForSave(null, $data);
        $createData = $this->prepareCreateData($data, $mergedEmpresaIds, $principalId, $this->formularioRequiereEmpresas($data));

        DB::beginTransaction();

        try {
            $user = User::create($createData);

            $roleIds = $data['roles'] ?? [];
            if (is_array($roleIds) && count($roleIds) > 0) {
                $user->syncRoles($roleIds);
            }

            $this->syncEmpresaUserPivot(
                $user,
                $mergedEmpresaIds,
                true,
                $user->empresa_id !== null ? (int) $user->empresa_id : null,
            );

            DB::commit();

            return $user->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Actualiza un usuario con roles, pivot empresa_user y JSON tipo.
     *
     * @param  array<string, mixed>  $data  Estado completo del formulario
     *
     * @throws ValidationException
     * @throws \Throwable
     */
    public function update(User $user, array $data): User
    {
        $data = $this->mergeNormalizedTipo($data);
        $this->validateEmpresaPrincipalCuandoAplica($data, $user);
        $this->validateEmpresasCuandoAplica($data, $user);
        $this->validateReportLimit($data, $user->id);

        $mergedEmpresaIds = $this->mergedEmpresaPivotIds($data, $user);
        $this->validarRolesPorEmpresas($data['roles'] ?? [], $mergedEmpresaIds);
        $oldEmpresaIds = $user->empresas()->get()->pluck('id')->unique()->values()->all();
        $removedEmpresaIds = array_values(array_diff($oldEmpresaIds, $mergedEmpresaIds));

        DB::beginTransaction();

        try {
            $principalId = $this->resolvePrincipalEmpresaIdForSave($user, $data);
            $updateData = $this->prepareUpdateData($data, $principalId, $this->formularioRequiereEmpresas($data));
            $user->update($updateData);

            if (filled($data['password'] ?? null)) {
                $user->password = $data['password'];
                $user->saveQuietly();
            }

            $requiereEmpresas = $this->formularioRequiereEmpresas($data);
            $this->syncEmpresaUserPivot(
                $user,
                $mergedEmpresaIds,
                $requiereEmpresas,
                $requiereEmpresas ? $principalId : null,
            );

            $roleIds = $data['roles'] ?? [];
            $user->syncRoles(is_array($roleIds) ? $roleIds : []);

            $this->revocarRolesSpatiePorEmpresas($user, $removedEmpresaIds);

            DB::commit();

            return $user->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mergeNormalizedTipo(array $data): array
    {
        $tipo = $data['tipo'] ?? [];
        if (is_string($tipo)) {
            $decoded = json_decode($tipo, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $tipo = $decoded;
            } elseif (trim($tipo) !== '') {
                $tipo = [$tipo];
            } else {
                $tipo = [];
            }
        }
        if (! is_array($tipo)) {
            $tipo = [];
        }

        $out = [];
        foreach ($tipo as $value) {
            if ($value === null || $value === '') {
                continue;
            }
            if ($value instanceof \BackedEnum) {
                $value = $value->value;
            }
            $out[] = is_string($value) ? $value : (string) $value;
        }

        $data['tipo'] = array_values(array_unique($out));

        return $data;
    }

    /**
     * BL: con tipo cliente o colaborador, la empresa principal (App móvil) es obligatoria.
     */
    protected function validateEmpresaPrincipalCuandoAplica(array $data, ?User $existingUser): void
    {
        if (! $this->formularioRequiereEmpresas($data)) {
            return;
        }

        if ($this->resolvePrincipalEmpresaIdForSave($existingUser, $data) === null) {
            throw ValidationException::withMessages([
                'empresa_id' => ['Selecciona la empresa principal.'],
            ]);
        }
    }

    protected function validateEmpresasCuandoAplica(array $data, ?User $existingUser): void
    {
        if (! $this->formularioRequiereEmpresas($data)) {
            return;
        }

        if ($this->mergedEmpresaPivotIds($data, $existingUser) === []) {
            throw ValidationException::withMessages([
                'empresas' => ['Selecciona al menos una empresa.'],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function formularioRequiereEmpresas(array $data): bool
    {
        $tipo = $data['tipo'] ?? [];

        return is_array($tipo) && (
            in_array('cliente', $tipo, true) || in_array('colaborador', $tipo, true)
        );
    }

    /**
     * BL: empresa_id es la empresa principal (no se toma del orden del multiselect). El pivot incluye principal + empresas seleccionadas.
     *
     * @param  bool  $applyPrincipalAlUsuario  Si es false, no se modifica users.empresa_id (p. ej. tipo solo administrador).
     */
    protected function syncEmpresaUserPivot(User $user, array $empresaIds, bool $applyPrincipalAlUsuario, ?int $principalEmpresaId): void
    {
        $user->empresas()->sync($empresaIds);
        if ($applyPrincipalAlUsuario) {
            if ($principalEmpresaId !== null) {
                $user->empresa_id = $principalEmpresaId;
            } elseif ($empresaIds !== []) {
                $user->empresa_id = (int) $empresaIds[0];
            } else {
                $user->empresa_id = null;
            }
        }
        $user->save();
    }

    /**
     * Empresa principal: campo empresa_id del formulario o la del usuario existente si no viene en el payload.
     */
    protected function resolvePrincipalEmpresaIdForSave(?User $existingUser, array $data): ?int
    {
        if (isset($data['empresa_id']) && $data['empresa_id'] !== '' && $data['empresa_id'] !== null) {
            return (int) $data['empresa_id'];
        }

        return $existingUser?->empresa_id !== null ? (int) $existingUser->empresa_id : null;
    }

    /**
     * IDs para empresa_user: empresa principal + selección del multiselect (únicos).
     *
     * @return list<int>
     */
    protected function mergedEmpresaPivotIds(array $data, ?User $existingUser): array
    {
        $selected = $this->empresasIdsFromState($data);
        if (! $this->formularioRequiereEmpresas($data)) {
            return $selected;
        }

        $principal = $this->resolvePrincipalEmpresaIdForSave($existingUser, $data);

        if ($principal !== null) {
            return array_values(array_unique(array_merge([$principal], $selected)));
        }

        return $selected;
    }

    /**
     * @param  array<int>  $empresaIdsRemovidas
     */
    protected function revocarRolesSpatiePorEmpresas(User $user, array $empresaIdsRemovidas): void
    {
        if ($empresaIdsRemovidas === []) {
            return;
        }

        $ids = array_map('intval', $empresaIdsRemovidas);

        $roles = $user->roles()->withoutGlobalScopes()->get();
        foreach ($roles as $role) {
            if ($role->company_id !== null && in_array((int) $role->company_id, $ids, true)) {
                $user->removeRole($role);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>  $mergedEmpresaIds
     * @return array<string, mixed>
     */
    protected function prepareCreateData(array $data, array $mergedEmpresaIds, ?int $principalId, bool $requiereEmpresas): array
    {
        $data = Arr::except($data, ['password_confirmation', 'roles', 'empresas', 'acceso_panel_cliente', 'empresa_id']);

        if ($requiereEmpresas) {
            $empresaId = $principalId ?? ($mergedEmpresaIds[0] ?? null);
            $data['empresa_id'] = $empresaId !== null ? (int) $empresaId : null;
        } else {
            $data['empresa_id'] = null;
        }

        if (empty($data['password'] ?? null)) {
            $data['password'] = null;
        }

        return array_intersect_key($data, array_flip((new User)->getFillable()));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepareUpdateData(array $data, ?int $principalId, bool $requiereEmpresas): array
    {
        $data = Arr::except($data, ['password_confirmation', 'roles', 'empresas', 'password', 'acceso_panel_cliente', 'empresa_id']);

        $updateData = array_intersect_key($data, array_flip((new User)->getFillable()));
        if ($requiereEmpresas) {
            $updateData['empresa_id'] = $principalId;
        }

        return $updateData;
    }

    /**
     * @return array<int>
     */
    protected function empresasIdsFromState(array $state): array
    {
        $empresas = $state['empresas'] ?? null;
        if (is_array($empresas)) {
            $ids = [];
            foreach ($empresas as $v) {
                if ($v === null || $v === '') {
                    continue;
                }
                $ids[] = (int) $v;
            }

            return array_values(array_unique($ids));
        }
        if (is_numeric($empresas)) {
            return [(int) $empresas];
        }

        return [];
    }

    /**
     * @param  array<int|string>  $roleIds
     * @param  array<int>  $empresaIds
     */
    protected function validarRolesPorEmpresas(array $roleIds, array $empresaIds): void
    {
        $auth = Auth::user();
        if ($auth instanceof User && $auth->hasRole(Utils::getSuperAdminName())) {
            return;
        }

        $ids = array_values(array_map('intval', array_filter($roleIds, fn ($id): bool => $id !== null && $id !== '')));
        if ($ids === []) {
            return;
        }

        $roles = SpatieRole::withoutGlobalScopes()->whereIn('id', $ids)->get();

        foreach ($roles as $rol) {
            if ($rol->company_id === null) {
                continue;
            }
            if (! in_array((int) $rol->company_id, $empresaIds, true)) {
                $label = $rol->display_name ?? $rol->name;

                throw ValidationException::withMessages([
                    'roles' => ["El rol «{$label}» pertenece a una empresa no asignada al usuario."],
                ]);
            }
        }
    }

    /**
     * Valida límite de usuarios con ver_reportes por empresa (solo tipo cliente).
     */
    protected function validateReportLimit(array $data, ?int $excludeUserId): void
    {
        if (! filter_var($data['ver_reportes'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $tipo = $data['tipo'] ?? [];
        if (! is_array($tipo) || ! in_array('cliente', $tipo, true)) {
            return;
        }

        $existingUser = $excludeUserId !== null ? User::query()->find($excludeUserId) : null;
        $empresaIds = $this->mergedEmpresaPivotIds($data, $existingUser);
        $empresaId = $empresaIds[0] ?? null;
        if (! $empresaId) {
            return;
        }

        $empresa = Empresa::find($empresaId);
        if (! $empresa) {
            return;
        }

        $limite = (int) ($empresa->num_usuarios_reportes ?? 0);
        $query = User::query()
            ->whereJsonContains('tipo', 'cliente')
            ->whereHas('empresas', fn ($q) => $q->where('empresas.id', $empresaId))
            ->where('ver_reportes', true);

        if ($excludeUserId !== null) {
            $query->where('id', '!=', $excludeUserId);
        }

        if ($query->count() >= $limite) {
            throw ValidationException::withMessages([
                'ver_reportes' => ['Se alcanzó el límite de usuarios con acceso a reportes para esta empresa.'],
            ]);
        }
    }
}
