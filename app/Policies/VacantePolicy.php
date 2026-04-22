<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Vacante;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class VacantePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Vacante');
    }

    public function view(AuthUser $authUser, Vacante $vacante): bool
    {
        return $authUser->can('View:Vacante');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Vacante');
    }

    public function update(AuthUser $authUser, Vacante $vacante): bool
    {
        return $authUser->can('Update:Vacante');
    }

    public function delete(AuthUser $authUser, Vacante $vacante): bool
    {
        return $authUser->can('Delete:Vacante');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Vacante');
    }

    public function restore(AuthUser $authUser, Vacante $vacante): bool
    {
        return $authUser->can('Restore:Vacante');
    }

    public function forceDelete(AuthUser $authUser, Vacante $vacante): bool
    {
        return $authUser->can('ForceDelete:Vacante');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Vacante');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Vacante');
    }

    public function replicate(AuthUser $authUser, Vacante $vacante): bool
    {
        return $authUser->can('Replicate:Vacante');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Vacante');
    }
}
