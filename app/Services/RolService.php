<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SpatieRole;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RolService
{
    private const SYNC_TIMEOUT_SECONDS = 60;

    private const PERMISSIONS_CHUNK_SIZE = 50;

    /**
     * Crea un nuevo rol con sus permisos en una transacción.
     *
     * @param  array<string, mixed>  $data  name, guard_name, display_name, description, company_id
     * @param  array<int|string>  $permissions  Nombres o IDs de permisos
     *
     * @throws ValidationException
     * @throws \Throwable
     */
    public function create(array $data, array $permissions = []): SpatieRole
    {
        $guardName = $data['guard_name'] ?? Utils::getFilamentAuthGuard();
        $this->validateUniqueName(
            (string) $data['name'],
            isset($data['company_id']) ? (int) $data['company_id'] : null,
            null,
            $guardName
        );

        $previousLimit = (int) ini_get('max_execution_time');
        set_time_limit(self::SYNC_TIMEOUT_SECONDS);

        DB::beginTransaction();

        try {
            $rol = SpatieRole::create(array_intersect_key(
                array_merge($data, ['guard_name' => $guardName]),
                array_flip((new SpatieRole)->getFillable())
            ));

            if ($permissions !== []) {
                $this->syncPermissionsInChunks($rol, $permissions);
            }

            DB::commit();

            return $rol->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        } finally {
            set_time_limit($previousLimit);
        }
    }

    /**
     * Actualiza un rol y sus permisos en una transacción.
     *
     * @param  array<string, mixed>  $data  name, guard_name, display_name, description, company_id
     * @param  array<int|string>  $permissions  Nombres o IDs de permisos
     *
     * @throws ValidationException
     * @throws \Throwable
     */
    public function update(SpatieRole $rol, array $data, array $permissions = []): SpatieRole
    {
        $name = $data['name'] ?? $rol->name;
        $companyId = array_key_exists('company_id', $data) ? $data['company_id'] : $rol->company_id;
        if ($name !== $rol->name || $companyId != $rol->company_id) {
            $this->validateUniqueName(
                (string) $name,
                $companyId !== null ? (int) $companyId : null,
                $rol->id,
                (string) ($data['guard_name'] ?? $rol->guard_name)
            );
        }

        $previousLimit = (int) ini_get('max_execution_time');
        set_time_limit(self::SYNC_TIMEOUT_SECONDS);

        DB::beginTransaction();

        try {
            $rol->update(array_intersect_key($data, array_flip((new SpatieRole)->getFillable())));

            $this->syncPermissionsInChunks($rol, $permissions);

            DB::commit();

            return $rol->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        } finally {
            set_time_limit($previousLimit);
        }
    }

    /**
     * Sincroniza permisos en lotes: solo revoca los que sobran y asigna los nuevos por chunks.
     * Reduce carga en BD frente a syncPermissions() con cientos de permisos.
     *
     * @param  array<int|string>  $permissionNames  Nombres (o IDs) de permisos deseados
     */
    private function syncPermissionsInChunks(SpatieRole $rol, array $permissionNames, int $chunkSize = self::PERMISSIONS_CHUNK_SIZE): void
    {
        $start = microtime(true);
        $current = $rol->permissions()->pluck('name')->all();
        $toAdd = array_values(array_diff($permissionNames, $current));
        $toRemove = array_values(array_diff($current, $permissionNames));

        if ($toRemove !== []) {
            $rol->revokePermissionTo($toRemove);
        }

        if ($toAdd !== []) {
            $chunks = array_chunk($toAdd, $chunkSize);
            foreach ($chunks as $chunk) {
                $rol->givePermissionTo($chunk);
            }
        }

        $duration = round(microtime(true) - $start, 2);
        Log::info('RolService: sincronización de permisos', [
            'rol_id' => $rol->id,
            'total' => count($permissionNames),
            'added' => count($toAdd),
            'removed' => count($toRemove),
            'duration_seconds' => $duration,
        ]);
    }

    /**
     * Unicidad de (name, guard_name, company_id). Excluir rol actual en edición.
     */
    private function validateUniqueName(
        string $name,
        ?int $companyId,
        ?int $excludeId,
        string $guardName
    ): void {
        $query = SpatieRole::withoutGlobalScopes()
            ->where('name', $name)
            ->where('guard_name', $guardName);

        if ($companyId !== null) {
            $query->where('company_id', $companyId);
        } else {
            $query->whereNull('company_id');
        }

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'name' => ['Ya existe un rol con este nombre'.($companyId !== null ? ' en esta empresa.' : ' (rol global).')],
            ]);
        }
    }
}
