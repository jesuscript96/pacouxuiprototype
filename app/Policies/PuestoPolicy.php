<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Puesto;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PuestoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Puesto');
    }

    public function view(AuthUser $authUser, Puesto $puesto): bool
    {
        return $authUser->can('View:Puesto');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Puesto');
    }

    public function update(AuthUser $authUser, Puesto $puesto): bool
    {
        return $authUser->can('Update:Puesto');
    }

    public function delete(AuthUser $authUser, Puesto $puesto): bool
    {
        return $authUser->can('Delete:Puesto');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Puesto');
    }

    public function restore(AuthUser $authUser, Puesto $puesto): bool
    {
        return $authUser->can('Restore:Puesto');
    }

    public function forceDelete(AuthUser $authUser, Puesto $puesto): bool
    {
        return $authUser->can('ForceDelete:Puesto');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Puesto');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Puesto');
    }

    public function replicate(AuthUser $authUser, Puesto $puesto): bool
    {
        return $authUser->can('Replicate:Puesto');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Puesto');
    }
}
