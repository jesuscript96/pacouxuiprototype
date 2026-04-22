<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Empresa;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class EmpresaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Empresa');
    }

    public function view(AuthUser $authUser, Empresa $empresa): bool
    {
        return $authUser->can('View:Empresa');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Empresa');
    }

    public function update(AuthUser $authUser, Empresa $empresa): bool
    {
        return $authUser->can('Update:Empresa');
    }

    public function delete(AuthUser $authUser, Empresa $empresa): bool
    {
        if (! $authUser->can('Delete:Empresa')) {
            return false;
        }

        return ! $empresa->tieneUsuariosAsignados();
    }

    public function restore(AuthUser $authUser, Empresa $empresa): bool
    {
        return $authUser->can('Restore:Empresa');
    }

    public function forceDelete(AuthUser $authUser, Empresa $empresa): bool
    {
        if (! $authUser->can('ForceDelete:Empresa')) {
            return false;
        }

        return ! $empresa->tieneUsuariosAsignados();
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Empresa');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Empresa');
    }

    public function replicate(AuthUser $authUser, Empresa $empresa): bool
    {
        return $authUser->can('Replicate:Empresa');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Empresa');
    }
}
