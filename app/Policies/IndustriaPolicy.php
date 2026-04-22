<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Industria;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class IndustriaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Industria');
    }

    public function view(AuthUser $authUser, Industria $industria): bool
    {
        return $authUser->can('View:Industria');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Industria');
    }

    public function update(AuthUser $authUser, Industria $industria): bool
    {
        return $authUser->can('Update:Industria');
    }

    public function delete(AuthUser $authUser, Industria $industria): bool
    {
        return $authUser->can('Delete:Industria')
            && ! $industria->tieneSubindustrias()
            && ! $industria->tieneEmpresasAsignadas();
    }

    public function restore(AuthUser $authUser, Industria $industria): bool
    {
        return $authUser->can('Restore:Industria');
    }

    public function forceDelete(AuthUser $authUser, Industria $industria): bool
    {
        return $authUser->can('ForceDelete:Industria')
            && ! $industria->tieneSubindustrias()
            && ! $industria->tieneEmpresasAsignadas();
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Industria');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Industria');
    }

    public function replicate(AuthUser $authUser, Industria $industria): bool
    {
        return $authUser->can('Replicate:Industria');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Industria');
    }
}
