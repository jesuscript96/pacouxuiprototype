<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Subindustria;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SubindustriaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Subindustria');
    }

    public function view(AuthUser $authUser, Subindustria $subindustria): bool
    {
        return $authUser->can('View:Subindustria');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Subindustria');
    }

    public function update(AuthUser $authUser, Subindustria $subindustria): bool
    {
        return $authUser->can('Update:Subindustria');
    }

    public function delete(AuthUser $authUser, Subindustria $subindustria): bool
    {
        return $authUser->can('Delete:Subindustria')
            && ! $subindustria->tieneEmpresasAsignadas();
    }

    public function restore(AuthUser $authUser, Subindustria $subindustria): bool
    {
        return $authUser->can('Restore:Subindustria');
    }

    public function forceDelete(AuthUser $authUser, Subindustria $subindustria): bool
    {
        return $authUser->can('ForceDelete:Subindustria')
            && ! $subindustria->tieneEmpresasAsignadas();
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Subindustria');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Subindustria');
    }

    public function replicate(AuthUser $authUser, Subindustria $subindustria): bool
    {
        return $authUser->can('Replicate:Subindustria');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Subindustria');
    }
}
