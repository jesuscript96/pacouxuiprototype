<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role as SpatieRoleBase;
use Spatie\Permission\PermissionRegistrar;

class SpatieRole extends SpatieRoleBase
{
    protected $table = 'spatie_roles';

    protected $fillable = [
        'name',
        'guard_name',
        'display_name',
        'description',
        'company_id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('company', function (Builder $builder): void {
            $companyId = request()?->attributes?->get('shield.company_id');
            if ($companyId !== null) {
                $builder->where(function (Builder $q) use ($companyId): void {
                    $q->where('company_id', $companyId)
                        ->orWhereNull('company_id');
                });
            }
        });
    }

    /**
     * BL: Validar dependencias antes de permitir eliminación.
     * Se overridea delete() en lugar de usar hook deleting porque
     * la cadena de herencia de Spatie puede interferir con el evento.
     */
    public function delete(): ?bool
    {
        if ($this->tieneUsuariosAsignados()) {
            throw ValidationException::withMessages([
                'rol' => "No se puede eliminar el rol '{$this->name}' porque tiene usuarios asignados.",
            ]);
        }

        return parent::delete();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'company_id');
    }

    /**
     * BL: Verifica si el rol tiene usuarios asignados.
     * Un rol con usuarios no puede ser eliminado.
     */
    public function tieneUsuariosAsignados(): bool
    {
        return $this->usuarios()->exists();
    }

    /**
     * Usuarios que tienen este rol (tabla model_has_roles).
     */
    public function usuarios(): BelongsToMany
    {
        $registrar = app(PermissionRegistrar::class);

        return $this->morphedByMany(
            User::class,
            'model',
            config('permission.table_names.model_has_roles'),
            $registrar->pivotRole ?? 'role_id',
            config('permission.column_names.model_morph_key', 'model_id')
        );
    }

    /**
     * Scope: roles de una empresa + roles globales (company_id null).
     */
    public function scopeForCompany(Builder $query, ?int $companyId): Builder
    {
        return $query->where(function (Builder $q) use ($companyId) {
            $q->where('company_id', $companyId)
                ->orWhereNull('company_id');
        });
    }

    /**
     * Rol global (no asignado a ninguna empresa).
     */
    public function getIsGlobalAttribute(): bool
    {
        return is_null($this->company_id);
    }

    /**
     * Incluir company_id en la unicidad al crear (mismo nombre por empresa).
     */
    public static function create(array $attributes = []): static
    {
        $attributes['guard_name'] ??= \Spatie\Permission\Guard::getDefaultName(static::class);

        $params = [
            'name' => $attributes['name'],
            'guard_name' => $attributes['guard_name'],
        ];

        if (array_key_exists('company_id', $attributes)) {
            $params['company_id'] = $attributes['company_id'];
        }

        if (static::findByParam($params)) {
            throw \Spatie\Permission\Exceptions\RoleAlreadyExists::create(
                $attributes['name'],
                $attributes['guard_name']
            );
        }

        return static::query()->create($attributes);
    }

    /**
     * findById / findByParam usan guard_name; permitir búsqueda por company_id.
     */
    protected static function findByParam(array $params = []): ?\Spatie\Permission\Contracts\Role
    {
        $query = static::withoutGlobalScopes();

        $registrar = app(PermissionRegistrar::class);

        if ($registrar->teams) {
            $teamsKey = $registrar->teamsKey;
            $query->where(fn ($q) => $q->whereNull($teamsKey)
                ->orWhere($teamsKey, $params[$teamsKey] ?? getPermissionsTeamId()));
            unset($params[$teamsKey]);
        }

        foreach ($params as $key => $value) {
            $query->where($key, $value);
        }

        return $query->first();
    }
}
