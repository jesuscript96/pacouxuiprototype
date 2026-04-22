<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CentroCosto;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CentroCostoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CentroCosto');
    }

    public function view(AuthUser $authUser, CentroCosto $centroCosto): bool
    {
        return $authUser->can('View:CentroCosto');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CentroCosto');
    }

    public function update(AuthUser $authUser, CentroCosto $centroCosto): bool
    {
        return $authUser->can('Update:CentroCosto');
    }

    public function delete(AuthUser $authUser, CentroCosto $centroCosto): bool
    {
        return $authUser->can('Delete:CentroCosto')
            && ! $centroCosto->tieneEmpresasAsignadas();
    }

    public function restore(AuthUser $authUser, CentroCosto $centroCosto): bool
    {
        return $authUser->can('Restore:CentroCosto');
    }

    public function forceDelete(AuthUser $authUser, CentroCosto $centroCosto): bool
    {
        return $authUser->can('ForceDelete:CentroCosto')
            && ! $centroCosto->tieneEmpresasAsignadas();
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CentroCosto');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CentroCosto');
    }

    public function replicate(AuthUser $authUser, CentroCosto $centroCosto): bool
    {
        return $authUser->can('Replicate:CentroCosto');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CentroCosto');
    }
}
