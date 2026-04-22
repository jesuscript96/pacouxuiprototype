<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PuestoGeneral;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PuestoGeneralPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PuestoGeneral');
    }

    public function view(AuthUser $authUser, PuestoGeneral $puestoGeneral): bool
    {
        return $authUser->can('View:PuestoGeneral');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PuestoGeneral');
    }

    public function update(AuthUser $authUser, PuestoGeneral $puestoGeneral): bool
    {
        return $authUser->can('Update:PuestoGeneral');
    }

    public function delete(AuthUser $authUser, PuestoGeneral $puestoGeneral): bool
    {
        return $authUser->can('Delete:PuestoGeneral');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PuestoGeneral');
    }

    public function restore(AuthUser $authUser, PuestoGeneral $puestoGeneral): bool
    {
        return $authUser->can('Restore:PuestoGeneral');
    }

    public function forceDelete(AuthUser $authUser, PuestoGeneral $puestoGeneral): bool
    {
        return $authUser->can('ForceDelete:PuestoGeneral');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PuestoGeneral');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PuestoGeneral');
    }

    public function replicate(AuthUser $authUser, PuestoGeneral $puestoGeneral): bool
    {
        return $authUser->can('Replicate:PuestoGeneral');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PuestoGeneral');
    }
}
